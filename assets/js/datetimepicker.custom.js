$(document).on('focusin', '[data-toggle="date"]', function(e) {
	$(this).data('DateTimePicker') ||
	$(this).datetimepicker({
		format: 'YYYY-MM-DD'
	});
	setTimeout(function() {
		console.log($(".bootstrap-datetimepicker-widget")[0].outerHTML);
	}, 100);
});

$(document).on('focusin', '[data-toggle="time"]', function(e) {
	$(this).data('DateTimePicker') ||
	$(this).datetimepicker({
		format: 'HH:mm:ss'
	});
	setTimeout(function() {
		console.log($(".bootstrap-datetimepicker-widget")[0].outerHTML);
	}, 100);
});

$(document).on('focusin', '[data-toggle="datetime"]', function(e) {
	$(this).data('DateTimePicker') ||
	$(this).datetimepicker({
		format: 'YYYY-MM-DD HH:mm:ss',
		sideBySide: true
	});
	setTimeout(function() {
		console.log($(".bootstrap-datetimepicker-widget")[0].outerHTML);
	}, 100);
});