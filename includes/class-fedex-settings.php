<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pluginnova_Fedex_Settings {

    /**
     * Initialize settings
     */
    public static function init() {

        // Add FedEx as a section under WooCommerce → Shipping
        add_filter( 'woocommerce_get_sections_shipping', array( __CLASS__, 'add_fedex_section' ) );

        // Load FedEx settings fields
        add_filter( 'woocommerce_get_settings_shipping', array( __CLASS__, 'get_fedex_settings' ), 10, 2 );

        // Handle Test Connection button
        add_action( 'admin_init', array( __CLASS__, 'handle_test_connection' ) );
    }

    /**
     * Add FedEx section
     */
    public static function add_fedex_section( $sections ) {
        $sections['fedex'] = __( 'FedEx', 'fedex-shipping-plugin' );
        return $sections;
    }

    /**
     * FedEx settings fields
     */
    public static function get_fedex_settings( $settings, $current_section ) {

        if ( 'fedex' !== $current_section ) {
            return $settings;
        }

        return array(

            // Section title
            array(
                'title' => __( 'FedEx General Settings', 'fedex-shipping-plugin' ),
                'type'  => 'title',
                'id'    => 'pluginnova_fedex_settings_title',
            ),

            // Enable FedEx
            array(
                'title'   => __( 'Enable FedEx', 'fedex-shipping-plugin' ),
                'id'      => 'pluginnova_fedex_enabled',
                'type'    => 'checkbox',
                'default' => 'no',
            ),

            // Environment
            array(
                'title'   => __( 'Environment', 'fedex-shipping-plugin' ),
                'id'      => 'pluginnova_fedex_environment',
                'type'    => 'select',
                'options' => array(
                    'sandbox'    => __( 'Sandbox (Test)', 'fedex-shipping-plugin' ),
                    'production' => __( 'Production (Live)', 'fedex-shipping-plugin' ),
                ),
                'default' => 'sandbox',
            ),

            // Account Number
            array(
                'title' => __( 'FedEx Account Number', 'fedex-shipping-plugin' ),
                'id'    => 'pluginnova_fedex_account_number',
                'type'  => 'text',
            ),

            // Client ID (API Key)
            array(
                'title' => __( 'Client ID (API Key)', 'fedex-shipping-plugin' ),
                'id'    => 'pluginnova_fedex_api_key',
                'type'  => 'text',
            ),

            // Client Secret
            array(
                'title' => __( 'Client Secret', 'fedex-shipping-plugin' ),
                'id'    => 'pluginnova_fedex_api_secret',
                'type'  => 'password',
            ),

            // Test Connection button
            array(
                'type' => 'button',
                'title' => __( 'Test FedEx Connection', 'fedex-shipping-plugin' ),
                'id' => 'pluginnova_fedex_test_connection',
                'css' => 'min-width:200px;',
                'desc' => __( 'Click to verify FedEx API credentials.', 'fedex-shipping-plugin' ),
            ),

            // End section
            array(
                'type' => 'sectionend',
                'id'   => 'pluginnova_fedex_settings_end',
            ),
        );
    }

    /**
     * Handle Test Connection button click
     */
    public static function handle_test_connection() {

        if ( ! isset( $_POST['pluginnova_fedex_test_connection'] ) ) {
            return;
        }

        if ( ! class_exists( 'Pluginnova_Fedex_API' ) ) {
            return;
        }

        $result = Pluginnova_Fedex_API::test_connection();

        add_action( 'admin_notices', function() use ( $result ) {

            if ( $result['success'] ) {
                echo '<div class="notice notice-success"><p>' . esc_html( $result['message'] ) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html( $result['message'] ) . '</p></div>';
            }

        } );
    }
}
