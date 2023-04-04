(function($) {
    /*
    $.fn.exists = $.fn.exists || function() {

        var e = $(this).length > 0 ? $(this) : $();

        if (arguments.length == 1 && typeof arguments[0] == 'object') {

            $.each(arguments[0], function(checkFunction, checkValue) {
                if (checkFunction && checkValue && typeof $.fn[checkFunction] == 'function') {
                    e = e[checkFunction](checkValue) ? e : $();
                }
            });

            console.log(e);

        } else if (arguments.length == 2) {

            var checkFunction = arguments[0], checkValue = arguments[1];

            if (checkFunction && checkValue && typeof $.fn[checkFunction] == 'function') {
                e = e[checkFunction](checkValue) ? e : $();
            }
        }

        return e;
    };
    */
    $.fn.visible = $.fn.visible || function(position, partially) {

        var e = $(this);

        if (e.length == 0 || typeof e.offset() != 'object') {
            return false;
        }

        switch (position) {

            case 'left':
            case 'right':
            case 'horizontal':
            case 'horizontally':

                var viewWidth      = $(window).width(),
                    eBox           = e.get(0).getBoundingClientRect(),
                    isVisibleLeft  = partially ? (eBox.left + eBox.width) >= 0 : (eBox.left >= 0) && (eBox.left + eBox.width) <= viewWidth,
                    isVisibleRight = partially ? eBox.left < viewWidth : (eBox.left + eBox.width) <= viewWidth;

                if ('left' === position) {
                    return isVisibleLeft;
                } else if ('right' === position) {
                    return isVisibleRight;
                } else {
                    return isVisibleLeft && isVisibleRight;
                }

            case 'top':
            case 'bottom':
            case 'vertical':
            case 'vertically':
            default:

                var viewTop         = $(window).scrollTop(),
                    viewBottom      = viewTop + $(window).height(),
                    fromTop         = e.offset().top,
                    fromBottom      = fromTop + e.height(),
                    isVisibleTop    = (partially ? fromBottom : fromTop) >= viewTop,
                    isVisibleBottom = (partially ? fromTop : fromBottom) <= viewBottom;

                if ('top'===position) {
                    return isVisibleTop;
                } else if ('bottom'===position) {
                    return isVisibleBottom;
                } else {
                    return isVisibleTop && isVisibleBottom;
                }
        }
    };

    /* syntactic sugar */
    $.fn.partiallyVisible = $.fn.partiallyVisible || function(position) {
        return $(this).visible(position, true);
    };

    $.wcAddToCart = $.wcAddToCart || function (productId, qty, callback) {

        if (isNaN(qty)) {
            qty = 1;
        }

        if (isNaN(productId)) {
            productId = parseInt(productId);
        }

        if (isNaN(productId) || typeof window.woocommerce_params == 'undefined') {
            callback(false);
        }

        $.post(woocommerce_params.wc_ajax_url.replace( '%%endpoint%%', 'add_to_cart' ), {product_id: productId, quantity: qty})
        .done(function(result) {
            callback(result);
        })
        .fail(function(request, status, error) {
            callback(error);
        });
    };

    $.fn.wcQuantityButtons = $.fn.wcQuantityButtons || function() {
        $(this).each(function(_, input){
            function getAttributeIntValue(element, attributeName, defaultValue) {
                var value = $(element).attr(attributeName);
                return value == '' || typeof value == 'undefined' ? defaultValue : parseInt(value, 10);
            }
            if (input.readOnly || $(input).hasClass('quantity-dec-inc-loaded')) return;
            $(input).before('<span class="quantity-dec" style="cursor:pointer">–</span>')
                    .after('<span class="quantity-inc" style="cursor:pointer">+</span>');
            $('.quantity-inc', $(input).parent()).on('click', function(){
                var max = getAttributeIntValue(input, 'max', Infinity),
                    newValue = parseInt(input.value, 10) + 1,
                    value = newValue < max ? newValue : max;
                $(input).attr('value', value).prop('value', value).trigger('change');
                document.dispatchEvent(new CustomEvent('wcQuantityInputFieldUpdated'));
                $('[name="update_cart"]').click();
            });
            $('.quantity-dec', $(input).parent()).on('click', function(){
                var min = getAttributeIntValue(input, 'min', 1),
                    newValue = parseInt(input.value, 10) - 1,
                    value = newValue > min ? newValue : min;
                $(input).attr('value', value).prop('value', value).trigger('change');
                document.dispatchEvent(new CustomEvent('wcQuantityInputFieldUpdated'));
                $('[name="update_cart"]').click();
            });
            $(input).parent().addClass('quantity-dec-inc-loaded');
        })
    };

})(jQuery);
