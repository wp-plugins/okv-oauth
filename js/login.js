

// js work on page loaded ------------------------------------------------------------------------
(function($) {
	// move those social login btn to top.
	$('#loginform').find('.okv-oauth-logins').prependTo('#loginform');
	
	// if okvoauth_login_method = 2 (use oauth only)
	if (typeof(okvoauth_login_method) !== 'undefined' && okvoauth_login_method === 2) {
		$('#loginform').addClass('oauth-only');
		// remove login form.
		$('#loginform').find('p:has(label)').remove();
		// remove remember me form and submit btn
		$('#loginform').find('.forgetmenot, .submit').remove();
		// remove forgot password link.
		$('#nav a:last-child').remove();
	}
})(jQuery);