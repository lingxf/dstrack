<html>
<Title>Register</Title>
<form enctype="multipart/form-data" action="book_user_register.php" method="POST">
    ID: <input name="user" value="" /><br>
    Name: <input name="name" value="" /><br>
    Email: <input name="email" value="" /><br>
    Password:&nbsp;&nbsp;&nbsp;   <input name="password1" type="password"/><br>
    Password Again:&nbsp;&nbsp;&nbsp;   <input name="password2" type="password"/><br>
    <input type="submit" name="register" value="Register" />
    <input type="submit" name="forget" value="Forget" />
</form>
<?php
include 'book_lib.php';
/*
   copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
 */
include 'debug.php';
include 'db_connect.php';
session_name('book');
session_start();
if(isset($_GET['activate']))
{
	$sid= $_GET['activate'];
	$user = $_GET['user'];
	$sql = "update user.user set activate = 1 where user_id = '$user' and sid = $sid";
	$res=mysql_query($sql) or die("Query Error:".$sql . mysql_error());
	print("activing $user $sql<br>"); 
	$rows = mysql_affected_rows();
	if($rows > 0 ){
		print "$user activate successfully!";
	}else
		print "$user activate fail!";
}

if(isset($_POST['forget']))
{
	$message = "Please click <a href=http://cedump-sh.ap.qalcomm.com/book/book_user_register.php?user=$user&activate=$sid>here</a> to reset your password";
	mail_html($email, '', "$user reset mail", "");
}

if(isset($_POST['register']))
{
	if(isset($_POST['email']))
		$email = $_POST['email'];
	if(isset($_POST['user']))
		$user = $_POST['user'];
	if(isset($_POST['name']))
		$name = $_POST['name'];
	if(isset($_POST['password1']))
		$ps1 = $_POST['password1'];
	if(isset($_POST['password2']))
		$ps2 = $_POST['password2'];
	if($ps1 == $ps2)
	{
		$sql="SELECT * FROM user.user WHERE user_id = '$user'";
		$sid = mt_rand();
		$sql_ins="INSERT into user.user set user_id = '$user', email = '$email', password = '$ps1', sid = $sid ;";
		$res=mysql_query($sql) or die("Query Error:".$sql . mysql_error());
		$row=mysql_fetch_array($res);
		if($row){
			print "$user already registered";
		}else{
			$res=mysql_query($sql_ins) or die("Query Error:".$sql_ins . mysql_error());
			$row=mysql_affected_rows($link);
			print "$user is added successful<br>";
			$message = "
				<html>
				<head>
				<title>Activate</title>
				<body>
				";
				$message .= "Please click <a href=http://cedump-sh.ap.qualcomm.com/book/book_user_register.php?user=$user&activate=$sid>here</a> to activate your account";
				$message .= "Please click <a href=http://linux-bug.ap.qualcomm.com/book-dev/book_user_register.php?user=$user&activate=$sid>here</a> to activate your account";
			$message .= " </body> </html> ";
			mail_html($email, '', "$user activate mail", $message);
			print "mail to $email for activate, please click the link in the mail<br>";
		}
	}
}
?>
