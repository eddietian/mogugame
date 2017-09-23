define("/Public/Mobile/js/index", ["../../cmdmodule/common", "../../cmdmodule/common/portal", "../../cmdmodule/ucb", "../../cmdmodule/common/lazyload", "../../cmdmodule/common/search", "../../cmdmodule/common/mod_fun", "../../cmdmodule/big_change_img", "/Public/Mobile/js/slip", "./module/push_popup", "./module/guide_mask", "../../cmdmodule/have_cookie", "../../cmdmodule/scroll_notice", "./module/dele_game_cookie"], function(a) {
    a("/common"),
    a("/Public/Mobile/js/big_change_img").init(),
    a("/push_popup"),
    a("/guide_mask"),
    a("/scroll_notice"),
    $("#clsoePagegame").tap(function() {
        $("#pagegameWrap").hide()
    }),
    a("./module/dele_game_cookie")
}),
define("dist/cmdmodule/common", ["dist/cmdmodule/common/portal", "dist/cmdmodule/ucb", "dist/cmdmodule/common/lazyload", "dist/cmdmodule/common/search", "dist/cmdmodule/common/mod_fun"], function(a) {
    a("dist/cmdmodule/common/portal"),
    a("dist/cmdmodule/common/lazyload"),
    a("dist/cmdmodule/common/search"),
    a("dist/cmdmodule/common/mod_fun")
}),
define("dist/cmdmodule/common/portal", ["dist/cmdmodule/ucb"], function(a) {
    var b = a("dist/cmdmodule/ucb")
      , c = c || {}
      , d = c;
    c.Statis = c.Statis || {},
    function(a) {
        var c = function(a, b) {
            if (0 >= b)
                return void 0;
            if (void 0 == a || 0 == a.length)
                return void 0;
            var d = a.attr("data-statis");
            return null  != d && void 0 != d && d.length >= 1 ? d : c(a.parent(), --b)
        }
        ;
        a.handler = function(a) {
            var d = $(a.target)
              , e = c(d, 5);
            null  != e && void 0 != e && e.length >= 1 ? b.Cookie.set("statis", e, {
                path: "/",
                domain: ".fpwap.com"
            }) : b.Cookie.remove("statis")
        }
        ,
        a.documentListener = function() {
            var c = "click";
            b.Supports.Touch ? c = "touchstart" : "onmousedown" in window && (c = "mousedown"),
            b.Cookie.remove("statis"),
            $(document).on(c, a.handler)
        }
    }(c.Statis),
    d.Statis.documentListener()
}),
define("dist/cmdmodule/ucb", [], function(a, b, c) {
    var d = d || {};
    d.Supports = {
        Touch: "ontouchstart" in window
    },
    d.Cookie = d.Cookie || {},
    function(a) {
        a._isValidKey = function(a) {
            return new RegExp('^[^\\x00-\\x20\\x7f\\(\\)<>@,;:\\\\\\"\\[\\]\\?=\\{\\}\\/\\u0080-\\uffff]+$').test(a)
        }
        ,
        a.getRaw = function(b) {
            if (a._isValidKey(b)) {
                var c = new RegExp("(^| )" + b + "=([^;]*)(;|$)")
                  , d = c.exec(document.cookie);
                if (d)
                    return d[2] || null 
            }
            return null 
        }
        ,
        a.get = function(b) {
            var c = a.getRaw(b);
            return "string" == typeof c ? c = decodeURIComponent(c) : null 
        }
        ,
        a.setRaw = function(b, c, d) {
            if (a._isValidKey(b)) {
                d = d || {};
                var e = d.expires;
                "number" == typeof d.expires && (e = new Date,
                e.setTime(e.getTime() + d.expires)),
                document.cookie = b + "=" + c + (d.path ? "; path=" + d.path : "") + (e ? "; expires=" + e.toGMTString() : "") + (d.domain ? "; domain=" + d.domain : "") + (d.secure ? "; secure" : "")
            }
        }
        ,
        a.set = function(b, c, d) {
            a.setRaw(b, encodeURIComponent(c), d)
        }
        ,
        a.remove = function(b, c) {
            c = c || {},
            c.expires = new Date(0),
            a.setRaw(b, "", c)
        }
    }(d.Cookie),
    c.exports = d
}),
define("dist/cmdmodule/common/lazyload", [], function() {
    var a = {
        init: function() {
            var a = this;
            a.img.onerrorImgUrl = "/public/images/android_new/link/grey.gif",
            a.img.srcStore = "dataimg",
            a.img.class = "lazy",
            a.img.sensitivity = 50,
            a.img.init()
        },
        img: {
            trigger: function() {
                var a = this;
                eventType = a.isPhone && "touchend" || "scroll",
                a.imglist = $("img." + a.class),
                $(window).trigger(eventType)
            },
            init: function() {
                var a = this
                  , b = 5
                  , c = 200
                  , d = navigator.appVersion.match(/(iPhone\sOS)\s([\d_]+)/)
                  , e = d && !0 || !1
                  , f = e && d[2].split("_");
                if (f = f && parseFloat(f.length > 1 ? f.splice(0, 2).join(".") : f[0], 10),
                e = a.isPhone = e && 6 > f) {
                    var g, h;
                    $(window).on("touchstart", function() {
                        g = {
                            sy: window.scrollY,
                            time: Date.now()
                        },
                        h && clearTimeout(h)
                    }).on("touchend", function(d) {
                        if (d && d.changedTouches) {
                            var e = Math.abs(window.scrollY - g.sy);
                            if (e > b) {
                                var f = Date.now() - g.time;
                                h = setTimeout(function() {
                                    a.changeimg(),
                                    g = {},
                                    clearTimeout(h),
                                    h = null 
                                }, f > c ? 0 : 200)
                            }
                        } else
                            a.changeimg()
                    }).on("touchcancel", function() {
                        h && clearTimeout(h),
                        g = {}
                    })
                } else
                    $(window).on("scroll", function() {
                        a.changeimg()
                    });
                a.trigger(),
                a.isload = !0
            },
            changeimg: function() {
                function a(a) {
                    var b = window.pageYOffset
                      , d = window.pageYOffset + window.innerHeight
                      , e = a.offset().top;
                    return e >= b && e - c.sensitivity <= d
                }
                function b(a, b) {
                    var d = a.attr(c.srcStore);
                    a.attr("src", d),
                    a[0].onload || (a[0].onload = function() {
                        $(this).removeClass(c.class).removeAttr(c.srcStore),
                        c.imglist[b] = null ,
                        this.onerror = this.onload = null 
                    }
                    ,
                    a[0].onerror = function() {
                        this.src = c.onerrorImgUrl,
                        $(this).removeClass(c.class).removeAttr(c.srcStore),
                        c.imglist[b] = null ,
                        this.onerror = this.onload = null 
                    }
                    )
                }
                var c = this;
                c.imglist.each(function(d, e) {
                    if (e) {
                        var f = $(e);
                        a(f) && f.attr(c.srcStore) && b(f, d)
                    }
                })
            }
        }
    };
    a.init()
}),
define("dist/cmdmodule/common/search", [], function() {
    function a() {
        var a = $.trim($("#js_page_so_pop").val());
        return "" == a ? !1 : ($("#kw").val(a),
        $("#kw_form").submit(),
        void 0)
    }
    function b() {
        i.style.display = "block",
        i.style.bottom = "0",
        m.style.display = "block",
        k.focus()
    }
    function c() {
        i.style.display = "none"
    }
    function d() {
        var a = $("#js_so_pop_text").val();
        l.style.display = "" == a ? "none" : "block"
    }
    function e() {
        $("#js_so_pop_text").val(""),
        n.style.display = "none"
    }
    function f() {
        var a = "andsearch" == $("#id_search_type").val() ? "2" : "3"
          , b = "undefined" != typeof $("#id_limit") ? $("#id_limit").val() : 10
          , c = "undefined" != typeof $("#id_filter") ? $("#id_filter").val() : 0
          , d = k.value.length;
        if (1 > d)
            return n.style.display = "none",
            o.style.display = "block",
            void 0;
        try {
            $.ajax({
                type: "get",
                dataType: "json",
                url: "/searchname.html",
                data: {
                    kwd: k.value,
                    platform: a,
                    random: Math.random(),
                    limit: b,
                    filter: c,
                    columnId: 2002142
                },
                success: function(a) {
                    $("#js_so_pop_guess").html("");
                    for (var b in a)
                        if (0 == b && null  != a[b].image && "" != a[b].image) {
                            var c = "";
                            c = "1" == a[b].btnType ? '<span class="btn" type="btn" data-statis="text:btn_x_serach_tip_' + a[b].gameid + '" href="/game/downs_' + a[b].gameid + '_2.html" >下载</span>' : "1" == a[b].btnType ? '<span class="btn" type="btn" data-statis="text:btn_x_serach_tip_' + a[b].gameid + '" href="/game/detail_' + a[b].gameid + '.html" >进入</span>' : '<span class="btn" type="btn" data-statis="text:btn_x_serach_tip_' + a[b].gameid + '" href="/game/detail_' + a[b].gameid + '.html" >详情</span>',
                            $("#js_so_pop_guess").append('<li class="match-item"><div class="m-wrap" type="btn" data-statis="text:logo_x_serach_tip_' + a[b].gameid + '" href="/game/detail_' + a[b].gameid + '.html"><img src="http://image.game.uc.cn' + a[b].image + '" class="game-icon-sm" /><div class="content"><p class="game-title">' + a[b].name + "</p></div>" + c + "</div></li>")
                        } else
                            $("#js_so_pop_guess").append("<li class='select_item' type='btn' data-statis='text:logo_x_serach_tip_" + a[b].gameid + "' href='/search/?keyword=" + a[b].name + "&text=xandsearch'>" + a[b].name + "</li>")
                }
            })
        } catch (e) {
            k.style.webkitBorderRadius = "3px",
            n.style.display = "none"
        }
    }
    function g() {
        n.style.display = "block",
        o.style.display = "none"
    }
    var h = document.querySelector("#soSubmit");
    void 0 != h && null  != h && ($("#kw_form").submit(function() {
        var b = $.trim($("#js_page_so_pop").val());
        return "" == b ? !1 : (a(),
        void 0)
    }),
    $("#soSubmit").tap(function() {
        a()
    }));
    var i = ai.i("js_so_pop");
    if (void 0 != i && null  != i) {
        var j, k = ai.i("js_so_pop_text"), l = ai.i("js_so_clear"), m = (ai.i("js_so_pop_btn"),
        ai.i("js_so_pop_main")), n = ai.i("js_so_pop_guess"), o = ai.i("js_so_pop_promote"), p = ai.i("js_so_pop_form_shell");
        $("#iconSo").click(function() {
            b()
        }),
        $("#js_page_so_pop").click(function() {
            b()
        }),
        $("#js_so_pop_close").click(function() {
            c()
        }),
        ai.touchMovePreventDefault(i),
        ai.touchMovePreventDefault(p),
        p.addEventListener("touchstart", function(a) {
            a.stopPropagation()
        }),
        ai.touchClick(l, function() {
            k.value = "",
            k.focus(),
            this.style.display = "none",
            n.style.display = "none",
            o.style.display = "block"
        }),
        k.addEventListener("focus", function() {
            b()
        }),
        k.addEventListener("input", function() {
            j = k.value.length,
            j > 0 ? (l.style.display = "block",
            $("#js_so_pop_text").css("color", "#333")) : l.style.display = "none",
            g(),
            f()
        }),
        $("#js_so_pop_form").submit(function() {
            var a = $("#js_so_pop_text").val();
            return "" == a ? ($("#js_so_pop_text").val("请输入关键字"),
            $("#js_so_pop_text").css("color", "#999"),
            l.style.display = "block",
            !1) : "请输入关键字" == a ? !1 : void 0
        }),
        d(),
        $("#js_so_pop_close").click(function() {
            e()
        }),
        $(".js_so_pop_return").click(function() {
            e(),
            window.history.go(-1)
        }),
        k.addEventListener("focus", function() {
            o.style.display = "block"
        })
    }
}),
define("dist/cmdmodule/common/mod_fun", [], function() {
    $(".js_closeparent").each(function() {
        $(this).tap(function() {
            var a = $(this).parents(".js_closeparent_wrap");
            a.hide()
        })
    }),
    $(".popup_close").each(function() {
        $(this).tap(function() {
            $(this).parents(".mod-popup").hide()
        })
    }),
    $(".mod-popup-bg").on("touchmove", function(a) {
        a.preventDefault()
    })
}),
define("/big_change_img", ["/Public/Mobile/js/slip"], function(a, b, c) {
    var d = a("/Public/Mobile/js/slip")
      , e = {}
      , f = document;
    e.init = function() {
        function a(a, b) {
            function c() {
                for (var a = 0; a < j.length; a++)
                    j[a].className = a == this.page ? "on" : ""
            }
            var e = a.querySelector("ul")
              , f = a.querySelectorAll("li")
              , g = a.querySelectorAll("#bigIndex")[0];
            if (1 >= b)
                g.innerHTML = "";
            else {
                for (var h = "<i class='on'></i>", i = 1; b > i; i++)
                    h += "<i></i>";
                g.innerHTML = h
            }
            for (var j = a.querySelectorAll("i"), k = f[0].offsetWidth, i = 0; i < f.length; i++)
                f[i].style.width = k + "px";
            e.style.width = k * f.length + "px",
            d("page", e, {
                change_time: 5e3,
                num: b,
                parent_wide_high: k,
                endFun: c
            })
        }
        var b = f.querySelector("#bigGlide");
        a(b, b.querySelectorAll("li").length)
    }
    ,
    c.exports = e
}),
define("dist/cmdmodule/slip", [], function(a, b, c) {
    !function(a, b) {
        function d(a, b, c) {
            if (c || (c = {}),
            _fun.ios() && parseInt(_fun.version()) >= 5 && "x" == c.direction && c.wit)
                b.parentNode.style.cssText += "overflow:scroll; -webkit-overflow-scrolling:touch;";
            else
                switch (a) {
                case "page":
                    c.direction = "x";
                    var d = _fun.clone(g);
                    return d._init(b, c),
                    d;
                case "px":
                    var e = _fun.clone(h);
                    return e._init(b, c),
                    e
                }
        }
        _fun = {
            ios: function() {
                var a = navigator.userAgent.match(/.*OS\s([\d_]+)/)
                  , b = !!a;
                return !this._version_value && b && (this._version_value = a[1].replace(/_/g, ".")),
                this.ios = function() {
                    return b
                }
                ,
                b
            },
            version: function() {
                return this._version_value
            },
            clone: function(a) {
                function b() {}
                return b.prototype = a,
                new b
            }
        };
        var f = {
            _refreshCommon: function(a, b) {
                var c = this;
                c.wide_high = a || c.core[c.offset] - c.up_range,
                c.parent_wide_high = b || c._parent_node[c.offset],
                c._getCoreWidthSubtractShellWidth()
            },
            _initCommon: function(a, b) {
                var c = this;
                c.core = a,
                c.startFun = b.startFun,
                c.moveFun = b.moveFun,
                c.touchEndFun = b.touchEndFun,
                c.endFun = b.endFun,
                c.direction = b.direction,
                c.up_range = b.up_range || 0,
                c.down_range = b.down_range || 0,
                c._parent_node = c.core.parentNode,
                "x" == c.direction ? (c.offset = "offsetWidth",
                c._pos = c.__posX) : (c.offset = "offsetHeight",
                c._pos = c.__posY),
                c.wide_high = b.wide_high || c.core[c.offset] - c.up_range,
                c.parent_wide_high = b.parent_wide_high || c._parent_node[c.offset],
                c._getCoreWidthSubtractShellWidth(),
                c._bind("touchstart"),
                c._bind("touchmove"),
                c._bind("touchend"),
                c._bind("webkitTransitionEnd"),
                c.xy = 0,
                c.y = 0
            },
            _getCoreWidthSubtractShellWidth: function() {
                var a = this;
                a.width_cut_coreWidth = a.parent_wide_high - a.wide_high,
                a.coreWidth_cut_width = a.wide_high - a.parent_wide_high
            },
            handleEvent: function(a) {
                var b = this;
                switch (a.type) {
                case "touchstart":
                    b._start(a);
                    break;
                case "touchmove":
                    b._move(a);
                    break;
                case "touchend":
                case "touchcancel":
                    b._end(a);
                    break;
                case "webkitTransitionEnd":
                    b._transitionEnd(a)
                }
            },
            _bind: function(a, b) {
                this.core.addEventListener(a, this, !!b)
            },
            _unBind: function(a, b) {
                this.core.removeEventListener(a, this, !!b)
            },
            __posX: function(a) {
                this.xy = a,
                this.core.style.webkitTransform = "translate3d(" + a + "px, 0px, 0px)"
            },
            __posY: function(a) {
                this.xy = a,
                this.core.style.webkitTransform = "translate3d(0px, " + a + "px, 0px)"
            },
            _posTime: function(a, b) {
                this.core.style.webkitTransitionDuration = "" + b + "ms",
                this._pos(a)
            },
            addCallback: function(a, b) {
                this[a] = b
            }
        }
          , g = _fun.clone(f);
        g._init = function(a, b) {
            var c = this;
            if (c.num = b.num,
            1 != c.num) {
                if (c._initCommon(a, b),
                c.page = 0,
                c.loop = b.loop,
                c.change_time = b.change_time,
                c.lastPageFun = b.lastPageFun,
                c.firstPageFun = b.firstPageFun,
                b.change_time && c._autoChange(),
                c.loop) {
                    var d = c.core.querySelectorAll("a")
                      , e = d[0].cloneNode(!0)
                      , f = d[c.num - 1].cloneNode(!0);
                    c.core.insertBefore(f, d[0]),
                    c.core.appendChild(e),
                    c._initial_coordinates = -c.parent_wide_high
                } else
                    c._initial_coordinates = 0;
                b.no_follow ? (c._move = c._moveNoMove,
                c.next_time = 500) : c.next_time = 300,
                c._pos(c._initial_coordinates),
                c._parent_node.style.webkitTransform = "translate3d(0px, 0px, 0px)"
            }
        }
        ,
        g._start = function(a) {
            var b = this
              , a = a.touches[0];
            b._abrupt_x = 0,
            b._abrupt_x_abs = 0,
            b._start_x = b._start_x_clone = a.pageX,
            b._start_y = a.pageY,
            b._movestart = void 0,
            b.change_time && b._stop(),
            b.startFun && b.startFun(a)
        }
        ,
        g._move = function(a) {
            var b = this;
            if (b._moveShare(a),
            !b._movestart) {
                var c = a.touches[0];
                if (a.preventDefault(),
                b.offset_x = b.loop ? b._dis_x + b.xy : b.xy > 0 || b.xy < b.width_cut_coreWidth ? b._dis_x / 2 + b.xy : b._dis_x + b.xy,
                b._start_x = c.pageX,
                b._abrupt_x_abs < 6)
                    return b._abrupt_x += b._dis_x,
                    b._abrupt_x_abs = Math.abs(b._abrupt_x),
                    void 0;
                b._pos(b.offset_x),
                b.moveFun && b.moveFun(c)
            }
        }
        ,
        g._moveNoMove = function(a) {
            var b = this;
            b._moveShare(a),
            b._movestart || (a.preventDefault(),
            b.moveFun && b.moveFun(e))
        }
        ,
        g._moveShare = function(a) {
            var b = this
              , c = a.touches[0];
            b._dis_x = c.pageX - b._start_x,
            b._dis_y = c.pageY - b._start_y,
            "undefined" == typeof b._movestart && (b._movestart = !!(b._movestart || Math.abs(b._dis_x) < Math.abs(b._dis_y)))
        }
        ,
        g._end = function(a) {
            if (!this._movestart) {
                var b = this;
                b._end_x = a.changedTouches[0].pageX,
                b._range = b._end_x - b._start_x_clone,
                b._range > 35 ? b._backward() : Math.abs(b._range) > 35 && b._forward(),
                b.touchEndFun && b.touchEndFun(a)
            }
        }
        ,
        g.backward = function() {
            var a = this;
            a.change_time && a._stop(),
            a._backward()
        }
        ,
        g.forward = function() {
            var a = this;
            a.change_time && a._stop(),
            a._forward()
        }
        ,
        g._backward = function(a) {
            var b = this
              , c = b.page -= 1;
            0 > c && (b.loop ? c = -1 : (c = 0,
            b.firstPageFun && b.firstPageFun(a))),
            b.toPage(c, b.next_time)
        }
        ,
        g._forward = function(a) {
            var b = this
              , c = b.page += 1;
            c > b.num - 1 && (b.loop ? c = b.num : (c = b.num - 1,
            b.lastPageFun && b.lastPageFun(a))),
            b.toPage(c, b.next_time)
        }
        ,
        g._transitionEnd = function(a) {
            var b = this;
            a.stopPropagation(),
            b.core.style.webkitTransitionDuration = "0",
            b._stop_ing && b._autoChange(),
            b._stop_ing = !1,
            b.loop && (b.page >= b.num ? b.toPage(0, 0) : b.page <= -1 && b.toPage(b.num - 1, 0)),
            b.endFun && b.endFun()
        }
        ,
        g.toPage = function(a, b) {
            this._posTime(-this.parent_wide_high * a + this._initial_coordinates, b || 0),
            this.page = a
        }
        ,
        g._stop = function() {
            clearInterval(this._autoChangeSet),
            this._stop_ing = !0
        }
        ,
        g._autoChange = function() {
            var a = this;
            a._autoChangeSet = setInterval(function() {
                a._page_medium = a.page + 1,
                a.page != a.num - 1 ? a.page += 1 : a.page = 0,
                a.loop ? a.toPage(a._page_medium, a.next_time) : a.toPage(a.page, a.next_time)
            }, a.change_time)
        }
        ,
        g.refresh = function(a, b) {
            this._refreshCommon(a, b),
            this.loop && (this._initial_coordinates = -this.parent_wide_high),
            this.toPage(this.page)
        }
        ;
        var h = _fun.clone(f);
        h._init = function(a, b) {
            var c = this;
            c._initCommon(a, b),
            c._pos(-c.up_range),
            c.perfect = b.perfect,
            c.bar_no_hide = b.bar_no_hide,
            c._steps = [],
            "x" == c.direction ? (c.page_x = "pageX",
            c.page_y = "pageY",
            c.width_or_height = "width",
            c._real = c._realX,
            c._posBar = c.__posBarX) : (c.page_x = "pageY",
            c.page_y = "pageX",
            c.width_or_height = "height",
            c._real = c._realY,
            c._posBar = c.__posBarY),
            c.perfect ? (c._transitionEnd = function() {}
            ,
            c._stop = c._stopPerfect,
            c._slipBar = c._slipBarPerfect,
            c._posTime = c._posTimePerfect,
            c._bar_upRange = c.up_range,
            c.no_bar = !1,
            c._slipBarTime = function() {}
            ) : (c.no_bar = b.no_bar,
            c.core.style.webkitTransitionTimingFunction = "cubic-bezier(0.33, 0.66, 0.66, 1)"),
            c.bar_no_hide && (c._hideBar = function() {}
            ,
            c._showBar = function() {}
            ),
            c.no_bar ? (c._hideBar = function() {}
            ,
            c._showBar = function() {}
            ) : (c.coreWidth_cut_width <= 0 && (c._bar_shell_opacity = 0,
            c._showBarStorage = c._showBar,
            c._showBar = function() {}
            ),
            c._insertSlipBar(b))
        }
        ,
        h._start = function(a) {
            var b = this
              , a = a.touches[0];
            b._animating = !1,
            b._abrupt_x = 0,
            b._abrupt_x_abs = 0,
            b._start_x = b._start_x_clone = a[b.page_x],
            b._start_y = a[b.page_y],
            b._start_time = a.timeStamp || Date.now(),
            b._movestart = void 0,
            !b.perfect && b._need_stop && b._stop(),
            b.core.style.webkitTransitionDuration = "0",
            b.startFun && b.startFun(a)
        }
        ,
        h._move = function(a) {
            var b = this
              , c = a.touches[0]
              , d = c[b.page_x]
              , e = c[b.page_y]
              , f = b.xy;
            if (b._dis_x = d - b._start_x,
            b._dis_y = e - b._start_y,
            "x" == b.direction && "undefined" == typeof b._movestart && (b._movestart = !!(b._movestart || Math.abs(b._dis_x) < Math.abs(b._dis_y))),
            !b._movestart) {
                if (a.preventDefault(),
                b._move_time = c.timeStamp || Date.now(),
                b.offset_x = f > 0 || f < b.width_cut_coreWidth - b.up_range ? b._dis_x / 2 + f : b._dis_x + f,
                b._start_x = d,
                b._start_y = e,
                b._abrupt_x_abs < 6)
                    return b._abrupt_x += b._dis_x,
                    b._abrupt_x_abs = Math.abs(b._abrupt_x),
                    void 0;
                b._pos(b.offset_x),
                b.no_bar || b._slipBar(),
                b._move_time - b._start_time > 300 && (b._start_time = b._move_time,
                b._start_x_clone = d),
                b.moveFun && b.moveFun(c)
            }
        }
        ,
        h._end = function(a) {
            if (!this._movestart) {
                var b = this
                  , a = a.changedTouches[0]
                  , c = (a.timeStamp || Date.now()) - b._start_time
                  , d = a[b.page_x] - b._start_x_clone;
                if (b._need_stop = !0,
                300 > c && Math.abs(d) > 10)
                    if (b.xy > -b.up_range || b.xy < b.width_cut_coreWidth)
                        b._rebound();
                    else {
                        var e = b._momentum(d, c, -b.xy - b.up_range, b.coreWidth_cut_width + b.xy, b.parent_wide_high);
                        b._posTime(b.xy + e.dist, e.time),
                        b.no_bar || b._slipBarTime(e.time)
                    }
                else
                    b._rebound();
                b.touchEndFun && b.touchEndFun(a)
            }
        }
        ,
        h._transitionEnd = function(a) {
            var b = this;
            a.target == b.core && (b._rebound(),
            b._need_stop = !1)
        }
        ,
        h._rebound = function(a) {
            var b = this
              , c = b.coreWidth_cut_width <= 0 ? 0 : b.xy >= -b.up_range ? -b.up_range : b.xy <= b.width_cut_coreWidth - b.up_range ? b.width_cut_coreWidth - b.up_range : b.xy;
            return c == b.xy ? (b.endFun && b.endFun(),
            b._hideBar(),
            void 0) : (b._posTime(c, a || 400),
            b.no_bar || b._slipBarTime(a),
            void 0)
        }
        ,
        h._insertSlipBar = function(a) {
            var c = this;
            if (c._bar = b.createElement("div"),
            c._bar_shell = b.createElement("div"),
            c._bar_show_ing = !0,
            "x" == c.direction)
                var d = "height: 5px; position: absolute; top:1px;z-index: 10; pointer-events: none;"
                  , e = "opacity: " + c._bar_shell_opacity + "; left:2px; bottom: 2px; right: 2px; height: 6px; position: absolute; z-index: 10; pointer-events: none;";
            else
                var d = "width: 5px; position: absolute; left:1px; z-index: 10; pointer-events: none;"
                  , e = "opacity: " + c._bar_shell_opacity + "; top:2px; bottom: 2px; right: 2px; width: 6px; position: absolute; z-index: 10; pointer-events: none; ";
            var f = " background-color: rgba(0, 0, 0, 0.5); border-radius: 11px; -webkit-transition: cubic-bezier(0.33, 0.66, 0.66, 1);"
              , d = d + f + a.bar_css;
            c._bar.style.cssText = d,
            c._bar_shell.style.cssText = e,
            c._countAboutBar(),
            c._countBarSize(),
            c._setBarSize(),
            c._countWidthCutBarSize(),
            c._bar_shell.appendChild(c._bar),
            c._parent_node.appendChild(c._bar_shell),
            setTimeout(function() {
                c._hideBar()
            }, 500)
        }
        ,
        h._posBar = function() {}
        ,
        h.__posBarX = function(a) {
            var b = this;
            b._bar.style.webkitTransform = "translate3d(" + a + "px, 0px, 0px)"
        }
        ,
        h.__posBarY = function(a) {
            var b = this;
            b._bar.style.webkitTransform = "translate3d(0px, " + a + "px, 0px)"
        }
        ,
        h._slipBar = function() {
            var a = this
              , b = a._about_bar * (a.xy + a.up_range);
            0 >= b ? b = 0 : b >= a._width_cut_barSize && (b = Math.round(a._width_cut_barSize)),
            a._showBar(),
            a._posBar(b)
        }
        ,
        h._slipBarPerfect = function() {
            var a = this
              , b = a._about_bar * (a.xy + a._bar_upRange);
            if (a._bar.style[a.width_or_height] = a._bar_size + "px",
            0 > b) {
                var c = a._bar_size + 3 * b;
                a._bar.style[a.width_or_height] = Math.round(Math.max(c, 5)) + "px",
                b = 0
            } else if (b >= a._width_cut_barSize) {
                var c = a._bar_size - 3 * (b - a._width_cut_barSize);
                5 > c && (c = 5),
                a._bar.style[a.width_or_height] = Math.round(c) + "px",
                b = Math.round(a._width_cut_barSize + a._bar_size - c)
            }
            a._showBar(),
            a._posBar(b)
        }
        ,
        h._slipBarTime = function(a) {
            this._bar.style.webkitTransitionDuration = "" + a + "ms",
            this._slipBar()
        }
        ,
        h._stop = function() {
            var a = this
              , b = a._real();
            a._pos(b),
            a.no_bar || (a._bar.style.webkitTransitionDuration = "0",
            a._posBar(a._about_bar * b))
        }
        ,
        h._stopPerfect = function() {
            clearTimeout(this._aniTime),
            this._animating = !1
        }
        ,
        h._realX = function() {
            var a = getComputedStyle(this.core, null ).webkitTransform.replace(/[^0-9-.,]/g, "").split(",");
            return 1 * a[4]
        }
        ,
        h._realY = function() {
            var a = getComputedStyle(this.core, null ).webkitTransform.replace(/[^0-9-.,]/g, "").split(",");
            return 1 * a[5]
        }
        ,
        h._countBarSize = function() {
            this._bar_size = Math.round(Math.max(this.parent_wide_high * this.parent_wide_high / this.wide_high, 5))
        }
        ,
        h._setBarSize = function() {
            this._bar.style[this.width_or_height] = this._bar_size + "px"
        }
        ,
        h._countAboutBar = function() {
            this._about_bar = (this.parent_wide_high - 4 - (this.parent_wide_high - 4) * this.parent_wide_high / this.wide_high) / this.width_cut_coreWidth
        }
        ,
        h._countWidthCutBarSize = function() {
            this._width_cut_barSize = this.parent_wide_high - 4 - this._bar_size
        }
        ,
        h.refresh = function(a, b) {
            var c = this;
            c._refreshCommon(a, b),
            c.no_bar || (c.coreWidth_cut_width <= 0 ? (c._bar_shell_opacity = 0,
            c._showBar = function() {}
            ) : (c._showBar = c._showBarStorage || c._showBar,
            c._countAboutBar(),
            c._countBarSize(),
            c._setBarSize(),
            c._countWidthCutBarSize())),
            c._rebound(0)
        }
        ,
        h._posTimePerfect = function(a, b) {
            var c = this;
            c._steps.push({
                x: a,
                time: b || 0
            }),
            c._startAni()
        }
        ,
        h.pos = function(a, b) {
            var c = this;
            if (c.xy != a) {
                var d = b || 0;
                c._posTime(a, d),
                c.perfect || 0 != d || (c._showBar(),
                setTimeout(function() {
                    c._hideBar()
                }, 100)),
                c.no_bar || c._slipBarTime(d)
            }
        }
        ,
        h._startAni = function() {
            var a, b, c, d = this, e = d.xy, f = Date.now();
            if (!d._animating) {
                if (!d._steps.length)
                    return d._rebound(),
                    void 0;
                a = d._steps.shift(),
                a.x == e && (a.time = 0),
                d._animating = !0,
                c = function() {
                    var g, h = Date.now();
                    return h >= f + a.time ? (d._pos(a.x),
                    d._animating = !1,
                    d._startAni(),
                    void 0) : (h = (h - f) / a.time - 1,
                    b = Math.sqrt(1 - h * h),
                    g = (a.x - e) * b + e,
                    d._pos(g),
                    d._animating && (d._slipBar(),
                    d._aniTime = setTimeout(c, 1)),
                    void 0)
                }
                ,
                c()
            }
        }
        ,
        h._momentum = function(a, b, c, d, e) {
            var f = .001
              , g = Math.abs(a) / b
              , h = g * g / (2 * f)
              , i = 0
              , j = 0;
            return a > 0 && h > c ? (j = e / (6 / (h / g * f)),
            c += j,
            g = g * c / h,
            h = c) : 0 > a && h > d && (j = e / (6 / (h / g * f)),
            d += j,
            g = g * d / h,
            h = d),
            h *= 0 > a ? -1 : 1,
            i = g / f,
            {
                dist: h,
                time: i
            }
        }
        ,
        h._showBar = function() {
            var a = this;
            a._bar_show_ing || (a._bar_shell.style.opacity = "1",
            a._bar_shell.style.webkitTransitionDelay = "0ms",
            a._bar_shell.style.webkitTransitionDuration = "0ms",
            a._bar_show_ing = !0)
        }
        ,
        h._hideBar = function() {
            var a = this;
            a._bar_shell.style.opacity = "0",
            a._bar_shell.style.webkitTransitionDelay = "300ms",
            a._bar_shell.style.webkitTransitionDuration = "300ms",
            a._bar.style.webkitTransitionDuration = "0ms",
            a._bar_show_ing = !1
        }
        ,
        c.exports = d
    }(window, document)
}),
define("dist/app/index/module/push_popup", [], function() {
    function a() {
        b.style.display = "none"
    }
    if (document.getElementById("pushPopup")) {
        var b = ai.i("pushPopup");
        $(".push_close").each(function() {
            $(this).click(function() {
                a()
            })
        })
    }
}),
define("dist/app/index/module/guide_mask", ["dist/cmdmodule/have_cookie", "dist/cmdmodule/ucb"], function(a) {
    var b = a("dist/cmdmodule/have_cookie")
      , c = b.init({
        cookieKey: "isNewUser",
        expires: 60,
        dom: $("#popupMain"),
        domain: ".fpwap.com",
        fun: function() {
            $("#popLayer").show(),
            $("#popupMain").show()
        }
    });
    c || ($(".popupShow").each(function() {
        $(this).tap(function() {
            setTimeout(function() {
                $(".updateGuide").hide(),
                $(".updateSubmit").show()
            }, 2e3)
        })
    }),
    $("#submitInput[type=text]").focus(function() {
        $("#popupMain").css("top", "0px"),
        $(".update-guide-box").css({
            top: "50px"
        })
    }),
    $("#submitInput[type=text]").blur(function() {
        setTimeout(function() {
            $(".update-guide-box").css({
                top: "25%"
            })
        }, 100)
    }),
    $("#popClose").each(function() {
        $(this).tap(function() {
            $("#popLayer").hide(),
            $("#popupMain").hide()
        })
    }))
}),
define("dist/cmdmodule/have_cookie", ["dist/cmdmodule/ucb"], function(a, b, c) {
    var d = a("dist/cmdmodule/ucb")
      , e = {
        init: function(a) {
            var b = this;
            b.cookieKey = a.cookieKey,
            b.expires = 864e5 * a.expires,
            b.domain = a.domain || ".fpwap.com",
            b.fun = a.fun,
            b.delayTime = a.delayTime || 0;
            var c = d.Cookie.get(b.cookieKey);
            return null  == c ? (b.fun(),
            d.Cookie.set(b.cookieKey, "true", {
                path: "/",
                domain: b.domain,
                expires: b.expires
            }),
            !1) : !0
        }
    };
    c.exports = e
}),
define("dist/cmdmodule/scroll_notice", ["dist/cmdmodule/ucb"], function(a) {
    $(".scrool-notice").each(function() {
        var b = a("dist/cmdmodule/ucb")
          , c = ".fpwap.com"
          , d = b.Cookie.get("notice_activated");
        null  == d ? $(this).show() : $(this).hide(),
        $(this).click(function() {
            b.Cookie.set("notice_activated", "true", {
                path: "/",
                domain: c,
                expires: 2592e5
            })
        })
    });
    var b = ai.i("autoScroll");
    if (void 0 != b && null  != b) {
        slide = function() {
            function a() {
                c.style.webkitTransitionDuration = "0",
                c.style.top = "0px",
                g++;
                for (var e = 0; e < d.length; e++)
                    d[e].innerHTML = h[b(e + g)];
                c.removeEventListener("webkitTransitionEnd", a, !1)
            }
            function b(a) {
                return a % e
            }
            for (var c = document.querySelector("#autoScroll ul"), d = c.querySelectorAll("li"), e = d.length, f = null , g = 0, h = [], i = 0; e > i; i++)
                h[i] = d[i].innerHTML;
            f = setInterval(function() {
                c.style.webkitTransitionDuration = "500ms",
                c.style.top = "-38px",
                c.addEventListener("webkitTransitionEnd", a, !1)
            }, 2e3)
        }
        ;
        var c = $("#autoScroll li").length;
        c > 1 && slide()
    }
}),
define("dist/app/index/module/dele_game_cookie", ["dist/cmdmodule/ucb"], function(a) {
    var b = a("dist/cmdmodule/ucb");
    console.log(b.Cookie.getRaw("uzs"));
    var c = {
        init: function() {
            this._getDom(),
            this._bind()
        },
        _getDom: function() {
            this.deleGameCookie = document.getElementById("clsoePagegame")
        },
        _bind: function() {
            ai.tap(this.deleGameCookie, function() {
                b.Cookie.setRaw("uzs", "", {
                    path: "/",
                    domain: ".fpwap.com",
                    expires: -1
                })
            })
        }
    };
    c.init()
});
