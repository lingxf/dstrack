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

$login_id=$_SESSION['user'];
$role = is_member($login_id);
if(!isset($_POST['op']))
	exit();
$op=$_POST['op'];
if($op == 'read' || $op == 'write' || $op=='modify'){
	$book_id=$_POST['book_id'];
	$col=$_POST['col'];
	$text=$_POST['text'];
}else if($op == 'edit' || $op == 'add'){
	if(isset($_POST['book_id']))
		$book_id = $_POST['book_id']; 
	$name = $_POST['name'];
	$author= $_POST['author'];
	$isbn = $_POST['ISBN'];
	$index = $_POST['index'];
	$price = $_POST['price'];
	$sponsor = $_POST['sponsor'];
	$note = $_POST['note'];
	$buy_date = $_POST['buy_date'];
	$desc =  $_POST['desc'];
}

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
	if($col == 'class')
		print(get_class_name($text));
	else
		print($text);
}else if($book_id && $op=="read"){
	$tt = read_book_column($book_id, $col);
	if($tt == -1)
		print("No this book");
	else
		print $tt;
	return;

}else if($book_id && $op=="edit"){

	$desc = str_replace("'", "''", $desc);
	$note = str_replace("'", "''", $note);
	$sql = "UPDATE books set `name`='$name', author='$author', ISBN='$isbn', `index`='$index', price='$price', buy_date='$buy_date', sponsor='$sponsor', note='$note', `desc`='$desc' where book_id = $book_id";
	$res=mysql_query($sql) or die("Invalid query1:" . $sql . mysql_error());
	$rows = mysql_affected_rows();
	print("Update $rows<br>");
	home_link();
	return;
}else if($op=="add"){
	
	$sql = "insert into books set `name`='$name', author='$author', ISBN='$isbn', `index`='$index', price='$price', buy_date='$buy_date', sponsor='$sponsor', note='$note', `desc`='$desc'";
	$res=mysql_query($sql) or die("Invalid query1:" . $sql . mysql_error());
	$rows = mysql_affected_rows();

	$sql = "select book_id from books where `name`='$name' and author='$author' and buy_date='$buy_date' and sponsor='$sponsor'";
	$res=mysql_query($sql) or die("Invalid query1:" . $sql . mysql_error());
	if($row1=mysql_fetch_array($res))
		$book_id = $row1['book_id'];
	else
		$book_id = 0;
	print("Add $rows rows, book_id:$book_id book:$name $author $sponsor<br>");
	add_log($login_id, $login_id, $book_id, 10);
	home_link();
	return;
}


?>
