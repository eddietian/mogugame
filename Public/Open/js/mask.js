/**
 * 蒙层
 */
(function($){
	$.Mask = {
		show: function(str) {
			var width = $("body").width();
			var height = $("body").height();
			var wb = $(window).height();
			var divMask = '<div id="mask" style="position: absolute; background-color: #999; width: 100%; height: 100%; z-index: 99999; top: 0; left: 0; opacity: 0.3; text-align: center;"></div>';
			var divTxt = '<div id="maskTxt" style="position: absolute; background-color: white; z-index: 100000; padding: 20px; border: 1px solid #ccc; border-radius: 5px; box-shadow: 0 3px 3px gray; display: table-cell; vertical-align: middle;"><i class="icon-spinner icon-spin icon-2x pull-left"></i><span style="padding-top: 4px; display: inline-block;">' + str + '</span></div>';
			$("body").append(divMask).append(divTxt);
			var divTxtWidth = $("#maskTxt").outerWidth();
			var divTxtHeight = $("#maskTxt").outerHeight();
			var left = (width - divTxtWidth) / 2;
			var top = (height - divTxtHeight) / 2;
			if (wb > height) {
				height = wb;
			} else {
				height += 62;
			}
			$("#mask").css("height", height + "px");
			$("#maskTxt").css({"left": left + "px", "top": top + "px"});
		},
		hidden: function() {
			$("#mask").remove();
			$("#maskTxt").remove();
		}
	};
}(jQuery));