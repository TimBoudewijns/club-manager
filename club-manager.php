<?php
/**
 * Plugin Name: Club Manager
 * Plugin URI: https://example.com/club-manager
 * Description: A comprehensive club management system for hockey trainers
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: club-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CLUB_MANAGER_VERSION', '1.0.0');
define('CLUB_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLUB_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once CLUB_MANAGER_PLUGIN_DIR . 'includes/class-club-manager-database.php';
require_once CLUB_MANAGER_PLUGIN_DIR . 'includes/class-club-manager-ajax.php';
require_once CLUB_MANAGER_PLUGIN_DIR . 'includes/class-club-manager-shortcode.php';
require_once CLUB_MANAGER_PLUGIN_DIR . 'includes/class-club-manager-ai.php';

// Main plugin class
class Club_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Hook activation and deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize components
        add_action('init', array($this, 'init'));
    }
    
    public function activate() {
        Club_Manager_Database::create_tables();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function init() {
        // Initialize database
        Club_Manager_Database::init();
        
        // Initialize AJAX handlers
        Club_Manager_Ajax::init();
        
        // Initialize shortcode
        Club_Manager_Shortcode::init();
        
        // Initialize AI
        Club_Manager_AI::init();
        
        // Load text domain
        load_plugin_textdomain('club-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Initialize the plugin
Club_Manager::get_instance();