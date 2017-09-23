var loads = false;
function ajaxload(url,data,callback) {
    var totalheight = 0;         
    var ajax = function ajax(url,data,callback) {
        var container = $("#ajaxContainer"); 
        var data = data || {}; 
        // 当前页
        var currentPage = parseInt(container.attr("currentpage"));
                
        // 总页数
        var maxPage = parseInt(container.attr("maxpage"));
        
        if (loads) {
            return false;
        }

        if ( currentPage >= maxPage ) {
            $('.loading').addClass('ms-none');
            $('#moreBtn').removeClass('ms-none').html('对不起，数据已经加载完');
            return false;
        }        
        
        var lastId = parseInt(container.find(".lists:last").attr("ajax-id"));
        
        data.p = currentPage+1;
        
        jQuery.ajax({ 
            type:"post", 
            url: url, 
            data:data, 
            dataType: "json", 
            beforeSend: function(){ 
                $('.loading').addClass('ms-none');
                $('#loadingTip').removeClass('ms-none');loads=true;
            }, success:callback, error:function(){
                $('.loading').addClass('ms-none');
                $('#errorTip').removeClass('ms-none');
            } 
        }); 
    }
    
    var loadData = function(){
        totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop()); 
        if ($(document).height() <= totalheight) {
            ajax(url,data,callback);
        } else {
            ajax(url,data,callback);
        }
    }
    $(window).scroll( function() { 
        loadData();
    });
    
    loadData();
    
    $('#moreBtn').on('click',function(){loadData()});
    
    $(document).on('click','#loadingTip',function(){ajax(url,data,callback);}); 
}
