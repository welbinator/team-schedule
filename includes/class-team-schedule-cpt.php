<?php

if ( ! class_exists( 'Team_Schedule_CPT' ) ) {
    class Team_Schedule_CPT {

        public static function init() {
            add_action( 'init', [ __CLASS__, 'register_cpt' ] );
            add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
            add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
            add_action( 'save_post_team_schedule_team', [ __CLASS__, 'save_meta_box_data' ] );
            // add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_scripts' ] );
            add_action( 'wp_ajax_delete_opponent_game', [ __CLASS__, 'delete_opponent_game' ] );
        }

        public static function register_cpt() {
            $labels = array(
                'name' => __( 'Schedule Teams', 'team-schedule' ),
                'singular_name' => __( 'Team', 'team-schedule' ),
                'menu_name' => __( 'Schedule Teams', 'team-schedule' ),
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
                'menu_position' => 25, // Unique position
                'rewrite' => array( 'slug' => 'team-schedule-team' ), // Unique slug
            );

            register_post_type( 'team_schedule_team', $args );
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
                'post_type' => 'team_schedule_team',
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
                'team_schedule_team',
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

            $teams = get_posts( array(
                'post_type'   => 'team_schedule_team',
                'numberposts' => -1,
                'exclude'     => array( $post->ID ),
            ));

            echo '<table id="team-games-table" data-teams="' . esc_attr( json_encode( array_map( function( $team ) {
                return [ 'id' => $team->ID, 'title' => $team->post_title ];
            }, $teams ) ) ) . '">';
            echo '<thead><tr><th>' . __( 'Date', 'team-schedule' ) . '</th><th>' . __( 'Time', 'team-schedule' ) . '</th><th>' . __( 'Home/Away', 'team-schedule' ) . '</th><th>' . __( 'Field', 'team-schedule' ) . '</th><th>' . __( 'Opponent', 'team-schedule' ) . '</th><th></th></tr></thead>';
            echo '<tbody>';
            foreach ( $games as $index => $game ) {
                echo '<tr>';
                echo '<td><input type="date" name="team_games[date][]" value="' . esc_attr( $game['date'] ) . '" /></td>';
                echo '<td><input type="time" name="team_games[time][]" value="' . esc_attr( $game['time'] ) . '" /></td>';
                echo '<td><input type="text" name="team_games[home_away][]" value="' . esc_attr( $game['home_away'] ) . '" /></td>';
                echo '<td><input type="text" name="team_games[field][]" value="' . esc_attr( $game['field'] ) . '" /></td>';
                echo '<td><select name="team_games[opponent][]">';
                echo '<option value="">' . __( 'Choose Team', 'team-schedule' ) . '</option>';
                foreach ( $teams as $team ) {
                    $selected = ( $game['opponent'] == $team->ID ) ? 'selected="selected"' : '';
                    echo '<option value="' . esc_attr( $team->ID ) . '" ' . $selected . '>' . esc_html( $team->post_title ) . '</option>';
                }
                echo '</select></td>';
                echo '<td><button type="button" class="button delete-game" data-index="' . $index . '">-</button></td>';
                echo '</tr>';
            }
            // Add an empty row by default
            echo '<tr>';
            echo '<td><input type="date" name="team_games[date][]" /></td>';
            echo '<td><input type="time" name="team_games[time][]" /></td>';
            echo '<td><input type="text" name="team_games[home_away][]" /></td>';
            echo '<td><input type="text" name="team_games[field][]" /></td>';
            echo '<td><select name="team_games[opponent][]">';
            echo '<option value="">' . __( 'Choose Team', 'team-schedule' ) . '</option>';
            foreach ( $teams as $team ) {
                echo '<option value="' . esc_attr( $team->ID ) . '">' . esc_html( $team->post_title ) . '</option>';
            }
            echo '</select></td>';
            echo '<td><button type="button" class="button add-game">+</button></td>';
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
        }

        // public static function enqueue_admin_scripts() {
        //     wp_enqueue_script( 'team-schedule-admin', plugin_dir_url( __FILE__ ) . 'admin.js', array( 'jquery', 'wp-i18n' ), TEAM_SCHEDULE_VERSION, true );
        // }

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
                    $home_away = sanitize_text_field( $_POST['team_games']['home_away'][ $index ] );
                    $opponent_id = intval( $_POST['team_games']['opponent'][ $index ] );

                    $games[] = [
                        'date'      => sanitize_text_field( $date ),
                        'time'      => sanitize_text_field( $_POST['team_games']['time'][ $index ] ),
                        'home_away' => $home_away,
                        'field'     => sanitize_text_field( $_POST['team_games']['field'][ $index ] ),
                        'opponent'  => $opponent_id,
                    ];

                    // Automatically add the game to the opponent team as well
                    $opponent_games = get_post_meta( $opponent_id, 'team_games', true );

                    if ( ! is_array( $opponent_games ) ) {
                        $opponent_games = [];
                    }

                    $opponent_home_away = ($home_away === 'Home') ? 'Away' : 'Home';

                    $opponent_games[] = [
                        'date'      => sanitize_text_field( $date ),
                        'time'      => sanitize_text_field( $_POST['team_games']['time'][ $index ] ),
                        'home_away' => $opponent_home_away,
                        'field'     => sanitize_text_field( $_POST['team_games']['field'][ $index ] ),
                        'opponent'  => $post_id,
                    ];

                    update_post_meta( $opponent_id, 'team_games', $opponent_games );
                }
                update_post_meta( $post_id, 'team_games', $games );
            }
        }

        public static function delete_opponent_game() {
            if ( ! isset( $_POST['opponent_id'], $_POST['date'], $_POST['time'] ) ) {
                wp_send_json_error( 'Invalid data' );
            }

            $opponent_id = intval( $_POST['opponent_id'] );
            $date = sanitize_text_field( $_POST['date'] );
            $time = sanitize_text_field( $_POST['time'] );

            $opponent_games = get_post_meta( $opponent_id, 'team_games', true );

            if ( ! is_array( $opponent_games ) ) {
                $opponent_games = [];
            }

            foreach ( $opponent_games as $index => $game ) {
                if ( $game['date'] === $date && $game['time'] === $time ) {
                    unset( $opponent_games[ $index ] );
                    break;
                }
            }

            update_post_meta( $opponent_id, 'team_games', array_values( $opponent_games ) );

            wp_send_json_success( 'Game deleted from opponent' );
        }
    }
}

Team_Schedule_CPT::init();

