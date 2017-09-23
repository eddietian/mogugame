var SURL = '/media.php?s=/',
	UURL = '/media.php?s=/Member/';
// xigu  lwx
(function() {
    var XGUC = window.XGUC = window.XGUC || {};
    (function() {
        XGUC.init = function() {
            XGUC.is_login();
        };

        XGUC.is_login = function() {
            $.ajax({
                type: 'POST',
                async: false,
                dataType: 'json',
                url: UURL + 'is_login',
                data: '',
                success: function(data) {
                    switch (parseInt(data['status'])) {
                   case 1:
                        $('.login-register').addClass('logon').removeClass('widthoutLogin');
						$('.login-register-link.withoutLogon-link').addClass('hidden');
						$('.login-register-link.logon-link').removeClass('hidden');
						$('#topShowName').text('您好，' + data.account).attr('href',UURL +'index');;
						return;
                        break;
                    default:
						$('.login-register').removeClass('logon').addClass('widthoutLogin');
						$('.login-register-link.withoutLogon-link').removeClass('hidden');
						$('.login-register-link.logon-link').addClass('hidden');
						$('#topShowName').text('').attr('href','#');
                        return false;
                        break; 
                    }
                },
                error: function() {
					$('.withoutLogon-link').removeClass('hidden');
					$('.logon-link').addClass('hidden').find('#topShowName').attr('href','#').empty();
                },
                cache: false
            });
			$('.top-login-link').on('click',function() {
				XGUC.login('xglogin');				
			});
			
			$('.top-register-link').on('click',function() {
				XGUC.reg('xgregister');				
			});
			
			$('#topLoginOut').on('click',function() {
				XGUC.login_out();
            });

            $('#xglogin .link-close').click(function() {
                XGUC.login_close();
            });

            $('#xgregister .link-close').click(function() {
                XGUC.reg_close();
            });
        };
		
        XGUC.login_out = function() {
            $.ajax({
                type: 'POST',
                async: false,
                dataType: 'json',
                url: UURL + 'logout',
                data: 'reurl=' + location.href,
                success: function(data) {
                    if (data.reurl) {
                        location.href = data.reurl;
                    } else {
                        location.reload();
                    }
                },
                cache: false
            });
        };

        XGUC.login = function(pop_id) {
            XGUC.reg_close();
            if (!pop_id) {
                pop_id = 'xglogin';
            }
            if (pop_id == 'xglogin') {
				$('#'+pop_id).addClass('active');
            }			
			var form_id = 'loginform';			
			$('#goRegisterPop').on('click',function() {
				XGUC.login_close();
				XGUC.reg('xgregister');	
			});			
			(function i() {
				$('.g-login-pop').find('input[type=text]').val('');
				$('.g-login-pop').find('input[type=password]').val('');
				$('.g-login-pop').find('.input-optimize').removeClass('error').removeClass('correct').find('.placeholder').show();
				$('#errMsg').text('');
			})();
			var n = $('.input-optimize');
			n.on('click', '.placeholder',function() {
				$(this).hide(),
				$(this).siblings('input').focus(),
				$(this).siblings('input').val().match(/^\s*|\s*$/g) && $(this).siblings('input').val('')
			});
			n.on('blur', 'input',function() {
				var i = $(this).siblings('.placeholder');
				0 != i.length && ('' == $(this).val() || $(this).val() == i.text()) && ($(this).val(''), i.show())
				$('#errMsg').text('');
			});
			n.on('focus', 'input',function() {
				var i = $(this).siblings('.placeholder');
				0 != i.length && i.hide()
			});
            $('#' + form_id + ' #loginSubmit').val('登录').attr('disabled', false);
            $('#' + form_id).unbind('submit').bind('submit',
            function(event) {
                event.preventDefault();
				var username = $.trim($('#' + form_id + ' #loginInputUname').val());
                if (username == '') {
                    $('#errMsg').text('用户名不能为空！');
                    $('#' + form_id + ' #loginInputUname').focus();
                    return false;
                }
                var password = $.trim($('#' + form_id + ' #loginPassword').val());
                if (password == '') {
                    $('#errMsg').text('密码不能为空！');
                    $('#' + form_id + ' #loginPassword').focus();
                    return false;
                }
				$('#errMsg').text('');
				$.ajax({
					type: 'POST',
					async: true,
					dataType: 'json',
					url: UURL + 'login',
					data: $('#' + form_id + '').serialize(),
					beforeSend: function() {
						$('#' + form_id + ' #loginSubmit').val('登录中').attr('disabled', true);
					},
					success: function(data) {
						switch (parseInt(data['status'])) {
							case 1:
								$('#errMsg').removeClass('error-msg').addClass('right-msg').text(data.msg);
								setTimeout(function () {
									var reurl = $('#' + form_id).data('reurl');
									if (reurl) {
										location.href = reurl;
									} else {
										location.reload();
									}
								}, 1000);
								break;

							case -3:
								layer.prompt({
									title: '动态密码'
								}, function (value, index, elem) {
									$.ajax({
										type: 'POST',
										async: true,
										dataType: 'json',
										url: UURL + 'login',
										data:{account:username,password:password,code:value},
										success:function(res){
											switch (parseInt(res.status)) {
												case 1:
													$('#errMsg').removeClass('error-msg').addClass('right-msg').text(res.msg);
													setTimeout(function () {
														var reurl = $('#' + form_id).data('reurl');
														if (reurl) {
															location.href = reurl;
														} else {
															location.reload();
														}
													}, 1000);
													break;
												default:
													$('#errMsg').removeClass('right-msg').addClass('error-msg').text(res['msg']);
													$('#' + form_id + ' #loginSubmit').val('登录').attr('disabled', false);
													break;
											}
										}
									});
									layer.close(index);
								});

							default:
								$('#errMsg').removeClass('right-msg').addClass('error-msg').text(data['msg']);
								$('#' + form_id + ' #loginSubmit').val('登录').attr('disabled', false);
								break;
						}
						return false;
					},
					error: function() {
                        $('#errMsg').removeClass('right-msg').addClass('error-msg').text('服务器故障，稍后再试');
						$('#' + form_id + ' #loginSubmit').val('登录').attr('disabled', false);
					},
					cache: false
				});
				return false;
            });
        };

        XGUC.login_close = function() {
			$('#xglogin').removeClass('active');
        };

        XGUC.reg = function(pop_id) {
            XGUC.login_close();
            if (!pop_id) {
                pop_id = 'xgregister';
            }
            if (pop_id == 'xgregister') {
				$('#xgregister').addClass('active');
            }
			(function i() {
				$('.g-register-pop').find('input[type=text]').val('');
				$('.g-register-pop').find('input[type=password]').val('');
				$('.g-register-pop').find('.input-optimize').removeClass('error').removeClass('correct').find('.placeholder').show();
				
			})();		
			$('.checkcode').on('click',function() {
				var e = (new Date).getTime();
				$(this).attr('src', UURL+'verify/t/' + e);
			});
			$('.m-register-box').find('.checkbox-optimize').on('click',function() {
				$(this).hasClass('active') ? ($(this).removeClass('active'), $(this).children('input[type=hidden]').val(0), $(this).siblings('.agree-txt').removeClass('active')) : ($(this).addClass('active'), $(this).children('input[type=hidden]').val(1), $(this).siblings('.agree-txt').addClass('active'))
			});
			$('#imeLogin').on('click',function() {
				XGUC.reg_close();
				XGUC.login('xglogin');
			});
			var e = $('.input-optimize'),i = $('#getSafeCodePop');
			e.on('click','.placeholder',function() {
				$(this).hide(),
				$(this).siblings('input').focus(),
				$(this).siblings('input').val().match(/^\s*|\s*$/g) && $(this).siblings('input').val('')
			});
			e.on('blur', 'input',function() {
				var e = $(this).siblings('.placeholder');
				0 != e.length && ('' == $(this).val() || $(this).val() == e.text()) && ($(this).val(''), e.show())
				$(this).closest('.input-optimize').removeClass('error');
				$(this).siblings('.error-msg').text('');
			});
			e.on('focus', 'input',function() {
				var e = $(this).siblings('.placeholder');
				0 != e.length && e.hide()
			});
			e.on('click', '.clear-text',function() {
				$(this).siblings('input').val('');
				var e = $(this).siblings('.placeholder');
				$(this).parents('.input-optimize').removeClass('error').removeClass('correct'),
				$(this).parents('.input-optimize').find('.error-msg').text(''),
				0 != e.length && ('' == $(this).val() || $(this).val() == e.text()) && ($(this).val(''), e.show()),
				i.addClass('disabled')
			});			
			var form_id = 'mPhoneRegisterFormPop';
			if (form_id == 'mPhoneRegisterFormPop')
				XGUC.phone_reg(form_id);
			else 
				XGUC.name_reg('mNameRegisterFormPop');			
			$('#'+pop_id+' .tab-trigger-bar a').on('click',function() {
				$(this).addClass('active').siblings().removeClass('active');
				$id = $(this).attr('data-target');
				$('#'+$id).addClass('active').siblings().removeClass('active');
				form_id = $id.replace('Pop','FormPop');
				if (form_id == 'mPhoneRegisterFormPop')
					XGUC.phone_reg(form_id);
				else 
					XGUC.name_reg('mNameRegisterFormPop');
			});	
        };
		
		XGUC.phone_reg = function(form_id) {
			if (!form_id) {
				form_id = 'mPhoneRegisterFormPop';
			}
			$('#' + form_id + ' #pregisterByNameSubmitPop').attr('disabled', false);	
			$('#r_quan').blur(function() {
				var code = $.trim($(this).val());
				if (code == '') {f('r_quan','*验证码不能为空',false);return false;}
				f('r_quan','',1);
			});
			that = $('#getSafeCodePop');
			$("#telnum").blur(function(){
			    phone=$(this).val();
			    if (phone.length !== 11 || !(/^[1][358][0-9]{9}$/.test(phone))) {
			      f('telnum','*手机号码格式不正确',false);
                	that.addClass('disabled');
                	that.attr('style','border:0;color: #E4D9D1;');
			      return false;
			    }
				f('telnum','',1);
                that.removeClass('disabled');
                that.attr('style','border:0;');
		  	});
			that.click(function(){
				var code = $.trim($('#r_quan').val());
				if (code == '') {f('r_quan','*验证码不能为空',false);return false;}
				$.post(
					UURL+'checkverifycode',{
					code:$('#r_quan').val()},
					function(data){
					if (parseInt(data.status) != 1) {
						f('r_quan',data.msg);
						// $('.checkcode').click();
						return false;
					}else{
						f('r_quan','',1);
						s();
						return;
					}
				});
			});
			var s = function() {			
				// 发送安全码
				if (!$(that).hasClass('disabled')) {
					var e = that,p = $.trim($('#telnum').val());
	                e.addClass('disabled');
	                e.attr('style','border:0;color: #E4D9D1;');
					$.ajax({
						type:'post',
						url: UURL+'telsvcode',
						data: 'phone='+p,
						dataType: 'json',
						success: function(d) {
							if (parseInt(d.status) == 1) {
	                            r && r(parseInt(d.status))
	                        } else {
	                            alert(data.msg);
	                        } 
						}
					});
					var r = function(i, t) {
						if (1 == i) {
							var r = 60;
							e.addClass('disabled');
	                		e.attr('style','border:0;color: #E4D9D1;');
							var a = setInterval(function() {
								r--;
								$(e).text(r + '秒后重发'),
								0 == r && (e.removeClass('disabled'),e.attr('style','border:0;'), e.text('发送安全码'), clearInterval(a))
							},1e3)
						} 
					};						
				}
				return false;
			},f=function(id,msg,type='') {
				if(type==''){
					$('#'+id).addClass('hz_reg_txt2');
				}else{
					$('#'+id).removeClass('hz_reg_txt2');
				}
				$('#'+id).closest('.hz_td2').siblings('.error-msg').html(msg);
			};		
			$('#r_telyzm').blur(function() {
				var code = $.trim($(this).val());
				if (code == '') {f('r_telyzm','*安全码不能为空',false);return;}
				f('r_telyzm','',1);
			});
			$('#pwd2').blur(function() {
				var pwd = $.trim($(this).val());
				if (pwd == '') {f('pwd2','*密码不能为空',false);return;}
                if (pwd.length<6 || pwd.length>30){f('pwd2','*6~30位数字、字母或特殊字符组成');return false;}
                f('pwd2','',1);
			});
			$('#pwd3').blur(function() {
				var pwd2 = $.trim($('#pwd2').val());
				var pwd3 = $.trim($('#pwd3').val());
				if (pwd2 != pwd3) {f('pwd3','*两次密码输入不一致',false);return;}
                f('pwd3','',1);
			});
			$("#t_name").blur(function(){
				var rrname=$.trim($(this).val());
				if (rrname == '') {f('t_name','*真实姓名不能为空！');return;}
				$.post(UURL+'isCnameajax',{name:rrname},function(data){
					if (parseInt(data.status) != 1) {
						$('#' + form_id + ' #pregisterByNameSubmitPop').addClass('error2');
						f('t_name','*'+data.msg);
					}else{
						$('#' + form_id + ' #pregisterByNameSubmitPop').removeClass('error2');
						f('t_name','',1);
					}
				});
				f('t_name','',1);
			});
			$("#t_icard").blur(function(){
				var rr_icard=$.trim($(this).val());
				if (rr_icard == '') {f('t_icard','*身份证号不能为空！');return;}
				$.post(UURL+'isIdcardajax',{idcard:rr_icard},function(data){
					if (parseInt(data.status) != 1) {
						$('#' + form_id + ' #pregisterByNameSubmitPop').addClass('error3');
						f('t_icard','*'+data.msg);
					}else{
						$('#' + form_id + ' #pregisterByNameSubmitPop').removeClass('error3');
						f('t_icard','',1);
					}
				});
				f('t_icard','',1);
			});
            $('#' + form_id).unbind('submit').bind('submit',function(event) {
                event.preventDefault();
				var phone = $.trim($('#telnum').val()),
				vcode = $.trim($('#r_quan').val()),
				pcode = $.trim($('#r_telyzm').val()),
				pwd = $.trim($('#pwd2').val());
				pwd3 = $.trim($('#pwd3').val());
				rrname=t_name=$.trim($('#t_name').val());
				rr_icard=t_icard=$.trim($('#t_icard').val());
				if (vcode == ''){f('r_quan','*验证码不能为空',false);return false;}
				if (phone == '') {f('telnum','*手机号码不能为空',true);return false;}
				if (!(/^[1][358][0-9]{9}/.test(phone))) {f('telnum','*手机号码格式不正确',true);return false;}
				if (pcode == ''){f('r_telyzm','*安全码不能为空',false);return false;}
				if (pwd == ''){f('pwd2','*密码不能为空',false);return false;}
                if (pwd.length<6 || pwd.length>30){f('#pwd2','*6~30位数字、字母或特殊字符组成');return false;}
				if (pwd != pwd3){f('pwd3','*两次密码输入不一致',false);return false;}
				if (t_name == ''){f('t_name','*真实姓名不能为空！',false);return false;}
				$.ajax({
                    type: 'POST',
                    async: false,
                    dataType: 'json',
                    url: UURL+'isCnameajax',
                    data: {name:rrname},
                    success:function(data){
                    	if (parseInt(data.status) != 1) {
							$('#' + form_id + ' #pregisterByNameSubmitPop').addClass('error2');
							f('userNameByNamePop',data.msg);
						}else{
							$('#' + form_id + ' #pregisterByNameSubmitPop').removeClass('error2');
							f('userNameByNamePop','',1); return ;
						}
                    }
                });
				if (t_icard == ''){f('t_icard','*身份证号不能为空！',false);return false;}
				$.ajax({
                    type: 'POST',
                    async: false,
                    dataType: 'json',
                    url: UURL+'isIdcardajax',
                    data: {idcard:rr_icard},
                    success:function(data){
                    	if (parseInt(data.status) != 1) {
							$('#' + form_id + ' #pregisterByNameSubmitPop').addClass('error3');
							f('userNameByNamePop',data.msg);
						}else{
							$('#' + form_id + ' #pregisterByNameSubmitPop').removeClass('error3');
							f('userNameByNamePop','',1); return ;
						}
                    }
                });
				if (!$('#' + form_id + ' .sureagreement1').hasClass('on')) {
                    alert('还没有同意注册协议呢！');
                    return false;
                }
                $error2=$('#' + form_id + ' #pregisterByNameSubmitPop').hasClass('error2');	
                // alert($error2);		
                $error3=$('#' + form_id + ' #pregisterByNameSubmitPop').hasClass('error3');	
                // alert($error3);		
                if($error2||$error3){
                	// alert('条件不允许');
                }else{				
					$.ajax({
	                    type: 'POST',
	                    async: true,
	                    dataType: 'json',
	                    url: UURL + 'telregister',
	                    data: $('#' + form_id + '').serialize(),
	                    beforeSend: function() {
	                        $('#' + form_id + ' #pregisterByNameSubmitPop').val('注册中').attr('disabled', true);
	                    },
						success: function(data) {
	                        switch (parseInt(data.status)) {
	                        case 1:
	                            $('#' + form_id + ' #pregisterByNameSubmitPop').val('注册成功，即将登陆').attr('disabled', true);
	                            setTimeout(function() {
	                                /* var reurl = data.reurl;
	                                if (reurl) {
	                                    location.href = reurl;
	                                } else {
	                                    location.reload();
	                                } */ 
	                                $('#' + form_id + ' #pregisterByNameSubmitPop').val('正在登陆...').attr('disabled', true);                                
	                                $.ajax({
	                                    type: 'POST',
	                                    async: true,
	                                    dataType: 'json',
	                                    url: UURL + 'login',
	                                    data: {account:phone,password:pwd},
	                                    beforeSend: function() {
	                                        //$('#' + form_id + ' #registerByNameSubmitPop').val('登录中').attr('disabled', true); 
	                                    },
	                                    success: function(data) {
	                                        switch (parseInt(data['status'])) {
	                                        case 1000:
	                                			$('#' + form_id + ' #pregisterByNameSubmitPop').val(data.msg).attr('disabled', true);                                
	                                            setTimeout(function() {
	                                                window.location.href=UURL+'users_index.html';                     
	                                            },1000);							
	                                            break;
	                                        default:
	                                            alert(data.msg);
	                                            break;
	                                        }
	                                        return false;
	                                    },
	                                    error: function() { 
	                                    	alert("服务器故障，稍后再试");
	                                        // $('#notice').removeClass('success').addClass('fail').text('服务器故障，稍后再试');
	                                        //$('#' + form_id + ' #registerByNameSubmitPop').val('登录').attr('disabled', false);
	                                        
	                                    },
	                                    cache: false
	                                });    
	                                    
	                            },1000);
	                            break;
	                        default:
	                            $('#notice').removeClass('success').addClass('fail').text(data.msg);
	                            $('#' + form_id + ' #registerByPhoneSubmitPop').val('注册').attr('disabled', false);
	                            break;
	                        }
	                    },
	                    error: function() {
	                        alert('服务器故障，稍后再试')
	                        $('#' + form_id + ' #registerByPhoneSubmitPop').val('注册').attr('disabled', false);
	                    },
	                    cache: false
	                }); 
               	}
			});
		}
		
		XGUC.name_reg = function(form_id) {
			if (!form_id) {
				form_id = 'mNameRegisterFormPop';
			}
			// $("input[type=reset]").trigger("click");
			var f=function(id,msg,type='') {
				if(type==''){
					$('#'+id).addClass('hz_reg_txt2');
				}else{
					$('#'+id).removeClass('hz_reg_txt2');
				}
				$('#'+id).closest('.hz_td2').siblings('.error-msg').html(msg);
			};
			$('#' + form_id + ' #registerByNameSubmitPop').attr('disabled', false);	
			
			$('#userNameByNamePop').blur(function() {
				var name = $.trim($(this).val());
				if (name == '') {f('userNameByNamePop','*用户名不能为空！');return;}
				if (name.length<6 || name.length>30){f('userNameByNamePop','*6~30位数字、字母或下划线');return;}
				if (!(/^[a-zA-Z]+[0-9a-zA-Z_]{5,29}$/.test(name))){f('userNameByNamePop','*用户名必须由字母和数字组成,以字母开头');return;}
				$.post(UURL+'checkUser',{username:name},function(data){
					if (parseInt(data.status) != 1) {
						$('#' + form_id + ' #registerByNameSubmitPop').addClass('error1');
						f('userNameByNamePop',data.msg);
					}else{
						$('#' + form_id + ' #registerByNameSubmitPop').removeClass('error1');
						f('userNameByNamePop','',1); return ;
					}
				});
			});
			$('#userPassPop').blur(function() {
				var pwd = $.trim($(this).val());
				if (pwd == '') {
					f('userPassPop','*密码不能为空！');
					return;
				}
				if (pwd.length<6 || pwd.length>30){
					f('userPassPop','*6~30位数字、字母或特殊字符组成');
					return;
				}
				f('userPassPop','',1);
			});
			$('#userConfirmPasssPop').blur(function() {
				var repwd = $.trim($(this).val()),
				pwd = $.trim($('#userPassPop').val());
				if (repwd == '') {f('userConfirmPasssPop','*重复密码不能为空！');return;}
				if (pwd != repwd){f('userConfirmPasssPop','*两次密码不一致');return;}
				f('userConfirmPasssPop','',1);
			});
			$("#rrealname").blur(function(){
				var rrname=$.trim($(this).val());
				if (rrname == '') {f('rrealname','*真实姓名不能为空！');return;}
				$.post(UURL+'isCnameajax',{name:rrname},function(data){
					if (parseInt(data.status) != 1) {
						$('#' + form_id + ' #registerByNameSubmitPop').addClass('error2');
						f('rrealname','*'+data.msg);
					}else{
						$('#' + form_id + ' #registerByNameSubmitPop').removeClass('error2');
						f('rrealname','',1);
					}
				});
				f('rrealname','',1);
			});
			$("#r_icard").blur(function(){
				var rr_icard=$.trim($(this).val());
				if (rr_icard == '') {f('r_icard','*身份证号不能为空！');return;}
				$.post(UURL+'isIdcardajax',{idcard:rr_icard},function(data){
					if (parseInt(data.status) != 1) {
						$('#' + form_id + ' #registerByNameSubmitPop').addClass('error3');
						f('r_icard','*'+data.msg);
					}else{
						$('#' + form_id + ' #registerByNameSubmitPop').removeClass('error3');
						f('r_icard','',1);
					}
				});
				f('r_icard','',1);
			});
			// $('#registerNameVcodePop').blur(function() {
			// 	var code = $.trim($(this).val());
			// 	if (code == '') {f('registerNameVcodePop','验证码不能为空！');return;}
			// });
			
            $('#' + form_id).unbind('submit').bind('submit',function(event) {
                event.preventDefault();
				var name = $.trim($('#userNameByNamePop').val()),
					pwd = $.trim($('#userPassPop').val()),
					repwd = $.trim($('#userConfirmPasssPop').val()),
					rrname = $.trim($('#rrealname').val());
					rr_icard = $.trim($('#r_icard').val());
					// code = $.trim($('#registerNameVcodePop').val());
				if (name == '') {f('userNameByNamePop','*用户名不能为空！');return false;}
				$.ajax({
                    type: 'POST',
                    async: false,
                    dataType: 'json',
                    url: UURL+'checkUser',
                    data: {username:name},
                    success:function(data){
                    	if (parseInt(data.status) != 1) {
							$('#' + form_id + ' #registerByNameSubmitPop').addClass('error1');
							f('userNameByNamePop',data.msg);
						}else{
							$('#' + form_id + ' #registerByNameSubmitPop').removeClass('error1');
							f('userNameByNamePop','',1); return ;
						}
                    }
                });
				if (name.length<6 || name.length>15){f('userNameByNamePop','*6~15位数字、字母或下划线');return false;}
				if (!(/^[a-zA-Z]+[0-9a-zA-Z_]{5,29}$/.test(name))){f('userNameByNamePop','*用户名必须由字母和数字组成,以字母开头');return false;}
				if (pwd == '') {f('userPassPop','*密码不能为空！');return false;}
				if (pwd.length<6 || pwd.length>30){f('#userPassPop','*6~30位数字、字母或特殊字符组成');return false;}
				if (repwd == '') {f('userConfirmPasssPop','*重复密码不能为空！');return false;}
				if (pwd != repwd){f('userConfirmPasssPop','*两次密码不一致');return false;}
				if (rrname == '') {f('rrealname','*真实姓名不能为空！');return false;}
				$.ajax({
                    type: 'POST',
                    async: false,
                    dataType: 'json',
                    url: UURL+'isCnameajax',
                    data: {name:rrname},
                    success:function(data){
                    	if (parseInt(data.status) != 1) {
                    		$('#' + form_id + ' #registerByNameSubmitPop').addClass('error2');
							f('rrealname','*'+data.msg);
						}else{
							$('#' + form_id + ' #registerByNameSubmitPop').removeClass('error2');
							f('rrealname','',1);
						}
                    }
                });
				if (rr_icard == '') {f('r_icard','*身份证号不能为空！');return false;}
				$.ajax({
                    type: 'POST',
                    async: false,
                    dataType: 'json',
                    url: UURL+'isIdcardajax',
                    data: {idcard:rr_icard},
                    success:function(data){
                    	if (parseInt(data.status) != 1) {
                    		$('#' + form_id + ' #registerByNameSubmitPop').addClass('error3');
							f('r_icard','*'+data.msg);
						}else{
							$('#' + form_id + ' #registerByNameSubmitPop').removeClass('error3');
							f('r_icard','',1);
						}
                    }
                });
				// if (code == '') {f('registerNameVcodePop','*验证码不能为空！');return false;}
				if (!$('#' + form_id + ' .sureagreement').hasClass('on')) {
                    alert('*还没有同意注册协议呢！');
                    return false;
                }	
                $error1=$('#' + form_id + ' #registerByNameSubmitPop').hasClass('error1');			
                $error2=$('#' + form_id + ' #registerByNameSubmitPop').hasClass('error2');			
                $error3=$('#' + form_id + ' #registerByNameSubmitPop').hasClass('error3');	
                if($error1||$error2||$error3){
                	// alert('条件不允许');
                }else{
                	$.ajax({
	                    type: 'POST',
	                    async: true,
	                    dataType: 'json',
	                    url: UURL + 'register',
	                    data: $('#' + form_id + '').serialize(),
	                    beforeSend: function() {
	                        $('#' + form_id + ' #registerByNameSubmitPop').val('注册中').attr('disabled', true);
	                    },
						success: function(data) {
	                        switch (parseInt(data.status)) {
	                        case 1:
	                        	$('#' + form_id + ' #registerByNameSubmitPop').val('注册成功，即将登陆').attr('disabled', true);
	                            setTimeout(function() {
	                                /* var reurl = data.reurl;
	                                if (reurl) {
	                                    location.href = reurl;
	                                } else {
	                                    location.reload();
	                                } */ 
	                                $('#' + form_id + ' #registerByNameSubmitPop').val('正在登陆...').attr('disabled', true);                                
	                                $.ajax({
	                                    type: 'POST',
	                                    async: true,
	                                    dataType: 'json',
	                                    url: UURL + 'login',
	                                    data: {account:name,password:pwd},
	                                    beforeSend: function() {
	                                        //$('#' + form_id + ' #registerByNameSubmitPop').val('登录中').attr('disabled', true); 
	                                    },
	                                    success: function(data) {
	                                        switch (parseInt(data['status'])) {
	                                        case 1000:
	                                			$('#' + form_id + ' #registerByNameSubmitPop').val(data.msg).attr('disabled', true);                                
	                                            setTimeout(function() {
	                                                window.location.href=UURL+'users_index.html';                     
	                                            },1000);							
	                                            break;
	                                        default:
	                                            alert(data.msg);
	                                            break;
	                                        }
	                                        return false;
	                                    },
	                                    error: function() { 
	                                        $('#notice').removeClass('success').addClass('fail').text('服务器故障，稍后再试');
	                                        //$('#' + form_id + ' #registerByNameSubmitPop').val('登录').attr('disabled', false);
	                                        
	                                    },
	                                    cache: false
	                                });    
	                                    
	                            },1000);
	                            break;
	                        default:
	                            alert(data.msg);
	                            // $('#' + form_id + ' #registerByNameSubmitPop').val('注册').attr('disabled', false);
	                            // break;
	                        }
	                    },
	                    error: function() {
	                        alert('服务器故障，稍后再试')
	                        // $('#' + form_id + ' #registerByNameSubmitPop').val('注册').attr('disabled', false);
	                    },
	                    cache: false
	                }); 
                }		
			});
		}

        XGUC.reg_close = function() {
			$('#xgregister').removeClass('active');
        };

        XGUC.get_game_gift = function(giftid, giftname) {
            if (!giftid) {
                alert('礼包不存在');
                return;
            }
            $.ajax({
                type: 'POST',
                async: true,
                dataType: 'json',
                url: UURL + 'getGameGift',
                data: {
                    'giftid': giftid,
                    'giftname': giftname
                },
                beforeSend: function() {
				},
                success: function(data) {
                    switch (parseInt(data.status)) {
                    case 1:
                        var h = '<div class="wx_gift_ok">';
                        h += '<div class="mask_layer"></div>';
                        h += '<div class="box">';
                        h += '<div class="close">×</div>';
                        h += ' <div class="cons">';
                        if (data.msg == 'ok') {
                            h += ' <h5>您领取的【' + giftname + '】礼包码为：</h5>';
                            h += ' <div><p>' + data.data + '</p><div class="btns" id="copy_code" data-url="' + data.data + '">复制</div></div>';
                            h += ' <span>礼包领取成功 (:</span>';
                        } else if (data.msg == 'noc') {
                            h += ' <h5>您领取的【' + giftname + '】礼包码为：</h5>';
                            h += ' <span>当前礼包未发放激活码 (:</span>';
                        } else if(data.msg=='not'){
                        	h += ' <span>当前礼包激活码已失效 (:</span>';
                        }else {
                            h += ' <h5>您领取的【' + giftname + '】礼包码为：</h5>';
                            h += ' <div><p>' + data.data + '</p><div class="btns" id="copy_code" data-url="' + data.data + '">复制</div></div>';
                            h += ' <span>当前礼包您已经领取过了 (:</span>';
                        }
                        h += ' </div>';
                        h += ' </div>';
                        h += '</div>';
                        $('body').append(h);
                        $('.mask_layer').css({
                            opacity: 0.8
                        });
                        $('.wx_gift_ok').find('.close').bind('click',
                        function() {
                            $('.wx_gift_ok').remove();
                        });
                        $("#copy_code").zclip({
                            path: "/sy3.0/Public/Media/js/ZeroClipboard.swf",
                            copy: function() {
                                return $(this).data('url');
                            },
                            beforeCopy: function() {
                                $(this).css("color", "orange");
                            },
                            afterCopy: function() {
                                $(this).text('已经复制');
                            }
                        });
                        break;
                    default:
                        alert(data.msg);
                        break;
                    }
                },
                error: function() {
                    alert('服务器故障，稍后再试')
                },
                cache: false
            });
        };

        XGUC.uc_content_avatar = function() {

            var $pick = $('#uc-avatar-bnt'),
            uploader;

            uploader = WebUploader.create({

                auto: true,

                pick: {

                    id: $pick,

                    innerHTML: '<div class="btnok">点击选择截图</div>',

                    multiple: false

                },

                formData: {

                    'type': 'avatar'

                },

                swf: RES_BASE_DIR + 'js/Uploader.swf',

                chunked: false,

                chunkSize: 512 * 1024,

                server: SERVICE_URL + 'uploadAvatar',

                accept: {

                    title: 'Images',

                    extensions: 'gif,jpg,jpeg,bmp,png',

                    mimeTypes: 'image/*'

                },

                compress: {

                    width: 1024,

                    height: 1024,

                    // 图片质量，只有type为`image/jpeg`的时候才有效。
                    quality: 90,

                    // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.
                    allowMagnify: false,

                    // 是否允许裁剪。
                    crop: true,

                    // 是否保留头部meta信息。
                    preserveHeaders: true,

                    // 如果发现压缩后文件大小比原来还大，则使用原来图片
                    // 此属性可能会影响图片自动纠正功能
                    noCompressIfLarger: false,

                    // 单位字节，如果图片大小小于此值，不会采用压缩。
                    compressSize: 2 * 1024 * 1024

                },

                disableGlobalDnd: true,

                fileNumLimit: 1,
                // 最多文件个数
                fileSizeLimit: 2 * 1024 * 1024,
                // 2 M
                fileSingleSizeLimit: 2 * 1024 * 1024 // 2 M
            });

            // 文件上传成功
            uploader.on('uploadSuccess',
            function(file, ret) {

                console.log(file);

                if (ret.state == 'SUCCESS') {
                    alert('上传成功');

                    $('#thumb').val(ret.url);

                    $('#users-avatar-src').attr('src', ret.url + '?' + Math.random());

                    uploader.removeFile(file);

                } else {

                    alert(ret.state);

                }

            });

        };

    })();
})();

window.onload = function() {
    XGUC.init();
}	