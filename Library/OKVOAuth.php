<?php
/**
 * This class will initiate the plugin and call to those add action or filter.
 * 
 * @package okv-oauth
 */


namespace OKVOAuth\Library;


if (!class_exists('\\OKVOAuth\\Library\\OKVOAuth')) {
    /**
     * class of this plugin.
     */
    class OKVOAuth
    {


        /**
         * add actions and filters.
         */
        public function addActionsFilters()
        {
            // hook at wp init before headers are sent.
            add_action('init', [$this, 'wpInit']);
            
            // hook to admin menu.
            $admin_menu = new \OKVOAuth\Library\AdminMenu();
            add_action('admin_menu', [$admin_menu, 'pluginSettingsMenu']);
            unset($admin_menu);

            // register activation hooks. (activate/deactivate/delete or uninstall).
            $activation = new \OKVOAuth\Library\Activation();
            // register activate hook
            register_activation_hook(OKV_OAUTH_FILE, [&$activation, 'activation']);
            // register deactivate hook
            register_deactivation_hook(OKV_OAUTH_FILE, [&$activation, 'deactivation']);
            // register uninstall hook. this hook will be work on delete plugin.
            // * register uninstall hook MUST be static method or function.
            register_uninstall_hook(OKV_OAUTH_FILE, array('\\OKVOAuth\\Library\\Activation', 'uninstall'));
            unset($activation);
            
            // hook to login pages. (register/login/logout)
            $login_page = new \OKVOAuth\Library\HookLoginPage();
            $this->HookLoginPage = $login_page;
            // hook to login head tag.
            add_action('login_head', [$login_page, 'loginHtmlHead']);
            // change logo url.
            add_filter('login_headerurl', [$login_page, 'loginHeaderUrl']);
            // register form hook
            add_action('register_form', [$login_page, 'registerForm']);
            // register before after form submitted before process.
            add_action('register_post', [$login_page, 'registerPost'], 5, 3);
            // add buttons into login page.
            add_action('login_form', [$login_page, 'loginForm']);
            // set auth cookie expiration
            add_filter('auth_cookie_expiration', [$login_page, 'authCookieExpiration']);
            // authenticate hook
            add_filter('authenticate', [$login_page, 'authenticate'], 20, 3);
            // lost password page.
            add_action('lostpassword_form', [$login_page, 'lostPasswordForm']);
            // lost password after form submitted before process.
            add_action('lostpassword_post', [$login_page, 'lostPasswordPost']);
            add_action('retreive_password', [$login_page, 'lostPasswordPost']);
            add_filter('allow_password_reset', [$login_page, 'lostPasswordPost']);
            // logout hook
            add_action('wp_logout', [$login_page, 'wpLogout']);
            unset($login_page);
        }// addActionsFilters


        /**
         * plugin initiate.
         */
        public function pluginInit() 
        {
           // load language.
           $result = load_plugin_textdomain('okv-oauth', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }// pluginInit


        /**
         * Fires after WordPress has finished loading but before any headers are sent. 
         */
        public function wpInit()
        {
            if (property_exists($this, 'HookLoginPage')) {
                $this->HookLoginPage->wpInit();
                unset($this->HookLoginPage);
            }
        }// wpInit


    }// end class ------------------------------------------------------------------
}