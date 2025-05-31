<?php
// File: includes/class-club-manager-teams-integration.php

class Club_Manager_Teams_Integration {
    
    public static function init() {
        // Check if Teams for WooCommerce Memberships is active
        if (!self::is_teams_plugin_active()) {
            return;
        }
        
        // Add AJAX handlers for club teams
        add_action('wp_ajax_cm_get_club_teams', array(__CLASS__, 'get_club_teams'));
        add_action('wp_ajax_cm_get_club_team_players', array(__CLASS__, 'get_club_team_players'));
        add_action('wp_ajax_cm_check_user_role', array(__CLASS__, 'check_user_role'));
        add_action('wp_ajax_cm_get_club_player_evaluations', array(__CLASS__, 'get_club_player_evaluations'));
        add_action('wp_ajax_cm_get_club_player_advice', array(__CLASS__, 'get_club_player_advice'));
        add_action('wp_ajax_cm_get_club_player_history', array(__CLASS__, 'get_club_player_history'));
    }
    
    /**
     * Check if Teams for WooCommerce Memberships is active
     */
    private static function is_teams_plugin_active() {
        return class_exists('WC_Memberships_For_Teams');
    }
    
    /**
     * Get user teams - helper function for Teams plugin integration
     */
    private static function wc_memberships_for_teams_get_user_teams($user_id) {
        if (!function_exists('wc_memberships_for_teams_get_user_teams')) {
            // Fallback implementation if the function doesn't exist
            $teams = array();
            
            // Try to get teams using WooCommerce Memberships for Teams API
            if (class_exists('WC_Memberships_For_Teams_Team')) {
                $args = array(
                    'post_type' => 'wc_memberships_team',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_member_id',
                            'value' => $user_id,
                            'compare' => '='
                        )
                    )
                );
                
                $team_posts = get_posts($args);
                foreach ($team_posts as $team_post) {
                    $teams[] = new WC_Memberships_For_Teams_Team($team_post->ID);
                }
            }
            
            return $teams;
        }
        
        return wc_memberships_for_teams_get_user_teams($user_id);
    }
    
    /**
     * Check if user is an owner or manager of any team
     */
    public static function is_user_owner_or_manager($user_id = null) {
        if (!self::is_teams_plugin_active()) {
            return false;
        }
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Get all teams the user is a member of
        $teams = self::wc_memberships_for_teams_get_user_teams($user_id);
        
        if (empty($teams)) {
            return false;
        }
        
        foreach ($teams as $team) {
            $role = $team->get_user_role($user_id);
            if (in_array($role, array('owner', 'manager'))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get all team members from the same club
     */
    private static function get_club_members($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $member_ids = array();
        
        // Get all teams the user is a member of
        $user_teams = self::wc_memberships_for_teams_get_user_teams($user_id);
        
        foreach ($user_teams as $team) {
            // Only process if user is owner or manager
            $role = $team->get_user_role($user_id);
            if (!in_array($role, array('owner', 'manager'))) {
                continue;
            }
            
            // Get all members of this team
            $members = $team->get_members();
            foreach ($members as $member) {
                $member_ids[] = $member->get_user_id();
            }
        }
        
        return array_unique($member_ids);
    }
    
    /**
     * AJAX handler to check user role
     */
    public static function check_user_role() {
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        $is_owner_or_manager = self::is_user_owner_or_manager($user_id);
        
        wp_send_json_success(array(
            'is_owner_or_manager' => $is_owner_or_manager
        ));
    }
    
    /**
     * AJAX handler to get club teams
     */
    public static function get_club_teams() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view club teams');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        
        // Check if user is owner or manager
        if (!self::is_user_owner_or_manager($user_id)) {
            wp_send_json_error('You must be an owner or manager to view club teams');
            return;
        }
        
        global $wpdb;
        $teams_table = $wpdb->prefix . 'cm_teams';
        $season = sanitize_text_field($_POST['season']);
        
        // Get all club member IDs
        $member_ids = self::get_club_members($user_id);
        
        if (empty($member_ids)) {
            wp_send_json_success(array());
            return;
        }
        
        // Get teams from all club members
        $placeholders = array_fill(0, count($member_ids), '%d');
        $query = $wpdb->prepare(
            "SELECT t.*, u.display_name as coach_name, u.user_email as coach_email 
            FROM $teams_table t
            LEFT JOIN {$wpdb->users} u ON t.created_by = u.ID
            WHERE t.created_by IN (" . implode(',', $placeholders) . ") 
            AND t.season = %s 
            ORDER BY u.display_name, t.name",
            array_merge($member_ids, array($season))
        );
        
        $teams = $wpdb->get_results($query);
        
        // Group teams by coach
        $grouped_teams = array();
        foreach ($teams as $team) {
            $coach_key = $team->created_by;
            if (!isset($grouped_teams[$coach_key])) {
                $grouped_teams[$coach_key] = array(
                    'coach_id' => $team->created_by,
                    'coach_name' => $team->coach_name,
                    'coach_email' => $team->coach_email,
                    'teams' => array()
                );
            }
            $grouped_teams[$coach_key]['teams'][] = $team;
        }
        
        wp_send_json_success(array_values($grouped_teams));
    }
    
    /**
     * AJAX handler to get club team players
     */
    public static function get_club_team_players() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view players');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        
        // Check if user is owner or manager
        if (!self::is_user_owner_or_manager($user_id)) {
            wp_send_json_error('You must be an owner or manager to view club team players');
            return;
        }
        
        global $wpdb;
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        
        // Verify team belongs to a club member
        $team_owner = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        $member_ids = self::get_club_members($user_id);
        if (!in_array($team_owner, $member_ids)) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Get players
        $query = $wpdb->prepare("
            SELECT p.*, tp.position, tp.jersey_number, tp.notes
            FROM {$wpdb->prefix}cm_team_players tp
            JOIN {$wpdb->prefix}cm_players p ON tp.player_id = p.id
            WHERE tp.team_id = %d AND tp.season = %s
            ORDER BY p.last_name, p.first_name
        ", $team_id, $season);
        
        $players = $wpdb->get_results($query);
        
        wp_send_json_success($players);
    }
    
    /**
     * AJAX handler to get club player evaluations (read-only)
     */
    public static function get_club_player_evaluations() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view evaluations');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        
        // Check if user is owner or manager
        if (!self::is_user_owner_or_manager($user_id)) {
            wp_send_json_error('You must be an owner or manager to view evaluations');
            return;
        }
        
        global $wpdb;
        $player_id = intval($_POST['player_id']);
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        
        // Verify team belongs to a club member
        $team_owner = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        $member_ids = self::get_club_members($user_id);
        if (!in_array($team_owner, $member_ids)) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Get evaluations
        $evaluations_table = $wpdb->prefix . 'cm_player_evaluations';
        $evaluations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $evaluations_table 
            WHERE player_id = %d AND team_id = %d AND season = %s
            ORDER BY evaluated_at DESC, category, subcategory",
            $player_id, $team_id, $season
        ));
        
        // Calculate averages
        $averages = $wpdb->get_results($wpdb->prepare(
            "SELECT category, AVG(score) as average 
            FROM $evaluations_table 
            WHERE player_id = %d AND team_id = %d AND season = %s
            GROUP BY category",
            $player_id, $team_id, $season
        ));
        
        wp_send_json_success(array(
            'evaluations' => $evaluations,
            'averages' => $averages
        ));
    }
    
    /**
     * AJAX handler to get club player advice (read-only)
     */
    public static function get_club_player_advice() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view advice');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        
        // Check if user is owner or manager
        if (!self::is_user_owner_or_manager($user_id)) {
            wp_send_json_error('You must be an owner or manager to view advice');
            return;
        }
        
        global $wpdb;
        $player_id = intval($_POST['player_id']);
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        
        // Verify team belongs to a club member
        $team_owner = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        $member_ids = self::get_club_members($user_id);
        if (!in_array($team_owner, $member_ids)) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Get advice
        $advice_table = $wpdb->prefix . 'cm_player_advice';
        $advice = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $advice_table 
            WHERE player_id = %d AND team_id = %d AND season = %s
            ORDER BY generated_at DESC
            LIMIT 1",
            $player_id, $team_id, $season
        ));
        
        if ($advice) {
            wp_send_json_success(array(
                'advice' => $advice->advice,
                'generated_at' => $advice->generated_at,
                'status' => $advice->status
            ));
        } else {
            wp_send_json_success(array(
                'advice' => null,
                'status' => 'no_evaluations'
            ));
        }
    }
    
    /**
     * AJAX handler to get club player history
     */
    public static function get_club_player_history() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view player history');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        
        // Check if user is owner or manager
        if (!self::is_user_owner_or_manager($user_id)) {
            wp_send_json_error('You must be an owner or manager to view player history');
            return;
        }
        
        global $wpdb;
        $player_id = intval($_POST['player_id']);
        
        // Get all club member IDs
        $member_ids = self::get_club_members($user_id);
        
        if (empty($member_ids)) {
            wp_send_json_error('No club members found');
            return;
        }
        
        // Get player info - only if created by a club member
        $placeholders = array_fill(0, count($member_ids), '%d');
        $player = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cm_players 
            WHERE id = %d AND created_by IN (" . implode(',', $placeholders) . ")",
            array_merge(array($player_id), $member_ids)
        ));
        
        if (!$player) {
            wp_send_json_error('Player not found or access denied');
            return;
        }
        
        // Get all teams history - only from teams created by club members
        $query = $wpdb->prepare("
            SELECT 
                t.name as team_name,
                t.season,
                tp.position,
                tp.jersey_number,
                tp.notes,
                t.created_at,
                u.display_name as coach_name
            FROM {$wpdb->prefix}cm_team_players tp
            JOIN {$wpdb->prefix}cm_teams t ON tp.team_id = t.id
            LEFT JOIN {$wpdb->users} u ON t.created_by = u.ID
            WHERE tp.player_id = %d 
            AND t.created_by IN (" . implode(',', $placeholders) . ")
            ORDER BY t.season DESC, t.created_at DESC
        ", array_merge(array($player_id), $member_ids));
        
        $history = $wpdb->get_results($query);
        
        wp_send_json_success(array(
            'player' => $player,
            'history' => $history
        ));
    }
}