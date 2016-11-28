<?php

function rsort_by_index($array, $index)
{
	$new_array = Array();
	foreach($array as $key => $data){
		$new_array[$key] = $data[$index];
	}
	arsort($new_array);
	foreach($array as $key => $data){
		$new_array[$key] = $data;
	}
	return $new_array;
}


function show_home_link($str="Home", $action='', $more=''){
	if($action!='')
    	print("<a href=\"book.php?action=$action\">$str</a>" . $more);
	else
    	print("<a href=\"book.php\">$str</a>" . $more);
}


function manage_record()
{
	global $login_id;
	print("&nbsp;&nbsp;申请：");
	print("&nbsp;&nbsp;<a href=edit_book.php?op=edit_notice_ui>编辑规则</a>");
	list_record($login_id, 'approve', " (history.status = 1 or history.status = 5) ");
	print("&nbsp;&nbsp;归还：");
	list_record($login_id, 'approve', " history.status = 3 ");
	print("&nbsp;&nbsp;等候：");
	list_record($login_id, 'approve', " (history.status = 4 or history.status = 0x104) ");
	print("&nbsp;&nbsp;分享：<a href=edit_book.php?op=add_share_ui>添加</a>");
	list_record($login_id, 'share', " 0x105 ");
	print("&nbsp;&nbsp;申请入会：");
	list_record($login_id, 'member');
	print("&nbsp;&nbsp;待购图书:");
	list_recommend('', '', 3);
}

function out_record()
{
	global $login_id;
	list_record($login_id, 'out');
}
/* 0x100 cancel 
   0x101 reject
   0x104 wait 260
   0x105 share 
   0x106 share_done
   0x107 apply_join
   0x108 approve_member
   0x109 add score
   0x110 share_cancel
   0x111 add want
 */
function get_book_status_name($status)
{
	$status_name = array('在库', '借阅中','借出','归还中', '待购', '续借中');
	return $status_name[$status];
}

function list_record($login_id, $format='self', $condition='')
{
	global $role, $table_head, $role_city, $disp_city;
	$role_city = isset($role_city)?$role_city:0;
	$disp_city = isset($disp_city)?$disp_city:0;
	$cond = " 1 ";
	if($disp_city != 255 && $disp_city != '')
		$cond .= " and books.city = $role_city ";
	$book_db = mysql_result(mysql_query("select database()") or die(mysql_error()."error get db"), 0);
	$mail_url = get_cur_php();
	if($login_id == -2){
		$book_db = 'book';
		$mail_url = "http://cedump-sh.ap.qualcomm.com/book/book.php";
	}
	if($mail_url == ''){
		$mail_url = "http://cedump-sh.ap.qualcomm.com/book/book.php";
	}
	print($table_head);
	if($format == 'approve'){
		print_tdlist(array('序号', '借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, misc, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t3.user = t1.borrower $condition order by adate asc ";
		$sql = " select record_id, borrower, history.status, name, misc, user_name, data, adate, bdate,rdate,sdate, history.book_id from history left join `books` as t2 using (`book_id`) left join member on member.user = history.borrower  where $condition order by adate asc ";
	}else if($format == 'self'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '操作'));
		$sql = " select record_id, borrower, history.status, books.status as bstatus, data, name, user_name, adate, bdate,rdate,sdate, history.book_id from history, books, member  where history.borrower='$login_id' and history.book_id = books.book_id and member.user = history.borrower and $condition order by adate desc ";
	}else if($format == 'score'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '评分'));
		$sql = " select record_id, borrower, history.status, books.status as bstatus, data, name, user_name, adate, bdate,rdate,sdate, history.book_id from history, books, member  where history.borrower='$login_id' and history.book_id = books.book_id and member.user = history.borrower and $cond and $condition order by adate desc ";
	}else if( $format == 'waityou'){
		print_tdlist(array('序号','等候人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期', '状态', '操作'));
		$book_ids = get_bookid_by_borrower($login_id);
		$sql = " select record_id, borrower, t1.status, name, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.status = 0x104  and t1.borrower = t3.user and t1.book_id = t2.book_id and ( 0 ";
		foreach($book_ids as $book_id){
			$sql .= " or t1.book_id=$book_id " ;
		}
		$sql .= ") ";
		$sql .= " order by adate asc ";
	}else if($format == 'out'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '借出日期', '状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, misc, user_name, data,adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status  = 2 and t3.user = t1.borrower and t2.city = $role_city order by bdate desc";
	}else if($format == 'history'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '借出日期', '归还日期','入库日期' ));
		$sql = " select record_id, borrower, t1.status, name, user_name, data,adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where t1.book_id = t2.book_id and t1.status = 0 and t3.user = t1.borrower and t2.city = $role_city order by sdate desc ";
	}else if($format == 'share'){
		print_tdlist(array('序号','借阅人', '书名','编号','申请日期', '完成日期' ));
		$sql = " select record_id, borrower, t1.status, name, user_name, data, misc, adate, bdate,rdate,sdate, t1.book_id from $book_db.history t1, $book_db.books t2, $book_db.member t3 where t1.book_id = t2.book_id and t1.status = $condition and t3.user = t1.borrower and t2.city = $role_city order by sdate desc,adate desc ";
	}else if($format == 'member'){
		print_tdlist(array('序号','帐号','申请人','申请日期', '批准日期', '操作'));
		$sql = " select record_id, borrower, t1.status, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, member t3 where t1.book_id = 0 and t1.status = 0x107 and t3.user = t1.borrower and t3.city = $role_city order by adate desc ";
	}else if($format == 'timeout'){	
		print_tdlist(array('序号', '借阅人', '书名','编号','申请日期', '借出日期', '到期日期','状态', '操作'));
		$sql = " select record_id, borrower, t1.status, name, misc, user_name, data, adate, bdate,rdate,sdate, t1.book_id from history t1, books t2, member t3 where (t1.status = 2 or t1.status = 3 or t1.status = 5) and  t1.book_id = t2.book_id and t3.user = t1.borrower and t2.city = $role_city ";
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
		$name = isset($row['name'])?$row['name']:''; 
		$name = "<a href='$mail_url?action=show_borrower&book_id=$book_id'>$name</a>";
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
				$rdate = $bdate + 28;
				$ldate = strtotime($bdate) + 28*24*3600; 
				$rdate = strftime("%Y-%m-%d", $ldate);
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
				$rdate = $bdate + 28;
				$ldate = strtotime($bdate) + 28*24*3600; 
				$rdate = strftime("%Y-%m-%d", $ldate);
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
			if($status == 0x104){
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
		else if($format == 'share'){
			if($book_id == 0)
				$name = $row['name'].":".$row['misc'];
			if($status == 0x105 && $role == 2 && $login_id != -2){
				$blink = "<a href=\"book.php?record_id=$record_id&action=share_done\">完成</a>";
				$blink .= "&nbsp;<a href=\"book.php?record_id=$record_id&action=share_cancel\">取消</a>";
				$blink .= "&nbsp;<a href=\"edit_book.php?record_id=$record_id&op=edit_share_ui\">编辑</a>";
			}
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $sdate, $blink)); 
		}else if($format == 'member')
			print_tdlist(array($i,$borrower_id, $borrower,$adate, $sdate, $blink)); 
		else if($format == 'score')
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $bdate, $rdate,$sdate, get_book_status_name($row['bstatus']), $score)); 
		else if($format == 'timeout')
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $bdate, $rdate,$status_text, $blink)); 
		else
			print_tdlist(array($i,$borrower, $name,$book_id,  $adate, $bdate, $rdate,$sdate, $status_text, $blink)); 
		print("</tr>\n");
	}
	print("</table>");
}

function list_statistic()
{
	cal_score();
	print("积分排名");
	point_statistic();
	print("评论统计");
	comment_statistic(0);
	//comment_statistic_legacy(0);
	print("分享统计");
	share_statistic();
	print("评分统计");
	score_statistic();
}
function share_statistic($type = 0)
{
	$tr_width=400;
	//print("<table id='$table_name' class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	print_table_head('', 400);

	print("<tr>");
	print("<th>User</th>");
	print("<th >分享次数</th>");
	print("</tr>");

	$sql = "select borrower, user_name, count(*) as ct from history, member where status = 0x106 and history.borrower = member.user group by borrower";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$borrower = $row['borrower'];
		$name = $row['user_name'];
		$count = $row['ct'];
		print("<tr>");
		print_td($name);
		print_td($count);
		print("</tr>");
	}
	print("</table>");
}

function point_statistic($type = 0)
{
//	$tr_width=400;
//	print("<table id='$table_name' class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	print_table_head('', 400);
	print("<tr>");
	print("<th>姓名</th>");
	print("<th >积分</th>");
	print("<th >已用积分</th>");
	print("<th >可用积分</th>");
	print("<th >累计借书</th>");
	print("<th >累计分享</th>");
	print("<th >累计评论</th>");
	print("<th >有效评论</th>");
	print("</tr>");

	$tb_comments = " (select borrower, count(words) as total_comments from `comments` group by borrower)";
	$sql = "  select user,user_name, score, score_used, tc.total_comments, effect_comments, ";
	$sql .= "COUNT( CASE WHEN `status` = 0 THEN 1 ELSE NULL END ) AS `books_his`,  COUNT( CASE WHEN `status` = 0x106 THEN 1 ELSE NULL END ) AS `shares`";
//	$sql .= ", count(case when `words` != '' then 1 else null end ) as `total_comments`";
	$sql .= " from `member` left join $tb_comments tc on member.user = tc.borrower ";
	$sql .= "  left join `history` on member.user = history.borrower ";
	$sql .= " group by user order by score desc ";

	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$name = $row['user_name'];
		$score = $row['score'];
		$score_used = $row['score_used'];
		$score_free = $score - $score_used;
		$books_his = $row['books_his'];
		$shares = $row['shares'];
		$comments = $row['total_comments'];
		$effect_comments = $row['effect_comments'];
		print("<tr>");
		print_td($name);
		print_td($score);
		print_td($score_used);
		print_td($score_free);
		print_td($books_his);
		print_td($shares);
		print_td($comments);
		print_td($effect_comments);
		print("</tr>");
	}
	print("</table>");
}
function add_comment($book_id, $user, $this_comment, $month='', $date='')
{
	if($month == ''){
		$time = strftime("%Y-%m-%d %H:%M:%S", time());
	}else
		$time = "2016-$month-$date";
	$sql = "select * from comments where book_id = $book_id and borrower = '$user' and words = '$this_comment'";
	$res = read_mysql_query($sql);
//	if($user == 'lgao' && $month == 8)
//		print("add comment:$rows , $month, $date, $book_id, $user, $this_comment<br>");
	if(!($row = mysql_fetch_array($res))){
		$sql = "insert into comments set book_id = $book_id, borrower = '$user', words = '$this_comment', timestamp = '$time'";
		$rows = update_mysql_query($sql);
	//	print("add comment:$rows , $month, $date, $book_id, $user, $this_comment<br>");
	}else{
		//print("Already exist: $book_id, $user, $this_comment<br>");
	}
}

function transfer_comment()
{
	$sql = "select * from books where comments != ''";
	$res = read_mysql_query($sql);
	$reg = '/\[(\D+)\]\[(\d+)\/(\d+)\]([^\[]*)([\d\D\n.]*)/';
	$ct_array = array();
	while($row = mysql_fetch_array($res)){
		$comment = $row['comments'];
		$book_id = $row['book_id'];
		$book = $row['name'];
		while(preg_match($reg, $comment, $matches)){
			$user = $matches[1];
			$month = $matches[2];
			$date = $matches[3];
			$this_comment = $matches[4];
			$comment = $matches[5];
			if(preg_match("/^:([\d\D\n.]+)/", $this_comment, $matches2)){
				$this_comment = $matches2[1];
			}
			$this_comment = str_replace("<br>", "", $this_comment);
//			if($user == 'lgao' && $month==8)
//				print("$book_id:$book $user $this_comment<br>");
			add_comment($book_id, $user, $this_comment, $month, $date);
		}
	}
}


function cal_score()
{
	$sql = "select * from comments ";
	$res = read_mysql_query($sql);
	$ct_array = array();
	$cc_array = array();
	$comment_array = array();
	while($row = mysql_fetch_array($res)){
		$this_comment = $row['words'];
		$book_id = $row['book_id'];
		$user = $row['borrower'];
		$this_comment = str_replace("\n", "", $this_comment);
		if($len = mb_strlen($this_comment, "UTF-8") ) {
			if(!isset($comment_array[$user][$book_id]))
				$comment_array[$user][$book_id] = 0;
			$comment_array[$user][$book_id] += $len;
		}
	}
	foreach($comment_array as $user=>$book_ct){
		foreach($book_ct as $book_id=>$ct){
			if($ct > 50){
				$ct_array[$user] = isset($ct_array[$user]) ? $ct_array[$user] + 20 : 20;
				$cc_array[$user] = isset($cc_array[$user]) ? $cc_array[$user] + 1 : 1;
				
			}
		}
	}
	
	$sql = "select * from history where status = 0x106";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$user = $row['borrower'];
		$ct_array[$user] = isset($ct_array[$user]) ? $ct_array[$user] + 200 : 200;
	}

	foreach($ct_array as $user=>$score){
		$cc = isset($cc_array[$user])?$cc_array[$user]:0;
		$sql = "update member set score=$score, effect_comments=$cc where user = '$user'";
		$rows = update_mysql_query($sql);
	}
}

function cal_score_legacy()
{
	$sql = "select * from books where comments != ''";
	$res = read_mysql_query($sql);
	$reg = '/\[(\D+)\]\[(\d+)\/(\d+)\]([^\[]*)([\d\D\n.]*)/';
	$ct_array = array();
	while($row = mysql_fetch_array($res)){
		$comment = $row['comments'];
		$book = $row['name'];
		while(preg_match($reg, $comment, $matches)){
			$user = $matches[1];
			$month = $matches[2];
			$date = $matches[3];
			$this_comment = $matches[4];
			$comment = $matches[5];
			//print("<br>:$user:". $this_comment.":".mb_strlen($this_comment, "UTF-8"));
			$this_comment = str_replace("\n", "", $this_comment);
			if(mb_strlen($this_comment, "UTF-8") >= 50){
				$ct_array[$user]+=20;
			}
		}
	}

	$sql = "select * from history where status = 0x106";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$user = $row['borrower'];
		$ct_array[$user]+=200;
	}

	foreach($ct_array as $user=>$score){
		$sql = "update member set score=$score where user = '$user'";
		$rows = update_mysql_query($sql);
	}
}

function comment_statistic($type = 0)
{
	$tr_width=400;
//	print("<table id='$table_name' class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	print_table_head('', 400);
	$sql = "select book_id, borrower, words, month(timestamp) as mon, timestamp  from comments ";
	$res = read_mysql_query($sql);
	$ct_array = array();
	while($row = mysql_fetch_array($res)){
		$this_comment = $row['words'];
		$book_id = $row['book_id'];
		$user = $row['borrower'];
		$time = $row['timestamp'];
		$month = $row['mon'];
		$this_comment = str_replace("\n", "", $this_comment);
		if(mb_strlen($this_comment, "UTF-8") >= 50 || $type == 0){
			$ct_array[$user][$month] = isset($ct_array[$user][$month]) ? $ct_array[$user][$month] + 1: 1;
			$ct_array[$user][0] = isset($ct_array[$user][0]) ? $ct_array[$user][0] + 1:1 ;
		}
//		print("$book:$user $date $comment<br>");
	}
	$ct_array = rsort_by_index($ct_array, 0);
	$mm = array(7=>'Jul.', 8=>'Aug.', 9=>'Sep.', 10=>'Oct.', 11=>'Nov.', 12=>'Dec.');
	print("<tr>");
	print("<th>User</th>");
	foreach($mm as $m=>$name){
		print("<th >$name</th>");
	}
	print("<th>Total</th>");
	print("</tr>");
	$total = array();
	foreach($ct_array as $user=>$mct){
		print("<tr>");
		$user_name = get_user_name($user);
		print_td($user_name, 150);
		$t = 0;
		foreach($mm as $m=>$name){
			$mc = isset($mct[$m]) ? $mct[$m] : 0;
			print_td($mc);
			$total[$m] = isset($total[$m]) ? $total[$m] + $mc : $mc;
		}
		print("<th>$mct[0]</th>");
		$mc = isset($mct[0]) ? $mct[0] : 0;
		$total[0] = isset($total[0]) ? $total[0] + $mc : $mc;
		print("</tr>");
	}
	print("<tr>");
	print("<th>Total</th>");
	foreach($mm as $m=>$name){
		print_td($total[$m]);
	}
	print("<th>$total[0]</th>");
	print("</tr>");
	print("</table>");
//	print_r($ct_array);
}

function score_statistic($type = 0)
{
	$tr_width=400;
	//print("<table id='$table_name' class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	print_table_head('', 400);
	$sql = "select book_id, borrower, month(adate) as mon, adate from history where status=0x109";
	$res = read_mysql_query($sql);
	$ct_array = array();
	while($row = mysql_fetch_array($res)){
		$book_id = $row['book_id'];
		$user = $row['borrower'];
		$month = $row['mon'];
		$ct_array[$user][$month] = isset($ct_array[$user][$month]) ? $ct_array[$user][$month] + 1: 1;
		$ct_array[$user][0] = isset($ct_array[$user][0]) ? $ct_array[$user][0] + 1:1 ;
	}
	$ct_array = rsort_by_index($ct_array, 0);
	$mm = array(7=>'Jul.', 8=>'Aug.', 9=>'Sep.', 10=>'Oct.', 11=>'Nov.', 12=>'Dec.');
	print("<tr>");
	print("<th>User</th>");
	foreach($mm as $m=>$name){
		print("<th >$name</th>");
	}
	print("<th>Total</th>");
	print("</tr>");
	$total = array();
	foreach($ct_array as $user=>$mct){
		print("<tr>");
		$user_name = get_user_name($user);
		print_td($user_name, 150);
		$t = 0;
		foreach($mm as $m=>$name){
			$mc = isset($mct[$m]) ? $mct[$m] : 0;
			print_td($mc);
			$total[$m] = isset($total[$m]) ? $total[$m] + $mc : $mc;
		}
		print("<th>$mct[0]</th>");
		$mc = isset($mct[0]) ? $mct[0] : 0;
		$total[0] = isset($total[0]) ? $total[0] + $mc : $mc;
		print("</tr>");
	}
	print("<tr>");
	print("<th>Total</th>");
	foreach($mm as $m=>$name){
		print_td($total[$m]);
	}
	print("<th>$total[0]</th>");
	print("</tr>");
	print("</table>");
//	print_r($ct_array);
}


function comment_statistic_legacy($type = 0)
{
	$tr_width=400;
	print("<table id='$table_name' class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	$sql = "select * from books where comments != '' ";
	$res = read_mysql_query($sql);
	$reg = '/\[(\D+)\]\[(\d+)\/(\d+)\]([^\[]*)([\d\D\n.]*)/';
	$ct_array = array();
	while($row = mysql_fetch_array($res)){
		$comment = $row['comments'];
		$book = $row['name'];
		$book_id = $row['book_id'];
		while(preg_match($reg, $comment, $matches)){
			$user = $matches[1];
			$month = $matches[2];
			$date = $matches[3];
			$this_comment = $matches[4];
			if(preg_match("/^:([\d\D\n.]+)/", $this_comment, $matches2)){
				$this_comment = $matches2[1];
			}
			$this_comment = str_replace("<br>", "", $this_comment);
			$comment = $matches[5];
			if(mb_strlen($this_comment, "UTF-8") >= 50 || $type == 0){
				$ct_array[$user][$month]++;
				$ct_array[$user][0]++;
			}
//			if($user == 'lgao' && $month==8)
//				print("$book_id:$book $user $this_comment<br>");
		}
	}
	$ct_array = rsort_by_index($ct_array, 0);
	$mm = array(7=>'Jul.', 8=>'Aug.', 9=>'Sep.', 10=>'Oct.', 11=>'Nov.', 12=>'Dec.');
	print("<tr>");
	print("<th>User</th>");
	foreach($mm as $m=>$name){
		print("<th >$name</th>");
	}
	print("<th>Total</th>");
	print("</tr>");
	$total = array();
	foreach($ct_array as $user=>$mct){
		print("<tr>");
		$user_name = get_user_name($user);
		print_td($user_name, 150);
		$t = 0;
		foreach($mm as $m=>$name){
			print_td($mct[$m]);
			$total[$m] += $mct[$m];
		}
		print("<th>$mct[0]</th>");
		$total[0] += $mct[0];
		print("</tr>");
	}
	print("<tr>");
	print("<th>Total</th>");
	foreach($mm as $m=>$name){
		print_td($total[$m]);
	}
	print("<th>$total[0]</th>");
	print("</tr>");
	print("</table>");
//	print_r($ct_array);
}

function list_member()
{

	global $login_id, $role, $class, $comment_type, $book_sname;

	cal_score();
	$table_name = "book";
	$tr_width = 700;
	$background = '#efefef';

	$hasmore = false;
	$hasprev = false;

	print('<form enctype="multipart/form-data" action="book.php" method="POST">');

	print("<table id='$table_name' width=600 class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>");
	print_tdlist(array('序号', '帐号', '姓名','邮件', '身份','已借','曾借','积分','已用积分', 'wish', '操作'));

	$sql = "  select user,user_name, email, role,  ";
	$sql .= "COUNT( CASE WHEN `status` = 0 THEN 1 ELSE NULL END ) AS `books_his`,  COUNT( CASE WHEN `status` = 2 THEN 1 ELSE NULL END ) AS `books_borrow`";
	$sql .= ", score, score_used ";
	$sql .= " from `member` left join `history` on member.user = history.borrower group by user ";

	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	$i = 0;
	$total_wish = 0;
	while($row=mysql_fetch_array($res)){
		$user_id = $row['user']; 
		$user_name = $row['user_name'];
		$email = $row['email'];
		$role = $row['role'];
		$books = $row['books_borrow'];
		$books_his = $row['books_his'];
		$score = $row['score'];
		$score_used = $row['score_used'];
		if($role >= 1) {
			$status_text = "会员";
			$blink = "<a href=book.php?action=remove_member&borrower=$user_id>离会</a>";
		} else { 
			$status_text = "非会员";
			$blink = "<a href=book.php?action=approve_member&borrower=$user_id>入会</a>";
		}
		$blink .= "&nbsp;<a href='javascript:deduce_member_score(this,\"$user_id\");' >扣分</a>";
		$blink .= "&nbsp;<a href='edit_book.php?op=add_share_ui&borrower=$user_id' >分享</a>";
		print("<tr>\n");
		if(preg_match("/^test/", $user_id))
			continue;
		$i++;
		print_td($i,5);
		print_td($user_id,10);
		print_td($user_name, 250);
		print_td($email);
		print_td($status_text,65);
		print_td($books, 20);
		print_td($books_his, 20);
		print_td($score, 20);
		print_td($score_used, 20);
		$wish = floor(($score - $score_used)/100);
		$total_wish += $wish;
		print_td($wish, 20);
		print_td($blink, 180);
		print("</tr>\n");
	}
	print("</table>");
	printf("Total Wish List:%d", $total_wish);
	print('<br><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
	print('<input type="submit"'); print(' disabled name="begin" value="Begin" />   ');
	print('<input type="submit"'); if(!$hasprev) print(" disabled "); print(' name="prev" value="Prev" />   ');
	print('<input type="submit"'); if(!$hasmore) print(" disabled "); print(' name="next" value="Next" />   ');
	print('<input type="submit"');  print(' disabled name="end" value="End" />   ');
	print('</form');
}

function get_city_booktb($city)
{
	$city_book = array('books', 'books', 'books', 'books');
	return $city_book[$city];
}

function list_book($format='normal', $start=0, $items=50, $order = 0, $condition='')
{
	global $login_id, $role, $class, $comment_type, $book_sname, $favor;
	global $role_city, $disp_city;

	$table_name = "book";
	$tb_name = get_city_booktb($disp_city);

	$tr_width = 800;
	$background = '#efefef';

	$hasmore = false;
	$hasprev = false;

	$cond = "where (name != 'TBD' and name != '' and book_id != 0) ";
	if($format == 'tbd')
		$cond = "where (name = 'TBD' or name = '') ";
	if($class == 100)
		$cond .= "";
	else
		$cond .= " and class = $class";
	if(isset($book_sname)){
		if(is_numeric($book_sname))
			$cond .= " and book_id = '$book_sname' ";
		else
			$cond .= " and name like '%$book_sname%' ";
	}

	if($comment_type != 0)
		$cond .= " and comments != '' ";

	if($order == 1){
		$sql_time = "select book_id, count( distinct history.borrower) as btimes from history left join $tb_name using (book_id) where history.status<6 group by book_id ";
		$sql = " select * from $tb_name left join ($sql_time) btime using (book_id) $cond order by btime.btimes desc"; 
	}else if($order == 2){
		$sql_time = "select book_id, round(avg( history.data),1) as score from history left join $tb_name using (book_id) where history.status = 0x109 group by book_id ";
		$sql = " select * from $tb_name left join ($sql_time) score using (book_id) $cond order by score.score desc"; 
	}else if($order == 3){
		$sql_time = "select book_id, count(comments.words) as cmtimes from comments left join $tb_name using (book_id) group by book_id ";
		$sql = " select * from $tb_name left join ($sql_time) btime using (book_id) $cond order by btime.cmtimes desc"; 
	}else{
		$sql_time = "select book_id, count( distinct history.borrower) as btimes from history left join $tb_name using (book_id) where history.status<6 group by book_id ";
		$sql = " select * from $tb_name left join ($sql_time) btime using (book_id) $cond order by book_id asc"; 
	}

	if($condition == 'favor')
		$sql = "select * from favor left join $tb_name using (book_id) where member_id = '$login_id'";
	else if($condition == 'history')
		$sql = "select * from history left join $tb_name using (book_id) where borrower = '$login_id' and (history.status < 6) ";
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
		print_tdlist(array('编号', '书名','作者', '描述','评论','分类','评分','状态', '操作'));
	else if($format == 'brief'){
		if($order == 1 || $order == 0)
			print_tdlist(array('编号', '书名','作者','分类','次数','状态', '操作'));
		else if($order == 2)
			print_tdlist(array('编号', '书名','作者','分类','评分','状态', '操作'));
	}else if($format == 'class')
		print_tdlist(array('编号', '书名','作者','描述','推荐人','中图分类','会员分类','状态', '操作'));
	else if($format == 'tbd')
		print_tdlist(array('编号', '书名','作者','描述','推荐人','中图分类','会员分类','状态', '操作'));
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
		$id = $book_id&0xffff;
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
			print_td($data,35);
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

	$sql = "select * from user.user where user_id=\"$login_id\"";
	$res = mysql_query($sql) or die("Invalid query:".$sql.mysql_error());
	if($row = mysql_fetch_array($res)){
		if($row['activate'] == 0)
			return -1;
		return 0;
	}
	return -2;
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

function apply_share($book_id, $login_id, $date=0)
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

$table_name = "id_table_record";
$tr_width = 800;
$background = '#cfcfcf';
$table_head = "<table id='$table_name' width=600 class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 style='width:$tr_width.0pt;background:$background;margin-left:20.5pt;border-collapse:collapse'>";
//$width_px = 800;
//$table_head = "<table border=1 bordercolor='#0000f0', cellspacing='0' cellpadding='0' style='padding:0.2em;border-color:#0000f0;border-style:solid; width: $width_px"."px;background: none repeat scroll 0% 0% #e0e0f5;font-size:12pt;border-collapse:collapse;border-spacing:1;table-layout:auto'>";

function show_book($book_id)
{
	global $login_id, $table_head;

	print("介绍 - ");
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
	}

	print("《" . $name . "》");
	if($member_id != $login_id)
		$blink .= "&nbsp;<a href='book.php?action=add_favor&book_id=$book_id' >收藏</a>";
	$blink .= "&nbsp;<a href='javascript:add_score(this,$book_id);'>打分</a>";
	$blink .= "&nbsp;<a href='book.php?action=share&book_id=$book_id' >分享</a>";
	print("[" . get_book_status_name($status) . "]&nbsp;");
	print("得分:$score&nbsp");
	print("$blink");
	print($table_head);
	print('<tr>');
	print_tdlist(array('编号', 'ISBN','索引','价格','中图分类', '会员分类', 'Sponsor', '购买日期', '次数', '状态'));
	print("</tr>");
	print("<tr>");
	print_tdlist(array($book_id, $isbn, $index, $price, $class_name, $class_text, $sponsor, $buy_date, $times, get_book_status_name($status))); 
	print("</tr>");
	print("</table>");
	print("<br/>");

	print($table_head);
	print("<tr><td>$desc</td></tr>");
	print("</table>");

	print("评论<br>");
	list_comments($book_id, '', 1);
	print("当前借阅人<br>");
	show_borrower($book_id, 'out');
	print("等待列表<br>");
	show_borrower($book_id, 'wait');
	print("历史借阅记录<br>");
	show_borrower($book_id, 'history');
	print("评分记录<br>");
	show_borrower($book_id, 'score');

	return;
}



function list_comments($book_id='', $borrower='', $format=0, $last_days='')
{
	global $table_head, $login_id, $role;
	
	if($book_id == -2){
		$dbcomments = 'book.comments';
		$dbbooks = 'book.books';
	}else{
		$dbcomments = 'comments';
		$dbbooks = 'books';
	}

	$mail_url = get_cur_php();
	$web_root = get_cur_root();
	if($book_id == -2){
		$mail_url = "http://cedump-sh.ap.qualcomm.com/book/book.php";
		$web_root = "http://cedump-sh.ap.qualcomm.com/book";
		$book_id = '';
	}
	if($mail_url == ''){
			$mail_url = "http://cedump-sh.ap.qualcomm.com/book/book.php";
	}

	$cond = "1 ";
	if($book_id != '')
		$cond .= " and $dbcomments.book_id = $book_id";
	if($borrower != '') {
		if($format == 2)
			$cond .= " and cm.borrower = '$borrower'";
		else
			$cond .= " and $dbcomments.borrower = '$borrower'";
	}
	if($last_days != '')
		$cond .= " and (to_days(now()) - to_days(`timestamp`)) < $last_days";
	print($table_head);
	if($format == 1)
		print_tbline(array('序号', '用户', '日期', '评论', '命令'));
	else
		print_tbline(array('序号', '用户', '日期', '书名','评论', '命令'));
	$tc = "(select comment_id, borrower from $dbcomments)";
	$sql = "select $dbcomments.comment_id, $dbcomments.borrower, parent, words, date(timestamp) as dt, $dbcomments.book_id, $dbbooks.name, cm.borrower as parent_user from $dbcomments left join $tc as cm on $dbcomments.parent = cm.comment_id left join $dbbooks on $dbcomments.book_id = $dbbooks.book_id where $cond order by timestamp desc limit 100";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$comment_id = $row['comment_id'];
		$parent = $row['parent'];
		$parent_user = $row['parent_user'];
		$borrower = $row['borrower'];
		$comments = $row['words'];
		$date = $row['dt'];
		$comments = str_replace("\n", "", $comments);
		$book = $row['name'];
		$book_id = $row['book_id'];
		$count = mb_strlen($comments, "UTF-8");
		$char_count = strlen($comments);
		print("<tr>");
		if($role == 2 || $borrower == $login_id){
			$webroot = dirname($mail_url);
			$comment_id_link = "<a href=$webroot/edit_book.php?op=edit_comment_ui&comment_id=$comment_id>$comment_id</a>";
		}else
			$comment_id_link = $comment_id;
		print_td($comment_id_link, 30);
		$borrower_link = "<a href=$mail_url?action=list_comments&borrower=$borrower>$borrower</a>";
		$book_link = "<a href=$mail_url?action=show_borrower&book_id=$book_id>$book</a>";
		print_tdlist(array($borrower_link,$date));
		if($format != 1)
			print_td($book_link, 120);
		if($parent != 0){
			$comments = "回复:$parent_user:".$comments;
		}
		print_td($comments."($count)");
		$cmd = "<a href='$web_root/edit_book.php?op=add_comment_ui&book_id=$book_id&comment_id=$comment_id&borrower=$borrower'>回复</a>";
		print_td($cmd, 40);
	}
	print("</table>");
}

function add_want($book_id, $login_id)
{
	$sql = " select * from history where book_id = $book_id and borrower='$login_id' and (status = 0x111)";
	$res = read_mysql_query($sql);
	$rows = mysql_num_rows($res);
	if($rows > 0){
		print ("You already vote for this book");
		return 0;
	}
	add_record($book_id, $login_id, 0x111, false);
	$add = 1;
	$sql = "update books_nostock set times = `times` + $add where book_id = $book_id";
	$rows = update_mysql_query($sql);
	print("OK");
	return 1;
}


function cancel_recommend($book_id='')
{
	$sql = "update books_nostock set status = 0 where book_id = $book_id";
	$rows = update_mysql_query($sql);
	if($rows == 1)
		print("OK");
	else
		print("no update");
}

function list_recommend($book_id='', $borrower='', $status=-1, $last_days='')
{
	global $table_head, $login_id, $role;

	$mail_url = get_cur_php();
	if($mail_url == '')
		$mail_url = "http://cedump-sh.ap.qualcomm.com/book/book.php";

	$cond = "1 ";
	if($book_id != '')
		$cond .= " and comments.book_id = $book_id";
	if($borrower != '') {
		$cond .= " and borrower = '$borrower'";
	}
	if($last_days != '')
		$cond .= " and (to_days(now()) - to_days(`buy_date`)) < $last_days";
	if($status != -1)
		$cond .= " and status = $status ";
	else
		$cond .= " and status != 0 ";
	print($table_head);
	print_tbline(array('编号', '书名','作者', '描述','评论','推荐人', '类别', '热度', '操作'));
	$tc = "(select comment_id, borrower from comments)";
	$sql = "select * from books_nostock where $cond";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$borrower= $row['sponsor'];
		$book_id= $row['book_id'];
		$book_name = $row['name'];
		$comments = $row['comments'];
		$desc = $row['desc'];
		$author = $row['author'];
		$status = $row['status'];
		$azurl= $row['note'];
		$times = $row['times'];

		$status_string = array('取消', '捐赠', '推荐', '待购');
		$status_text = $status_string[$status];
		print("<tr>");
		$borrower_link = "<a href=$mail_url?action=list_comments&borrower=$borrower>$borrower</a>";
		if($azurl != '')
			$book_link = "<a href=$azurl>$book_name</a>";
		else
			$book_link = $book_name;
		print_td($book_id, 30);
		print_td($book_link, 150);
		print_td($author, 120);
		print_tdlist(array($desc, $comments));
		print_td($borrower_link, 60);
		print_td($status_text, 40);
		print_td($times, 30);
		$cmd = "";
		if($borrower == $login_id){
			$cmd .= "<a href='edit_book.php?op=edit_recommend_ui&book_id=$book_id'>编辑</a>";
			$cmd .= "&nbsp;&nbsp;<a href='javascript:cancel_recommend($book_id)'>取消</a>";
			if($status == 2)
				$cmd .= "&nbsp;&nbsp;<a href='edit_book.php?op=buy_book_ui&book_id=$book_id'>换购</a>";
		}else{
			$cmd = "<a href='javascript:want_read($book_id)'>想看</a>";
			if($status == 2)
				$cmd .= "&nbsp;&nbsp;<a href='edit_book.php?op=buy_book_ui&book_id=$book_id'>换购</a>";
		}
		if($role == 2 && $status == 3)
			$cmd .= "&nbsp;&nbsp;<a href='edit_book.php?op=buy_book_done&book_id=$book_id'>入库</a>";
		print_td($cmd, 120);
		print("</tr>");
	}
	print("</table>");
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
		}else if($status == 264){
			$status_text = "入会";
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
	global $table_head;
	print($table_head);

	if($format == 'history')
		print_tdlist(array('编号', '书名','借阅人','借出日期', '归还日期', '评分'));
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
		$sdate = $row['sdate'];
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
		if($format == 'history')
			print_tdlist(array($book_id, $name, $user_name,$date,$sdate, $status_text)); 
		else
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

function add_record_one($book_id, $user_id, $adate, $bdate, $rdate, $sdate, $status=1, $data=0, $misc='')
{
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$sql = " insert into history set `borrower`='$user_id', book_id=$book_id, adate='$adate', bdate= '$bdate', rdate='$rdate', sdate='$sdate', status=$status, data=$data, misc='$misc'";
	$res = update_mysql_query($sql);
	return $res;
}

function add_record_full($book_id, $user_id, $bdate, $sdate, $status=1, $data=0, $misc='')
{
	$time = time();
	$time_start = strftime("%Y-%m-%d %H:%M:%S", $time);
	$sql = " insert into history set `borrower`='$user_id', book_id=$book_id, adate='$bdate', bdate= '$bdate', rdate='$sdate', sdate='$sdate', status=$status, data=$data, misc='$misc'";
	$res = update_mysql_query($sql);
	return $res;
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
	else if($status == 0x106 || $status == 0x105)
		$sql = " update history set sdate= '$time_start', status=$status where `record_id` = $record_id";
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

function add_member($user, $name, $email, $role) {
	$sql1 = "replace member set user = '$user', user_name= '$name', email='$email', role = $role ";
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_affected_rows($res1))
		return true;
	return false;
}

function check_member_score($user, $score){
	$sql = "select * from member where `user` = '$user' ";
	$res = read_mysql_query($sql);
	while($row = mysql_fetch_array($res)){
		$score = $row['score'];
		$score_used = $row['score_used'];
		return $score - $score_used;
	}
	return 0;
}

function deduce_member_score($user, $score){
	$sql = "update member set `score_used` = `score_used` + $score where `user` = '$user' ";
	$res=mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($rows=mysql_affected_rows() > 0)
		return true;
	return false;
}

function set_member_attr($user, $prop, $value) {
	$sql = "update member set `$prop` = '$value' where `user` = '$user' ";
	$res=mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	if($rows=mysql_affected_rows() > 0)
		return true;
	return false;
}

function get_user_name($user){
	return get_user_attr($user, 'name');
}

function get_user_email($user){
	return get_user_attr($user, 'email');
}

function get_user_attr($user, $prop) {
	$sql1 = "select * from user.user where user_id ='$user'";
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_fetch_array($res1)){
		if(isset($row1["$prop"]))
			return $row1[$prop];
	}
	$sql1 = "select * from member where user ='$user'";
	if($prop == 'name')
		$prop = 'user_name';
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_fetch_array($res1)){
		if(isset($row1[$prop]))
			return $row1["$prop"];
	}
	return -1;
}

function set_user_attr($user, $prop, $value) {
	$sql1 = "update user.user set `$prop` = '$value' where user_id ='$user'";
	$res1=mysql_query($sql1) or die("Invalid query:" . $sql1 . mysql_error());
	if($row1=mysql_affected_rows($res1) > 0)
		return true;
	return false;
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
	$sql = "select email from member where role = 2";
	$res = read_mysql_query($sql);
	$cc = "";
	while($row = mysql_fetch_array($res)){
		$user = $row['user'];
		if($user == 'xling')
			continue;
		$cc .= $row['email'];
		$cc .= ";";
	}
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



/* only used once*/
function import_favor_from_history()
{
	$sql =  "select borrower, book_id, status from history where status = 0 or status = 2 or status = 0x104";
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());
	while($row=mysql_fetch_array($res)){
		add_favor($row['borrower'], $row['book_id']);
	}

}

function get_city_str($c='')
{
	global $city;
	if($c == '')
		$c = $city;
	$cityname = array('BJ', 'SH', 'SZ', 'XA');
	$cn = $cityname[$c];
	return $cn;
}

function get_city_name($c='')
{
	global $city;
	if($c == '')
		$c = $city;
	$cityname = array('北京', '上海', '深圳', '西安');
	$cn = $cityname[$c];
	return $cn;
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
