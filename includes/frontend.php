<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpgr_display_recommendations($atts = array()) {
    $api_key = get_option('wpgr_api_key');
    $place_id = get_option('wpgr_place_id');
    $star_level = get_option('wpgr_star_level', 3);
    $orientation = get_option('wpgr_scroll_orientation', 'horizontal');

    if (!$api_key || !$place_id) {
        return '<p>Configure o plugin WP Google Recomendações nas opções de administração.</p>';
    }

    $recommendations = wpgr_get_google_place_reviews($api_key, $place_id, $star_level);

    if (empty($recommendations)) {
        return '<p>Nenhuma recomendação encontrada.</p>';
    }

    $scroll_class = $orientation == 'vertical' ? 'wpgr-vertical' : 'wpgr-horizontal';
    ob_start();
    echo "<div class='wpgr-recommendations $scroll_class'>";
    foreach ($recommendations as $rec) {
        echo "<div class='wpgr-rec-item'>";
        echo "<strong>".esc_html($rec['author_name'])."</strong><br />";
        echo "<span>".esc_html($rec['text'])."</span><br />";
        echo "<span>Estrelas: ".esc_html($rec['rating'])."</span>";
        echo "</div>";
    }
    echo "</div>";
    return ob_get_clean();
}
add_shortcode('wpgr_recommendations', 'wpgr_display_recommendations');

function wpgr_get_google_place_reviews($api_key, $place_id, $star_level) {
    $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id=".urlencode($place_id)."&fields=review&key=".urlencode($api_key);
    $response = wp_remote_get($url);
    if (is_wp_error($response)) return [];
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (empty($data['result']['reviews'])) return [];
    $filtered = array_filter($data['result']['reviews'], function($review) use ($star_level) {
        return isset($review['rating']) && $review['rating'] >= $star_level;
    });
    return $filtered;
}

function wpgr_enqueue_styles() {
    wp_enqueue_style('wpgr-style', WPGR_PLUGIN_URL . 'assets/wpgr-style.css');
}
add_action('wp_enqueue_scripts', 'wpgr_enqueue_styles');
