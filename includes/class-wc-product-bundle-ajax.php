<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WC_Bundle_Product_Ajax {
    public static function init() {
        add_action( 'wp_ajax_add_bundle_item', [ __CLASS__, 'add_bundle_item' ] );
        add_action( 'wp_ajax_nopriv_add_bundle_item', [ __CLASS__, 'add_bundle_item' ] );

        add_action( 'wp_ajax_edit_bundle_item', [ __CLASS__, 'edit_bundle_item' ] );
        add_action( 'wp_ajax_nopriv_edit_bundle_item', [ __CLASS__, 'edit_bundle_item' ] );
    }

    public static function add_bundle_item() {
        check_ajax_referer( 'add_bundle_item_nonce', 'security' );

        // Retrieve the posted data and add the item to the bundle
        // Example implementation:
        $product_id = (int) $_POST['product_id'];
        $bundle_id = (int) $_POST['bundle_id'];

        // Add item to bundle logic here

        wp_send_json_success( [ 'message' => __( 'Item added to bundle', 'wc-bundle-products' ) ] );
    }

    public static function edit_bundle_item() {
        check_ajax_referer( 'edit_bundle_item_nonce', 'security' );

        // Retrieve the posted data and edit the item in the bundle
        // Example implementation:
        $product_id = intval( $_POST['product_id'] );
        $bundle_id = intval( $_POST['bundle_id'] );

        // Edit item in bundle logic here

        wp_send_json_success( [ 'message' => __( 'Item edited in bundle', 'wc-bundle-products' ) ] );
    }
}

WC_Bundle_Product_Ajax::init();


?>

