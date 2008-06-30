<?php
session_start();
require('conf/variables.php');
require_once 'autologin.inc.php';
require('top.php');

$personalladder = isset($_GET['personalladder']) ? $_GET['personalladder'] : "";

?>
<script type="text/javascript">
$(document).ready(function() 
    { 
        $("#ladder").tablesorter({sortList: [[0,0]], widgets: ['zebra'] }); 
    } 
); 
</script>
<h2><?php echo $personalladder ?> Ladder Standings</h2>
<table id="ladder" class="tablesorter" width="100%">
<thead>
<tr>
<th align="left" width='10%'>No.</th>
<th align="left">Avatar&nbsp; &nbsp;</th>
<th align="left">Player&nbsp; &nbsp;</th>
<th align="center">Wins&nbsp; &nbsp;</th>
<th align="center">Losses&nbsp; &nbsp;</th>
<th align="center">Total&nbsp; &nbsp;</th>
<th align="center">Wins% &nbsp; &nbsp;</th>
<th align="center">Rating&nbsp; &nbsp;</th>
<th align="center">Streak&nbsp; &nbsp;</th>
</tr>
</thead>
<tbody>

<?php

$sql = "select * from (select a.name, g.reported_on, 
       CASE WHEN g.winner = a.name THEN g.winner_elo ELSE g.loser_elo END as rating,
       CASE WHEN g.winner = a.name THEN g.winner_wins ELSE g.loser_wins END as wins,
       CASE WHEN g.winner = a.name THEN g.winner_losses ELSE g.loser_losses END as losses,
       CASE WHEN g.winner = a.name THEN g.winner_games ELSE g.loser_games END as games,
       CASE WHEN g.winner = a.name THEN g.winner_streak ELSE g.loser_streak END as streak
       FROM (select name, max(reported_on) as latest_game FROM $playerstable JOIN $gamestable ON (name = winner OR name = loser)  WHERE contested_by_loser = 0 AND withdrawn = 0 GROUP BY 1) a JOIN $gamestable g ON (g.reported_on = a.latest_game)) standings join $playerstable USING (name) WHERE
       reported_on > now() - interval $passivedays day AND rating >= $ladderminelo AND games >= $gamestorank ORDER BY 3 desc, 6 desc LIMIT $playersshown";
$result=mysql_query($sql,$db) ;
//echo "<br />".$sql;

##die ("failed to select players");
$cur=1;

unset($myrank);
while ($row = mysql_fetch_array($result)) {
    if ($row['name'] == $personalladder) {
        $myrank = $cur;
        break;
    }
    $cur++;
}
// Reset the result set
mysql_data_seek($result, 0);
$cur = 1;

while ($row = mysql_fetch_array($result)) {
	$namepage = "$row[name]";

    if (isset($myrank) && ($cur < ($myrank - 10) || $cur > ($myrank + 10))) {
        $cur++;
        continue;
    }


if ($row[streak] >= $hotcoldnum) {
    $picture = 'images/streakplusplus.gif';
} else if ($row[streak] <= -$hotcoldnum) {
    $picture = 'images/streakminusminus.gif';
} else if ($row[streak] > 0) {
    $picture = 'images/streakplus.gif';
} else if ($row[streak] < 0) {
    $picture = 'images/streakminus.gif';
} else {
    $picture = 'images/streaknull.gif';
}
if ($personalladder == $namepage) {
echo '<tr class="myrow">';
} else {
?>
<tr>
<?php 
}
?>
<td><?php echo "$cur"?></td>	
<td><?php echo "<img border='0' height='20px' src='avatars/$row[Avatar].gif' alt='avatar' />"?><a name="<?php echo $namepage ?>"></a></td>
<td><a href='profile.php?name=<?php echo "$namepage '> $namepage"; ?></a> </td>
<td><?php echo "$row[wins]" ?> </td>
<td><?php echo "$row[losses]" ?></td>
<td><?php echo ($row[games]); ?></td>
<td><?php printf("%.0f", $row['wins']/$row['games']*100); ?></td>
<td><?php echo "$row[rating]"; ?></td>
<td><?php echo "$row[streak]"?></td>
</tr>
<?php 
	$cur++;
}
?>
</tbody>
</table>

<p class="copyleft">To <i>show up</i> in the ladder above you must have played >= <?php echo "$gamestorank"; ?> games, have a rating of >= <?php echo "$ladderminelo"; ?> & have played within <?php echo "$passivedays"; ?> days. Don't worry  if you haven't played for a while. All it takes is one game to become active again. Your rating doesn't decay while you are gone. 1500 is the <i>average</i> skilled player, new players will have less and vets more. Hence, don't quit playing if you rate below 1500.</p>

<?php 
require('bottom.php');
?>
