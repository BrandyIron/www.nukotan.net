<?php
require_once(dirname(__FILE__) . '/../include/twitteroauth/twitteroauth.php');
require_once(dirname(__FILE__) . '/../include/tmhOAuth/tmhOAuth.php');
require_once(dirname(__FILE__) . '/../../data/twitter_key.php');
require_once(dirname(__FILE__) . '/../include/nukotanDbh2.php');

$dbh = getPDO();
$res = array();

date_default_timezone_set('Asia/Tokyo');


$twitterURL = 'https://api.twitter.com/1.1/statuses/update.json';

$mode = $argv[1];

// Get lowest number tweet message from nukotan_word
$candidateArr = array();
$sql = "SELECT id, word, tweet_count, referer FROM nukotan_word WHERE tweet_count = (SELECT MIN(tweet_count) FROM (SELECT tweet_count FROM nukotan_word UNION SELECT tweet_count FROM instagram_rss) AS mix)";
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute();
$res['nukotan_word'] = $sth->fetchAll();
foreach ($res['nukotan_word'] as $val) {
	array_push($candidateArr, 'nukotan_word_' . $val['id']);
}

// Get lowest number tweet message from instagram_rss
$sql = "SELECT id, title, link, image_path FROM instagram_rss WHERE tweet_count = (SELECT MIN(tweet_count)  FROM (SELECT tweet_count FROM nukotan_word UNION SELECT tweet_count FROM instagram_rss) AS mix)";
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute();
$res['instagram_rss'] = $sth->fetchAll();
foreach ($res['instagram_rss'] as $val) {
	array_push($candidateArr, 'instagram_rss_' . $val['id']);
}


// Select tweet article from nukotan_word and instagram_rss
$pickupVal = $candidateArr[rand(0, count($candidateArr))];

if (preg_match('/nukotan_word/', $pickupVal)) {
	// In case that select tweet article from nukotan_word
	$pickupId = preg_replace('/nukotan_word_/', '', $pickupVal);
	foreach ($res['nukotan_word'] as $val) {
		if ($val['id'] == $pickupId) {
			if (mb_strlen($val['word']) < 110) {
				$message = $val['word'] . ' ' . $val['referer'];
			} else {
				$message = mb_substr($val['word'], 0, 110) . '... ' . $val['referer'];
			}
			// Update tweet count
			$sql = "UPDATE nukotan_word SET tweet_count = (tweet_count + 1) WHERE id = :pickupId";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			if ($mode == "run") $sth->execute(array(':pickupId' => $pickupId));

			// Send tweet
			$to = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
			if ($mode == "run") $req = $to->OAuthRequest($twitterURL, 'POST', array('status' => $message));
		}
	}
} else {
	// In case that select tweet article from instagram_rss
	$pickupId = preg_replace('/instagram_rss_/', '', $pickupVal);
	foreach ($res['instagram_rss'] as $val) {
		if ($val['id'] == $pickupId) {
			if (mb_strlen($val['title']) < 110) {
				$message = $val['title'] . ' ' . $val['link'];
			} else {
				$message = mb_substr($val['title'], 0, 110) . '... ' . $val['link'];
			}
			// Update tweet count
			$sql = "UPDATE instagram_rss SET tweet_count = (tweet_count + 1) WHERE id = :pickupId";
			$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			if ($mode == "run") $sth->execute(array(':pickupId' => $pickupId));

			// Send tweet with image
			$tmhOAuth = new tmhOAuth(array(
				'consumer_key' => $consumerKey,
				'consumer_secret' => $consumerSecret,
				'user_token' => $accessToken,
				'user_secret' => $accessTokenSecret,
				'curl_ssl_verifypeer' => false
			));
			if ($mode == "run") {
				$image_binary = file_get_contents($val['image_path']);
				
				$res = $tmhOAuth->request('POST',
					$tmhOAuth->url('1.1/statuses/update_with_media'),
					array(
						'media[]' => "{$image_binary};type=image/jpeg;filename=nukotan_instagram",
						'status' => $message
					),
					true,
					true
				);

				print_r($tmhOAuth->response);

				if ($res == 200) {
					print_r(json_decode($tmhOAuth->response['response']));
				} else {
					print_r($tmhOAuth->response);
				}
			}
		}
	}
}

echo date('Ymd-His') . ' : ' . $message . "\n";

?>
