<?php

if ( ! class_exists( 'Team_Schedule_CPT' ) ) {
    class Team_Schedule_CPT {

        public static function init() {
            add_action( 'init', [ __CLASS__, 'register_cpt' ] );
            add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
        }

        public static function register_cpt() {
            $labels = array(
                'name' => __( 'Teams', 'team-schedule' ),
                'singular_name' => __( 'Team', 'team-schedule' ),
                'menu_name' => __( 'Teams', 'team-schedule' ),
                'name_admin_bar' => __( 'Team', 'team-schedule' ),
                'add_new' => __( 'Add New', 'team-schedule' ),
                'add_new_item' => __( 'Add New Team', 'team-schedule' ),
                'new_item' => __( 'New Team', 'team-schedule' ),
                'edit_item' => __( 'Edit Team', 'team-schedule' ),
                'view_item' => __( 'View Team', 'team-schedule' ),
                'all_items' => __( 'All Teams', 'team-schedule' ),
                'search_items' => __( 'Search Teams', 'team-schedule' ),
                'parent_item_colon' => __( 'Parent Teams:', 'team-schedule' ),
                'not_found' => __( 'No teams found.', 'team-schedule' ),
                'not_found_in_trash' => __( 'No teams found in Trash.', 'team-schedule' )
            );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'has_archive' => true,
                'show_in_rest' => true,
                'supports' => array( 'title', 'editor', 'custom-fields' ),
                'menu_icon' => 'dashicons-groups',
            );

            register_post_type( 'team', $args );
            register_post_type( 'game', [
                'label' => 'Games',
                'public' => true,
                'supports' => [ 'title', 'custom-fields' ],
                'show_in_rest' => true,
            ] );
        }

        public static function register_routes() {
            register_rest_route( 'team-schedule/v1', '/teams', array(
                'methods' => 'GET',
                'callback' => [ __CLASS__, 'get_teams' ],
            ));

            register_rest_route( 'team-schedule/v1', '/games', array(
                'methods' => 'GET',
                'callback' => [ __CLASS__, 'get_games' ],
                'args' => array(
                    'team' => array(
                        'required' => true,
                    ),
                ),
            ));
        }

        public static function get_teams() {
            $teams = get_posts( array(
                'post_type' => 'team',
                'numberposts' => -1,
            ));

            return rest_ensure_response( $teams );
        }

        public static function get_games( $request ) {
            $team_id = $request->get_param( 'team' );
            $games = get_posts( array(
                'post_type' => 'game',
                'meta_query' => array(
                    array(
                        'key' => 'team',
                        'value' => $team_id,
                        'compare' => '=',
                    ),
                ),
                'numberposts' => -1,
            ));

            return rest_ensure_response( $games );
        }
    }
}

