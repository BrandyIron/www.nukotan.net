<?php
require_once(dirname(__FILE__) . '/../../lib/include/header.php');
require_once(dirname(__FILE__) . '/../../lib/include/footer.php');
require_once(dirname(__FILE__) . '/../../lib/include/nukotanDbh2.php');

$dbh = getPDO();

// Get Song Title
$sql = "SELECT id, song FROM nukotan_live WHERE id = :id";
$sth = $dbh->prepare($sql);
$sth->execute(array(':id' => $_GET['id']));
$res = $sth->fetchAll();
$song = $res[0]['song'];

publishHeader("ぬこたん公演まとめ～{$song}");

date_default_timezone_set('Asia/Tokyo');



// Retrieving Performance History
$sql = "SELECT song, date FROM nukotan_live WHERE song = '" . $song . "' ORDER BY date";

$playhistory = array();
$isfirst = true;
foreach ($dbh->query($sql) as $row) {
	$yearmonth = preg_replace('/(\d+)-(\d+)-(\d+)/', '${1}-${2}', $row['date']);
	if ($isfirst) {
		$startyearmonth = $yearmonth;
		$isfirst = false;
	}
	(array_key_exists($yearmonth, $playhistory)) ? $playhistory["$yearmonth"]++ : $playhistory["$yearmonth"] = 1;
}
// Detecting max value
$max = 0;
foreach ($playhistory as $k => $v) {
	if ($v > $max) $max = $v;
}


// Filling 0 count to no performance yearmonth
$date = new DateTime('NOW');
$endyearmonth = $date->format('Y-m');

$current = $startyearmonth;
while($current <= $endyearmonth) {
	if (!array_key_exists($current, $playhistory)) {
		$playhistory["$current"] = 0;
	}
	$date = new DateTime($current);
	$date->add(new DateInterval('P1M'));
	$current = $date->format('Y-m');
}
ksort($playhistory);

// Retrieving Live History
$sql = 'select DATE_FORMAT(date, \'%Y-%m\') AS yearmonth, COUNT(DISTINCT date) from nukotan_live GROUP BY yearmonth ORDER BY yearmonth;';
$livehistory = array();
foreach ($dbh->query($sql) as $row) {
	$livehistory[$row['yearmonth']] = $row['COUNT(DISTINCT date)'];
}

print <<< DOC_END
<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['YearMonth', 'PerformanceCount', 'LiveCount'],
DOC_END;

// Filling output array
$extract_array = array();
foreach ($playhistory as $yearmonth => $count) {
	if (array_key_exists($yearmonth, $livehistory) && $livehistory[$yearmonth] != false) {
		array_push($extract_array, "['{$yearmonth}', $count, {$livehistory[$yearmonth]}]");
	} else {
		array_push($extract_array, "['{$yearmonth}', $count, 0]");
	}
}


echo implode(',', $extract_array);

print <<< DOC_END
		]);
		
		var options = {
			'title' : 'nukotan', 
			'width' : 900,
			'height' : 500,
			'legend' : {position: 'top', maxLines: 3},
			'bar' : {groupWidth: '75%'}
		};
		
		var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script>
<div id="playingnumber">
DOC_END;
echo "<h2 class=\"title\">$song (*´Д`)ﾊｧﾊｧ</h2>";

// YouTube Movie
$contents = file_get_contents("http://gdata.youtube.com/feeds/api/videos?vq=陰陽座+" . $song);
//echo "http://gdata.youtube.com/feeds/api/videos?vq=" . $song;
$xml = simplexml_load_string($contents);
if ($xml) {
	$url = $xml->entry->link->attributes()->href;

	//echo $url;
	$video_id = preg_replace('@(http://www\.youtube\.com/watch\?v=)|(&feature=youtube_gdata)@i', '', $url);
	echo "<iframe width='1' height='1' src='http://www.youtube.com/embed/' frameborder='0' allowfullscreen></iframe>";
	echo "<iframe width='420' height='315' src='http://www.youtube.com/embed/{$video_id}' frameborder='0' allowfullscreen></iframe>";
}




print <<< DOC_END
<!-- end of #playingnumber -->
</div>
<!-- end of #columns -->
</div>
<div id="chart_div" style="width: 900px; height: 500px;"></div>
DOC_END;

publishFooter();
