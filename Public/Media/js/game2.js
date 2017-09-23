$(function() {
    (function() {
        var isSwiping = false;
        var imgItemWidth = $('.m-screenshot-box .m-screenshot-item').outerWidth() + 10;
        $('.m-screenshot-box').on('click', '.m-button-left', function() {
            swipe('left');
        }).on('click', '.m-button-right', function() {
            swipe('right')
        }).on('mouseover', '.m-button-left', function() {
            $(this).find('.sprite').toggleClass('sprite-ic-arrow-1').toggleClass('sprite-ic-arrow-1-y');
        }).on('mouseover', '.m-button-right', function() {
            $(this).find('.sprite').toggleClass('sprite-ic-arrow-2').toggleClass('sprite-ic-arrow-2-y');
        }).on('mouseout', '.m-button-left', function() {
            $(this).find('.sprite').toggleClass('sprite-ic-arrow-1').toggleClass('sprite-ic-arrow-1-y');
        }).on('mouseout', '.m-button-right', function() {
            $(this).find('.sprite').toggleClass('sprite-ic-arrow-2').toggleClass('sprite-ic-arrow-2-y');
        });

        function swipe(dir) {
            if (isSwiping) {
                return false;
            }
            isSwiping = true;
            var left = parseFloat($('.m-screenshot-box .m-screenshot-list').css('left'));
            if (dir == 'left') {
                if (left < 0) {
                    $('.m-screenshot-box .m-screenshot-list').animate({
                        left: left + imgItemWidth
                    }, function() {
                        isSwiping = false;
                    });
                } else {
                    isSwiping = false;
                }
            } else {
                if ($('.m-screenshot-box .m-screenshot-list').width() - (-left) - 30 > $('.m-screenshot-box').width()) {
                    $('.m-screenshot-box .m-screenshot-list').animate({
                        left: left - imgItemWidth
                    }, function() {
                        isSwiping = false;
                    });
                } else {
                    isSwiping = false;
                }
            }
        }
    })();

    
    (function(){
        var clickHandle = function(e){
            var offsetX = e.offsetX;
            if (offsetX<=$(e.target).width() / 2) {
                $('.m-screenshot-list').viewer('prev');
            }else{
                $('.m-screenshot-list').viewer('next');
            }
            return false;
        }
        var mouseOverHandle = function(e){
            var offsetX = e.offsetX;
            if (offsetX<=$(e.target).width() / 2) {
                $(this).css({
                    cursor: 'url(/Public/Media/images/ic-arrow-l-w.png),pointer'
                })
            }else{
                $(this).css({
                    cursor: 'url(/Public/Media/images/ic-arrow-r-w.png),pointer'
                })
            }
            return false;
        }
        $('.m-screenshot-list').viewer({
            url: 'data-original-src',
            movable: false,
            shown: function(){
            },
            viewed: function(){
                $('.viewer-container .viewer-canvas img').on('click',clickHandle).mousemove(mouseOverHandle);
            },
        });

    })();
    
     (function(){
        var height = $('.m-detail-text-box .m-detail-text-wrapper').height();
        if (height > 80) {
            $('.m-detail-text-box .m-detail-text-wrapper').css('maxHeight', '80px');
            $('.m-detail-text-box .u-btn-expand').show();
            $('.m-detail-text-box .u-btn-expand').show().attr('data-opend','0');
        }else{
            $('.m-detail-text-box .u-btn-expand').hide();
        }
        $('.m-detail-text-box .u-btn-expand').on('click',function(){
            if ($(this).attr('data-opend') == '0') {
                $(this).attr('data-opend',1);
                $('.m-detail-text-box .m-detail-text-wrapper').css({ 'maxHeight': '' });
                $(this).html('<span>收起</span><i class="sprite sprite-ic-arrow-4"></i>');
            } else {
                $(this).attr('data-opend',0);
                $('.m-detail-text-box .m-detail-text-wrapper').css({ 'maxHeight': '80px' });
                $(this).html('<span>展开</span><i class="sprite sprite-ic-arrow-3"></i>');
            }
            return false;
        });
    })();
});