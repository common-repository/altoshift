<?php

namespace Altoshift\WordPress\Page;

defined('ABSPATH') or die;

use Altoshift\WordPress\AltoshiftWordpressPlugin;

class Settings {
    public function __construct() {
        add_menu_page(
            'dashicons-search',
            'Altoshift',
            'admin_init',
            'altoshift-wordpress',
            null,
            'dashicons-search'
        );
        add_submenu_page(
            'altoshift-wordpress',
            'Layer',
            'Layer',
            'manage_options',
            'altoshift-layer',
            function () {
                include AltoshiftWordpressPlugin::getInstance()->getViewsDir() . 'admin/settings/layer.php';
            }
        );
        add_submenu_page(
            'altoshift-wordpress',
            'Data Feed',
            'Data Feed',
            'manage_options',
            'altoshift-feed',
            function () {
                include AltoshiftWordpressPlugin::getInstance()->getViewsDir() . 'admin/settings/feed.php';
            }
        );
    }

    public function get_sections() {
        $sections = array(
            ''           => __( 'Altoshift Layer', 'woocommerce-altoshift' ),
            'feed'  => __( 'Data Feed', 'woocommerce-altoshift' ),
        );

        return $sections;
    }

    public function get_settings($current_section) {
        global $current_section;
        $current_section='';
        switch ($current_section) {
            case '':
                return include AltoshiftWordpressPlugin::getInstance()->getViewsDir() . 'admin/settings/layer.php';

            case 'feed':
                return include AltoshiftWordpressPlugin::getInstance()->getViewsDir() . 'admin/settings/feed.php';
        }
    }

    public function save() {
        parent::save();

        if (isset($_POST['altoshift_layer_code'])) {
            update_option('altoshift_layer_code', sanitize_text_field($_POST['altoshift_layer_code']));
        }
    }

    public function before_settings() {
        global $current_section;

        if ($current_section == 'feed') {
            include AltoshiftWordpressPlugin::getInstance()->getViewsDir() . 'admin/settings/feed-url.php';
        }
    }
}