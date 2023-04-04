String.prototype.i18n = String.prototype.i18n || function() {
	var str = this.toString();
	return (window.i18n_vocabulary || {})[str] || str;
};

String.prototype.replaceVars = String.prototype.replaceVars || function(vars) {
	vars = typeof vars == 'object' ? vars : { vars };
	var template = this.toString();
	Object.keys(vars).forEach(function(key) {
		template = template.replace(new RegExp('{{'+key+'}}', 'g'), vars[key]);
	});
	return template;
};

'undefined' == typeof window.installPrompt, function(iOSInstallOverlayConfig, installTriggerConfig) {

window.installPromptEvent;

window.iOSInstallOverlay = (function() {

	document.dispatchEvent(new CustomEvent('iOSInstallOverlayConfig', {'detail':iOSInstallOverlayConfig}));

	var config = Object.assign(iOSInstallOverlayConfig, window.customiOSInstallOverlayConfig);

	function toggleOverlay(on) {
		var content = document.querySelector(".add-to-homescreen");
		if (content) {
			content.style.display = on?'block':'none';
		}
		document.querySelectorAll(config.containers).forEach(function(container){
			if (container) container.classList[on?'add':'remove']('add-to-homescreen-blur');
		});
	}

	function showOverlay () {
		toggleOverlay(1)
	}
	function hideOverlay () {
		toggleOverlay(0)
	}

	function createOverlay(config) {
		var wrapper = document.createElement('div');
		wrapper.innerHTML = config.template.replaceVars(config);
		document.body.appendChild(wrapper);
		var closer = document.querySelector('.add-to-homescreen-close');
		if (closer) closer.addEventListener('click', hideOverlay);
	}

	return {
		init: function() {
			var customConfig = arguments.length <= 0 || arguments[0] === undefined ? config : arguments[0];
			if (customConfig.containers) {
				createOverlay(Object.assign(config, customConfig));
			} else {
				console.error('Blur element is required');
			}
		},
		enabled: ((navigator.userAgent && !navigator.userAgent.match('CriOS') && navigator.userAgent.match(/iPhone/i))||[]).length > 0,
		hide: hideOverlay,
		show: showOverlay
	}
})();

window.installPrompt = (function() {

	document.dispatchEvent(new CustomEvent('installTriggerConfig', {'detail':installTriggerConfig}));

	var config = Object.assign(installTriggerConfig, window.customInstallTriggerConfig);

	function addIntallTrigger() {
		var wrapper = document.createElement('div'),
			trigger = document.getElementById(config.triggerId),
			containers = document.querySelectorAll(config.containers);
		if (!containers) return;
		wrapper.innerHTML = config.template.replaceVars(config);
		containers.forEach(function(container){
			if (container) container.appendChild(wrapper.firstElementChild);
		});
		if (trigger) trigger.addEventListener('click', function(event) {
			event.preventDefault();
			if (window.installPromptEvent) {
				window.installPromptEvent.prompt();
			} else if (window.iOSInstallOverlay.enabled) {
				window.iOSInstallOverlay.init();
				window.iOSInstallOverlay.show();
			}
		});
	}

	return {
		init: function() {
			window.addEventListener('beforeinstallprompt', function(event) {
				event.preventDefault();
				window.installPromptEvent = event;
				addIntallTrigger();
			});
			if (window.iOSInstallOverlay.enabled) {
				addIntallTrigger();
			}
			window.addEventListener('appinstalled', function(event) {
				window.installPromptEvent = null;
				document.getElementById(config.triggerId).style.display = 'none';
			});
		}
	}
})();

}(

	({
		containers: ['#page', '#wpcontent'],
		text1: 'To install tap'.i18n(),
		text2: 'and choose'.i18n(),
		text3: 'Add to Home Screen'.i18n(),
		template: '<div class="add-to-homescreen">\
						<a href="javascript:void(0)" class="add-to-homescreen-close"></a>\
						<div class="add-to-homescreen-text">{{text1}}\
							<div class="add-to-homescreen-button"></div> {{text2}}\
							<br /> {{text3}}\
							<div class="add-to-homescreen-pointer"></div>\
						</div>\
					</div>'
	}),

	({
		triggerId: 'app-install-button',
		containers: ['.handheld-navigation > ul', 'body.wp-admin #wp-admin-bar-site-name-default'],
		text: 'Install App'.i18n(),
		template: '<li class="app-install">\
						<a href="javascript:void(0)" class="ab-item" id="{{triggerId}}">{{text}}</a>\
					</li>'
	})

);

if (window.self == window.top && !window.isPhonegap &&
	(!('standalone' in window.navigator) || !window.navigator.standalone)) {
		document.addEventListener('DOMContentLoaded', function() {
			window.installPrompt.init();
		});
	}
