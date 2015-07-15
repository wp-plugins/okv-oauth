<?php
/**
 * Okvee OAuth hook to login page.
 * 
 * @package okv-oauth
 */


namespace OKVOAuth\Library;


if (!class_exists('\\OKVOAuth\\Library\\HookLoginPage')) {
    class HookLoginPage
    {


        use \OKVOAuth\Library\OptionsTrait;


        public $okvoauth_login_method = 0;


        public function __construct()
        {
            $this->okvoauth_login_method = intval(get_option('okvoauth_login_method'));
        }// __construct


        /**
         * set auth cookie expiration.
         * @param integer $expire seconds to be expire from now.
         * @param integer $user_id user id.
         * @param boolean $remember true to remember, false to not remember.
         * @return integer number that will be add in expiration
         */
        public function authCookieExpiration($expire = '', $user_id = '', $remember = false)
        {
            return intval(14 * DAY_IN_SECONDS);
        }// authCookieExpiration


        /**
         * hook wordpress authenticate.
         * 
         * @param mixed $user null or WP_User or WP_Error
         * @param string $username
         * @param string $password
         * @return mixed
         */
        public function authenticate($user, $username=null, $password=null)
        {
            if ($this->okvoauth_login_method === 1 || $this->okvoauth_login_method === 2) {
                // 1 = use both wp+oauth. 2 = use oauth only.
                if ($this->okvoauth_login_method === 2 && $username != null && $password != null) {
                    return new \WP_Error('okvoauth_login_error', __('The original login step has been disabled.', 'okv-oauth'));
                }

                // google connect.
                $myconnect_google = new \OKVOAuth\MyConnect\Google();
                $google_auth = $myconnect_google->googleLoginByEmail($user, $username, $password);
                unset($myconnect_google);
                if ($google_auth !== false) {
                    return $google_auth;
                }
                unset($google_auth);
                
                // none authenticate hook.
                return $user;
            }
        }// authenticate


        /**
         * echo display the error message.
         * 
         * @param string $error_message error message.
         * @param string $error_id id of this error element.
         */
        public function displayError($error_message = '', $error_id = 'login_error')
        {
            echo '<div class="error-message" id="' . $error_id . '"><p>' . $error_message . '</p></div>';
        }// displayError


        /**
         * logged in redirect<br>
         * these code copy from wp-login.php file.
         * 
         * @param object $user user object from wp.
         */
        public function loggedinRedirect($user)
        {
            if (is_wp_error($user) || (!is_wp_error($user) && !is_object($user))) {
                return false;
            }
            
            $secure_cookie = '';
            
            if (isset($_REQUEST['redirect_to'])) {
                $redirect_to = $_REQUEST['redirect_to'];
                // Redirect to https if user wants ssl
                if ($secure_cookie && false !== strpos($redirect_to, 'wp-admin'))
                    $redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
            } else {
                $redirect_to = admin_url();
            }

            $requested_redirect_to = (isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '');
            $redirect_to = apply_filters('login_redirect', $redirect_to, $requested_redirect_to, $user);
            unset($requested_redirect_to);

            if ((empty($redirect_to) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url())) {
                // If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
                if (is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin($user->ID))
                    $redirect_to = user_admin_url();
                elseif (is_multisite() && !$user->has_cap('read'))
                    $redirect_to = get_dashboard_url($user->ID);
                elseif (!$user->has_cap('edit_posts'))
                    $redirect_to = admin_url('profile.php');
            }
            
            wp_safe_redirect($redirect_to);
            
            exit;
        }// loggedinRedirect


        /**
         * Hook into login page only.
         * 
         * @return boolean
         */
        public function loginForm()
        {
            // wp-login.php have many actions. (postpass (post's password to view), logout, (lostpassword, retrievepassword), (resetpass, rp), register, (login, ''))
            if ($this->okvoauth_login_method === 1 || $this->okvoauth_login_method === 2) {
                // 1 = use both wp+oauth. 2 = use oauth only.
                echo '<div class="okv-oauth-logins">'."\n";
                
                // google connect.
                $myconnect_google = new \OKVOAuth\MyConnect\Google();
                $myconnect_google->displayGoogleLoginLink();
                unset($myconnect_google);
                
                if ($this->okvoauth_login_method === 1) {
                    echo '<p class="or-original-wp-login">'.__('OR', 'okv-oauth').'</p>';
                }
                
                echo '</div>'."\n";// end .okv-oauth-logins
                
                echo '<script>var okvoauth_login_method = '.$this->okvoauth_login_method.';</script>';
                wp_enqueue_script('oauth-login-btn', plugins_url('js/login.js', __DIR__), ['jquery']);
            } else {
                // maybe use wp only (value = 0). do nothing.
                return false;
            }
        }// loginForm


        /**
         * hook header url to change url link in logo of login page.
         * 
         * @return string
         */
        public function loginHeaderUrl()
        {
            return home_url();
        }// loginHeaderUrl


        /**
         * hook into login_enqueue_scripts 
         * 
         * @return mixed return false if not use oauth, true if use, number if can count how many oauth use.
         */
        public function loginHtmlHead()
        {
            if ($this->okvoauth_login_method === 0) {
                return false;
            }
            
            $action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
            if (!in_array($action, ['postpass', 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register', 'login'], true) && false === has_filter('login_form_' . $action )) {
                $action = 'login';
            }
            $oauth_i = 0;
            
            switch ($action) {
                case 'postpass':
                case 'logout':
                case 'lostpassword':
                case 'retrievepassword':
                case 'resetpass':
                case 'rp':
                    break;
                case 'register':
                case 'login':
                default:
                    // this does not work because do_action('login_head') does not echo it.
                    //wp_enqueue_style('okvoauthcss', plugins_url('css/okv-oauth.css', __DIR__));
                    // directly use echo link tag instead.
                    echo '<link rel="stylesheet" href="' . plugins_url('css/okv-oauth.css', __DIR__) . '" type="text/css" />'."\n";
                    
                    if ($this->okvoauth_login_method === 1 || $this->okvoauth_login_method === 2) {
                        // 1 = use both wp+oauth. 2 = use oauth only.
                        // google connect css.
                        $myconnect_google = new \OKVOAuth\MyConnect\Google();
                        $mycon_result = $myconnect_google->displayGoogleLoginCss(true);
                        unset($myconnect_google);
                        if ($mycon_result !== false) {
                            $oauth_i++;
                        }
                    }
                    break;
            }
            
            unset($action, $mycon_result);
            
            if ($oauth_i > 0) {
                return $oauth_i;
            } else {
                return true;
            }
        }// loginHtmlHead


        /**
         * Runs at the end of the form used to retrieve a user's password by email, to allow a plugin to supply extra fields. 
         * 
         * @return boolean
         */
        public function lostPasswordForm()
        {
            if ($this->okvoauth_login_method === 2) {
                // 2 = use oauth only.
                $err = new \WP_Error('okvoauth_login_error', __('The original forgot password step has been disabled.', 'okv-oauth'));
                if (is_wp_error($err)) {
                    $error_string = $err->get_error_message();
                    echo '<div class="error-message" id="login_error"><p>' . $error_string . '</p></div>';
                }
                unset($err, $error_string);
                
                echo '<script>var okvoauth_login_method = '.$this->okvoauth_login_method.';</script>';
                wp_enqueue_script('oauth-login-btn', plugins_url('js/lostpassword.js', __DIR__), ['jquery']);
            } else {
                // maybe use wp only (value = 0). do nothing.
                return false;
            }
        }// lostPasswordForm


        /**
         * Runs when the user has requested an email message to retrieve their password, to allow a plugin to modify the PHP $_POST variable before processing.
         */
        public function lostPasswordPost()
        {
            if ($this->okvoauth_login_method === 2) {
                // DO NOT allow to forgot password while using social connect. really!
                exit;
            }
        }// lostPasswordPost
        


        /**
         * hook into register page only.
         * 
         * @return boolean
         */
        public function registerForm()
        {
            if ($this->okvoauth_login_method === 1 || $this->okvoauth_login_method === 2) {
                // 1 = use both wp+oauth. 2 = use oauth only.
                echo '<div class="okv-oauth-logins">'."\n";
                
                // google connect.
                $myconnect_google = new \OKVOAuth\MyConnect\Google();
                $myconnect_google->displayGoogleRegisterLink();
                unset($myconnect_google);
                
                if ($this->okvoauth_login_method === 1) {
                    echo '<p class="or-original-wp-login">'.__('OR', 'okv-oauth').'</p>';
                }
                
                echo '</div>'."\n";// end .okv-oauth-logins
                
                echo '<script>var okvoauth_login_method = '.$this->okvoauth_login_method.';</script>'."\n";
                wp_enqueue_script('oauth-login-btn', plugins_url('js/register.js', __DIR__), ['jquery']);
            } else {
                // maybe use wp only (value = 0). do nothing.
                return false;
            }
        }// registerForm


        /**
         * Runs before a new user registration request is processed.
         * 
         * @param string $sanitized_user_login
         * @param string $user_email
         * @param \WP_Error $errors
         * @return mixed return true if success register, \WP_Error if contain some error.
         */
        public function registerPost($sanitized_user_login = '', $user_email = '', $errors = '')
        {
            if ($this->okvoauth_login_method === 1 || $this->okvoauth_login_method === 2) {
                // 1 = use both wp+oauth. 2 = use oauth only.
                if (is_wp_error($errors)) {
                    return $errors;
                }
                
                return true;
            }
        }// registerPost


        /**
         * Fires after WordPress has finished loading but before any headers are sent.<br>
         * Do not try to echo or send out anything before the headers are sent or it will be error.
         */
        public function wpInit()
        {
            if ($this->okvoauth_login_method === 1 || $this->okvoauth_login_method === 2) {
                // 1 = use both wp+oauth. 2 = use oauth only.
                // google connect.
                $myconnect_google = new \OKVOAuth\MyConnect\Google();
                $myconnect_google->googleWpInit();
                unset($myconnect_google);
            }
        }// wpInit


        /**
         * remove cookies that were use when logged in.
         */
        public function wpLogout()
        {
            // get options of this plugin.
            $plugin_options = $this->getOptions();
            if (is_array($plugin_options)) {
                foreach ($plugin_options as $option_name => $option_val) {
                    $$option_name = $option_val;
                }
            }
            unset($option_name, $option_val, $plugin_options);

            // google connect.
            $myconnect_google = new \OKVOAuth\MyConnect\Google();
            $myconnect_google->googleLogout();
            unset($myconnect_google);
        }// wpLogout


    }// end class ------------------------------------------------------------------
}