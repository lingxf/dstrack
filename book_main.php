<?php
/*
   copyright Xiaofeng(Daniel) Ling<lingxf@gmail.com>, 2016, Aug.
 */

include 'debug.php';
$home_page = 'book_main.php';
session_set_cookie_params(7*24*3600);
session_name($web_name);
session_start();
setcookie('username',session_name(),time()+3600);    //创建cookie
if(isset($_COOKIE["username"])){    //使用isset()函数检测cookie变量是否已经被设置
	$username = $_COOKIE["username"];    //您好！nostop     读取cookie 
}else{
	$username = '';
}

include 'db_connect.php';
include_once 'myphp/common.php';
include_once 'myphp/disp_lib.php';
include 'book_lib.php';
global $login_id, $max_book, $setting;	
include_once 'myphp/login_action.php';


?>

<html>
<title>Book</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="zh-CN" /> 
<link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">
<style type="text/css">
.menu_button {
    background-color: #cee;
    border: medium none;
    color: inherit;
    cursor: pointer;
    display: inline-block;
    overflow: hidden;
    padding: 2px 8px;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    white-space: nowrap;
}

.menu_button:hover {
    background-color: #888 !important;
	    color: #000 !important;
}
</style>

<script src="jquery-3.3.1.min.js"></script>
<script src="jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="inpage_edit.js"></script>
<!--
-<link rel="stylesheet" type="text/css" href="report.css" media="screen12"/>
	A php that could manage book library 
	by Ling Xiaofeng <lingxf@gmail.com>
-->
<style type="text/css">
@media screen {
	.print_ignore {
display: none;
	}

	body, table, th, td {
		font-size:         12pt;
	}

	table, th, td {
		border-width:      1px;
		border-color:      #0000f0;
		border-style:      solid;
	}
	th, td {
padding:           0.2em;
	}
}
</style>
<body onload="load_intro()">
<script type="text/javascript" src='book_main.js'></script>
<script type="text/javascript">
$(function(){
	$("#tabs").removeAttr("hidden");
	$("#tabs").tabs({
		beforeLoad:function(event, ui){
		}
	});
	tabid = "div_main";
	$("#tabs").tabs("option", "active", 1);

/*
	url = "book.php";
	div_main = $("div#tabs_main" + " p");
	div_main.html("Loading...");
	div_main.load(url, function(response, status, xhr){
		if(status == "success"){
			on_tab_load(tabid);
		}
	});
	for(i = 0; i < 10; i++){
		href = $("#tabs a:eq("+i+")").attr("href");
		if(tabid == href.replace("#", "" )){
			$("#tabs").tabs("option", "active", i);
		}
	}
*/
	function on_tab_load(name){
		console.log('tab loaded');
		$(".book_name").click(function(){
			book_id = $(this).attr("book_id");
			console.log("link click hook");
			url = "book.php?action=show_borrower&book_id="+book_id;
			div_main = $("div#tabs_main" + " p");
			div_main.load(url, function(response, status, xhr){
				if(status == "success"){
				}
			});
		});
		return false;
		$("input.button").click(function(){
			console.log('click');
			return false;
			url = $(this).attr("href");
			console.log(url);
			text = $(this).val();
			console.log(text);
			div_main = $("div#tabs_main" + " p");
			div_main.html("Loading...");
			div_main.load(url, function(response, status, xhr){
				if(status == "success"){
				}
			});
			return false;
	
		});
	}
	$("#tabs a[href]").click(function(){
		hf = $(this).attr("action");
		text = $(this).text();
		console.log(text);
		url = hf.replace('#', '');
		div_main = $("div#tabs_main" + " p");
		div_main.html("Loading...".url);
		div_main.load(url, function(response, status, xhr){
			console.log(url);
			if(status == "success"){
				on_tab_load(text);
			}
		});

	});

})


</script>

<?php
global $login_id, $max_book, $setting;	

$max_books = 1;
$user_groups = 0;
$role = is_member($login_id, $user_groups);
$role_city = get_user_attr($login_id, 'city');
$role_city = $role_city ? $role_city:0;
$disp_city = 0;
$action="home";
if(isset($_GET['action']))$action=$_GET['action'];

if($role == 2)
	$role_text = "管理员";
else if($role == 1)
	$role_text = "会员";
else if($role == 0)
	$role_text = "非会员";
else if($role == -1)
	$role_text = "未激活";
else if($role == -2)
	$role_text = "访客";

if($login_id == 'guest')
	$login_text = "<li><a class='login_button' id='id_login_name' href=?action=show_login>登录</a></li><li><a href=\"?action=show_register\">注册</a></li>";
else
	$login_text = "<li><a class='login_button' href=book_user_setting.php>$login_id($role_text)</a></li><li><a class='login_button' href=\"?action=logout&url=book.php\">注销</a></li>";

//$login_text .= "&nbsp;&nbsp;".get_city_name($city);

$book_id=0;

?>
<div hidden id="tabs">
  <ul>
	<li><a  href="#tabs_main" action="book.php">首页</a></li>
	<li><a  href="#tabs_main" action="book.php?action=library">书库</a></li>
	<li><a  href="#tabs_main" action="book.php?action=admin">贡献</a></li>
	<li><a  href="#tabs_main" action="book.php?action=list_favor">我的</a></li>
	<li><a  href="#tabs_main" action="book.php?action=list_share">分享</a></li>
	<li><a  href="#tabs_main" action="book.php?action=list_comments_all">最新评论</a></li>
	<li><a  href="#tabs_main" action="book.php?action=list_recommend">推荐/兑换</a></li>
	<li><a  href="#tabs_main" action="book.php?action=list_out">借出</a></li>
	<li><a  href="#tabs_main" action="book.php?action=history">借阅历史</a></li>
	<li><a  href="#tabs_main" action="book.php?action=list_timeout">超时</a></li>
	<li><a  href="#tabs_main" action="book.php?action=list_statistic">统计</a></li>
	<li><a  href="#tabs_main" action="book.php?action=list2">List</a></li>
<?php
if($role >= 2){
print("
	<li><a  class='menu_button' href='book.php?action=manage'>管理</a></li>
	<li><a  class='menu_button' href='book.php?action=log'>日志</a></li>
	<li><a  class='menu_button' href='book.php?action=list_member'>会员</a></li>
	<li><a  class='menu_button' href='book.php?action=add_newbook'>新书</a></li>
	<li><a  class='menu_button' href='book.php?action=list_tbd'>待定</a></li>
	");
}
?>	
<?php
	print($login_text);
?>
  </ul>
<div id='tabs_main'> <p> </p> </div>
</div>
</body>
</html>
