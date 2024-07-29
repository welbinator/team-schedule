jQuery(document).ready(function($) {
    const deleteGame = function(index, table) {
        const row = table.find('tbody tr').eq(index);
        const opponentId = row.find('select[name="team_games[opponent][]"]').val();
        const date = row.find('input[name="team_games[date][]"]').val();
        const time = row.find('input[name="team_games[time][]"]').val();

        // Remove the row
        row.remove();

        // Find the game in the opponent's data and remove it
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'delete_opponent_game',
                opponent_id: opponentId,
                date: date,
                time: time,
            },
            success: function(response) {
                console.log(response);
            }
        });
    };

    $(document).on('click', '.add-game', function() {
        const teams = JSON.parse($('#team-games-table').attr('data-teams'));
        let options = '<option value="">' + __('Choose opponent', 'team-schedule') + '</option>';
        teams.forEach(team => {
            options += `<option value="${team.id}">${team.title}</option>`;
        });

        const newRow = `
            <tr>
                <td><input type="date" name="team_games[date][]" /></td>
                <td><input type="time" name="team_games[time][]" /></td>
                <td><input type="text" name="team_games[home_away][]" /></td>
                <td><input type="text" name="team_games[field][]" /></td>
                <td><select name="team_games[opponent][]">${options}</select></td>
                <td><button type="button" class="button delete-game" data-index="">-</button></td>
            </tr>
        `;
        $('#team-games-table tbody').append(newRow);
        // Update the add-game button to be in the last row
        $('#team-games-table tbody tr:last').find('.add-game').remove();
        $('#team-games-table tbody tr:last').append('<td><button type="button" class="button add-game">+</button></td>');
    });

    $(document).on('click', '.delete-game', function() {
        const index = $(this).closest('tr').index();
        const table = $('#team-games-table');
        deleteGame(index, table);
    });
});
