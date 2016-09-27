<?php
function get_client_ip(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
		$ip = $_SERVER['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return($ip);
}

function strip($str){
	$reg = "/^\s*([^\s][^\s\r\n]*)\s*$/";
	if(preg_match($reg, $str, $match))
		return $match[1];
	return $str;
}

function read_mysql_query($sql)
{
	($res = mysql_query($sql)) or die("Invalid read query:" . $sql ."<br>\n". mysql_error());
	return $res;
}

function update_mysql_query($sql)
{
	($res = mysql_query($sql)) or die("Invalid update query:" . $sql . "<br>\n" .mysql_error());
	return $res;
}

function update_mysql_query2($sql)
{

	$link=mysql_connect("localhost","bookweb","book2web");
	mysql_query("set character set 'utf8'");//..
	mysql_query("set names 'utf8'");//.. 
	$db=mysql_select_db("testbook",$link);
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());

	$link=mysql_connect("cedump-sh.ap.qualcomm.com","bookweb","book2web");
	$db=mysql_select_db("testbook",$link);
	mysql_query("set character set 'utf8'");//..
	mysql_query("set names 'utf8'");//.. 
}

?>
