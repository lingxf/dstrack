<?php
include 'common.php';
function dprint($str)
{
	global $debug_print, $debug;
	if(isset($debug_print) && $debug_print == 1)
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
	print("&nbsp;&nbsp;申请：");
	list_record($login_id, 'approve', " (history.status = 1 or history.status = 5) ");
	print("&nbsp;&nbsp;归还：");
	list_record($login_id, 'approve', " history.status = 3 ");
	print("&nbsp;&nbsp;等候：");
	list_record($login_id, 'approve', " (history.status = 4 or history.status = 0x104) ");
	print("&nbsp;&nbsp;分享：");
	list_record($login_id, 'approve', " history.status = 0x105 ");
	print("&nbsp;&nbsp;申请入会：");
	list_record($login_id, 'member');
}

function out_record()
{
	global $login_id;
	list_record($login_id, 'out');
}
/* 0x100 cancel 
   0x101 reject
   0x105 share 
   0x104 wait
   0x106 share_done
   0x107 apply_join
   0x108 approve_member
   0x109 add score
 */
function get_book_status_name($status)
{
	$status_name = array('在库', '借阅中','借出','归还中', '待购', '续借中');
	return $status_name[$status];
}

function list_record($login_id, $format='self', $condition='')
{
	global $role;
	$table_name = "id_table_record";
	$tr_width = 800;
	$background = '#cfcfcf';
	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	if($format == 'approve'){
		print_tdlist(array('序号', '借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t3.user = t1.borrower $condition order by adate asc ";
		$sql = " select record_id, borrower, history.status, name, user_name, data, adate, bdate,rdate,sdate, history.book_id from history left join `books` as t2 using (`book_id`) left join member on member.user = history.borrower  where $condition order by adate asc ";
	}else if($format == 'self'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '操作'));
		$sql = " select record_id, borrower, history.status, books.status as bstatus, data, name, user_name, adate, bdate,rdate,sdate, history.book_id from history, books, member  where history.borrower='$login_id' and history.book_id = books.book_id and member.user = history.borrower and $condition order by adate desc ";
	}else if($format == 'score'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '评分'));
		$sql = " select record_id, borrower, history.status, books.status as bstatus, data, name, user_name, adate, bdate,rdate,sdate, history.book_id from history, books, member  where history.borrower='$login_id' and history.book_id = books.book_id and member.user = history.borrower and $condition order by adate desc ";
	}else if( $format == 'waityou'){
		print_tdlist(array('序号','等候人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '操作'));
		$book_ids = get_bookid_by_borrower($login_id);
		$sql = " select record_id, borrower, t1.status, name, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.status = 4  and t1.borrower = t3.user and t1.book_id = t2.book_id and ( 0 ";
		foreach($book_ids as $book_id){
			$sql .= " or t1.book_id=$book_id " ;
		}
		$sql .= ") ";
		$sql .= " order by adate asc ";
	}else if($format == 'out'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '借出日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, user_name, data,adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status  = 2 and t3.user = t1.borrower order by bdate desc";
	}else if($format == 'history'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期' ));
		$sql = " select record_id, borrower, t1.status, name, user_name, data,adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status = 0 and t3.user = t1.borrower order by sdate desc ";
	}else if($format == 'share'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '完成日期' ));
		$sql = " select record_id, borrower, t1.status, name, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status = 0x105 and t3.user = t1.borrower order by adate desc ";
	}else if($format == 'member'){
		print_tdlist(array('序号','帐号','申请人','申请日期', '批准日期', '操作'));
		$sql = " select record_id, borrower, t1.status, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, member t3 where t1.book_id = 0 and t1.status = 0x107 and t3.user = t1.borrower order by adate desc ";
	}else if($format == 'timeout'){	
		print_tdlist(array('序号', '借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where (t1.status = 2 or t1.status = 3 or t1.status = 5) and  t1.book_id = t2.book_id and t3.user = t1.borrower ";
		if($condition != ''){
			$condition = " (to_days(now())  - to_days(bdate)) >= $condition ";
			$sql .= "and $condition";
		}
		$sql .= " order by bdate asc";
	}

	$i = 0;
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		print("<tr>");
		$record_id = $row['record_id']; 
		$borrower_id = $row['borrower']; 
		$borrower = $row['user_name']; 
		$book_id = $row['book_id']; 
		$name = $row['name']; 
		$name = "<a href='book.php?action=show_borrower&book_id=$book_id'>$name</a>";
		$adate= $row['adate']; 
		$bdate= $row['bdate']; 
		$rdate= $row['rdate']; 
		$sdate= $row['sdate']; 
		$score = $row['data'];
		$time = time();
		$nowdate = strftime("%Y-%m-%d", $time);
		if($format == 'self'){
			$adate = substr($adate, 0, 10);
			$bdate = substr($bdate, 0, 10);
			$rdate = substr($rdate, 0, 10);
			$sdate = substr($sdate, 0, 10);
		}
		$status = $row['status'];
		$status_text = "";
		$blink = "";
		if($format == 'approve' || $format == 'out' || $format == 'timeout'){
			if($status == 1){
				$status_text = "申请中";
				$blink = "<a href=\"book.php?record_id=$record_id&action=lend\">批准</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=reject\">拒绝</a>";
			}else if($status == 2){
				$status_text = "借出";
				$blink = "<a href=\"book.php?record_id=$record_id&action=push\">催还</a>";
				if(substr($bdate, 0, 10) == $nowdate)
					$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=stock\">入库</a>";
				else
					$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=stock\">入库</a>";
			}else if($status == 3){
				$status_text = "归还中";
				$blink = "<a href=\"book.php?record_id=$record_id&action=stock\">入库</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=reject_return\">拒绝</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=push\">催还</a>";
			}else if($status == 4 || $status == 0x104 ){
				$status_text = "等候";
				$blink = "<a href=\"book.php?record_id=$record_id&action=lend\">批准</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=reject_wait\">拒绝</a>";
			}else if($status == 5){
				$status_text = "续借";
				$blink = "<a href=\"book.php?record_id=$record_id&action=approve_renew\">批准</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=reject_wait\">拒绝</a>";
			}else if($status == 0x105){
				$status_text = "分享";
				$blink = "<a href=\"book.php?record_id=$record_id&action=share_done\">完成</a>";
			}else if($status == 0){
				$status_text = "已还";
			}else{
				$status_text = "取消";
			}
			if($role < 2)
				$blink = '';
		}else if($format == 'self'){
			$bstatus = $row['bstatus'];
			if($status == 0){
				$status_text = "已还";
				$url = "book.php?action=list_favor";
				$blink = "<a href='javascript:add_score(this,$book_id)'>评分</a>";
				$blink .= "&nbsp;<a href='javascript:show_share_choice(this,$book_id)'>分享</a>";
			}else if($status == 1){
				$status_text = "借阅中";
			}else if($status == 2){
				$status_text = "借出";
				$blink = "<a href=\"book.php?record_id=$record_id&action=returning\">归还</a>";
				if(!check_wait($book_id))
					$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=renew\">续借</a>";
			}else if($status == 3){
				$status_text = "归还中";
				$blink = "";
			}else if($status == 4 || $status == 0x104){
				$status_text = get_book_status_name($bstatus);
				$blink = "<a href=\"book.php?record_id=$record_id&action=cancel\">取消</a>";
				if($bstatus == 0)
					$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=borrow\">借阅</a>";
			}else if($status == 5){
				$status_text = "续借中";
				#$blink = "<a href=\"book.php?record_id=$record_id&action=cancel\">取消</a>";
			}else if($status == 0x100){
				$status_text = "取消";
			}else if($status == 0x101){
				$status_text = "拒绝";
			}else{
				$status_text = "其它";
			}
		}else if($format == 'waityou'){
			if($status == 4){
				$status_text = "等候";
				$blink = "<a href=\"book.php?record_id=$record_id&action=transfer\">转移</a>";
			}
		}else if($format == 'member'){
			if($status == 0x107){
				$status_text = "申请";
				$blink = "<a href=\"book.php?record_id=$record_id&borrower=$borrower_id&action=approve_member\">批准</a>";
			}
		}

		$i++;

		if($format == 'out')
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $bdate, $status_text, $blink)); 
		else if($format == 'history')
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $bdate, $rdate,$sdate)); 
		else if($format == 'share')
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $sdate)); 
		else if($format == 'member')
			print_tdlist(array($i,$borrower_id, $borrower,$adate, $sdate, $blink)); 
		else if($format == 'score')
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $bdate, $rdate,$sdate, get_book_status_name($row['bstatus']), $score)); 
		else
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $bdate, $rdate,$sdate, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");
}

function list_member()
{

	global $login_id, $role, $class, $comment_type, $book_sname;

	$table_name = "book";
	$tr_width = 500;
	$background = '#efefef';

	$hasmore = false;
	$hasprev = false;

	print('<form enctype="multipart/form-data" action="book.php" method="POST">');

	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	print_tdlist(array('序号', '帐号', '姓名','邮件', '身份','已借','曾借', '操作'));

	$sql = "  select user,user_name, email, role,  ";
	$sql .= "COUNT( CASE WHEN `status` = 0 THEN 1 ELSE NULL END ) AS `books_his`,  COUNT( CASE WHEN `status` = 2 THEN 1 ELSE NULL END ) AS `books_borrow`";
	$sql .= " from `member` left join `history` on member.user = history.borrower group by user ";

	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$i = 0;
	while($row=mysql_fetch_array($res)){
		$user_id = $row['user']; 
		$user_name = $row['user_name'];
		$email = $row['email'];
		$role = $row['role'];
		$books = $row['books_borrow'];
		$books_his = $row['books_his'];
		if($role >= 1) {
			$status_text = "会员";
			$blink = "<a href=book.php?action=remove_member&borrower=$user_id>离会</a>";
		} else { 
			$status_text = "非会员";
			$blink = "<a href=book.php?action=approve_member&borrower=$user_id>入会</a>";
		}
		print("<tr>\n");
		$i++;
		print_td($i,5);
		print_td($user_id,10);
		print_td($user_name, 150);
		print_td($email);
		print_td($status_text,65);
		print_td($books, 20);
		print_td($books_his, 20);
		print_td($blink,60);
		print("</tr>\n");
	}
	print("</table>");

	print('<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
	print('<input type="submit"'); print(' disabled name="begin" value="Begin" />   ');
	print('<input type="submit"'); if(!$hasprev) print(" disabled "); print(' name="prev" value="Prev" />   ');
	print('<input type="submit"'); if(!$hasmore) print(" disabled "); print(' name="next" value="Next" />   ');
	print('<input type="submit"');  print(' disabled name="end" value="End" />   ');
	print('</form');
}


function list_book($format='normal', $start=0, $items=50, $order = 0, $condition='')
{
	global $login_id, $role, $class, $comment_type, $book_sname, $favor;

	$table_name = "book";
	$tr_width = 800;
	$background = '#efefef';

	$hasmore = false;
	$hasprev = false;

	$cond = "where (name != 'TBD' and name != '') ";
	if($format == 'tbd')
		$cond = "where (name = 'TBD' or name = '') ";
	if($class == 100)
		$cond .= "";
	else
		$cond .= " and class = $class";
	if(isset($book_sname))
		$cond .= " and name like '%$book_sname%' ";

	if($comment_type != 0)
		$cond .= " and comments != '' ";

	if($order == 1){
		$sql_time = "select book_id, count( distinct history.borrower) as btimes from history left join books using (book_id) where history.status<6 group by book_id ";
		$sql = " select * from books left join ($sql_time) btime using (book_id) $cond order by btime.btimes desc"; 
	}else if($order == 2){
		$sql_time = "select book_id, round(avg( history.data),1) as score from history left join books using (book_id) where history.status = 0x109 group by book_id ";
		$sql = " select * from books left join ($sql_time) score using (book_id) $cond order by score.score desc"; 
	}else{
		$sql_time = "select book_id, count( distinct history.borrower) as btimes from history left join books using (book_id) where history.status<6 group by book_id ";
		$sql = " select * from books left join ($sql_time) btime using (book_id) $cond order by book_id asc"; 
	}

	if($condition == 'favor')
		$sql = "select * from favor left join books using (book_id) where member_id = '$login_id'";
	else if($condition == 'history')
		$sql = "select * from history left join books using (book_id) where borrower = '$login_id' and (history.status < 6) ";
	else{
		$res1 = mysql_query($sql) or die("Invalid query:" .$sql. mysql_error());
		$rows = mysql_num_rows($res1);
		if($start >= $rows){
			$start = $rows - $items;
			if($start < 0)
				$start = 0;
			$_SESSION['start'] = $start;
		}
		dprint("$start, $rows, $items");
		$ns = $start+$items;

		if($ns < $rows){
			$hasmore = true;
		}

		if($start > 0)
			$hasprev = true;

		$sql .= " limit $start, $items";

		print('<form enctype="multipart/form-data" action="book.php" method="POST">');
		print('<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		print('<input type="submit"'); print(' name="begin" value="Begin" />   ');
		print('<input type="submit"'); if(!$hasprev) print(" disabled "); print(' name="prev" value="Prev" />   ');
		print('<input type="submit"'); if(!$hasmore) print(" disabled "); print(' name="next" value="Next" />   ');
		print('<input type="submit"');  print(' name="end" value="End" />   ');
		print("&nbsp;共 $rows 本&nbsp;");
		print('</span>');
	}

	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	if($format == 'normal')
		print_tdlist(array('编号', '书名','作者', '描述','评论','分类', '状态', '操作'));
	else if($format == 'brief'){
		if($order == 1 || $order == 0)
			print_tdlist(array('编号', '书名','作者','分类','次数','状态', '操作'));
		else if($order == 2)
			print_tdlist(array('编号', '书名','作者','分类','评分','状态', '操作'));
	}else if($format == 'class')
		print_tdlist(array('编号', '书名','作者','描述','推荐人','中图分类','咱分类','状态', '操作'));
	else if($format == 'tbd')
		print_tdlist(array('编号', '书名','作者','描述','推荐人','中图分类','咱分类','状态', '操作'));
	else
		print_tdlist(array('id', 'name','author', 'ISBN','index','price','buy_date','sponsor','status', 'action'));


	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id']; 
		$name = $row['name'];
		$name = "<a href='book.php?action=show_borrower&book_id=$book_id'>$name</a>";
		$author= $row['author'];
		$author = substr($author, 0, 64);
		$isbn = $row['ISBN'];
		$index = $row['index'];
		$price = $row['price'];
		$sponsor = $row['sponsor'];
		$buy_date = substr($row['buy_date'], 0, 10);
		$class =  $row['class'];
		if($order == 1)
			$data= $row['btimes'];
		else if($order == 2)
			$data= $row['score'];
		else
			$data = '';

		$class_name = get_class_name($index);
		$class_text = get_class_name($class);

		$desc =  $row['desc'];
		mb_internal_encoding("UTF-8");
		$desc = mb_substr($desc, 0, 100);
		if($desc)
			$desc .= "<a href='book.php?action=show_borrower&book_id=$book_id'>...</a>";
		$comments=  $row['comments'];
		$comments = mb_substr($comments, 0, 100);
		$sc_class = "";
		$sc_desc = "";
		if($role > 0){
			$sc_desc = "ondblclick='show_edit_col(this,$book_id,1)'";
			$sc_comments = "ondblclick='show_edit_col(this,$book_id,2)'";
			$sc_class = "ondblclick='show_edit_col(this,$book_id,3)'";
		}
		$id = $book_id;
		if($role > 1){
			$id = "<a href='book.php?action=edit_book&book_id=$book_id'>$id</a>";
		}

		$status=$row['status'];	
		if($status != 0){
			$status_text = "Out";
			$text = "借出";
			$bcolor = '#efcfef';
			if($status == 1){
				$text = "借中";
				$bcolor = '#efcf2f';
			}else if($status == 3){
				$text = "还中";
				$bcolor = '#ef2fef';
			}
			$status_text = "<a href=book.php?action=show_borrower&book_id=\"$book_id\">$text</a>";
			$blink = "<a href=book.php?action=wait&book_id=\"$book_id\">等候</a>";
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
				$blink = "<a href=book.php?action=borrow&book_id=$book_id>借阅</a>";
				$bcolor = 'white';
			}
		}
		$blink .= "&nbsp;<a href='javascript:show_share_choice(this,$book_id);' >分享</a>";
		$blink .= "&nbsp;<a href='javascript:add_score(this,$book_id);' >评分</a>";
		#$blink .= "&nbsp;<a href='book.php?action=share&book_id=$book_id' >分享</a>";
		print("<tr style='background:$bcolor;'>");
		if($format == 'normal'){
			print_td($id,10);
			print_td($name,200);
			print_td($author,150);
			print_td($desc,'','','',$sc_desc);
			print_td($comments, '150','','',$sc_comments);
			print_td($class_text, 35, '', '', $sc_class);
			print_td($status_text,35);
			print_td($blink,80);
		}else if($format == 'class' || $format == 'tbd'){
			print_td($id,10);
			print_td($name);
			print_td($author);
			print_td($desc,'','','',$sc_desc);
			print_td($sponsor,60);
			print_td($class_name);
			print_td($class_text, 35, '', '', $sc_class);
			print_td($status_text,35);
			print_td($blink,80);
		}else if($format == 'brief'){
			print_td($id,10);
			print_td($name);
			print_td($author);
			print_td($class_text, 35, '', '', $sc_class);
			print_td($data,35);
			print_td($status_text,35);
			print_td($blink,120);
		}else
			print_tdlist(array($id, $name, $author, $isbn, $index, $price, $buy_date, $sponsor, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");
	if($condition == ''){
		print('<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		print('<input type="submit"'); print(' name="begin" value="Begin" />   ');
		print('<input type="submit"'); if(!$hasprev) print(" disabled "); print(' name="prev" value="Prev" />   ');
		print('<input type="submit"'); if(!$hasmore) print(" disabled "); print(' name="next" value="Next" />   ');
		print('<input type="submit"');  print(' name="end" value="End" />   ');
		print('</form');
	}
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

function check_wait($book_id)
{

	$sql = " select * from history where book_id=$book_id and (status = 4 || status = 0x104)";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		return true;
	}
	return false;
}

function wait_book($book_id, $login_id)
{
	if(!check_record($book_id, $login_id))
		return false;
	add_record($book_id, $login_id, 0x104);
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


function borrow_wait_book($record_id, $login_id)
{
	global $max_books;
	$sql = " select * from history where borrower='$login_id' and (status = 1 or status = 2)";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$rows = mysql_num_rows($res);
	if($rows >= $max_books){
		print ("You already reached the maximum books:$rows >= $max_books !");
		return false;
	}

	$sql = " select * from history left join books using (book_id) where record_id = $record_id and borrower='$login_id' and books.status = 0 ";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		$name = $row['name'];
		set_record_status($record_id, 1);
		print ("Applied for Book <$name>");
		return true;
	}
	print ("Book not return or not found!");
	return false;
}

function borrow_book($book_id, $login_id)
{
	global $max_books;
	if(!check_record($book_id, $login_id))
		return false;
	$sql = " select * from history where borrower='$login_id' and (status = 1 or status = 2)";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$rows = mysql_num_rows($res);
	if($rows >= $max_books){
		print ("You already reached the maximum books:$rows >= $max_books !");
		return false;
	}
	add_record($book_id, $login_id, 1);
	set_book_status($book_id, 1);
	return true;
}

function renew_book($book_id, $record_id, $login_id)
{
	global $max_books;
	$sql = " select * from history left join books using (book_id) where book_id = $book_id and history.status = 0x104 ";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		print ("Someone wait, Can not renew!");
		return false;
	}
	set_record_status($record_id, 5); 
	return true;
}

function add_score($book_id, $login_id, $score=0)
{
	$sql = " select * from history where book_id = $book_id and borrower='$login_id' and (status = 0x109)";
	$res = read_mysql_query($sql);
	$rows = mysql_num_rows($res);
	if($rows > 0){
		print ("You already add score, update score");
		$sql = " update history set data = $score where book_id = $book_id and borrower='$login_id' and (status = 0x109)";
		$res = update_mysql_query($sql);
		return;
	}
	add_record($book_id, $login_id, 0x109, false, $score);
	print ("add score ok!");
	return true;
}

function apply_share($book_id, $login_id)
{
	$sql = " select * from history where book_id = $book_id and borrower='$login_id' and (status = 0x105)";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$rows = mysql_num_rows($res);
	if($rows > 0){
		print ("You already apply share!");
		return false;
	}
	add_record($book_id, $login_id, 0x105);
	print ("申请分享成功!");
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

#$class_list = array('0-未分','1-小说', '2-历史', '3-技术', '4-科普', '5-社会', '6-传记', '7-管理', '8-文学','9-经济', '10-教育', '11-艺术', '12-心理');
$class_list = array('未分','小说', '历史', '技术', '科普', '社会', '传记', '管理', '文学','经济', '教育', '艺术', '心理');
function get_class_name($class=0)
{
	global $class_list;
	if(is_numeric($class))
		return $class_list[$class];
	else if($class == '')
		return $class_list[0];
	else{
		$in = substr($class, 0, 1);
		$sql = "select * from class_name where `index` = '$in'";
		$res=mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
		if($row=mysql_fetch_array($res)){
			$tt = $row["class_name"];
			return $tt;
		}
		return $class_list[0];
	}
}

function show_book($book_id)
{
	global $login_id;
	$sql = " select * from (select count(distinct(borrower)) as btimes from history where book_id = $book_id and status < 6) as a, " .
	" (select round(avg(data), 1) as score from history where book_id = $book_id and status = 0x109) as b ";
	$res = read_mysql_query($sql);
	while($row=mysql_fetch_array($res)){
		$times = $row['btimes'];
		$score = $row['score'];
	}

	$sql = " select * from books left join favor on books.book_id = favor.book_id where books.book_id=$book_id";
	$res = read_mysql_query($sql);
	while($row=mysql_fetch_array($res)){
		$desc= $row['desc'];
		$name= $row['name'];
		$status = $row['status'];
		$id= $row['book_id'];
		$comments= $row['comments'];
		$isbn = $row['ISBN'];
		$index = $row['index'];
		$class_name = get_class_name($index);
		$price = $row['price'];
		$buy_date= $row['buy_date'];
		$buy_date= substr($buy_date, 0, 10);
		$sponsor = $row['sponsor'];
		$class = $row['class'];
		$class_text = get_class_name($class);
		$member_id = $row['member_id'];

		if($status == 0)
			$blink = "<a href=book.php?action=borrow&book_id=$book_id>借阅</a>";
		else
			$blink = "<a href=book.php?action=wait&book_id=\"$book_id\">等候</a>";
		dprint("member: $member_id, $login_id<br>");
		
		if($member_id == $login_id){
			$blink .= "&nbsp;<a href='book.php?action=remove_favor&book_id=$book_id' >去藏</a>";
			break;
		}
		$blink .= "&nbsp;<a href='javascript:add_score(this,$book_id);'>打分</a>";
	}

	print("《" . $name . "》");
	if($member_id != $login_id)
		$blink .= "&nbsp;<a href='book.php?action=add_favor&book_id=$book_id' >收藏</a>";
	$blink .= "&nbsp;<a href='book.php?action=share&book_id=$book_id' >分享</a>";
	print("[" . get_book_status_name($status) . "]&nbsp;");
	print("得分:$score&nbsp");
	print("$blink");
	print('<table border=1 bordercolor="#0000f0", cellspacing="0" cellpadding="0" style="padding:0.2em;border-color:#0000f0;border-style:solid; width: 600px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto">');
	print("<tr>");
	print_tdlist(array('编号', 'ISBN','索引','价格','中图分类', '咱分类', 'Sponsor', '购买日期', '次数', '状态'));
	print("</tr>");
	print("<tr>");
	print_tdlist(array($id, $isbn, $index, $price, $class_name, $class_text, $sponsor, $buy_date, $times, get_book_status_name($status))); 
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

function get_borrower($book_id)
{
	$r = array();
	$r = get_record_by_bookid($book_id);
	return $r[1];
}

function get_record($book_id)
{
	$r = array();
	$r = get_record_by_bookid($book_id);
	return $r[0];
}

function get_class_no($book_id)
{
	$r = array();
	$r = get_record_by_bookid($book_id);
	return $r[2];
}

function get_record_by_bookid($book_id)
{

	$sql = " select record_id, borrower, t1.status, class, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status != 0 and t1.status < 6 and t1.status != 4 order by `adate` asc";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$borrower = $row['borrower'];
		$record_id = $row['record_id'];
		$class = $row['class'];
		return array($record_id, $borrower, $class);
	}
	return array('', '', 0);
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
	$sql = " select f1.book_id, f1.operator, f1.member_id, f1.timestamp, f2.name, f3.user_name, f1.status from log f1, books f2, member f3 where f1.book_id = f2.book_id and f1.member_id = f3.user order by timestamp desc";
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
		}else if($status == 10){
			$status_text = "新加";
		}else if($status == 11){
			$status_text = "新购";
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

	if($format == 'score')
		print_tdlist(array('编号', '书名','借阅人','日期', '评分'));
	else
		print_tdlist(array('编号', '书名','借阅人','日期', '状态'));
	if($format == 'out')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status != 0 and t1.status != 4 and t1.status < 0x100 order by `adate` asc";
	else if($format == 'wait')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and (t1.status = 4 or t1.status = 0x104 ) order by `adate` asc";
	else if($format == 'score')
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id, data from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status = 0x109 order by `adate` asc";
	else
		$sql = " select record_id, borrower, t1.status, name, user_name, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id=$book_id and t1.book_id = t2.book_id and t3.user = t1.borrower and t1.status = 0 order by `adate` asc";

	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$book_id = $row['book_id']; 
		$name= $row['name'];
		$user_name= $row['user_name'];
		$status=$row['status'];	
		$date = $row['adate'];
		if($status == 4 || $status == 0x104){
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
		}else if($status == 0x109){
			$score = $row['data'];
			$status_text = "$score";
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
	$sql = " select * from history where borrower='$login_id' and book_id=$book_id and (status < 0x100 and status !=0 or status = 0x104) ";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		if($row['status'] == 0x104)
			print ("You already wait this book, please borrow from the old record!");
		else
			print ("You already borrowed this book!");
		return false;
	}
	return true;
}

function add_record($book_id, $user_id, $status=1, $record_id=false, $data=0)
{
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$sql = " insert into history set `borrower`='$user_id', book_id=$book_id, adate= '$time_start', status=$status, data=$data";
	$res = update_mysql_query($sql);
	if($record_id){
		$sql = " select * from history where `borrower`='$user_id' and book_id=$book_id and adate= '$time_start' and status=$status";
		$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
		while($row = mysql_fetch_array($res)){
			return $row['record_id'];
		}
		return $record_id;
	}
	return 0;
}

function add_record_full($book_id, $user_id, $bdate, $sdate, $status=1)
{
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$sql = " insert into history set `borrower`='$user_id', book_id=$book_id, adate='$bdate', bdate= '$bdate', rdate='$sdate', sdate='$sdate', status=$status";
	$res = update_mysql_query($sql);
	return true;
}

function get_bookid_by_borrower($borrower)
{
	$book_ids = array();
	$sql = " select * from history where `borrower` = '$borrower' and status = 2 ";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row = mysql_fetch_array($res)){
		$book_id = $row['book_id'];
		$book_ids[] = $book_id;
	}
	return $book_ids;
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

function get_borrower_by_record($record_id)
{
	$sql = " select * from history where `record_id` = $record_id";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($row = mysql_fetch_array($res)){
		$borrower = $row['borrower'];
		return $borrower;
	}
	return 0;
}

function set_record_status($record_id, $status)
{
	dprint("set_record_status:$record_id:$status<br>");
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	if($status == 0)
		$sql = " update history set sdate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 1)
		$sql = " update history set bdate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 2)
		$sql = " update history set bdate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 3)
		$sql = " update history set rdate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 4)
		$sql = " update history set adate= '$time_start', status=$status where `record_id` = $record_id";
	else if($status == 5)
		$sql = " update history set rdate= '$time_start', status=$status where `record_id` = $record_id";
	else
		$sql = " update history set adate= '$time_start', status=$status where `record_id` = $record_id";

	dprint("$sql<br>");
	$res = update_mysql_query($sql);
	if($status < 0x100){
		$book_id = get_bookid_by_record($record_id);
		set_book_status($book_id, $status);
	}
}

function get_book_status($book_id)
{
	$sql = "select status from books where book_id=$book_id";
	$res = mysql_query($sql) or die("Invalid query:".$sql.mysql_error());
	if($row = mysql_fetch_array($res)){
		return $row['status'];
    }
	return -1;
}
function set_book_status($book_id, $status)
{
	$sql = "update books set `status` = $status where book_id=$book_id";
	if($status == 2)
		$sql = "update books set `status` = $status, `times` = `times` + 1 where book_id=$book_id";
	$res = update_mysql_query($sql);
	$rows = mysql_affected_rows();
	if($rows != 0){
		return true;
    }
	return false;
}

function check_passwd($login_id, $login_passwd){

	$sql1="SELECT * FROM user.user WHERE user_id = '$login_id';";
	$res1=mysql_query($sql1) or die("Query Error:" . mysql_error());
	$row1=mysql_fetch_array($res1);
	if(!$row1)
		return 1;
	if($row1['password'] == "")
		return 0;
    if($row1['password'] == $login_passwd)
        return 0;
	$sql1="SELECT * FROM user.user WHERE user_id = '$login_id' and password=ENCRYPT('$login_passwd', 'ab');";
	$res1=mysql_query($sql1) or die("Query Error:" . mysql_error());
	$row1=mysql_fetch_array($res1);
	if(!$row1)
		return 2;
//	$passwd = crypt($login_passwd);
	return 0;
}


function add_member($user, $name, $email, $role) {
	$sql1 = "replace member set user = '$user', user_name= '$name', email='$email', role = $role ";
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_affected_rows($res1))
		return true;
	return false;
}

function set_member_attr($user, $prop, $value) {
	$sql = "update member set `$prop` = '$value' where `user` = '$user' ";
	print $sql;
	$res=mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($rows=mysql_affected_rows() > 0)
		return true;
	return false;
}

function get_user_attr($user, $prop) {
	$sql1 = "select * from user.user where user_id ='$user'";
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_fetch_array($res1))
		return $row1["$prop"];
	$sql1 = "select * from member where user ='$user'";
	if($prop == 'name')
		$prop = 'user_name';
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_fetch_array($res1))
		return $row1["$prop"];
	return false;
}

function set_user_attr($user, $prop, $value) {
	$sql1 = "update user.user set `$prop` = '$value' where user_id ='$user'";
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_affected_rows($res1) > 0)
		return true;
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

function get_class_by_index($index)
{
	$sql = " select class_no from class_name where `index` = '$index'";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		$class_no = $row['class_no'];
		return $class_no;
	}
	return 0;
}

function get_first_wait_mail($book_id)
{
	$sql = "select * from history where book_id = $book_id and (status = 4 or status = 0x104)  order by adate asc";
	$res=mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$to = false;
	while($row = mysql_fetch_array($res)){
		$borrower = $row['borrower'];
		dprint("waiter $borrower");
		$to .= get_user_attr($borrower, 'email');
		$to .= ";";
	}
	return $to;
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
	dprint("add_log:$sql <br>");
	$res = update_mysql_query($sql);
	$rows = mysql_affected_rows();
	dprint("rows:$rows<br>");
	return true;
}

function add_favor($member_id, $book_id)
{
	$sql = " select * from favor where `member_id`='$member_id' and book_id=$book_id" ;
	$res = update_mysql_query($sql);
	if($row = mysql_fetch_array($res)){
		dprint("Already in favorate list");
		return false;
	}
	$sql = " insert into favor set `member_id`='$member_id', book_id=$book_id" ;
	$res = update_mysql_query($sql);
	$rows = mysql_affected_rows();
	dprint("add favor rows:$member_id, $book_id, $rows<br>");
	return true;
}

function remove_favor($member_id, $book_id)
{
	$sql = "delete from favor where `member_id`='$member_id' and book_id=$book_id";
	$res = update_mysql_query($sql);
	$rows = mysql_affected_rows();
	dprint("$sql");
	dprint("remove favor $member_id, $book_id, rows:$rows<br>");
	return true;
}


function clear_favor($member_id)
{
	$sql = "delete from favor where `member_id`='$member_id'";
	$res = update_mysql_query($sql);
	$rows = mysql_affected_rows();
	dprint("$sql");
	dprint("remove favor $member_id, rows:$rows<br>");
	return true;
}

function mail_html($to, $cc, $subject, $message)
{
	global $debug_mail, $debug;
	$headers = 'From: book@cedump-sh.ap.qualcomm.com' . "\r\n" .
	    'Reply-To: xling@qti.qualcomm.com' . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	if($debug == 1){
		$message .= "\r\n To:$to, CC:$cc";
		$cc = 'xling@qti.qualcomm.com';
		$to = 'xling@qti.qualcomm.com';
	}
	if($cc)
		$headers .= "Cc: $cc" . "\r\n";
	if(isset($debug_mail) && $debug_mail == 1)
		$headers .= "Bcc: xling@qti.qualcomm.com" . "\r\n";

	dprint("mail|to:$to|cc:$cc|". htmlentities($subject, ENT_COMPAT, 'utf-8') . "<br>\n");
//	print("$message\n");
	mail($to,$subject, $message, $headers);

}

/* only used once*/
function import_favor_from_history()
{
	$sql =  "select borrower, book_id, status from history where status = 0 or status = 2 or status = 0x104";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		add_favor($row['borrower'], $row['book_id']);
	}

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

?>
