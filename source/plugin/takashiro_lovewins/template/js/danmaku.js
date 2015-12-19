(function($){
	$.fn.danmaku = function(action, options){
		var config = {
			'texts' : ['都没人发弹幕QAQ', '好凄凉啊~~~~~'],
			'lineNum' : 3
		};
		$.extend(config, options);

		var area = $(this);
		var danmaku_area = area.children('.danmaku_area');
		if(danmaku_area.length == 0){
			var area_position = area.css('position');
			if (area_position != 'absolute' && area_position != 'relative')
				area.css('position', 'relative');

			danmaku_area = $('<div></div>');
			danmaku_area.addClass('danmaku_area');
			danmaku_area.appendTo(area);
		}

		function move_text(span, lineNum){
			var area = span.parent();

			var line_height = span.outerHeight();
			var top_offset = Math.floor(Math.random() * lineNum);

			span.css({
				'left' : area.width(),
				'top' : area.height() / 3 + top_offset * line_height,
				'display' : 'inline'
			});

			span.animate({'left' : '-' + span.outerWidth()}, 5000, 'linear', function(){
				span.remove();
			});
		}

		function add_text(content){
			var text = $('<span></span>');
			text.html(content);
			danmaku_area.append(text);
			move_text(text, config.lineNum);
		}

		if(action == 'config'){
			function danmaku(){
				var id = Math.floor(Math.random() * config.texts.length);
				var content = config.texts.splice(id, 1);
				add_text(content);
			}
			danmaku();
			setInterval(danmaku, 3000);
		}else if(action == 'add'){
			add_text(options);
		}
	}
})(jQuery);
