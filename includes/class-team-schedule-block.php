<?php

if ( ! class_exists( 'Team_Schedule_Block' ) ) {
    class Team_Schedule_Block {
        
        public static function init() {
            add_action( 'init', [ __CLASS__, 'register_block' ] );
        }

        public static function register_block() {
            if ( ! function_exists( 'register_block_type' ) ) {
                return;
            }

            wp_register_script(
                'team-schedule-block-editor',
                plugins_url( 'assets/js/block-editor.js', __FILE__ ),
                array( 'wp-blocks', 'wp-element', 'wp-editor' ),
                TEAM_SCHEDULE_VERSION,
                true
            );

            wp_register_style(
                'team-schedule-block-editor',
                plugins_url( 'assets/css/block-editor.css', __FILE__ ),
                array( 'wp-edit-blocks' ),
                TEAM_SCHEDULE_VERSION
            );

            wp_register_style(
                'team-schedule-block',
                plugins_url( 'assets/css/block.css', __FILE__ ),
                array(),
                TEAM_SCHEDULE_VERSION
            );

            register_block_type( 'team-schedule/team-schedule-block', array(
                'editor_script' => 'team-schedule-block-editor',
                'editor_style' => 'team-schedule-block-editor',
                'style' => 'team-schedule-block',
                'render_callback' => [ __CLASS__, 'render_block' ]
            ) );
        }

        public static function render_block( $attributes ) {
            ob_start();
            ?>
            <div id="team-schedule-app"></div>
            <?php
            return ob_get_clean();
        }
    }
}
