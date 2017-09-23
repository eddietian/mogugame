var DOM = {};
DOM.listToArray = function(eles) {
    try {
        var a = [].slice.call(eles, 0)
    } catch(e) {
        var a = [];
        for (var i = 0; i < eles.length; i++) {
            a.push(eles[i])
        }
    }
    return a
};
DOM.getIndex = function(ele) {
    var index = 0;
    for (var p = ele.previousSibling; p;) {
        if (p.nodeType == 1) {
            index++
        }
        p = p.previousSibling
    }
    return index
};
DOM.getElesByClass = function(strClass, context) {
    context = context || document;
    if (context.getElementsByClassName) {
        return context.getElementsByClassName(strClass)
    }
    var aClass = strClass.split(/ +/);
    var eles = context.getElementsByTagName("*");
    for (var i = 0; i < aClass.length; i++) {
        eles = byClass(aClass[i], eles)
    }
    return eles;
    function byClass(className, eles) {
        var reg = new RegExp("(?:^| +)" + className + "(?: +|$)");
        var a = [];
        for (var i = 0; i < eles.length; i++) {
            var ele = eles[i];
            if (reg.test(ele.className)) {
                a.push(ele)
            }
        }
        return a
    }
};
DOM.addClass = function(ele, strClass) {
    var reg = new RegExp("(?:^| +)" + strClass + "(?: +|$)");
    if (!reg.test(ele.className)) ele.className += " " + strClass
};
DOM.removeClass = function(ele, strClass) {
    var reg = new RegExp("(?:^| +)" + strClass + "(?: +|$)", "g");
    ele.className = ele.className.replace(reg, "")
};
DOM.children = function(ele, tagName) {
    var childNodes = ele.childNodes;
    var a = [];
    if (typeof tagName == "string") {
        tagName = tagName.toUpperCase();
        for (var i = 0; i < childNodes.length; i++) {
            var child = childNodes[i];
            if (child.nodeType == 1 && child.nodeName == tagName) {
                a.push(child)
            }
        }
    }
    return a
};
function getCss(a, b) {
    if (b == "scrollTop") {
        return a.scrollTop
    }
    if (window.getComputedStyle) return parseFloat(getComputedStyle(a, null)[b]);
    else return parseFloat(a.currentStyle[b])
};
function setCss(a, b, c) {
    if (b == "opacity") {
        a.style.opacity = c;
        a.style.filter = "alpha(opacity=" + c * 100 + ")"
    } else if (b == "scrollTop") {
        a.scrollTop = c
    } else {
        a.style[b] = c + "px"
    }
};
function animate(ele, obj, duration, type, fnCallback) {
    var zhufengEffect = {
        zfLinear: function(t, b, c, d) {
            return c * t / d + b
        },
        Quad: {
            easeIn: function(t, b, c, d) {
                return c * (t /= d) * t + b
            },
            easeOut: function(t, b, c, d) {
                return - c * (t /= d) * (t - 2) + b
            },
            easeInOut: function(t, b, c, d) {
                if ((t /= d / 2) < 1) return c / 2 * t * t + b;
                return - c / 2 * ((--t) * (t - 2) - 1) + b
            }
        },
        Cubic: {
            easeIn: function(t, b, c, d) {
                return c * (t /= d) * t * t + b
            },
            easeOut: function(t, b, c, d) {
                return c * ((t = t / d - 1) * t * t + 1) + b
            },
            easeInOut: function(t, b, c, d) {
                if ((t /= d / 2) < 1) return c / 2 * t * t * t + b;
                return c / 2 * ((t -= 2) * t * t + 2) + b
            }
        },
        Quart: {
            easeIn: function(t, b, c, d) {
                return c * (t /= d) * t * t * t + b
            },
            easeOut: function(t, b, c, d) {
                return - c * ((t = t / d - 1) * t * t * t - 1) + b
            },
            easeInOut: function(t, b, c, d) {
                if ((t /= d / 2) < 1) return c / 2 * t * t * t * t + b;
                return - c / 2 * ((t -= 2) * t * t * t - 2) + b
            }
        },
        Quint: {
            easeIn: function(t, b, c, d) {
                return c * (t /= d) * t * t * t * t + b
            },
            easeOut: function(t, b, c, d) {
                return c * ((t = t / d - 1) * t * t * t * t + 1) + b
            },
            easeInOut: function(t, b, c, d) {
                if ((t /= d / 2) < 1) return c / 2 * t * t * t * t * t + b;
                return c / 2 * ((t -= 2) * t * t * t * t + 2) + b
            }
        },
        Sine: {
            easeIn: function(t, b, c, d) {
                return - c * Math.cos(t / d * (Math.PI / 2)) + c + b
            },
            easeOut: function(t, b, c, d) {
                return c * Math.sin(t / d * (Math.PI / 2)) + b
            },
            easeInOut: function(t, b, c, d) {
                return - c / 2 * (Math.cos(Math.PI * t / d) - 1) + b
            }
        },
        Expo: {
            easeIn: function(t, b, c, d) {
                return (t == 0) ? b: c * Math.pow(2, 10 * (t / d - 1)) + b
            },
            easeOut: function(t, b, c, d) {
                return (t == d) ? b + c: c * ( - Math.pow(2, -10 * t / d) + 1) + b
            },
            easeInOut: function(t, b, c, d) {
                if (t == 0) return b;
                if (t == d) return b + c;
                if ((t /= d / 2) < 1) return c / 2 * Math.pow(2, 10 * (t - 1)) + b;
                return c / 2 * ( - Math.pow(2, -10 * --t) + 2) + b
            }
        },
        Circ: {
            easeIn: function(t, b, c, d) {
                return - c * (Math.sqrt(1 - (t /= d) * t) - 1) + b
            },
            easeOut: function(t, b, c, d) {
                return c * Math.sqrt(1 - (t = t / d - 1) * t) + b
            },
            easeInOut: function(t, b, c, d) {
                if ((t /= d / 2) < 1) return - c / 2 * (Math.sqrt(1 - t * t) - 1) + b;
                return c
                /*�زļ�԰ - www.sucaijiayuan.com*/
                / 2 * (Math.sqrt(1 - (t -= 2) * t) + 1) + b
            }
        },
        Elastic: {
            easeIn: function(t, b, c, d, a, p) {
                if (t == 0) return b;
                if ((t /= d) == 1) return b + c;
                if (!p) p = d * .3;
                if (!a || a < Math.abs(c)) {
                    a = c;
                    var s = p / 4
                } else var s = p / (2 * Math.PI) * Math.asin(c / a);
                return - (a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b
            },
            easeOut: function(t, b, c, d, a, p) {
                if (t == 0) return b;
                if ((t /= d) == 1) return b + c;
                if (!p) p = d * .3;
                if (!a || a < Math.abs(c)) {
                    a = c;
                    var s = p / 4
                } else var s = p / (2 * Math.PI) * Math.asin(c / a);
                return (a * Math.pow(2, -10 * t) * Math.sin((t * d - s) * (2 * Math.PI) / p) + c + b)
            },
            easeInOut: function(t, b, c, d, a, p) {
                if (t == 0) return b;
                if ((t /= d / 2) == 2) return b + c;
                if (!p) p = d * (.3 * 1.5);
                if (!a || a < Math.abs(c)) {
                    a = c;
                    var s = p / 4
                } else var s = p / (2 * Math.PI) * Math.asin(c / a);
                if (t < 1) return - .5 * (a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b;
                return a * Math.pow(2, -10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p) * .5 + c + b
            }
        },
        Back: {
            easeIn: function(t, b, c, d, s) {
                if (s == undefined) s = 1.70158;
                return c * (t /= d) * t * ((s + 1) * t - s) + b
            },
            easeOut: function(t, b, c, d, s) {
                if (s == undefined) s = 1.70158;
                return c * ((t = t / d - 1) * t * ((s + 1) * t + s) + 1) + b
            },
            easeInOut: function(t, b, c, d, s) {
                if (s == undefined) s = 1.70158;
                if ((t /= d / 2) < 1) return c / 2 * (t * t * (((s *= (1.525)) + 1) * t - s)) + b;
                return c / 2 * ((t -= 2) * t * (((s *= (1.525)) + 1) * t + s) + 2) + b
            }
        },
        zfBounce: {
            easeIn: function(t, b, c, d) {
                return c - zhufengEffect.zfBounce.easeOut(d - t, 0, c, d) + b
            },
            easeOut: function(t, b, c, d) {
                if ((t /= d) < (1 / 2.75)) {
                    return c * (7.5625 * t * t) + b
                } else if (t < (2 / 2.75)) {
                    return c * (7.5625 * (t -= (1.5 / 2.75)) * t + .75) + b
                } else if (t < (2.5 / 2.75)) {
                    return c * (7.5625 * (t -= (2.25 / 2.75)) * t + .9375) + b
                } else {
                    return c * (7.5625 * (t -= (2.625 / 2.75)) * t + .984375) + b
                }
            },
            easeInOut: function(t, b, c, d) {
                if (t < d / 2) return zhufengEffect.zfBounce.easeIn(t * 2, 0, c, d) * .5 + b;
                else return zhufengEffect.zfBounce.easeOut(t * 2 - d, 0, c, d) * .5 + c * .5 + b
            }
        }
    };
    var fnEffect = zhufengEffect.Expo.easeOut;
    if (typeof type == "number") {
        switch (type) {
        case 0:
            break;
        case 1:
            fnEffect = zhufengEffect.zfLinear;
            break;
        case 2:
            fnEffect = zhufengEffect.Back.easeOut;
            break;
        case 3:
            fnEffect = zhufengEffect.Elastic.easeOut;
            break;
        case 4:
            fnEffect = zhufengEffect.zfBounce.easeOut;
            break
        }
    } else if (typeof type == "function") {
        fnCallback = type
    };
    var oBegin = {};
    var oChange = {};
    var flag = 0;
    for (var attr in obj) {
        var begin = getCss(ele, attr);
        var change = obj[attr] - begin;
        if (change) {
            oBegin[attr] = begin;
            oChange[attr] = change;
            flag++
        }
    }
    if (flag === 0) return;
    var times = 0;
    if (document.all) var interval = 15;
    else var interval = 13;
    window.clearTimeout(ele.timer);
    _move();
    function _move() {
        times += interval;
        if (times >= duration) {
            for (var a in oChange) {
                var b = obj[a];
                setCss(ele, a, b)
            }
            ele.timer = null;
            if (typeof fnCallback == "function") {
                fnCallback.call(ele)
            }
        } else {
            for (var a in oChange) {
                var c = oChange[a];
                var d = oBegin[a];
                var e = fnEffect(times, d, c, duration);
                setCss(ele, a, e)
            }
            ele.timer = window.setTimeout(_move, interval)
        }
    }
};;