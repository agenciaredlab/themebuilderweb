<?php
/**
 * Autoloader
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Autoloader {

    private static $classes_map;

    public static function run() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function get_classes_map() {
        if (!self::$classes_map) {
            self::init_classes_map();
        }
        return self::$classes_map;
    }

    private static function init_classes_map() {
        self::$classes_map = [
            'TBP_Utils' => 'core/class-utils.php',
            'TBP_Assets' => 'core/class-assets.php',
            'TBP_Controls_Manager' => 'core/class-controls-manager.php',
            'TBP_Schemes_Manager' => 'core/class-schemes-manager.php',
            'TBP_Responsive' => 'core/class-responsive.php',
            'TBP_Conditions' => 'core/class-conditions.php',
            'TBP_DB' => 'core/class-db.php',
            'TBP_Documents_Manager' => 'core/class-documents-manager.php',
            'TBP_Modules_Manager' => 'modules/class-modules-manager.php',
            'TBP_Widgets_Manager' => 'widgets/class-widgets-manager.php',
            'TBP_Widget_Base' => 'widgets/class-widget-base.php',
            'TBP_Dynamic_Tags_Manager' => 'dynamic/class-dynamic-tags-manager.php',
            'TBP_Theme_Builder' => 'templates/class-theme-builder.php',
            'TBP_Popup_Manager' => 'templates/class-popup-manager.php',
            'TBP_Form_Manager' => 'forms/class-form-manager.php',
            'TBP_REST_API' => 'api/class-rest-api.php',
            'TBP_Integrations_Manager' => 'integrations/class-integrations-manager.php',
        ];
    }

    private static function autoload($class) {
        if (strpos($class, 'TBP_') !== 0) {
            return;
        }

        $classes_map = self::get_classes_map();

        if (!isset($classes_map[$class])) {
            return;
        }

        $filename = TBP_INCLUDES_DIR . $classes_map[$class];

        if (is_readable($filename)) {
            require $filename;
        }
    }
}

TBP_Autoloader::run();
