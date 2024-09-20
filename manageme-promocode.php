<?php

/**
 * @see              wonderweb.ch
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       ManageMe Promo Code
 * Plugin URI:        manage-me.pro
 * Description:       Shortcode pour le code Promo -> [manageme_promocode societyid="XXX"]
 * Version:           1.0.8
 * Author:            Wonderweb
 * Text Domain:       mme-promocode
 * Author URI:        wonderweb.ch
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: em-piguet/mme-promocode
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    exit;
}

define('MANAGEME_PROMOCODE_VERSION', '1.0.8');

require plugin_dir_path(__FILE__).'inc/manageme-promocode-public.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_manageme_promo_api()
{
    add_shortcode('manageme_promocode', 'display_promocode');
    add_action('wp_enqueue_scripts', 'enqueue_manageme_promo_assets');
}

/**
 * Enqueue les scripts et styles du plugin.
 */
function enqueue_manageme_promo_assets()
{
    wp_enqueue_script('manageme-promo-api-js', plugin_dir_url(__FILE__).'js/manageme-promo.js', [], MANAGEME_PROMOCODE_VERSION, false);
    wp_enqueue_style('manageme-promo-api-css', plugin_dir_url(__FILE__).'css/manageme-promo.css', [], MANAGEME_PROMOCODE_VERSION, 'all');
    wp_localize_script('manageme-promo-api-js', 'manageme_promo', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'codeActivated' => __('Code activated', 'mme-promocode'),
        'goToCart' => __('Go to cart', 'mme-promocode'),
    ]);
}
function mme_promocode_load_textdomain()
{
    load_plugin_textdomain('mme-promocode', false, dirname(plugin_basename(__FILE__)).'/languages/');
}
add_action('plugins_loaded', 'mme_promocode_load_textdomain');
function manageme_promocode_check_for_updates($transient)
{
    if (empty($transient->checked)) {
        return $transient;
    }

    $plugin_slug = 'manageme-promocode/manageme-promocode.php';
    $plugin_data = get_plugin_data(__FILE__);
    $current_version = $plugin_data['Version'];

    $github_user = 'em-piguet';
    $github_repo = 'mme-promocode';

    $github_response = wp_remote_get("https://api.github.com/repos/{$github_user}/{$github_repo}/releases/latest");

    if (!is_wp_error($github_response) && 200 === wp_remote_retrieve_response_code($github_response)) {
        $github_data = json_decode(wp_remote_retrieve_body($github_response));

        if (version_compare($current_version, $github_data->tag_name, '<')) {
            $res = new stdClass();
            $res->slug = 'manageme-promocode';
            $res->plugin = $plugin_slug;
            $res->new_version = $github_data->tag_name;
            $res->tested = '6.2';
            $res->package = $github_data->zipball_url;
            $transient->response[$plugin_slug] = $res;
        }
    }

    return $transient;
}

function manageme_promocode_plugin_info($res, $action, $args)
{
    if ('plugin_information' !== $action) {
        return $res;
    }

    if ('manageme-promocode' !== $args->slug) {
        return $res;
    }

    $github_user = 'em-piguet';
    $github_repo = 'mme-promocode';

    $github_response = wp_remote_get("https://api.github.com/repos/{$github_user}/{$github_repo}/releases/latest");

    if (!is_wp_error($github_response) && 200 === wp_remote_retrieve_response_code($github_response)) {
        $github_data = json_decode(wp_remote_retrieve_body($github_response));

        $res = new stdClass();
        $res->name = 'ManageMe Promo Code';
        $res->slug = 'manageme-promocode';
        $res->version = $github_data->tag_name;
        $res->tested = '6.2';
        $res->requires = '5.0';
        $res->author = 'Wonderweb';
        $res->author_profile = 'https://wonderweb.ch';
        $res->download_link = $github_data->zipball_url;
        $res->trunk = $github_data->zipball_url;
        $res->last_updated = $github_data->published_at;
        $res->sections = [
            'description' => $github_data->body,
            'changelog' => $github_data->body,
        ];

        return $res;
    }

    return $res;
}

add_filter('pre_set_site_transient_update_plugins', 'manageme_promocode_check_for_updates');
add_filter('plugins_api', 'manageme_promocode_plugin_info', 20, 3);

add_action('wp_ajax_validate_promo_code', 'validate_promo_code_callback');
add_action('wp_ajax_nopriv_validate_promo_code', 'validate_promo_code_callback');

function validate_promo_code_callback()
{
    $society_id = $_POST['society_id'];
    $promo_code = $_POST['promo_code'];

    $shop_url = get_field('shop_url', 'option');

    if ($shop_url) {
        $api_url = $shop_url."/api/society/{$society_id}/promocode/{$promo_code}";
    } else {
        $api_url = "https://www.manage-me.pro/api/society/{$society_id}/promocode/{$promo_code}";
    }

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        wp_send_json_error([
            'Message' => __('An error has occured', 'mme-promocode'),
            'code' => $response->get_error_code(),
            'message' => $response->get_error_message(),
            'debug_api_url' => $api_url, // Ajout de l'URL de l'API pour le débogage
        ]);
    } else {
        $body = wp_remote_retrieve_body($response);
        $decoded_body = json_decode($body);

        // Ajout de l'URL de l'API à la réponse
        if (is_object($decoded_body)) {
            $decoded_body->debug_api_url = $api_url;
        } elseif (is_array($decoded_body)) {
            $decoded_body['debug_api_url'] = $api_url;
        }

        wp_send_json($decoded_body);
    }

    wp_die();
}

run_manageme_promo_api();
