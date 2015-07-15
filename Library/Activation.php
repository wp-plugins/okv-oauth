<?php
/**
 * Okvee OAuth WP plugin activation/deactivation/delete actions
 * 
 * @package okv-oauth
 */


namespace OKVOAuth\Library;


if (!class_exists('\\OKVOAuth\\Library\\Activation')) {
    /**
     * Activation deactivation, delete plugin actions class.
     */
    class Activation
    {


        use \OKVOAuth\Library\OptionsTrait;


        /**
         * activate the plugin by admin on wp plugin page.
         */
        public function activation()
        {
            if (is_array($this->options_name)) {
                foreach ($this->options_name as $option_name => $option_key) {
                    $test_get_value = get_option($option_name);
                    if ($test_get_value === false) {
                        // get the default option value
                        if (is_array($option_key) && array_key_exists('value', $option_key)) {
                            add_option($option_name, $option_key['value']);
                        }
                    }
                    unset($test_get_value);
                }// endforeach;
                unset($option_key, $option_name);
            }
        }// activation


        /**
         * deactivate the plugin hook.
         */
        public function deactivation()
        {
            // do something that will be happens on deactivate plugin.
        }// deactivation


        /**
         * delete the plugin.
         */
        public static function uninstall()
        {
            // do something that will be happens on delete plugin.
            // delete options.
            if (is_array($this->options_name)) {
                foreach ($this->options_name as $option_name => $option_key) {
                    $test_get_value = get_option($option_name);
                    if ($test_get_value !== false) {
                        delete_option($option_name);
                    }
                    unset($test_get_value);
                }// endforeach;
                unset($option_key, $option_name);
            }
        }// uninstall


    }// end class ------------------------------------------------------------------
}