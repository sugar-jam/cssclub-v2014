(function($){
	var mask_colors = ['#FF6E04', '#2B52B6', '#319113'];
	$(function(){
		$('.dxb_bc .xld .cl').each(function(){
			var mask = $(this).children('.mask');
			mask.css('background', mask_colors[$(this).index()]);
		});
		$('.dxb_bc .xld .cl .mask').css('opacity', 0.8);
		$('.dxb_bc .xld .cl').find('.mask, a dt, a dd').fadeIn();

		$('.dxb_bc .xld .cl').mouseenter(function(){
			var mask = $(this).children('.mask');
			mask.fadeOut();

			var title = $(this).find('dt');
			var content = $(this).find('dd');
			title.animate({'opacity':0, 'top':-title.outerHeight()}, 300);
			content.animate({'opacity':0, 'bottom':-content.outerHeight()}, 300);
		});

		$('.dxb_bc .xld .cl').mouseleave(function(){
			var mask = $(this).children('.mask');
			mask.fadeIn();

			var title = $(this).find('dt');
			var content = $(this).find('dd');
			title.animate({'opacity':1, 'top':0}, 300);
			content.animate({'opacity':1, 'bottom':0}, 300);
		});
	});
})(jQuery);
