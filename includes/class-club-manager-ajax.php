<?php

class Club_Manager_Ajax {
    
    public static function init() {
        // Team AJAX actions - both logged in and non-logged in users
        add_action('wp_ajax_cm_create_team', array(__CLASS__, 'create_team'));
        add_action('wp_ajax_cm_get_teams', array(__CLASS__, 'get_teams'));
        add_action('wp_ajax_cm_get_team_players', array(__CLASS__, 'get_team_players'));
        
        // Player AJAX actions
        add_action('wp_ajax_cm_create_player', array(__CLASS__, 'create_player'));
        add_action('wp_ajax_cm_get_players', array(__CLASS__, 'get_players'));
        add_action('wp_ajax_cm_add_player_to_team', array(__CLASS__, 'add_player_to_team'));
        add_action('wp_ajax_cm_search_players', array(__CLASS__, 'search_players'));
        
        // Season preference
        add_action('wp_ajax_cm_save_season_preference', array(__CLASS__, 'save_season_preference'));
        
        // Remove player from team
        add_action('wp_ajax_cm_remove_player_from_team', array(__CLASS__, 'remove_player_from_team'));
        
        // Evaluation actions
        add_action('wp_ajax_cm_save_evaluation', array(__CLASS__, 'save_evaluation'));
        add_action('wp_ajax_cm_get_evaluations', array(__CLASS__, 'get_evaluations'));
        
        // AI Advice actions
        add_action('wp_ajax_cm_get_player_advice', array(__CLASS__, 'get_player_advice'));
        add_action('wp_ajax_cm_generate_player_advice', array(__CLASS__, 'generate_player_advice'));
        
        // Player History action - TOEGEVOEGD
        add_action('wp_ajax_cm_get_player_history', array(__CLASS__, 'get_player_history'));
    }
    
    public static function create_team() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to create teams');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $teams_table = $wpdb->prefix . 'cm_teams';
        
        $name = sanitize_text_field($_POST['name']);
        $coach = sanitize_text_field($_POST['coach']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        $result = $wpdb->insert(
            $teams_table,
            array(
                'name' => $name,
                'coach' => $coach,
                'season' => $season,
                'created_by' => $user_id
            )
        );
        
        if ($result) {
            wp_send_json_success(array(
                'id' => $wpdb->insert_id,
                'message' => 'Team created successfully'
            ));
        } else {
            wp_send_json_error('Failed to create team');
        }
    }
    
    public static function get_teams() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view teams');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $teams_table = $wpdb->prefix . 'cm_teams';
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        $teams = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $teams_table WHERE created_by = %d AND season = %s ORDER BY name",
            $user_id,
            $season
        ));
        
        wp_send_json_success($teams);
    }
    
    public static function create_player() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to add players');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $players_table = $wpdb->prefix . 'cm_players';
        $team_players_table = $wpdb->prefix . 'cm_team_players';
        
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $birth_date = sanitize_text_field($_POST['birth_date']);
        $email = sanitize_email($_POST['email']);
        $team_id = intval($_POST['team_id']);
        $position = sanitize_text_field($_POST['position']);
        $jersey_number = intval($_POST['jersey_number']);
        $notes = sanitize_textarea_field($_POST['notes']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        // Verify team ownership
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Check if player exists for this user
        $existing_player = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $players_table WHERE email = %s AND created_by = %d",
            $email,
            $user_id
        ));
        
        if ($existing_player) {
            $player_id = $existing_player->id;
        } else {
            // Create new player
            $result = $wpdb->insert(
                $players_table,
                array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'birth_date' => $birth_date,
                    'email' => $email,
                    'created_by' => $user_id
                )
            );
            
            if (!$result) {
                wp_send_json_error('Failed to create player');
            }
            
            $player_id = $wpdb->insert_id;
        }
        
        // Add player to team
        $result = $wpdb->insert(
            $team_players_table,
            array(
                'team_id' => $team_id,
                'player_id' => $player_id,
                'position' => $position,
                'jersey_number' => $jersey_number,
                'notes' => $notes,
                'season' => $season
            )
        );
        
        if ($result) {
            wp_send_json_success(array(
                'player_id' => $player_id,
                'message' => 'Player added successfully'
            ));
        } else {
            wp_send_json_error('Failed to add player to team');
        }
    }
    
    public static function get_team_players() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view players');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        // Verify team ownership
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
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
    
    public static function search_players() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to search players');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $search = sanitize_text_field($_POST['search']);
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        // Verify team ownership
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Get only players created by current user and not in the team
        $query = $wpdb->prepare("
            SELECT p.*
            FROM {$wpdb->prefix}cm_players p
            WHERE p.created_by = %d
            AND (p.first_name LIKE %s OR p.last_name LIKE %s OR p.email LIKE %s)
            AND p.id NOT IN (
                SELECT player_id FROM {$wpdb->prefix}cm_team_players 
                WHERE team_id = %d AND season = %s
            )
            LIMIT 10
        ", $user_id, "%$search%", "%$search%", "%$search%", $team_id, $season);
        
        $players = $wpdb->get_results($query);
        
        wp_send_json_success($players);
    }
    
    public static function add_player_to_team() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to add players to teams');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $team_players_table = $wpdb->prefix . 'cm_team_players';
        
        $team_id = intval($_POST['team_id']);
        $player_id = intval($_POST['player_id']);
        $position = sanitize_text_field($_POST['position']);
        $jersey_number = intval($_POST['jersey_number']);
        $notes = sanitize_textarea_field($_POST['notes']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        // Verify team ownership
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Verify player ownership
        $player_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_players WHERE id = %d",
            $player_id
        ));
        
        if ($player_check != $user_id) {
            wp_send_json_error('Unauthorized access to player');
            return;
        }
        
        $result = $wpdb->insert(
            $team_players_table,
            array(
                'team_id' => $team_id,
                'player_id' => $player_id,
                'position' => $position,
                'jersey_number' => $jersey_number,
                'notes' => $notes,
                'season' => $season
            )
        );
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Player added to team successfully'
            ));
        } else {
            wp_send_json_error('Failed to add player to team');
        }
    }
    
    public static function save_season_preference() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to save preferences');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        update_user_meta($user_id, 'cm_preferred_season', $season);
        
        wp_send_json_success();
    }
    
    public static function remove_player_from_team() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to remove players');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $team_players_table = $wpdb->prefix . 'cm_team_players';
        
        $team_id = intval($_POST['team_id']);
        $player_id = intval($_POST['player_id']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        // Verify team ownership
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Remove player from team (but keep player record)
        $result = $wpdb->delete(
            $team_players_table,
            array(
                'team_id' => $team_id,
                'player_id' => $player_id,
                'season' => $season
            ),
            array('%d', '%d', '%s')
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Player removed from team successfully'
            ));
        } else {
            wp_send_json_error('Failed to remove player from team');
        }
    }
    
    public static function save_evaluation() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to save evaluations');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $evaluations_table = $wpdb->prefix . 'cm_player_evaluations';
        
        $player_id = intval($_POST['player_id']);
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        $category = sanitize_text_field($_POST['category']);
        $subcategory = isset($_POST['subcategory']) ? sanitize_text_field($_POST['subcategory']) : null;
        $score = floatval($_POST['score']);
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        $user_id = get_current_user_id();
        
        // Verify team ownership
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Verify player is in this team
        $player_in_team = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cm_team_players 
            WHERE team_id = %d AND player_id = %d AND season = %s",
            $team_id, $player_id, $season
        ));
        
        if (!$player_in_team) {
            wp_send_json_error('Player not in this team');
            return;
        }
        
        // Always insert new evaluation (no more checking for existing ones)
        $result = $wpdb->insert(
            $evaluations_table,
            array(
                'player_id' => $player_id,
                'team_id' => $team_id,
                'season' => $season,
                'category' => $category,
                'subcategory' => $subcategory,
                'score' => $score,
                'notes' => $notes,
                'evaluated_by' => $user_id,
                'evaluated_at' => current_time('mysql')
            )
        );
        
        if ($result !== false) {
            // Trigger advice generation in background
            wp_schedule_single_event(time() + 1, 'cm_generate_player_advice', array($player_id, $team_id, $season));
            
            wp_send_json_success(array('message' => 'Evaluation saved successfully'));
        } else {
            wp_send_json_error('Failed to save evaluation');
        }
    }
    
    public static function get_evaluations() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view evaluations');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $evaluations_table = $wpdb->prefix . 'cm_player_evaluations';
        
        $player_id = intval($_POST['player_id']);
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        // Verify team ownership
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        $evaluations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $evaluations_table 
            WHERE player_id = %d AND team_id = %d AND season = %s
            ORDER BY evaluated_at DESC, category, subcategory",
            $player_id, $team_id, $season
        ));
        
        // Calculate averages for main categories
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
    
    public static function get_player_advice() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view advice');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $advice_table = $wpdb->prefix . 'cm_player_advice';
        
        $player_id = intval($_POST['player_id']);
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        // Verify team ownership
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Get the latest advice
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
    
    public static function generate_player_advice() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to generate advice');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        $player_id = intval($_POST['player_id']);
        $team_id = intval($_POST['team_id']);
        $season = sanitize_text_field($_POST['season']);
        $user_id = get_current_user_id();
        
        // Verify team ownership
        global $wpdb;
        $team_check = $wpdb->get_var($wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cm_teams WHERE id = %d",
            $team_id
        ));
        
        if ($team_check != $user_id) {
            wp_send_json_error('Unauthorized access to team');
            return;
        }
        
        // Schedule the advice generation
        wp_schedule_single_event(time() + 1, 'cm_generate_player_advice', array($player_id, $team_id, $season));
        
        wp_send_json_success(array('message' => 'Advice generation started'));
    }

    public static function get_player_history() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to view player history');
            return;
        }
        
        check_ajax_referer('club_manager_nonce', 'nonce');
        
        global $wpdb;
        $player_id = intval($_POST['player_id']);
        $user_id = get_current_user_id();
        
        // Get player info - only if created by current user
        $player = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cm_players WHERE id = %d AND created_by = %d",
            $player_id, $user_id
        ));
        
        if (!$player) {
            wp_send_json_error('Player not found or access denied');
            return;
        }
        
        // Get all teams history - only from teams created by current user
        $query = $wpdb->prepare("
            SELECT 
                t.name as team_name,
                t.season,
                tp.position,
                tp.jersey_number,
                tp.notes,
                t.created_at
            FROM {$wpdb->prefix}cm_team_players tp
            JOIN {$wpdb->prefix}cm_teams t ON tp.team_id = t.id
            WHERE tp.player_id = %d 
            AND t.created_by = %d
            ORDER BY t.season DESC, t.created_at DESC
        ", $player_id, $user_id);
        
        $history = $wpdb->get_results($query);
        
        wp_send_json_success(array(
            'player' => $player,
            'history' => $history
        ));
    }
}