
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
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status != 0 and t3.user = t1.borrower";
	else
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.borrower='$login_id' and t1.book_id = t2.book_id and t3.user = t1.borrower";
	if($login_id == 'all')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status=0 and t3.user = t1.borrower";
	
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
		$status = $row['status'];
		dprint("$login_id $status<br>");
		if($role == 2){
			$blink = "";
			if($status == 1){
				$status_text = "Applying";
				$blink = "<a href=\"book.php?record_id=$record_id&action=approve\">Approve</a>";
			}else if($status == 2){
				$status_text = "Out";
				$blink = "<a href=\"book.php?record_id=$record_id&action=push\">Push</a>";
			}else if($status == 3){
				$status_text = "Returning";
				$blink = "<a href=\"book.php?record_id=$record_id&action=stock\">Approve</a>";
			}else if($status == 4){
				$status_text = "Waiting";
				$blink = "<a href=\"book.php?record_id=$record_id&action=approve\">Approve</a>";
			}else{
				$status_text = "Stock";
				$blink = "";
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
			}else{
				$status_text = "Stock";
				$blink = "";
			}
		}
		print_tdlist(array($record_id, $borrower, $name, $adate, $bdate, $rdate,$sdate, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");
}




function list_book($format='')
{
	global $lgoin_id;
	print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 1000px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');
	$borrow = "<a href=\"book.php?user=$login_id&action=new\">new</a>";
	print_tdlist(array('id', 'name','author', 'ISBN','index','price','buy_date', 'status', $borrow));
	$sql = " select * from books";

//	$sql = " select * from weekly.importcase";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
	//	$prog = $row['Latest Qualcomm Progress Update'];
//		$prog = $row['Subject'];
	//	print $prog;
		$book_id = $row['book_id']; 
		$name= $row['name'];
		$author= $row['author'];
		$author = substr($author, 0, 32);
		$isbn= $row['ISBN'];
		$index= $row['index'];
		$price= $row['price'];
		$buy_date= $row['buy_date'];
		$blink = "<a href=book.php?action=borrow&book_id=\"$book_id\">Borrow</a>";
		if($status & 1)
			$status_text = "Out";
		else
			$status_text = "Stock";
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

function get_quiz_attribute($quiz_id, $attr)
{
	$sql = "select * from quiz where quiz_id=$quiz_id";
	$res = mysql_query($sql) or die("Invalid query:".$sql.mysql_error());
	if($row = mysql_fetch_array($res)){
		$status = $row[$attr];
		return $status;
    }
	return -1;
}

function wait_book($book_id, $login_id)
{
	borrow_book($book_id, $login_id, 4);
}
function borrow_book($book_id, $login_id, $status=1)
{
	global $role, $max_books;
	dprint("book:$book_id, login:$login_id, status:$status");
	if($role == 0){
		print("You are not a member!");
		return false;
	}
	$sql = " select * from history where borrower='$login_id' and book_id=$book_id and status!=0";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		print ("You already borrowed this book!");
		return false;
	}

	$sql = " select * from history where borrower='$login_id' and status!=0";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$num_rows = mysql_num_rows($res);
	if($num_rows >= $max_books){
		print ("You has reached maximum books you can borrow!");
		return false;
	}

	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$sql = " insert into history set `borrower`='$login_id', book_id=$book_id, adate= '$time_start', status=$status";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	return true;
}

function set_book_status($record_id, $status)
{
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	if($status == 2)
		$sql = " update history set bdate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 3)
		$sql = " update history set rdate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 0)
		$sql = " update history set sdate= '$time_start', status=$status where `record_id` = $record_id";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
}

function set_quiz_attribute($quiz_id, $attr, $value)
{
	$sql = "update quiz set `$attr` = $value where quiz_id=$quiz_id";
	$res = mysql_query($sql) or die("Invalid query:".$sql.mysql_error());
	if($row = mysql_fetch_array($res)){
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
?>
