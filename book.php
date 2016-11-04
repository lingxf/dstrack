<?php
session_set_cookie_params(7*24*3600);
session_name('book');
session_start();
?>

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
<body onload="load_intro()">
<script type="text/javascript">
function load_intro(){
	var intr = document.getElementById("div_homeintro");
	if(intr){
		intr.innerHTML="Please wait...";
		url = "brqclub.htm";
		loadXMLDoc(url,function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				intr.innerHTML=xmlhttp.responseText;
			}else{
				if(xmlhttp.status=='0')
				intr.innerHTML="Please wait...";
				else
				intr.innerHTML=xmlhttp.status+xmlhttp.responseText;
				}
			});
	}

}
function change_class(bookclass, view){
	url = "show_book.php?";
	url = url + "class="+bookclass;
	if(view != 0)
		url = url + "&view="+view;

	document.getElementById("div_booklist").innerHTML="Please wait...";
	loadXMLDoc(url,function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
			}else{
			if(xmlhttp.status=='0')
			document.getElementById("div_booklist").innerHTML="Please wait...";
			else
			document.getElementById("div_booklist").innerHTML=xmlhttp.status+xmlhttp.responseText;
			}
	});
};

function change_order(order, view){
	url = "show_book.php?";
	url = url + "order="+order;
	if(view != 0)
		url = url + "&view="+view;

	document.getElementById("div_booklist").innerHTML="Please wait...";
	loadXMLDoc(url,function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
			}else{
				if(xmlhttp.status=='0')
				document.getElementById("div_booklist").innerHTML="Please wait...";
				else
				document.getElementById("div_booklist").innerHTML=xmlhttp.status+xmlhttp.responseText;
			}
	});
};

function book_search(){
	url = "show_book.php?";
	bookname = document.getElementById("id_book_name").value;
	url = url + "book_sname="+bookname;
	document.getElementById("div_booklist").innerHTML="Please wait...";
	loadXMLDoc(url,function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
			}else{
			if(xmlhttp.status=='0')
			document.getElementById("div_booklist").innerHTML="Please wait...";
			else
			document.getElementById("div_booklist").innerHTML=xmlhttp.status+xmlhttp.responseText;
			}
			});
};

function show_share_choice(tdc, book_id)
{
	var result = confirm("Do you want to share your reading feelings for this book in seminar?");
	var url = "book_action.php?action=share&book_id="+book_id;
	if(result)
	loadXMLDoc(url, function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
			}
		});
};

function add_score(tdc, book_id)
{
	var result = prompt("Please input score 1-5:");
	if(result < 1 || result > 5){
		alert("score shall be 1-5");
		return;
	}
	var url = "book_action.php?action=add_score&book_id="+book_id+"&score="+result;
	loadXMLDoc(url, function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				//tdc.innerHTML = result;
				location.reload();
				//document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
				//setTimeout("windows.location.href="+backurl, 1000);
			}
	});
	return;
};

function deduce_member_score(tdc, member)
{
	var result = prompt("Deduce Score:");
	var url = "book_action.php?action=deduce_member_score&borrower="+member+"&score="+result;
	loadXMLDoc(url, function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				//tdc.innerHTML = result;
				location.reload();
				//document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
				//setTimeout("windows.location.href="+backurl, 1000);
			}
	});
	return;
};
</script>

<?php
include 'book_lib.php';
/*
   copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
 */

include 'debug.php';
include 'db_connect.php';

global $login_id, $max_book, $setting;	

//foreach(session_get_cookie_params() as $a=>$b){ print("$a=>$b<br>");}

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
			home_link();
			exit;
		}else if($ret == 2){
			print("wrong password");
			unset($_SESSION['user']);
			home_link();
			exit;
		}else{
			$_SESSION = array();
			session_destroy();
			session_name('book');
			session_start();
			$_SESSION['user'] = $login_id;
		}

	}
}else if(isset($_POST['register'])){
	include 'book_user_register.php';
	//header("Location: book_user_register.php");
	exit;
}else if(isset($_SESSION['user'])){
	$login_id=$_SESSION['user'];
}else{
	$view = 'class';
	$_SESSION['view'] = $view;
}

setcookie('username',session_name(),time()+3600);    //创建cookie
if(isset($_COOKIE["username"])){    //使用isset()函数检测cookie变量是否已经被设置
	$username = $_COOKIE["username"];    //您好！nostop     读取cookie 
}else{
	$username = '';
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
	$role_text = "管理员";
else if($role == 1)
	$role_text = "会员";
else
	$role_text = "非会员";

if($login_id == 'NoLogin')
	$login_text = "<a href=book_user_login.php>登录</a>";
else
	$login_text = "<a href=book_user_setting.php>$login_id($role_text)<a/> &nbsp;&nbsp;<a href=\"book.php?action=logout\">注销</a>";

$action="home";
if(isset($_GET['action']))$action=$_GET['action'];
if($action == "logout"){
	$_SESSION = array();
	session_destroy();
	print "You are logout now";
	sleep(5);
	header("Location: book.php");
# header("Location: book_user_login.php");
}

$book_id=0;
if(isset($_GET['book_id'])) $book_id=$_GET['book_id'];
if(isset($_GET['record_id'])) $record_id=$_GET['record_id'];
if(isset($_GET['borrower'])) $borrower =$_GET['borrower'];


print "<a href=\"book.php\">首页</a> &nbsp;&nbsp;$login_text ";

if($role == 0){
	print "&nbsp;&nbsp;<a href=\"book.php?action=join\">入会</a>";
}

if($role >= 1){
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_favor\">我的</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_share\">分享</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_out\">借出</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=history\">借阅历史</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_timeout\">超时</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_statistic\">统计</a>";
}

if($role == 2){
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"book.php?action=manage\">管理</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=log\">日志</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_member\">会员</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=add_newbook\">新书</a>";
	print "&nbsp;&nbsp;<a href=\"book.php?action=list_tbd\">待定</a>";
}

print("<br>");

if($role < 1 && $login_id == 'NoLogin'){
	print("<div id='div_homentro'>");
	$lines = file("QClubIntroduction.htm");
	foreach ($lines as $line_num => $line) {
	    #print(htmlspecialchars($line) . "<br/>\n");
	    print($line);
	}
	print('</div>');
}
#print('<div id="div_homeintro" ></div>');


if(isset($_GET['items_perpage'])) $items_perpage=$_GET['items_perpage'];
else if(isset($_SESSION['items_perpage'])) $items_perpage = $_SESSION['items_perpage'];
$_SESSION['items_perpage'] = $items_perpage;

$order = 2;
if(isset($_GET['order'])) $order=$_GET['order'];
else if(isset($_SESSION['order'])) $order = $_SESSION['order'];
$_SESSION['order'] = $order;

if(!isset($_SESSION['start'])) $_SESSION['start'] = 0;
$start = $_SESSION['start'];

if(isset($_GET['book_sname'])) $book_sname = $_GET['book_sname'];

if(isset($_POST['prev'])) $action="prev";
if(isset($_POST['next']))$action="next";
if(isset($_POST['begin'])) $action="begin";
if(isset($_POST['end']))$action="end";
if(isset($_POST['list_all']))$action="list_all";


dprint("Action:$action Login:$login_id book_id:$book_id start:$start items:$items_perpage setting:$setting<br>");

if($role != 2 && preg_match("/manager|approve|stock|push|log|reject_wait/",$action)){
	print("You are not administrator!");
	return;
}

if($role < 1 && preg_match("/lend|history/",$action)){
	print("You are not member!");
	return;
}


if(isset($_GET['comment_type'])) $comment_type=$_GET['comment_type'];
else if(isset($_SESSION['comment_type'])) $comment_type=$_SESSION['comment_type'];
else $comment_type = 0;
$_SESSION['comment_type'] = $comment_type;
if($comment_type == 1){
	$view = 'normal';
	$_SESSION['view'] = $view;
}

if(isset($_GET['class'])) $class=$_GET['class'];
else if(isset($_SESSION['class'])) $class=$_SESSION['class'];
else $class = 100;
$_SESSION['class'] = $class;

if(isset($_GET['view'])) $view=$_GET['view'];
else if(isset($_SESSION['view'])) $view=$_SESSION['view'];
else $view = $setting & 1 ? 'normal':'brief';
dprint("view:$view, setting:$setting<br>");
$_SESSION['view'] = $view;
$_SESSION['setting'] = $setting;
$favor = false;

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
		$start = 0;
		$_SESSION['start'] = $start;
		show_home();
		break;
	case "end":
		$end = get_total_books();
		$start = $end - $items_perpage - 1;
		if($start < 0)
			$start = 0;
		$_SESSION['start'] = $start;
		show_home();
		break;
	case "prev":
		$start -= $items_perpage;
		if($start < 0)
			$start = 0;
		$_SESSION['start'] = $start;
		show_home();
		break;

	case "borrow":
		if(isset($record_id)){
			borrow_wait_book($record_id, $login_id);
		}else
			borrow_book($book_id, $login_id);
		show_my_hot($login_id);
		break;
	case "renew":
		$book_id = get_bookid_by_record($record_id);
		renew_book($book_id, $record_id, $login_id);
		show_my_hot($login_id);
		break;
	case "cancel":
		set_record_status($record_id, 0x100);
		show_my_hot($login_id);
		break;
	case "returning":
		set_record_status($record_id, 3);
		show_my_hot($login_id);
		break;
	case "share":
		apply_share($book_id, $login_id);
		break;
	case "share_done":
		set_record_status($record_id, 0x106);
		manage_record($login_id);
		break;
	case "share_cancel":
		set_record_status($record_id, 0x110);
		manage_record($login_id);
		break;
	case "wait":
		if(wait_book($book_id, $login_id)){
			$bookname = get_bookname($book_id);
			$borrower = get_borrower($book_id);
			$to = get_user_attr($borrower, 'email');
			$user = get_user_attr($login_id, 'name');
			$cc = '';
			mail_html($to, $cc, "$user is waiting for your book <$bookname>", "");
		}
		home_link();
		break;
	case "show_borrower":
		show_book($book_id);
		break;
	case "list_share":
		show_share($login_id);
		break;
	case "add_favor":
		add_favor($login_id, $book_id);
		list_book($view, $start, $items_perpage, 0, 'favor');
		break;
	case "remove_favor":
		remove_favor($login_id, $book_id);
		list_book($view, $start, $items_perpage,0, 'favor');
		break;
	case "clear_favor":
		clear_favor($login_id);
		list_book($view, $start, $items_perpage,0, 'favor');
		break;
	case "list_favor":
		$favor = true;
		show_my($login_id);
		break;
	case "list_out":
		out_record($login_id);
		break;
	case "list_tbd":
		list_book('tbd');
		break;
	case "list_timeout":
		print(">8 week<br>");
		list_record('', 'timeout', 56);
		print(">4 week<br>");
		list_record('', 'timeout', 28);
		print(">3.5 week<br>");
		list_record('', 'timeout', 24);
		print(">3 week<br>");
		list_record('', 'timeout', 21);
		break;
	case "list_statistic":
		list_statistic();
		break;

		/*admin*/

	case "transfer":
		$book_id = get_bookid_by_record($record_id);
		$old_borrower = get_borrower($book_id);
		$bookname = get_bookname($book_id);
		$record_id_my = get_record($book_id);
		$new_borrower = get_borrower_by_record($record_id);
		dprint("trasnfer:$book_id,$old_borrower, $new_borrower, $bookname, $record_id, $record_id_my<br>");
		if($old_borrower != $login_id){
			print("$bookname is not owned by you currently<br>");
			break;
		}
		$old_status = get_book_status($book_id);
		$old_user = get_user_attr($old_borrower, 'name');
		$new_user = get_user_attr($new_borrower, 'name');
		$new_max = get_user_attr($new_borrower, 'max');
		$sql = " select * from history where borrower='$new_borrower' and (status = 1 or status = 2)";
		$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
		$rows = mysql_num_rows($res);
		if($rows >= $new_max){
			print ("$new_user 已达最高借阅数，请让他/她先归还!");
			break;
		}

		$to = get_user_attr($new_borrower, 'email');
		$to .= ';' . get_user_attr($old_borrower, 'email');
		$cc = get_admin_mail();
		add_log($login_id, $old_borrower, $book_id, 0);
		add_log($login_id, $new_borrower, $book_id, 2);
		mail_html($to, $cc, "<$bookname> is transfered from <$old_borrower:$old_user> to <$new_borrower:$new_user>", "");
		set_record_status($record_id_my, 0);
		set_record_status($record_id, 2);
		show_home($login_id);
		break;

	/*admin*/
	case "migrate":
		migrate_record($login_id);
		break;
	case "transfer_comment":
		transfer_comment();
		break;
	case "update_borrow_times":
		update_borrow_times();
		break;
	case "import_favor":
		import_favor_from_history();
		break;

	case "list_member":
		list_member();
		break;
	case "manage":
		manage_record($login_id);
		break;
	case "add_newbook":
		add_newbook($login_id);
		break;
	case "edit_book":
		edit_book($book_id);
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

	case "approve_renew":
		$book_id = get_bookid_by_record($record_id);
		$borrower = get_borrower($book_id);
		$bookname = get_bookname($book_id);
		$to = get_user_attr($borrower, 'email');
		$user = get_user_attr($borrower, 'name');
		$cc = get_admin_mail();
		set_record_status($record_id, 0);
		add_log($login_id, $borrower, $book_id, 0);
		mail_html($to, $cc, "<$bookname> is returned by <$borrower:$user>", "");

		$record_id = add_record($book_id, $borrower, 1, true);
		set_record_status($record_id, 2);
		add_log($login_id, $borrower, $book_id, 2);
		mail_html($to, $cc, "<$bookname> is lent to <$borrower:$user>", "");
		manage_record($login_id);
		break;
	case "lend":
		$book_id = get_bookid_by_record($record_id);
		$borrower = get_borrower_by_record($record_id);
		$bookname = get_bookname($book_id);
		$old_status = get_book_status($book_id);
		if($old_status != 0 && $old_status != 1){
			print("<$book_id>$bookname is not returned yet");
			break;
		}
		$to = get_user_attr($borrower, 'email');
		$user = get_user_attr($borrower, 'name');
		$cc = get_admin_mail();
		set_record_status($record_id, 2);
		$message = "book:$book_id $bookname, record:$record_id, $user";
		mail_html($to, $cc, "<$bookname> is lent to <$borrower:$user>", "$message");
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
		if($to = get_first_wait_mail($book_id))
			mail_html($to, $cc, "your waiting book <$bookname> is returned by <$user>", "");
		add_log($login_id, $borrower, $book_id, 0);
		set_record_status($record_id, 0);
		manage_record($login_id);
		break;
	case "remove_member":
		dprint("remove $borrower");
		set_member_attr($borrower, 'role', 0);
		list_member();
		break;
	case "approve_member":
		$to = get_user_attr($borrower, 'email');
		$user = get_user_attr($borrower, 'name');
		$cc = get_admin_mail();
		mail_html($to, $cc, "$user is approved to join reading club", "");
		add_log($login_id, $borrower, 0, 0x108);
		if(isset($record_id))
			set_record_status($record_id, 0x108);
		set_member_attr($borrower, 'role', 0x1);
		manage_record($login_id);
		break;
	case "join":
		if($login_id == 'NoLogin'){
			print("please register first!");
			break;
		}
		$borrower = $login_id;
		$cc = get_user_attr($borrower, 'email');
		$user = get_user_attr($borrower, 'name');
		$to = get_admin_mail();
		add_member($borrower, $user, $cc, 0x0);
		add_record(0, $login_id, 0x107);
		mail_html($to, $cc, "$user is applying to join reading club", "");
		manage_record($login_id);
		break;
	case "reject_return":
		$book_id = get_bookid_by_record($record_id);
		$borrower = get_borrower($book_id);
		$bookname = get_bookname($book_id);
		$to = get_user_attr($borrower, 'email');
		$user = get_user_attr($borrower, 'name');
		$cc = get_admin_mail();
		mail_html($to, $cc, "Your return for <$bookname> is rejected", "");
		set_record_status($record_id, 0x2);
		set_book_status($book_id, 2);
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
		set_record_status($record_id, 0x101);
		set_book_status($book_id, 0);
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
function show_my($login_id)
{
	global $view, $start, $items_perpage;

	cal_score();
	$score = get_user_attr($login_id, 'score');
	$score_used = get_user_attr($login_id, 'score_used');
	print("我的积分:$score 已用积分:$score_used<br>");
	print("收藏夹&nbsp;<a href='book.php?action=clear_favor'>全部清除</a>");
	list_book($view, $start, $items_perpage,0, 'favor');
	print("曾借书本");
	list_book($view, $start, $items_perpage,0, 'history');
	print("<div>我的借阅");
	list_record($login_id, 'self', ' (history.status = 2 or history.status = 3 or history.status = 1 ) ');
	print("我的等候");
	list_record($login_id, 'self', ' (history.status = 4 or history.status = 0x100 or history.status = 0x101 or history.status = 0x104)  ');
	print("等我的人");
	list_record($login_id, 'waityou');
	print("我的借阅记录");
	list_record($login_id, 'self', ' history.status = 0 ');
	print("我的评论");
	list_comments('', $login_id);
	print("我的评分记录");
	list_record($login_id, 'score', ' history.status = 0x109 ');
	print("</div>");
}

function show_my_hot($login_id)
{
		print("我的借阅");
		list_record($login_id, 'self', ' (history.status = 2 or history.status = 3 or history.status = 1 ) ');
		print("我的等候");
		list_record($login_id, 'self', ' (history.status = 4 or history.status = 0x100 or history.status = 0x101 or history.status = 0x104)  ');
		print("等我的人");
		list_record($login_id, 'waityou');
}

function show_home()
{
	global $login_id, $view, $start, $items_perpage;
	global $class_list, $class, $comment_type, $role, $order;
	if($role > 0){
		show_my_hot($login_id);
	}
	$view_op = $view == 'brief'?'normal':'brief';
	$view_ch = $view_op == 'brief'?'简略':'完整';
	print("<div'>书库列表 <a href='book.php?view=$view_op'>$view_ch</a>");
	print("&nbsp;<a href='book.php?items_perpage=25'>25</a>");
	print("&nbsp;<a href='book.php?items_perpage=50'>50</a>");
	print("&nbsp;<a href='book.php?items_perpage=100'>100</a>");
	print("&nbsp;<a href='book.php?items_perpage=200'>200</a>");
	print("&nbsp;&nbsp;&nbsp;&nbsp;<a href='book.php?view=class'>分类</a>&nbsp;");
	print("<select id='sel_class' onchange='change_class(this.value, 0)'>");
	print("<option value='100'>所有</option>");
	foreach($class_list as $key => $class_text) {
		print("<option value='$key' ");
		if($class == $key) print("selected");
		print(" >$key-$class_text</option>");
	}
	print("</select>");
	if($comment_type == 0)
		print("&nbsp;<a href='book.php?comment_type=1'>只看评论</a>");
	else
		print("&nbsp;<a href='book.php?comment_type=0'>全部</a>");
	print("&nbsp;书名检索&nbsp;<input id='id_book_name' name='book_name' type='text' value=''>");
	print("<input class='btn' type='button' name='search' value='检索' onclick='book_search()'>");

	print("&nbsp;排序&nbsp;");
	print("<select id='sel_class' onchange='change_order(this.value, 0)'>");
	$order_list = array("编号","次数", "评分");
	foreach($order_list as $key => $order_text) {
		print("<option value='$key'");
		if($key == $order) print("selected");
		print(">$order_text</option>");
	}
	print("</select>");

	print("</div>");

	print("<div id='div_booklist'>");
	list_book($view, $start, $items_perpage, $order, "");
	print("</div>");
}

function show_share()
{

	print("<iframe height=1920 width=1024 src='import_file.php'></iframe>");
}

function add_newbook()
{

	print("<iframe height=1920 width=800 src='edit_book_ui.php'></iframe>");
}

function edit_book($book_id)
{
	print("<iframe height=1920 width=800 src='edit_book_ui.php?book_id=$book_id'></iframe>");
}

function update_borrow_times()
{
	$sql = " update books as b inner join ( select book_id, count(status) as cnt from history where status = 0 or status = 2 or status = 3 or status = 4 group by book_id) as x using(book_id) set b.times = x.cnt";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$rows = mysql_affected_rows();
	print("<br>update $rows lines");
}

?>

</body>
</html>
