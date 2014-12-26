<?php
date_default_timezone_set('Asia/Tokyo');

function publishLiveCounts() {
	require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
	$dbh = getPDO();

	$output_filename = '/../../html/chart/live_counts.js';

	$sql = 'SELECT COUNT(*) AS count, LEFT(date, 7) AS yearmonth FROM nukotan_live GROUP BY YEARMONTH ORDER BY date';
	$performance_counts = array();
	$sum = 0;
	foreach ($dbh->query($sql) as $res) {
		$sum += $res['count'];
		array_push($performance_counts, array('count' => $res['count'], 'yearmonth' => $res['yearmonth'], 'total' => $sum));
	}

	$sql = 'SELECT COUNT(DISTINCT place) AS count, LEFT(date, 7) AS yearmonth FROM nukotan_live GROUP BY yearmonth ORDER BY date';
	$live_counts = array();
	$sum = 0;
	foreach ($dbh->query($sql) as $res) {
		$sum += $res['count'];
		array_push($live_counts, array('count' => $res['count'], 'yearmonth' => $res['yearmonth'], 'total' => $sum));
	}

	$output = "var live_counts = [
		['date', 'live_counts', 'performance_counts', 'live_counts_total', 'performance_counts_total'],\n";

	$current_yearmonth = $live_counts[0]['yearmonth'];
	for ($i = 0; $i < count($performance_counts); $i++) {
		while ($i != 0 && $current_yearmonth != ($before_yearmonth = date('Y-m', strtotime("{$live_counts[$i]['yearmonth']} - 1 month")))) {
			$current_yearmonth = date('Y-m', strtotime("$current_yearmonth + 1 month"));
			$output .= "['{$current_yearmonth}', 0, 0, {$live_counts[$i-1]['total']}, {$performance_counts[$i-1]['total']}],\n";
		}

		$output .= "['{$live_counts[$i]['yearmonth']}', {$live_counts[$i]['count']}, {$performance_counts[$i]['count']}, {$live_counts[$i]['total']}, {$performance_counts[$i]['total']}],\n";
		$current_yearmonth = $live_counts[$i]['yearmonth'];
	}
	$output .= "];";

	file_put_contents(dirname(__FILE__) . $output_filename, $output);
}

