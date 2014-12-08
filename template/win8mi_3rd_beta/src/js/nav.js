//导航固定
(function($){
	$(function(){
		var logo = $('.hd_logo').find('img');
		$(window).scroll(function(){
			var angle = Math.floor($(window).scrollTop() / 3);
			logo.css({'rotate':angle});
		});

		$('.nav a').mouseenter(function(){
			var logo = $('.hd_logo').find('img');
			if(logo.data('is_rotating')){
				return true;
			}
			logo.data('is_rotating', true);

			var angle = Math.floor($(window).scrollTop() / 3);
			logo.animate({'rotate': angle + 359}, 500, function(){
				$(this).css('rotate', angle);
				logo.data('is_rotating', false);
			});
		});

		var header = $('.header');
		var filling_box = $('<div></div>');
		header.after(filling_box);
		var header_height = header.outerHeight();
		$(window).scroll(function(){
			var header = $('.header');
			var filling_box = header.next();
			var scroll_top = $(window).scrollTop();
			if(scroll_top == 0){
				header.removeClass('headboxfixed');
			}else{
				header.addClass('headboxfixed');
				var filling_height = 40 - scroll_top;
				if(filling_height > 0){
					filling_box.css('height', filling_height);
				}
			}
		});
	});
})(jQuery);
