// 登录
var username = getCookie('login_name');
if(!username) {
	$('#relogin').show();
	$('#logined').hide();
}else {
	$('#relogin').hide();
	$('#logined').show();
	$('#top-username').html(username);
}

// 配置幻灯
var navactive, actlocation = navactive ? navactive : 0;
if (actlocation == '0') {
	$('#slide-box').slideBox({
		mode : 'fade',
		nextBtn : true,
		prevBtn : true,
		delay: 5
	});
	var sobj = $('#slide-box .slide-nav');
	var sw = sobj.outerWidth(true);
	sobj.css('margin-left', -1*sw/2+'px');
};
if (parseInt(actlocation) >= 0) {
	$('.g-menu a').eq(actlocation).addClass('active');
};
// 顶栏全部游戏
$('.g-allgame-trigger, .g-allgame-list').hover(function(){
	$('.g-allgame-trigger').addClass('active');
	$('.g-allgame-list').show();
},function(){
	$('.g-allgame-trigger').removeClass('active');
	$('.g-allgame-list').hide();
});
// 置顶
$('#totop').click(function(){
	$('html,body').animate({scrollTop:0},300);
});
// 游戏排行榜
$('.rank-game-list li').mouseenter(function(){
	$(this).addClass('active').siblings().removeClass('active')
}).eq(0).trigger('mouseenter');
// 新闻页面TAB
$('#newstype-tab a').mouseenter(function(){
	var i = $(this).index();
	$(this).addClass('active').siblings().removeClass('active');
	$('#news-content-box .news-list').hide().eq(i).show();
});
// 领取礼包
$('.giftmix-left .btn-getgift').click(function(){
	if( !sign ) {
		// alert('请先登录');
		window.location.href = C9377.app_url +'/login.php?ac=login';
		return;
	}else {
		$.getJSON('/api/get_new_card.php?card_type='+card_type+'&sign='+sign+'&callback=?', function(json){
			if( json.status != 1 ) {
				// alert(json.msg);
				$('#pop-gift .success').hide();
				$('#pop-gift .false').html(json.msg).show();
			}else {
				$('#pop-gift .false').hide();
				$('#pop-gift .success').show();
				$('#card').html(json.msg);
				setCopyTxt('.btn-copy', json.msg);
			}
			$('#pop-gift, #mask').show();
		});
	}
});
$('#btn-subcard, .pop-close').click(function(){
	$('#pop-gift, #mask').hide();
});
// 复制功能
function setCopyTxt(elem, code, tip){
	var clip = new ZeroClipboard.Client();
	var w = $(elem).innerWidth();
	var h = $(elem).innerHeight();
	$(elem).append(clip.getHTML(w,h));
	clip.setText(code);
	clip.addEventListener('onComplete', function(){
		var tip = tip ? tip : '复制成功！请在游戏中用ctrl+v进行粘贴。';
		alert(tip);
		return false;
	});
}
// 懒加载
function lazy_img(){
	var top = $(document).scrollTop();
	var height = $(window).height();
	lazy_img.img.each(function(){
		var _this = $(this);
		if(_this.attr('src')){
			_this.removeClass('lazy-load');
			return;
		}
		
		var y = _this.offset().top;
		var h = _this.height();
		if(y >= top && y <= top + height || y < top && y + h >= top){
			_this.attr('src', _this.attr('_src')).removeClass('lazy-load');
		}
	});
	lazy_img.img = $('img.lazy-load');
}
lazy_img.img = $('img.lazy-load');
$(window).scroll(lazy_img);
lazy_img();

// 通用游戏滚动截图
if (actlocation == '99') {
	$('#gamepic-box').slideBox({
		mode : 'left',
		optevent: 'mouseenter', // 操作事件：click, mouseenter
		navCell : false, // 导航按钮
		nextBtn : true, // 下一页按钮
		prevBtn : true, // 上一页按钮
		autoPlay : true, // 自动播放
		viewItem : 3 // 滚动数量
	});
}

//input默认值
	var text_val=$('.input-focus').val();
	$('.input-focus').focus(function(){
		if(this.value==text_val)this.value='';
	}).blur(function(){
		if(this.value=='')this.value=text_val;
	});
	
//搜索游戏
	function check_search(form) {
		var keywords = $(form).find('input[name="q"]').val(); 
		if( !keywords || keywords == '搜索游戏' ) {
			alert('请输入搜索关键字。');
			$(form).find('input[name="q"]').focus();
			return false;
		}
		return true;
	}