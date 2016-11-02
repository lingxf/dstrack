<?php
include 'book_lib.php';
/*
   copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
 */

include 'debug.php';
include 'db_connect.php';

global $login_id, $max_book, $setting;	

session_name('book');
session_set_cookie_params(60*60*18);
session_start();

$sid=session_id();
$login_id = "";

if(isset($_SESSION['user'])) $login_id=$_SESSION['user'];

$max_books = 1;
$items_perpage = 50;
$role = is_member($login_id);

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

if($role != 2 && preg_match("/deduce_member_score|manager|approve|stock|push|log|reject_wait/",$action)){
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

	/*admin*/
	case "migrate":
		migrate_record($login_id);
		break;
	case "deduce_member_score":
		$score = $_GET['score'];
		deduce_member_score($borrower, $score);
		break;
}

?>

</body>
</html>
