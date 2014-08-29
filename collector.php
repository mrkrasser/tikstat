<?php
/*
	Получаем данные со счетчиками интерфейса с микротика и сохраняем их в БД
	/collector.php?sn=<SERIAL NUMBER>&tx=<INTERFACE OUR BYTES>&rx=<INTERFACE IN BYTES>
*/


require("init.php");

// Check input data
if (isset($_GET[sn]) 
	and isset($_GET[tx]) and is_numeric($_GET[tx])
	and isset($_GET[rx]) and is_numeric($_GET[rx])) {
	$device_serial = substr($_GET[sn], 0, 12);
} else {
	echo 'fail';
	exit;
}

// Create new devices if not exist
$device = $db->arrayQuery("SELECT id ,last_tx, last_rx FROM devices WHERE sn='".$device_serial."'",SQLITE_ASSOC);
if (!is_numeric($device[0][id])) { 
	$db->query("INSERT INTO devices(sn, last_check, last_tx, last_rx) VALUES ('".$device_serial."', '".time()."', '".$_GET[tx]."', '".$_GET[rx]."')");
	$device[0][id] = $db->lastInsertRowid();
	$txBytes = $_GET[tx];
	$rxBytes = $_GET[rx];
} else {
	// update last receiving data
	$db->query("UPDATE devices SET last_check='".time()."', last_tx='".$_GET[tx]."', last_rx='".$_GET[rx]."' WHERE id='".$device[0][id]."'");
	// check last received value
	// Если получили по счетчикам меньше трафика чем было, значит счетчик был сброшен.
	if ($device[0][last_tx] > $_GET[tx]) {
		$txBytes = $_GET[tx];
	} else {
		$txBytes = $_GET[tx]-$device[0][last_tx];
	}
	if ($device[0][last_rx] > $_GET[rx]) {
		$rxBytes = $_GET[rx];
	} else {
		$rxBytes = $_GET[rx]-$device[0][last_rx];
	}
}

$entry = $db->arrayQuery("SELECT * FROM traffic WHERE device_id='".$device[0][id]."' AND datetime = '".mktime(date('H'),0,0)."' LIMIT 1", SQLITE_ASSOC);
if (is_numeric($entry[0][id])) {
	$db->queryExec("UPDATE traffic SET tx='".($entry[0][tx]+$txBytes)."', rx='".($entry[0][rx]+$rxBytes)."' WHERE id='".$entry[0][id]."'");
} else {
	$db->queryExec("INSERT INTO traffic(device_id, datetime, tx, rx) VALUES ('".$device[0][id]."', '".mktime(date('H'),0,0)."', '".$txBytes."', '".$rxBytes."')");
}
