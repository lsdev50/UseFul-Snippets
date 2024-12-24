jQuery(function ($) {
    if (!$('body').hasClass('single-product')) return;

    function updateVariations() {
        var variationsData = $('form.variations_form').attr('data-product_variations');
        if (!variationsData) return;

        var variations = JSON.parse(variationsData);
        var selectedAttributes = {};

        $('form.variations_form ul').each(function () {
            var attributeName = $(this).attr('data-attribute_name');
            var selectedValue = $(this).find('li.selected').attr('data-value');

            if (selectedValue) {
                selectedAttributes[attributeName] = selectedValue;
            }
        });

        $('form.variations_form ul li').removeClass('disabled').css('pointer-events', '').css('opacity', '');

        $('form.variations_form ul').each(function () {
            var attributeName = $(this).attr('data-attribute_name');

            $(this).find('li').each(function () {
                var currentValue = $(this).attr('data-value');
                var isAvailable = false;

                variations.forEach(function (variation) {
                    var isValid = true;

                    for (var key in selectedAttributes) {
                        if (key === attributeName) continue;
                        if (variation.attributes[key] !== selectedAttributes[key]) {
                            isValid = false;
                            break;
                        }
                    }

                    if (isValid && variation.attributes[attributeName] === currentValue && variation.is_in_stock) {
                        isAvailable = true;
                    }
                });

                if (!isAvailable) {
                    $(this).addClass('disabled').css('pointer-events', 'none').css('opacity', '0.5');
                }
            });
        });
    }
    updateVariations();

    $('form.variations_form').on('click change', 'ul li, select', function () {
        if (!$(this).hasClass('reset_variations')) {
            updateVariations();
        }
    });

    $('form.variations_form').on('click', '.reset_variations', function () {
        $('form.variations_form ul li').removeClass('selected');
        $('form.variations_form select').val('');
        updateVariations();
    });
});

