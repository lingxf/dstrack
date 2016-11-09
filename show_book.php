<?php
/*
	book library system
	copyright Xiaofeng(Daniel) Ling<xling@qualcomm.com>, 2012, Aug.
*/

include 'book_lib.php';
include 'debug.php';
include 'db_connect.php';

session_name('book');
session_start();

if(isset($_GET['view'])) $view=$_GET['view'];
else $view=$_SESSION['view'];

if(isset($_GET['book_sname'])) $book_sname = $_GET['book_sname'];

$class=$_SESSION['class'];
if(isset($_GET['class'])) $class=$_GET['class'];
$_SESSION['class'] = $class;

$order=$_SESSION['order'];
if(isset($_GET['order'])) $order=$_GET['order'];
$_SESSION['order'] = $order;

$login_id = $_SESSION['user'];
$role = is_member($login_id);
$role_city = get_user_attr($login_id, 'city');
$disp_city = 0;

$start=$_SESSION['start'];
$items_perpage=$_SESSION['items_perpage'];
$comment_type = $_SESSION['comment_type'];

list_book($view, $start, $items_perpage, $order);

?>
