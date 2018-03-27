<?php
include 'debug.php';
$home_page='book.php';
session_set_cookie_params(7*24*3600);
if($web_name != session_name($web_name))
	session_start();

include_once 'myphp/common.php';
include_once 'db_connect.php';

include_once 'myphp/login_action.php';
include_once 'myphp/data_action_simple.php';

