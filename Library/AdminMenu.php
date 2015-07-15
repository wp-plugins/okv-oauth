<?php
/**
 * Okvee OAuth admin menu
 * 
 * @package okv-oauth
 */


namespace OKVOAuth\Library;


if (!class_exists('\\OKVOAuth\\Library\\AdminMenu')) {
    /**
     * Add item into administration menu and setup plugin's settings page.
     */
    class AdminMenu
    {


        use \OKVOAuth\Library\OptionsTrait;


        /**
         * setup settings menu to go to settings page.
         */
        public function pluginSettingsMenu()
        {
            add_options_page(__('Okvee OAuth settings', 'okv-oauth'), __('Okvee OAuth', 'okv-oauth'), 'manage_options', 'okvoauth_settings', [$this, 'pluginSettingsPage']);
        }// pluginSettingsMenu


        /**
         * display and functional settings page.
         */
        public function pluginSettingsPage()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.', 'okv-oauth'));
            }
            
            // get option values -------------------
            if (is_array($this->options_name)) {
                foreach ($this->options_name as $option_name => $option_key) {
                    ${$option_name.'_val'} = get_option($option_name);
                }
                unset($option_key, $option_name);
            }
            
            // if form submitted
            if (isset($_POST) && !empty($_POST)) {
                // get values and update
                $okvoauth_login_method_val = (array_key_exists('okvoauth_login_method', $_POST) ? $_POST['okvoauth_login_method'] : 0);
                update_option('okvoauth_login_method', $okvoauth_login_method_val);
                $okvoauth_google_client_id_val = (array_key_exists('okvoauth_google_client_id', $_POST) ? $_POST['okvoauth_google_client_id'] : '');
                update_option('okvoauth_google_client_id', $okvoauth_google_client_id_val);
                $okvoauth_google_client_secret_val = (array_key_exists('okvoauth_google_client_secret', $_POST) ? $_POST['okvoauth_google_client_secret'] : '');
                update_option('okvoauth_google_client_secret', $okvoauth_google_client_secret_val);
                
                // if nothing wrong, save success.
                echo '<div class="updated"><p><strong>'.__('Settings saved.').'</strong></p></div>'."\n";
            }

            // display settings page.
            echo '<div class="wrap">'."\n";
            echo '<h2>'.__('Okvee OAuth settings', 'okv-oauth').'</h2>'."\n";
            echo '<form method="post">'."\n";
            echo '<table class="form-table">'."\n";
            // login method
            echo '<tr>'."\n";
            echo '<th scope="row"><label for="okvoauth_login_method">'.__('Login method', 'okv-oauth').'</label></th>'."\n";
            echo '<td>'."\n"
                . '<select id="okvoauth_login_method" name="okvoauth_login_method">'."\n"
                . '<option value="0"'.(isset($okvoauth_login_method_val) && $okvoauth_login_method_val == '0' ? ' selected=""' : '').'>'.__('Do not use (use WordPress login)', 'okv-oauth').'</option>'."\n"
                . '<option value="1"'.(isset($okvoauth_login_method_val) && $okvoauth_login_method_val == '1' ? ' selected=""' : '').'>'.__('Use WordPress login with OAuth', 'okv-oauth').'</option>'."\n"
                . '<option value="2"'.(isset($okvoauth_login_method_val) && $okvoauth_login_method_val == '2' ? ' selected=""' : '').'>'.__('Use OAuth only', 'okv-oauth').'</option>'."\n"
                . '</select>'."\n"
                . '</td>'."\n";
            echo '</tr>'."\n";
            echo '</table>'."\n";
            echo '<hr>'."\n";
            
            // google login settings
            echo '<h3 class="title">'.__('Google login', 'okv-oauth').'</h3>'."\n";
            echo '<table class="form-table">'."\n";
            echo '<tr>'."\n";
            echo '<th scope="row"><label for="okvoauth_google_client_id">'.__('Client ID', 'okv-oauth').'</label></th>'."\n";
            echo '<td>'."\n"
                . '<input id="okvoauth_google_client_id" class="regular-text" type="text" name="okvoauth_google_client_id" value="'.$okvoauth_google_client_id_val.'" autocomplete="off">'."\n"
                . '</td>'."\n";
            echo '</tr>'."\n";
            echo '<tr>'."\n";
            echo '<th scope="row"><label for="okvoauth_google_client_secret">'.__('Client secret', 'okv-oauth').'</label></th>'."\n";
            echo '<td>'."\n"
                . '<input id="okvoauth_google_client_secret" class="regular-text" type="text" name="okvoauth_google_client_secret" value="'.$okvoauth_google_client_secret_val.'" autocomplete="off">'."\n"
                . '</td>'."\n";
            echo '</tr>'."\n";
            echo '</table>'."\n";
            $mycon_google = new \OKVOAuth\MyConnect\Google();
            echo '<p class="description">'."\n";
            echo sprintf(__('Please visit <a href="%s" target="gg_project">Google Projects</a> and create or open your project.', 'okv-oauth'), 'https://console.developers.google.com/project').'<br>'."\n";
            echo __('Go to APIs &amp; auth &gt; APIs and enable Google+ API.', 'okv-oauth').'<br>'."\n";
            echo __('Go to Credentials.', 'okv-oauth').'<br>'."\n";
            echo __('Click on Create new Client ID.', 'okv-oauth').'<br>'."\n";
            echo __('Authorized JavaScript origins: enter <strong>'.$mycon_google->getGoogleLoginUrl().'</strong> where your WordPress installed. You may insert one more copy by include both http and https.', 'okv-oauth').'<br>'."\n";
            echo __('Authorized redirect URIs: enter <strong>'.$mycon_google->getGoogleLoginUrl().'</strong>, <strong>'.$mycon_google->getGoogleLoginUrl().'?action=login</strong>, <strong>'.$mycon_google->getGoogleLoginUrl().'?action=register</strong>. You may insert one more copy by include both http and https.', 'okv-oauth').'<br>'."\n";
            echo __('Use Client ID and Client secret generated from there.', 'okv-oauth').'<br>'."\n";
            echo __('Go to Consent screen and enter your information there.', 'okv-oauth').'<br>'."\n";
            
            echo '</p>'."\n";
            
            submit_button();
            echo '</form>'."\n";
            echo '</div><!--.wrap-->'."\n";

            // clear unused variables.
            unset($okvoauth_google_client_id_val, $okvoauth_google_client_secret_val, $okvoauth_login_method_val);
        }// pluginSettingsPage


    }// end class ------------------------------------------------------------------
}