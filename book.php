<html>
<title>Book</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-CN" /> 
<!--
<link rel="stylesheet" type="text/css" href="report.css" media="screen12"/>
	A php that could do a quiz test based on a database
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
	weekly report and manual tracking system
	copyright Xiaofeng(Daniel) Ling<xling@qualcomm.com>, 2012, Aug.
*/


$link=mysql_connect("10.233.140.115:3306","weekly","week2pass");
#$link=mysql_connect("localhost","exam","");
mysql_query("set character set 'utf8'");//..
mysql_query("set names 'utf8'");//.. 
$db=mysql_select_db("book",$link);

global $login_id;	
global $show_techarea_case;
$debug=1;

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

if($role == 0)
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

if(isset($_GET['book_id']))$book_id=$_GET['book_id'];
if(isset($_GET['record_id']))$record_id=$_GET['record_id'];


print "<a href=\"book.php\">Home</a> &nbsp;&nbsp;$login_text ";
if($role == 2){
	print "&nbsp;&nbsp;<a href=\"book.php?action=manager\">Manager</a>";
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

switch($action){
	case "init":
		print("<div>My Borrow");
		list_record($login_id);
		print("</div>");
		print("<div>All Book");
		list_book($login_id, $format);
		print("</div>");
		break;
	case "manager":
		if($role == 2)
			manage_record($login_id);
		else
			print("You are not administrator!");
		break;
	case "history":
		if($role == 2)
			list_record('all');
		break;
	case "borrow":
		borrow_book($book_id, $login_id);
		list_record($login_id);
		//show_home();
		break;
	case "approve":
		set_book_status($record_id, 2);
		list_record($login_id);
		break;
	case "returning":
		set_book_status($record_id, 3);
		list_record($login_id);
		break;
	case "stock":
		set_book_status($record_id, 0);
		list_record($login_id);
		break;
	case "wait":
		wait_book($book_id);
		break;
	case "submit":
        	save_answer($start, $end);
		show_score($login_id);
		list_book($login_id);
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
