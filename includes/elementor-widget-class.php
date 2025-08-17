<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class WPGR_Elementor_Widget extends Widget_Base {
    public function get_name() {
        return 'wpgr_recommendations';
    }
    public function get_title() {
        return __('Google Recomendações', 'wpgr');
    }
    public function get_icon() {
        return 'eicon-star';
    }
    public function get_categories() {
        return ['general'];
    }
    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Conteúdo', 'wpgr'),
            ]
        );
        $this->add_control(
            'star_level',
            [
                'label' => __('Nível de Estrelas', 'wpgr'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => '1 estrela',
                    '2' => '2 estrelas',
                    '3' => '3 estrelas',
                    '4' => '4 estrelas',
                    '5' => '5 estrelas',
                ],
                'default' => get_option('wpgr_star_level', 3),
            ]
        );
        $this->add_control(
            'orientation',
            [
                'label' => __('Orientação', 'wpgr'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'horizontal' => 'Horizontal',
                    'vertical' => 'Vertical',
                ],
                'default' => get_option('wpgr_scroll_orientation', 'horizontal'),
            ]
        );
        $this->end_controls_section();
    }
    protected function render() {
        $settings = $this->get_settings_for_display();
        echo do_shortcode('[wpgr_recommendations star_level="' . esc_attr($settings['star_level']) . '" orientation="' . esc_attr($settings['orientation']) . '"]');
    }
}
