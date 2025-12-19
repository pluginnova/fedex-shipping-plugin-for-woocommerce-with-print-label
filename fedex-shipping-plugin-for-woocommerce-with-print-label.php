<?php
/**
 * Plugin Name: FedEx Shipping Plugin for WooCommerce with Print Label
 * Plugin URI:  https://pluginnova.com
 * Description: FedEx Shipping Plugin for WooCommerce with Print Label – Phase 1 & 2.
 * Version:     1.0.0
 * Author:      Pluginnova
 * Author URI:  https://pluginnova.com
 * Text Domain: fedex-shipping-plugin
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Initialize FedEx Plugin
 */
add_action( 'plugins_loaded', 'pluginnova_fedex_init' );

function pluginnova_fedex_init() {

    /**
     * Load GLOBAL FedEx settings tab
     * (WooCommerce → Settings → Shipping → FedEx)
     */
    if ( class_exists( 'WooCommerce' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-fedex-settings.php';
        Pluginnova_Fedex_Settings::init();
    }

    require_once plugin_dir_path( __FILE__ ) . 'includes/class-fedex-api.php';


    /**
     * Load SHIPPING METHOD (Zones)
     */
    if ( class_exists( 'WC_Shipping_Method' ) ) {

        require_once plugin_dir_path( __FILE__ ) . 'includes/class-fedex-shipping-method.php';

        add_filter( 'woocommerce_shipping_methods', 'pluginnova_add_fedex_shipping_method' );
    }
}

/**
 * Register FedEx Shipping Method
 */
function pluginnova_add_fedex_shipping_method( $methods ) {
    $methods['pluginnova_fedex'] = 'Pluginnova_Fedex_Shipping_Method';
    return $methods;
}
