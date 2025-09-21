<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

global $product;

if ($product->is_purchasable()) : ?>
<?php
$bundle_minimum_quantity = get_post_meta($product->get_id(), '_bundle_general_minimum_quantity', true);
$bundle_maximum_quantity = get_post_meta($product->get_id(), '_bundle_general_maximum_quantity', true);
?>


<?php do_action('woocommerce_before_add_to_cart_form'); ?>
<?php
//if ( ! OCWS_Deli::calculate_product_availability( $product->is_purchasable(), $product) ) {
//    $menus = OCWS_Deli_Menus::instance();
//    $product_dates = $menus->find_product_dates($product->get_id());
//    $product_menus_message = OCWS_Deli::generate_product_menus_message($product_dates['weekdays'], $product_dates['dates'], $product_dates['prep_days']);
//    $class = 'ocws-not-available';
//}
//if( !empty($product_menus_message) ) {
//    return;
//} ?>

<div class="bundle-review">




    <?php do_action('oc_bundle_products_before_bundle_review'); ?>
    <?php
    echo '<div class="total-extras-div">' . esc_html__('Bundle Extras:', 'wc-bundle-products') . '<span id="total-extras"></span></div>'; ?>
    <?php do_action('oc_bundle_text_above_quantity');?>

    <form class="cart" method="post" enctype='multipart/form-data'>
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>

        <input type="hidden" name="bundle_total_price" id="bundle_total_price" value=""/>
        <input type="hidden" name="bundle_product_data" id="bundle_product_data" value=""/>

        <div class="quantity">
            <button class="btn-qty minus" value="minus" type="button"
                    aria-label="<?php _e('button minus', 'oc-main-theme') ?>">-
            </button>
            <input type="number" id="bundle_quantity" name="quantity" value="<?php echo $bundle_minimum_quantity; ?>"
                   min="<?php echo $bundle_minimum_quantity; ?>" max="<?php echo $bundle_maximum_quantity;?>"/>
            <button class="btn-qty plus" value="plus" type="button"
                    aria-label="<?php _e('button plus', 'oc-main-theme') ?>">+
            </button>
        </div>

        <?php do_action('woocommerce_after_quantity_input_field'); ?>

        <button type="submit" class="single_add_to_cart_button button alt disabled" disabled
                value="<?php echo $product->get_id(); ?>"><?php echo esc_html($product->single_add_to_cart_text()); ?>
            <span class="update_price"></span></button>

        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
    </form>

    <?php do_action('woocommerce_after_add_to_cart_form'); ?>

    <?php endif; ?>



