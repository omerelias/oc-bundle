jQuery(document).ready(function($) {

    function openReplacementProductsDialog(bundleId, productId, level,name='',currently='') {
        $.ajax({
            url: wc_bundle_products_params.ajaxurl,
            method: 'POST',
            data: {
                action: 'get_replacement_products',
                bundle_id: bundleId,
                product_id: productId,
                level_index: level,
                product_name: name,
                _ajax_nonce: wc_bundle_products_params.edit_bundle_item_nonce,
            },
            success: function(response) {

                // Remove any existing dialog to prevent duplicates
                $('#replacement-products-dialog').remove();

                var dialogHtml = '<div id="replacement-products-dialog" title="בחר מוצר חלופי">' +
                    '<div class="popup-title">בחר מוצר חלופי ל-' + name + '</div>' +
                '<ul>';
                response.data.products.forEach(function(product) {
                    console.log(product.id, currently);
                    if(product.id == currently) return;
                    dialogHtml += `
                    <li class="product bundle-item" data-product-id="${product.id}" data-product-price="${product.price}" data-product-url="${product.url}" data-product-image="${product.image}">
                        <div class="bundle-item-thumbnail">
                            <a href="${product.url}">
                                <img src="${product.image}" alt="${product.name}" />								
                                <span class="switch-item">החלף</span>
                            </a>
                        </div>
                        <div class="bundle-item-details">
                            <a class="prod-url" href="${product.url}">
                                <span class="prod-name">${product.name}</span>
                            </a>
                            <div class="bundle-item-extra-price" data-extra-price="${product.price}">
<!--                                <p>תוספות: ${product.price}</p>-->
                            </div>
                        </div>
                    </li>`;
                });

                dialogHtml += '</ul></div>';

                $('body').append(dialogHtml);

                $('#replacement-products-dialog').dialog({
                    modal: true,
                    width: 550,
                    position: { my: "center", at: "center", of: window },
                    open: function(event, ui) {
                        $('body').addClass('no-scroll');
                    },
                    close: function() {
                        $(this).remove();
                        $('body').removeClass('no-scroll');
                    }
                });

                // Event listener for selecting a product
                $('#replacement-products-dialog li').on('click', function() {
                    var newProductId = $(this).data('product-id');
                    console.log(newProductId);
                    var newProductPrice = $(this).data('product-price');
                    var newProductImage = $(this).data('product-image');
                    var newProductUrl = $(this).data('product-url');
                    // console.log('Selected product:', {
                    //     id: newProductId,
                    //     price: newProductPrice,
                    //     name: $(this).text()
                    // }); // Log selected product data

                    var productElement = $('.bundle-level-details[data-level-index="' + level + '"] .bundle-item[data-product-id="' + productId + '"]');
                    // console.log(productElement);
                    // Update hidden input values
                    var hiddenInput = $('input[name="bundle_product_quantity_' + productId + '"]');
                    hiddenInput.attr('name', 'bundle_product_quantity_' + newProductId);
                    // hiddenInput.attr('data-product-id', newProductId);

                    var hiddenProductIdInput = $('input[name="bundle_product_id[]"][value="' + productId + '"]');
                    hiddenProductIdInput.val(newProductId);

                    var hiddenLevelNameInput = $('input[name="bundle_product_level_name_' + productId + '"]');
                    hiddenLevelNameInput.attr('name', 'bundle_product_level_name_' + newProductId);

                    productElement.attr('data-product-id', newProductId);
                    productElement.attr('data-product-price', newProductPrice);
                    productElement.attr('data-currently', newProductId);
                    productElement.attr('data-product-image', newProductImage);
                    productElement.attr('data-product-url', newProductUrl);
                    productElement.find('.prod-name').text($(this).find('.prod-name').text());
                    productElement.find('.prod-url').attr('href', newProductUrl);
                    // productElement.data('product-id', newProductId);

                    var editButton = productElement.find('.edit-bundle-item-button');
                    editButton.attr('data-product-id', newProductId);
                    editButton.attr('data-currently', newProductId);
                    editButton.parent().parent().parent().attr('data-currently', newProductId);

                    // Update the image source
                    var productImage = productElement.find('img');
                    productImage.attr('src', newProductImage);

                    console.log('Updated product element:', {
                        id: productElement.data('product-id'),
                        price: productElement.data('product-price'),
                        image: productElement.data('product-image')
                    });

                    // Close the dialog
                    $('#replacement-products-dialog').dialog('close');

                    // Update the dynamic pricing
                    updateDynamicPricing();
                    updateHiddenInputs();
                    updateSelectedProductsCount();
                    updateTotalExtras();
                });
            },
            error: function() {
                alert('An error occurred while fetching replacement products.');
            }
        });
    }

    function updateDynamicPricing() {
        let priceType = wc_bundle_product_meta._bundle_price_type || 'dynamic';
        let discountType = wc_bundle_product_meta._bundle_discount_type || 'fixed';
        let discountValue = parseFloat(wc_bundle_product_meta._bundle_discount_value) || 0;
        let totalPrice = 0;
        let totalExtra = 0;
        let newTot = 0;
        $('.bundle-level-details').each(function() {
            $(this).find('.bundle-item').each(function() {
                let quantity = $(this).find('input[type="number"]').val() || 1;
                let productPrice = parseFloat($(this).attr('data-product-price')) || 0;
                let extraPrice = parseFloat($(this).find('.bundle-item-extra-price').data('extra-price')) || 0;
                totalExtra += quantity * extraPrice;
                totalPrice += quantity * productPrice;
            });
        });

        let discountedPrice = totalPrice;

        // Apply discount
        if (priceType === 'fixed') {
            let fixedPrice = parseFloat(wc_bundle_product_meta._bundle_price) || 0;
            totalPrice = fixedPrice;
            discountedPrice = fixedPrice;
        }

        if (discountType === 'percent') {
            discountedPrice = totalPrice * (1 - discountValue / 100);
        } else if (discountType === 'fixed') {
            discountedPrice = totalPrice - discountValue;
        }

        if (discountedPrice < 0) {
            discountedPrice = 0; // Ensure the total price is not negative
        }

        // Include extra price in total when price type is fixed
        if (priceType === 'fixed') {
            discountedPrice += totalExtra;
        }

        $('#bundle_total_price').val(discountedPrice.toFixed(2));

        let bundleQuantity = $('#bundle_quantity').val();
        if(bundleQuantity==0){
            bundleQuantity = 1;
        }

        if (discountedPrice < totalPrice) {
            $('.bundle-review .price').html('<span style="text-decoration: line-through;">'
                + totalPrice.toFixed(2)
                + '</span>'
                + wc_bundle_products_params.currency_symbol +
                '<span style="color: red;">'
                + wc_bundle_products_params.currency_symbol
                + discountedPrice.toFixed(2)
                + '</span>'
                +
                ' <span style="">' + wc_bundle_product_meta._bundle_text_after_price
                + '</span>');

            $('.update_price').text((totalPrice*bundleQuantity).toFixed(2) + ' ' + wc_bundle_products_params.currency_symbol);
        } else {
            $('.bundle-review .price').text(totalPrice.toFixed(2)
                + ' '
                + wc_bundle_products_params.currency_symbol
                + ' '
                + wc_bundle_product_meta._bundle_text_after_price);

            $('.update_price').text((discountedPrice*bundleQuantity).toFixed(2) + ' ' + wc_bundle_products_params.currency_symbol);
        }
        setTimeout(function() {
            $('body').trigger('priceUpdated');
        }, 100); // Delay in milliseconds
    }

    function updateHiddenInputs() {
        let bundleProductData = [];

        $('.bundle-level-details').each(function() {
            $(this).find('.bundle-item').each(function() {
                let productId = $(this).attr('data-product-id');
                let quantity = $(this).find('input[type="number"]').val() || 1;
                let productPrice = parseFloat($(this).attr('data-product-price')) || 0;
                let extraPrice = parseFloat($(this).find('.bundle-item-extra-price').data('extra-price')) || 0;
                let levelName = $(this).attr('data-level-name') || '';
                let levelId = $(this).attr('data-level-id') || '';

                bundleProductData.push({
                    product_id: productId,
                    quantity: quantity,
                    price: productPrice,
                    extra_price: extraPrice,
                    level_name: levelName,
                    level_id: levelId
                });
            });
        });

        $('#bundle_product_data').val(JSON.stringify(bundleProductData));
    }

    function checkAndHidePrice() {
        // Get the price text and remove any currency symbols or commas
        let priceText = $('.price').text().replace(/[^\d.-]/g, '');
        let priceValue = parseFloat(priceText);

        // Check if the price is 0 and hide the price div if true
        if (priceValue === 0 && wc_bundle_product_meta._bundle_price_type !== 'fixed') {
            $('.price').hide();
            $('.update_price').hide();
        } else {
            $('.price').show();
            $('.update_price').show();
        }
    }

    function checkAndHideExtras() {
        // Get the extras price text and remove any currency symbols or commas
        let extrasText = $('#total-extras').text().replace(/[^\d.-]/g, '');
        let extrasValue = parseFloat(extrasText);

        // Check if the extras price is 0 and hide the extras price span if true
        if (extrasValue === 0) {
            $('.total-extras-div').hide();
        } else {
            $('.total-extras-div').show();
        }
    }

    function updateSelectedProductsCount() {
        let isNext = false;
        let allLevelsAtMax = true;

        $('.bundle-level-details').each(function() {
            let selectedCount = 0;
            let maxCount = parseInt($(this).attr('data-max'));
            let levelIndex = $(this).data('level-index');
            let selectedProducts = [];

            $(this).find('.bundle-item').each(function() {
                let quantity = parseInt($(this).find('input[type="number"]').val()) || 0;
                let productName = $(this).find('.prod-name').text();
                selectedCount += quantity;
                if (quantity > 0) {
                    selectedProducts.push(productName + ' ' + quantity + 'x');
                }
            });

            $(this).find('.selected-products-count').text(selectedCount + '/' + maxCount);

            if (selectedCount < maxCount) {
                allLevelsAtMax = false; // Not all levels are at their max
            }

            // Update the selected products display
            $(this).find('.selected-products').html(selectedProducts.join('<br>'));

            // Close the accordion tab if selectedCount equals maxCount
            if (selectedCount >= maxCount) {
                if(wc_bundle_product_meta._bundle_type === 'self_select'){
                    $(this).find('.bundle-products-list').slideUp();

                }
                $(this).find('.level_title').removeClass('active');
                // $(this).find('.level_title').removeClass('active');
                $(this).addClass('done');
                isNext = true;
            } else {
                if (isNext) {
                    
                    $(this).find('.bundle-products-list').slideDown();
                    $(this).find('.level_title').addClass('active');
                    $(this).removeClass('done');

                    isNext = false;
                }
            }

            // Disable/Enable plus buttons based on the selected count
            $(this).find('.btn-qty.plus').each(function() {
                if (selectedCount >= maxCount) {
                    $(this).attr('disabled', 'disabled');
                } else {
                    $(this).removeAttr('disabled');
                }
            });
        });

        // Enable add-to-cart button if all levels are at max
        if (allLevelsAtMax && (! $('.ocws-availability-message').hasClass('ocws-not-available')) ) {
            $('.single_add_to_cart_button').removeAttr('disabled');
            $('.single_add_to_cart_button').removeClass('disabled');
        } else {
            $('.single_add_to_cart_button').attr('disabled', 'disabled');
            $('.single_add_to_cart_button').addClass('disabled');
        }

    }

// Quantity increment/decrement logic
    $('.bundle-level-details').on('click', '.btn-qty', function() {
        var $input = $(this).siblings('input[type="number"]');
        var currentValue = parseInt($input.val());
        console.log(currentValue);
        var max = parseInt($input.attr('max'));
        var min = parseInt($input.attr('min'));

        if ($(this).hasClass('plus')) {
            if (currentValue < max) {
                $input.val(currentValue + 1);
                // $(this)
                $(this).parent().parent().addClass('active');
            }
        } else if ($(this).hasClass('minus')) {
            if (currentValue > min) {
                $input.val(currentValue - 1);
                $(this).parent().parent().removeClass('active');

            }
        }


        updateDynamicPricing();
        updateHiddenInputs();
        updateSelectedProductsCount();
        updateTotalExtras();
    });

    $('input[type="number"]').on('input', function() {
        updateDynamicPricing();
        updateHiddenInputs();
        updateSelectedProductsCount();
        updateTotalExtras();
    });

    function updateTotalExtras() {
        let totalExtras = 0;

        $('.bundle-level-details').each(function() {
            $(this).find('.bundle-item').each(function() {
                let quantity = $(this).find('input[type="number"]').val() || 1;
                let extraPrice = parseFloat($(this).find('.bundle-item-extra-price').data('extra-price')) || 0;
                totalExtras += quantity * extraPrice;
            });
        });

        $('#total-extras').text(totalExtras + ' ש"ח');
    }




    $(document).on('click', '.edit-bundle-item-button', function(e) {
        console.log(e);
        e.preventDefault();
        let productId = $(this).data('product-id');
        let bundleId = $(this).data('bundle-id');
        let level = $(this).data('level');
        let name = $(this).data('product-name');
        let currently = $(this).parent().parent().parent().data('currently');

        openReplacementProductsDialog(bundleId, productId, level, name, currently);
    });





    $('.bundle-review').on('click', '.btn-qty', function() {
        var $input = $(this).parent().find('input[type="number"]');
        var currentValue = parseInt($input.val());
        var max = parseInt($input.attr('max'));
        var min = parseInt($input.attr('min'));

        if ($(this).hasClass('plus')) {
            if (currentValue < max) {
                $input.val(currentValue + 1);

            }
        } else if ($(this).hasClass('minus')) {
            if (currentValue > min) {
                $input.val(currentValue - 1);
            }
        }

        updateDynamicPricing();
        updateHiddenInputs();
        updateSelectedProductsCount();
        updateTotalExtras();
    });

    $('input[type="number"]').on('input', function() {
        updateDynamicPricing();
        updateHiddenInputs();
        updateSelectedProductsCount();
        updateTotalExtras();
    });

    $('body').on('priceUpdated', function() {
        if (wc_bundle_product_meta._bundle_price_type !== 'fixed') {
            checkAndHidePrice();
        }
        checkAndHideExtras();

    });


    $('.bundle-level-details h3').on('click', function() {
        // $(this).siblings('.bundle-products-list').slideToggle().css('display', 'flex');
        if(wc_bundle_product_meta._bundle_type !== 'self_select') {return}
        $(this).parent().parent().find('.bundle-products-list').slideToggle().css('display', 'flex');
        $(this).toggleClass('active');
    });

    if(wc_bundle_product_meta._bundle_type !== 'editable') {
        $('.bundle-products-list').hide().first().show().css('display', 'flex');
    }

    $(document).on( 'click', 'button.mini-close', function(e) {

    })

    updateDynamicPricing();
    updateHiddenInputs();
    updateSelectedProductsCount();
    updateTotalExtras();
    checkAndHidePrice();
    checkAndHideExtras();






});
