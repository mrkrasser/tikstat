<?php
/*
	Инициализируем соеденение с базой, создаем таблички если их нет
*/

/*
 @TODO: rewrite on PDO
 @TODO: $DBH = new PDO("sqlite:my/database/path/database.db"); 
 @TODO: 
 @TODO: под одну запись on duplicate key update
 @TODO: , PRIMARY KEY (id), UNIQUE (device_id, datetime) ON CONFLICT IGNORE


mikrotik script
=================
:local sysnumber [/system routerboard get value-name=serial-number]
:local txbyte [/interface ethernet get ether1-gateway value-name=driver-tx-byte]
:local rxbyte [/interface ethernet get ether1-gateway value-name=driver-rx-byte]
/tool fetch url=("http://example.com/tikstat/collector.php\?sn=$sysnumber&tx=$txbyte&rx=$rxbyte") mode=http keep-result=no
=================
*/

if( $db = new SQLiteDatabase('tikstat.sqlite.db') ) {
	// base for devices
	$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='devices'");
    if( $result->numRows() == 0 ) { 
		$db->queryExec('CREATE TABLE devices (id INTEGER PRIMARY KEY, sn text, comment text, last_check int, last_tx int, last_rx int)');
	}
	// base for detalized traffic
	$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='traffic'");
    if( $result->numRows() == 0 ) { 
		$db->queryExec('CREATE TABLE traffic (id INTEGER PRIMARY KEY, device_id int, datetime int, tx int, rx int)');
		$db->queryExec('CREATE UNIQUE INDEX IF NOT EXISTS UniqueIndex ON traffic (device_id,datetime)');
	}
	// base for summary traffic
} else {
	echo "Ошибка работы с БД.";
	exit;
}

