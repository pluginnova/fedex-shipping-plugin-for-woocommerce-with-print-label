<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pluginnova_Fedex_Shipping_Method extends WC_Shipping_Method {

    /**
     * Constructor
     */
    public function __construct( $instance_id = 0 ) {

        $this->id                 = 'pluginnova_fedex';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'FedEx Shipping (Pluginnova)', 'fedex-shipping-plugin' );
        $this->method_description = __( 'FedEx Shipping Plugin for WooCommerce with Print Label.', 'fedex-shipping-plugin' );

        $this->supports = array(
            'shipping-zones',
            'instance-settings',
        );

        $this->init();
    }

    /**
     * Init settings
     */
    public function init() {

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option( 'enabled' );
        $this->title   = $this->get_option( 'title' );

        add_action(
            'woocommerce_update_options_shipping_' . $this->id,
            array( $this, 'process_admin_options' )
        );
    }

    /**
     * Zone-level settings
     */
    public function init_form_fields() {

        $this->form_fields = array(

            'enabled' => array(
                'title'   => __( 'Enable / Disable', 'fedex-shipping-plugin' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable FedEx Shipping', 'fedex-shipping-plugin' ),
                'default' => 'yes',
            ),

            'title' => array(
                'title'   => __( 'Method Title', 'fedex-shipping-plugin' ),
                'type'    => 'text',
                'default' => __( 'FedEx Shipping', 'fedex-shipping-plugin' ),
            ),
        );
    }

    /**
     * Calculate shipping (LIVE FedEx Rate)
     */
    public function calculate_shipping( $package = array() ) {

        /* ---------------- BASIC CHECKS ---------------- */

        if ( get_option( 'pluginnova_fedex_enabled' ) !== 'yes' ) {
            return;
        }

        $token = Pluginnova_Fedex_API::get_access_token();
        if ( empty( $token ) ) {
            error_log( 'FedEx Error: Missing OAuth token' );
            return;
        }

        $account_number = get_option( 'pluginnova_fedex_account_number' );
        if ( empty( $account_number ) ) {
            error_log( 'FedEx Error: Missing account number' );
            return;
        }

        /* ---------------- CALCULATE WEIGHT ---------------- */

        $weight = 0;
        foreach ( $package['contents'] as $item ) {
            if ( $item['data']->has_weight() ) {
                $weight += (float) $item['data']->get_weight() * $item['quantity'];
            }
        }

        if ( $weight <= 0 ) {
            $weight = 1;
        }

        /* ---------------- ADDRESSES ---------------- */

        $store_country  = WC()->countries->get_base_country();
        $store_state    = WC()->countries->get_base_state();
        $store_postcode = WC()->countries->get_base_postcode();

        $destination = $package['destination'];

        /* ---------------- FEDEX REQUEST BODY ---------------- */

        $request_body = array(
    'accountNumber' => array(
        'value' => $account_number,
    ),
    'requestedShipment' => array(
    'rateRequestType' => array( 'ACCOUNT', 'LIST' ),

    'shipper' => array(
        'address' => array(
            'postalCode' => $store_postcode,
            'countryCode' => $store_country,
            'stateOrProvinceCode' => $store_state,
        ),
    ),

    'recipient' => array(
        'address' => array(
            'postalCode' => $destination['postcode'],
            'countryCode' => $destination['country'],
            'stateOrProvinceCode' => $destination['state'],
        ),
    ),

    'pickupType'    => 'DROPOFF_AT_FEDEX_LOCATION',
    'packagingType' => 'YOUR_PACKAGING',

    'requestedPackageLineItems' => array(
        array(
            'weight' => array(
                'units' => 'LB',
                'value' => $weight,
            ),
        ),
    ),
),
);

error_log( 'FedEx Rate Request: ' . print_r( $request_body, true ) );


        // LOG REQUEST
        error_log( 'FedEx Rate Request: ' . print_r( $request_body, true ) );

        /* ---------------- API CALL ---------------- */

        $response = wp_remote_post(
            'https://apis-sandbox.fedex.com/rate/v1/rates/quotes',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ),
                'body'    => wp_json_encode( $request_body ),
                'timeout' => 20,
            )
        );

        if ( is_wp_error( $response ) ) {
            error_log( 'FedEx HTTP Error: ' . $response->get_error_message() );
            return;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // LOG RESPONSE
        error_log( 'FedEx Rate Response: ' . print_r( $body, true ) );

        /* ---------------- ADD RATE ---------------- */

       if ( ! empty( $body['output']['rateReplyDetails'] ) ) {

    foreach ( $body['output']['rateReplyDetails'] as $service ) {

        $service_code = $service['serviceType'] ?? '';
        $service_name = $service['serviceDescription']['description'] ?? $service_code;

        if ( empty( $service['ratedShipmentDetails'] ) ) {
            continue;
        }

        foreach ( $service['ratedShipmentDetails'] as $rate ) {

            if ( ! in_array( $rate['rateType'], array( 'ACCOUNT', 'LIST' ), true ) ) {
                continue;
            }

            // Handle both FedEx response formats
            if ( is_array( $rate['totalNetCharge'] ) && isset( $rate['totalNetCharge']['amount'] ) ) {
                $cost = (float) $rate['totalNetCharge']['amount'];
            } else {
                $cost = (float) $rate['totalNetCharge'];
            }

            if ( $cost <= 0 ) {
                continue;
            }

            $this->add_rate( array(
                'id'    => $this->id . '_' . strtolower( $service_code ),
                'label' => $service_name,
                'cost'  => $cost,
            ) );

            break; // one rate per service
        }
    }
}

    }
}
