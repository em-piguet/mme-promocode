<?php

/**
 * @see              wonderweb.ch
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       ManageMe Promo Code
 * Plugin URI:        manage-me.pro
 * Description:       Shortcode pour le code Promo -> [manageme_promocode societyid="XXX"]
 * Version:           1.0.5
 * Author:            Wonderweb
 * Author URI:        wonderweb.ch
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    exit;
}

define('MANAGEME_PROMOCODE_VERSION', '1.0.4');

require plugin_dir_path(__FILE__).'inc/manageme-promocode-public.php';

if (is_admin()) {
    // require_once plugin_dir_path( __FILE__ ) .'inc/manageme-slider-api-admin.php';
}

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
    wp_enqueue_script('manageme-promo-api-js', plugin_dir_url(__FILE__).'js/manageme-promo.js', ['jquery'], MANAGEME_PROMOCODE_VERSION, false);
    wp_enqueue_style('manageme-promo-api-css', plugin_dir_url(__FILE__).'css/manageme-promo.css', [], MANAGEME_PROMOCODE_VERSION, 'all');
}

run_manageme_promo_api();
