$.fn.extend({

	animateCss: function (animationName, callAtEnd, onceOnly) {
		if ($(this).data('animationsApplied')) {
			return setTimeout(function(elem) {
				$(elem).stopAnimate();
				$(elem).animateCss(animationName, callAtEnd, onceOnly);
			}, 0, this);
		}
		var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
		$(this).removeClass($(this).data('animationsApplied'));
		$(this).data('animationsApplied', animationName);
		animationName += $(this).hasClass('animated') ? '' : ' animated';
		$(this).addClass(animationName).one(animationEnd, function(e) {
			if (onceOnly) {
				$(this).data('animationsApplied', null);
				$(this).removeClass(animationName).trigger('animateCssEnd');
			}
			'function' === typeof callAtEnd && callAtEnd.call(this, e);
		});
	},

	stopAnimate: function() {
		$(this).removeClass($(this).data('animationsApplied') + ' animated');
		$(this).data('animationsApplied', null);
	}

});