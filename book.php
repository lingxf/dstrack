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

include 'debug.php';
include 'db_connect.php';

global $login_id, $max_book, $setting;	

session_name('book');
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
$items_perpage = 50;
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


$action="home";
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
if(isset($_GET['book_id'])) $book_id=$_GET['book_id'];
if(isset($_GET['record_id'])) $record_id=$_GET['record_id'];


print "<a href=\"book.php\">Home</a> &nbsp;&nbsp;$login_text ";
if($role == 2){
	print "&nbsp;&nbsp;<a href=\"book.php?action=manage\">Manage</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_out\">Lent</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=history\">History</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=log\">Log</a>";
}

print("<br>");

if(isset($_GET['items_perpage'])) $items_perpage=$_GET['items_perpage'];
else if(isset($_SESSION['items_perpage'])) $items_perpage = $_SESSION['items_perpage'];
$_SESSION['items_perpage'] = $items_perpage;

if(!isset($_SESSION['start'])) $_SESSION['start'] = 1;
$start = $_SESSION['start'];


if(isset($_POST['prev'])) $action="prev";
if(isset($_POST['next']))$action="next";
if(isset($_POST['begin'])) $action="begin";
if(isset($_POST['end']))$action="end";
if(isset($_POST['list_all']))$action="list_all";

dprint("Action:$action Login:$login_id book_id:$book_id start:$start items:$items_perpage setting:$setting<br>");

if($role != 2 && preg_match("/manager|approve|history|stock|push|list_out|lend|reject_wait/",$action)){
	print("You are not administrator!");
	return;
}

if(isset($_GET['view'])) $view=$_GET['view'];
else if(isset($_SESSION['view'])) $view=$_SESSION['view'];
else $view = $setting & 1 ? 'normal':'brief';
$_SESSION['view'] = $view;
$_SESSION['setting'] = $setting;

switch($action){
	case "home":
		show_home();
		break;
    case "next":
        $start += $items_perpage;
        $_SESSION['start'] = $start;
		show_home();
        break;
    case "begin":
        $start = 1;
        $_SESSION['start'] = $start;
		show_home();
        break;
    case "end":
        $end = get_total_books();
		$start = $end + 1 - $items_perpage;
        $_SESSION['start'] = $start;
		show_home();
        break;
    case "prev":
        $start -= $items_perpage;
        if($start < 1)
            $start = 1;
        $_SESSION['start'] = $start;
		show_home();
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
			$borrower = get_borrower($book_id);
			$to = get_user_attr($borrower, 'email');
			$user = get_user_attr($login_id, 'name');
			mail_html($to, $cc, "$user is waiting for your book <$bookname>", "");
		}
		home_link();
		break;
	case "show_borrower":
		print("介绍 - ");
		show_book($book_id);
		print("当前借阅人<br>");
		show_borrower($book_id, 'out');
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
		add_log($login_id, $borrower, $book_id, 2);
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
		add_log($login_id, $borrower, $book_id, 0);
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
	case "log":
		list_log();
		break;

}

function show_home()
{
	global $login_id, $view, $start, $items_perpage;
	print("<div>我的借阅");
	list_record($login_id);
	print("</div>");
	$view_op = $view == 'brief'?'normal':'brief';
	$view_ch = $view_op == 'brief'?'简略':'完整';
	print("<div>书库列表 <a href='book.php?view=$view_op'>$view_ch</a>");
	print("&nbsp;<a href='book.php?items_perpage=25'>25</a>");
	print("&nbsp;<a href='book.php?items_perpage=50'>50</a>");
	print("&nbsp;<a href='book.php?items_perpage=100'>100</a>");
	print("&nbsp;<a href='book.php?items_perpage=200'>200</a>");
	list_book($view, $start, $items_perpage);
	print("</div>");
}

?>

</body>
</html>
