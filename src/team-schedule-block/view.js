document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.team-schedule-dropdown');
    const gamesContainer = document.querySelector('.team-schedule-games');

    if (dropdown) {
        // Fetch teams and populate the dropdown
        fetch('/wp-json/team-schedule/v1/teams')
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    dropdown.innerHTML = '<option value="">No teams found</option>';
                } else {
                    data.forEach(team => {
                        const option = document.createElement('option');
                        option.value = team.ID;
                        option.textContent = team.post_title;
                        dropdown.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error fetching teams:', error));

        dropdown.addEventListener('change', function() {
            const teamId = dropdown.value;
            if (teamId) {
                fetch(`/wp-json/team-schedule/v1/games?team=${teamId}`)
                    .then(response => response.json())
                    .then(data => {
                        gamesContainer.innerHTML = ''; // Clear previous games

                        if (data.length === 0) {
                            gamesContainer.innerHTML = 'No games found for this team.';
                        } else {
                            const gamesList = document.createElement('ul');
                            gamesList.classList.add('game-list'); // Add class to ul

                            // Add header row
                            const headerItem = document.createElement('li');
                            headerItem.classList.add('game-list-header');
                            headerItem.innerHTML = '<strong>Date</strong>, <strong>Time</strong>, <strong>Home/Away</strong>, <strong>Field</strong>, <strong>Opponent</strong>';
                            gamesList.appendChild(headerItem);

                            data.forEach(game => {
                                const gameItem = document.createElement('li');
                                gameItem.classList.add('game-list-item'); // Add class to li
                                gameItem.textContent = `Date: ${game.date}, Time: ${game.time}, ${game.home_away}, Field: ${game.field}, Opponent: ${game.opponent}`;
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
