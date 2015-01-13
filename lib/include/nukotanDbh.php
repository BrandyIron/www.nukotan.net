<?php

function getPDO($env = null) {
	$dsn = 'mysql:host=localhost;dbname=nukotan';
	$username = 'nukotan';
	$password = '0716';
	/*
	$options = array(
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);
	 */

	$dbh = new PDO($dsn, $username, $password, $options);
	return $dbh;
}
?>
