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
if(isset($_POST['op'])) $op=$_POST['op'];
if(isset($_GET['op'])) $op=$_GET['op'];

if(!isset($op))
	exit();

if(isset($_POST['comment_id'])) $comment_id = $_POST['comment_id'];
if(isset($_GET['comment_id'])) $comment_id = $_GET['comment_id'];

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
	$old_date = $_POST['old_date'];
	$desc =  $_POST['desc'];
}
if($role == 0)
	return '';

if($book_id && $op=="modify"){
	$intext = str_replace("'", "''", $text);
	if($col == 'comments'){
		$cm = "[$login_id]$intext<br>";
		$reg = '/\[(\d+)\/(\d+)\]:([^\[]*)([\d\D\n.]*)/';
		if(preg_match($reg, $intext, $matches)){
			$intext = $matches[3];
		}
		add_comment($book_id, $login_id, $intext);
		$tt =  read_book_column($book_id, $col);
		$bookname = read_book_column($book_id, 'name');
		if($tt != -1)
			$cm .= $tt;
		$intext = $cm;
		$to = 'QClub.BJ.Reading@qti.qualcomm.com';
		#$to = 'xling@qti.qualcomm.com';
		$user = get_user_attr($login_id, 'name');
		$cc = get_user_attr($login_id, 'email');
		$cc = '';
		#mail_html($to, $cc, "$user is adding comments for book <$bookname>", $text);
	}
	if($col == 'class')
		$sql = "UPDATE books set `$col`=$intext ";
	else
		$sql = "UPDATE books set `$col`='$intext'";
	$sql .= " where `book_id`=$book_id";
	$res1=update_mysql_query($sql);
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
	$sql = "UPDATE books set `name`='$name', author='$author', ISBN='$isbn', `index`='$index', price='$price', buy_date='$buy_date', sponsor='$sponsor', note='$note', `desc`='$desc' ";
	$class = get_class_no($book_id);
	if($index != '' and $class == 0){
		$class_no = get_class_by_index(substr($index, 0, 1));
		$sql .= ", `class` = $class_no ";
	}
	$sql .= "where book_id = $book_id";
	$res=update_mysql_query($sql);
	$rows = mysql_affected_rows();
	if($old_date == '0000-00-00' && $buy_date != ''){
		add_log($login_id, $login_id, $book_id, 11);
		print("Buy New Book $name <br>");
	}
	dprint("$old_date: $buy_date<br>");
	print("Update $rows<br>");
	return;
}else if($op=="add"){
	$sql = "insert into books set `name`='$name', author='$author', ISBN='$isbn', `index`='$index', price='$price', buy_date='$buy_date', sponsor='$sponsor', note='$note', `desc`='$desc'";
	if($index != ""){
		$class_no = get_class_by_index(substr($index, 0, 1));
		$sql .= ", `class` = $class_no ";
	}
	$res=update_mysql_query($sql);
	$rows = mysql_affected_rows();

	$sql = "select book_id from books where `name`='$name' and author='$author' and sponsor='$sponsor'";
	$res=mysql_query($sql) or die("Invalid query1:" . $sql . mysql_error());
	if($row1=mysql_fetch_array($res))
		$book_id = $row1['book_id'];
	else
		$book_id = 0;
	print("Add $rows rows, book_id:$book_id book:$name $author $sponsor<br>");
	add_log($login_id, $login_id, $book_id, 10);
	home_link();
	return;
}else if($op=="save_comment" || $op=="add_comment"){
	if(isset($_POST['cancel'])){
		print("<script type=\"text/javascript\">setTimeout(\"window.location.href='book.php?action=list_comments_all'\",1000);</script>");
		return;
	}
	$comment = $_POST['comment'];
	$borrower = $_POST['borrower'];
	$date = $_POST['date'];
	if($op=="save_comment"){
		$sql = "update comments set words = '$comment', timestamp='$date'  where comment_id = $comment_id";
		$row = update_mysql_query($sql);
		print("Update $row rows for $borrower");
	}else{
		$parent = $_POST['parent'];
		$book_id = $_POST['book_id'];
		$sql = "insert comments set words = '$comment', borrower='$borrower', parent='$parent', book_id='$book_id'  ";
		$row = update_mysql_query($sql);
		print("Insert $row rows for $borrower, $book_id");
	}
	print("<script type=\"text/javascript\">setTimeout(\"window.location.href='book.php?action=list_comments_all'\",2000);</script>");

}else if($op=="edit_comment_ui"||$op=="add_comment_ui"){
	if($op=="edit_comment_ui"){
		$op = "save_comment";
		$sql = "select * from comments, books where comments.book_id = books.book_id and comment_id = $comment_id";
		$res = read_mysql_query($sql);
		while($row = mysql_fetch_array($res)){
			$comment = $row['words'];
			$date = $row['timestamp'];
			$borrower = $row['borrower'];
			$book_id = $_row['book_id'];
			$book_name = $row['name'];
		}
	}else{
		$op = "add_comment";
		$parent = $_GET['comment_id'];
		$comment_id = $parent;
		$parent_user = $_GET['borrower'];
		$book_id = $_GET['book_id'];
		$book_name = get_bookname($book_id); 
		$borrower = $login_id;
	}
	print("
		<html>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<meta http-equiv='Content-Language' content='zh-CN' /> 
	");
	print("
		<form method='post' action='edit_book.php'>
		<table border=1 bordercolor='#0000f0', cellspacing='0' cellpadding='0' style='padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:0;table-layout:auto'>
		<tbody>
		<input type='hidden' name='op' value='$op'>
		<input name='comment_id' type='hidden' value='$comment_id'>
		<input name='book_id' type='hidden' value='$book_id'>
		<input name='parent' type='hidden' value='$parent'>
		<input name='borrower' type='hidden' value='$borrower'>
		<tr class='odd noclick'><th>ID:</th><td>$comment_id</td></tr>
		<tr class='odd noclick' ><th>ReplyTo:</th><td>$parent_user</td></tr>
		<tr class='odd noclick'><th>User:</th><td>$borrower</td></tr>
		<tr class='odd noclick'><th>Book:</th><td>$book_name</td></tr>
		");
	if($op=="save_comment")
		print("<tr class='odd noclick'><th>Date:</th><td><input name='date' type='text' value='$date' ></td></tr> ");
	print("
		<tr><th>Comment:</th><td>
		<textarea wrap='soft' type='text' name='comment' rows='8' maxlength='2000' cols='60'>$comment</textarea>
		</td></tr>
		</tbody>
		</table>
		<input class='btn' type='submit' name='save' value='Save'>
		<input class='btn' type='submit' name='cancel' value='Cancel'>
		</form> ");
}else if($op=="recommend_book" || $op == "save_recommend" || $op == "buy_book"){
	if(isset($_POST['cancel'])){
		print("<script type=\"text/javascript\">setTimeout(\"window.location.href='book.php?action=list_recommend'\",1000);</script>");
		return;
	}
	$borrower= $_POST['borrower'];
	$book_name = $_POST['book_name'];
	$comments = $_POST['comments'];
	$desc = $_POST['desc'];
	$status = $_POST['status'];
	$author = $_POST['author'];
	$azurl = $_POST['note'];
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	if($op=='recommend_book'){
		$sql = "insert into books_nostock set `name`='$book_name', author='$author', buy_date='$time_start', sponsor='$borrower', note='$azurl', `desc`='$desc', `comments`='$comments', status=$status";
		$res=update_mysql_query($sql);
		$rows = mysql_affected_rows();
		print(" Add $rows rows $book_name, $borrower<br>");
	}else if($op=='buy_book'){
		$sql = "insert into books_nostock set `name`='$book_name', author='$author', buy_date='$time_start', sponsor='$borrower', note='$azurl', `desc`='$desc', `comments`='$comments', status=3";
		$res=update_mysql_query($sql);
		$rows = mysql_affected_rows();
		print(" Add $rows rows $book_name, $borrower<br>");
	}else{
		$book_id= $_POST['book_id'];
		$sql = " update books_nostock set `name`='$book_name', author='$author', buy_date='$time_start', sponsor='$borrower', note='$azurl', `desc`='$desc', `comments`='$comments', status=$status where book_id = $book_id";
		$res = update_mysql_query($sql);
		print("Update $rows rows $book_id, $book_name, $borrower, $date<br>");
	}
	print("<script type=\"text/javascript\">setTimeout(\"window.location.href='book.php?action=list_recommend'\",1000);</script>");
}else if($op=="add_recommend_ui" || $op=="edit_recommend_ui"||$op=="buy_book_ui"){
	if($op=="add_recommend_ui"){
		$op = "recommend_book";
		$status = $_GET['status'];
		$borrower = $login_id;
	}else{
		if($op == "edit_recommend_ui")
			$op = "save_recommend";
		else
			$op = "buy_book";
		$book_id = $_GET['book_id'];
		$sql = "select * from books_nostock where book_id = $book_id";
		$res = read_mysql_query($sql);
		while($row = mysql_fetch_array($res)){
			$borrower= $row['sponsor'];
			$book_id= $row['book_id'];
			$book_name = $row['name'];
			$comments = $row['comments'];
			$desc = $row['desc'];
			$author = $row['author'];
			$azurl = $row['note'];
			$status = $row['status'];
		}
	}
	if($op == "buy_book")
		$borrower = $login_id;
	$status_string = array('取消', '捐赠', '推荐', '待购');
	$status_text = $status_string[$status];
	print("<html>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<meta http-equiv='Content-Language' content='zh-CN' /> 
		");
			print("
		<form method='post' action='edit_book.php'>
		<table border=1 bordercolor='#0000f0', cellspacing='0' cellpadding='0' style='padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:0;table-layout:auto'>
		<tbody>
		<input type='hidden' name='op' value='$op'>
		<input type='hidden' name='book_id' value='$book_id'>
		<input type='hidden' name='status' value='$status'>
		<tr class='odd noclick'><th>类别:</th><td>$status_text</td></tr>
		<tr class='odd noclick'><th>推荐人:</th><td><input name='borrower' readonly type='text' value='$borrower' ></td></tr>
		<tr class='odd noclick'><th>书名:</th><td><input name='book_name' type='text' value='$book_name' ></td></tr>
		<tr class='odd noclick'><th>作者:</th><td><input name='author' type='text' value='$author' ></td></tr>
		<tr class='odd noclick'><th>亚马逊链接:</th><td><input name='note' type='text' value='$azurl' ></td></tr>
		<tr><th>图书介绍:</th><td>
		<textarea wrap='soft' type='text' name='desc' rows='3' maxlength='2000' cols='60'>$desc</textarea>
		</td></tr>
		<tr><th>推荐评论:</th><td>
		<textarea wrap='soft' type='text' name='comments' rows='3' maxlength='2000' cols='60'>$comments</textarea>
		</td></tr>
		</tbody>
		</table>
		<input class='btn' type='submit' name='save' value='Save'>
		<input class='btn' type='submit' name='cancel' value='Cancel'>
		</form> ");
}else if($op=="add_share" || $op == "save_share"){
	if(isset($_POST['cancel'])){
		print("<script type=\"text/javascript\">setTimeout(\"window.location.href='book.php?action=list_share'\",1000);</script>");
		return;
	}
	$book_id = $_POST['book_id'];
	$borrower = $_POST['borrower'];
	$date = $_POST['date'];
	$book_name = $_POST['book_name'];
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$adate = $time_start;
	$sdate = $date;
	if($op=='add_share'){
		$rows = add_record_one($book_id, $borrower, $adate, "", $sdate, $sdate, 0x105, 0, $book_name);
		print(" Add $rows rows $book_id, $book_name, $borrower<br>");
	}else{
		$record_id = $_POST['record_id'];
		$sql = " update history set `borrower`='$borrower', book_id=$book_id, sdate='$sdate', misc='$book_name' where record_id = $record_id";
		$res = update_mysql_query($sql);
		print("Update $rows rows $book_id, $book_name, $borrower, $date<br>");
	}

	print("<script type=\"text/javascript\">setTimeout(\"window.location.href='book.php?action=list_share'\",1000);</script>");
}else if($op=="add_share_ui" || $op=="edit_share_ui"){
	if($op=="add_share_ui"){
		$op = "add_share";
		$time = time() + 3600*24*7;
		$date = strftime("%Y-%m-%d %H:%M:%S", $time);
		$borrower = isset($_GET['borrower'])?$_GET['borrower']:'';
		$book_id = 0;
		$book_name = '';
	}else{
		$op = "save_share";
		$record_id = $_GET['record_id'];
		$sql = "select * from history where record_id = $record_id";
		$res = read_mysql_query($sql);
		while($row = mysql_fetch_array($res)){
			$borrower= $row['borrower'];
			$book_id= $row['book_id'];
			$date= $row['sdate'];
			$book_name = $row['misc'];
		}
	}
	print("<html>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<meta http-equiv='Content-Language' content='zh-CN' /> 
		");
			print("
		<form method='post' action='edit_book.php'>
		<table border=1 bordercolor='#0000f0', cellspacing='0' cellpadding='0' style='padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:0;table-layout:auto'>
		<tbody>
		<input type='hidden' name='op' value='$op'>
		<input type='hidden' name='record_id' value='$record_id'>
		<tr class='odd noclick'><th>编号:</th><td><input name='book_id' type='text' value='$book_id' ></td></tr>
		<tr class='odd noclick'><th>用户名:</th><td><input name='borrower' type='text' value='$borrower' ></td></tr>
		<tr class='odd noclick'><th>日期:</th><td><input name='date' type='text' value='$date' ></td></tr>
		<tr><th>非库书名:</th><td>
		<textarea wrap='soft' type='text' name='book_name' rows='1' maxlength='2000' cols='60'>$book_name</textarea>
		</td></tr>
		</tbody>
		</table>
		<input class='btn' type='submit' name='save' value='Save'>
		<input class='btn' type='submit' name='cancel' value='Cancel'>
		</form> ");
}else{
	print("unsupported $op");
}

?>
