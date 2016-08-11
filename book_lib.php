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
	if($format == 'approve'){
		print_tdlist(array('序号','借阅人', '书名','申请日期', '借出日期', '回还日期','入库日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status < 0x100 and t1.status != 0 and t1.status != 2 and t3.user = t1.borrower order by adate asc";
	}else if($format == 'self'){
		print_tdlist(array('序号','借阅人', '书名','申请日期', '借出日期', '回还日期','入库日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.borrower='$login_id' and t1.book_id = t2.book_id and t3.user = t1.borrower order by adate desc ";
	}else if($format == 'out'){
		print_tdlist(array('序号','借阅人', '书名','申请日期', '借出日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status  = 2 and t3.user = t1.borrower order by bdate desc";
	}else if($format == 'history'){
		print_tdlist(array('序号','借阅人', '书名','申请日期', '借出日期', '回还日期','入库日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status = 0 and t3.user = t1.borrower order by rdate desc ";
	}	

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
		if($format == 'approve' || $format == 'out'){
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
				$status_text = "归还中";
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
		if($format == 'out')
			print_tdlist(array($i, $borrower, $name, $adate, $bdate, $status_text, $blink)); 
		else
			print_tdlist(array($i, $borrower, $name, $adate, $bdate, $rdate,$sdate, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");
}

function list_book($format='normal', $start=0, $items=50)
{
	global $login_id, $role, $class;

	$table_name = "book";
	$tr_width = 800;
	$background = '#efefef';

    $hasmore = false;
    $hasprev = false;
	$sql = "select * from books";
	if($class != 100)
		$sql .= " where class = $class ";
	$res1 = mysql_query($sql) or die("Invalid query:" .$sql. mysql_error());
	$rows = mysql_num_rows($res1);
	print("$start, $rows, $items");
	if($start >= $rows){
		$start = $rows - $items;
        if($start < 0)
            $start = 0;
        $_SESSION['start'] = $start;
	}

	print("$start, $rows, $items");

	$ns = $start+$items;

	if($ns < $rows){
        $hasmore = true;
    }

	if($start > 0)
        $hasprev = true;

    print('<form enctype="multipart/form-data" action="book.php" method="POST">');
	print('<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    print('<input type="submit"'); print(' name="begin" value="Begin" />   ');
    print('<input type="submit"'); if(!$hasprev) print(" disabled "); print(' name="prev" value="Prev" />   ');
    print('<input type="submit"'); if(!$hasmore) print(" disabled "); print(' name="next" value="Next" />   ');
    print('<input type="submit"');  print(' name="end" value="End" />   ');
	print('</span>');

	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	if($format == 'normal')
		print_tdlist(array('编号', '书名','作者', '描述','评论','分类', '状态', '操作'));
	else if($format == 'brief')
		print_tdlist(array('编号', '书名','作者', '状态', '操作'));
	else
		print_tdlist(array('id', 'name','author', 'ISBN','index','price','buy_date', 'status', 'action'));

	if($class == 100)
		$sql = " select * from books order by book_id asc limit $start, $items";
	else
		$sql = " select * from books where class = $class order by book_id asc limit $start, $items";

	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id']; 
		$name = $row['name'];
		if($name == "TBD" || $name == "")
			continue;
		$name = "<a href='book.php?action=show_borrower&book_id=$book_id'>$name</a>";
		$author= $row['author'];
		$author = substr($author, 0, 64);
		$isbn = $row['ISBN'];
		$index = $row['index'];
		$price = $row['price'];
		$buy_date = substr($row['buy_date'], 0, 10);
		$class =  $row['class'];
		$class_text = get_class_name($class);
		$desc =  $row['desc'];
		mb_internal_encoding("UTF-8");
		$desc = mb_substr($desc, 0, 100);
		if($desc)
			$desc .= "<a href='book.php?action=show_borrower&book_id=$book_id'>...</a>";
		$comments=  $row['comments'];
		$comments = mb_substr($comments, 0, 100);
		if($role > 0){
			$sc_desc = "ondblclick='show_edit_col(this,$book_id,1)'";
			$sc_comments = "ondblclick='show_edit_col(this,$book_id,2)'";
			$sc_class = "ondblclick='show_edit_col(this,$book_id,3)'";
		}

		$status=$row['status'];	
		if($status != 0){
			$status_text = "Out";
			$status_text = "<a href=book.php?action=show_borrower&book_id=\"$book_id\">借出</a>";
			$blink = "<a href=book.php?action=wait&book_id=\"$book_id\">等候</a>";
			$bcolor = '#efcfef';
		}else{
			$status_text = "<a href=book.php?action=show_borrower&book_id=\"$book_id\">";
			if($buy_date == '' || $buy_date == "0000-00-00"){
				$status_text .= "待购";
				$status_text .= "</a>";
				$blink = "";
				$bcolor = '#00ff80';
			}else{
				$status_text .= "在库";
				$status_text .= "</a>";
				$blink = "<a href=book.php?action=borrow&book_id=\"$book_id\">借阅</a>";
				$bcolor = 'white';
			}
		}
		if($name == "TBD" || $name == "")
			continue;
		print("<tr style='background:$bcolor;'>");
		if($format == 'normal'){
			print_td($book_id,10);
			print_td($name,200);
			print_td($author,150);
			print_td($desc,'','','',$sc_desc);
			print_td($comments, '150','','',$sc_comments);
			print_td($class_text, 35, '', '', $sc_class);
			print_td($status_text,35);
			print_td($blink,35);
		}else if($format == 'brief'){
			print_td($book_id,10);
			print_td($name);
			print_td($author);
			print_td($status_text,35);
			print_td($blink,35);
		}else
			print_tdlist(array($book_id, $name, $author, $isbn, $index, $price, $buy_date, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");

	print('<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    print('<input type="submit"'); print(' name="begin" value="Begin" />   ');
    print('<input type="submit"'); if(!$hasprev) print(" disabled "); print(' name="prev" value="Prev" />   ');
    print('<input type="submit"'); if(!$hasmore) print(" disabled "); print(' name="next" value="Next" />   ');
    print('<input type="submit"');  print(' name="end" value="End" />   ');
	print('</form');
}

function is_member($login_id)
{
	global $max_books, $setting, $items_perpage;
	$sql = "select * from member where user=\"$login_id\"";
	$res = mysql_query($sql) or die("Invalid query:".$sql.mysql_error());
	if($row = mysql_fetch_array($res)){
		$role= $row['role'];
		$max_books = $row['max'];
		$setting = $row['setting'];
		$items_perpage = $row['perpage'];
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

function get_total_books()
{
	$sql = " select * from books";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$rows = mysql_num_rows($res);
	return $rows;
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
	return -1;
}

$class_list = array('未分','小说', '历史', '技术', '科普', '社会', '传记', '管理');
function get_class_name($class=0)
{
	global $class_list;
	#print_r($namelist);
	return $class_list[$class];
}

function show_book($book_id)
{
	$sql = " select * from books where book_id=$book_id";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$desc= $row['desc'];
		$name= $row['name'];
		$id= $row['book_id'];
		$comments= $row['comments'];
		$isbn = $row['ISBN'];
		$index = $row['index'];
		$price = $row['price'];
		$buy_date= $row['buy_date'];
		$buy_date= substr($buy_date, 0, 10);
		$sponsor = $row['sponsor'];
		$class = $row['class'];
		$class_text = get_class_name($class);

		print("《" . $name . "》");
		print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');
		print("<tr>");
		print_tdlist(array('编号', 'ISBN','索引','价格','分类', 'Sponsor', '购买日期'));
		print("</tr>");
		print("<tr>");
		print_tdlist(array($id, $isbn, $index, $price, $class_text, $sponsor, $buy_date)); 
		print("</tr>");
		print("</table>");
		print("<br/>");

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

function list_log($format='normal')
{
	global $login_id, $role;

	$table_name = "log";
	$tr_width = 800;
	$background = '#efefef';
	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	if($format == 'normal')
		print_tdlist(array('日期', '操作人','编号', '书名','借阅人','动作'));
	$sql = " select * from log f1, books f2, member f3 where f1.book_id = f2.book_id and f1.member_id = f3.user order by timestamp desc";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id']; 
		$operator = $row['operator'];
		$member_id= $row['member_id'];
		$timestamp= $row['timestamp'];
		$bookname = $row['name'];
		$username = $row['user_name'];
		$status=$row['status'];	

		if($status == 0){
			$status_text = "还入";
		}else{
			$status_text = "借出";
		}
		$bcolor = 'white';
		if($status != 0)
			$bcolor = '#efcfef';
		print("<tr style='background:$bcolor;'>");
		if($format == 'normal'){
			print_tdlist(array($timestamp, $operator, $book_id, $bookname, $username, $status_text)); 
		}
		print("</tr>\n");
	}
	print("</table>");
}

function show_borrower($book_id, $format="wait")
{
	print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');

	print_tdlist(array('编号', '书名','借阅人','日期', '状态'));
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
			$status_text = "等候";
			$date = $row['adate'];
		}else if($status == 2){
			$status_text = "已借";
			$date = $row['bdate'];
		}else if($status == 1){
			$status_text = "申请中";
			$date = $row['adate'];
		}else if($status == 3){
			$status_text = "归还中";
			$date = $row['rdate'];
		}else if($status == 0){
			$status_text = "已还";
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
	session_name('book');
	session_start();
	if(isset($_SESSION['user'])) $login_id=$_SESSION['user'];
	else{
		print("You are not login!");
		exit();
	}
}
function get_admin_mail()
{
	global $debug;
	$cc = '';
	$cc = "yingwang@qti.qualcomm.com;";
	if($debug == 1)
		$cc = "xling@qti.qualcomm.com";
	return $cc;
}
function add_log($login_id, $borrower, $book_id, $status)
{
	$sql = " insert into log set `operator`='$login_id', book_id=$book_id, member_id = '$borrower', status=$status";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	return true;
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
