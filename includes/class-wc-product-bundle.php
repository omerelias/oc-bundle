<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WC_Bundle_Product extends WC_Product {
    public function __construct( $product = 0 ) {
        $this->product_type = 'bundle';
        parent::__construct( $product );
    }

    // Ensure the product is purchasable
    public function is_purchasable() {
        // Check if the product is published
        if ( 'publish' !== $this->get_status() ) {
            return false;
        }

        // Check if the product is in stock or if backorders are allowed
        if ( ! $this->is_in_stock() && ! $this->backorders_allowed() ) {
            return false;
        }

        return true;
    }

    public function get_min_purchase_quantity() {
        // Get the custom minimum quantity from post meta
        $min_quantity = get_post_meta( $this->get_id(), '_bundle_general_minimum_quantity', true );

        // If the meta value exists and is a valid number, return it
        if ( $min_quantity !== '' && is_numeric( $min_quantity ) ) {
            return intval( $min_quantity );
        }

        // Fallback to the default logic if the meta value doesn't exist
        return $this->is_sold_individually() ? 1 : ( $this->backorders_allowed() || ! $this->managing_stock() ? -1 : $this->get_stock_quantity() );
    }


    // Define other necessary properties and methods
    public function get_type() {
        return 'bundle';
    }

    public static function init() {
        add_filter( 'woocommerce_product_class', [ __CLASS__, 'get_classname_for_product_type' ], 10, 2 );
        add_filter( 'product_type_selector', [ __CLASS__, 'add_bundle_product_type' ] );
    }

    public static function get_classname_for_product_type( $classname, $product_type ) {
        if ( 'bundle' === $product_type ) {
            $classname = 'WC_Bundle_Product';
        }
        return $classname;
    }

    public static function add_bundle_product_type( $types ) {
        $types['bundle'] = __( 'Bundle', 'wc-bundle-products' );
        return $types;
    }
}

// Register the bundle product type
add_action( 'plugins_loaded', [ 'WC_Bundle_Product', 'init' ], 20 );
?>
