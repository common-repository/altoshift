<?php

namespace Altoshift\Wordpress;

defined('ABSPATH') or die;

class Frontend
{
    public function __construct()
    {
        $this->injectFrontendLayerScript();
    }

    private function injectFrontendLayerScript()
    {
        $layerEnabled = get_option('altoshift_layer_enabled', 'no');
        if ($layerEnabled === 'yes') {
            $scriptCode = get_option('altoshift_layer_code', '');

            if (strlen($scriptCode) > 0) {
                add_action('wp_footer', function () use ($scriptCode) {
                    echo stripslashes($scriptCode);
                });
            }
        }
    }
}