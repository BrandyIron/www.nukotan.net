<?php
//error_reporting(-1);

$dsn = 'mysql:host=localhost;dbname=nukotan_live';
$username = 'nukotan';
$password = '0716';
$options = array(
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

$dbh = new PDO($dsn, $username, $password, $options);

print <<< DOC_END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>ぬこたん☆公演まとめβ (*´Д`)ﾊｧﾊｧ</title>
<link type="text/css" rel="stylesheet" href="css/base.css" media="all" />

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-darkness/jquery-ui.css" rel="stylesheet" />



<script type="text/javascript">
$(function(){
	$("#startdate").datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect: function( selectedDate ) {
						$( "#enddate" ).datepicker( "option", "minDate", selectedDate );
					}
	});
	$("#startdate").datepicker("option", "dateFormat", "yy-mm-dd");
});
$(function(){
	$("#enddate").datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect: function( selectedDate ) {
						$( "#startdate" ).datepicker( "option", "maxDate", selectedDate );
					}
	});
	$("#enddate").datepicker("option", "dateFormat", "yy-mm-dd");	
});
</script>

</head>

<body>
<div id="columns">
<h1 class="page-title">ぬこたん☆公演まとめβ (*´Д`)ﾊｧﾊｧ</h1>
DOC_END;


$sql = 'SELECT title, place, date FROM nukotan_live ORDER BY date DESC LIMIT 1';
$row = $dbh->query($sql)->fetch();
	
print <<< DOC_END
<div id="playingnumber">
<h2 class="title">演奏回数リスト(*´Д`)ﾊｧﾊｧ</h2>
DOC_END;

echo "<p>最新公演 : $row[title]@$row[place] ($row[date])</p>";
echo "<form action=" . $_SERVER['SCRIPT_NAME'] . " method='POST'>";

print <<< DOC_END
<div class="pre">
<ul>
DOC_END;

echo "	<li>[期間] START : <input type=\"text\" id=\"startdate\" name=\"startdate\"> / END : <input type=\"text\" id=\"enddate\" name=\"enddate\"></li>";
print <<< DOC_END
	<li>[ツアー] <select name="tour">
		<option value="">全部(*´Д`)ﾊｧﾊｧ</option>
DOC_END;

	$sql = "SELECT DISTINCT title FROM nukotan_live ORDER BY date DESC";
	foreach ($dbh->query($sql) as $row) {
		if ($row[title] == $_POST[tour]) {
			print "<option value=\"$row[title]\" selected>$row[title]</option>\n";
		} else {
			print "<option value=\"$row[title]\">$row[title]</option>\n";
		}
	}

$checked = "";
if ($_POST[nukotan][0] == "nukotan") $checked = "checked";
echo "</select>
	<li><input type=\"checkbox\" name=\"nukotan[]\" value=\"nukotan\" $checked>ぬこたん(*´Д`)ﾊｧﾊｧ</li>";

print <<< DOC_END
</ul>
<input type="submit" value="(ﾟДﾟ)">
</form>
</div>
DOC_END;

	$conds = array();
	$cond_text = "";
	// Check date condition
	if (preg_match('/\d{4}-\d{2}-\d{2}/', $_POST["startdate"]) && preg_match('/\d{4}-\d{2}-\d{2}/', $_POST["enddate"])) {
		array_push($conds, "date >= '" . $_POST["startdate"] . "' AND date <= '" . $_POST["enddate"] . "'");
		$cond_text .= "<li>期間 : $_POST[startdate] ~ $_POST[enddate]</li>";
	}
	// Check place condition
	if ($_POST["tour"]) {
		$place = $_POST["tour"];
		array_push($conds, "title = '$place'");
		$cond_text .= "<li>$place ツアー</li>";
	}
	// Check nukotan condition
	if ($_POST["nukotan"]) {
		$nukotan = $_POST["nukotan"][0];
		if ($nukotan == "nukotan") {
			$sql = "SELECT song FROM nukotan_song";
			$nukotan_songs = array();
			foreach ($dbh->query($sql) as $row) {
				array_push($nukotan_songs, "song = '" . $row[song] . "'");
			}
			array_push($conds, "(" . implode(' OR ', $nukotan_songs) . ")");
		}
		$cond_text .= "<li>ぬこたんが作詞・作曲に携わったナンバー(*´Д`)ﾊｧﾊｧ</li>";
	}
	if (count($conds) != 0) {
		$condition = "WHERE " . implode(' AND ', $conds);
		echo "<div class='pre'><ul>";
		echo $cond_text;
		echo "</ul><p>から絞込み</p></div>";
	}
	
	$sql = "SELECT song, count(song) as num FROM nukotan_live $condition GROUP BY song ORDER BY num DESC";

print <<< DOC_END
	<table id="nukotanTable" class="tablesorter">
	<thead>
		<tr>
			<th>曲名</th><th>回数</th>
		</tr>
	</thead>
DOC_END;

	foreach ($dbh->query($sql) as $row) {
		print "<tr>
			<td>$row[song]</td>
			<td>$row[num]</td>
		</tr>";
	}

print <<< DOC_END
</table>
<!-- end of #playingnumber -->
</div>

<!-- end of #columns -->
</div>
</body>
</html>
DOC_END;
?>