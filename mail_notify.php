<?php
include_once 'debug.php';
include_once 'db_connect.php';
include_once 'myphp/disp_lib.php';
include_once 'myphp/common.php';
include 'book_lib.php';

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
else if($action == 'reply')
	notify_reply_comment(1);
else if($action == 'gen_comment')
	gen_comment();

function gen_comment()
{
	list_comments('', '', 0, 7);
}

function notify_reply_comment($last_days='')
{
	$mail_url = get_cur_php();
	if($mail_url == '')
		$mail_url = "http://cedump-sh.ap.qualcomm.com/book/book.php";

	$cond = "1 ";
	if($last_days != '')
		$cond .= " and `timestamp` > date_sub(now(), interval $last_days day)";
	$cond .= " and comments.parent != 0 ";

	$tc = "(select comment_id, words, borrower from comments)";
	$sql = "select comments.comment_id, comments.borrower, parent, cm.words, date(timestamp) as dt, comments.book_id, books.name, cm.borrower as parent_user from comments left join $tc as cm on comments.parent = cm.comment_id left join books on comments.book_id = books.book_id where $cond order by timestamp desc limit 100";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$comment_id = $row['comment_id'];
		$comment = $row['words'];
		$parent_user = $row['parent_user'];
		$borrower = $row['borrower'];
		$book_id = $row['book_id'];
		$book_name = $row['name'];
		$mail_url .= "?action=show_borrower&book_id=$book_id";
		mail_reply_comment($parent_user, $comment, $borrower, $book_name, $mail_url);
	}
}

function mail_reply_comment($parent, $comment, $borrower, $book_name, $url)
{
	$message = "
	<html>
	<head>
	  <title>Reply Comments </title>
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
	$subject = "$borrower 回复了你对<$book_name>的评论";
	$message .= "$subject<br>\r\n";
	$message .= "$comment<br>\r\n";
	$message .= "点此<a href=$url>$book_name</a>查看";
	$message .= " </body> </html> ";
	$to = get_user_email($parent);
//	$to = 'xling@qti.qualcomm.com';
	$cc = '';
	mail_html($to, $cc, $subject, $message);
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
//	$message = base64_encode($message);
	$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
	mail_html($to, $cc, $subject, $message);
}

function mail_tbd_list(){
	$reason = "";
	$time = time();
	$date = strftime("%Y-%m-%d %H:%M:%S", $time);
	$subject = "Book Library Action List $date";
	global $city;
	if($city == 2)
		$url = "http://cedump-sh.ap.qualcomm.com/szbook/book.php?action=manage";
	else
		$url = "http://cedump-sh.ap.qualcomm.com/book/book.php?action=manage";

	$message = "
	<html>
	<head>
	  <title>Action List</title>
	</head>
	<body>
	<table style='font-size:14pt; color:#800000;'>
	<tr><th style='text-align: left;'>Date:</th><td >$date</td></tr>
	<tr><th style='text-align: left;'>Link:</th><td><a href='$url'>Manage</a></td></tr>
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
		$cc = get_admin_mail();
		if($remain_days > 0)
			mail_html($to, $cc, "your book <$bookname> has $remain_days days left", "$borrower $bookname $bdate");
		else
			mail_html($to, $cc, "your book <$bookname> has timeout, please return", "$borrower $bookname $bdate");
	}
}

?>
