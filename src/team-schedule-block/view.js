document.addEventListener('DOMContentLoaded', () => {
    const blocks = document.querySelectorAll('.wp-block-create-block-team-schedule-block');

    blocks.forEach(block => {
        const select = block.querySelector('.team-schedule-select');
        const list = block.querySelector('.team-schedule-list');

        // Fetch teams and populate the dropdown
        fetch('/wp-json/wp/v2/team')
            .then(response => response.json())
            .then(data => {
                select.innerHTML = data.map(team => `<option value="${team.id}">${team.title.rendered}</option>`).join('');
                // Trigger change event to load the first team's schedule
                select.dispatchEvent(new Event('change'));
            });

        // Fetch and display games for the selected team
        select.addEventListener('change', () => {
            const teamId = select.value;
            fetch(`/wp-json/wp/v2/games?team=${teamId}`)
                .then(response => response.json())
                .then(data => {
                    list.innerHTML = data.map(game => `<li>${game.date} - ${game.opponent} (${game.home_away}) at ${game.field} - ${game.time}</li>`).join('');
                });
        });
    });
});
