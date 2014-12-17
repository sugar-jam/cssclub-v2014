/*
 * JQuery CSS Rotate property using CSS3 Transformations
 * Copyright (c) 2011 Jakub Jankiewicz  <http://jcubic.pl>
 * licensed under the LGPL Version 3 license.
 * http://www.gnu.org/licenses/lgpl.html
 */
(function($) {
    $.cssHooks['rotate'] = {
		get: function(elem, computed, extra){
			var value = elem.style['transform'];
			if(value == undefined){
				value = elem.style['-webkit-transform'];
			}
		    return value != 'undefined' ? value.replace(/.*rotate\((.*)deg\).*/, '$1') : '';
		},
		set: function(elem, value){
		    value = parseInt(value);
			var transform = (value == 0) ? '' : 'rotate(' + (value % 360) + 'deg)';
			elem.style['transform'] = elem.style['-webkit-transform'] = transform;
		}
    };
    $.fx.step['rotate'] = function(fx){
		$.cssHooks['rotate'].set(fx.elem, fx.now);
    };
})(jQuery);
