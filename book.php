<html>
<title>Book</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-CN" /> 

<script type="text/javascript" src="inpage_edit.js"></script>
<!--
-<link rel="stylesheet" type="text/css" href="report.css" media="screen12"/>
	A php that could manage book library 
	by Ling Xiaofeng <lingxf@gmail.com>
-->
<style type="text/css">
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
<body onload="show_filter()">
<?php
include 'book_lib.php';
/*
	copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
*/

include 'db_connect.php';
include 'debug.php';

global $login_id;	
global $show_techarea_case;

session_start();

$sid=session_id();
$login_id = "NoLogin";
if(isset($_POST['login'])){
	if(isset($_POST['user'])){
	    $login_id=$_POST['user'];
	    if(isset($_POST['password'])) $password=$_POST['password'];
	    $ret = check_passwd($login_id, $password);
	    if($ret == 1){
	        print("No user $login_id exist");
	        unset($_SESSION['user']); 
	    }else if($ret == 2){
	        print("wrong password");
	        unset($_SESSION['user']);
	    }else
	        $_SESSION['user'] = $login_id;
	}
}else if(isset($_POST['register'])){
    header("Location: book_user_register.php");
    exit;
}


if(isset($_SESSION['user'])) $login_id=$_SESSION['user'];
else{
//    header("Location: book_user_login.php");
//  exit;
}
$max_books = 1;
$role = is_member($login_id);
if($role == 2)
	$role_text = "Admin";
else if($role == 1)
	$role_text = "Member";
else
	$role_text = "Non-member";

if($login_id == 'NoLogin')
	$login_text = "<a href=book_user_login.php>Login<a/>";
else
	$login_text = "<a href=book_user_setting.php>$login_id($role_text)<a/> &nbsp;&nbsp;<a href=\"book.php?action=logout\">Logout</a>";


$action="init";
if(isset($_GET['action']))$action=$_GET['action'];
if($action == "logout"){
	$_SESSION = array();
	session_destroy();
	print "You are logout now";
	sleep(5);
    header("Location: book_user_login.php");
	exit;
}
$book_id=0;
if(isset($_GET['book_id']))$book_id=$_GET['book_id'];
if(isset($_GET['record_id']))$record_id=$_GET['record_id'];


print "<a href=\"book.php\">Home</a> &nbsp;&nbsp;$login_text ";
if($role == 2){
	print "&nbsp;&nbsp;<a href=\"book.php?action=manage\">Manage</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_out\">Lent</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=history\">History</a>";
}

print("<br>");
dprint("Action:$action Login:$login_id book_id:$book_id<br>");

if(!isset($_SESSION['item_perpage'])) $_SESSION['item_perpage'] = 10;
if(!isset($_SESSION['start'])) $_SESSION['start'] = 1;
if(!isset($_SESSION['end'])) $_SESSION['end'] = $_SESSION['start'] + $_SESSION['item_perpage'] - 1;

$item_perpage = $_SESSION['item_perpage'];
$start = $_SESSION['start'];
$end = $_SESSION['end'];
$set_standard_answer = false;

if(isset($_POST['submit'])) $action="submit";
if(isset($_POST['prev'])) $action="prev";
if(isset($_POST['next']))$action="next";
if(isset($_POST['begin'])) $action="begin";
if(isset($_POST['end']))$action="end";
if(isset($_POST['list_all']))$action="list_all";

if($role != 2 && preg_match("/manager|approve|history|stock|push|list_out|lend|reject_wait/",$action)){
	print("You are not administrator!");
	return;
}

switch($action){
	case "init":
		print("<div>我的借阅");
		list_record($login_id);
		print("</div>");
		print("<div>书库列表");
		list_book();
		print("</div>");
		break;
	case "borrow":
		borrow_book($book_id, $login_id);
		list_record($login_id);
		//show_home();
		break;
	case "cancel":
		set_record_status($record_id, 0x100);
		list_record($login_id);
		break;
	case "returning":
		set_record_status($record_id, 3);
		list_record($login_id);
		break;
	case "wait":
		if(wait_book($book_id, $login_id)){
			$bookname = get_bookname($book_id);
			$to = get_user_attr($borrower, 'email');
			//$cc = 'xling@qti.qualcomm.com';
			mail_html($to, $cc, "$login_id is waiting for your book <$bookname>", "");
		}
		home_link();
		break;
	case "show_borrower":
		print("当前借阅人<br>");
		show_borrower($book_id, 'out');
		print("介绍<br>");
		show_book($book_id);
		print("等待列表<br>");
		show_borrower($book_id, 'wait');
		print("历史借阅记录<br>");
		show_borrower($book_id, 'borrow');
		break;

	/*admin*/
	case "migrate":
		migrate_record($login_id);
		break;
	case "manage":
		manage_record($login_id);
		break;
	case "list_out":
		out_record($login_id);
		break;
	case "push":
		$book_id = get_bookid_by_record($record_id);
		$borrower = get_borrower($book_id);
		$bookname = get_bookname($book_id);
		$to = get_user_attr($borrower, 'email');
		$cc = get_admin_mail();
		mail_html($to, $cc, "Timeout, Please return the book <$bookname>", "");
		home_link("Back", 'manage');
		break;
	case "lend":
		$book_id = get_bookid_by_record($record_id);
		$borrower = get_borrower($book_id);
		$bookname = get_bookname($book_id);
		$to = get_user_attr($borrower, 'email');
		$user = get_user_attr($borrower, 'name');
		$cc = get_admin_mail();
		set_record_status($record_id, 2);
		mail_html($to, $cc, "<$bookname> is lent to <$user>", "");
		manage_record($login_id);
		break;
	case "stock":
		$book_id = get_bookid_by_record($record_id);
		$borrower = get_borrower($book_id);
		$bookname = get_bookname($book_id);
		$to = get_user_attr($borrower, 'email');
		$user = get_user_attr($borrower, 'name');
		$cc = get_admin_mail();
		mail_html($to, $cc, "<$bookname> is returned by <$user>", "");
		set_record_status($record_id, 0);
		manage_record($login_id);
		break;
	case "reject":
		$book_id = get_bookid_by_record($record_id);
		$borrower = get_borrower($book_id);
		$bookname = get_bookname($book_id);
		$to = get_user_attr($borrower, 'email');
		$user = get_user_attr($borrower, 'name');
		$cc = get_admin_mail();
		mail_html($to, $cc, "You apply to <$bookname> is rejected", "");
		set_record_status($record_id, 0x100);
		manage_record($login_id);
		break;
	case "reject_wait":
		set_record_status($record_id, 0x101);
		manage_record($login_id);
		break;
	case "history":
		list_record('all', 'history');
		break;

}

function show_home()
{
	global $login_id;
	print("<div>My Borrow");
	list_record($login_id);
	print("</div>");
	print("<div>All Book");
	list_book();
	print("</div>");
}

?>

</body>
</html>
