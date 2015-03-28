
(function($){
	$.fn.editlist = function(options){
		var defaults = {
			'edit' : '',
			'delete' : '',
			'primarykey' : 'id',
			'noedit' : false,
			'attr' : [],
			'buttons' : {'edit':'编辑', 'delete':'删除'}
		};

		options = $.extend(defaults, options);

		options.ajax_edit = options.edit + (options.edit.indexOf('?') == -1 ? '?' : '&') + 'ajax=1';
		options.ajax_delete = options.delete + (options.delete.indexOf('?') == -1 ? '?' : '&') + 'ajax=1';

		var display_operations = function(operation_td){
			operation_td.html('');
			for(var i in options.buttons){
				var button = $('<button></button>');
				button.attr('type', 'button');
				button.attr('class', i);
				button.html(options.buttons[i]);
				operation_td.append(button);
			}
		}

		var operation_td = this.find('tbody tr:not(:last-child) td:last-child');
		display_operations(operation_td);

		this.on('click', '.add', function(e){
			var button = $(e.target);
			var new_tr = button.parent().parent();
			var empty_tr = new_tr.clone();

			empty_tr.find('input,select').val('');
			new_tr.parent().append(empty_tr);

			display_operations(new_tr.children('td:last-child'));
			renderpage(empty_tr);
		});

		this.on('click', '.edit', function(e){
			var button = $(e.target);
			var tr = button.parent().parent();
			location.href = options.edit + (options.edit.indexOf('?') == -1 ? '?' : '&') + options.primarykey + '=' + tr.attr('primaryvalue');
		});

		this.on('click', '.delete', function(e){
			var button = $(e.target);
			var tr = button.parent().parent();
			tr.remove();
		});
	}
})(jQuery);
