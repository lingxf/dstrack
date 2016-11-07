<?php 
include_once 'debug.php';
include_once 'book_lib.php';
include_once "db_connect.php";
global $login_id;	
$login_id="";
session_name("book");
session_start();
if(isset($_SESSION['user']))
	$login_id=$_SESSION['user'];

print("
<html>
<Title>Import Sharing PPT</Title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<meta http-equiv='Content-Language' content='zh-CN' /> 
");
print("
<form enctype='multipart/form-data' action='import_file.php' method='POST'>
    <input type='hidden' name='MAX_FILE_SIZE' value='128000000' />
    文件: <input name='userfile' type='file' />
    <input name='upload' type='submit' value='Upload' />
</form>
");

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
print("已分享:<br>");
list_record($login_id, 'share', 0x106);
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

print("<html>");
?>

