(function() {

	function MyCaller(scope, key, callback, delay, args, sendKey) {
		this.scope    = scope;
		this.key      = key;
		this.callback = callback;
		this.delay    = delay;
		this.args     = args;
		this.sendKey  = sendKey;
		MyCaller.timeouts[key] = this;
		this.set();
	}

	MyCaller.timeouts = {};

	MyCaller.prototype.set = function() {
		this.id = setTimeout(function(me) {
			me.run();
		}, this.delay, this);
	};

	MyCaller.prototype.stop = function() {
		this.id && clearTimeout(this.id);
		this.id = null;
	}

	MyCaller.prototype.clear = function() {
		this.stop();
		MyCaller.timeouts[this.key] = null;
		this.cleared = true;
	};

	MyCaller.prototype.force = function() {
		this.stop();
		this.run();
	};

	MyCaller.prototype.run = function() {
		if (!this.cleared) {
			var args = this.sendKey ?
				[this.key].concat(this.args) : this.args;
			this.callback.apply(this.scope, args);
		}
		this.clear();
	};

	// jQuery.???

	$.setTimeout = function(key, callback, delay) {
		$.clearTimeout(key);
		var args = $.makeArray(arguments).slice(3);
		new MyCaller(this, key, callback, delay, args, true);
	};

	$.forceTimeout = function(key) {
		var prev = MyCaller.timeouts[key];
		prev && prev.force();
	};

	$.clearTimeout = function(key) {
		var prev = MyCaller.timeouts[key];
		prev && prev.clear();
	};

	// jQuery.fn.???

	function getKey() {
		var toId = $(this).data('timeoutId');
		if (!toId) {
			toId = 'timeout' + ~~(Math.random() * 1e6);
			$(this).data('timeoutId', toId);
		}
		return toId;
	}

	$.fn.setTimeout = function(callback, delay) {
		$(this).each(function() {
			var key = getKey.call(this);
			$(this).clearTimeout();
			var args = $.makeArray(arguments).slice(3);
			new MyCaller(this, key, callback, delay, args, false);
		});
	};

	$.fn.forceTimeout = function() {
		$(this).each(function() {
			$.forceTimeout(getKey.call(this));
		});
	};


	$.fn.clearTimeout = function() {
		$(this).each(function() {
			$.clearTimeout(getKey.call(this));
		});
	};

})();