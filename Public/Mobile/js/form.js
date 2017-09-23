$(document).ready(function(){
	$(".inputClose").focus(function(){
		$(this).siblings(".input-close").show();
	})
	$(".input-close").click(function(){
		$(".inputClose").val("");
		$(this).hide();
	})
});