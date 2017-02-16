toastr.showLoader = function(message) {
	if ($('#toastr-loading').length) {
		return $('#toastr-loading .content').effect('shake');
	}
	message = message ? message : 'Loading, please wait...';
	$('body').append('\
			<div id="toastr-loading">\
				<div class="overlay"></div>\
				<div class="content">' + message + '</div>\
			</div>\
		');
	setTimeout(function() {
		// $('#toastr-loading').addClass('showed');
		$('#toastr-loading .content').animateCss('bounceIn');
	}, 0);
};

toastr.hideLoader = function() {
	$('#toastr-loading .content').animateCss('bounceOut', function(e) {
		$('#toastr-loading').remove();
	});
};

