<html>
<Title>Import Sharing PPT</Title>
已分享PPT:
<?php 
global $login_id;	
$login_id="";
session_name("book");
session_start();
if(isset($_SESSION['user']))
	$login_id=$_SESSION['user'];
else
	exit();
?>
<br>
<?php 
include 'book_lib.php';
include "db_connect.php";

if(isset($_POST['upload'])){
	$uploaddir = 'share/';
	$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
	dprint("Move {$_FILES['userfile']['tmp_name']} to $uploadfile<br>");
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		dprint("File is valid, and was successfully uploaded.\n");
	} else {
		dprint("Move {$_FILES['userfile']['tmp_name']} to $uploadfile failed<br>");
		return;
	}
}

if ($handle = opendir('share/')) {
	while (false !== ($file = readdir($handle))) {
		if($file == '..' || $file == '.')
			continue;
		print("<a href='share/$file'>$file</a>\n<br>");
	}
	closedir($handle);
}

?>
<form enctype="multipart/form-data" action="import_file.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="128000000" />
    文件: <input name="userfile" type="file" />
    <input name='upload' type="submit" value="Upload" />
</form>

<html>
