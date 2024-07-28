<?php

if ( ! class_exists( 'Team_Schedule_CPT' ) ) {
    class Team_Schedule_CPT {

        public static function init() {
            add_action( 'init', [ __CLASS__, 'register_cpt' ] );
            add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
            add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
            add_action( 'save_post_team', [ __CLASS__, 'save_meta_box_data' ] );
            add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_scripts' ] );
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
            $games = get_post_meta( $team_id, 'team_games', true );

            if ( ! is_array( $games ) ) {
                $games = [];
            }

            return rest_ensure_response( $games );
        }

        public static function add_meta_boxes() {
            add_meta_box(
                'team_games_meta_box',
                __( 'Team Games', 'team-schedule' ),
                [ __CLASS__, 'render_meta_box' ],
                'team',
                'normal',
                'high'
            );
        }

        public static function render_meta_box( $post ) {
            wp_nonce_field( 'team_games_meta_box', 'team_games_meta_box_nonce' );

            $games = get_post_meta( $post->ID, 'team_games', true );
            if ( ! is_array( $games ) ) {
                $games = [];
            }

            echo '<table id="team-games-table">';
            echo '<thead><tr><th>' . __( 'Date', 'team-schedule' ) . '</th><th>' . __( 'Time', 'team-schedule' ) . '</th><th>' . __( 'Home/Away', 'team-schedule' ) . '</th><th>' . __( 'Field', 'team-schedule' ) . '</th><th>' . __( 'Opponent', 'team-schedule' ) . '</th></tr></thead>';
            echo '<tbody>';
            foreach ( $games as $index => $game ) {
                echo '<tr>';
                echo '<td><input type="date" name="team_games[date][]" value="' . esc_attr( $game['date'] ) . '" /></td>';
                echo '<td><input type="time" name="team_games[time][]" value="' . esc_attr( $game['time'] ) . '" /></td>';
                echo '<td><input type="text" name="team_games[home_away][]" value="' . esc_attr( $game['home_away'] ) . '" /></td>';
                echo '<td><input type="text" name="team_games[field][]" value="' . esc_attr( $game['field'] ) . '" /></td>';
                echo '<td><input type="text" name="team_games[opponent][]" value="' . esc_attr( $game['opponent'] ) . '" /></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '<button type="button" class="button add-game">' . __( 'Add Game', 'team-schedule' ) . '</button>';
        }

        public static function enqueue_admin_scripts() {
            wp_enqueue_script( 'team-schedule-admin', plugin_dir_url( __FILE__ ) . 'admin.js', array( 'jquery' ), TEAM_SCHEDULE_VERSION, true );
        }

        public static function save_meta_box_data( $post_id ) {
            if ( ! isset( $_POST['team_games_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['team_games_meta_box_nonce'], 'team_games_meta_box' ) ) {
                return;
            }

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }

            if ( isset( $_POST['team_games'] ) && is_array( $_POST['team_games'] ) ) {
                $games = [];
                foreach ( $_POST['team_games']['date'] as $index => $date ) {
                    $games[] = [
                        'date'      => sanitize_text_field( $date ),
                        'time'      => sanitize_text_field( $_POST['team_games']['time'][ $index ] ),
                        'home_away' => sanitize_text_field( $_POST['team_games']['home_away'][ $index ] ),
                        'field'     => sanitize_text_field( $_POST['team_games']['field'][ $index ] ),
                        'opponent'  => sanitize_text_field( $_POST['team_games']['opponent'][ $index ] ),
                    ];
                }
                update_post_meta( $post_id, 'team_games', $games );
            }
        }
    }

    Team_Schedule_CPT::init();
}
