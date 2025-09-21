<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Bundle_Product_Admin
{
    public static function init()
    {
        add_action('woocommerce_product_data_tabs', [__CLASS__, 'add_bundle_product_tabs']);
        add_action('woocommerce_product_data_panels', [__CLASS__, 'bundle_product_tabs_content']);
        add_action('woocommerce_process_product_meta', [__CLASS__, 'save_meta_boxes']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_scripts']);
    }


    public static function add_bundle_product_tabs($tabs)
    {
        $tabs['general_settings'] = [
            'label' => __('Bundle - General Settings', 'wc-bundle-products'),
            'target' => 'general_product_data_main',
            'class' => ['show_if_bundle'],
            'priority' => 21,
        ];

        $tabs['bundle_levels'] = [
            'label' => __('Bundle - Levels', 'wc-bundle-products'),
            'target' => 'bundle_levels_product_data',
            'class' => ['show_if_bundle'],
            'priority' => 22,
        ];
        $tabs['inventory'] = array(
            'label'    => __('Inventory', 'woocommerce'),
            'target'   => 'inventory_product_data', // ID of the panel to display
            'class'    => array('show_if_bundle', 'show_if_simple', 'show_if_variable'), // Allow display for your custom type
            'priority' => 23,
        );


        return $tabs;
    }

    public static function bundle_product_tabs_content()
    {
        global $post;
        ?>
        <div id='general_product_data_main' class='panel woocommerce_options_panel' style="display: block;">
            <div class='options_group'>
                <!-- Inner Tabs -->
                <h2 class="nav-tab-wrapper">
                    <a href="#" class="nav-tab nav-tab-active"
                       data-tab="general-settings"><?php esc_html_e('General Settings', 'wc-bundle-products'); ?></a>
                    <a href="#" class="nav-tab"
                       data-tab="pricing-settings"><?php esc_html_e('Pricing Settings', 'wc-bundle-products'); ?></a>
                    <a href="#" class="nav-tab"
                       data-tab="display-settings"><?php esc_html_e('Display Settings', 'wc-bundle-products'); ?></a>
                </h2>

                <!-- General Settings -->
                <div id="general-settings" class="inner-tab-content" style="display: block;">
                    <?php
                    woocommerce_wp_select([
                        'id' => '_bundle_type',
                        'label' => __('Bundle Type', 'wc-bundle-products'),
                        'description' => __('Select the type of bundle.', 'wc-bundle-products'),
                        'options' => [
                            'closed' => __('Closed Bundle', 'wc-bundle-products'),
                            'editable' => __('Editable Bundle', 'wc-bundle-products'),
                            'self_select' => __('Self-Selection Bundle', 'wc-bundle-products'),
                        ],
                        'desc_tip' => true,
                    ]);

                    woocommerce_wp_checkbox([
                        'id' => '_bundle_show_short_description',
                        'label' => __('Show Short Description', 'wc-bundle-products'),
                        'description' => __('Show a short description for each product in the bundle.', 'wc-bundle-products'),
                    ]);

                    woocommerce_wp_select([
                        'id' => '_bundle_number_of_levels',
                        'label' => __('Number of Levels', 'wc-bundle-products'),
                        'description' => __('Select the number of levels for the bundle product.', 'wc-bundle-products'),
                        'options' => [
                            '1' => '1',
                            '2' => '2',
                            '3' => '3',
                            '4' => '4',
                            '5' => '5',
                        ],
                        'desc_tip' => true,
                    ]);

                    woocommerce_wp_text_input([
                        'id' => '_bundle_general_minimum_quantity',
                        'label' => __('Bundle Minimum quantity', 'wc-bundle-products'),
                        'description' => __('Enter the minimum quantity can be added to cart.', 'wc-bundle-products'),
                        'type' => 'number',
                        'desc_tip' => true,
                        'custom_attributes' => [
                            'step' => 'any',
                            'min' => '0',
                        ],
                    ]);

                    woocommerce_wp_text_input([
                        'id' => '_bundle_general_maximum_quantity',
                        'label' => __('Bundle Maximum quantity', 'wc-bundle-products'),
                        'description' => __('Enter the maximum quantity can be added to cart.', 'wc-bundle-products'),
                        'type' => 'number',
                        'desc_tip' => true,
                        'custom_attributes' => [
                            'step' => 'any',
                            'min' => '0',
                        ],
                    ]);

                    ?>
                </div>

                <!-- Pricing Settings -->
                <div id="pricing-settings" class="inner-tab-content" style="display: none;">
                    <?php
                    woocommerce_wp_select([
                        'id' => '_bundle_price_type',
                        'label' => __('Bundle Price Type', 'wc-bundle-products'),
                        'description' => __('Select how the bundle price is determined.', 'wc-bundle-products'),
                        'options' => [
                            'selected_price' => __('By the product selected price', 'wc-bundle-products'),
                            'fixed' => __('Fixed price', 'wc-bundle-products'),
                        ],
                        'desc_tip' => true,
                    ]);

                    echo '<div id="bundle-fixed-price-setting" style="display: none;">';
                    woocommerce_wp_text_input([
                        'id' => '_bundle_price',
                        'label' => __('Bundle Price', 'wc-bundle-products'),
                        'description' => __('Enter the fixed price for the bundle.', 'wc-bundle-products'),
                        'type' => 'number',
                        'desc_tip' => true,
                        'custom_attributes' => [
                            'step' => 'any',
                            'min' => '0',
                        ],
                    ]);
                    echo '</div>';

                    echo '<div id="bundle-discount-settings" style="display: none;">';
                    woocommerce_wp_select([
                        'id' => '_bundle_discount_type',
                        'label' => __('Discount Type', 'wc-bundle-products'),
                        'description' => __('Select the discount type for the bundle.', 'wc-bundle-products'),
                        'options' => [
                            'percent' => __('Percentage', 'wc-bundle-products'),
                            'fixed' => __('Fixed Amount', 'wc-bundle-products'),
                        ],
                        'desc_tip' => true,
                    ]);

                    woocommerce_wp_text_input([
                        'id' => '_bundle_discount_value',
                        'label' => __('Discount Value', 'wc-bundle-products'),
                        'description' => __('Enter the discount value for the bundle.', 'wc-bundle-products'),
                        'type' => 'number',
                        'desc_tip' => true,
                        'custom_attributes' => [
                            'step' => 'any',
                            'min' => '0',
                        ],
                    ]);

                    echo '</div>';
                    ?>
                </div>

                <!-- Display Settings -->
                <div id="display-settings" class="inner-tab-content" style="display: none;">
                    <?php
                    woocommerce_wp_text_input([
                        'id' => '_bundle_text_after_price',
                        'label' => __('Text After Price', 'wc-bundle-products'),
                        'description' => __('Enter the text to display after the price.', 'wc-bundle-products'),
                        'type' => 'text',
                        'desc_tip' => true,
                    ]);

                    woocommerce_wp_text_input([
                        'id' => '_bundle_text_above_quantity',
                        'label' => __('Text Above Quantity', 'wc-bundle-products'),
                        'description' => __('Enter the text to display above the quantity.', 'wc-bundle-products'),
                        'type' => 'text',
                        'desc_tip' => true,
                    ]);

                    woocommerce_wp_textarea_input([
                        'id' => '_bundle_summary_notes',
                        'label' => __('Summary Notes', 'wc-bundle-products'),
                        'description' => __('Enter summary notes to display below the add to cart button.', 'wc-bundle-products'),
                        'desc_tip' => true,
                    ]);
                    ?>
                </div>
            </div>
        </div>

        <div id='bundle_levels_product_data' class='panel woocommerce_options_panel'>
            <div class='options_group'>
                <!-- Outer Tabs -->
                <h2 class="nav-tab-wrapper">
                    <a href="#" class="nav-tab nav-tab-active"
                       data-tab="level-1-settings"><?php esc_html_e('Level 1', 'wc-bundle-products'); ?></a>
                    <a href="#" class="nav-tab"
                       data-tab="level-2-settings"><?php esc_html_e('Level 2', 'wc-bundle-products'); ?></a>
                    <a href="#" class="nav-tab"
                       data-tab="level-3-settings"><?php esc_html_e('Level 3', 'wc-bundle-products'); ?></a>
                    <a href="#" class="nav-tab"
                       data-tab="level-4-settings"><?php esc_html_e('Level 4', 'wc-bundle-products'); ?></a>
                    <a href="#" class="nav-tab"
                       data-tab="level-5-settings"><?php esc_html_e('Level 5', 'wc-bundle-products'); ?></a>
                    <a href="#" class="nav-tab"
                       data-tab="level-6-settings"><?php esc_html_e('Upsells', 'wc-bundle-products'); ?></a>
                </h2>

                <?php for ($i = 1; $i <= 6; $i++) : ?>
                    <div id="level-<?php echo $i; ?>-settings" class="inner-tab-content"
                         style="display: <?php echo $i === 1 ? 'block' : 'none'; ?>;">
                        <?php
                        $is_upsells = $i == 6 ? true : false;

                        if ($is_upsells) {
                            woocommerce_wp_checkbox([
                                'id' => "_bundle_level_{$i}_enable_upsells",
                                'label' => __('Enable upsells', 'wc-bundle-products'),
                            ]);
                        }
                        woocommerce_wp_text_input([
                            'id' => "_bundle_level_{$i}_title",
                            'label' => sprintf(__('Title', 'wc-bundle-products')),
                            'description' => sprintf(__('Enter the title for level %d.', 'wc-bundle-products'), $i),
                            'type' => 'text',
                            'desc_tip' => true,
                        ]);

                        woocommerce_wp_textarea_input([
                            'id' => "_bundle_level_{$i}_description",
                            'label' => sprintf(__('Description', 'wc-bundle-products')),
                            'description' => sprintf(__('Enter the description for level %d.', 'wc-bundle-products'), $i),
                            'desc_tip' => true,
                        ]);

                        if (!$is_upsells) {
                            woocommerce_wp_text_input([
                                'id' => "_bundle_level_{$i}_amount_products",
                                'label' => sprintf(__('Number of products', 'wc-bundle-products')),
                                'description' => sprintf(__('Enter the Amount of products for level %d.', 'wc-bundle-products'), $i),
                                'type' => 'text',
                                'desc_tip' => true,
                            ]);
                        }

                        woocommerce_wp_checkbox([
                            'id' => "_bundle_level_{$i}_show_price",
                            'label' => sprintf(__('Show products price', 'wc-bundle-products')),
                            'description' => sprintf(__('Enter the price of products for level %d.', 'wc-bundle-products'), $i),
                            'type' => 'text',
                            'desc_tip' => true,
                        ]);


                        // Using WooCommerce Select2 for product selection
                        $product_ids = get_post_meta($post->ID, "_bundle_level_{$i}_products", true);
                        $selected_products = !empty($product_ids) ? array_filter(array_map('absint', explode(',', $product_ids))) : [];
                        ?>
                        <p class="form-field">
                            <label for="bundle_level_<?php echo $i; ?>_products"><?php printf(__('Products', 'wc-bundle-products')); ?></label>
                            <select class="wc-product-search " multiple="multiple" style="width: 50%;"
                                    id="bundle_level_<?php echo $i; ?>_products"
                                    name="bundle_level_<?php echo $i; ?>_products[]"
                                    data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wc-bundle-products'); ?>"
                                    data-action="woocommerce_json_search_products_and_variations">
                                <?php
                                foreach ($selected_products as $product_id) {
                                    $product = wc_get_product($product_id);
                                    if (is_object($product)) {
                                        echo '<option value="' . esc_attr($product_id) . '" selected="selected">' . wp_kses_post($product->get_formatted_name()) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </p>

                        <?php
                        // Using WooCommerce Select2 for product selection
                        $product_ids = get_post_meta($post->ID, "_bundle_level_{$i}_default_products", true);
                        $selected_default_products = !empty($product_ids) ? array_filter(array_map('absint', explode(',', $product_ids))) : [];
                        $product_type = get_post_meta($post->ID, "_bundle_type", true);
                        ?>
                        <?php if ($product_type == 'editable'): ?>
                            <p class="form-field">
                                <label for="bundle_level_<?php echo $i; ?>_default_products"><?php printf(__('Replacement Products', 'wc-bundle-products')); ?></label>
                                <select class="wc-product-search" multiple="multiple" style="width: 50%;"
                                        id="bundle_level_<?php echo $i; ?>_default_products"
                                        name="bundle_level_<?php echo $i; ?>_default_products[]"
                                        data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wc-bundle-products'); ?>"
                                        data-action="woocommerce_json_search_products_and_variations">
                                    <?php
                                    foreach ($selected_default_products as $product_id) {
                                        $product = wc_get_product($product_id);
                                        if (is_object($product)) {
                                            echo '<option value="' . esc_attr($product_id) . '" selected="selected">' . wp_kses_post($product->get_formatted_name()) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </p>
                        <?php endif; ?>
                        <!-- Hidden input to ensure the level is saved even if no products are selected -->
                        <input type="hidden" name="bundle_level_<?php echo $i; ?>_exists" value="1"/>
                        <input type="hidden" name="bundle_level_<?php echo $i; ?>_default_exists" value="1"/>

                        <!-- Accordion for product-specific settings -->
                        <div class="product-settings-accordion" id="accordion-level-<?php echo $i; ?>">
                            <?php
                            foreach ($selected_products as $product_id) {
                                // Retrieve saved values
                                $quantity_min = get_post_meta($post->ID, "bundle_level_{$i}_product_{$product_id}_quantity_min", true);
                                $quantity_max = get_post_meta($post->ID, "bundle_level_{$i}_product_{$product_id}_quantity_max", true);
                                $quantity_closed = get_post_meta($post->ID, "bundle_level_{$i}_product_{$product_id}_quantity", true);
                                $extra_price = get_post_meta($post->ID, "bundle_level_{$i}_product_{$product_id}_extra_price", true);
                                $prod_notice = get_post_meta($post->ID, "bundle_level_{$i}_product_{$product_id}_notice", true);
                                $level_id = $i;
                                ?>
                                <h3><?php echo esc_html(wc_get_product($product_id)->get_name()); ?></h3>
                                <div class="product-settings" data-product-id="<?php echo esc_attr($product_id); ?>">

                                    <?php if ($product_type != 'closed') : ?>
                                        <!-- Product-specific settings here -->
                                        <p class="form-field">
                                            <label><?php esc_html_e('Quantity Min', 'wc-bundle-products'); ?></label>
                                            <input type="number"
                                                   name="bundle_level_<?php echo $i; ?>_product_<?php echo $product_id; ?>_quantity_min"
                                                   value="<?php echo esc_attr($quantity_min); ?>"/>
                                        </p>
                                        <p class="form-field">
                                            <label><?php esc_html_e('Quantity Max', 'wc-bundle-products'); ?></label>
                                            <input type="number"
                                                   name="bundle_level_<?php echo $i; ?>_product_<?php echo $product_id; ?>_quantity_max"
                                                   value="<?php echo esc_attr($quantity_max); ?>"/>
                                        </p>
                                        <p class="form-field">
                                            <label><?php esc_html_e('Extra price', 'wc-bundle-products'); ?></label>
                                            <input type="number"
                                                   name="bundle_level_<?php echo $i; ?>_product_<?php echo $product_id; ?>_extra_price"
                                                   value="<?php echo esc_attr($extra_price); ?>"/>
                                        </p>
                                    <?php else: ?>
                                        <p class="form-field">
                                            <label><?php esc_html_e('Quantity', 'wc-bundle-products'); ?></label>
                                            <input type="number"
                                                   name="bundle_level_<?php echo $i; ?>_product_<?php echo $product_id; ?>_quantity"
                                                   value="<?php echo esc_attr($quantity_closed); ?>"/>
                                        </p>
                                    <?php endif; ?>

                                    <p class="form-field">
                                        <label><?php esc_html_e('Notice', 'wc-bundle-products'); ?></label>
                                        <input type="text"
                                               name="bundle_level_<?php echo $i; ?>_product_<?php echo $product_id; ?>_notice"
                                               value="<?php echo esc_attr($prod_notice); ?>"/>
                                    </p>



                                    <?php if ($product_type == 'editable'): ?>
                                        <?php
                                        $product_ids = get_post_meta($post->ID, "bundle_level_{$i}_product_{$product_id}_default_products", true);
                                        ?>
                                        <p class="form-field">
                                            <label for="bundle_level_<?php echo $i; ?>_product_<?php echo $product_id; ?>_default_products"><?php printf(__('Replacement Products', 'wc-bundle-products')); ?></label>
                                            <select class="wc-product-search" multiple="multiple" style="width: 50%;"
                                                    id="bundle_level_<?php echo $i; ?>_product_<?php echo $product_id; ?>_default_products"
                                                    name="bundle_level_<?php echo $i; ?>_product_<?php echo $product_id; ?>_default_products[]"
                                                    data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'wc-bundle-products'); ?>"
                                                    data-action="woocommerce_json_search_products_and_variations">
                                                <?php
                                                // Using WooCommerce Select2 for product selection

                                                $selected_default_products = !empty($product_ids) ? array_filter(array_map('absint', explode(',', $product_ids))) : [];
                                                foreach ($selected_default_products as $product_id) {
                                                    $product = wc_get_product($product_id);
                                                    if (is_object($product)) {
                                                        echo '<option value="' . esc_attr($product_id) . '" selected="selected">' . wp_kses_post($product->get_formatted_name()) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </p>
                                    <?php endif; ?>
                                    <!-- Hidden input to ensure the level is saved even if no products are selected -->
                                    <input type="hidden" name="bundle_level_<?php echo $i; ?>_exists" value="1"/>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                <?php endfor; ?>

            </div>
        </div>
        <?php
    }

    public static function save_meta_boxes($post_id)
    {
        if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $bundle_type = isset($_POST['_bundle_type']) ? sanitize_text_field($_POST['_bundle_type']) : '';
        update_post_meta($post_id, '_bundle_type', $bundle_type);

        $bundle_price_type = isset($_POST['_bundle_price_type']) ? sanitize_text_field($_POST['_bundle_price_type']) : '';
        update_post_meta($post_id, '_bundle_price_type', $bundle_price_type);

        $bundle_price = isset($_POST['_bundle_price']) ? sanitize_text_field($_POST['_bundle_price']) : '';
        update_post_meta($post_id, '_bundle_price', $bundle_price);

        $bundle_discount_type = isset($_POST['_bundle_discount_type']) ? sanitize_text_field($_POST['_bundle_discount_type']) : '';
        update_post_meta($post_id, '_bundle_discount_type', $bundle_discount_type);

        $bundle_discount_value = isset($_POST['_bundle_discount_value']) ? sanitize_text_field($_POST['_bundle_discount_value']) : '';
        update_post_meta($post_id, '_bundle_discount_value', $bundle_discount_value);

        $bundle_show_short_description = isset($_POST['_bundle_show_short_description']) ? 'yes' : 'no';
        update_post_meta($post_id, '_bundle_show_short_description', $bundle_show_short_description);

        $bundle_text_after_price = isset($_POST['_bundle_text_after_price']) ? sanitize_text_field($_POST['_bundle_text_after_price']) : '';
        update_post_meta($post_id, '_bundle_text_after_price', $bundle_text_after_price);

        $bundle_text_above_quantity = isset($_POST['_bundle_text_above_quantity']) ? sanitize_text_field($_POST['_bundle_text_above_quantity']) : '';
        update_post_meta($post_id, '_bundle_text_above_quantity', $bundle_text_above_quantity);

        $bundle_summary_notes = isset($_POST['_bundle_summary_notes']) ? sanitize_textarea_field($_POST['_bundle_summary_notes']) : '';
        update_post_meta($post_id, '_bundle_summary_notes', $bundle_summary_notes);

        $bundle_number_of_levels = isset($_POST['_bundle_number_of_levels']) ? intval($_POST['_bundle_number_of_levels']) : 0;
        update_post_meta($post_id, '_bundle_number_of_levels', $bundle_number_of_levels);

        $bundle_general_minimum_quantity = isset($_POST['_bundle_general_minimum_quantity']) ? intval($_POST['_bundle_general_minimum_quantity']) : 0;
        update_post_meta($post_id, '_bundle_general_minimum_quantity', $bundle_general_minimum_quantity);

        $bundle_general_maximum_quantity = isset($_POST['_bundle_general_maximum_quantity']) ? intval($_POST['_bundle_general_maximum_quantity']) : '100';
        update_post_meta($post_id, '_bundle_general_maximum_quantity', $bundle_general_maximum_quantity);


        for ($i = 1; $i <= 6; $i++) {
            if (isset($_POST["bundle_level_{$i}_exists"])) {

                $level_title = isset($_POST["_bundle_level_{$i}_enable_upsells"]) ? sanitize_text_field($_POST["_bundle_level_{$i}_enable_upsells"]) : '';
                update_post_meta($post_id, "_bundle_level_{$i}_enable_upsells", $level_title);

                $level_title = isset($_POST["_bundle_level_{$i}_title"]) ? sanitize_text_field($_POST["_bundle_level_{$i}_title"]) : '';
                update_post_meta($post_id, "_bundle_level_{$i}_title", $level_title);

                $level_description = isset($_POST["_bundle_level_{$i}_description"]) ? sanitize_textarea_field($_POST["_bundle_level_{$i}_description"]) : '';
                update_post_meta($post_id, "_bundle_level_{$i}_description", $level_description);

                $level_amount_products = isset($_POST["_bundle_level_{$i}_amount_products"]) ? sanitize_text_field($_POST["_bundle_level_{$i}_amount_products"]) : '';
                update_post_meta($post_id, "_bundle_level_{$i}_amount_products", $level_amount_products);

                $level_amount_products = isset($_POST["_bundle_level_{$i}_show_price"]) ? sanitize_text_field($_POST["_bundle_level_{$i}_show_price"]) : '';
                update_post_meta($post_id, "_bundle_level_{$i}_show_price", $level_amount_products);


                $level_products = isset($_POST["bundle_level_{$i}_products"]) ? array_filter(array_map('intval', $_POST["bundle_level_{$i}_products"])) : [];
                update_post_meta($post_id, "_bundle_level_{$i}_products", implode(',', $level_products));

                $level_default_products = isset($_POST["bundle_level_{$i}_default_products"]) ? array_filter(array_map('intval', $_POST["bundle_level_{$i}_default_products"])) : [];
                update_post_meta($post_id, "_bundle_level_{$i}_default_products", implode(',', $level_default_products));

                // Save product-specific settings for each product in the level
                foreach ($level_products as $product_id) {
                    $quantity_closed = isset($_POST["bundle_level_{$i}_product_{$product_id}_quantity"]) ? intval($_POST["bundle_level_{$i}_product_{$product_id}_quantity"]) : 1;
                    update_post_meta($post_id, "bundle_level_{$i}_product_{$product_id}_quantity", $quantity_closed);

                    $quantity_min = isset($_POST["bundle_level_{$i}_product_{$product_id}_quantity_min"]) ? intval($_POST["bundle_level_{$i}_product_{$product_id}_quantity_min"]) : 1;
                    update_post_meta($post_id, "bundle_level_{$i}_product_{$product_id}_quantity_min", $quantity_min);


                    $quantity_max = isset($_POST["bundle_level_{$i}_product_{$product_id}_quantity_max"]) ? intval($_POST["bundle_level_{$i}_product_{$product_id}_quantity_max"]) : 1;
                    update_post_meta($post_id, "bundle_level_{$i}_product_{$product_id}_quantity_max", $quantity_max);

                    $extra_price = isset($_POST["bundle_level_{$i}_product_{$product_id}_extra_price"]) ? floatval($_POST["bundle_level_{$i}_product_{$product_id}_extra_price"]) : 0;
                    update_post_meta($post_id, "bundle_level_{$i}_product_{$product_id}_extra_price", $extra_price);

                    $prod_notice = isset($_POST["bundle_level_{$i}_product_{$product_id}_notice"]) ? $_POST["bundle_level_{$i}_product_{$product_id}_notice"]: '';
                    update_post_meta($post_id, "bundle_level_{$i}_product_{$product_id}_notice", $prod_notice);

                    // Check if the default products are set before updating the meta
                    if (isset($_POST["bundle_level_{$i}_product_{$product_id}_default_products"])) {

                        $level_product_default_products = array_filter(array_map('intval', $_POST["bundle_level_{$i}_product_{$product_id}_default_products"]));

                        update_post_meta($post_id, "bundle_level_{$i}_product_{$product_id}_default_products", implode(',', $level_product_default_products));

                    } else {
                        delete_post_meta($post_id, "bundle_level_{$i}_product_{$product_id}_default_products");
                    }

                }
                // If there are no products, still save empty meta
                if (empty($level_products)) {
                    update_post_meta($post_id, "_bundle_level_{$i}_products", '');
                }
            }
        }

    }

    public static function enqueue_admin_scripts()
    {
        $hook_suffix = get_current_screen()->id;
        if (! ($hook_suffix === 'product')) {
          return;
        }
        wp_enqueue_script('oc-bundle-settings-js', plugin_dir_url(__FILE__) . '../oc-bundle-settings.js', array('jquery', 'wp-util', 'select2', 'jquery-ui-accordion'), '1.0.0', true);
        wp_enqueue_style('oc-bundle-settings-css', plugin_dir_url(__FILE__) . '../oc-bundle-settings.css', array('select2', 'jquery-ui'), '1.0.0', true);
        wp_enqueue_style('woocommerce_admin_styles');
        wp_enqueue_script('woocommerce_admin');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('select2');

        global $post;
        $product_type = '';
        if ($post && $post->post_type == 'product') {
            $product = wc_get_product($post->ID);
            if ($product) {
                $product_type = $product->get_type();
            }
        }

        wp_localize_script('oc-bundle-settings-js', 'wc_bundle_admin_params', array(
            'product_type' => $product_type,
        ));
    }


    public static function add_bundle_cart_item_data($cart_item_data, $product_id, $variation_id)
    {
        $product = wc_get_product($product_id);

        if ('bundle' === $product->get_type()) {
            $bundle_items = json_decode(stripslashes($_POST['bundle_product_data']), true);
            $total_price = floatval($_POST['bundle_total_price']);

            // Filter out items with quantity 0
            $bundle_items = array_filter($bundle_items, function ($item) {
                return (int)$item['quantity'] > 0;
            });

            // Calculate total price again just to be safe
            $calculated_total_price = 0;
            foreach ($bundle_items as &$item) {
                $item['price'] = floatval($item['price']);
                $calculated_total_price += $item['price'] * intval($item['quantity']);
            }
            $total_price = $calculated_total_price;

            $cart_item_data['bundle_data'] = array(
                'bundle_id' => $product_id,
                'items' => $bundle_items,
                'total_price' => $total_price
            );

            // Override the cart item price with the calculated bundle price
            if (isset($cart_item_data['data']) && is_object($cart_item_data['data'])) {
                $cart_item_data['data']->set_price($total_price);
            }
        }

        return $cart_item_data;
    }

    public static function display_bundle_cart_item_data($item_data, $cart_item)
    {
        if (isset($cart_item['bundle_data'])) {
            $bundle_data = $cart_item['bundle_data'];

            $item_data[] = array(
                'key' => __('Bundle Total Price', 'wc-bundle-products'),
                'value' => wc_price($bundle_data['total_price'])
            );

            foreach ($bundle_data['items'] as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    $item_data[] = array(
                        'key' => __('Bundle Item', 'wc-bundle-products'),
                        'value' => sprintf(
                            __('%s (x %d)', 'wc-bundle-products'),
                            $product->get_name(),
                            $item['quantity']
                        )
                    );
                    $item_data[] = array(
                        'key' => __('Item Price', 'wc-bundle-products'),
                        'value' => wc_price($item['price'])
                    );
                    $item_data[] = array(
                        'key' => __('Extra Price', 'wc-bundle-products'),
                        'value' => wc_price($item['extra_price'])
                    );
                }
            }
        }

        return $item_data;
    }


}

WC_Bundle_Product_Admin::init();
