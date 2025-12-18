<?php
/**
 * Plugin Name: FedEx Shipping Plugin for WooCommerce with Print Label
 * Plugin URI:  https://pluginnova.com
 * Description: FedEx Shipping Plugin for WooCommerce with Print Label – Phase 1 Skeleton.
 * Version:     1.0.0
 * Author:      Pluginnova
 * Author URI:  https://pluginnova.com
 * Text Domain: fedex-shipping-plugin
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 */
add_action( 'plugins_loaded', 'pluginnova_fedex_shipping_init', 11 );

function pluginnova_fedex_shipping_init() {

    if ( ! class_exists( 'WC_Shipping_Method' ) ) {
        return;
    }

    /**
     * Include shipping method class
     */
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-fedex-shipping-method.php';

    /**
     * Register the shipping method
     */
    add_filter( 'woocommerce_shipping_methods', 'pluginnova_add_fedex_shipping_method' );

    function pluginnova_add_fedex_shipping_method( $methods ) {
        $methods['pluginnova_fedex'] = 'Pluginnova_Fedex_Shipping_Method';
        return $methods;
    }
}
