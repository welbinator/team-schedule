jQuery(document).ready(function($) {
    $('.add-game').on('click', function() {
        const teams = JSON.parse($('#team-games-table').attr('data-teams'));
        let options = '';
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
            </tr>
        `;
        $('#team-games-table tbody').append(newRow);
    });
});
