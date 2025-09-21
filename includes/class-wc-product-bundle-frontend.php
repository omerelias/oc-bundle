<?php if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Bundle_Product_Frontend
{
    public static function init()
    {

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
        add_action('woocommerce_single_product_summary', [__CLASS__, 'display_bundle_product_details'], 20);
        add_action('oc_woocommerce_after_product_image', [__CLASS__, 'add_bundle_add_to_cart'], 30);

        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
        add_action('oc_bundle_products_before_bundle_review', 'woocommerce_template_single_price', 10);

        add_filter('wc_get_template_part', [__CLASS__, 'custom_cart_template'], 10, 3);
        add_filter('woocommerce_add_to_cart_fragments', [__CLASS__, 'customize_mini_cart']);
        add_action('woocommerce_after_add_to_cart_form', [__CLASS__, 'display_summary_notes']);
//        add_filter('woocommerce_get_price_html', [__CLASS__, 'add_text_after_price'], 10, 2);
        add_action('wp_ajax_get_replacement_products', [__CLASS__, 'get_replacement_products']);
        add_action('wp_ajax_nopriv_get_replacement_products', [__CLASS__, 'get_replacement_products']);
        add_filter('woocommerce_add_cart_item_data', [__CLASS__, 'add_bundle_cart_item_data'], 10, 3);
        add_filter('woocommerce_get_item_data', [__CLASS__, 'display_bundle_cart_item_data'], 10, 2);
        add_filter('woocommerce_add_cart_item', [__CLASS__, 'set_bundle_price_in_cart'], 10, 1);
        add_filter('woocommerce_get_cart_item_from_session', [__CLASS__, 'get_cart_items_from_session'], 10, 2);
        add_action('woocommerce_before_order_itemmeta', [__CLASS__, 'display_bundle_order_item_data'], 10, 3); // Add this line
        add_action('woocommerce_add_order_item_meta', [__CLASS__, 'add_bundle_order_item_meta'], 10, 3);
        remove_action( 'woocommerce_after_quantity_input_field', 			'oc_theme_output_minus_btn' );
        remove_action( 'woocommerce_before_quantity_input_field', 			'oc_theme_output_plus_btn' );
        add_action('oc_bundle_text_above_quantity', [__CLASS__, 'display_text_above_quantity']);
        add_filter('body_class', [__CLASS__,'oc_bundle_frontend_body_class']);


//        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
//        add_action('woocommerce_before_add_to_cart_button', 'woocommerce_template_single_price', 10);


    }

    public static function add_bundle_order_item_meta($item_id, $values, $cart_item_key) {
        if (isset($values['bundle_data'])) {
            wc_add_order_item_meta($item_id, '_bundle_data', $values['bundle_data']);
        }
    }


// Display bundle product details in the admin order page
    public static function display_bundle_order_item_data($item_id, $item, $product) {
        if (isset($item['bundle_data'])) {
            $bundle_data = $item['bundle_data'];

            echo '<div class="bundle-products">';
            echo '<strong>' . __('Bundle Products:', 'wc-bundle-products') . '</strong>';
            echo '<ul>';

            foreach ($bundle_data['items'] as $bundle_item) {
                $product = wc_get_product($bundle_item['product_id']);
                if ($product) {
                    echo '<li>';
                    echo '<a href="'.esc_html($product->get_permalink()).'">' .esc_html($product->get_name()) . ' &times; ' . esc_html($bundle_item['quantity'] );
                    echo '</li>';
                }
            }

            echo '</ul>';
            echo '</div>';
        }
    }

    // Set the price for the bundle in the cart
    public static function set_bundle_price_in_cart($cart_item) {
        if (isset($cart_item['bundle_data'])) {
            $cart_item['data']->set_price($cart_item['bundle_data']['total_price']);
        }
        return $cart_item;
    }

// Retrieve cart items from session
    public static function get_cart_items_from_session($cart_item, $values) {
        if (isset($values['bundle_data'])) {
            $cart_item['bundle_data'] = $values['bundle_data'];
            $cart_item['data']->set_price($cart_item['bundle_data']['total_price']);
        }
        return $cart_item;
    }

    public static function get_replacement_products() {
        $product_id = intval($_POST['product_id']);
        $bundle_id = intval($_POST['bundle_id']);
        $level_index = intval($_POST['level_index']); // Retrieve the level index

        if (!$bundle_id || !$level_index) {
            wp_die();
        }
        $replacement_products = get_post_meta($bundle_id, "bundle_level_{$level_index}_product_{$product_id}_default_products", true);
        $replacement_products = !empty($replacement_products) ? array_filter(array_map('absint', explode(',', $replacement_products))) : [];
        // Fetch replacement products from the meta field for the specified level
        if(!$replacement_products){
            $replacement_products = get_post_meta($bundle_id, "_bundle_level_{$level_index}_default_products", true);
            $replacement_products = !empty($replacement_products) ? array_filter(array_map('absint', explode(',', $replacement_products))) : [];
        }

        $replacement_products[] = $product_id;

        $products = [];

        foreach ( $replacement_products as $replacement_product_id ) {
            $product = wc_get_product( $replacement_product_id );

            if ( $product ) {
                $image_id = $product->get_image_id();
                $image_src = wp_get_attachment_image_src($image_id, 'thumbnail');

                $products[] = [
                    'id'    => $product->get_id(),
                    'name'  => esc_html(self::get_full_product_name($product)),
                    'price' => $product->get_price(),
                    'image' => $image_src[0],
                    'url'   => $product->get_permalink(),
                ];
            }
        }

        wp_send_json_success( [ 'products' => $products ] );
    }

    public static function custom_cart_template($template, $slug, $name)
    {
        if ($slug === 'single-product/add-to-cart' && $name === 'bundle') {
            $template = plugin_dir_path(__FILE__) . '../templates/single-product/add-to-cart/add-to-cart-bundle.php';
        }
        return $template;
    }

    public static function enqueue_scripts() {
        if(is_singular('product')) {
            $product = wc_get_product(get_the_ID());
            if (!($product && ($product->get_type() == 'bundle'))) {
                return;
            }
        }else{
            return;
        }


        error_log('enqueue_scripts called'); // Debugging line

        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('oc-bundle-frontend-css', plugin_dir_url(__FILE__) . '../oc-bundle-frontend.css', array(), '1.0.0');
        wp_enqueue_script('jquery-ui-dialog'); // Enqueue WordPress provided jQuery UI Dialog

        // Debugging lines
        if (file_exists(plugin_dir_path(__FILE__) . '../oc-bundle-frontend.js')) {
            error_log('oc-bundle-frontend.js file exists');
        } else {
            error_log('oc-bundle-frontend.js file does not exist');
        }

        wp_enqueue_script('oc-bundle-frontend-js', plugin_dir_url(__FILE__) . '../oc-bundle-frontend.js', array('jquery', 'jquery-ui-dialog'), '1.0.0', true);

        wp_localize_script('oc-bundle-frontend-js', 'wc_bundle_products_params', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'add_bundle_item_nonce' => wp_create_nonce('add_bundle_item_nonce'),
            'edit_bundle_item_nonce' => wp_create_nonce('edit_bundle_item_nonce'),
            'currency_symbol' => get_woocommerce_currency_symbol(),
        ));
        if(is_singular('product')){
            $product = wc_get_product(get_the_ID());
            if(! ($product &&($product->get_type() == 'bundle'))){
                return;
            }

            // Retrieve all meta data for the product
            $product_meta_data = get_post_meta(get_the_ID());

            // Prepare meta data for localization
            $localized_meta_data = array();
            foreach ($product_meta_data as $key => $value) {
                // If the meta value is an array with a single element, use that element directly
                $localized_meta_data[$key] = is_array($value) && count($value) === 1 ? $value[0] : $value;
            }

            // Localize the script with the product meta data
            wp_localize_script('oc-bundle-frontend-js', 'wc_bundle_product_meta', $localized_meta_data);
        }
    }

    public static function display_text_above_quantity()
    {
        global $product;
        if(!$product) {
            return;
        }
        if ('bundle' !== $product->get_type()) {
            return;
        }
        $text_above_quantity = get_post_meta($product->get_id(), '_bundle_text_above_quantity', true);
        if ($text_above_quantity) {
            echo '<div class="bundle-text-above-quantity">' . esc_html($text_above_quantity) . '</div>';
        }
    }

    public static function display_summary_notes()
    {
        global $product;
        if ('bundle' !== $product->get_type()) {
            return;
        }
        $bundle_notes = get_post_meta($product->get_id(), '_bundle_summary_notes', true);
        if ($bundle_notes) {
            echo '<div class="bundle-summary-notes">' . esc_html($bundle_notes) . '</div></div>';
        }
    }

    public static function add_text_after_price($price, $product) {
        if ('bundle' !== $product->get_type()) {
            return $price;
        }

        $text_after_price = get_post_meta($product->get_id(), '_bundle_text_after_price', true);
        if ($text_after_price) {
            // Append the new div directly inside the price HTML
            $price .= '<div id="after_price">' . esc_html($text_after_price) . '</div>';
        }
        return $price;
    }

    public static function customize_mini_cart($fragments)
    {
        ob_start();
        woocommerce_mini_cart();
        $fragments['div.widget_shopping_cart_content'] = ob_get_clean();
        return $fragments;
    }

    public static function display_bundle_product_details() {
        global $product;
        if ( ! $product || 'bundle' !== $product->get_type() ) {
            return;
        }

        $bundle_id = $product->get_id();
        $bundle_type = get_post_meta( $bundle_id, '_bundle_type', true );
        $bundle_number_of_levels = get_post_meta( $bundle_id, '_bundle_number_of_levels', true );
        $bundle_price_type = get_post_meta( $bundle_id, '_bundle_price_type', true );

        echo '<div class="bundle-product-details" data-bundle-id="' . esc_attr( $bundle_id ) . '">';
//        echo '<h2>' . esc_html__( 'Bundle Details', 'wc-bundle-products' ) . '</h2>';

        // Add the span for displaying total extras

        $total_price = 0;
        echo '<div class="bundle-levels-container" data-bundle-id="' . esc_attr( $bundle_id ) . '">';

        for ( $i = 1; $i <= $bundle_number_of_levels; $i++ ) {
            $class = ( $i === 1 ) ? 'active' : '';
            $level_title = get_post_meta( $bundle_id, "_bundle_level_{$i}_title", true );
            $level_description = get_post_meta( $bundle_id, "_bundle_level_{$i}_description", true );
            $level_products = get_post_meta( $bundle_id, "_bundle_level_{$i}_products", true );

            if ( $level_title || $level_description || $level_products ) {
                $max_amount = get_post_meta($bundle_id, "_bundle_level_{$i}_amount_products", true);
                echo '<div class="bundle-level-details" data-max="' . esc_attr($max_amount) . '" data-level-index="' . esc_attr( $i ) . '">';
                echo '<div class="bundle-level-header">';
                if ( $level_title ) {
                    echo '<h3 class="level_title '.$class.'">' . esc_html( $level_title ) . '</h3>';
                }
                if ( $level_description ) {
                    echo '<p>' . esc_html( $level_description ) . '</p>';
                }
                if ( $level_products ) {
                    $product_ids = explode( ',', $level_products );
                    echo '<span class="selected-products-count">0/' . esc_attr($max_amount) . '</span>';
                    echo '<span class="hidden total-selected-products" style="display:none">'.esc_attr($max_amount).'</span>';
                    echo '<div class="selected-products"></div>';
                    echo '</div>';
                    echo '<ul class="bundle-products-list open">';

                    foreach ( $product_ids as $product_id_new ) {
                        $product_id_new = (int) $product_id_new;
                        if ( $product_id_new ) {
                            $product_new = wc_get_product( $product_id_new );
                            if ( $product_new ) {
                                // Fetch quantity, min quantity, max quantity, and extra price
                                $quantity = esc_attr(get_post_meta($bundle_id, "bundle_level_{$i}_product_{$product_id_new}_quantity", true));
                                $min_quantity = esc_attr(get_post_meta($bundle_id, "bundle_level_{$i}_product_{$product_id_new}_quantity_min", true));
                                $max_quantity = esc_attr(get_post_meta($bundle_id, "bundle_level_{$i}_product_{$product_id_new}_quantity_max", true));
                                $extra_price = get_post_meta($bundle_id, "bundle_level_{$i}_product_{$product_id_new}_extra_price", true);
                                $show_desc = get_post_meta($bundle_id, "_bundle_show_short_description", true);
                                $prod_notice = get_post_meta($bundle_id, "bundle_level_{$i}_product_{$product_id_new}_notice", true);
                                $level_id = $i;
                                $editable = ($bundle_type == 'editable');
                                $normal_bundle = ($bundle_type == 'closed');

                                // Include the template part for each product
                                include plugin_dir_path(__FILE__) . '../templates/bundle-product-item.php';

                                $total_price += (int) $product_new->get_price();
                            }
                        }
                    }
                    echo '</ul>';
                }
                echo '</div>';
            }
        }

        echo '</div>';

        echo '<div class="bundle-dynamic-pricing">';
        if ($bundle_price_type === 'selected_price') {
//            echo '<p>' . sprintf(esc_html__('Bundle Price: %s', 'wc-bundle-products'), wc_price($total_price)) . '</p>';
        } elseif ($bundle_price_type === 'fixed') {
            $bundle_price = get_post_meta($bundle_id, '_bundle_price', true);
//            echo '<p>' . sprintf(esc_html__('Bundle Price: %s', 'wc-bundle-products'), wc_price($bundle_price)) . '</p>';
        }
        echo '</div>';
        echo '</div>';
    }
    public static function add_bundle_add_to_cart() {
        global $product;
        if ( 'bundle' === $product->get_type() ) {
            wc_get_template_part( 'single-product/add-to-cart', 'bundle' );
        }
    }

    public static function add_bundle_cart_item_data($cart_item_data, $product_id, $variation_id) {
        $product = wc_get_product($product_id);

        if ('bundle' === $product->get_type()) {
            $bundle_items = json_decode(stripslashes($_POST['bundle_product_data']), true);

            // Filter out items with quantity 0
            $bundle_items = array_filter($bundle_items, function($item) {
                return intval($item['quantity']) > 0;
            });

            // Get bundle pricing settings
            $price_type = get_post_meta($product_id, '_bundle_price_type', true) ?: 'dynamic';
            $discount_type = get_post_meta($product_id, '_bundle_discount_type', true) ?: 'fixed';
            $discount_value = floatval(get_post_meta($product_id, '_bundle_discount_value', true)) ?: 0;

            $calculated_total_price = 0;

            if ($price_type === 'fixed') {
                $calculated_total_price = floatval(get_post_meta($product_id, '_bundle_price', true)) ?: 0;


                foreach ($bundle_items as &$item) {
                    $extra_price = floatval(get_post_meta($product_id, "bundle_level_{$item['level_id']}_product_{$item['product_id']}_extra_price", true));
                    $quantity = intval($item['quantity']);

                    $extra_product_price += ($extra_price) * $quantity;
                }
                if($extra_product_price > 0){
                    $calculated_total_price+= $extra_product_price;
                }

                // Apply discount
                if ($discount_type === 'percent') {
                    $calculated_total_price = $calculated_total_price * (1 - $discount_value / 100);
                } elseif ($discount_type === 'fixed') {
                    $calculated_total_price = $calculated_total_price - $discount_value;
                }



            } else {
                foreach ($bundle_items as &$item) {
                    $extra_price = floatval(get_post_meta($product_id, "bundle_level_{$item['level_id']}_product_{$item['product_id']}_extra_price", true));
                    $temp_product = wc_get_product($item['product_id']);
                    $temp_product_price = floatval($temp_product->get_price());
                    $quantity = intval($item['quantity']);

                    $calculated_total_price += ($temp_product_price + $extra_price) * $quantity;
                }

                // Apply discount
                if ($discount_type === 'percent') {
                    $calculated_total_price = $calculated_total_price * (1 - $discount_value / 100);
                } elseif ($discount_type === 'fixed') {
                    $calculated_total_price = $calculated_total_price - $discount_value;
                }
            }

            if ($calculated_total_price < 0) {
                $calculated_total_price = 0; // Ensure the total price is not negative
            }

            // Set bundle data
            $cart_item_data['bundle_data'] = array(
                'bundle_id' => $product_id,
                'items' => $bundle_items,
                'total_price' => $calculated_total_price
            );

            // Override the cart item price with the calculated bundle price
            if (isset($cart_item_data['data']) && is_object($cart_item_data['data'])) {
                $cart_item_data['data']->set_price($calculated_total_price);
            }
        }

        return $cart_item_data;
    }

    public static function display_bundle_cart_item_data($item_data, $cart_item) {
        if (isset($cart_item['bundle_data'])) {
            $bundle_data = $cart_item['bundle_data'];
            $current_level = '';

            foreach ($bundle_data['items'] as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    if ($current_level !== $item['level_name']) {
                        $key = __($item['level_name'], 'wc-bundle-products');
                        $current_level = $item['level_name'];
                        $item_data[] = array(
                            'key' => $key,
                            'value' => ''
                        );
                    }


                    $item_data[] = array(
                        'key' => '',
                        'value' => sprintf(
                            __('%s %dx', 'wc-bundle-products'),
                            $product->get_name(),
                            $item['quantity']
                        )
                    );
                }
            }
        }

        return $item_data;
    }

    public static function get_full_product_name($product_id) {
        $product = wc_get_product($product_id);

        // Get the product name
        $name = $product->get_name();

        // Check if it's a variation
        if ($product->is_type('variation')) {
            // Get the variation attributes
            $attributes = $product->get_attributes();
            $attribute_names = array();

            foreach ($attributes as $attribute => $value) {
                // Get the attribute name and value
                $taxonomy = wc_attribute_label($attribute);
                $term = get_term_by('slug', $value, $attribute);
                $attribute_names[] = $taxonomy . ': ' . $term->name;
            }

            // Append the attributes to the product name
            if (!empty($attribute_names)) {
                $name .= ' (' . implode(', ', $attribute_names) . ')';
            }
        }

        return $name;
    }

    public static function oc_bundle_frontend_body_class($classes) {
        global $product;
        $is_bundle = (is_a($product,'WC_Bundle_Product'));
        if(!$is_bundle){
            return $classes;
        }
        $bundle_type = get_post_meta($product->get_id(), '_bundle_type', true);
        $classes[] = 'bundle-product';
        $classes[] = $bundle_type;

        return $classes;
    }

    function remove_colons_from_display($output, $instance, $flat) {
        // Remove colons from the output
        $output = str_replace(':', '', $output);
        return $output;
    }


}

WC_Bundle_Product_Frontend::init();



?>
