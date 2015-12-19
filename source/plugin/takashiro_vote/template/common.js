(function($){
	function scroll_timer(tile){
		var next_timeout = Math.round(Math.random() * 5) + 3;
		setTimeout(function(){
			var fields = tile.children();
			var current = parseInt(tile.data('current'), 10);
			current++;
			if(current >= fields.length)
				current = 0;
			tile.data('current', current);

			var offset_top = current * fields.eq(0).outerHeight();
			tile.animate({scrollTop: offset_top + 'px'}, 600, 'swing');
			scroll_timer(tile);
		}, next_timeout * 1000);
	}

	$(function(){
		$('.votefield .value').click(function(){
			var data = {
				votedfield : $(this).data('field'),
				cid : $(this).parent().parent().data('cid')
			};
			var value = $(this);
			$.get('plugin.php?id=takashiro_vote:main&action=vote', data, function(text){
				var result = parseInt(text, 10);

				if(result > 0){
					value.text(parseInt(value.text(), 10) + result);
					var icon = $('<div></div>');
					icon.addClass('redheart_icon');
					icon.appendTo($('body'));
					icon.css({top: value.offset().top, left: value.offset().left + value.outerWidth() - icon.width()});
					icon.animate({width: '+=8px', height: '+=8px', top: '-=32px', left: '-=4px', opacity: 0}, 600, 'linear', function(){
						icon.remove();
					});
				}else{
					if(result == -1){
						showError('已经过了投票时间/(ㄒoㄒ)/~~');
					}else if(result == -2){
						showError('您今天已经投过票了，明天再来吧！');
					}else if(result == -3){
						showError('每天只能投10票哦，明天再来吧！');
					}else if(result == -4){
						showError('还未到投票开放时间哦！');
					}else{
						showError('好像是网络君感觉不太好，让他瘫痪一会儿……');
					}
				}
			}, 'text');
		});

		$('.votefield button.vote').click(function(){
			var field = $(this).parent().parent();
			var value = field.children('.value');
			value.click();
		});

		$('ul.candidate .avatar').each(function(){
			var img = $(this).find('img');
			var top = img.data('css-top');
			if(top){
				img.css('top', top);
			}
		});

		$('ul.candidate .tile').each(function(){
			$(this).data('current', 0);
			scroll_timer($(this));
		});

		$('form.link_autosubmit').each(function(){
			var form = $(this);
			$(this).find('a').click(function(){
				var name = $(this).data('name');
				var value = $(this).data('value');
				form.find('input[name="' + name + '"]').val(value);
				form.submit();
			});
		});
	});
})(jQuery);
