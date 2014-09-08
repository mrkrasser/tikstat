<?php
/*
	Отображаем список оборудование и рисуем простой график детализации
*/

require("init.php");

if (isset($_GET[id]) and is_numeric($_GET[id])) {

$result = $db->arrayQuery("SELECT sn,comment FROM devices WHERE id='".$_GET[id]."'", SQLITE_ASSOC);

    echo 's/n: <strong>'.$result[0][sn].'</strong> ('.$result[0][comment].')<br/>';
	$jsdata = '';
	$traffQ = $db->arrayQuery("SELECT datetime,tx,rx FROM traffic WHERE device_id='".$_GET[id]."' ORDER BY datetime ASC LIMIT 24", SQLITE_ASSOC);
	foreach ($traffQ as $traffEntry) {
		$jsdata .= '["'.date("d M y H:i:s", $traffEntry[datetime]).'",'.round($traffEntry[tx]/1024/1024,2).','.round($traffEntry[rx]/1024/1024,2).'],';
	}
?>	
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart);
		function drawChart() {
			var data = google.visualization.arrayToDataTable([ 
				['Hour', 'TX', 'RX'],
				<?php echo $jsdata; ?> 
			]);
			
			var options = {
				title: 'Сегодня',
				hAxis: {title: 'Часы', titleTextStyle: {color: 'red'}}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
			chart.draw(data, options);
		}
	</script>
	<div id="chart_div" style="width: 900px; height: 500px;"></div>

<?php
	//Сегодня 
	$today_result = $db->arrayQuery("SELECT sum(tx) as sumtx, sum(rx) as sumrx FROM traffic WHERE device_id='".$_GET[id]."' AND datetime>='".mktime(0,0,0)."' AND datetime<'".mktime(23,59,59)."'", SQLITE_ASSOC);
	//, sum(rx) as sumrx
	echo 'Сегодня: ';
	echo 'TX '.round(($today_result[0][sumtx]/1024/1024),2).' ';
	echo 'RX '.round(($today_result[0][sumrx]/1024/1024),2).' ';
	echo 'Total '.round((($today_result[0][sumtx]+$today_result[0][sumrx])/1024/1024),2).'Mb<br>';
	//Эта неделя
	$week_result = $db->arrayQuery("SELECT sum(tx) as sumtx, sum(rx) as sumrx FROM traffic WHERE device_id='".$_GET[id]."' AND datetime>='".strtotime(date('Y').'W'.date('W').'1')."' AND datetime<'".strtotime(date('Y').'W'.date('W').'7')."'", SQLITE_ASSOC);
	echo 'Эта неделя: ';
	echo 'TX '.round(($week_result[0][sumtx]/1024/1024),2).' ';
	echo 'RX '.round(($week_result[0][sumrx]/1024/1024),2).' ';
	echo 'Total '.round((($week_result[0][sumtx]+$week_result[0][sumrx])/1024/1024),2).'Mb<br>';
	//Этот месяц
	$month_result = $db->arrayQuery("SELECT sum(tx) as sumtx, sum(rx) as sumrx FROM traffic WHERE device_id='".$_GET[id]."' AND datetime>='".strtotime(date('Y-m-1'))."' AND datetime<'".strtotime(date('Y-m-t'))."'", SQLITE_ASSOC);
	echo 'Этот месяц: ';
	echo 'TX '.round(($month_result[0][sumtx]/1024/1024),2).' ';
	echo 'RX '.round(($month_result[0][sumrx]/1024/1024),2).' ';
	echo 'Total '.round((($month_result[0][sumtx]+$month_result[0][sumrx])/1024/1024),2).'Mb<br>';
} else {
	$result = $db->arrayQuery('SELECT id,sn,comment FROM devices', SQLITE_ASSOC);
	foreach ($result as $entry) { 
		echo '<a href="?id='.$entry[id].'"><strong>'.$entry[sn].'</strong></a> ('.$entry[comment].')<br/>';
	}
}