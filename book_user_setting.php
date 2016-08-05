<html>
<head>
<link rel="stylesheet" type="text/css" href="edit_case.css" media="screen"/>
</head>
<body>
<h1>Book - User Setting</h1>

<?php
include 'weekly_lib.php';
include 'db_connect.php';
global $login_id, $login_password;
check_auth();
	$sql1 = "select * from reporter where reporter='$login_id'";
	
	$res1=mysql_query($sql1) or die("invalid query:" . mysql_error());
	if(!$row1=mysql_fetch_array($res1)) {
		print("not found user id");
		return;
	}

	$reporter1=$row1['reporter'];
	$name1=$row1['name'];
	$email1=$row1['email'];

if(isset($_GET['op'])){
	$op = $_GET['op'];
	if($op == 'delsub'){
		$subid = $_GET['subid'];
		$sql = "delete from subscribe where id='$subid' and reporter='$login_id'";
		$res1=mysql_query($sql) or die("invalid query:$sql" . mysql_error());
		print("delete subcribe $subid");
		return;
	}
}else if(isset($_POST['op'])){
	$name=$_POST['name'];
	$email=$_POST['email'];
	$op=$_POST['op'];
	$password=$_POST['password'];
	$newpassword1=$_POST['newpassword1'];
	$newpassword2=$_POST['newpassword2'];
	if($op == 'delsub'){
		$no = 0;
		$line = "line$no";	
		while($no < 20 && isset($_POST["$line"])){
			print "del:$line<br>";	
			$no += 1;
			$line = "line$no";	
		}
		return;

	}else if($newpassword1 != $newpassword2){
		print("2 new password does not match");
	}else{
		if($newpassword1){
			if(check_passwd($login_id, $password) !=0){
				print("password does not correct");
				return;
			}
			$sql1 = "update reporter set password=ENCRYPT('$newpassword1', 'ab'),name='$name',email='$email' where reporter='$login_id'";
			$res1=mysql_query($sql1) or die("invalid query:" . mysql_error());
			printf("Change password successfully!");
		}else{
			$sql1 = "update reporter set name='$name',email='$email' where reporter='$login_id'";
			$res1=mysql_query($sql1) or die("invalid query:" . mysql_error());
			printf("Update successfully!");
		return;
		}
	}

}

?>

<form method="post" action="user_setting.php">
<table>
<tbody>
<tr class="odd noclick"><th>User ID:</th><td><input name="reporter" type="text" value="<?php  echo $reporter1; ?>"></td></tr>
<tr><th>Name:</th><td><input name="name" type="text" value='<?php  echo $name1;?>'></td></tr>
<tr><th>email:</th><td><input name="email" type="text" value='<?php  echo $email1;?>'></td></tr>
<tr><th>Password:</th><td><input name="password" type="password" ></td><tr>
<tr><th>Newpassword:</th><td><input name="newpassword1" type="password"></td></tr>
<tr><th>Newpassword:</th><td><input name="newpassword2" type="password"></td></tr>
<tr><td><input name="op" type="hidden" value="save"></td></tr>
</tbody>
</table>
<br>
<fieldset class="tblFooters">
<input class="btn" type="submit" value="Save">
</fieldset>
</form>
</tbody>
</table>
</form>
<br>
</body>
</html>
