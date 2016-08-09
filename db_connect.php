<?php
$link=mysql_connect("10.233.140.115:3306","bookweb","book2web");
mysql_query("set character set 'utf8'");//..
mysql_query("set names 'utf8'");//.. 
#mysql_query("set character set 'gb2312'");//..
#mysql_query("set names 'gb2312'");//.. 
if($db==1)
	$db=mysql_select_db("testbook",$link);
else
	$db=mysql_select_db("book",$link);

?>
