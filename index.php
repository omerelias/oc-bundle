<?php
/*
Plugin Name: WooCommerce Bundle Products
Description: Adds bundle product type to WooCommerce.
Version: 1.0
Author: Original Concepts
Author URI: https://onlinestore.co.il/
Text Domain: wc-bundle-products
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Register the bundle product type and include necessary files after WooCommerce is loaded
add_action( 'plugins_loaded', 'wc_bundle_products_init', 20 );

function wc_bundle_products_init() {
    // Check if WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Include necessary files
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-product-bundle.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-product-bundle-admin.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-product-bundle-frontend.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-product-bundle-ajax.php';

    // Initialize the plugin
    WC_Bundle_Product::init();
    WC_Bundle_Product_Admin::init();
    WC_Bundle_Product_Frontend::init();
    WC_Bundle_Product_Ajax::init();

    load_plugin_textdomain( 'wc-bundle-products', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
?>
