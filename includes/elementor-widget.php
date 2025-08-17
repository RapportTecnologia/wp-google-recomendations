<?php
if (!defined('ABSPATH')) {
    exit;
}

// Verifica se Elementor está ativo
add_action('plugins_loaded', function() {
    if (defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')) {
        add_action('elementor/widgets/register', function($widgets_manager) {
            require_once __DIR__ . '/elementor-widget-class.php';
            $widgets_manager->register(new \WPGR_Elementor_Widget());
        });
    }
});
