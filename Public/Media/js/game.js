function loadIconError(el){
        var src = $(el).attr("data-original-src");
        if (!src) {
            src = $(el).attr("src").replace(/_(\d)\d0x(\1)\d0/i,"");
        }
        var src404 = $(el).attr("data-404-src");
        if (!src404) {
            src404 = prefix + "css/images/404-pic.png";
        }
        if (src) {
            var _image = new window.Image();
            _image.src = src;
            _image.onload = function(){
                $(el).attr("src", src);
            };
            _image.onerror = function(){
                $(el).removeAttr("onerror").attr("src", src404);
            };
        }else{
            var _image = new window.Image();
            _image.src = src404;
            _image.onload = function(){
                $(el).attr("src", src404);
            };
            _image.onerror = function(){
                $(el).removeAttr("onerror").attr("src", src404);
            };
        }
    }
    
    function getImgNaturalDimensions(img, fn, fnErr){
        if (img.naturalWidth) {
            var nWidth = img.naturalWidth;
            var nHeight = img.naturalHeight;
            fn(nWidth, nHeight);
        } else {
            var _image = new window.Image();
            _image.onload = function(){
                if (typeof fn == "function") {
                    fn(_image.width, _image.height);
                }
            };
            _image.onerror = function(){
                if (typeof fnErr == "function") {
                    fnErr();
                }
            };
            _image.src = img.src;
        }
    }

    function resizeImage(el){
        var cssw = $(el).parent().width();
        var cssh = $(el).parent().height();
        (function(img, cssw, cssh){
            getImgNaturalDimensions(img, function(rw, rh){
                var ratio_w = cssw / rw;
                var ratio_h = cssh / rh;
                var ratio = 0, aw = 0, ah = 0;
                ratio = Math.max(ratio_w, ratio_h);
                aw = rw * ratio;
                ah = rh * ratio;
                var max_w = $(img).css("max-width");
                if (max_w != "none") {
                    max_w = parseFloat(max_w);
                    if (aw > max_w) {
                        aw = max_w;
                    }
                }
                var max_h = $(img).css("max-height");
                if (max_h != "none") {
                    max_h = parseFloat(max_h);
                    if (ah > max_h) {
                        ah = max_h;
                    }
                }

                var left = (aw - cssw) / 2;
                var top = (ah - cssh) / 2;
                var marginTop = -top + "px";
                var cssDisplay = "block";
                if ($(img).hasClass("imgfixhidden")) {
                    cssDisplay = "hidden";
                }
                $(img).css({
                    width: aw + "px",
                    height: ah + "px",
                    position: "absolute",
                    marginLeft: -left + "px",
                    marginTop: marginTop,
                    display: cssDisplay
                });
            });
        })(el, cssw, cssh);
    }
    