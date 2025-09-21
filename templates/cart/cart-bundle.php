<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
<?php do_action( 'woocommerce_before_cart_table' ); ?>

    <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
    <thead>
    <tr>
        <th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
        <th class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
        <th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
        <th class="product-subtotal"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
    </tr>
    </thead>
    <tbody>
<?php do_action( 'woocommerce_before_cart_contents' ); ?>

<?php
foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
        ?>
        <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

            <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                <?php
                if ( ! $product_permalink ) {
                    echo wp_kses_post( $_product->get_name() . '&nbsp;' );
                } else {
                    echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ) );
                }

                echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.
                ?>
            </td>

            <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
                <?php
                echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
                ?>
            </td>

            <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                <?php
                if ( $_product->is_sold_individually() ) {
                    $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                } else {
                    $product_quantity = woocommerce_quantity_input( array(
                        'input_name'   => "cart[{$cart_item_key}][qty]",
                        'input_value'  => $cart_item['quantity'],
                        'max_value'    => $_product->get_max_purchase_quantity(),
                        'min_value'    => '0',
                        'product_name' => $_product->get_name(),
                    ), $_product, false );
                }

                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
                ?>
            </td>

            <td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
                <?php
                echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
                ?>
            </td>
        </tr>
        <?php
    }
}

do_action( 'woocommerce_cart_contents' );
?>

<tr>
    <td colspan="6" class="actions">

<?php if ( wc_coupons_enabled() ) { ?>
    <div class="coupon">
        <label for="coupon_code"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
        <?php do_action( 'woocommerce_cart_coupon' ); ?>
    </div>
<?php } ?>

    <button type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

<?php do_action( 'woocommerce_cart_actions' ); ?>

<?php
wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                </td>
            </tr>

            <?php do_action( 'woocommerce_after_cart_contents' ); ?>
        </tbody>
    </table>

    <?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_after_cart' ); ?>
