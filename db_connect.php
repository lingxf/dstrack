<?php
if(isset($server))
	$link=mysql_connect($server,"bookweb","book2web");
else
	$link=mysql_connect("localhost","bookweb","book2web");

mysql_query("set character set 'utf8'");//..
mysql_query("set names 'utf8'");//.. 
if(isset($db) && $db==1){
	$db=mysql_select_db("testbook",$link);
}else if($db==2){
	$db=mysql_select_db("szbook",$link);
}else
	$db=mysql_select_db("book",$link);

?>
