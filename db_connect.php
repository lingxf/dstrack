<?php
$link=mysql_connect("localhost","bookweb","book2web");
mysql_query("set character set 'utf8'");//..
mysql_query("set names 'utf8'");//.. 
if($db==1)
	$db=mysql_select_db("testbook",$link);
else
	$db=mysql_select_db("book",$link);

?>
