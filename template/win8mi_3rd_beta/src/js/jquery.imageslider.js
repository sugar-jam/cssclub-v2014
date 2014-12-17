(function($){
	var hideByLeft = function(frame, is_animated){
		var slider = frame.parent();
		frame.css('z-index', 0);

		var data = {
			'width': slider.width() * 0.2,
			'height': slider.height() * 0.2,
			'opacity': 0,
			'top': slider.height() * 0.4,
			'left': -10
		};

		if(is_animated){
			frame.animate(data, 500, function(){
				$(this).hide();
			});
		}else{
			frame.css(data);
		}
	};

	var hideByRight = function(frame, is_animated){
		var slider = frame.parent();
		frame.css('z-index', 0);

		var data = {
			'width': slider.width() * 0.2,
			'height': slider.height() * 0.2,
			'opacity': 0,
			'top': slider.height() * 0.4,
			'left': slider.width() * 0.8 + 10
		};

		if(is_animated){
			frame.animate(data, 500, function(){
				$(this).hide();
			});
		}else{
			frame.css(data);
		}
	};

	var moveToLeft = function(frame){
		frame.show();
		var slider = frame.parent();
		frame.css('z-index', 1);
		frame.animate({
			'width': slider.width() * 0.6 * 0.75,
			'height': slider.height() * 0.75,
			'top': slider.height() * 0.125,
			'left': 0,
			'opacity': 1
		}, 500);
	};

	var moveToCenter = function(frame){
		frame.show();
		var slider = frame.parent();

		slider.children('.current').removeClass('current');
		frame.addClass('current');

		frame.css('z-index', 2);
		frame.animate({
			'width': slider.width() * 0.6,
			'height': slider.height(),
			'top': 0,
			'left': slider.width() * 0.2,
			'opacity': 1
		}, 500);
	};

	var moveToRight = function(frame){
		frame.show();
		var slider = frame.parent();
		frame.css('z-index', 1);
		frame.animate({
			'width': slider.width() * 0.6 * 0.75,
			'height': slider.height() * 0.75,
			'top': slider.height() * 0.125,
			'left': slider.width() * 0.55,
			'opacity': 1
		}, 500);
	};

	$.fn.imageslider = function(action){
		var slider = $(this);
		var frames = slider.children('.frames').children();

		if(action == undefined){
			frames.hide();

			var current = frames.eq(1);
			moveToCenter(current);
			var prev = current.prev();
			moveToLeft(prev);
			var next = current.next();
			moveToRight(next);

			var left_button = slider.children('.left_button');
			left_button.css('top', (slider.height() - left_button.height()) / 2);
			left_button.click(function(){
				slider.imageslider('moveLeft');
			});
			left_button.fadeIn();

			var right_button = slider.children('.right_button');
			right_button.css('top', (slider.height() - right_button.height()) / 2);
			right_button.click(function(){
				slider.imageslider('moveRight');
			});
			right_button.fadeIn();

		}else{
			var current = frames.filter('.current');
			var current_index = frames.index(current);
			var prev_index = current_index - 1;
			if(prev_index < 0){
				prev_index = frames.length - 1;
			}
			var prev = frames.eq(prev_index);
			var next_index = (current_index + 1) % frames.length;
			var next = frames.eq(next_index);
			if(action == 'moveRight'){
				hideByLeft(prev, true);
				moveToLeft(current);
				moveToCenter(next);

				var new_frame = frames.eq((next_index + 1) % frames.length);
				if(new_frame.length == 0){
					new_frame = frames.eq(0);
				}
				hideByRight(new_frame, false);
				moveToRight(new_frame);
			}else{
				hideByRight(next, true);
				moveToRight(current);
				moveToCenter(prev);

				var new_index = prev_index - 1;
				if(new_index < 0){
					new_index = frames.length - 1;
				}
				var new_frame = frames.eq(new_index);
				if(new_frame.length == 0){
					new_frame = frames.eq(frames.length - 1);
				}
				hideByLeft(new_frame, false);
				moveToLeft(new_frame);
			}
		}
	};

	$(function(){
		$('.imageslider').imageslider();
	});
})(jQuery);
