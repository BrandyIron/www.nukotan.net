<?php
require_once(dirname(__FILE__) . '/../lib/include/nukotanDbh.php');
$dbh = getPDO();

date_default_timezone_set('Asia/Tokyo');

// Get Song Title
$sql = "SELECT id, song FROM nukotan_live WHERE id = :id";
$sth = $dbh->prepare($sql);
$sth->execute(array(':id' => $_GET['id']));
$res = $sth->fetchAll();
$song = $res[0]['song'];


print <<< DOC_END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
DOC_END;

echo "<title>$song (*´Д`)ﾊｧﾊｧ</title>";

print <<< DOC_END
<link type="text/css" rel="stylesheet" href="css/base.css" media="all" />

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-darkness/jquery-ui.css" rel="stylesheet" />
DOC_END;

// History
$sql = "SELECT song, date FROM nukotan_live WHERE song = '" . $song . "' ORDER BY date";

$playhistory = array();
$isfirst = true;
foreach ($dbh->query($sql) as $row) {
	$yearmonth = preg_replace('/(\d+)-(\d+)-(\d+)/', '${1}-${2}', $row['date']);
	if ($isfirst) {
		$startyearmonth = $yearmonth;
		$isfirst = false;
	}
	$playhistory["$yearmonth"]++;
}

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

print <<< DOC_END
<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['YearMonth', 'Count'],
DOC_END;

$extract_array = array();
foreach ($playhistory as $yearmonth => $count) {
	array_push($extract_array, "['{$yearmonth}', $count]");
}
echo implode(',', $extract_array);

print <<< DOC_END
		]);
		
		var options = {
			'title' : 'nukotan', 
			'width' : 900,
			'height' : 500
		};
		
		var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script>


</head>

<body>
<div id="columns">
<h1 class="page-title">ぬこたん☆公演まとめβ (*´Д`)ﾊｧﾊｧ</h1>
DOC_END;

print <<< DOC_END
<div id="playingnumber">
DOC_END;
echo "<h2 class=\"title\">$song (*´Д`)ﾊｧﾊｧ</h2>";

// YouTube Movie
$contents = file_get_contents("http://gdata.youtube.com/feeds/api/videos?vq=" . $song);
//echo "http://gdata.youtube.com/feeds/api/videos?vq=" . $song;
$xml = simplexml_load_string($contents);
$url = $xml->entry->link->attributes()->href;

//echo $url;
$video_id = preg_replace('@(http://www\.youtube\.com/watch\?v=)|(&feature=youtube_gdata)@i', '', $url);
echo "<iframe width='1' height='1' src='http://www.youtube.com/embed/' frameborder='0' allowfullscreen></iframe>";
echo "<iframe width='420' height='315' src='http://www.youtube.com/embed/{$video_id}' frameborder='0' allowfullscreen></iframe>";





// Map




print <<< DOC_END

<!-- end of #playingnumber -->
</div>
<!-- end of #columns -->
</div>
<div id="chart_div" style="width: 900px; height: 500px;"></div>
</body>
</html>
DOC_END;
?>