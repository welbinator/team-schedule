document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.team-schedule-dropdown');
    const gamesContainer = document.querySelector('.team-schedule-games');

    if (dropdown) {
        // Fetch teams and populate the dropdown
        fetch('/wp-json/team-schedule/v1/teams')
            .then(response => response.json())
            .then(data => {
                console.log('Fetched teams:', data);
                if (data.length === 0) {
                    dropdown.innerHTML = '<option value="">No teams found</option>';
                } else {
                    data.forEach(team => {
                        console.log('Adding team to dropdown:', team.id, team.post_title);
                        const option = document.createElement('option');
                        option.value = team.ID; // Ensure the value is correctly assigned
                        option.textContent = team.post_title;
                        dropdown.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error fetching teams:', error));

        dropdown.addEventListener('change', function() {
            const teamId = dropdown.value;
            console.log('Selected team ID:', teamId); // Log the selected team ID
            if (teamId) {
                fetch(`/wp-json/team-schedule/v1/games?team=${teamId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Fetched games for team', teamId, ':', data);
                        gamesContainer.innerHTML = ''; // Clear previous games

                        if (data.length === 0) {
                            gamesContainer.innerHTML = 'No games found for this team.';
                        } else {
                            const gamesList = document.createElement('ul');
                            data.forEach(game => {
                                console.log('Processing game:', game);
                                const gameItem = document.createElement('li');
                                gameItem.textContent = `Date: ${game.date}, Time: ${game.time}, Home/Away: ${game.home_away}, Field: ${game.field}, Opponent: ${game.opponent}`;
                                gamesList.appendChild(gameItem);
                            });
                            gamesContainer.appendChild(gamesList);
                        }
                    })
                    .catch(error => console.error('Error fetching games:', error));
            } else {
                gamesContainer.innerHTML = 'Please choose a team.';
            }
        });
    }
});
