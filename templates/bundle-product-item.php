<?php
defined('ABSPATH') || exit;

global $product;
?>
<li class="product bundle-item" data-product-id="<?php echo esc_attr($product_new->get_id()); ?>"
    data-product-price="<?php echo esc_attr($product_new->get_price()); ?>"
    data-level-name="<?php echo esc_attr($level_title); ?>" data-level-id="<?php echo esc_attr($level_id); ?>">
    <div class="bundle-item-wrap">

    <div class="bundle-item-thumbnail">
        <div href="<?php echo esc_url(get_permalink($product_new->get_id())); ?>">
            <?php echo $product_new->get_image('oc-product-archive-thumbnail'); // Smaller image size ?>
        </div>

        <div class="bundle-item-quantity">
            <div class="bundle-item-quantity-content">
            <?php if ($normal_bundle || $editable) {
                $readonly = 'readonly';?>
                <input type="number" <?php echo $readonly; ?>
                       name="bundle_product_quantity_<?php echo esc_attr($product->get_id()); ?>"
                       value="<?php echo esc_attr($min_quantity); ?>" min="<?php echo esc_attr($min_quantity); ?>"
                       max="<?php echo esc_attr($max_quantity); ?>"/>

            <?php } else {
                $readonly = '';?>
                <button class="btn-qty minus" value="minus" type="button"
                        aria-label="<?php _e('button minus', 'oc-main-theme') ?>">-
                </button>

                <input type="number" <?php echo $readonly; ?>
                       name="bundle_product_quantity_<?php echo esc_attr($product->get_id()); ?>"
                       value="<?php echo esc_attr($min_quantity); ?>" min="<?php echo esc_attr($min_quantity); ?>"
                       max="<?php echo esc_attr($max_quantity); ?>"/>
                <button class="btn-qty plus" value="plus" type="button"
                        aria-label="<?php _e('button plus', 'oc-main-theme') ?>">+
                </button>

            <?php }
            ?>

            </div>
        </div>
    </div>
    <div class="bundle-item-details">
        <div class="prod-url" href="<?php echo esc_url(get_permalink($product_new->get_id())); ?>">
<!--            <span class="prod-name">--><?php //echo esc_html(WC_Bundle_Product_Frontend::get_full_product_name($product_new)); ?><!--</span>-->
            <span class="prod-name"><?php echo esc_html($product_new->get_name()); ?></span>
            <?php if ($prod_notice):?>
            <span class="prod-notice"><?php echo esc_html($prod_notice); ?></span>
            <?php endif;?>
        </div>


        <?php if ($show_desc == 'yes') : ?>
            <div class="bundle-product-description">
                <?php echo wp_kses_post($product_new->get_short_description()); ?>
            </div>
        <?php endif; ?>
        <?php if($show_price == 'yes'):?>
        <div class="bundle-item-price">
            <label><?php esc_html_e('מחיר ליחידה:', 'wc-bundle-products'); ?></label>
            <p><?php echo wc_price($product_new->get_price()); ?></p>
        </div>
        <?php endif;?>

        <?php if ($extra_price) : ?>
            <div class="bundle-item-extra-price" data-extra-price="<?php echo $extra_price; ?>">
                <p><?php esc_html_e(' ש"ח +', 'wc-bundle-products'); ?><?php echo $extra_price; ?></p>
            </div>
        <?php endif; ?>
        <?php if ($editable) : ?>
            <button class="edit-bundle-item-button" data-product-id="<?php echo esc_attr($product_new->get_id()); ?>"
                    data-product-name="<?php echo esc_attr($product_new->get_name()); ?>"
                    data-bundle-id="<?php echo esc_attr($product->get_id()); ?>"
                    data-level="<?php echo esc_attr($level_id); ?>"
                    data-currently="<?php echo esc_attr($product_new->get_id()); ?>"
            >
                <?php esc_html_e('החלף מוצר', 'wc-bundle-products'); ?>
            </button>
        <?php endif; ?>
    </div>
    </div>
</li>
