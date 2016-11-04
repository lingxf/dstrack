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
else if($action == 'gen'){
	$login_id = 'xling';
	manage_record();
}
else if($action == 'check')
	check_timeout();
else if($action == 'comment')
	mail_new_comment();
else if($action == 'gen_comment')
	gen_comment();

function gen_comment()
{
	list_comments('', '', 0, 7);
}

function mail_new_comment()
{
	$message = "
	<html>
	<head>
	  <title>This Weeks Comments </title>
	</head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	<meta http-equiv=\"Content-Language\" content=\"zh-CN\" /> 
	<style type=\"text/css\">
	@media screen {
		.print_ignore {
	display: none;
		}
		body, table, th, td {
			font-size:         12pt;
		}
		table, th, td {
			border-width:      1px;
			border-color:      #0000f0;
			border-style:      solid;
		}
		th, td {
	padding:           0.2em;
		}
	}
	</style>

	<body>
	<table style='font-size:14pt; color:#800000;'>
	<tr><th style='text-align: left;'>Link:</th><td><a href='http://cedump-sh.ap.qualcomm.com/book/book.php?action=list_comments_all'>最新评论</a></td></tr>
	</table>";
	$subject = "本周评论";
	exec("php mail_notify.php gen_comment", $output);
	foreach($output as $line){
		$message .= $line . "\n"; 
	}
	$message .= " </body> </html> ";
	$to = 'QClub.BJ.Reading@qti.qualcomm.com';
//	$to = 'xling@qti.qualcomm.com';
	$cc = '';
	mail_html($to, $cc, $subject, $message);
}

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
	<tr><th style='text-align: left;'>Link:</th><td><a href='http://cedump-sh.ap.qualcomm.com/book/book.php?action=manage'>Manage</a></td></tr>
	</table>";

	exec("php mail_notify.php gen", $output);
	foreach($output as $line){
		$message .= $line . "\n"; 
	}
	$message .= " </body> </html> ";

	$to = get_admin_mail();
	$cc = '';
	mail_html($to, $cc, $subject, $message);
}

function check_timeout()
{
	mail_reminder_day(7);
	mail_reminder_day(3);
	mail_reminder_day(1);
	mail_reminder_day(0);
}

function mail_reminder_day($remain_days)
{

	$time = time();
	$lend_days = 28 - $remain_days;
	if($remain_days > 0)
		$cond = "(to_days(now())  - to_days(bdate)) = $lend_days";
	else
		$cond = "(to_days(now())  - to_days(bdate)) >= 28";

	$sql = " select record_id, borrower, t1.status, name, email, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where (t1.status = 2 or t1.status = 3 or t1.status = 5) and $cond and  t1.book_id = t2.book_id and t3.user = t1.borrower order by bdate asc ";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$i = 0;
	while($row=mysql_fetch_array($res)){
		$record_id = $row['record_id']; 
		$borrower = $row['user_name']; 
		$book_id = $row['book_id']; 
		$name = $row['name']; 
		$email = $row['email']; 
		$adate= $row['adate']; 
		$bdate= $row['bdate']; 
		$rdate= $row['rdate']; 
		$sdate= $row['sdate']; 
		$status = $row['status'];
		$status_text = "";
		$blink = "";
		$i++;
		$bookname = $name; 
		$to = $email;
		$cc = 'yingwang@qti.qualcomm.com';
		if($remain_days > 0)
			mail_html($to, $cc, "your book <$bookname> has $remain_days days left", "$borrower $bookname $bdate");
		else
			mail_html($to, $cc, "your book <$bookname> has timeout, please return", "$borrower $bookname $bdate");
	}
}

?>
