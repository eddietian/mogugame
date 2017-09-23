$(function () {
    var initDom = function () {
        var w = 0;
        $('.channel-item').each(function (i, dom) {
            var domW = $(dom).width();
            w += domW;
        });
        w = Math.max($('.channel').width(), w);
        $('.channel-list').width(w);
        if ($('#kw').attr('value') === '') {
            $('#icon_remove').hide();
        }
    };

    var removeText = function () {
        $('#kw').val('');
        $('#icon_remove').hide();
    };

    var slider = function () {
        var startPosition;
        var endPosition;
        var deltaX;
        var left;
        $('.channel-list').on('touchstart', function (e) {
            var touch = e.touches[0];
            startPosition = {
                x: touch.pageX,
                y: touch.pageY
            };
            left = parseInt($(this).css('left'), 10);
        });
        $('.channel-list').bind('touchmove', function (e) {
            var touch = e.touches[0];
            endPosition = {
                x: touch.pageX,
                y: touch.pageY
            };
            deltaX = left + endPosition.x - startPosition.x;
            if (endPosition.x - startPosition.x > 0) {
                $(this).css({
                    left: deltaX + 'px'
                });
            }
            else {
                $(this).css({
                    left: deltaX + 'px'
                });
            }
        });
        $('.channel-list').bind('touchend', function (e) {
            var left = parseInt($(this).css('left'), 10);
            if (left <= $('.channel').width() - $(this).width()) {
                $(this).animate({
                    left: $('.channel').width() - $(this).width() + 'px'
                }, 300);
            }
            else if (left >= 0) {
                $(this).animate({
                    left: 0
                }, 300);
            }
        });
    };

    var bind = function () {
        slider();
        $('#icon_remove').on('click', function () {
            removeText();
        });
        $('#su').on('click', function () {
            $('#bdcs-search-form').submit();
        });
    };

    var init = function () {
        bind();
        initDom();
    };

    init();
});