function pop(title,content,callback,data) {
    title = title || '提示';
    
    var flag = false,popheight = $('body').height();
    
    $('body').append('<div class="o_pop_wrap" id="popwindow" style="height:'+popheight+'px;"><div class="o_pop"><div class="o_pop_sub"><div class="o_pop_title_wrap"><span class="o_pop_title">'+title+'</span><a class="o_pop_close" id="pop_close">x</a></div><div class="o_pop_content_wrap"><div class="o_pop_cotent pop_center"></div><div class="o_pop_btn_wrap" id="popbtn"></div></div></div></div></div>');
    
    if (typeof content == 'string') {
        if (!/<img .+/.test(content)) {
            content = '<div class="pop_text_wrap_parent"><div class="pop_text_wrap"><div class="pop_text" >'+content+'</div></div></div>';
        }
        
        $('.o_pop_cotent').append(content);
        
    } else if (typeof content == 'function') {
        content($('.o_pop_cotent').empty());
    } else if (typeof content == 'number') {
        setTimeout(function() {
            $('#pop_close').click();
        },content);
    }
    
    
    $('#popwindow').fadeIn('slow');
    
    $('#pop_close').on('click',function() {
        $(this).closest('#popwindow').fadeOut('slow',function() {
            $(this).remove();
        });       
    });
    
    var btn = $('#popwindow .o_pop_btn_wrap');
    if (typeof callback == 'object') {
        $.each(callback,function(k,v) {
            btn.append('<button class="o_pop_btn o_pop_'+k+'" >'+v+'</button>');
            $('.o_pop_'+k).click(function() {
                $('#pop_close').click();
            });  
        });
    } else if (typeof callback == 'string') {
        if ($.trim(callback) == '') {
            
        } else {
            btn.empty().append('<button class="o_pop_btn" id="o_pop_comfirm" >'+callback+'</button>');
        }
        $('#o_pop_comfirm').click(function() {
            $('#pop_close').click();
        });
    } else if (typeof callback == 'function') {
        if (btn.length > 0) {
            return callback(btn.empty());
        }
    } else if (typeof callback == 'number') {
        setTimeout(function() {
            $('#pop_close').click();
        },parseInt(callback));
    }
    
    if (typeof data == 'number') {
        setTimeout(function() {
            $('#pop_close').click();
        },data);
    } else if (typeof data == 'string') {
        setTimeout(function() {
            window.location.href=data.url;   
        },1500);
    } else if (typeof data == 'object' ) {
        setTimeout(function() {
            window.location.href=data.url;   
        },data.time);
    }
 
    return flag;
}