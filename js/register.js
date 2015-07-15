

function okvOauthAutoFillRegisterFormByGoogle() {
	// if got google code and ready to register.
	if (typeof(okvoauth_google_register_got_code) !== 'undefined' && okvoauth_google_register_got_code === true) {
		form_user_login = jQuery('#user_login').val();
		form_user_email = jQuery('#user_email').val();
		
		if (form_user_login == '') {
			jQuery('#user_login').val(okvoauth_google_wp_user_login);
		}
		if (form_user_email == '') {
			jQuery('#user_email').removeAttr('readonly').removeClass('readonly');
			jQuery('#user_email').val(okvoauth_google_wp_user_email);
			jQuery('#user_email').attr('readonly', '').addClass('readonly');
		}
		
		delete form_user_email;
		delete form_user_login;
	}
}// okvOauthAutoFillRegisterFormByGoogle


// js work on page loaded ------------------------------------------------------------------------
(function($) {
	// move those social login btn to top.
	$('#registerform').find('.okv-oauth-logins').prependTo('#registerform');
	// move error message to below generic message.
	$('#registerform').find('.error-message').insertAfter($('.message'));
	// hide email form field and set it to read only.
	$('#registerform #user_email').attr('readonly', '').addClass('readonly');
	
	// if okvoauth_login_method = 2 (use oauth only)
	if (
		(
			typeof(okvoauth_login_method) !== 'undefined' && 
			okvoauth_login_method === 2
		) &&
		(
			typeof(okvoauth_google_register_got_code) == 'undefined' ||
			(
				typeof (okvoauth_google_register_got_code) != 'undefined' &&
				okvoauth_google_register_got_code === false
			)
		)
	) {
		$('#registerform').addClass('oauth-only');
		// remove register form.
		$('#registerform').find('p:has(label)').remove();
		// remove register message, clear new line, submit btn
		$('#registerform').find('#reg_passmail, .clear, .submit').remove();
		// remove forgot password link.
		$('#nav a:last-child').remove();
	}
	
	// auto fill register form by google.
	okvOauthAutoFillRegisterFormByGoogle();
})(jQuery);