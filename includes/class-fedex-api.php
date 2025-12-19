<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pluginnova_Fedex_API {

    public static function get_access_token() {

        $token = get_transient( 'pluginnova_fedex_access_token' );
        if ( $token ) {
            return $token;
        }

        $client_id     = get_option( 'pluginnova_fedex_api_key' );
        $client_secret = get_option( 'pluginnova_fedex_api_secret' );

        if ( empty( $client_id ) || empty( $client_secret ) ) {
            error_log( 'FedEx OAuth Error: Missing client credentials' );
            return false;
        }

        $response = wp_remote_post(
            'https://apis-sandbox.fedex.com/oauth/token',
            array(
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'body' => http_build_query( array(
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                ) ),
                'timeout' => 20,
            )
        );

        if ( is_wp_error( $response ) ) {
            error_log( 'FedEx OAuth HTTP Error: ' . $response->get_error_message() );
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['access_token'] ) ) {
            error_log( 'FedEx OAuth Failed: ' . print_r( $body, true ) );
            return false;
        }

        set_transient(
            'pluginnova_fedex_access_token',
            $body['access_token'],
            (int) $body['expires_in'] - 60
        );

        return $body['access_token'];
    }
}
