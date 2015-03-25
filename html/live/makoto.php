<?php
require_once(dirname(__FILE__) . '/../../lib/include/header.php');
require_once(dirname(__FILE__) . '/../../lib/include/footer.php');
require_once(dirname(__FILE__) . '/../../lib/include/nukotanDbh2.php');

$dbh = getPDO();

require_once(dirname(__FILE__) . '/../../lib/nukotan_live/calc_live_rate_of_adoption.php');

publishHeader("MA KO TO(*´Д`)ﾊｧﾊｧ");

echo <<<EOD
<script>
$(document).ready(function(){
	$('.performance_list').dataTable({
		'order': [[1, 'desc']],
		'iDisplayLength': 100,
		'bStateSave': true
	});
});
</script>
EOD;

echo <<<EOD
<div class="container">
	<div class="page-header">
        	<h1>MA KO TO(*´Д`)ﾊｧﾊｧ</h1>
		<ul>
		<li>MAKOTO氏がまだ演奏したことのない曲目(*´Д`)ﾊｧﾊｧ</li>
                <li>ぬこたん公式庵頁のテキストが元データなのでそっちが間違っているとこっちも間違っています(*´Д`)ﾊｧﾊｧ</li>
                <li>MAKOTO氏が全曲ドラミング制覇する日が来るのを夢見て...(*´Д`)ﾊｧﾊｧ</li>
        </div>
EOD;

$sql = "SELECT id, song, COUNT(song) as num, MAX(date) as last_date FROM nukotan_live AS org WHERE NOT EXISTS (SELECT song FROM nukotan_live WHERE org.song = song AND date >= '2012-03-20' GROUP BY song) group by song;";

echo <<<EOD
<div class="table-responsible">
	<table class="table table-striped table-hover performance_list">
	<thead>
		<tr>
			<th>Song Title</th><th>Performance Count</th><th>Last Preformance Date</th>
		</tr>
	</thead>
EOD;

foreach ($dbh->query($sql) as $row) {
	$rate_of_adoption = get_rate_of_adoption($row['song']);
	echo "<tr>
		<td>{$row['song']}<a href='./detail.php?id={$row['id']}' >[♪]</a></td>
		<td>{$row['num']}</td>
		<td>{$row['last_date']}</td>
		</tr>";
}




echo <<<EOD
</table>
</div>

</div>
EOD;

publishFooter();
