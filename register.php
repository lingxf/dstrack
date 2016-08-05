<html>
<title>Do Quiz</title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<meta http-equiv="Content-Language" content="zh-CN" /> 
<link rel="stylesheet" type="text/css" href="report.css" media="screen12"/>
<!--
	A php that could do a quiz test based on a database
	by Ling Xiaofeng <lingxf@gmail.com>
-->
<?php
$link=mysql_connect("10.233.140.115:3306","weekly","week2pass");
#$link=mysql_connect("localhost","exam","");
$db=mysql_select_db("exam",$link);

global $login_id;	
global $show_techarea_case;

session_start();

$sid=session_id();

if(isset($_POST['email']))
	$email = $_POST['email'];
if(isset($_POST['user']))
	$user = $_POST['user'];
if(isset($_POST['password1']))
	$ps1 = $_POST['password1'];
if(isset($_POST['password2']))
	$ps2 = $_POST['password2'];
if($ps1 == $ps2)
	$sql="SELECT * FROM weekly.reporter WHERE reporter = '$user' or email = '$email';";
	$sql_ins="INSERT into weekly.reporter set reporter = '$user', email = '$email', password = '$ps1';";
	$res=mysql_query($sql) or die("Query Error:".$sql . mysql_error());
	$row=mysql_fetch_array($res);
	if($row){
		print "$user already registered";
	}else{
		$res=mysql_query($sql_ins) or die("Query Error:".$sql_ins . mysql_error());
		$row=mysql_affected_rows($link);
		print "$user is added successful";
    	header("Location: user_register.php");
	}
?>
</body>
</html>
