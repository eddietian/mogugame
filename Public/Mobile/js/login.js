userislogin();
function userislogin(){
	document.getElementById('login').innerHTML = "<a href='http://my.fpwap.com/dl.html?location="+window.location.href+"' target='_self'>µÇÂ¼</a>";
	document.getElementById('reg').innerHTML = "<a href='http://my.fpwap.com/reg.html?location="+window.location.href+"' target='_self'>×¢²á</a>";
$.ajax({
	 url:"http://fahao.fpwap.com/xmw/fun.php?action=islogin",
	 type: 'GET',
	 dataType: 'jsonp',
	 jsonp: "callbackparam",
	 jsonpCallback:"returnJsonplog",
	 timeout: 10000,
	 success: function(data){
		 if(data.status==1){
			document.getElementById('login').innerHTML = "<a href='http://my.fpwap.com'>"+data.username+"</a>";
			document.getElementById('reg').innerHTML = "<a href='http://my.fpwap.com/fun.php?action=loginout' target='_self'>ÍË³ö</a>";
		 }
	 }
	 }); 
}
