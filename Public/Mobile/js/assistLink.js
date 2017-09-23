;(function() {
    var tool = {
        tap: function(obj, fun) {
            if ('ontouchstart' in window) {} else {
                this.tap = this.clickFun;
                this.clickFun(obj, fun);
                return
            }
            var start_x = 0
              , start_y = 0;
            obj.addEventListener('touchstart', function(e) {
                start_x = e.touches[0].clientX;
                start_y = e.touches[0].clientY;
            });
            obj.addEventListener('touchend', function(e) {
                var endX = e.changedTouches[0].clientX;
                var endY = e.changedTouches[0].clientY;
                if (Math.abs(endX - start_x) < 5 && Math.abs(endY - start_y) < 5) {
                    fun.call(obj, e);
                }
            });
        },
        clickFun: function(obj, fun) {
            obj.addEventListener('click', function(e) {
                fun.call(obj, e);
            })
        },
        isBtn: function(obj) {
            var nowObj;
            var body = document.getElementsByTagName('body')[0];
            do {
                if (obj.getAttribute('type') == 'btn' || obj.nodeName.toLowerCase() == 'a') {
                    return obj;
                }
                obj = obj.parentNode;
            } while (obj != body);return false;
        },
        touchHover: function(obj, sClass) {
            var sBegin = obj.className;
            sClass = !sClass ? 'press' : sClass;
            obj.className = sBegin + ' ' + sClass;
            document.addEventListener('touchend', fnEnd, false);
            document.addEventListener('touchmove', fnEnd, false);
            function fnEnd() {
                obj.className = sBegin;
                document.removeEventListener('touchmove', fnEnd, false);
                document.removeEventListener('touchend', fnEnd, false);
            }
        }
    };
    var oHtml = document.getElementsByTagName('html')[0];
    oHtml.addEventListener('touchstart', function(e) {
        var oTarget = e.target;
        tool.touchHover(tool.isBtn(oTarget));
    }, false);
    tool.tap(oHtml, function(e) {
        var oTarget = e.target;
        if (tool.isBtn(oTarget) && tool.isBtn(oTarget).getAttribute('href')) {
            window.location = tool.isBtn(oTarget).getAttribute('href');
        }
    });
}());
