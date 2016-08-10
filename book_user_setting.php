<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-CN" /> 
<head>
<link rel="stylesheet" type="text/css" href="edit_case.css" media="screen"/>
</head>
<body>
<?php
include 'book_lib.php';
include 'debug.php';
include 'db_connect.php';

global $login_id, $login_password;
check_login();
$setting = $_SESSION['setting'];

print "<a href=\"book.php\">Home</a> &nbsp;&nbsp;<a href=\"book_user_setting.php\">Setting</a>";

$sql1 = "select * from weekly.reporter where reporter='$login_id'";
$res1=mysql_query($sql1) or die("invalid query:" . mysql_error());
if(!$row1=mysql_fetch_array($res1)) {
	print("not found user id");
	return;
}

$reporter1=$row1['reporter'];
$name1=$row1['name'];
$email1=$row1['email'];

if(isset($_POST['save'])){
	$name=$_POST['name'];
	$email=$_POST['email'];
	$view=$_POST['view'];
	$setting = ($setting & ~1) | $view;
	$sql1 = "update weekly.reporter set name='$name',email='$email' where reporter='$login_id'";
	$res1=mysql_query($sql1) or die("invalid query:" . mysql_error());

	$sql = "update member set user_name='$name',email='$email', setting=$setting where user='$login_id'";
	$res=mysql_query($sql) or die("invalid query:$sql" . mysql_error());

	printf("<br>Update successfully!");
	return;
}else if(isset($_POST['change_password'])){
	$password=$_POST['password'];
	$newpassword1=$_POST['newpassword1'];
	$newpassword2=$_POST['newpassword2'];
	if($newpassword1 != $newpassword2){
		print("2 new password does not match");
	}else{
		if($newpassword1){
			if(check_passwd($login_id, $password) !=0){
				print("password does not correct");
				return;
			}
			$sql1 = "update weekly.reporter set password=ENCRYPT('$newpassword1', 'ab') where reporter='$login_id'";
			$res1=mysql_query($sql1) or die("invalid query:" . mysql_error());
			printf("<br>Change password successfully!");
			home_link("Back");
			return;
		}else{
			printf("New Password is empty!");
			return;
		}
	}
}
?>

<form method="post" action="book_user_setting.php">
<table id='id_setting' width=150 class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 style='width:200pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>
<tr class="odd noclick"><th width=100>User ID:</th><td><input name="reporter" readonly type="text" value="<?php  echo $reporter1; ?>"></td></tr>
<tr><th>Name:</th><td><input name="name" type="text" value='<?php  echo $name1;?>'></td></tr>
<tr><th>email:</th><td><input name="email" type="text" value='<?php  echo $email1;?>'></td></tr>
<tr><th>default view:</th><td><input type='radio' name="view" value=0 <?php if(!$setting&1) print'checked'; ?> >简略<input type='radio' name="view" value=1 <?php if($setting&1) print'checked';?> >完整</td></tr>
</table>
<input class="btn" type="submit" name="save" value="Save">

<table id='id_setting' width=100 class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 style='width:100pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>
<tr><th>Password:</th><td><input name="password" type="password" ></td><tr>
<tr><th>Newpassword:</th><td><input name="newpassword1" type="password"></td></tr>
<tr><th>Newpassword:</th><td><input name="newpassword2" type="password"></td></tr>
<tr><td><input name="op" type="hidden" value="save"></td></tr>
</table>
<input class="btn" type="submit" name="change_password" value="Change Password">
</form>
<br>
</body>
</html>
