<?php
require_once(dirname(__FILE__) . '/../../lib/include/header.php');
require_once(dirname(__FILE__) . '/../../lib/include/footer.php');
require_once(dirname(__FILE__) . '/../../lib/include/nukotanDbh2.php');

$dbh = getPDO();

require_once(dirname(__FILE__) . '/../../lib/nukotan_live/calc_live_rate_of_adoption.php');

publishHeader("ぬこたん公演まとめ(*´Д`)ﾊｧﾊｧ");

echo <<<EOD
<script>
$(document).ready(function(){
	$('.performance_list').dataTable({
		'order': [[1, 'desc']],
		'iDisplayLength': 100,
		'bStateSave': true
	});
});
</script>
EOD;

$sql = 'SELECT title, place, date FROM nukotan_live ORDER BY date DESC LIMIT 1';
$row = $dbh->query($sql)->fetch();

echo "<p>Newest Live : {$row['title']}@{$row['place']} ({$row['date']})</p>";
echo <<<EOD
<div class="container">
	<div class="page-header">
        	<h1>ぬこたん公演まとめ(*´Д`)ﾊｧﾊｧ</h1>
                <p class="lead"></p>
        </div>

<form method='POST'>
	<div class="form-group">
		<label for="startdate">Start Date</label>
		<input type="text" id="startdate" name="startdate" class="form-control">
	</div> 
	<div class="form-group">
		<label for="enddate">End Date</label>
		<input type="text" id="enddate" name="enddate" class="form-control">
	</div> 
	<div class="form-group">
		<label for="tour">Tour</label>
		<select name="tour" class="form-control">
			<option value="">All</option>
EOD;
$sql = 'SELECT DISTINCT title FROM nukotan_live ORDER BY date DESC';
foreach ($dbh->query($sql) as $row) {
	if (isset($_POST['tour']) && $row['title'] == $_POST['tour']) {
		echo "<option value='{$row['title']}' selected>{$row['title']}</option>\n";
	} else {
		echo "<option value='{$row['title']}'>{$row['title']}</option>\n";
	}
}

$checked = array();
if (isset($_POST['nukotan']) && $_POST['nukotan'][0] == 'nukotan') $checked['nukotan'] = "checked";
if (isset($_POST['makoto']) && $_POST['makoto'][0] == 'makoto') $checked['makoto'] = "checked";

echo <<<EOD
</select>
</div>
<div class="checkbox">
	<input type="checkbox" name="nukotan[]" value="nukotan" 
EOD;
echo "{$checked['nukotan']} ><label>Nukotan</label><br>";
echo <<<EOD
<input type="checkbox" name="makoto[]" value="makoto" 
EOD;
echo "{$checked['makoto']} ><label>Makoto (<a href='./makoto.php'>Titles that Makoto has not performed yet</a>)</label></div>";
echo <<<EOD
<input type="submit" class="btn btn-primary" value="Submit">
</form>
EOD;

// Check a conditions
$conds = array();
$cond_text = '';
if (isset($_POST['makoto']) && $_POST['makoto'] && $_POST['makoto'][0] == 'makoto') {
	array_push($conds, "date >= '2012-03-20'");
	$cond_text .= "<li>Titles that Makoto has already performed</li>";
} elseif (isset($_POST['startdate']) && isset($_POST['enddate']) && preg_match('/\d{4}-\d{2}-\d{2}/', $_POST['startdate']) && preg_match('/\d{4}-\d{2}-\d{2}/', $_POST['enddate'])) {
	array_push($conds, "date >= '{$_POST['startdate']}' AND date <= '{$_POST['enddate']}'");
	$cond_text .= "<li>Period : {$_POST['startdate']} ~ {$_POST['enddate']}</li>";
}

if (isset($_POST['tour'] ) && $_POST['tour']) {
	$place = $_POST['tour'];
	array_push($conds, "title = '$place'");
	$cond_text .= "<li>$place Tour</li>";
}

if (isset($_POST['nukotan']) && $_POST['nukotan']) {
	$nukotan = $_POST['nukotan'][0];
	if ($nukotan == 'nukotan') {
		$sql = 'SELECT song FROM nukotan_song';
		$nukotan_songs = array();
		foreach ($dbh->query($sql) as $row) {
			array_push($nukotan_songs, "song = '{$row['song']}'");
		}
		array_push($conds, '(' . implode(' OR ', $nukotan_songs) . ')');
	}
	$cond_text .= "<li>Nukotan songs</li>";
}

$condition = '';
if (count($conds) != 0) {
	$condition = 'WHERE ' . implode(' AND ', $conds);
	echo "<ul>$cond_text</ul>";
}

$sql = "SELECT id, song, count(song) as num FROM nukotan_live $condition GROUP BY song ORDER BY num DESC";

echo <<<EOD
<div class="table-responsible">
	<table class="table table-striped table-hover performance_list">
	<thead>
		<tr>
			<th>Song Title</th><th>Performance Count</th><th>Rate Of Adoption</th>
		</tr>
	</thead>
EOD;

foreach ($dbh->query($sql) as $row) {
	$rate_of_adoption = get_rate_of_adoption($row['song']);
	echo "<tr>
		<td>{$row['song']}<a href='./detail.php?id={$row['id']}' >[♪]</a></td>
		<td>{$row['num']}</td>
		<td>{$rate_of_adoption}</td>
		</tr>";
}




echo <<<EOD
</table>
</div>

</div>
EOD;

publishFooter();
