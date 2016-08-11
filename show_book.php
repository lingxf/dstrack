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

$class=$_SESSION['class'];
if(isset($_GET['class'])) $class=$_GET['class'];
$_SESSION['class'] = $class;

$start=$_SESSION['start'];
$items_perpage=$_SESSION['items_perpage'];

list_book($view, $start, $items_perpage);

?>
