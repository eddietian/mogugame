$('#user-menu li').click(function(){
	var i = $(this).index();
	$(this).addClass('active').siblings().removeClass('active');
	$('#user-section-wrap .user-section').hide().eq(i).show();
});

// 用户个人设置-修改头像
function avapopOpen(){
	// if (!BBS_ID) {alert('请先激活论坛账号'); return;};
	$('#mask, #avatar-pop').show();
}
function uploadevent(data){
	if (data == 1) {
		window.location.reload();
	};
}
function avapopClose(){
	$('#mask, #avatar-pop').hide();
}
// getAvaList();
function getAvaList(){
	$.getJSON('http://bbs.9377.com/api/get_9377_avatar.php?callback=?',function(json){
		var avahtml = '';
		for(var i in json){
			avahtml += '<a href="javascript:;" class="item" isrc="'+json[i].name+'"><img src="'+json[i].path+'" alt=""></a>'
		}
		$('#sys-ava-list').html(avahtml);
	})
}
$('.ava-uptype a').click(function(){
	$(this).addClass('active').siblings().removeClass('active');
	$('.ava-upwrap .ava-upbox').eq($(this).index()).show().siblings().hide();
});
$('.sys-ava-list .item').live('click', function(){
	var nsrc = $(this).find('img').attr('src');
	$('#curavatar img').attr('src', nsrc);
	$(this).addClass('active').siblings().removeClass('active');
});
$('#sys-ava-submit').click(function(){
	$('.sys-ava-list .item').each(function(i){
		if ($(this).hasClass('active')) {
			var isrc = $(this).attr('isrc');
			var data = {
				'mod': 'spacecp',
				'ac': 'avatar',
				'do': 'avatar9377',
				'file': isrc
			}
			$.ajax({
				'url': '/users/users_do.php',
				'type': 'GET',
				'data': data,
				'async': false,
				'cache': false,
				success: function(info){
					if (info == 1) {
						$('#s-tips').text('保存成功！');
						window.location.reload();
					}else if( info == -3) {
						$('#s-tips').text('请先激活论坛账号');
					}else{
						$('#s-tips').text('保存失败，请重试');
					}
				},
				error: function(e){
					alert('网络繁忙！');
				}
			});
		};
	});
});
// 手机绑定
function check_form(){
	var cellphone = $('#cellphone').val();
	if(!/^1\d{10}$/.test(cellphone)){
		alert('手机格式不正确');
		$('#cellphone').focus();
		return false;
	}
	
	
	var captcha = $('#captcha').val();
	if(captcha.length == 0){
		alert('请先获取验证码');
		$('#captcha').focus();
		return false;
	}
	
	if(!/^\d+$/.test(captcha)){
		alert('验证码格式不正确');
		$('#captcha').focus();
		return false;
	}
	
	return true;
}

function cellphone_captcha(){
	var cellphone = $('#cellphone').val();
	var code = $('#code').val();
	if (exist_phone && bind_phone) {
		if (!cellphone) {
			alert('请输入已绑定的手机号码（'+exist_phone+'）');
			$('#cellphone').focus();
			return false;
		};
	};
	if(!/^1\d{10}$/.test(cellphone)){
		alert('手机格式不正确');
		$('#cellphone').focus();
		return;
	}

	if( !code ) {
		alert('请输入验证码');
		return false;
	}
	
	$('#captcha_tr').show();
	$.ajax({
		type: 'post',
		dataType: 'json',
		cache: false,
		url: '/users/users_do.php',
		data: {'do': 'bind_cellphone', step: 1, cellphone: cellphone, 'code':code},
		success: function(data){
			//alert(data);
			if(data.status == 0){
				refetch.time = 60;
				refetch(true);
			}else if(data.status == -1){
				alert('手机格式不正确');
			}else if(data.status == -2){
				alert('验证码发送太频繁，请稍后再试');
				if(data.remain){
					refetch.time = parseInt(data.remain);
					refetch(true);
				}
			}else if(data.status == -3) {
				alert('对不起，您输入的手机号码和绑定号码不一致。');
			}else if(data.status == -4) {
				$('#img_sec').trigger('click');
				alert('请输入正确的验证码。');
			}else{
				alert('发送失败');
			}
		}
	});
}

function refetch(start){
	if(start === true){
		$('#fetch').attr('disabled', true);
		refetch.interval = setInterval(refetch, 1000);
		refetch.time--;
	}else if(0 == refetch.time){
		clearInterval(refetch.interval);
		$('#fetch').val(refetch.text);
		$('#fetch').attr('disabled', false);
		return;
	}
	
	$('#fetch').val('('+ refetch.time +') '+ refetch.text);
	
	refetch.time--;
}
refetch.text = '重新获取验证码';
refetch.interval = null;
refetch.time = 60;