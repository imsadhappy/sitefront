'undefined' == typeof jQuery.sitefront, function($) {

$.sitefront = $.sitefront || {
    header: $('.sitefront-header'),
    footerbar: $('.storefront-handheld-footer-bar')
};

$.sitefront.ready = $.sitefront.ready || function() {

    this.wpLoadPostsHooks();
    this.changeLinks();
    this.resize.all();
    this.checkbox.all();

    if (!window.isPhonegap) {
        FoUC(1, 'pageshow');
    }

    $(document).trigger('sitefrontReady', this);
};

$.sitefront.unload = $.sitefront.unload || function() {
    /* Unload *//*

        window.addEventListener('pagehide', function(event) {
            FoUC(0, event.type);
        });

        window.addEventListener('popstate', function(event) {
            FoUC(0, event);
        });

        window.addEventListener('onbeforeunload', function(event) {
            FoUC(0, event.type);
        });

        window.addEventListener('visibilitychange', function(event) {
            FoUC(document.visibilityState != 'hidden', event.type)
        });
        */
};

$.sitefront.resize = $.sitefront.resize || {
    all: function(e) {
        e = typeof e == 'string' ? [e] : (e || []);
        $.each(this, function(i, f) {
            if (i != 'all' && e.indexOf(i) < 0) f();
        });
        $(document).trigger('sitefrontResizeAll', this);
    },
    HeaderSearch: function(callback) {
        var parent = $('.site-header') || $(),
            target = parent.find('.site-search'),
            cart = parent.find('.cart-contents span'),
            w = parent.find('.col-full').width();
        if (target.length > 0 && ! parent.hasClass('no-search-resize')) {
            $.each(target.siblings(), function(i, e) {
                if (!$(e).hasClass('hidden')) {
                    w -= $(e).outerWidth(true);
                }
            });
            w = Math.round(w)-20;
            target.width(w);
            target[w<200?'hide':'show']();
            cart[w<10?'hide':'show']();
            $.sitefront.header[w<200?'addClass':'removeClass']('no-search');
            if (typeof callback == 'function') {
                callback();
            }
        }
    },
    GroupedTable: function() {
        var target = $('table.woocommerce-grouped-product-list.group_table').parent();
        if (target.length > 0) {
            var trs = target.find('tr'),
                trCount = trs.length,
                trHeight = trs.first().height();
            if (trCount > 4) {
                var x = $('.woocommerce-tabs').outerHeight(),
                    y = $('.woocommerce-tabs').offset().top,
                    z = target.offset().top,
                    h = Math.floor((x+y-z) / trHeight);
                target.addClass('toggled');
                target.prev('.toggler').removeClass('hidden');
                target.height(h > 0 && window.innerWidth > 768 ? ((h-1) * trHeight + h - 3 - trHeight) : (trHeight * 4 + 3));
            }
        }
    },
    YoutubeFrame: function() {
        $('iframe[src*="youtube.com"]').each(function(i, e) {
            e.height = $(e).outerWidth() / 16 * 9
        });
    },
    Adminbar: function() {
        var parent = $('#wp-toolbar'),
            target = $('#wp-admin-bar-root-default');
        if (parent.length > 0 && target.length > 0) {
            $.each(target.children().get().reverse(), function(i, e) {
                $('a', $(e)).css('font-size', '')
                .css('font-size', parent.find('> ul, > ul > li').get().some(function(e){ return e.offsetTop > 0 }) ? 0 : '');
            });
        }
    },
    SiteContent: function() {
        var target = $('#content'),
            gap = window.innerHeight - $('body').outerHeight(true),
            adminbar = $('#wpadminbar').height();
        if (target.length > 0 && gap > 0) {
            target.css('min-height', target.outerHeight() - (adminbar ? adminbar : 0) + gap);
        }
    }
};

$.sitefront.wpLoadPostsHooks = $.sitefront.wpLoadPostsHooks || function() {

    if (typeof $.fn.wpPosts != 'function') return;

    /*
    $.wpPostTemplate('post', function(html) {
        return $('<div>'+html+'</div>').find('.hentry');
    });
    */

    /*
    $.wpPostTemplate('featured_products', function(html) {
        return $(html).find('.product');
    });
    */

    if (!$('body').hasClass('wc-catalog-lazyload-enabled'))
        return;

    $('.post-type-archive-product .products')
    .not('.wp-load-posts')
    .wpPosts({
        post_type: 'products',
        page: 2
    }, function(html) {
        return $('<div>'+html+'</div>').find('.product');
    })
    .trigger('wpLoadPostsWhenLastVisible');

    $('.storefront-featured-products .products')
    .not('.wp-load-posts')
    .wpPosts({
        post_type: 'featured_products',
        page: 2
    }, function(html) {
        return $('<div>'+html+'</div>').find('.product');
    })
    .trigger('wpLoadPostsWhenLastVisible');

    $('.blog-home .site-main')
    .not('.wp-load-posts')
    .each(function() {
        $(this).wpPosts({
            posts_per_page: 4,
            offset: $(this).children('.hentry')
                    .not('.sticky')
                    .length,
            ignore_sticky_posts: true
        }, function(html) {
            return $('<div>'+html+'</div>').find('.hentry');
        })
        .trigger('wpLoadPostsWhenLastVisible')
        .find('#post-navigation')
        .remove()
    });
};

$.sitefront.changeLinks = $.sitefront.changeLinks || function() {

    $('a[rel="home"], \
        a[rel="bookmark"], \
        [href].checkout-button, \
        .wc-block-grid__product-link, \
        .woocommerce-loop-product__link, \
        .woocommerce-MyAccount-navigation-link > a[href]')
    .addClass('FoUC');

    $('[href].goto')
    .not('[data-goto]')
    .each(function(i, e) {
        $(e).attr({
            'data-goto': $(e).attr('href'),
            'href': '#'
        })
    });

    $('a[href="#"], a[href=""]').each(function(i, e) {
        $(e).attr('href', 'javascript:void(0)')
    });

    $('[class="search-field]"').each(function(i, e) {
        $(e).attr('autocomplete', 'off')
    });
};

$.sitefront.checkbox = $.sitefront.checkbox || {
    all: function(e) {
        e = typeof e == 'string' ? [e] : (e || []);
        $.each(this, function(i, f) {
            if (i != 'all' && e.indexOf(i) < 0) f();
        });
    },
    rememberMe: function() {
        var cb = $('#rememberme');
        if (cb.length > 0 && cb.not(':checked')) {
            cb.trigger('click')
              .prop('checked', true)
              .hide()
              .parent('label')
              .hide();
        }
    },
    shipToDifferentAddress: function() {
        var cb = $('#ship-to-different-address-checkbox');
        if (cb.length > 0 && cb.not(':checked')) {
            cb.trigger('click')
              .prop('checked', false);
        }
    },
    checkTerms: function() {
        var cb = $('.checkout #terms'),
            target = $('.checkout').find('#place_order');
        if (cb.length > 0 && target.length > 0) {
            target.attr('disabled', ! cb.is(':checked'));
        }
    }
};

}(jQuery);