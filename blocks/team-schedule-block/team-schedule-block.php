<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_team_schedule_block_init() {
    register_block_type_from_metadata( plugin_dir_path( __FILE__ ) . '../../build/team-schedule-block' );
}
add_action( 'init', 'create_block_team_schedule_block_init' );

/**
 * Enqueue block editor assets.
 */
function team_schedule_enqueue_block_editor_assets() {
    if ( function_exists( 'get_current_screen' ) ) {
        $screen = get_current_screen();
        if ( $screen && $screen->is_block_editor() ) {
            wp_enqueue_script(
                'team-schedule-block-editor-script',
                plugin_dir_url( __FILE__ ) . '../../build/team-schedule-block/index.js',
                array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
                TEAM_SCHEDULE_VERSION,
                true
            );

            wp_enqueue_style(
                'team-schedule-block-editor-style',
                plugin_dir_url( __FILE__ ) . '../../build/team-schedule-block/style-index.css',
                array( 'wp-edit-blocks' ),
                TEAM_SCHEDULE_VERSION
            );
        }
    }
}
add_action( 'enqueue_block_editor_assets', 'team_schedule_enqueue_block_editor_assets' );
