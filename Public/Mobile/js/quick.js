/**
 * 公用导航搜索和快捷导航
 * 
 * @dependencies 依赖zepto.min.js
 */
$(document).ready(function() {
	var oSoClear = ai.i("js-so-ico-clear"),
		oSoPopText = ai.i("js-so-pop-text"),
		oSoPopBtnCancel = ai.i("js-so-pop-btn-cancel"),
		oSoPopGuessSlip = ai.i("js-so-pop-guess-slip"),
		oSoPop= ai.i("js-so-pop"),
		oSoPopBtn = ai.i("js-so-pop-btn"),
		oSoPopGuess = ai.i("js-so-pop-guess"),
		oSoPopPromote = ai.i("js-so-pop-promote"),
		oSoPopFormShell = ai.i("js-so-pop-form-shell");
	
	$("#js-ico-search").click(function() {
		if ("undefined" == typeof browserType) {
			browserType = "";
		}
		if (browserType == "0") {
			$("#js-so-pop").show();
			$("#js-so-pop-text").val("");
			$("#js-so-pop-text").focus();
		} else {
			window.location = "/search.html";
		}
	});
	$("#js-so-pop-btn-cancel").click(function(event) {
		$(".so-pop-guess-slip").html("");
		$(".g-search").hide();
	});
	$("#js-so-ico-clear").click(function(event) {
		$("#js-so-pop-guess").hide();
		$("#js-so-pop-btn").hide();
		$("#js-so-pop-btn-cancel").show();
		$("#js-so-pop-promote").show();
		$("#js-so-pop-text").val("");
		$("#js-so-pop-text").focus();
	});
	$("#js-so-pop-text").on("input", function(o) {
		textChange(o);
	}, false);

	ai.touchMovePreventDefault(oSoPop);
	ai.touchMovePreventDefault(oSoPopFormShell);
	oSoPopFormShell.addEventListener('touchstart', function(e){
		e.stopPropagation(); 
	});
	oSoPopGuess.addEventListener('touchstart', function(e){
		e.stopPropagation(); 
	});
	oSoPopGuessSlip.addEventListener('touchstart',function(e){
		e.preventDefault();
		oSoPopBtn.focus();
	});
	oSoPopPromote.addEventListener('touchstart', function(e){
		e.stopPropagation(); 
	});

});

//首页的搜索
$(document).ready(function() {
	btnStates();

	$(".formShell").click(function(event) {
		event.stopImmediatePropagation();
	});

	// touch版的首页中间的搜索
	// IE
	if ($.browser.msie) {
		$("#name").on("onpropertychange", function(o) {
			inputKeyGetVal();
		}, false);
		// 非IE
	} else {
		$("#name").on("input", function(o) {
			inputKeyGetVal();
		}, false);
	}
    $("#name").on("focus", function(o) {
            inputKeyGetVal();
        }, false);
        
	// 清除文本框内容
	$(".closeList").click(function(event) {
		event.stopImmediatePropagation();
		$(this).hide();
		$(".inputKey").val("");
		$(".popGuess").hide();
		$("#searchMask").hide();
		btnStates();
	});

	$("#searchMask").click(function(){
		$("#searchMask").hide();
	});	
	
	$("#searchMask").on("touchmove",function(e){
		e.preventDefault();
	})	
	
	$("#searchMask li").click(function(event){
		event.stopImmediatePropagation();
		$(".inputKey").val($(this).html());
		$("#searchMask").hide();
	})
	
	$("#searchMask li").click(function(event){
		event.stopImmediatePropagation();
		$(".inputKey").val($(this).html());
		$("#searchMask").hide();
	})
});

function textChange(o) {
	var num = o.target.value.getBytes();
	if (num > 0) {
		$("#js-so-pop-guess-slip").html("");
		$("#js-so-pop-btn-cancel").hide();
		$("#js-so-pop-btn").show();
	} else {
		$("#js-so-pop-btn").hide();
		$("#js-so-pop-btn-cancel").show();
	}

	if (num > 2) {
		try {
			$.ajax({
				url : '/matching.html?requestType=ajax&term=' + o.target.value,
				type : 'get',
				dataType : "json",
				success : function(result) {
					for ( var i in result) {
						var id = 'sopopchange' + i;
						$("#js-so-pop-guess-slip").append('<li id='+id+'>' + result[i] + '</li>');
						var isTouch = 'ontouchstart' in window;
						if(isTouch){
							//在此使用ai库绑定 选择下拉内容
							ai.touchClick(document.getElementById(id), function() {
								$("#js-so-pop-text").val($(this).text());
								$("#js-so-pop-guess").hide();
								$("#js-so-pop-promote").show();
							});
						}
						else{
							//一般的onclick
							document.getElementById(id).addEventListener('click',function(e){
								$("#js-so-pop-text").val($(this).text());
								$("#js-so-pop-guess").hide();
								$("#js-so-pop-promote").show();
							});
						}
					}
					if (result != null) {
						$("#js-so-pop-promote").hide();
						$("#js-so-pop-guess").show();
					}
				}
			});
		} catch (e) {
			$("#js-so-pop-guess").hide();
		}
	}
	return false;
}

function setNameVal(dom){
	//选择下拉内容
	if(dom!=null){
		$(".inputKey").val(dom.innerHTML);
		$("#searchMask").hide();
		$(".so-pop-guess-slip").html('');
		$(".so-pop-guess").hide();
	}
}

// touch版首页的搜索
function inputKeyGetVal() {
	// 先清空
	$(".so-pop-guess-slip").html('');
	$(".so-pop-guess").hide();

	// 输入内容长度不合法， 就无需请求
	var num = $(".inputKey").val().length;
	if (num > 1 && num < 32) {
		try {
			$.ajax({
				url : '/matching.html?requestType=ajax&term='
						+ $(".inputKey").val() + '&random=' + Math.random(),
				type : 'get',
				dataType : "json",
				success : function(result) {
					// 先清空
					$(".so-pop-guess-slip").html('');
					$(".so-pop-guess").hide();
					for (var i in result) {
						var id = 'liId' + i;
						$(".so-pop-guess-slip").append('<li id="' + id + '" onclick=setNameVal("'+id+'") >' + result[i] + '</li>');
						var isInputTouch = 'ontouchstart' in window;
						if(isInputTouch){
							//在此使用ai库绑定 选择下拉内容
							ai.touchClick(document.getElementById(id), function() {
								$("#name").val($(this).text());
								$(".so-pop-guess-slip").html('');
								$(".so-pop-guess").hide();
							});
						}
						else{
							//一般的onclick
							document.getElementById(id).addEventListener('click',function(e){
								$("#name").val($(this).text());
								$(".so-pop-guess-slip").html('');
								$(".so-pop-guess").hide();
							});
						}
					}
					if (result != null) {
						$(".so-pop-guess").show();
						$("#searchMask").show();
					}
				}
			});
		} catch (e) {
			$(".so-pop-guess").hide();
		}
	} else {
		$(".so-pop-guess-slip").html('');
		$(".so-pop-guess").hide();
	}

	$(".popGuess").show();
	$(".closeList").show();
	btnStates();
}

/*
 * 头部搜索一系列动作
 */
function btnStates() {
	if ($(".inputKey").val() != "") {
		$(".btnSeach").show();
		$(".btnCancel").hide();
	} else {
		$(".btnSeach").hide();
		$(".btnCancel").show();
	}
}

String.prototype.getBytes = function() {
	var value = this.match(/[^\x00-\xff]/ig);
	return this.length + (value == null ? 0 : value.length);
}