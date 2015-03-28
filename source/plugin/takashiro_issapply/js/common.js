(function($){
	$(function(){
		renderpage($('body'));
	});
})(jQuery);

function renderpage(range){
	(function($){
		range.find('.tabs').children(':not(:first)').hide();

		range.find('.tabs').each(function(){
			var button_list = $(this).prev();
			if(button_list.is('ul')){
				var tabs = $(this).children();

				button_list.find('a').click(function(){
					var li = $(this).parent();
					button_list.children().removeClass('a');
					li.addClass('a');
					var index = li.index();
					tabs.hide();
					tabs.eq(index).show();
					return false;
				});
			}
		});

		range.find('.editlist').editlist({
			'edit' : '',
			'delete' : '',
			'attr' : ['starttime', 'endtime', 'university', 'college', 'major', 'degree'],
			'buttons' : {'delete':'删除'}
		});

		range.find('input.datetime').each(function(){
			$(this).datetimepicker({
				lang : 'cn',
				i18n : {
					cn : {
						months : [
							'1月','2月','3月','4月',
							'5月','6月','7月','8月',
							'9月','10月','11月','12月',
						],
						dayOfWeek : [
							"日", "一", "二", "三",
							"四", "五", "六",
						]
					}
				},
				format : 'Y-m',
				dateOnly : true
			});
		});
	})(jQuery);
}
