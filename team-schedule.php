<?php
/*
Plugin Name: Team Schedule
Description: A plugin to display team schedules for sports leagues.
Version: 1.0.9
Author: James Welbes
Text Domain: team-schedule-block
*/

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'TEAM_SCHEDULE_VERSION', '1.0' );
define( 'TEAM_SCHEDULE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include necessary files
include_once TEAM_SCHEDULE_PLUGIN_DIR . 'includes/class-team-schedule-cpt.php';
include_once TEAM_SCHEDULE_PLUGIN_DIR . 'includes/class-team-schedule-import.php';
include_once TEAM_SCHEDULE_PLUGIN_DIR . 'blocks/team-schedule-block/team-schedule-block.php';

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'team_schedule_activate' );
register_deactivation_hook( __FILE__, 'team_schedule_deactivate' );

function team_schedule_activate() {
    Team_Schedule_CPT::register_cpt();
    flush_rewrite_rules();
}

function team_schedule_deactivate() {
    flush_rewrite_rules();
}

// Initialize the plugin
function team_schedule_init() {
    Team_Schedule_CPT::init();
}
add_action( 'plugins_loaded', 'team_schedule_init' );

function enqueue_team_schedule_assets() {
    // Enqueue the JavaScript for the block
    wp_enqueue_script(
        'team-schedule-block-view',
        plugin_dir_url( __FILE__ ) . 'build/team-schedule-block/view.js',
        array(),
        '1.0',
        true
    );

    // Enqueue the stylesheet located in dist/styles.css
    wp_enqueue_style(
        'team-schedule-styles',
        plugin_dir_url( __FILE__ ) . 'dist/styles.css',
        array(),
        '1.0'
    );
}
add_action( 'wp_enqueue_scripts', 'enqueue_team_schedule_assets' );


function team_schedule_single_template( $template ) {
    if ( is_singular( 'team_schedule_team' ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/single-team.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'team_schedule_single_template' );


