<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-CN" /> 
<head>
<link rel="stylesheet" type="text/css" href="edit_case.css" media="screen"/>
</head>
<body>
<?php
$web_name = 'book';
$home_page = 'book.php';
session_set_cookie_params(7*24*3600);
session_name($web_name);
session_start();

include 'book_lib.php';
include 'debug.php';
include 'db_connect.php';
include 'myphp/login_lib.php';

global $login_id, $login_password;
check_login();

print "<a href=\"book.php\">Home</a> &nbsp;&nbsp;<a href=\"book_user_setting.php\">Setting</a>";


if(isset($_POST['save'])){
	$name=$_POST['name'];
	$email=$_POST['email'];
	$view=$_POST['view'];
	$perpage=$_POST['perpage'];
	$setting = ($setting & ~1) | $view;
	$sql1 = "update user.user set name='$name',email='$email' where user_id ='$login_id'";
	$res1=update_mysql_query($sql1);

	$sql = "update member set user_name='$name',email='$email', setting=$setting, perpage=$perpage where user='$login_id'";
	$res=update_mysql_query($sql);
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
			$sql1 = "update user.user set password=ENCRYPT('$newpassword1', 'ab') where user_id ='$login_id'";
			$res1=update_mysql_query($sql1);
			printf("<br>Change password successfully!");
			show_home_link("Back");
			return;
		}else{
			printf("New Password is empty!");
			return;
		}
	}
}else{
	$sql1 = "select * from user.user where user_id ='$login_id'";
	$res1=mysql_query($sql1) or die("invalid query:" . mysql_error());
	if(!$row1=mysql_fetch_array($res1)) {
		print("not found user id");
		return;
	}
	$user1=$row1['user_id'];
	$name1=$row1['name'];
	$email1=$row1['email'];

	$sql1 = "select * from member where user='$login_id'";
	$res1=mysql_query($sql1) or die("invalid query:" . mysql_error());
	if(!$row1=mysql_fetch_array($res1)) {
		print("not found user id");
		return;
	}
	$perpage=$row1['perpage'];
	$setting = $_SESSION['setting'];
	dprint("$user1, $perpage");
}
?>

<form method="post" action="book_user_setting.php">
<table id='id_setting' width=150 class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 style='width:200pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>
<tr class="odd noclick"><th width=100>User ID:</th><td><input name="user_id" readonly type="text" value="<?php  echo $user1; ?>"></td></tr>
<tr><th>Name:</th><td><input name="name" type="text" value='<?php  echo $name1;?>'></td></tr>
<tr><th>email:</th><td><input name="email" readonly type="text" value='<?php  echo $email1;?>'></td></tr>
<tr><th>default view:</th><td><input type='radio' name="view" value=0 <?php if(!$setting&1) print'checked'; ?> >简略<input type='radio' name="view" value=1 <?php if($setting&1) print'checked';?> >完整</td></tr>
<tr><th>book/page:</th><td><input type='text' name="perpage" value='<?php echo $perpage;?>'></td></tr>
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
