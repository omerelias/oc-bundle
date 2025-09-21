jQuery(document).ready(function($) {


    function toggleBundlePriceFields() {
        var bundlePriceType = $('#_bundle_price_type').val();

        if (bundlePriceType === 'fixed') {
            $('#bundle-fixed-price-setting').show();
            $('#bundle-discount-settings').show();
        } else {
            $('#bundle-discount-settings').show();
            $('#bundle-fixed-price-setting').hide();
        }
    }

    function toggleLevelFields() {
        var numberOfLevels = $('#_bundle_number_of_levels').val();
        for (var i = 1; i <= 5; i++) {
            if (i <= numberOfLevels) {
                $('#level-' + i + '-settings').show();
            } else {
                $('#level-' + i + '-settings').hide();
            }
        }
    }

    function initializeTabs() {
        $('.nav-tab-wrapper').each(function() {
            $(this).find('.nav-tab').first().addClass('nav-tab-active').trigger('click');
        });
        $('.inner-tab-content').each(function() {
            if (!$(this).hasClass('nav-tab-active')) {
                $(this).hide();
            }
        });
    }

    function initializeAccordions() {
        $('.product-settings-accordion').each(function() {
            $(this).accordion({
                heightStyle: "content",
                collapsible: true,
                active: false
            });
        });
    }

    function initializeSortableSelect() {
        // Initialize Select2 with default settings for .wc-product-search elements
        $('.wc-product-search').each(function() {
            var $select = $(this);

            // $select.select2({
            //     // Your Select2 initialization options
            // });

            // When an item is selected, refresh the sortable container
            $select.on('select2:select', function (e) {
                refreshSortable($select.next('.select2-container').find('ul.select2-selection__rendered'));
            });

            // Initialize sortable on the Select2 rendered container
            refreshSortable($select.next('.select2-container').find('ul.select2-selection__rendered'));
        });
    }
// Function to make Select2 selections sortable
    function refreshSortable(element) {
        element.sortable({
            containment: 'parent',
            update: function() {
                $(this).children('li[title]').each(function() {
                    var id = $(this).data('data').id;
                    var element = $(this).closest('.select2-container').prev('select');
                    var option = element.find('option[value="' + id + '"]');
                    option.detach();
                    element.append(option);
                });
            }
        });
    }

// Initialize the sortable Select2 fields
    $(document).ready(function() {
        initializeSortableSelect();
    });


    toggleBundlePriceFields();
    toggleLevelFields();
    initializeTabs();
    initializeAccordions();
    // initializeSortableSelect();

    $('#_bundle_price_type').change(function() {
        toggleBundlePriceFields();
    });

    $('#_bundle_number_of_levels').change(function() {
        toggleLevelFields();
    });

    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).data('tab');

        $(this).closest('.nav-tab-wrapper').find('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $(this).closest('.panel').find('.inner-tab-content').hide();
        $('#' + tab).show();
    });

    // Ensure the first tab of each "big" tab is active on page load
    $('.panel').each(function() {
        $(this).find('.nav-tab-wrapper .nav-tab').first().trigger('click');
    });
    // function updateLevelTabs() {
    //     var numberOfLevels = parseInt($('#_bundle_number_of_levels').val(), 10);
    //
    //     // Show/Hide level tabs
    //     $('.nav-tab[data-tab^="level-"]').each(function(index) {
    //         var level = index + 1;
    //         if (level <= numberOfLevels) {
    //             $(this).show();
    //             $('#level-' + level + '-settings').show();
    //         } else {
    //             $(this).hide();
    //             $('#level-' + level + '-settings').hide();
    //         }
    //     });
    //
    //     // Ensure the first visible tab is activated
    //     var firstVisibleTab = $('.nav-tab:visible').first();
    //     $('.nav-tab').removeClass('nav-tab-active');
    //     firstVisibleTab.addClass('nav-tab-active');
    //     $('.inner-tab-content').hide();
    //     $('#' + firstVisibleTab.data('tab')).show();
    // }
    // Make sure the product data panel is open
    $('#woocommerce-product-data').removeClass('closed');
    $('.postbox-header button.handlediv').attr('aria-expanded', 'true');
    if (wc_bundle_admin_params.product_type === 'bundle') {
        setTimeout(function () {
            $(".general_settings_options a").click();
        }, 100); // Adjust the delay as necessary
    }
});
