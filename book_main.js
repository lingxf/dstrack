function load_intro(){
	var intr = document.getElementById("div_homeintro");
	if(intr){
		intr.innerHTML="Please wait...";
		url = "brqclub.htm";
		loadXMLDoc(url,function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				intr.innerHTML=xmlhttp.responseText;
			}else{
				if(xmlhttp.status=='0')
				intr.innerHTML="Please wait...";
				else
				intr.innerHTML=xmlhttp.status+xmlhttp.responseText;
				}
			});
	}

}

function change_class(bookclass, view){
	url = "show_book.php?";
	url = url + "class="+bookclass;
	if(view != 0)
		url = url + "&view="+view;

	document.getElementById("div_booklist").innerHTML="Please wait...";
	loadXMLDoc(url,function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
			}else{
			if(xmlhttp.status=='0')
			document.getElementById("div_booklist").innerHTML="Please wait...";
			else
			document.getElementById("div_booklist").innerHTML=xmlhttp.status+xmlhttp.responseText;
			}
	});
};

function change_perpage(page, view){
	url = "book.php?";
	url = url + "action=library&items_perpage="+page;
	if(view != 0)
		url = url + "&view="+view;
	window.location.href = url;
	return;
};


function change_order(order, view){
	url = "show_book.php?";
	url = url + "order="+order;
	if(view != 0)
		url = url + "&view="+view;

	document.getElementById("div_booklist").innerHTML="Please wait...";
	loadXMLDoc(url,function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
			}else{
				if(xmlhttp.status=='0')
				document.getElementById("div_booklist").innerHTML="Please wait...";
				else
				document.getElementById("div_booklist").innerHTML=xmlhttp.status+xmlhttp.responseText;
			}
	});
};

function book_search(){
	url = "show_book.php?";
	bookname = document.getElementById("id_book_name").value;
	url = url + "book_sname="+bookname;
	document.getElementById("div_booklist").innerHTML="Please wait...";
	loadXMLDoc(url,function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
			}else{
			if(xmlhttp.status=='0')
			document.getElementById("div_booklist").innerHTML="Please wait...";
			else
			document.getElementById("div_booklist").innerHTML=xmlhttp.status+xmlhttp.responseText;
			}
			});
};

function show_share_choice(tdc, book_id)
{
	var result = confirm("Do you want to share your reading feelings for this book in seminar?");
	var url = "book_action.php?action=share&book_id="+book_id;
	if(result)
	loadXMLDoc(url, function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
			}
		});
};

function add_score(tdc, book_id)
{
	var result = prompt("Please input score 1-5:");
	if(result < 1 || result > 5){
		alert("score shall be 1-5");
		return;
	}
	var url = "book_action.php?action=add_score&book_id="+book_id+"&score="+result;
	loadXMLDoc(url, function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				//tdc.innerHTML = result;
				location.reload();
				//document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
				//setTimeout("windows.location.href="+backurl, 1000);
			}
	});
	return;
};

function want_read(book_id)
{
	var result = confirm("Are you interested in this book?:");
	var url = "book_action.php?action=add_want&book_id="+book_id;
	if(result)
	loadXMLDoc(url, function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				if(xmlhttp.responseText.substr(0, 2) == "OK")
					location.reload();
				else
					alert(xmlhttp.responseText);
				//document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
				//setTimeout("windows.location.href="+backurl, 1000);
			}
	});
	return;
};

function cancel_recommend(book_id)
{
	var result = confirm("Do you really want to cancel?");
	var url = "book_action.php?action=cancel_recommend&book_id="+book_id;
	if(result)
	loadXMLDoc(url, function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				if(xmlhttp.responseText.substr(0, 2) == "OK")
					location.reload();
				else
					alert(xmlhttp.responseText);
				//document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
				//setTimeout("windows.location.href="+backurl, 1000);
			}
	});
	return;
};

function deduce_member_score(tdc, member)
{
	var result = prompt("Deduce Score:");
	var url = "book_action.php?action=deduce_member_score&borrower="+member+"&score="+result;
	loadXMLDoc(url, function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				//tdc.innerHTML = result;
				location.reload();
				//document.getElementById("div_booklist").innerHTML=xmlhttp.responseText;
				//setTimeout("windows.location.href="+backurl, 1000);
			}
	});
	return;
};

