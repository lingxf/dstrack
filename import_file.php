<?php 
include_once 'debug.php';
include_once 'myphp/common.php';
include_once 'myphp/disp_lib.php';
include_once 'book_lib.php';
include_once "db_connect.php";
global $login_id, $role;	
$login_id="";
if($web_name != session_name($web_name))
	session_start();
include_once 'myphp/login_action.php';

$disp_city=0;
if(isset($_SESSION['user'])){
	$login_id=$_SESSION['user'];
	$groups = '';
	$role = is_member($login_id, $groups);
	$role_city = get_user_attr($login_id, 'city');
}
?>
<html>
<Title>Import Sharing PPT</Title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<meta http-equiv='Content-Language' content='zh-CN' /> 
<script type="text/javascript">
function add_join_member(record_id){
	var result = prompt("Please input user_ids(seperated by ,):");
	url = "book_action.php?record_id="+record_id+"&action=join_share&users="+result;
	load_url(url);
	return false;
}
</script>
<?php
include_once 'myphp/common_js.php';
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

if(isset($_GET['action']))
	$action = $_GET['action'];
else
	$action = '';

switch($action){
	case 'delete':
		if($role != 2)
			break;
		$file = 'share/'.$_GET['file'];
		unlink($file);
		break;
}

print("待分享:<br>");
list_record($login_id, 'share', ' t1.status = 0x105 ');
print("已分享:<br>");
list_record($login_id, 'share', ' t1.status = 0x106 ');
print("已分享PPT:<br>");
if ($handle = opendir('share/')) {
	$booklist = array();
	while (false !== ($file = readdir($handle))) {
		if($file == '..' || $file == '.')
			continue;
		$booklist[] = $file;
	}
	sort($booklist);
	print($table_head);
	foreach($booklist as $file){
		print("<tr>");
		print_td("<a href='share/$file'>$file</a>");
		if($role == 2)
			print_td("<a onclick='javascript:return confirm(\"Do you really want to delete?\");' href='import_file.php?action=delete&file=$file'>Delete</a>");
		print("</tr>");
	}
	print("</table>");
	closedir($handle);
}

if($city == 2){
	print("北京俱乐部:<br>");
	print("待分享:<br>");
	list_record(-2, 'share', ' t1.status = 0x105 ');
	print("已分享:<br>");
	list_record(-2, 'share', ' t1.status = 0x106 ');
}
print("<html>");
?>

