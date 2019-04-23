(function( $ ) {
	'use strict';

	$(document).ready(function() {
		var admin = $('#recright-feed-admin'),
				loop = admin.find('.recright-feed-template_loop:first'),
				focused = false;
		loop.on('focus', function() {
			focused = true;
		});
		admin.find('.recright-feed-variables:first button').each(function() {
			var button = $(this);
			button.on('click', function() {
				var name = button.data('name'),
						lcase = name.toLowerCase(),
						text = loop.val(),
						start = focused ? loop.prop('selectionStart') : text.length,
						end = focused ? loop.prop('selectionEnd') : start,
						tag = '[' + name,
						args = [];
				if (lcase.indexOf('date') >= 0 || lcase.indexOf('time') >= 0) {
					args.push('date')
				}
				if (lcase.indexOf('description') >= 0) {
					args.push('html')
				}
				if (args.length > 0) {
					tag += ':' + args.join(';');
				}
				tag += ']';
				var left = text.substr(0, start),
						right = text.substr(start),
						last = start + tag.length;
				if (start !== end) {
					right = right.substr(end - start);
				}
				loop
					.val(left + tag + right)
					.focus()
					.prop('selectionStart', last)
					.prop('selectionEnd', last);
			});
		});
	});

})( jQuery );
