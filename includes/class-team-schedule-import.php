<?php

if ( ! class_exists( 'Team_Schedule_Import' ) ) {
    class Team_Schedule_Import {

        public static function init() {
            add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );
            add_action( 'admin_post_team_schedule_import', [ __CLASS__, 'handle_csv_import' ] );
            add_action( 'admin_post_team_schedule_delete_games', [ __CLASS__, 'delete_all_games' ] );
            add_action( 'admin_post_team_schedule_delete_teams', [ __CLASS__, 'delete_all_teams' ] );
        }

        public static function admin_menu() {
            add_submenu_page(
                'edit.php?post_type=team_schedule_team',
                __( 'Import CSV', 'team-schedule' ),
                __( 'Import CSV', 'team-schedule' ),
                'manage_options',
                'team-schedule-import',
                [ __CLASS__, 'import_page' ]
            );
        }

        public static function import_page() {
            ?>
            <div class="wrap">
                <h1><?php _e( 'Import Team Schedule CSV', 'team-schedule' ); ?></h1>
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data" style="margin-bottom: 20px;">
                    <input type="hidden" name="action" value="team_schedule_import">
                    <?php wp_nonce_field( 'team_schedule_import', 'team_schedule_import_nonce' ); ?>
                    <input type="file" name="team_schedule_csv" required>
                    <input type="submit" value="<?php _e( 'Import CSV', 'team-schedule' ); ?>" class="button button-primary">
                </form>
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" onsubmit="return confirm('<?php _e( 'Are you sure you want to delete all game data? This action cannot be undone.', 'team-schedule' ); ?>');">
                    <input type="hidden" name="action" value="team_schedule_delete_games">
                    <?php wp_nonce_field( 'team_schedule_delete_games', 'team_schedule_delete_games_nonce' ); ?>
                    <input type="submit" value="<?php _e( 'Delete All Games', 'team-schedule' ); ?>" class="button button-danger">
                </form>
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" onsubmit="return confirm('<?php _e( 'Are you sure you want to delete all teams? This action cannot be undone.', 'team-schedule' ); ?>');">
                    <input type="hidden" name="action" value="team_schedule_delete_teams">
                    <?php wp_nonce_field( 'team_schedule_delete_teams', 'team_schedule_delete_teams_nonce' ); ?>
                    <input type="submit" value="<?php _e( 'Delete All Teams', 'team-schedule' ); ?>" class="button button-danger">
                </form>
            </div>
            <?php
        }

        public static function handle_csv_import() {
            if ( ! isset( $_POST['team_schedule_import_nonce'] ) || ! wp_verify_nonce( $_POST['team_schedule_import_nonce'], 'team_schedule_import' ) ) {
                wp_die( __( 'Nonce verification failed', 'team-schedule' ) );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You do not have permission to access this page', 'team-schedule' ) );
            }

            if ( ! isset( $_FILES['team_schedule_csv'] ) || ! is_uploaded_file( $_FILES['team_schedule_csv']['tmp_name'] ) ) {
                wp_die( __( 'No file uploaded', 'team-schedule' ) );
            }

            $file = $_FILES['team_schedule_csv']['tmp_name'];
            $csv_data = array_map( 'str_getcsv', file( $file ) );
            $header = array_shift( $csv_data );

            foreach ( $csv_data as $row ) {
                $data = array_combine( $header, $row );
                self::import_game_data( $data );
            }

            wp_redirect( admin_url( 'edit.php?post_type=team_schedule_team' ) );
            exit;
        }

        private static function import_game_data( $data ) {
            $date = self::parse_date( $data['Date'] );
            $time = self::parse_time( $data['Start Time'] );
            $field = sanitize_text_field( $data['Location Details'] );
        
            $home_team_id = self::get_or_create_team( sanitize_text_field( $data['Home Team'] ) );
            $away_team_id = self::get_or_create_team( sanitize_text_field( $data['Away Team'] ) );
        
            self::add_game_to_team( $home_team_id, $date, $time, 'Home', $field, $away_team_id );
            self::add_game_to_team( $away_team_id, $date, $time, 'Away', $field, $home_team_id );
        }

        private static function get_or_create_team( $team_name ) {
            $team = get_page_by_title( $team_name, OBJECT, 'team_schedule_team' );

            if ( ! $team ) {
                $team_id = wp_insert_post( [
                    'post_title'  => $team_name,
                    'post_type'   => 'team_schedule_team',
                    'post_status' => 'publish',
                ] );
            } else {
                $team_id = $team->ID;
            }

            return $team_id;
        }

        private static function add_game_to_team( $team_id, $date, $time, $home_away, $field, $opponent_id ) {
            $games = get_post_meta( $team_id, 'team_games', true );
        
            if ( ! is_array( $games ) ) {
                $games = [];
            }
        
            $games[] = [
                'date'      => $date,
                'time'      => $time,
                'home_away' => $home_away,
                'field'     => $field,
                'opponent'  => $opponent_id,
            ];
        
            update_post_meta( $team_id, 'team_games', $games );
        }

        private static function parse_date( $date_str ) {
            $date = DateTime::createFromFormat( 'n/j/Y', $date_str );
            if ( $date === false ) {
                return '';
            }
            return $date->format( 'Y-m-d' );
        }

        private static function parse_time( $time_str ) {
            $time = DateTime::createFromFormat( 'g:i A', $time_str );
            if ( $time === false ) {
                return '';
            }
            return $time->format( 'H:i' );
        }

        public static function delete_all_games() {
            if ( ! isset( $_POST['team_schedule_delete_games_nonce'] ) || ! wp_verify_nonce( $_POST['team_schedule_delete_games_nonce'], 'team_schedule_delete_games' ) ) {
                wp_die( __( 'Nonce verification failed', 'team-schedule' ) );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You do not have permission to access this page', 'team-schedule' ) );
            }

            $teams = get_posts( array(
                'post_type'   => 'team_schedule_team',
                'numberposts' => -1,
            ));

            foreach ( $teams as $team ) {
                delete_post_meta( $team->ID, 'team_games' );
            }

            wp_redirect( admin_url( 'edit.php?post_type=team_schedule_team' ) );
            exit;
        }

        public static function delete_all_teams() {
            if ( ! isset( $_POST['team_schedule_delete_teams_nonce'] ) || ! wp_verify_nonce( $_POST['team_schedule_delete_teams_nonce'], 'team_schedule_delete_teams' ) ) {
                wp_die( __( 'Nonce verification failed', 'team-schedule' ) );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You do not have permission to access this page', 'team-schedule' ) );
            }

            $teams = get_posts( array(
                'post_type'   => 'team_schedule_team',
                'numberposts' => -1,
            ));

            foreach ( $teams as $team ) {
                wp_delete_post( $team->ID, true );
            }

            wp_redirect( admin_url( 'edit.php?post_type=team_schedule_team' ) );
            exit;
        }
    }

    Team_Schedule_Import::init();
}
