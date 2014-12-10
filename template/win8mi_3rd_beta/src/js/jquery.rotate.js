/*
 * JQuery CSS Rotate property using CSS3 Transformations
 * Copyright (c) 2011 Jakub Jankiewicz  <http://jcubic.pl>
 * licensed under the LGPL Version 3 license.
 * http://www.gnu.org/licenses/lgpl.html
 */
(function($) {
    $.cssHooks['rotate'] = {
		get: function(elem, computed, extra){
		    return elem.style['transform'].replace(/.*rotate\((.*)deg\).*/, '$1');
		},
		set: function(elem, value){
		    value = parseInt(value);
			$(elem).data('rotatation', value);
			if (value == 0) {
				elem.style['transform'] = '';
			} else {
				elem.style['transform'] = 'rotate(' + value%360 + 'deg)';
			}
		}
    };
    $.fx.step['rotate'] = function(fx){
		$.cssHooks['rotate'].set(fx.elem, fx.now);
    };
})(jQuery);
