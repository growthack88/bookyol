<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Geo_Router {

    private $markets = array(
        'us'  => array( 'US', 'CA' ),
        'uk'  => array( 'GB' ),
        'eu'  => array( 'DE', 'FR', 'ES', 'IT', 'NL', 'SE', 'NO', 'DK', 'FI', 'AT', 'CH', 'BE', 'PL', 'PT', 'IE', 'CZ', 'RO', 'HU', 'GR', 'HR', 'BG', 'SK', 'SI', 'LT', 'LV', 'EE', 'LU', 'MT', 'CY' ),
        'gcc' => array( 'AE', 'SA', 'KW', 'QA', 'BH', 'OM', 'EG', 'JO', 'LB' ),
        'au'  => array( 'AU', 'NZ' ),
    );

    private $platform_priority = array(
        'us'     => array( 'everand', 'librofm', 'bookshop', 'ebookscom', 'kobo' ),
        'uk'     => array( 'everand', 'librofm', 'bookshop', 'ebookscom', 'kobo' ),
        'eu'     => array( 'ebookscom', 'librofm', 'kobo', 'everand' ),
        'gcc'    => array( 'ebookscom', 'kobo', 'jamalon' ),
        'au'     => array( 'everand', 'ebookscom', 'kobo', 'librofm' ),
        'global' => array( 'ebookscom', 'kobo', 'librofm' ),
    );

    private $country_code = null;

    public function get_country_code() {
        if ( $this->country_code !== null ) {
            return $this->country_code;
        }
        $ip = $this->get_ip();
        if ( empty( $ip ) || $this->is_private_ip( $ip ) ) {
            $this->country_code = 'US';
            return $this->country_code;
        }
        $cache_key = 'bookyol_geo_' . md5( $ip );
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) {
            $this->country_code = $cached;
            return $this->country_code;
        }
        if ( ! $this->check_rate_limit() ) {
            $this->country_code = 'US';
            return $this->country_code;
        }
        $response = wp_remote_get(
            'http://ip-api.com/json/' . rawurlencode( $ip ) . '?fields=countryCode',
            array( 'timeout' => 3 )
        );
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            $this->country_code = 'US';
            set_transient( $cache_key, 'US', HOUR_IN_SECONDS );
            return $this->country_code;
        }
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $cc   = ( is_array( $body ) && ! empty( $body['countryCode'] ) ) ? strtoupper( $body['countryCode'] ) : 'US';
        set_transient( $cache_key, $cc, DAY_IN_SECONDS );
        $this->country_code = $cc;
        return $cc;
    }

    public function get_market() {
        $cc = $this->get_country_code();
        foreach ( $this->markets as $market => $countries ) {
            if ( in_array( $cc, $countries, true ) ) {
                return $market;
            }
        }
        return 'global';
    }

    public function get_platform_priority() {
        $market = $this->get_market();
        return isset( $this->platform_priority[ $market ] ) ? $this->platform_priority[ $market ] : $this->platform_priority['global'];
    }

    private function get_ip() {
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $parts = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
            $ip    = trim( $parts[0] );
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }
        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }
        return '';
    }

    private function is_private_ip( $ip ) {
        return ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
    }

    private function check_rate_limit() {
        $key   = 'bookyol_geo_ratelimit';
        $count = (int) get_transient( $key );
        if ( $count >= 45 ) {
            return false;
        }
        set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
        return true;
    }
}
