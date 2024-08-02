document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.team-schedule-dropdown');
    const gamesContainer = document.querySelector('.team-schedule-games');

    if (dropdown) {
        if (!dropdown.hasEventListener) {
            dropdown.hasEventListener = true;

            const fetchAllTeams = async () => {
                let page = 1;
                let allTeams = [];
                let fetchMore = true;

                while (fetchMore) {
                    try {
                        const response = await fetch(`/wp-json/wp/v2/team_schedule_team?per_page=100&page=${page}`);
                        const data = await response.json();

                        if (data.length > 0) {
                            allTeams = allTeams.concat(data);
                            page++;
                        } else {
                            fetchMore = false;
                        }
                    } catch (error) {
                        console.error('Error fetching teams:', error);
                        fetchMore = false;
                    }
                }

                return allTeams;
            };

            const decodeHtmlEntities = (str) => {
                const textarea = document.createElement('textarea');
                textarea.innerHTML = str;
                return textarea.value;
            };

            fetchAllTeams().then(data => {
                dropdown.innerHTML = '<option value="">Please select your team</option>'; // Default option
                if (data.length === 0) {
                    dropdown.innerHTML += '<option value="">No teams found</option>';
                } else {
                    data.forEach(team => {
                        const option = document.createElement('option');
                        option.value = team.id;
                        option.textContent = decodeHtmlEntities(team.title.rendered);
                        dropdown.appendChild(option);
                    });
                }
            }).catch(error => console.error('Error fetching teams:', error));

            dropdown.addEventListener('change', function() {
                const teamId = dropdown.value;
                if (teamId) {
                    fetch(`/wp-json/team-schedule/v1/games?team=${teamId}`)
                        .then(response => response.json())
                        .then(async data => {
                            gamesContainer.innerHTML = ''; // Clear previous games

                            if (data.length === 0) {
                                gamesContainer.innerHTML = 'No games found for this team.';
                            } else {
                                const card = document.createElement('div');
                                card.classList.add('rounded-lg', 'border', 'bg-card', 'text-card-foreground', 'shadow-sm');

                                const header = document.createElement('div');
                                header.classList.add('flex', 'flex-col', 'space-y-1.5', 'p-6', 'px-7');

                                const title = document.createElement('h3');
                                title.classList.add('whitespace-nowrap', 'text-2xl', 'font-semibold', 'leading-none', 'tracking-tight');
                                title.textContent = 'Upcoming Games';

                                const description = document.createElement('p');
                                description.classList.add('text-sm', 'text-muted-foreground');
                                description.textContent = 'Schedule of upcoming games.';

                                header.appendChild(title);
                                header.appendChild(description);

                                const content = document.createElement('div');
                                content.classList.add('p-6');

                                const tableWrapper = document.createElement('div');
                                tableWrapper.classList.add('relative', 'w-full', 'overflow-auto');

                                const table = document.createElement('table');
                                table.classList.add('w-full', 'caption-bottom', 'text-sm', 'team-frontend');

                                const thead = document.createElement('thead');
                                thead.classList.add('[&amp;_tr]:border-b');
                                const headerRow = document.createElement('tr');
                                headerRow.classList.add('border-b', 'transition-colors', 'hover:bg-muted/50', 'data-[state=selected]:bg-muted');

                                const columns = ['Date', 'Time', 'Home/Away', 'Field', 'Opponent'];
                                columns.forEach(column => {
                                    const th = document.createElement('th');
                                    th.classList.add('h-12', 'px-4', 'text-left', 'align-middle', 'font-medium', 'text-muted-foreground');
                                    th.textContent = column;
                                    headerRow.appendChild(th);
                                });

                                thead.appendChild(headerRow);
                                table.appendChild(thead);

                                const tbody = document.createElement('tbody');
                                tbody.classList.add('[&amp;_tr:last-child]:border-0');

                                for (const game of data) {
                                    const opponentData = await fetchOpponentData(game.opponent);
                                    const gameRow = document.createElement('tr');
                                    gameRow.classList.add('border-b', 'transition-colors', 'hover:bg-muted/50', 'data-[state=selected]:bg-muted');

                                    const cells = [game.date, formatTime(game.time), game.home_away, game.field];
                                    cells.forEach(cell => {
                                        const td = document.createElement('td');
                                        td.classList.add('p-4', 'align-middle');
                                        td.textContent = decodeHtmlEntities(cell);
                                        gameRow.appendChild(td);
                                    });

                                    const opponentTd = document.createElement('td');
                                    opponentTd.classList.add('p-4', 'align-middle');
                                    const opponentLink = document.createElement('a');
                                    opponentLink.href = opponentData.url;
                                    opponentLink.textContent = opponentData.name;
                                    opponentTd.appendChild(opponentLink);
                                    gameRow.appendChild(opponentTd);

                                    tbody.appendChild(gameRow);
                                }

                                table.appendChild(tbody);
                                tableWrapper.appendChild(table);
                                content.appendChild(tableWrapper);

                                card.appendChild(header);
                                card.appendChild(content);

                                gamesContainer.appendChild(card);
                            }
                        })
                        .catch(error => console.error('Error fetching games:', error));
                } else {
                    gamesContainer.innerHTML = 'Please choose a team.';
                }
            });
        }
    }
});

async function fetchOpponentData(opponentId) {
    try {
        const response = await fetch(`/wp-json/wp/v2/team_schedule_team/${opponentId}`);
        const data = await response.json();
        return {
            name: decodeHtmlEntities(data.title.rendered),
            url: data.link
        };
    } catch (error) {
        console.error('Error fetching opponent data:', error);
        return {
            name: 'Unknown Opponent',
            url: '#'
        };
    }
}

function decodeHtmlEntities(str) {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = str;
    return textarea.value;
}

function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes);
    const options = { hour: 'numeric', minute: 'numeric', hour12: true };
    return new Intl.DateTimeFormat('en-US', options).format(date);
}
