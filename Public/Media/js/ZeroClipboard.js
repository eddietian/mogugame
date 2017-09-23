var ZeroClipboard = {
	version: "1.0.7",
	clients: {},
	moviePath: "./ZeroClipboard.swf?rand=" + Math.random(),
	nextId: 1,
	$: function(a) {
		if(typeof a == "string") a = document.getElementById(a);
		if(!a.addClass) {
			a.hide = function() {
				this.style.display = "none"
			};
			a.show = function() {
				this.style.display = ""
			};
			a.addClass = function(b) {
				this.removeClass(b);
				this.className += " " + b
			};
			a.removeClass = function(b) {
				for(var c = this.className.split(/\s+/), d = -1, e = 0; e < c.length; e++) if(c[e] == b) {
					d = e;
					e = c.length
				}
				if(d > -1) {
					c.splice(d, 1);
					this.className = c.join(" ")
				}
				return this
			};
			a.hasClass = function(b) {
				return !!this.className.match(new RegExp("\\s*" + b + "\\s*"))
			}
		}
		return a
	},
	setMoviePath: function(a) {
		this.moviePath = a
	},
	dispatch: function(a, b, c) {
		(a = this.clients[a]) && a.receiveEvent(b, c)
	},
	register: function(a, b) {
		this.clients[a] = b
	},
	getDOMObjectPosition: function(a, b) {
		for(var c = {
			left: 0,
			top: 0,
			width: a.width ? a.width : a.offsetWidth,
			height: a.height ? a.height : a.offsetHeight
		}; a && a != b;) {
			c.left += a.offsetLeft;
			c.top += a.offsetTop;
			a = a.offsetParent
		}
		return c
	},
	Client: function(a) {
		this.handlers = {};
		this.id = ZeroClipboard.nextId++;
		this.movieId = "ZeroClipboardMovie_" + this.id;
		ZeroClipboard.register(this.id, this);
		a && this.glue(a)
	}
};
ZeroClipboard.Client.prototype = {
	id: 0,
	ready: false,
	movie: null,
	clipText: "",
	handCursorEnabled: true,
	cssEffects: true,
	handlers: null,
	glue: function(a, b, c) {
		this.domElement = ZeroClipboard.$(a);
		a = 99;
		if(this.domElement.style.zIndex) a = parseInt(this.domElement.style.zIndex, 10) + 1;
		if(typeof b == "string") b = ZeroClipboard.$(b);
		else if(typeof b == "undefined") b = document.getElementsByTagName("body")[0];
		var d = ZeroClipboard.getDOMObjectPosition(this.domElement, b);
		this.div = document.createElement("div");
		var e = this.div.style;
		e.position = "absolute";
		e.left = "" + d.left + "px";
		e.top = "" + d.top + "px";
		e.width = "" + d.width + "px";
		e.height = "" + d.height + "px";
		e.zIndex = a;
		if(typeof c == "object") for(addedStyle in c) e[addedStyle] = c[addedStyle];
		b.appendChild(this.div);
		this.div.innerHTML = this.getHTML(d.width, d.height)
	},
	getHTML: function(a, b) {
		var c = "",
			d = "id=" + this.id + "&width=" + a + "&height=" + b;
		if(navigator.userAgent.match(/MSIE/)) {
			var e = location.href.match(/^https/i) ? "https://" : "http://";
			c += '<object style="position:absolute;top:0;left:0px;z-index:2" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="' + e + 'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="' + a + '" height="' + b + '" id="' + this.movieId + '" align="middle"><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="false" /><param name="movie" value="' + ZeroClipboard.moviePath + '" /><param name="loop" value="false" /><param name="menu" value="false" /><param name="quality" value="best" /><param name="bgcolor" value="#ffffff" /><param name="flashvars" value="' + d + '"/><param name="wmode" value="transparent"/></object>'
		} else c += '<embed style="position:absolute;top:0;left:0px;z-index:2" id="' + this.movieId + '" src="' + ZeroClipboard.moviePath + '" loop="false" menu="false" quality="best" bgcolor="#ffffff" width="' + a + '" height="' + b + '" name="' + this.movieId + '" align="middle" allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="' + d + '" wmode="transparent" />';
		return c
	},
	hide: function() {
		if(this.div) this.div.style.left = "-2000px"
	},
	show: function() {
		this.reposition()
	},
	destroy: function() {
		if(this.domElement && this.div) {
			this.hide();
			this.div.innerHTML = "";
			var a = document.getElementsByTagName("body")[0];
			try {
				a.removeChild(this.div)
			} catch(b) {}
			this.div = this.domElement = null
		}
	},
	reposition: function(a) {
		if(a)(this.domElement = ZeroClipboard.$(a)) || this.hide();
		if(this.domElement && this.div) {
			a = ZeroClipboard.getDOMObjectPosition(this.domElement);
			var b = this.div.style;
			b.left = "" + a.left + "px";
			b.top = "" + a.top + "px"
		}
	},
	setText: function(a) {
		this.clipText = a;
		this.ready && this.movie.setText(a)
	},
	addEventListener: function(a, b) {
		a = a.toString().toLowerCase().replace(/^on/, "");
		this.handlers[a] || (this.handlers[a] = []);
		this.handlers[a].push(b)
	},
	setHandCursor: function(a) {
		this.handCursorEnabled = a;
		this.ready && this.movie.setHandCursor(a)
	},
	setCSSEffects: function(a) {
		this.cssEffects = !! a
	},
	receiveEvent: function(a, b) {
		a = a.toString().toLowerCase().replace(/^on/, "");
		switch(a) {
		case "load":
			this.movie = document.getElementById(this.movieId);
			if(!this.movie) {
				var c = this;
				setTimeout(function() {
					c.receiveEvent("load", null)
				}, 1);
				return
			}
			if(!this.ready && navigator.userAgent.match(/Firefox/) && navigator.userAgent.match(/Windows/)) {
				c = this;
				setTimeout(function() {
					c.receiveEvent("load", null)
				}, 100);
				this.ready = true;
				return
			}
			this.ready = true;
			this.movie.setText(this.clipText);
			this.movie.setHandCursor(this.handCursorEnabled);
			break;
		case "mouseover":
			if(this.domElement && this.cssEffects) {
				this.domElement.addClass("hover");
				this.recoverActive && this.domElement.addClass("active")
			}
			break;
		case "mouseout":
			if(this.domElement && this.cssEffects) {
				this.recoverActive = false;
				if(this.domElement.hasClass("active")) {
					this.domElement.removeClass("active");
					this.recoverActive = true
				}
				this.domElement.removeClass("hover")
			}
			break;
		case "mousedown":
			this.domElement && this.cssEffects && this.domElement.addClass("active");
			break;
		case "mouseup":
			if(this.domElement && this.cssEffects) {
				this.domElement.removeClass("active");
				this.recoverActive = false
			}
			break
		}
		if(this.handlers[a]) for(var d = 0, e = this.handlers[a].length; d < e; d++) {
			var f = this.handlers[a][d];
			if(typeof f == "function") f(this, b);
			else if(typeof f == "object" && f.length == 2) f[0][f[1]](this, b);
			else typeof f == "string" && window[f](this, b)
		}
	}
};