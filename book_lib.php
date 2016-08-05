
<?php
function dprint($str)
{
	global $debug;
	if($debug)
		print($str);
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
	list_record($login_id, 2);
}

function list_record($login_id, $role=1)
{
	print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 800px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');
	print_tdlist(array('id', 'borrower', 'name','adate', 'bdate', 'rdate','sdate', 'status', 'action'));
	if($role == 2)
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status & 0xff != 0 and t3.user = t1.borrower";
	else
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.borrower='$login_id' and t1.book_id = t2.book_id and t3.user = t1.borrower";
	if($login_id == 'all')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status = 0 and t3.user = t1.borrower";
	
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
		if($role != 2){
			$adate = substr($adate, 0, 10);
			$bdate = substr($bdate, 0, 10);
			$rdate = substr($rdate, 0, 10);
			$sdate = substr($sdate, 0, 10);
		}
		$status = $row['status'];
		if($role == 2){
			$blink = "";
			if($status == 1){
				$status_text = "Applying";
				$blink = "<a href=\"book.php?record_id=$record_id&action=lend\">Approve</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=stock\">Reject</a>";
			}else if($status == 2){
				$status_text = "Out";
				$blink = "<a href=\"book.php?record_id=$record_id&action=push\">Push</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=stock\">Stock</a>";
			}else if($status == 3){
				$status_text = "Returning";
				$blink = "<a href=\"book.php?record_id=$record_id&action=stock\">Approve</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=lend\">Reject</a>";
			}else if($status == 4){
				$status_text = "Waiting";
				$blink = "<a href=\"book.php?record_id=$record_id&action=lend\">Approve</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=reject_wait\">Reject</a>";
			}else if($status == 0){
				$status_text = "Returned";
			}else{
				$status_text = "Cancel";
			}
		}else if($role == 1){
			$blink = "";
			if($status == 1){
				$status_text = "Borrowing";
			}else if($status == 4){
				$status_text = "Waiting";
				$blink = "<a href=\"book.php?record_id=$record_id&action=cancel\">Cancel</a>";
			}else if($status == 2){
				$status_text = "Out";
				$blink = "<a href=\"book.php?record_id=$record_id&action=returning\">Return</a>";
			}else if($status == 3){
				$status_text = "Returning";
				$blink = "";
			}else if($status == 0){
				$status_text = "Returned";
			}else{
				$status_text = "Cancel";
			}
		}
		print_tdlist(array($record_id, $borrower, $name, $adate, $bdate, $rdate,$sdate, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");
}

function list_book($format='')
{
	global $login_id;
	print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 1000px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');
	print_tdlist(array('id', 'name','author', 'ISBN','index','price','buy_date', 'status', 'action'));
	$sql = " select * from books";

	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id']; 
		$name= $row['name'];
		$author= $row['author'];
		$author = substr($author, 0, 32);
		$isbn= $row['ISBN'];
		$index= $row['index'];
		$price= $row['price'];
		$buy_date= substr($row['buy_date'], 0, 10);
		$status=$row['status'];	
		if($status != 0){
			$status_text = "Out";
			$status_text = "<a href=book.php?action=show_borrower&book_id=\"$book_id\">Out</a>";
			$blink = "<a href=book.php?action=wait&book_id=\"$book_id\">Wait</a>";
		}else{
			$status_text = "Stock";
			$blink = "<a href=book.php?action=borrow&book_id=\"$book_id\">Borrow</a>";
		}
		if($name == "TBD" || $name == "")
			continue;
		print("<tr>");
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

function borrow_book($book_id, $login_id)
{
	global $max_books;
	if(!check_record($book_id, $login_id))
		return false;
	$sql = " select * from history where borrower='$login_id' and status & 0xff !=0 and status != 3";
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

function show_borrower($book_id)
{
	print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');

	print_tdlist(array('id', 'book','person','date', 'status'));

	$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status != 0 order by `adate` asc";

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
	$sql = " select * from history where borrower='$login_id' and book_id=$book_id and status & 0xff !=0";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		print ("You already borrowed this book!");
		return false;
	}
	return true;
}

function add_record($book_id, $login_id, $status=1)
{
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$sql = " insert into history set `borrower`='$login_id', book_id=$book_id, adate= '$time_start', status=$status";
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
	print("$sql");
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

	dprint("mail|to:$to|cc:$cc|$subject\n");
//	print("$message\n");
//	$to = 'xling@qti.qualcomm.com';
	mail($to,$subject, $message, $headers);

}
?>
	

