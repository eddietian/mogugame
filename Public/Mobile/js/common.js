
function getBow(){//判断浏览器类型

	var Sys = {};

	var ua = navigator.userAgent.toLowerCase();

	var s;

	(s = ua.match(/msie ([\d.]+)/)) ? Sys.ie = s[1] :

	(s = ua.match(/firefox\/([\d.]+)/)) ? Sys.firefox = s[1] :

	(s = ua.match(/chrome\/([\d.]+)/)) ? Sys.chrome = s[1] :

	(s = ua.match(/opera.([\d.]+)/)) ? Sys.opera = s[1] :

	(s = ua.match(/version\/([\d.]+).*safari/)) ? Sys.safari = s[1] : 0;



	//以下进行测试

	if (Sys.ie &&  Sys.ie=='6.0'  ) return 'IE6';

	else if (Sys.ie &&  Sys.ie=='7.0'  ) return 'IE7';

	else if (Sys.ie &&  Sys.ie=='8.0'  ) return 'IE8';

	else if (Sys.ie &&  Sys.ie=='9.0'  ) return 'IE9';

	else if (Sys.ie &&  Sys.ie=='10.0'  ) return 'IE10';

	else if (Sys.ie &&  Sys.ie=='11.0'  ) return 'IE11';

	else if (Sys.firefox) return '-moz-';

	else if (Sys.chrome) return '-webkit-';

	else if (Sys.opera) return '-o-';

	else if (Sys.safari) return '-webkit-';

	else return '';

}

//设为首页 < a onclick="SetHome(this,window.location)" > 设为首页 < /a>

function SetHome(sUrl) {

	if (document.all) { 

		document.body.style.behavior='url(#default#homepage)'; 

		document.body.setHomePage(sUrl); 

	} 

	else if (window.sidebar) { 

		if(window.netscape) { 

			try { 

				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect"); 

			} 

			catch(e) { 

				alert("抱歉，您所使用的浏览器无法完成此操作。\n\n您需要手动将【"+sUrl+"】设置为首页。");

			} 

		} 

		var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components. interfaces.nsIPrefBranch); 

		prefs.setCharPref('browser.startup.homepage',sUrl);

	}else{

		alert("抱歉，您所使用的浏览器无法完成此操作。\n\n您需要手动将【"+sUrl+"】设置为首页。");

	}

}

// 加入收藏 < a onclick="AddFavorite(window.location,document.title)" >加入收藏< /a>

function AddFavorite(sURL, sTitle){

    try

    {

        window.external.addFavorite(sURL, sTitle);

    }

    catch (e)

    {

        try

        {

            window.sidebar.addPanel(sTitle, sURL, "");

        }

        catch (e)

        {

            alert("加入收藏失败，请使用Ctrl+D进行添加");

        }

    }

}

userislogin();

function userislogin(){

    $.ajax({
        url:UURL+"/islogin",
        type: 'POST',
        dataType: 'json',
        jsonp: "callbackparam",
        jsonpCallback:"returnJsonplog",
        timeout: 10000,
        success: function(data){
            if(data.status==1){
                $('.toplogin').empty().append('<p><a href="'+SURL+'/index">'+data.account+'</a></p><p><a onclick="userislogout()">退出</a></p>');
                $('#giftloginregbox').empty().append('<a href="'+SURL+'/index">'+data.account+'</a> | <a onclick="userislogout()">退出</a>');
            } else {
                $('.toplogin').empty().append('<i></i><p><a href="'+UURL+'/login" >登录</a></p>');
                $('#giftloginregbox').empty().append('<a href="'+UURL+'/login.html" class="g-link">登录</a><span class="g-space">|</span><a href="'+UURL+'/register.html" class="g-link">注册</a>');
            }
        }
	}); 
}

function userislogout() {
    $.ajax({
        url:UURL+"/logout",
        type: 'POST',
        dataType: 'json',
        success:function(data) {
            window.location.reload();
        }
    });
}