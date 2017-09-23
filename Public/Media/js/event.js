function bind(b, c, d) {
    var e = function() {
        d.call(b)
    };
    e.photo = d;
    if (!b['a' + c + '521']) {
        b['a' + c + "521"] = []
    }
    var a = b['a' + c + "521"];
    for (var i = 0; i < a.length; i++) {
        if (a[i].photo == d) return
    }
    b['a' + c + "521"].push(e)
};
function unbind(b, c, d) {
    var a = b['a' + c + '521'];
    if (!a) return;
    for (var i = 0; i < a.length; i++) {
        if (a[i].photo == d) {
            b.detachEvent('on' + c, a[i]);
            a.splice(i, 1);
            break
        }
    }
};
function fnDisguise(a, b) {
    return function(e) {
        b.call(a, e)
    }
};
function on(b, c, d) {
    if (typeof b['on' + c] == 'undefined') {
        var a = b['a' + c + '2002-10'];
        if (!a) {
            a = b['a' + c + '2002-10'] = []
        }
        a.push(d);
        return
    } else if (b.addEventListener) {
        b.addEventListener(c, d, false);
        return
    } else {
        var a = b['a' + c + '2002-10'];
        if (!a) {
            a = b['a' + c + '2002-10'] = []
        }
        a.push(d);
        bind(b, c, run)
    }
};
function fire(b, e) {
    var a = this['a' + b + '2002-10'];
    if (!a) return;
    for (var i = 0; i < a.length; i++) {
        a[i].call(this, e)
    }
};
function run(e) {
    e = e || window.event;
    if (!e.target) e.target = e.srcElement;
    if (typeof e.pageX == "undefined") {
        e.pageX = ((document.documentElement.scrollLeft || document.body.scrollLeft) + e.clientX);
        e.pageY = ((document.documentElement.scrollTop || document.body.scrollTop) + e.clientY)
    }
    if (typeof e.stopPropagation == "undefined") {
        e.stopPropagation = function() {
            e.cancelBubble = true
        }
    }
    if (typeof e.preventDefault == "undefined") {
        e.preventDefault = function() {
            e.returnValue = false
        }
    }
    var a = this['a' + e.type + '2002-10'];
    if (!a) return;
    for (var i = 0; i < a.length; i++) {
        a[i].call(this, e)
    }
};
function off(b, c, d) {
    if (b.removeEventListener) {
        b.removeEventListener(c, d, false);
        return
    }
    var a = b['a' + c + '2002-10'];
    if (!a) return;
    for (var i = 0; i < a.length; i++) {
        if (a[i] == d) {
            a.splice(i, 1);
            break
        }
    }
};

function down(e) {
    this.x = this.offsetLeft;
    this.y = this.offsetTop;
    this.mouseY = e.pageY;
    if (this.setCapture) {
        this.setCapture();
        on(this, 'mousemove', move);
        on(this, 'mouseup', up)
    } else {
        this.MOVE = fnDisguise(this, move);
        this.UP = fnDisguise(this, up);
        on(document, 'mousemove', this.MOVE);
        on(document, 'mouseup', this.UP)
    }
    e.preventDefault();
    this.preSt = 0;
    fire.call(this, "dragStart", e)
};
function move(e) {
    if (this.y + (e.pageY - this.mouseY) <= 0) this.style.top = 0;
    else if (this.y + (e.pageY - this.mouseY) >= (content.offsetHeight * container.offsetHeight / content.offsetHeight - this.offsetHeight)) {
        this.style.top = content.offsetHeight * container.offsetHeight / content.offsetHeight - this.offsetHeight + 'px'
    } else {
        this.style.top = this.y + (e.pageY - this.mouseY) + 'px'
    }
    var a = container.scrollTop = this.offsetTop * (content.offsetHeight / container.offsetHeight);
    if (a > this.preSt) {
        for (var j = 0; j < oLis.length; j++) {
            if (a < oDivs[j].st) break
        }
        if (oLis[j - 2] && this.preLi !== j) {
            if ((j) > (flag + 1)) {
                DOM.removeClass(oLis[j - 2], "selectedTab");
                DOM.addClass(oLis[j - 1], "selectedTab");
                animate(blueline, {
                    top: (j - 1) * 48
                },
                500, 2)
            }
        }
        flag = j - 1
    } else if (a < this.preSt) {
        for (var j = oLis.length - 1; j >= 0; j--) {
            if (a > oDivs[j].st) break
        }
        if (oLis[j + 2] && this.preLi !== j) {
            if (flag === undefined) return;
            if ((j) < (flag)) {
                for (var k = 0; k < oLis.length; k++) {
                    DOM.removeClass(oLis[k], "selectedTab")
                }
                DOM.addClass(oLis[j + 1], "selectedTab");
                animate(blueline, {
                    top: (j + 1) * 48
                },
                500, 2);
                upFlag = j + 1
            }
        }
    }
    this.preLi = j;
    this.preSt = a;
    fire.call(this, "dragging", e)
};
function up(e) {
    if (this.releaseCapture) {
        this.releaseCapture();
        off(this, 'mousemove', move);
        off(this, 'mouseup', up)
    } else {
        off(document, 'mousemove', this.MOVE);
        off(document, 'mouseup', this.UP)
    }
    fire.call(this, "dragEnd", e)
};