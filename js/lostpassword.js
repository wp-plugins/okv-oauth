

// js work on page loaded ------------------------------------------------------------------------
(function($) {
	// display error on lost password form.
	$('#lostpasswordform').find('.error-message').insertBefore('#lostpasswordform');
	
	// if okvoauth_login_method = 2 (use oauth only)
	if (typeof(okvoauth_login_method) != 'undefined' && okvoauth_login_method === 2) {
		// hide default instruction message.
		$('.message').remove();
		
		$('#lostpasswordform').addClass('oauth-only').hide();
		// remove login form.
		$('#lostpasswordform').find('p:has(label)').remove();
		// remove submit btn
		$('#lostpasswordform').find('.submit').remove();
		// remove register btn
		$('#nav a:last-child').remove();
	}
})(jQuery);