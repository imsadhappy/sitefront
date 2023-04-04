(function($) {

var $win = $(window), $doc = $(document), $bod = $(document.body)

;$win

.on(
'resize',
function() {
    $.sitefront.resize.HeaderSearch();
})

.on(
'resizeEnd',
function() {
    $.sitefront.resize.all();
})

.on(
'scrollDown',
function() {
    $('.wp-load-posts').not('.wp-load-posts-disabled').each(function() {
        $(this).trigger('wpLoadPostsWhenLastVisible');
    });
})


;$bod

.on(
'updated_checkout',
function(event) {
    if (typeof autocheckout != 'undefined') {
        $('#place_order').trigger('click');
    } else {
        $.sitefront.checkbox.checkTerms();
    }
})

.on(
'wc_fragments_refreshed',
function() {
    $.sitefront.resize.HeaderSearch();
})

.on(
'updated_wc_div',
function() {
    $('.quantity input.qty').wcQuantityButtons();
})

;$doc

.on(
'goTo',
function(event, url, element) {
    if (typeof url != 'undefined' && url != '' && url != 'javascript:void(0)') {
        window[typeof goToHandler == 'function' ? 'goToHandler' : 'goTo'](url, element);
    }
})

.on(
'click touch',
'[href="#"], [href=""], [href="javascript:void(0)"], .prevent-default, .current-menu-item',
eventFalse)

.on(
'click touch',
'.scroll-top',
function(event) {
    event.preventDefault();
    $('html, body, .site').animate({ scrollTop: 0 }, 'slow');
})

.on(
'click touch',
'[data-goto]',
function(event) {
    $doc.trigger('goTo', [$(this).data('goto'), $(this)])
})

.on(
'click touch close',
'.toggler',
function(event) {
    var e = $(this);
    e.find('.toggler-state').toggleClass('hidden');
    event.preventDefault();
    if (!e.data('target')) {
        e.toggleClass('active');
        var target = e.nextAll('.toggled:first');
        target.toggleClass('active');
        if (e.hasClass('scroll-to') && e.hasClass('active')) {
            $('html, body, .site').animate({ scrollTop: target.offset().top }, 100);
        }
    } else {
        $.each(e.data('target').split(','), function(n, id) {
            var c = e.data('class');
            $(id).toggleClass(c ? c : 'hidden');
        });
    };
    /* let the window know what you do to it, maybe smth. else will notice it */
    setTimeout(function(){
        $win.trigger('resizeEnd');
    }, 10);
})

.on(
'click touch close',
'.storefront-handheld-footer-bar > ul > li > a',
function(event) {
    var e = $(this);
    if (e.parent('li').hasClass('search')) {
        event.preventDefault();
    } else if (e.data('target')) {
        event.preventDefault();
        $(e.data('target')).trigger('click');
    } else if (e.hasClass('account-menu')) {

    } else {
        return;
    }
    e.parent('li').toggleClass('active');
    if (event.type != 'close') {
        e.parent('li').siblings('.active').children('a').trigger('close');
    }
    $doc.trigger('toggleBorderlessFooterbar');
    /* let the window know what you do to it, maybe smth. else will notice it */
    setTimeout(function(){
        $win.trigger('resizeEnd');
    }, 10);
})

.on(
'click touch',
'.handheld-navigation .menu-item-has-children > a',
function(event) {
    if (this.href == 'javascript:void(0)' || this.href == '#') {
        $(this).next('.dropdown-toggle').trigger('click');
    }
})

.on(
'touchstart touchend touchcancel touchmove',
'img, .button, button, tr.cart_item, input[type="submit"], input[type="button"], .woocommerce-orders-table tr.order',
function(event) {
    if (event.type == 'touchstart') {
        $(this).addClass('touched');
    } else {
        $(this).removeClass('touched');
    }
})

.on(
'click touch mouseenter mouseleave',
'.site-header .menu-item, .site-header-cart',
function(event) {
   $.sitefront.header.find('.focus, .blocked').removeClass('focus blocked');
})

.on(
'toggleBorderlessFooterbar',
function(event) {
    if ($.sitefront.footerbar.find('li.active').length > 0) {
        $bod.addClass('footer-bar-search-visible');
        $.sitefront.footerbar.addClass('borderless');
    } else {
        $bod.removeClass('footer-bar-search-visible');
        $.sitefront.footerbar.removeClass('borderless');
    }
})

.on(
'click touch change',
'.checkout #terms',
function (event) {
    $.sitefront.checkbox.checkTerms();
})

.on(
'click touch',
'a.FoUC',
function(event) {
    FoUC(0);
})

.on(
'submit',
'.login, .register, .cart, .checkout, .FoUC',
function(event) {
    FoUC(0, 'submit');
})

.on(
'click touch',
'.trigger-reload',
function(event) {
    event.preventDefault();
    FoUC(0, 'trigger-reload');
    setTimeout(function() {
        location.reload();
    }, 500);
})

.on(
'focusin focusout',
'.site-header .site-search',
function(event) {
    $(this)[event.type=='focusin'?'addClass':'removeClass']('expanded');
    $(this).next('.main-navigation')[event.type=='focusin'?'addClass':'removeClass']('hidden');
    $.sitefront.resize.HeaderSearch();
})

.on(
'wc-product-gallery-after-init',
function(event) {
    setTimeout(function(){
        $.sitefront.resize.GroupedTable();
    }, 200);
})

.on(
'change',
'#locale',
function(event) {
    event.preventDefault();
    FoUC(0, 'trigger-reload');
    $(this).parent('form').submit();
})

.on(
'load',
'iframe',
function(event) {
    setTimeout(function(){
        $win.trigger('resizeEnd');
    }, 10);
})

.on(
'platformSet',
function(event, data) {
    if (typeof data.eventType != 'undefined') {
        switch (data.eventType) {
            case 'keyboardDidShow':
                $.sitefront.footerbar.hide();
                break;
            case 'keyboardDidHide':
                $.sitefront.footerbar.show();
                break;
        }
    } else {
        FoUC(1);
    }
})

.on(
'wpPostsLoading wpPostsLoaded wpPostsNone wpPostBeforeView wpPostView wpPostHide',
'.wp-load-posts',
function(event) {
    if ($(this).next('.lazyloading').length == 0) {
        $(this).after('<div class="lazyloading">'+loadingIndicator+'</div>');
    }
    var target = $(this).next('.lazyloading');
    switch (event.type) {
        case 'wpPostsLoading':
            target.show();
                break;
        case 'wpPostsLoaded':
            $.sitefront.ready();
                break;
        case 'wpPostBeforeView':
            target.data('prevState', target.html()).html(loadingIndicator).show();
                break;
        case 'wpPostView':
            target.hide();
                break;
        case 'wpPostHide':
            target.html(target.data('prevState')).show();
                break;
        case 'wpPostsNone':
            target.html('<a href="javascript:void(0)" class="fa fa-arrow-up scroll-top">&nbsp;</a>');
            target.show();
                break;
    }
})

.on(
'wpLoadPostsWhenLastVisible',
'.wp-load-posts',
function() {
    if ($(this).children().last().partiallyVisible()) {
        $(this).wpLoadPosts();
    }
})

.on(
'wpPostsLoaded',
'.wp-load-posts',
function() {
    $(this).trigger('wpLoadPostsWhenLastVisible');
})

.on(
'wcQuantityInputFieldLoaded sitefrontReady',
function() {
    $('.quantity input.qty').wcQuantityButtons()
}
)

.on(
'wcQuantityInputFieldUpdated',
function() {
    $('[name="update_cart"]').click();
}
)

/*
.on('click touch',
'[href]',
function(event) {
    if ((this.href == '') ||
        (this.href == '#') ||
        (this.ariaHasPopup) ||
        (this.onclick != null) ||
        (this.target == '_blank') ||
        (this.href == 'javascript:void(0)') ||
        (this.href.match(/.(jpg|jpeg|png|gif|pdf)$/i)) ||
        (2 === event.which || event.metaKey || event.ctrlKey) ||
        (this.href == location.href.replace(location.search, ''))) return;
            FoUC(0, 'href-click')
});
*/

.ready($.sitefront.ready())

})(jQuery);
