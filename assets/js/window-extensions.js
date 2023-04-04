/* coplete event cancel */

window.eventFalse = window.eventFalse || function (event) {
	event.preventDefault();
	event.returnValue = false;
	return false;
};

/* prevent flash of unstyled content */

window.FoUC = window.FoUC || function (start, caller) {
	var d = document.documentElement,
		w = window,
		e = {event:start?'load':'unload'};
	if ((start && w.isLoaded) || (!start && w.isUnloaded)) return;
	d.style.opacity = start?1:0;
	d.classList.remove(start?'unloading':'loaded');
	d.classList.add(start?'loaded':'unloading');
	w.postMessage(e, '*');
	w.parent.postMessage(e, '*');
	w.isLoaded = start;
	w.isUnloaded = !start;
};

/* go to link without FOUC */

window.goTo = window.goTo || function (link, element){
	if (link == '#'|| link == '') return;
	FoUC(0, link);
	location.href = link;
};

/* resizeEnd event */

window.resizeEventTimeout = window.resizeEventTimeout || false;
window.resizeEventTime = window.resizeEventTime || null;
window.resizeEventDelta = window.resizeEventDelta || 200;

window.resizeEventEnd = window.resizeEventEnd || function() {
	if (new Date() - resizeEventTime < resizeEventDelta) {
		setTimeout(resizeEventEnd, resizeEventDelta);
	} else {
		resizeEventTimeout = false;
		setTimeout(function() {
			window.dispatchEvent(new CustomEvent('resizeEnd'));
		}, 50);
	}
};

window.addEventListener('resize', function (event) {
	resizeEventTime = new Date();
	if (resizeEventTimeout !== false) return;
	resizeEventTimeout = true;
	setTimeout(resizeEventEnd, resizeEventDelta);
});

/* scrollDown & scrollUp event */

window.previousPageYOffset = window.previousPageYOffset || 0;

window.addEventListener('scroll', function (event) {
	window.dispatchEvent(new CustomEvent(window.pageYOffset > window.previousPageYOffset ? 'scrollDown' : 'scrollUp'));
	window.previousPageYOffset = window.pageYOffset;
});

/* helpers */

window.getSafeAreaInset = window.getSafeAreaInset || function (pos) {
    return parseInt(getComputedStyle(document.documentElement).getPropertyValue("--safe-area-inset-" + pos).replace('px', ''));
};

window.getScrollBarWidth = window.getScrollBarWidth || function () {
    var inner = document.createElement('p');
    inner.style.width = "100%";
    inner.style.height = "200px";
    var outer = document.createElement('div');
    outer.style.position = "absolute";
    outer.style.top = "0px";
    outer.style.left = "0px";
    outer.style.visibility = "hidden";
    outer.style.width = "200px";
    outer.style.height = "150px";
    outer.style.overflow = "hidden";
    outer.appendChild (inner);
    document.body.appendChild (outer);
    var w1 = inner.offsetWidth;
    outer.style.overflow = 'scroll';
    var w2 = inner.offsetWidth;
    if (w1 == w2) {
        w2 = outer.clientWidth;
    }
    document.body.removeChild (outer);
    return (w1 - w2);
};

window.getScreenOrientation = window.getScreenOrientation || function () {
    if (typeof window.matchMedia != 'function') return 'unknown';
    var landscape = window.matchMedia("(orientation: landscape)");
    return landscape.matches ? 'landscape':'portrait';
};

window.i18n = window.i18n || function (str) {
	return (window.i18n_vocabulary || {})[str] || str;
};

String.prototype.i18n = function() {
	return window.i18n(this.toString());
};

window.stringReplaceVars = window.stringReplaceVars || function (template, vars) {
	vars = typeof vars == 'object' ? vars : { vars };
	Object.keys(vars).forEach(function(key) {
		var pattern = '{{' + key + '}}';
		template = template.replace(new RegExp(pattern, 'g'), vars[key]);
	});
	return template;
};

String.prototype.replaceVars = function(vars) {
	return window.stringReplaceVars(this.toString(), vars);
};
