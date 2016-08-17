<?php
include 'book_lib.php';
include 'debug.php';
include 'db_connect.php';

if($argc <= 1){
	print(" mail|gen \n");
	return;
}

$action = $argv[1];

if($action == 'mail')
	mail_tbd_list();
else if($action == 'gen')
	list_record('', 'approve');

function mail_tbd_list(){
	$reason = "";
	$time = time();
	$date = strftime("%Y-%m-%d %H:%M:%S", $time);
	$subject = "Book Library Action List $date";
	$message = "
	<html>
	<head>
	  <title>Action List</title>
	</head>
	<body>
	<table style='font-size:14pt; color:#800000;'>
	<tr><th style='text-align: left;'>Date:</th><td >$date</td></tr>
	<tr><th style='text-align: left;'>Link:</th><td><a href='http://cedump-sh.ap.qualcomm.com/book/?action=manage'>Manage</a></td></tr>
	</table>";

	exec("php mail_notify.php mail", $output);
	foreach($output as $line){
		$message .= $line . "\n"; 
	}
	$message .= " </body> </html> ";

	$to = get_admin_mail();
	$cc = '';
	mail_html($to, $cc, $subject, $message);
}


?>
