$.fn.extend({
	bs: function(x) {
		return this.data('bs.' + x);
	}
});

$(document).tooltip({
	selector: '[data-toggle="tooltip"]'
});