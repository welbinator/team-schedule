jQuery(document).ready(function($) {
    const { __ } = wp.i18n;

    const deleteAllGames = function() {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'team_schedule_delete_games',
                security: $('#team_schedule_delete_games_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                } else {
                    alert(response.data || __('An error occurred.', 'team-schedule'));
                }
            }
        });
    };

    const deleteAllTeams = function() {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'team_schedule_delete_teams',
                security: $('#team_schedule_delete_teams_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                } else {
                    alert(response.data || __('An error occurred.', 'team-schedule'));
                }
            }
        });
    };

    // Attach AJAX handler to the delete games form
    $(document).on('submit', '#delete-games-form', function(e) {
        e.preventDefault();

        if (confirm(__('Are you sure you want to delete all game data? This action cannot be undone.', 'team-schedule'))) {
            deleteAllGames();
        }
    });

    // Attach AJAX handler to the delete teams form
    $(document).on('submit', '#delete-teams-form', function(e) {
        e.preventDefault();

        if (confirm(__('Are you sure you want to delete all teams? This action cannot be undone.', 'team-schedule'))) {
            deleteAllTeams();
        }
    });
});
