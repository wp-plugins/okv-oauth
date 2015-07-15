<?php
/**
 * connect adapter. for call to those social with api and generate link or authenticate with out write it directly in HookLoginPage class.
 * 
 * @package okv-oauth
 */


namespace OKVOAuth\MyConnect;


if (!class_exists('\\OKVOAuth\\MyConnect\\Google')) {
    class Google extends \OKVOAuth\Library\HookLoginPage
    {


        public $okvoauth_login_method = 0;
        public $use_google_login = false;


        public function __construct() {
            require_once dirname(__DIR__).'/Google/autoload.php';

            // get settings of google api.
            $plugin_options = $this->getOptions(['okvoauth_google_client_id', 'okvoauth_google_client_secret']);
            if (is_array($plugin_options)) {
                foreach ($plugin_options as $option_name => $option_val) {
                    $$option_name = $option_val;
                }
            }
            unset($option_name, $option_val, $plugin_options);

            // check if admin was set google api then use this login.
            if (
                (isset($okvoauth_google_client_id) && $okvoauth_google_client_id != null) &&
                (isset($okvoauth_google_client_secret) && $okvoauth_google_client_secret != null)
            ) {
                $this->use_google_login = true;
            }
            
            $this->okvoauth_login_method = intval(get_option('okvoauth_login_method'));
        }// __construct


        /**
         * display this connect's css.
         * 
         * @param boolean $force_echo set to true if use echo, set to false if use wp_enqueue_style.
         */
        public function displayGoogleLoginCss($force_echo = false)
        {
            if ($this->use_google_login === true) {
                if ($force_echo === true) {
                    echo '<link rel="stylesheet" href="' . plugins_url('css/okv-oauth-google-login.css', __DIR__) . '" type="text/css" />'."\n";
                    return true;
                } else {
                    wp_enqueue_style('google-login-btn', plugins_url('css/okv-oauth-google-login.css', __DIR__));
                    return true;
                }
            }
            return false;
        }// displayGoogleLoginCss


        /**
         * display login link.
         * any content will be echo immediately here.
         * 
         * @return boolean
         */
        public function displayGoogleLoginLink() 
        {
            if ($this->use_google_login === true) {
                $client = $this->newGoogleClient($this->getGoogleOptions());
                $auth_url = $client->createAuthUrl();
                
                if (isset($auth_url)) {
                    echo '<p class="google-login oauth-login"><a href="'.$auth_url.'">'.__('Log in with Google', 'okv-oauth').'</a></p>';
                }
                
                unset($auth_url, $client);
                return true;
            } else {
                return false;
            }
        }// displayGoogleLoginLink


        /**
         * display register link
         * 
         * @return boolean
         */
        public function displayGoogleRegisterLink()
        {
            if ($this->use_google_login === true) {
                $client = $this->newGoogleClient($this->getGoogleOptions());
                $auth_url = $client->createAuthUrl();
                
                if (isset($auth_url)) {
                    echo '<p class="google-register oauth-login"><a href="'.$auth_url.'">'.__('Register with Google', 'okv-oauth').'</a></p>';
                }
                
                if (isset($_REQUEST['error'])) {
                    $this->googleRegisterError($_REQUEST['error'], true);
                    return false;
                }
                
                if (isset($_COOKIE['google_access_token']) && !empty($_COOKIE['google_access_token'])) {
                    $check_user_result = $this->googleRegisterCheckUser();
                    if ($check_user_result === false) {
                        return false;
                    } elseif (is_array($check_user_result)) {
                        // check user passed. now it is ready to prepare register form.
                        echo '<script>'."\n";
                        echo 'var okvoauth_google_register_got_code = true;'."\n";
                        echo 'var okvoauth_google_wp_user_login = \''.(isset($check_user_result['wp_user_login']) ? $check_user_result['wp_user_login'] : '').'\';'."\n";
                        echo 'var okvoauth_google_wp_user_email = \''.(isset($check_user_result['wp_user_email']) ? $check_user_result['wp_user_email'] : '').'\';'."\n";
                        echo 'var okvoauth_google_wp_user_avatar = \''.(isset($check_user_result['wp_user_avatar']) ? $check_user_result['wp_user_avatar'] : '').'\';'."\n";
                        echo '</script>'."\n";
                    }
                    unset($check_user_result);
                }
                
                unset($auth_url, $client);
                return true;
            } else {
                return false;
            }
        }// displayGoogleRegisterLink


        /**
         * get wp-login url.
         * 
         * @param string $action set ?action query string. if use auto leave false.
         * @param array $params additional querystrings. (key1 => val1, key2 => val2)
         * @return string
         */
        public function getGoogleLoginUrl($action = false, array $params = [])
        {
            $login_url = wp_login_url();
            if ($action === false) {
                $action = (isset($_GET['action']) ? $_GET['action'] : '');
            }

            if ($this->isSecureLogin()) {
                $login_url = 'https://'.substr($login_url,7);
            }
            
            if ($action !== false && $action !== null && $action !== '') {
                $login_url .= '?action='.$action;
            }
            
            if (!empty($params)) {
                if (strpos($login_url, '?') === false) {
                    $login_url .= '?';
                } else {
                    $login_url .= '&';
                }
                $params_arr = [];
                foreach ($params as $key => $val) {
                    $params_arr[] = $key.'='.$val;
                }
                $login_url .= implode('&', $params_arr);
                unset($key, $params_arr, $val);
            }

            return $login_url;
        }// getGoogleLoginUrl


        /**
         * get google required options.
         * 
         * @return array
         */
        public function getGoogleOptions()
        {
            // get settings of google api.
            $plugin_options = $this->getOptions(['okvoauth_google_client_id', 'okvoauth_google_client_secret']);
            if (is_array($plugin_options)) {
                foreach ($plugin_options as $option_name => $option_val) {
                    $$option_name = $option_val;
                }
            }
            unset($option_name, $option_val, $plugin_options);
            
            $options = [];
            $options['client_id'] = $okvoauth_google_client_id;
            $options['client_secret'] = $okvoauth_google_client_secret;
            
            unset($okvoauth_google_client_id, $okvoauth_google_client_secret);
            return $options;
        }// getGoogleOptions


        /**
         * authenticate wordpress login by email.
         * 
         * @param mixed $user null or WP_User or WP_Error
         * @param string $username
         * @param string $password
         * @return mixed return false if not use google login, return \WP_Error if found error, redirect if login success.
         */
        public function googleLoginByEmail($user, $username=null, $password=null)
        {
            if ($this->use_google_login !== true) {
                return false;
            }

            if (isset($_REQUEST['error'])) {
                return $this->googleRegisterError($_REQUEST['error']);
            }
            
            if (isset($_GET['code'])) {
                $client = $this->newGoogleClient($this->getGoogleOptions());
                
                try {
                    if ($client->getAccessToken() == null) {
                        $client->authenticate($_GET['code']);
                    } else {
                        $client->refreshToken($client);
                    }

                    // get token data.
                    $token_data = $client->verifyIdToken()->getAttributes();

                    if (
                        is_array($token_data) && 
                        array_key_exists('payload', $token_data) && 
                        array_key_exists('email', $token_data['payload']) &&
                        array_key_exists('email_verified', $token_data['payload'])
                    ) {
                        if (!$token_data['payload']['email_verified']) {
                            $user = new \WP_Error('okvoauth_google_login_error', __('You have to verify your email on Google account.', 'okv-oauth'));
                        } else {
                            $user = get_user_by('email', $token_data['payload']['email']);

                            if ($user === false) {
                                $user = new \WP_Error('okvoauth_google_login_error', __('Not found this email on the system.', 'okv-oauth'));
                            }

                            if ($user !== false && !is_wp_error($user)) {
                                wp_clear_auth_cookie();
                                wp_set_auth_cookie($user->ID, true);
                                setcookie('google_access_token', $client->getAccessToken(), time()+(2 * DAY_IN_SECONDS), '/', defined(COOKIE_DOMAIN) ? COOKIE_DOMAIN : '' );
                                // do hook action as login success.
                                do_action('wp_login', $token_data['payload']['email'], $user);
                                $this->loggedinRedirect($user);
                            }
                        }
                    } else {
                        $user = new \WP_Error('okvoauth_google_login_error', __('Unable to fetch user detail from Google.', 'okv-oauth'));
                    }
                } catch (\Google_Exception $e) {
                    $error_msg = $e->getMessage();
                    if (is_string($error_msg) && strpos($error_msg, 'invalid_client') !== false) {
                        $user = new \WP_Error('okvoauth_google_login_error', __('Incorrect Client secret. Administrator needs to settings Google login.', 'okv-oauth'));
                    } elseif (is_string($error_msg) && strpos($error_msg, 'invalid_grant') !== false) {
                        $user = new \WP_Error('okvoauth_google_login_error', __('Unable to reload. Please click Log In button and try again.', 'okv-oauth'));
                    } else {
                        $user = new \WP_Error('okvoauth_google_login_error', $error_msg);
                    }
                    unset($err_msg);
                }
                
                unset($client, $token_data);
            } else {
                // none authenticate from google. return false to lets other oauth working.
                $user = false;
            }
            
            return $user;
        }// googleLoginByEmail


        /**
         * remove cookie.
         * 
         * @param boolean $clear_cookie_var set to true to clear $_COOKIE variable. set to false to not clear that.
         */
        public function googleLogout($clear_cookie_var = true)
        {
            if ($this->use_google_login === true) {
                setcookie('google_access_token', '', (time()-((2 * DAY_IN_SECONDS)*2)), '/', defined(COOKIE_DOMAIN) ? COOKIE_DOMAIN : '' );

                if ($clear_cookie_var === true) {
                    unset($_COOKIE['google_access_token']);
                }
            }
        }// googleLogout


        /**
         * auth google code secret after init wp.<br>
         * never send output here. headers are ok.
         */
        public function googleRegisterAuthCode()
        {
            if (isset($_GET['code']) && !empty($_GET['code'])) {
                $client = $this->newGoogleClient($this->getGoogleOptions());
                
                try {
                    $client->authenticate($_GET['code']);
                    setcookie('google_access_token', $client->getAccessToken(), time()+(2 * DAY_IN_SECONDS), '/', defined(COOKIE_DOMAIN) ? COOKIE_DOMAIN : '' );
                    wp_redirect($this->getGoogleLoginUrl());
                    exit;
                } catch (\Google_Exception $e) {
                    $error_msg = $e->getMessage();
                    if (is_string($error_msg) && strpos($error_msg, 'invalid_client') !== false) {
                        $error_msg = 'invalid_client';
                    }
                    wp_redirect($this->getGoogleLoginUrl(false, ['error' => $error_msg]));
                    exit;
                }
                
                unset($client);
            } elseif (isset($_COOKIE['google_access_token']) && !empty($_COOKIE['google_access_token'])) {
                $client = $this->newGoogleClient($this->getGoogleOptions());
                
                try {
                    $client->setAccessToken(stripslashes($_COOKIE['google_access_token']));
                    // get token data.
                    $token_data = $client->verifyIdToken()->getAttributes();
                    
                    if (
                        is_array($token_data) && 
                        array_key_exists('payload', $token_data) && 
                        array_key_exists('email', $token_data['payload']) &&
                        array_key_exists('email_verified', $token_data['payload'])
                    ) {
                        if (!$token_data['payload']['email_verified']) {
                            // not verified email.
                            $this->googleLogout(false);
                        } else {
                            $user = get_user_by('email', $token_data['payload']['email']);
                            
                            if ($user !== false) {
                                // email exists.
                                $this->googleLogout(false);
                            }
                        }
                    } else {
                        // unable to fetch user data from google.
                        $this->googleLogout(false);
                    }
                } catch (\Google_Exception $e) {
                    // incorrect secret.
                    $this->googleLogout(false);
                }
                
                unset($client, $token_data);
            }
        }// googleRegisterAuthCode


        /**
         * check google token and user's info and the wp user's info.
         * 
         * @return mixed return false if failed to validate user or token. return array value of google user info
         */
        public function googleRegisterCheckUser()
        {
            $client = $this->newGoogleClient($this->getGoogleOptions());

            try {
                $client->setAccessToken(stripslashes($_COOKIE['google_access_token']));
                // get token data.
                $token_data = $client->verifyIdToken()->getAttributes();
                $g_oauth = new \Google_Service_Oauth2($client);
                
                if (property_exists($g_oauth, 'userinfo') && method_exists($g_oauth->userinfo, 'get')) {
                    $g_userinfo = $g_oauth->userinfo->get();
                }

                if (
                    is_array($token_data) && 
                    array_key_exists('payload', $token_data) && 
                    array_key_exists('email', $token_data['payload']) &&
                    array_key_exists('email_verified', $token_data['payload']) && 
                    isset($g_userinfo)
                ) {
                    if (!$token_data['payload']['email_verified']) {
                        $this->displayError(__('You have to verify your email on Google account.', 'okv-oauth'));
                        return false;
                    } else {
                        $user = get_user_by('email', $token_data['payload']['email']);

                        if ($user !== false) {
                            $this->displayError(__('This email is already in use. Please try to register with Google use another email.', 'okv-oauth'));
                            return false;
                        }

                        if (!is_wp_error($user)) {
                            $output = [];
                            $output['wp_user_login'] = $g_userinfo->givenName;
                            $output['wp_user_email'] = $g_userinfo->email;
                            $output['wp_user_avatar'] = $g_userinfo->picture;
                            unset($client, $g_oauth, $g_userinfo, $token_data, $user);
                            return $output;
                        }
                    }
                } else {
                    $this->displayError(__('Unable to fetch user detail from Google.', 'okv-oauth'));
                    return false;
                }
                
                unset($g_oauth, $g_userinfo, $token_data, $user);
            } catch (\Google_Exception $e) {
                $error_msg = $e->getMessage();

                if (is_string($error_msg) && strpos($error_msg, 'invalid_client') !== false) {
                    $this->displayError(__('Incorrect Client secret. Administrator needs to settings Google login.', 'okv-oauth'));
                } else {
                    $this->displayError($error_msg);
                }

                unset($error_msg);
                return false;
            }// end try
            
            unset($client);
        }// googleRegisterCheckUser


        /**
         * check and display google login/register error.
         * 
         * @param string $error
         * @param boolean $force_echo
         * @return \WP_Error|string
         */
        public function googleRegisterError($error = '', $force_echo = false)
        {
            switch ($error) {
                case 'access_denied':
                    $err_msg = __('You did not grant access.', 'okv-oauth');
                    break;
                case 'ga_needs_configuring':
                    $err_msg = __('Administrator needs to settings Google login.', 'okv-oauth');
                    break;
                case 'invalid_client':
                    $err_msg = __('Incorrect Client secret. Administrator needs to settings Google login.', 'okv-oauth');
                    break;
                default:
                    $err_msg = htmlentities2($error);
                    break;
            }
            
            if ($err_msg != null) {
                $err = new \WP_Error('okvoauth_google_login_error', $err_msg);
                
                if (is_wp_error($err) && $force_echo === true) {
                    $error_string = $err->get_error_message();
                    $this->displayError($error_string);
                    unset($error_string);
                }
                
                return $err;
            }
            
            unset($err, $err_msg);
            return $error;
        }// googleRegisterError


        /**
         * Fires after WordPress has finished loading but before any headers are sent. 
         */
        public function googleWpInit()
        {
            if (isset($_GET['action']) && $_GET['action'] == 'register') {
                // on register page
                $this->googleRegisterAuthCode();
            }
        }// googleWpInit


        /**
         * create new google client object.<br>
         * for the scope. use https://www.googleapis.com/auth/userinfo.profile instead of https://www.googleapis.com/auth/plus.login will prevent require user to signup google plus.
         * 
         * @param array $options
         * @return \Google_Client
         */
        protected function newGoogleClient(array $options = [])
        {
            $client = new \Google_Client();
            $client->setClientId((isset($options['client_id']) ? $options['client_id'] : ''));
            $client->setClientSecret((isset($options['client_secret']) ? $options['client_secret'] : ''));
            $client->setRedirectUri($this->getGoogleLoginUrl());
            $client->setScopes('https://www.googleapis.com/auth/userinfo.profile');
            $client->addScope('email');
            $client->setApprovalPrompt('force');// always allow user to switch user or accept everytime.
            
            // done.
            $options = [];
            return $client;
        }// newGoogleClient


    }// end class ------------------------------------------------------------------
}