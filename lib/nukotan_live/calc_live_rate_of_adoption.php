<?php

function get_rate_of_adoption($song) {
	$dbh = getPDO();

	// Retriving first day of performance the song for ejection new song within 2 years
	$sql = 'SELECT MIN(date) FROM nukotan_live WHERE song = \'' . $song . '\'';
	foreach ($dbh->query($sql) as $res) {
		$min_date = $res['MIN(date)'];
	}
	$sql = 'SELECT DATEDIFF(DATE(NOW()), \'' . $min_date . '\') AS date_diff';
	foreach ($dbh->query($sql) as $res) {
		$date_diff = $res['date_diff'];
	}

	// Calculating songs released over 2 years ago
	if ($date_diff >= 730) {
		// Calculating live count from first day of performance the song (denominator)
		$sql = 'SELECT COUNT(DISTINCT date) FROM nukotan_live WHERE date >= (SELECT MIN(date) FROM nukotan_live WHERE song = \'' . $song . '\')';
		foreach ($dbh->query($sql) as $res) {
			$live_count = $res['COUNT(DISTINCT date)'];
		}

		// Calculating performance count (numerator)
		$sql = 'SELECT COUNT(song) FROM nukotan_live WHERE song = \'' . $song . '\' GROUP BY song';
		foreach ($dbh->query($sql) as $res) {
			$performance_count = $res['COUNT(song)'];
		}

		$rate_of_adoption = $performance_count / $live_count * 100;

		return $rate_of_adoption;
	} else {
		return '-';
	}
}
