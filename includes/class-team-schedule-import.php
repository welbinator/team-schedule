<?php

if ( ! class_exists( 'Team_Schedule_Import' ) ) {
    class Team_Schedule_Import {

        public static function init() {
            add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );
            add_action( 'admin_post_team_schedule_import', [ __CLASS__, 'handle_csv_import' ] );
        }

        public static function admin_menu() {
            add_submenu_page(
                'edit.php?post_type=team',
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
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="team_schedule_import">
                    <?php wp_nonce_field( 'team_schedule_import', 'team_schedule_import_nonce' ); ?>
                    <input type="file" name="team_schedule_csv" required>
                    <input type="submit" value="<?php _e( 'Import CSV', 'team-schedule' ); ?>" class="button button-primary">
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
                self::import_team_data( $data );
            }

            wp_redirect( admin_url( 'edit.php?post_type=team' ) );
            exit;
        }

        private static function import_team_data( $data ) {
            $team_name = $data['Team'];
            $team = get_page_by_title( $team_name, OBJECT, 'team' );

            if ( ! $team ) {
                $team_id = wp_insert_post( [
                    'post_title'  => $team_name,
                    'post_type'   => 'team',
                    'post_status' => 'publish',
                ] );
            } else {
                $team_id = $team->ID;
            }

            $games = get_post_meta( $team_id, 'team_games', true );

            if ( ! is_array( $games ) ) {
                $games = [];
            }

            $games[] = [
                'date'      => $data['Date'],
                'time'      => $data['Time'],
                'home_away' => $data['Home/Away'],
                'field'     => $data['Field'],
                'opponent'  => $data['Opponent'],
            ];

            update_post_meta( $team_id, 'team_games', $games );
        }
    }

    Team_Schedule_Import::init();
}
