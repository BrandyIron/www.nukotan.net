<?php
date_default_timezone_set('Asia/Tokyo');

function publishLivePlace() {
	require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
	$dbh = getPDO();

	$output_filename = '/../../html/map/live_places.js';
	$sql = 'select date, place from nukotan_live group by place order by date';
	$output = 'var live_places = [';
	foreach ($dbh->query($sql) as $res) {
		$date = htmlspecialchars($res['date'], ENT_QUOTES);
		$place = htmlspecialchars($res['place'], ENT_QUOTES);
		$output .= "['$date', '$place'],\n";
	}
	$output .= "];";
	file_put_contents(dirname(__FILE__) . $output_filename, $output);
}

publishLivePlace();
