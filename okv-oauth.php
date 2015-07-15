<?php
/**
 * Plugin Name: Okvee OAuth
 * Plugin URI: http://okvee.net/
 * Description: Use OAuth such as Google to login and register.
 * Version: 0.1
 * Author: Vee Winch
 * Author URI: http://okvee.net
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Text Domain: okv-oauth
 * Domain Path: /languages/
 * 
 * @package okv-oauth
 */

// define this plugin file path
if (!defined('OKV_OAUTH_FILE')) {
    define('OKV_OAUTH_FILE', __FILE__);
}

// include this plugin's autoload
require_once __DIR__.'/autoload.php';

// begins plugin class
$okvoauth = new \OKVOAuth\Library\OKVOAuth();

// add actions and filters listening.
$okvoauth->addActionsFilters();

// initiate plugin.
$okvoauth->pluginInit();

// done. remove un-wanted variable.
unset($okvoauth);

