<?php
/**
 * common properties and functions
 */


namespace OKVOAuth\Library;



if (!trait_exists('\\OKVOAuth\\Library\\OptionsTrait')) {
    trait OptionsTrait
    {


        // okvee oauth options name and its default value.
        public $options_name = [
            'okvoauth_login_method' => ['value' => 0], // 0=no use (wp only), 1=wp+oauth, 2=oauth only.
            'okvoauth_google_client_id' => ['value' => ''], 
            'okvoauth_google_client_secret' => ['value' => ''],
        ];


        /**
         * get wp options.
         * 
         * @param array $options_name options name in array. if leave this blank it will get all this plugin options.
         * @return array
         */
        public function getOptions(array $options_name = [])
        {
            $output = [];

            if (!empty($options_name)) {
                foreach ($options_name as $option_name) {
                    $output[$option_name] = get_option($option_name);
                }// endforeach;
                unset($option_name);
            } elseif (empty($options_name) && is_array($this->options_name)) {
                foreach ($this->options_name as $option_name => $option_key) {
                    $output[$option_name] = get_option($option_name);
                }
                unset($option_key, $option_name);
            }

            return $output;
        }// getOptions


        /**
         * check that is set to secure login or secure admin.
         * 
         * @return boolean return true on ssl use, false if not ssl
         */
        public function isSecureLogin()
        {
            $login_url = wp_login_url();
            if ((force_ssl_login() || force_ssl_admin()) && strtolower(substr($login_url,0,7)) == 'http://') {
                return true;
            }
            return false;
        }// isSecureLogin


    }
}