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

$book_id=$_POST['book_id'];
$col=$_POST['col'];
$op=$_POST['op'];
$text=$_POST['text'];
$login_id=$_SESSION['user'];
$role = is_member($login_id);
if($role == 0)
	return '';

if($book_id && $op=="modify"){
	$intext = str_replace("'", "''", $text);
	if($col == 'comments'){
		$cm = "[$login_id]$intext<br>";
		$tt =  read_book_column($book_id, $col);
		if($tt != -1)
			$cm .= $tt;
		$intext = $cm;
	}
	if($col == 'class')
		$sql = "UPDATE books set `$col`=$intext ";
	else
		$sql = "UPDATE books set `$col`='$intext'";
	$sql .= " where `book_id`=$book_id";
	$res1=mysql_query($sql) or die("Invalid query1:" . $sql . mysql_error());
	$text = str_replace("''", "'", $intext);
	print(get_class_name($text));
}else if($book_id && $op=="read"){
	$tt = read_book_column($book_id, $col);
	if($tt == -1)
		print("No this book");
	else
		print $tt;
	return;

}
?>
