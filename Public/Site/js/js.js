/*
 * 全站公共脚本,基于jquery-1.7.1脚本库
*/
$(function(){
	//搜索
		$(".ipt").each(function(){
			var val = $(this).val();
			var color = $(this).css("color");
			$(this).focus(function(){
				if ($(this).val() == val)
				{
					$(this).css("color","#000").val("").closest("form").addClass("searchFocus");	
				}
			})
			$(this).blur(function(){
				if ($(this).val() == "")
				{
					$(this).css("color",color).val(val).closest("form").removeClass("searchFocus");		
				}
			})
		})
	//下载
		$(".close").on("tap",function(){
			$(".downOpen").transition({y:50,opacity:0,complete:function(){
				$(this).hide();
			}});
		})
		$(".dbtn").on("tap",function(){
			  var drop1= $(this).attr("drop1");
              var drop2= $(this).attr("drop2");
              var drop3= $(this).attr("drop3");
              $(".downList .android").find("a").attr("href",drop1);
              $(".downList .ios").find("a").attr("href",drop2);
              $(".downList .ios2").find("a").attr("href",drop3);

			$(".downOpen").show().css({opacity:0,y:-50}).transition({y:0,opacity:1});
			return false;
		})
//page4 
		
	//公告
		var play_num =1;
		//设置轮播间隔时间
		var auto_time = 3000;
		//获取图片数量
		var auto_num = $(".noticeList li").length;
		//插入数字番号列表，并为首个列表单元添加样式
		$(".notice").append("<ul class='num'></ul>");
		for(auto_i=1;auto_i<=auto_num;auto_i++)
		{
			$(".num").append("<li>"+auto_i+"</li>");
		};
		$(".num li:eq(0)").addClass("on");
		//自动循环播放
		auto_play = setInterval (function(){
			auto_start(play_num)
			play_num++;
			if (play_num == auto_num)
			{
			play_num = 0;	
			}
		},auto_time)
		function auto_start(play_num)
		{
			if(play_num == auto_num)
			{
				$(".noticeList li").hide().eq(0).fadeIn();
				$(".num li").removeClass("on").eq(0).addClass("on");
			}
			else
			{
				$(".noticeList li").hide().eq(play_num).fadeIn();
				$(".num li").removeClass("on").eq(play_num).addClass("on");
			}
		}
		//鼠标事件
		$(".num li").click(function(){
			auto_stop();
			auto_start($(this).index());
			if($(this).index()==auto_num-1)
			{
				play_num=0;
			}
			else
			{
			play_num = $(this).index()+1;
			}
			auto_replay();
		});
		//停止播放
		function auto_stop()
		{
			clearInterval(auto_play);	
		}
		//重新播放
		function auto_replay()
		{
			auto_play = setInterval (function(){
				auto_start(play_num)
				play_num++;
				if (play_num == auto_num)
				{
				play_num = 0;	
				}
			},auto_time)
		}
	//入驻人数
		var startNumber = 0;
		var number = $(".number").attr("data-number");
		numbergo = setInterval(function(){
			if ( startNumber <= number )
			{
				$(".number").html(startNumber);
				startNumber+=81;
			}
			else
			{
				$(".number").html(number);
				clearInterval(numbergo);	
			}
		},1)
	//入驻滚动
		var scrollHeight = $(".rzHidden li").height();
		setInterval(function(){
			$(".rzHidden ul").animate({marginTop:-37},500,function(){
				$(this).css({"marginTop":0}).find("li:first").appendTo($(this));
			})
		},3000)
	//列表
		$(document).on("mouseover",".top",function(){
			$(this).find(".tm").show();
		})
		$(document).on("mouseout",".top",function(){
			$(this).find(".tm").hide();
		})
		if ($.browser.msie&&($.browser.version == "7.0") ) {
			var downOpenMain = $(".downOpenMain");
			downOpenMain.css({"left":(document.documentElement.clientWidth-downOpenMain.width())/2,"top":(document.documentElement.clientHeight-downOpenMain.height())/2})
		}
		$(document).on("click",".dbtn",function(){
			$(".downOpen").fadeIn();
			return false;
		})
		$(document).on("click",".closed",function(){
			$(".downOpen").fadeOut();
			return false;
		})
		$(document).on("click",".close",function(){
			var fade = $(this).closest(".fade");
			var width = fade.outerWidth(true);
			var height = fade.outerHeight(true);
			fade.wrap("<div class='fadewrap'></div>");
			fade.parent().width(width).height(height);
			fade.fadeOut(500,function(){
				fade.closest(".fadewrap").animate({height:0},500);
			});
		})
		$(".to-top").on("click",function(){
			$(window).scrollTop(0);
		})
		$(".clo").on("click",function(){
			$(".first").hide();
			$(".clo").hide();
		})
		$(".client .link:first").hover(function(){
			$(".first").css({"left":-55,"bottom":40}).show();
		},function(){
			$(".first").hide();
		})
		$(window).scroll(function(){
			if ( document.documentElement.scrollTop+document.body.scrollTop > 100 )
			{
				$(".to-top").css("visibility","visible");
			}
			else
			{
				$(".to-top").css("visibility","hidden");
			}
		});                           

        $('.rzBtn').click(function() { 
        
            var loc = location,search_URL,id=$(this).data('id');
            
            if (loc.origin) {     
            
                add_URL = loc.origin;   
                
            } else {            
            
                add_URL = loc.protocol+'//'+loc.host;       

            }        
            
            add_URL += loc.pathname+'?s=/Index/add.html';        
            
                   
            
            $.post(add_URL,{id:id},function() {
                
                    window.location.reload();});  
                    
            });
            
            
})