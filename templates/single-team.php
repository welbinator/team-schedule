<?php
get_header(); 

// Get the current team ID
$team_id = get_the_ID();

// Fetch the team games
$games = get_post_meta( $team_id, 'team_games', true );

if ( ! is_array( $games ) ) {
    $games = [];
}

// Function to get the opponent's name
function get_opponent_name( $opponent_id ) {
    $opponent = get_post( $opponent_id );
    return $opponent ? html_entity_decode( $opponent->post_title ) : 'Unknown Opponent';
}

// Function to format the time
function format_time( $time ) {
    $date = DateTime::createFromFormat( 'H:i:s', $time );
    return $date ? $date->format( 'g:i A' ) : $time;
}
?>

<div class="rounded-lg border bg-card text-card-foreground shadow-sm" data-v0-t="card">
  <div class="flex flex-col space-y-1.5 p-6 px-7">
    <h3 class="whitespace-nowrap text-2xl font-semibold leading-none tracking-tight"><?php echo html_entity_decode( get_the_title() ); ?></h3>
    <p class="text-sm text-muted-foreground">Schedule of upcoming games.</p>
  </div>
  <div class="p-6">
    <div class="relative w-full overflow-auto">
      <table class="w-full caption-bottom text-sm team-frontend">
        <thead class="[&amp;_tr]:border-b">
          <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Time</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Home/Away</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Field</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Opponent</th>
          </tr>
        </thead>
        <tbody class="[&amp;_tr:last-child]:border-0">
          <?php foreach ( $games as $game ) : ?>
          <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
            <td class="p-4 align-middle"><?php echo esc_html( $game['date'] ); ?></td>
            <td class="p-4 align-middle"><?php echo esc_html( format_time( $game['time'] ) ); ?></td>
            <td class="p-4 align-middle"><?php echo esc_html( $game['home_away'] ); ?></td>
            <td class="p-4 align-middle"><?php echo esc_html( $game['field'] ); ?></td>
            <td class="p-4 align-middle"><?php echo esc_html( get_opponent_name( $game['opponent'] ) ); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
get_footer();
?>

