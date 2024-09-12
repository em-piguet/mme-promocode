<?php

/**
 * @see              wonderweb.ch
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       ManageMe Promo Code
 * Plugin URI:        manage-me.pro
 * Description:       Shortcode pour le code Promo -> [manageme_promocode societyid="XXX"]
 * Version:           1.0.61
 * Author:            Wonderweb
 * Author URI:        wonderweb.ch
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: em-piguet/mme-promocode
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    exit;
}

define('MANAGEME_PROMOCODE_VERSION', '1.0.61');

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
}

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

    $github_user = 'votre-nom-utilisateur';
    $github_repo = 'nom-du-repo';

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

run_manageme_promo_api();
