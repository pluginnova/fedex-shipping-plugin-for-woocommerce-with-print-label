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
        $this->supports           = array(
            'shipping-zones',
            'instance-settings',
        );

        $this->init();
    }

    /**
     * Initialize settings
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
     * Define settings fields
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
            'title'       => __( 'Method Title', 'fedex-shipping-plugin' ),
            'type'        => 'text',
            'description' => __( 'Title shown to customers during checkout.', 'fedex-shipping-plugin' ),
            'default'     => __( 'FedEx Shipping', 'fedex-shipping-plugin' ),
            'desc_tip'    => true,
        ),

        // Dummy field to keep WooCommerce JS happy (Phase 1)
        'dummy_cost' => array(
            'type'    => 'hidden',
            'default' => '0',
        ),
    );
}


    /**
     * Calculate shipping (Dummy rate for Phase 1)
     */
    public function calculate_shipping( $package = array() ) {

        $rate = array(
            'id'    => $this->id . '_rate',
            'label' => $this->title,
            'cost'  => 0, // Dummy rate for Phase 1
        );

        $this->add_rate( $rate );
    }
}
