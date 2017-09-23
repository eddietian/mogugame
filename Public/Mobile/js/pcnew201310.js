$(document).ready(function(){

	tabChange('.tag-tit-ul','.box-text',false,'.open-test'); //开测表

	tabChange('.tag-tit-ul','.more',false,'.open-test'); //开测表

	tabChange('.friend-link .tag-tit-ul','.friend-link>.box-text'); //友情链接

	tabChange('.game-info-down .tag-tit-ul2','.game-info-down .context-con'); //网游详情页下载

	

	

	/*  头部导航 重置宽度*/

	var nav_list_length=$('.seo-nav-list').find('.list').length;

	$('.seo-nav-list').css('width',nav_list_length*101 + 'px');

	/*  头部导航 kfzs */
	$('.header:eq(0)').find('.kfzs').hover(
	function () {
		$(this).find('.kfzs-list').show();
		$(this).children('a').addClass('current');
	},function () {
		$(this).find('.kfzs-list').hide();
		$(this).children('a').removeClass('current');
	});


	/*  头部导航 seo */

	$('.header:eq(0)').find('.seo-nav').hover(

	function () {

		$(this).find('.seo-nav-list').show();

		$(this).children('a').addClass('current');

	},function () {

		$(this).find('.seo-nav-list').hide();

		$(this).children('a').removeClass('current');

	});

	/* 头部导航 二维码*/

	$('.header:eq(0)').find('.wechat').hover(

	function () {

		$(this).find('.code').show();

		$(this).children('a').addClass('current');

	},function () {

		$(this).find('.code').hide();

		$(this).children('a').removeClass('current');

	});

	/* 搜索框 类型 */

	$('.search-type:eq(0)').hover(

	function () {

		$(this).addClass('current');

		$(this).children('.search-list').children().removeClass('current');

		$(this).children('.search-list').show();

	},function () {

		$(this).removeClass('current');

		$(this).children('.search-list').hide();

	});

	/* 搜索框 类型选择 */

	$('.search-list:eq(0)').children('li').bind({

		'click':function(){

			$('.search-type:eq(0)').children('span').html(this.innerHTML);

			$(this).parent().hide();

			$('#search_type').val($(this).attr('i-data'));

		},'mouseenter':function(){

			$(this).addClass('current').siblings().removeClass('current');

		}

	});

	/* 搜索框 文本框 */

	$('.search-con:eq(0)').children('.search-text').bind({

		'focus':function(){

			$(this).css('color','#333');

			if($(this).val()=='请输入关键字'){

				$(this).val('');

			}

		},'blur':function(){

			if($(this).val()==''){

				$(this).css('color','#a8a8a8').val('请输入关键字');

			}

		}

	});

	

	/* 搜索框 文本框 列表*/

	$('.result-list:eq(0)').delegate('li','click',function(){

	//	$('.search-text:eq(0)').val(this.innerHTML);

		$(this).parent().hide();

	});

	$('.result-list:eq(0)').delegate('li','mouseenter',function(){

		$(this).addClass('current').siblings().removeClass('current');

	});

	$("body").click(function(){

		$(".result-list:eq(0)").hide();

	});

	

	/* 推广 */

	$('.speard-con:eq(0)').delegate('a','mouseenter',function(){

		$(this).children('.meng').stop().animate({'bottom':"0"},300);

	});

	$('.speard-con:eq(0)').delegate('a','mouseleave',function(){

		$(this).children('.meng').stop().animate({'bottom':"-100%"},300);

	});

	/* 弹窗 */

	$(".close").click(function(){

		$("#pb_popup").hide();

		$('body').removeClass('popup_of');

	});

	$('#pb_popup').bind({'click':function(){

			$(this).hide();

			$('body').removeClass('popup_of');

	}});

	$('#pb_popup').find('.popup').bind({'mouseenter':function(){

		$('#pb_popup').unbind();

	},'mouseleave':function(){

		$('#pb_popup').bind({'click':function(){

			$(this).hide();

			$('body').removeClass('popup_of');

		}});

	}});

	

	/* 分页页码 */

	var page_time=null;

	$('.page-change:eq(0)').find('.now').hover(

	function () {

		clearTimeout(page_time);

		$(this).addClass('current');

		$(this).parent().children('.page-change-list').show();

	},function () {

		var $This=$(this);

		page_time=setTimeout(function(){

			$This.parent().children('.page-change-list').hide();

			clearTimeout(page_time);

			$This.removeClass('current');

		},500)

		

	});

	$('.page-change:eq(0)').find('.page-change-list').bind({'mouseenter':function(){

		clearTimeout(page_time);

	},'mouseleave':function(){

		var $This=$(this);

		page_time=setTimeout(function(){

			$This.hide();

			$('.page-change:eq(0)').find('.now').removeClass('current');

		},500)

	}});

	

	/* 固定导航 */

	(function(window){

		if(getBow() != 'IE6'){

			var This={};

			This.info_con=$('.sub-nav').eq(0);

			if($('.sub-nav').length>0){

				This.limit=This.info_con.offset().top;

				This.stat=0;

				This.height=This.info_con.height();

				$win=$(window);

				$win.scroll(function(){

					This.height=This.info_con.height();

					if($win.height() > This.height){

						This.change_pos();

					}

				});

				$win.resize(function(){

					This.change_pos();

				});

				This.change_pos=function(){

					This.height=This.info_con.height();

					if($win.height() > This.height && $win.scrollTop() > This.limit){

						if(!This.stat){

							This.info_con.addClass('sub-nav-fixed');

							This.stat=1;

						}

					}else{

						This.info_con.removeClass('sub-nav-fixed');

						This.stat=0;

					}

				}

			}

		}

	})(window);

});



function tabChange(ctrl,ctrled,stat,parent){  //tab切换函数

	stat=stat || false ;

	parent= parent || window.document;

	$(parent).each(function(){

		var $This=$(this);

		$This.find(ctrl).children().each(function(){

			$(this).bind({'mouseenter':function(){

				var p_index = $(this).index();

				$(this).addClass("current").siblings().removeClass("current");	

				var getbow=getBow();

				if(getbow!='IE6' && getbow!='IE7' && getbow!=''){

					if(stat)

						$This.find(ctrled).children().eq(p_index).fadeIn(500).siblings().fadeOut(500);

					else

						$This.find(ctrled).children().eq(p_index).show().siblings().hide();

				}else{

					$This.find(ctrled).children().eq(p_index).show().siblings().hide();

					

				}

			}});

		});

	});

	

}

function tabChange2(ctrl,ctrled,stat,parent){  //tab切换函数

	stat=stat || false ;

	parent= parent || window.document;

	$(parent).each(function(){

		var $This=$(this);

		$This.find(ctrl).children().each(function(){

			$(this).bind({'click':function(){

				var p_index = $(this).index();

				$(this).addClass("current").siblings().removeClass("current");	

				var getbow=getBow();

				if(getbow!='IE6' && getbow!='IE7' && getbow!=''){

					if(stat)

						$This.find(ctrled).children().eq(p_index).fadeIn(500).siblings().fadeOut(500);

					else

						$This.find(ctrled).children().eq(p_index).show().siblings().hide();

				}else{

					$This.find(ctrled).children().eq(p_index).show().siblings().hide();

					

				}

			}});

		});

	});

	

}



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
	document.getElementById('login').innerHTML = "<a href='http://my.fpwap.com/dl.html?location="+window.location.href+"' target='_self'>登录</a>";
	document.getElementById('reg').innerHTML = "<a href='http://my.fpwap.com/reg.html?location="+window.location.href+"' target='_self'>注册</a>";
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
			document.getElementById('reg').innerHTML = "<a href='http://my.fpwap.com/fun.php?action=loginout' target='_self'>退出</a>";
		 }
	 }
	 }); 
}