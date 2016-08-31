<html>
<Title>Import Sharing PPT</Title>

<form enctype="multipart/form-data" action="import_file.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="128000000" />
    文件: <input name="userfile" type="file" />
    <input name='upload' type="submit" value="Upload" />
</form>
<?php 
include 'debug.php';
include 'book_lib.php';
include "db_connect.php";
global $login_id;	
$login_id="";
session_name("book");
session_start();
if(isset($_SESSION['user']))
	$login_id=$_SESSION['user'];
else
	exit();


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


print("待分享:<br>");
list_record($login_id, 'share', 0x105);
print("已分享PPT:<br>");
if ($handle = opendir('share/')) {
	$booklist = array();
	while (false !== ($file = readdir($handle))) {
		if($file == '..' || $file == '.')
			continue;
		$booklist[] = $file;
	}
	sort($booklist);
	foreach($booklist as $file){
		print("<a href='share/$file'>$file</a>\n<br>");
	}
	closedir($handle);
}

?>

<html>
