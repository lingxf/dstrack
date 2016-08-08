
<?php
function dprint($str)
{
	global $debug;
	if($debug)
		print($str);
}
function print_td($text, $width='', $color='', $background='', $script='')
{
    $td = "<td width=$width style='width:$width pt;".
		"background:$background;" .
		"color:$color;" .
		"padding:0cm 5.4pt 0cm 5.4pt;height:33.0pt' $script>";
	$td .= "$text";
	$td .= "</td>";
	print $td;
}

function print_tdlist($tdlist)
{
	foreach($tdlist as $tdc)
	{
		print("<td>$tdc</td>"); 
	}
}

function manage_record()
{
	global $login_id;
	list_record($login_id, 'approve');
}

function out_record()
{
	global $login_id;
	list_record($login_id, 'out');
}

function list_record($login_id, $format='self')
{
	$table_name = "id_table_record";
	$tr_width = 800;
	$background = '#cfcfcf';
	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	print_tdlist(array('序号','借阅人', '书名','申请日期', '借出日期', '回还日期','入库日期', '状态', '操作'));
	if($format == 'approve')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status < 0x100 and t1.status != 0 and t1.status != 2 and t3.user = t1.borrower order by adate asc";
	else if($format == 'self')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.borrower='$login_id' and t1.book_id = t2.book_id and t3.user = t1.borrower order by adate desc ";
	else if($format == 'out')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status  = 2 and t3.user = t1.borrower order by bdate asc";
	else if($format == 'history')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status = 0 and t3.user = t1.borrower order by rdate desc ";
	
	$i = 0;
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		print("<tr>");
		$record_id = $row['record_id']; 
		$borrower = $row['user_name']; 
		$book_id = $row['book_id']; 
		$name = $row['name']; 
		$adate= $row['adate']; 
		$bdate= $row['bdate']; 
		$rdate= $row['rdate']; 
		$sdate= $row['sdate']; 
		if($format == 'self'){
			$adate = substr($adate, 0, 10);
			$bdate = substr($bdate, 0, 10);
			$rdate = substr($rdate, 0, 10);
			$sdate = substr($sdate, 0, 10);
		}
		$status = $row['status'];
		if($format == 'approve' || $role == 'out'){
			$blink = "";
			if($status == 1){
				$status_text = "申请中";
				$blink = "<a href=\"book.php?record_id=$record_id&action=lend\">批准</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=reject\">拒绝</a>";
			}else if($status == 2){
				$status_text = "借出";
				$blink = "<a href=\"book.php?record_id=$record_id&action=push\">催还</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=stock\">入库</a>";
			}else if($status == 3){
				$status_text = "归回中";
				$blink = "<a href=\"book.php?record_id=$record_id&action=stock\">入库</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=lend\">拒绝</a>";
			}else if($status == 4){
				$status_text = "等候";
				$blink = "<a href=\"book.php?record_id=$record_id&action=lend\">批准</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=reject_wait\">拒绝</a>";
			}else if($status == 0){
				$status_text = "已还";
			}else{
				$status_text = "取消";
			}
		}else if($format == 'self'){
			$blink = "";
			if($status == 1){
				$status_text = "借阅中";
			}else if($status == 4){
				$status_text = "等候";
				$blink = "<a href=\"book.php?record_id=$record_id&action=cancel\">取消</a>";
			}else if($status == 2){
				$status_text = "借出";
				$blink = "<a href=\"book.php?record_id=$record_id&action=returning\">归还</a>";
			}else if($status == 3){
				$status_text = "归还中";
				$blink = "";
			}else if($status == 0){
				$status_text = "已还";
			}else{
				$status_text = "取消";
			}
		}
		$i++;
		print_tdlist(array($i, $borrower, $name, $adate, $bdate, $rdate,$sdate, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");
}

function list_book($format='normal')
{
	global $login_id, $role;

	$table_name = "book";
	$tr_width = 800;
	$background = '#efefef';
	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	if($format == 'normal')
		print_tdlist(array('编号', '书名','作者', '描述','评论','状态', '操作'));
	else
		print_tdlist(array('id', 'name','author', 'ISBN','index','price','buy_date', 'status', 'action'));
	$sql = " select * from books order by book_id asc";

	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id']; 
		$name= $row['name'];
		$author= $row['author'];
		$author = substr($author, 0, 64);
		$isbn= $row['ISBN'];
		$index= $row['index'];
		$price= $row['price'];
		$buy_date= substr($row['buy_date'], 0, 10);
		$desc =  $row['desc'];
		$desc = substr($desc, 0, 300);
		$comments=  $row['comments'];
		$sc_desc = "ondblclick='show_edit_col(this,$book_id,1)'";
		$sc_comments = "ondblclick='show_edit_col(this,$book_id,2)'";

		$status=$row['status'];	
		if($status != 0){
			$status_text = "Out";
			$status_text = "<a href=book.php?action=show_borrower&book_id=\"$book_id\">借出</a>";
			$blink = "<a href=book.php?action=wait&book_id=\"$book_id\">等候</a>";
		}else{
			$status_text = "<a href=book.php?action=show_borrower&book_id=\"$book_id\">";
			$status_text .= "在库";
			$status_text .= "</a>";
			$blink = "<a href=book.php?action=borrow&book_id=\"$book_id\">借阅</a>";
		}
		if($name == "TBD" || $name == "")
			continue;
		$bcolor = 'white';
		if($status != 0)
			$bcolor = '#efcfef';
		print("<tr style='background:$bcolor;'>");
		if($format == 'normal'){
			print_td($book_id,10);
			print_td($name,200);
			print_td($author,150);
			print_td($desc,'','','',$sc_desc);
			print_td($comments, '150','','',$sc_comments);
			print_td($status_text,35);
			print_td($blink,35);
		}else
			print_tdlist(array($book_id, $name, $author, $isbn, $index, $price, $buy_date, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");
}

function is_member($login_id)
{
	global $max_books;
	$sql = "select * from member where user=\"$login_id\"";
	$res = mysql_query($sql) or die("Invalid query:".$sql.mysql_error());
	if($row = mysql_fetch_array($res)){
		$role= $row['role'];
		$max_books= $row['max'];
		return $role;
	}
	return 0;
}

function wait_book($book_id, $login_id)
{
	if(!check_record($book_id, $login_id))
		return false;
	add_record($book_id, $login_id, 4);
	print("Add to waiting list successfully<br>");
	return true;
}

function get_user_id($user_name)
{
	$sql = " select * from member";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$name= $row['user_name'];
		$id=$row['user'];
		$nn = explode(', ', $user_name);
		$n = count($nn);
		$mm = explode(' ', $name);
		$m = count($mm);
		//if($nn[0] == $mm[$m-1] && $nn[$n-1] == $mm[0]){
		if($nn[0] == $mm[$m-1]){
			$str1 = $mm[0];
			$str2 = str_replace(" ", '', $nn[1]);
			//print("$nn[0] -- |$nn[1]|$mm[0]|$str2|<br>");
			if(!strcasecmp($str1,$str2)){
				//print("$user_name|$name|$id:");
				return $id;
			}
		}
	}
	//print("$user_name not matched:");
	return '';
}
function migrate_record($login_id)
{
	global $max_books;
	$sql = " select book_id,bdate,rdate,t1.name, t1.user_name from old_record t1, books t2 where t1.index = t2.index ";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id'];
		$name= $row['name'];
		$bdate = $row['bdate'];
		$rdate = $row['rdate'];
		$user_name= $row['user_name'];
		if($rdate == '')
			$status = 2;
		else
			$status = 0;
		$id = get_user_id($user_name);
		if($id == '')
			print(" $user_name, $book_id, $name, $bdate, $rdate<br>");
		else{
			add_record_full($book_id, $id, $bdate, $rdate, $status);
			set_book_status($book_id, $status);
		}
	}
}

function borrow_book($book_id, $login_id)
{
	global $max_books;
	if(!check_record($book_id, $login_id))
		return false;
	$sql = " select * from history where borrower='$login_id' and status < 0x100 and status !=0 and status != 3";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$rows = mysql_num_rows($res);
	if($rows >= $max_books){
		print ("You already reached the maximum books!");
		return false;
	}
	add_record($book_id, $login_id, 1);
	set_book_status($book_id, 1);
	return true;
}

function get_bookname($book_id)
{
	$sql = " select * from books where book_id=$book_id";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$bookname = $row['name'];
		return $bookname;
	}
	return '';
}

function read_book_column($book_id, $col)
{

	$sql = "select * from books where `book_id`=$book_id";
	$res1=mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row1=mysql_fetch_array($res1)){
		$tt = $row1["$col"];
		return $tt;
	}
	return false;
}

function show_book($book_id)
{
	$sql = " select * from books where book_id=$book_id";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$desc= $row['desc'];
		$comments= $row['comments'];
		print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');
		print("<tr><td>$desc</td></tr>");
		print("</table>");
		print("评论<br>");
		print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');
		print("<tr><td>$comments</td></tr>");
		print("</table>");
		return;
	}
}

function get_borrower($book_id)
{

	$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status != 0 order by `adate` asc";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$borrower = $row['borrower'];
		return $borrower;
	}
	return '';
}

function show_borrower($book_id, $format="wait")
{
	print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');

	print_tdlist(array('序号', '书名','借阅人','日期', '状态'));
	if($format == 'out')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status != 0 and t1.status != 4 and t1.status < 0x100 order by `adate` asc";
	else if($format == 'wait')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status = 4 order by `adate` asc";
	else
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status = 0 order by `adate` asc";

	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id']; 
		$name= $row['name'];
		$user_name= $row['user_name'];
		$status=$row['status'];	
		if($status == 4){
			$status_text = "Waiting";
			$date = $row['adate'];
		}else if($status == 2){
			$status_text = "Own";
			$date = $row['bdate'];
		}else if($status == 1){
			$status_text = "Borrowing";
			$date = $row['adate'];
		}else if($status == 3){
			$status_text = "Returning";
			$date = $row['rdate'];
		}else if($status == 0){
			$status_text = "Returned";
			$date = $row['rdate'];
		}else
			continue;
		print("<tr>");
		print_tdlist(array($book_id, $name, $user_name,$date, $status_text)); 
		print("</tr>\n");
	}
	print("</table>");
}

function check_record($book_id, $login_id)
{
	global $role;
	if($role == 0){
		print("You are not a member!");
		return false;
	}
	$sql = " select * from history where borrower='$login_id' and book_id=$book_id and status < 0x100 and status !=0";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		print ("You already borrowed this book!");
		return false;
	}
	return true;
}

function add_record($book_id, $user_id, $status=1)
{
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$sql = " insert into history set `borrower`='$user_id', book_id=$book_id, adate= '$time_start', status=$status";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	return true;
}

function add_record_full($book_id, $user_id, $bdate, $sdate, $status=1)
{
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$sql = " insert into history set `borrower`='$user_id', book_id=$book_id, adate='$bdate', bdate= '$bdate', rdate='$sdate', sdate='$sdate', status=$status";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	return true;
}

function get_bookid_by_record($record_id)
{
	$sql = " select * from history where `record_id` = $record_id";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		$book_id = $row['book_id'];
		return $book_id;
	}
	return 0;
}

function set_record_status($record_id, $status)
{
	dprint("set_record_status:$record_id $status");
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	if($status == 2)
		$sql = " update history set bdate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 3)
		$sql = " update history set rdate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 0)
		$sql = " update history set sdate= '$time_start', status=$status where `record_id` = $record_id";
	else
		$sql = " update history set sdate= '$time_start', status=$status where `record_id` = $record_id";
	dprint("$sql");
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($status < 0x100){
		$book_id = get_bookid_by_record($record_id);
		set_book_status($book_id, $status);
	}
}

function set_book_status($book_id, $status)
{
	$sql = "update books set `status` = $status where book_id=$book_id";
	$res = mysql_query($sql) or die("Invalid query:".$sql.mysql_error());
	$rows = mysql_affected_rows();
	if($rows != 0){
		return true;
    }
	return false;
}

function check_passwd($login_id, $login_passwd){

	$sql1="SELECT * FROM weekly.reporter WHERE reporter = '$login_id';";
	$res1=mysql_query($sql1) or die("Query Error:" . mysql_error());
	$row1=mysql_fetch_array($res1);
	if(!$row1)
		return 1;
	if($row1['password'] == "")
		return 0;
    if($row1['password'] == $login_passwd)
        return 0;
	$sql1="SELECT * FROM weekly.reporter WHERE reporter = '$login_id' and password=ENCRYPT('$login_passwd', 'ab');";
	$res1=mysql_query($sql1) or die("Query Error:" . mysql_error());
	$row1=mysql_fetch_array($res1);
	if(!$row1)
		return 2;
//	$passwd = crypt($login_passwd);
	return 0;
}

function get_user_attr($user, $prop) {
	$sql1 = "select * from weekly.reporter where reporter='$user'";
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_fetch_array($res1))
		return $row1["$prop"];
	return false;
}

function home_link($str="Home", $action='', $more=''){
	if($action!='')
		print("<a href=\"book.php?action=$action\">$str</a>" . $more);
	else
		print("<a href=\"book.php\">$str</a>" . $more);
}

function check_login(){
	global $login_id;
	session_start();
	if(isset($_SESSION['user'])) $login_id=$_SESSION['user'];
	else{
		print("You are not login!");
		exit();
	}
}
function get_admin_mail()
{
	$cc = '';
//	$cc = "yingwang@qti.qualcomm.com;";
	$cc .= "xling@qti.qualcomm.com";
	return $cc;
}
function mail_html($to, $cc, $subject, $message)
{
	$headers = 'From: weekly@cedump-sh.ap.qualcomm.com' . "\r\n" .
	    'Reply-To: xling@qti.qualcomm.com' . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	if($cc)
		$headers .= "Cc: $cc" . "\r\n";
	$headers .= "Bcc: xling@qti.qualcomm.com" . "\r\n";

	dprint("mail|to:$to|cc:$cc|$subject<br>\n");
//	print("$message\n");
//	$to = 'xling@qti.qualcomm.com';
	mail($to,$subject, $message, $headers);

}
?>
	

