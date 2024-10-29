<?php

/*
Plugin Name:  Altoshift Wordpress Plugin
Description:  Plugin for Altoshift search integration into your Wordpress shop
Version:      1.0.2
Author:       Altoshift
Author URI:  https://altoshift.com/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/


namespace Altoshift\WordPress;

defined('ABSPATH') or die;

class AltoshiftWordpressPlugin
{
    // const STATS_ENDPOINT = 'http://127.0.0.1:8017/statsendpoint/stats';
    const STATS_ENDPOINT = 'https://api.altoshift.com/statsendpoint/stats';
    private static $_instance = null;
    private $_viewsDir = null;
    private $_pluginDir = null;

    public function __construct()
    {
        $this->_pluginDir = plugin_dir_path(__FILE__);
        $this->_viewsDir = $this->getPluginDir() . 'includes/views/';
        require $this->getPluginDir() . 'includes/classes/autoload.php';

        new \Altoshift\WordPress\Admin();
        new \Altoshift\WordPress\Frontend();

        $className = __CLASS__;

        add_action('init', function () use ($className) {
            call_user_func(array($className, 'registerCustomUrls'));
        });
    }

    public static function registerCustomUrls()
    {
        \Altoshift\WordPress\Feed\Feed::registerUrls();
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getViewsDir()
    {
        return $this->_viewsDir;
    }

    public function getPluginDir()
    {
        return $this->_pluginDir;
    }

    public static function postInstallationStats($data) {
        wp_remote_post(self::STATS_ENDPOINT, array('body' => $data, 'blocking' => false));
    }

    public static function onPluginEnabled()
    {
        try {
            $pluginData = get_plugin_data(__FILE__);
            self::postInstallationStats(array(
            	'event' => 'pluginInstall',
            	'data' => array(
			'pluginType' => 'Altoshift-Wordpress',
            		'pluginVersion' => $pluginData['Version'],
            		'host' => get_site_url(),
            		'ip' => $_SERVER['SERVER_ADDR'],
            		'locale' => get_locale(),
            		),
            ));
        } catch (Exception $e) {
        }
        self::getInstance()->registerCustomUrls();
        flush_rewrite_rules();
    }

    public static function onPluginDisabled()
    {
        flush_rewrite_rules();
    }

    public static function addProductIdMetaTag()
	{
		$post = get_post();
		if ($post !== null)
		{
			if($post->post_type === 'product'){
				echo "<meta name=\"productId\" content=\"$post->ID\" />";
			}
			elseif($post->post_type === 'post'){
				echo "<meta name=\"productId\" content=\"$post->ID\" />";
				echo "<meta name=\"postId\" content=\"$post->ID\" />";
			}
		}
	}
}

register_activation_hook(__FILE__, array('\Altoshift\WordPress\AltoshiftWordpressPlugin', 'onPluginEnabled'));
register_deactivation_hook(__FILE__, array('\Altoshift\WordPress\AltoshiftWordpressPlugin', 'onPluginDisabled'));

add_action('plugins_loaded', array('\Altoshift\WordPress\AltoshiftWordpressPlugin', 'getInstance'), 0);
add_action('wp_head', array('\Altoshift\WordPress\AltoshiftWordpressPlugin', 'addProductIdMetaTag'));

