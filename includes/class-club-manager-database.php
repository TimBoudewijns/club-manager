<?php

class Club_Manager_Database {
    
    public static function init() {
        // Any initialization needed
    }
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Teams table
        $teams_table = $wpdb->prefix . 'cm_teams';
        $sql_teams = "CREATE TABLE $teams_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            coach varchar(255) NOT NULL,
            season varchar(20) NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY created_by (created_by),
            KEY season (season)
        ) $charset_collate;";
        
        // Players table
        $players_table = $wpdb->prefix . 'cm_players';
        $sql_players = "CREATE TABLE $players_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            first_name varchar(255) NOT NULL,
            last_name varchar(255) NOT NULL,
            birth_date date NOT NULL,
            email varchar(255) NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY created_by (created_by)
        ) $charset_collate;";
        
        // Team players relationship table
        $team_players_table = $wpdb->prefix . 'cm_team_players';
        $sql_team_players = "CREATE TABLE $team_players_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            team_id mediumint(9) NOT NULL,
            player_id mediumint(9) NOT NULL,
            position varchar(100) DEFAULT NULL,
            jersey_number int(3) DEFAULT NULL,
            notes text DEFAULT NULL,
            season varchar(20) NOT NULL,
            PRIMARY KEY (id),
            KEY team_id (team_id),
            KEY player_id (player_id),
            KEY season (season),
            UNIQUE KEY team_player_season (team_id, player_id, season)
        ) $charset_collate;";
        
        // Player evaluations table
        $evaluations_table = $wpdb->prefix . 'cm_player_evaluations';
        $sql_evaluations = "CREATE TABLE $evaluations_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            player_id mediumint(9) NOT NULL,
            team_id mediumint(9) NOT NULL,
            season varchar(20) NOT NULL,
            category varchar(50) NOT NULL,
            subcategory varchar(100) DEFAULT NULL,
            score decimal(3,1) NOT NULL,
            notes text DEFAULT NULL,
            evaluated_by bigint(20) NOT NULL,
            evaluated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY player_team_season (player_id, team_id, season),
            KEY category (category),
            KEY evaluated_by (evaluated_by)
        ) $charset_collate;";
        
        // Player AI advice table
        $advice_table = $wpdb->prefix . 'cm_player_advice';
        $sql_advice = "CREATE TABLE $advice_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            player_id mediumint(9) NOT NULL,
            team_id mediumint(9) NOT NULL,
            season varchar(20) NOT NULL,
            advice text NOT NULL,
            status varchar(20) DEFAULT 'pending',
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY player_team_season (player_id, team_id, season),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_teams);
        dbDelta($sql_players);
        dbDelta($sql_team_players);
        dbDelta($sql_evaluations);
        dbDelta($sql_advice);
    }
}