<html>
<title>Add/Edit Book Library</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-CN" /> 
<head>
<link rel="stylesheet" type="text/css" href="edit_book.css" media="screen"/>
</head>
<body onLoad="if(this.bodyOnLoad) bodyOnLoad();">
<h2>Book Library - add/edit </h2>
<?php
/*
	book library system
	copyright Xiaofeng(Daniel) Ling<xling@qualcomm.com>, 2012, Aug.
*/

include 'book_lib.php';
include 'debug.php';
include 'db_connect.php';

function print_table_style(){

	print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:0;table-layout:auto">');
}


session_name('book');
session_start();
$login_id = $_SESSION['user'];
$role = is_member($login_id);
if($role < 2){
	print("No permission");
	exit();
}
if(isset($_GET['book_id'])){
	$book_id=$_GET['book_id'];
	$sql = " select * from books where book_id = $book_id";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id']; 
		$name = $row['name'];
		$author= $row['author'];
		$isbn = $row['ISBN'];
		$index = $row['index'];
		$price = $row['price'];
		$sponsor = $row['sponsor'];
		$note = $row['note'];
		$buy_date = substr($row['buy_date'], 0, 10);
		$desc =  $row['desc'];
	}
	$op = 'edit';
}else{
	$book_id = ''; 
	$name = '';
	$author= '';
	$isbn = ''; 
	$index = '';
	$price = '';
	$sponsor = '';
	$note = ''; 
	$buy_date  = '';
	$desc = '';
	$op = 'add';
}

home_link();
?>
<form method="post" action="edit_book.php">
<?php print_table_style();?>
<tbody>
<input type="hidden" name="op" value="<?php echo $op?>">
<tr class="odd noclick"><th>ID:</th><td><input name="book_id" readonly type="text" value="<?php echo $book_id; ?>"></td></tr>
<tr><th>Name:</th><td><input name="name" type="text" value='<?php echo $name;?>' ></td></tr>
<tr><th>Author:</th><td><input name="author" type="text" value='<?php echo $author;?>'></td></tr>
<tr><th>ISBN:</th><td><input name="ISBN" type="text" value='<?php  echo $isbn;?>'></td></tr>
<tr><th>index:</th><td><input name="index" type="text" value='<?php  echo $index;?>'></td></tr>
<tr><th>price:</th><td><input name="price" type="text" value='<?php  echo $price;?>'></td></tr>
<tr><th>buy_date:</th><td><input name="buy_date" type="text" value='<?php  echo $buy_date;?>'></td></tr>
<tr><th>Sponsor:</th><td><input name="sponsor" type="text" value='<?php  echo $sponsor;?>'></td></tr>
<tr><th>Description:</th><td>
<textarea wrap="soft" type="text" name="desc" rows="8" maxlength="2000" cols="60"><?php echo $desc;?></textarea>
</td></tr>
<tr><th>Note:</th><td>
<textarea wrap="soft" type="text" name="note" rows="2" maxlength="2000" cols="60"><?php echo $note;?></textarea>
</td></tr>
</tbody>
</table>
<input class="btn" <?php if(!isset($book_id)) print("hidden");?> type="submit" name="save" value="Save">
<input class="btn" <?php if(isset($book_id)) print("hidden");?> type="submit" name="insert" value="Insert">
</form>
<script  type="text/javascript">
function bodyOnload(){
	sfprint("ok");
};
</script>
<br>
</body>
</html>
