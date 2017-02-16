var Toast = (function() {

	var obj = {
		success: creator('success'),
		error: creator('error'),
		warning: creator('warning'),
		info: creator('info'),
		toasts: {}
	};

	function objector(astr) {
		return {
			$toast: $(astr),
			clear: function(opts) {
				return toastr.clear(astr, opts);
			},
			remove: function() {
				return $(astr).remove();
			},
			success: creator('success'),
			error: creator('error'),
			warning: creator('warning'),
			info: creator('info')
		};
	}

	function creator(type) {
		return function(message, name, options) {
			if (obj.toasts[name]) {
				// obj.toasts[name];
				obj.toasts[name].$toast.effect('pulsate', {}, 1000);
				return;
			}
			return obj.toasts[name] = objector(toastr[type](message, '', $.extend({}, options, {
				onHidden: function() {
					delete obj.toasts[name];
				}
			})));
		}
	}

	return obj;

})();