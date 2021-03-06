<?php
include_once 'debug.php';
include_once 'db_connect.php';
include 'myphp/common.php';
include 'myphp/disp_lib.php';
include 'book_lib.php';
/*
   copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
 */


global $login_id, $max_book, $setting;	

session_set_cookie_params(60*60*18);
if($web_name != session_name($web_name))
	session_start();

$sid=session_id();
include_once 'myphp/login_action.php';

$max_books = 1;
$items_perpage = 50;
$groups = '';
$role = is_member($login_id, $groups);

$action="home";
if(isset($_GET['action']))$action=$_GET['action'];

if($action == "logout"){
	$_SESSION = array();
	session_destroy();
	print "You are logout now";
}

$book_id=0;
if(isset($_GET['book_id'])) $book_id=$_GET['book_id'];
if(isset($_GET['record_id'])) $record_id=$_GET['record_id'];
if(isset($_GET['borrower'])) $borrower =$_GET['borrower'];

if($role < 1 ){
	print("You are not member!");
	return;
}

if($role != 2 && preg_match("/deduce_member_score|manager|approve|stock|push|log|reject_wait/",$action)){
	print("You are not administrator!");
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
$_SESSION['view'] = $view;
$_SESSION['setting'] = $setting;
$favor = false;


switch($action){
	case "share":
		apply_share($book_id, $login_id);
		break;
	case "add_score":
		$score = $_GET['score'];
		add_score($book_id, $login_id, $score);
		break;
	case "add_want":
		add_want($book_id, $login_id);
		break;
	case "cancel_recommend":
		cancel_recommend($book_id);
		break;

	/*admin*/
	case "join_share":
		$users = get_url_var('users', '');
		$users_array = explode(',', $users);
		$book_id = get_bookid_by_record($record_id);
		$total = 0;
		foreach($users_array as $borrower){
			$n = join_share($book_id, $record_id, $borrower);
			if($n === false)
				continue;
			else
				$total += $n;
		}
		if($total > 0)
			print ("添加".$total."位分享会参与者!");
		break;
	case "migrate":
		migrate_record($login_id);
		break;
	case "deduce_member_score":
		$score = $_GET['score'];
		deduce_member_score($borrower, $score);
		break;
}

?>
