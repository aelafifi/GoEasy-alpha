swal.showLoader = function(message) {
	message = message ? message : 'Loading, please wait...';
	swal(message);
	swal.disableButtons();
};