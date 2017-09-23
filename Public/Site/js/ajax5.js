/*
 * ajax脚本,基于jquery-1.9.1脚本库
*/
$(function(){
	//ajax
		var totalheight = 0; 
        function loadData(){ 
            totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop()); 
            if ($(document).height() <= totalheight) {  // 说明滚动条已达底部
                ajax()
            } 
        }
        $(window).scroll( function() { 
            loadData();
        }); 
		loadData();
		$(".more").one("click",function(){loadData()});
        function ajax()
        {
            var container = $(".list"); // 加载容器
            var data = {}; // 查询参数
            // 当前页
            var currentPage = parseInt(container.attr("currentpage"));
            // 总页数
            var maxPage = parseInt(container.attr("maxpage"));
            if ( currentPage >= maxPage )
            {
            	return false;
            }
            // 每次查询数据的条数
            var ajaxRow = parseInt(container.attr("ajaxRow"));
            // 当前最后一条数据的id值
            var lastId = parseInt(container.find(".lists:last").attr("ajax-id"));
            data.p = currentPage+1;
            data.maxPage = maxPage;
            data.lastId = lastId;
            jQuery.ajax({ 
                type:"post", 
                url: URL+"indexajax", 
                data:data, 
                dataType: "json", 
                 beforeSend: function(XMLHttpRequest){ 
                    $(".loading").removeClass("loadagin").show(); 
                    $(".loading").html("<img src='/Public/Sociaty/images/loading.png'/>正在加载中")
                }, success:function(data) {
                    if (parseInt(data.status) ==1) {
                        var text = '';
                        if (data.type == 'gift') {
                            for (var i in data.data) {
                                var item = data.data[i];
                                text += ' <div class="lists fade">'
       + ' <div class="close">关闭</div>'
        + '<div class="top"> '
       + '   <img src="'+item['icon']+'">'
       + '   <div class="tm" style="display: none;">'
        + '    <div class="rzs">已入驻 '+item['total']+' 人</div>'
        +'<a class="rzBtn" target="_blank" href="'+item['app_url']+'">立即入驻</a>'
        + '  </div>'
       + ' </div>'
       + ' <div class="bottom"> <img src="'+item['gameicon']+'" class="bImg">'
       + '   <div class="btit">'
       + '     <h3>'+item['game_name']+'</h3>'
       + '   </div>'
       + '   <p><span>礼包数量：'+item['novicetotal']+'</span>'
        + '  </p>'
        + '  <p><span>已领：'+item['novicetaken']+'</span></p>'
        + '  <ul class="dbutton">'
        + '    <li>';
        if (item['novicenum']>0)
            text+= '        <a target="_blank" href="'+item['gifturl']+'" class="libao">领取礼包<em>'+item['novicenum']+'</em></a>'
        else 
            text+= '        <a target="_blank" href="#" class="libao over">已结束</a></if>'
        text+= '    </li>'
        + '    <li><a href="'+item['downurl']+'" class="download" target="_blank">下载游戏</a></li>'
        + '  </ul>'
       + ' </div>'
     + '</div>';
                            }
                        } else {
                            for(var i in data.data) {
                                var item = data.data[i];
                                text += '<div class="lists fade" appid="'+item['appid']+'">'
            +'<div class="close">关闭</div>'
             +'<div class="top"> '
             +'    <img src="'+item['cover']+'">'
             +'  <div class="tm" style="display:none;">'
             +'    <div class="rzs">已入驻 <span class="ruzhurenshu">'+item['total']+'</span> 人</div>'
             +'<a class="rzBtn" target="_blank" href="'+item['app_url']+'">立即入驻</a>'
             +'    </div>'
             +'</div>'
             +'<div class="bottom"> <img src="'+item['icon']+'" class="bImg">'
             +'  <div class="z"></div>'
             +'  <div class="btit">'
              +'   <h3>'+item['game_name']+'</h3>   '
             +'  </div>'
             +'  <p><span>           '
             +''+item['category_name']
              +'   </span>|  <span>'+item['game_type_name']        
              +'   </span>|　<span>'+item['game_size']
              +'  </span>|　<span style="color:#f00;">'+item['fanli']
              +'%返利</span></p>'
             +'  <p><span>已入驻人数：'+item['total']+'人 </span></p>'
             +'  <ul class="dbutton">'
             +'    <li><a target="_blank" href="'+item['gifturl']+'" class="libao">领取礼包<em></em></a></li>'
             +'    <li><a href="'+item['downurl']+'" class="download" target="_blank">下载游戏</a></li>'
             +'  </ul>'
             +'</div>'
           +'</div>';                                
                            }                            
                        }
                        container.find(".lists:last").after(text); 
                        container.attr("currentpage",parseInt(data.currentpage));                      
                    }
                    if ( parseInt(data.currentpage) >= maxPage )
                    {
                        $(".loading").html("没有了");
                    }
                    else
                    {
                    	$(".loading").html("加载更多>>"); 
                    }
                }, error:function(){ 
                    $(".loading").addClass("loadagin").html('加载失败，点击重新加载')
                } 
            }); 
        }
        $(document).on("click",".loadagin",function(){ajax();});
})