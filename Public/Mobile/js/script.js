$(function() {
    // tab
    var $tab = $(".tab_ctrl").children();
    $tab.each(function() {
        $(this).click(function() {
            var tab_index = $tab.index(this);
            var $tab_wrap = $(this).parent().next(".tab_wrap");
            $(this).addClass("on").siblings().removeClass("on");
            $tab_wrap.children().eq(tab_index).show().siblings().hide();
        });
    });
    // title
    var $title = $(".title_ctrl").children();
    $title.each(function() {
        $(this).click(function() {
            var title_index = $title.index(this);
            var $title_wrap = $(this).parent().next(".title_wrap");
            $(this).addClass("on").siblings().removeClass("on");
            $title_wrap.children().eq(title_index).show().siblings().hide();
        });
    });
    // flex tab
    $(".flex_tab").each(function() {
        var tab_length = $(this).children().length;
        var tab_wa = 1 / tab_length;
        var tab_wb = tab_wa.toFixed(8);
        var tab_wc = tab_wb.slice(2, 4) + "." + tab_wb.slice(4, 8) + "%";
        $(this).children().width(tab_wc);
    });
    /*点击全选调系统复制功能*/ 
	var inputkeyCode=$(".inputkeyCode");
	for(var i=0;i<inputkeyCode.length;i++){
		(function(i){
			inputkeyCode[i].onclick=function(){
				this.select();
				var values=this.getAttribute("value")
				this.oninput=function(){
					this.setAttribute("readonly","readonly");
					$(this).val(values);
				}
			}
			inputkeyCode[i].onblur=function(){
				this.removeAttribute("readonly");
			}
		})(i)
	}
	
    // 判断页面是否有hot-news这个ID的存在
    if (document.getElementById("hot-news")) {
        var tips = {
            box : document.getElementById("hot-news"),
            p : document.getElementById("hot-news").getElementsByTagName("li"),
            n : 0,
            transEnd : function() {
                tips.box.style.cssText = "-webkit-transform:translateY(0px);-webkit-transition: 0ms;";
                tips.n = 0;
                tips.box.removeEventListener("webkitTransitionEnd", tips.transEnd);
            },
            tipsInit : function() {
                tips.box.appendChild(last);
            }
        };
        var last = (tips.p[0]).cloneNode(true);
        tips.tipsInit();
        var tipsPlay = setInterval(function() {
            tips.n++;
            tips.box.style.cssText =
                    "-webkit-transform:translateY(-" + (tips.n * tips.p[0].offsetHeight)
                            + "px);-webkit-transition: 300ms;";
            if (tips.n >= tips.p.length - 1) {
                tips.box.addEventListener("webkitTransitionEnd", tips.transEnd);
            }
        }, 5000);
    }
});
$(document).ready(function(){
	var $newCode=$(".gift-con .newCode");/*榜单切换*/
	var $newtGift=$(".gift-con .newtGift");
	$newCode.click(function(){
		$(this).addClass("on");
		$newtGift.removeClass("on");
		$("#newCodeList").show();
		$("#newGiftList").hide();
		imageLoad();		
	});
	$newtGift.click(function(){
		$(this).addClass("on");
		$newCode.removeClass("on");
		$("#newCodeList").hide();
		$("#newGiftList").show();
		imageLoad();
	});
});
/*图片大小的缩放*/
window.onload=imageLoad;
function imageLoad() {
	$(".dateilImg img").each(function(i){
		if($(this).width() > 290){
			$(this).width(290);
		}
	})
}