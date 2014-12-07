//导航固定
(function($){
	$(function(){
		var logo = $('.hd_logo').find('img:first');
		$(window).scroll(function(){
			var angle = Math.floor($(window).scrollTop() / 3);
			logo.css({'transform':'rotate(' + angle + 'deg)'});
		});

		var header = $('.header');
		var fillingbox = $('<div></div>');
		header.after(fillingbox);
		$(window).scroll(function(){
			var header = $('.header');
			var fillingbox = header.next();
			if($(window).scrollTop() == 0){
				header.removeClass('headboxfixed');
				fillingbox.hide();
			}else{
				header.addClass('headboxfixed');
				var fillingheight = header.outerHeight() - $(window).scrollTop();
				if(fillingheight > 0){
					fillingbox.show();
					fillingbox.css('height', fillingheight);
				}
			}
		});
	});
})(jQuery);
