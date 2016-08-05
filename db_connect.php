<?php
$link=mysql_connect("10.233.140.115:3306","weekly","week2pass");
#$link=mysql_connect("localhost","exam","");
mysql_query("set character set 'utf8'");//..
mysql_query("set names 'utf8'");//.. 
$db=mysql_select_db("book",$link);
?>
