<?php
require_once(dirname(__FILE__) . '/../include/twitteroauth/twitteroauth.php');
require_once(dirname(__FILE__) . '/../../data/twitter_key.php');

date_default_timezone_set('Asia/Tokyo');


$twitterURL = 'https://api.twitter.com/1.1/statuses/update.json';

$mode = $argv[1];

$dsn = 'mysql:host=localhost;dbname=nukotan_word';
$username = 'nukotan';
$password = '0716';
$options = array(
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);
$dbh = new PDO($dsn, $username, $password, $options);

// Get lowest number tweet message
$candidateArr = array();
$sql = "SELECT id, word, tweet_count, referer FROM nukotan_word WHERE tweet_count = (SELECT min(tweet_count) FROM nukotan_word)";
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute();
$res = $sth->fetchAll();
foreach ($res as $val) {
	array_push($candidateArr, $val['id']);
}

$pickupId = $candidateArr[rand(0, count($candidateArr))];
foreach ($res as $val) {
	if ($val['id'] == $pickupId) {
		if (mb_strlen($val['word']) < 110) {
			$message = $val['word'] . ' ' . $val['referer'];
		} else {
			$message = mb_substr($val['word'], 0, 110) . '... ' . $val['referer'];
		}
	}
}

echo date('Ymd-His') . ' : ' . $message . "\n";

// Update tweet count
$sql = "UPDATE nukotan_word SET tweet_count = (tweet_count + 1) WHERE id = :pickupId";
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($mode == "run") $sth->execute(array(':pickupId' => $pickupId));

// Send tweet
$to = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
if ($mode == "run") $req = $to->OAuthRequest($twitterURL, 'POST', array('status' => $message));


?>
