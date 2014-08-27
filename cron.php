<?php
/*
	Запускаем по крону каждое воскресенье для рассылки статистики по трафику на зашитые в коде адреса
*/

if (php_sapi_name() != "cli") {
	echo 'not run from web!';
	exit;
} 

require("init.php");
require("PHPMailer/PHPMailerAutoload.php");

$week_result = $db->arrayQuery("SELECT sum(tx) as sumtx, sum(rx) as sumrx FROM traffic WHERE device_id='1' AND datetime>'".strtotime(date('Y').'W'.date('W').'1')."' AND datetime<'".strtotime(date('Y').'W'.date('W').'7')."'", SQLITE_ASSOC);
$msg = 'Эта неделя: ';
$msg .= 'TX '.round(($week_result[0][sumtx]/1024/1024),2).' ';
$msg .= 'RX '.round(($week_result[0][sumrx]/1024/1024),2).' ';
$msg .= 'Total '.round((($week_result[0][sumtx]+$week_result[0][sumrx])/1024/1024),2).'Mb<br>';
$month_result = $db->arrayQuery("SELECT sum(tx) as sumtx, sum(rx) as sumrx FROM traffic WHERE device_id='1' AND datetime>'".strtotime(date('Y-m-1'))."' AND datetime<'".strtotime(date('Y-m-t'))."'", SQLITE_ASSOC);
$msg .= 'Этот месяц: ';
$msg .= 'TX '.round(($month_result[0][sumtx]/1024/1024),2).' ';
$msg .= 'RX '.round(($month_result[0][sumrx]/1024/1024),2).' ';
$msg .= 'Total '.round((($month_result[0][sumtx]+$month_result[0][sumrx])/1024/1024),2).'Mb<br>';
$msg .= 'Всего израсходовано: '.round((($month_result[0][sumtx]+$month_result[0][sumrx])/1024/1024),2).'Mb<br>';
$msg .= '<a href="http://example.com/tikstat/?id=1">Подробнее</a>';

//Create a new PHPMailer instance
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPSecure = "tls";
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = '';
$mail->Password = '';
$mail->CharSet = "UTF-8";
$mail->setFrom('', 'статистика');
$mail->addAddress('', '');
$mail->Subject = 'Статистика ('.date("d-m-Y").')';
$mail->isHTML(true);
$mail->Body = $msg; 

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error:<br>" . $mail->ErrorInfo ."";
} else {
    echo "Message sent!";
}
