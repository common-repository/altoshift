<?php

namespace Altoshift\WordPress;

defined( 'ABSPATH' ) or die;

use Altoshift\WordPress\Page\Settings;

class Admin {
    public function __construct() {
        $this->addSettingsPage();
    }

    private function addSettingsPage() {
        add_action('admin_menu', function() {
            $settings[] = new Settings();
            return $settings;
        });
	}
}