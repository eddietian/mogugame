//样式
$('.rank-kf-tab li').hover(function() {
	$(this).find('.kf-tab-box').addClass('kf-tab-hover');
},function() {
	$(this).find('.kf-tab-box').removeClass('kf-tab-hover')
});


//开服
var ul = $('.rank-kf-tab ul');
function _switch(dir, callback){
	var div = $('div.rank-kf-tab');
	var px = parseInt(div.css('margin-left').replace(/[^0-9-]/g, ''));
	var count = div.find('ul').length;
	var width = $('div.rank-kf-wrap').width();
	var max_px = width * count - width;
	var dis;
	
	if(dir == 'left'){
		dis = px == 0 ? 0 : px + width;
	}else{
		dis = px <= -max_px ? -max_px : px - width;
	}
	
	var current = Math.abs(Math.min(dis, 0) / width) +1;
	current = Math.min(count, current);
	div.animate({'margin-left': dis}, 200, callback);
	console.log(current,count);
	$('#current_game_page').html(current +'/'+ count);
}

_switch('left');
