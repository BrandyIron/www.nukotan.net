<?php
require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
require_once(dirname(__FILE__) . '/../include/statistics.php');

function getYoutubeFeed()
{
	$playlists_api = 'http://gdata.youtube.com/feeds/api/playlists/PL5FFFA409EAE8FAC4?v=2';
	$video_api = 'http://gdata.youtube.com/feeds/api/videos/';
	$resArr = array();

	for ($i = 1; $i < 200; $i += 50) {
		$res = file_get_contents($playlists_api . '&max-results=50&start-index=' . $i);
		$playlists_xml = simplexml_load_string($res);

		foreach($playlists_xml->entry as $entry) {
			$title = (string) $entry->title;

			$patterns = array('/' . preg_quote('http://www.youtube.com/watch?v=', '/') . '/', '/' . preg_quote('&feature=youtube_gdata') . '/');
			$replace = array('', '');
			$video_id = preg_replace($patterns, $replace, $entry->link->attributes()->href);

			if ($fileContents = file_get_contents($video_api . $video_id)) {
				$xml = new SimpleXMLElement($fileContents);

				$viewCount = (int) $xml->children('yt', true)->statistics->attributes()->viewCount;
				($xml->children('gd', true)->rating) ? $rating = $xml->children('gd', true)->rating->attributes()->numRaters : $rating = 0;
			}
			print_r(array('video_id' => $video_id, 'title' => $title, 'viewCount' => $viewCount, 'rating' => $rating, 'type' => 'youtube'));
			array_push($resArr, array('video_id' => $video_id, 'title' => $title, 'viewCount' => $viewCount, 'rating' => $rating, 'type' => 'youtube'));
		}
	}

	return $resArr;
}

function getNicoNicoFeed()
{
	$playlists_api = 'http://www.nicovideo.jp/mylist/12683379?rss=atom';
	$video_api = 'http://ext.nicovideo.jp/api/getthumbinfo/';
	$resArr = array();

	$res = file_get_contents($playlists_api);
	$playlists_xml = simplexml_load_string($res);

	foreach($playlists_xml->entry as $entry) {
		$title = (string) $entry->title;

		$video_id = preg_replace('/' . preg_quote('http://www.nicovideo.jp/watch/', '/') . '/', '', $entry->link->attributes()->href);

		if ($fileContents = file_get_contents($video_api . $video_id)) {
			$xml = new SimpleXMLElement($fileContents);

			$viewCount = (int) $xml->thumb->view_counter;
			$rating = ($xml->thumb->mylist_counter - 1 >= 0) ? $xml->thumb->mylist_counter - 1 : 0;
		}
		print_r(array('video_id' => $video_id, 'title' => $title, 'viewCount' => $viewCount, 'rating' => $rating, 'type' => 'niconico'));
		array_push($resArr, array('video_id' => $video_id, 'title' => $title, 'viewCount' => $viewCount, 'rating' => $rating, 'type' => 'niconico'));
	}
	return $resArr;
}

function storeFeedData($feedArr)
{
	require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');
	$dbh = getPDO();

	foreach($feedArr as $feed) {
		switch ($feed['type']) {
		case "youtube" :
			$table = "video_youtube";
			break;
		case "niconico" :
			$table = "video_niconico";
			break;
		}

		$sql = "SELECT count(video_id) FROM $table WHERE video_id = :video_id";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':video_id' => $feed['video_id']));
		$res = $sth->fetchAll();
		if ($res[0]['count(video_id)'] == 0) {
			$sql = "INSERT INTO $table (video_id, title, view_count, rating_count) VALUES (:video_id, :title, :view_count, :rating_count)";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':video_id' => $feed['video_id'], ':title' => $feed['title'], ':view_count' => $feed['viewCount'], ':rating_count' => $feed['rating']));
		} else {
			$sql = "UPDATE $table SET view_count = :view_count, rating_count = :rating_count WHERE video_id = :video_id";
			//echo "UPDATE $table SET view_count = {$feed['viewCount']}, rating_count = {$feed['rating']} WHERE video_id = {$feed['video_id']}\n";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->execute(array(':view_count' => $feed['viewCount'], ':rating_count' => $feed['rating'], ':video_id' => $feed['video_id']));
		}
	}
}


function updateDeviation($table)
{
	$videoIdArr = $viewCountArr = $ratingCountArr = $viewDeviationArr = $ratingDeviationArr = array();
	$dbh = getPDO();

	$sql = "SELECT video_id, view_count, rating_count FROM $table";
	foreach ($dbh->query($sql) as $row) {
		array_push($videoIdArr, $row['video_id']);
		array_push($viewCountArr, $row['view_count']);
		array_push($ratingCountArr, $row['rating_count']);
	}

	$viewDeviationArr = Statistics::updateDeviationArr($viewCountArr);
	$ratingDeviationArr = Statistics::updateDeviationArr($ratingCountArr);

	for ($i = 0; $i < count($videoIdArr); $i++) {
		$sql = "UPDATE $table SET view_deviation = :view_deviation, rating_deviation = :rating_deviation WHERE video_id = :video_id";
		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':view_deviation' => $viewDeviationArr[$i], ':rating_deviation' => $ratingDeviationArr[$i], ':video_id' => $videoIdArr[$i]));
	}

}

function connectYoutubeNicoNico() {
	$dbh = getPDO();

	// Truncate rel_video table first
	$sql = 'TRUNCATE rel_video';
	foreach ($dbh->query($sql) as $row) {
		print_r($row);
	}

	$sql = 'SELECT video_id, title FROM video_youtube';
	foreach ($dbh->query($sql) as $row) {
		$youtube_id = $row['video_id'];
		$title = preg_replace('/\[陰陽座 cover\] /', '', $row['title']);

		$sql = 'SELECT video_id, title FROM video_niconico WHERE title REGEXP \'　' . $title . '(　|を)\'';
		foreach ($dbh->query($sql) as $row) {
			$niconico_id = $row['video_id'];
			$sql = 'SELECT COUNT(id) FROM rel_video WHERE youtube_id = \'' . $youtube_id . '\' OR niconico_id = \'' . $niconico_id . '\'';
			//echo "$sql\n";
			foreach ($dbh->query($sql) as $row) {
				if ($row['COUNT(id)']) {
					$sql = 'UPDATE rel_video SET youtube_id = :youtube_id, niconico_id = :niconico_id WHERE youtube_id = :youtube_id OR niconico_id = :niconico_id';
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':youtube_id' => $youtube_id, ':niconico_id' => $niconico_id));
				} else {
					$sql = 'INSERT rel_video (youtube_id, niconico_id) VALUES (:youtube_id, :niconico_id)';
					$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':youtube_id' => $youtube_id, ':niconico_id' => $niconico_id));
				}
			}
		}	
	}
}


function publishDeviation()
{
	$dbh = getPDO();

	$output_filename = '/../../html/chart/deviations.js';
	$header = "var deviations = [
		['video_title', 'youtube_view_deviation', 'youtube_rating_deviation', 'niconico_view_deviation', 'niconico_rating_deviation'],\n";
	$output = $header;
	$sql = "SELECT video_youtube.title AS title, video_youtube.view_deviation AS youtube_view_deviation, video_youtube.rating_deviation AS youtube_rating_deviation, video_niconico.view_deviation AS niconico_view_deviation, video_niconico.rating_deviation AS niconico_rating_deviation FROM video_niconico, video_youtube, rel_video WHERE rel_video.youtube_id = video_youtube.video_id AND rel_video.niconico_id = video_niconico.video_id ORDER BY video_youtube.title";
	foreach ($dbh->query($sql) as $row) {
		$title = preg_replace('/\[陰陽座 cover\] /', '', $row['title']);
		$output .= "['$title', {$row['youtube_view_deviation']}, {$row['youtube_rating_deviation']}, {$row['niconico_view_deviation']}, {$row['niconico_rating_deviation']}],\n";
	}
	$output .= "];";

	file_put_contents(dirname(__FILE__) . $output_filename, $output);
}



storeFeedData(getYoutubeFeed());
storeFeedData(getNicoNicoFeed());

connectYoutubeNicoNico();

updateDeviation('video_youtube');
updateDeviation('video_niconico');

publishDeviation();
