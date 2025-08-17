<?php
/*
Plugin Name: WP Google Recommendations
Description: Exibe recomendações do Google com opções de configuração de estrelas, rolagem e integração com Elementor.
Version: 1.0.1
Author: Seu Nome
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// ===== GitHub Update Metadata =====
if (!defined('WPGR_GH_OWNER')) { define('WPGR_GH_OWNER', 'RapportTecnologia'); }
if (!defined('WPGR_GH_REPO'))  { define('WPGR_GH_REPO',  'wp-google-recomendations'); }

// ===== Update Checker (GitHub) =====
function wpgr_get_github_latest_release() {
    $transient_key = 'wpgr_github_latest_release';
    $cached = get_transient($transient_key);
    if ($cached) { return $cached; }
    $api = sprintf('https://api.github.com/repos/%s/%s/releases/latest', WPGR_GH_OWNER, WPGR_GH_REPO);
    $resp = wp_remote_get($api, [
        'headers' => [ 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'WordPress-WPGR' ],
        'timeout' => 15,
    ]);
    if (is_wp_error($resp)) { return false; }
    $code = wp_remote_retrieve_response_code($resp);
    if ($code !== 200) { return false; }
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (!is_array($body) || empty($body['tag_name'])) { return false; }
    $ver = ltrim(trim($body['tag_name']), 'vV');
    $zip = sprintf('https://github.com/%s/%s/archive/refs/tags/%s.zip', WPGR_GH_OWNER, WPGR_GH_REPO, rawurlencode($body['tag_name']));
    $result = [
        'version' => $ver,
        'tag' => $body['tag_name'],
        'zipball' => $zip,
        'html_url' => isset($body['html_url']) ? $body['html_url'] : sprintf('https://github.com/%s/%s/releases', WPGR_GH_OWNER, WPGR_GH_REPO),
    ];
    set_transient($transient_key, $result, WEEK_IN_SECONDS);
    set_transient('wpgr_github_last_check', time(), WEEK_IN_SECONDS);
    return $result;
}

function wpgr_inject_update_info($transient) {
    if (empty($transient) || empty($transient->checked)) { return $transient; }
    if (!function_exists('get_plugin_data')) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
    $plugin_file = plugin_basename(__FILE__);
    $plugin_data = get_plugin_data(__FILE__, false, false);
    $current_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '0.0.0';
    $release = wpgr_get_github_latest_release();
    if (!$release) { return $transient; }
    if (version_compare($release['version'], $current_version, '>')) {
        $obj = (object) [
            'slug' => dirname($plugin_file),
            'plugin' => $plugin_file,
            'new_version' => $release['version'],
            'package' => $release['zipball'],
            'url' => $release['html_url'],
            'tested' => '6.5',
            'requires' => '5.8',
        ];
        $transient->response[$plugin_file] = $obj;
        update_option('wpgr_latest_available', $release, false);
    } else {
        delete_option('wpgr_latest_available');
    }
    return $transient;
}
add_filter('pre_set_site_transient_update_plugins', 'wpgr_inject_update_info');

function wpgr_plugins_api($result, $action, $args) {
    if ($action !== 'plugin_information') { return $result; }
    $plugin_file = plugin_basename(__FILE__);
    if (empty($args->slug) || $args->slug !== dirname($plugin_file)) { return $result; }
    $release = wpgr_get_github_latest_release();
    if (!$release) { return $result; }
    return (object) [
        'name' => 'WP Google Recommendations',
        'slug' => dirname($plugin_file),
        'version' => $release['version'],
        'download_link' => $release['zipball'],
        'homepage' => $release['html_url'],
        'sections' => [
            'description' => __('Exibe recomendações do Google com integração Elementor.', 'wp-google-recomendations'),
            'changelog' => __('Consulte o CHANGELOG.md no repositório.', 'wp-google-recomendations'),
        ],
    ];
}
add_filter('plugins_api', 'wpgr_plugins_api', 10, 3);

function wpgr_admin_notice_new_release() {
    if (!current_user_can('update_plugins')) { return; }
    $release = get_option('wpgr_latest_available');
    if (!$release || empty($release['version'])) { return; }
    if (!function_exists('get_plugin_data')) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
    $plugin_data = get_plugin_data(__FILE__, false, false);
    $current_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '0.0.0';
    if (version_compare($release['version'], $current_version, '>')) {
        $update_url = admin_url('plugins.php');
        echo '<div class="notice notice-info is-dismissible"><p>'
            . esc_html(sprintf(__('Nova versão do WP Google Recommendations disponível: %s (você está na %s). Atualize em Plugins.', 'wp-google-recomendations'), $release['version'], $current_version))
            . ' <a href="' . esc_url($release['html_url']) . '" target="_blank">' . esc_html__('Notas da versão', 'wp-google-recomendations') . '</a>'
            . ' | <a href="' . esc_url($update_url) . '">' . esc_html__('Ir para Plugins', 'wp-google-recomendations') . '</a>'
            . '</p></div>';
    } else {
        delete_option('wpgr_latest_available');
    }
}
add_action('admin_notices', 'wpgr_admin_notice_new_release');

function wpgr_maybe_auto_update($update, $item) {
    $plugin_file = plugin_basename(__FILE__);
    if (!empty($item->plugin) && $item->plugin === $plugin_file) {
        $enabled = get_option('wpgr_enable_auto_update') ? true : false;
        return $enabled;
    }
    return $update;
}
add_filter('auto_update_plugin', 'wpgr_maybe_auto_update', 10, 2);

function wpgr_plugin_action_links($links) {
    $settings_url = admin_url('admin.php?page=wpgr-admin');
    $settings_link = '<a href="' . esc_url($settings_url) . '">' . esc_html__('Configurações', 'wp-google-recomendations') . '</a>';
    $enabled = (bool) get_option('wpgr_enable_auto_update');
    $action = $enabled ? 'disable' : 'enable';
    $label = $enabled ? __('Desativar atualização automática', 'wp-google-recomendations') : __('Ativar atualização automática', 'wp-google-recomendations');
    $toggle_url = wp_nonce_url(admin_url('admin-post.php?action=wpgr_toggle_auto_update&do=' . $action), 'wpgr_toggle_auto_update', 'wpgr_nonce');
    $toggle_link = '<a href="' . esc_url($toggle_url) . '">' . esc_html($label) . '</a>';
    array_unshift($links, $settings_link, $toggle_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpgr_plugin_action_links');

function wpgr_toggle_auto_update() {
    if (!current_user_can('manage_options')) { wp_die(__('Sem permissão.', 'wp-google-recomendations')); }
    if (!isset($_GET['wpgr_nonce']) || !wp_verify_nonce($_GET['wpgr_nonce'], 'wpgr_toggle_auto_update')) { wp_die(__('Nonce inválido.', 'wp-google-recomendations')); }
    $do = isset($_GET['do']) ? sanitize_key($_GET['do']) : '';
    if ($do === 'enable') { update_option('wpgr_enable_auto_update', '1'); }
    elseif ($do === 'disable') { update_option('wpgr_enable_auto_update', '0'); }
    wp_safe_redirect(admin_url('plugins.php'));
    exit;
}
add_action('admin_post_wpgr_toggle_auto_update', 'wpgr_toggle_auto_update');

// Definir constantes do plugin
if (!defined('WPGR_PLUGIN_DIR')) {
    define('WPGR_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('WPGR_PLUGIN_URL')) {
    define('WPGR_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Inclusão dos arquivos principais
require_once WPGR_PLUGIN_DIR . 'includes/admin.php';
require_once WPGR_PLUGIN_DIR . 'includes/frontend.php';
require_once WPGR_PLUGIN_DIR . 'includes/elementor-widget.php';

// Ativação do plugin
function wpgr_activate() {
    // Código de ativação se necessário
}
register_activation_hook(__FILE__, 'wpgr_activate');

// Desativação do plugin
function wpgr_deactivate() {
    // Código de desativação se necessário
}
register_deactivation_hook(__FILE__, 'wpgr_deactivate');
