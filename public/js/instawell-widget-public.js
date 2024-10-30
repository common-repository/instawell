(function ($) {
	'use strict';

	var _e = function (d) {
		try {
			console.log(d);
		} catch (e) {
		}
	};
	var _IW_WIDGET			= typeof _IW_WIDGET == 'undefined' ? {inited: false} : _IW_WIDGET;
	var _IW_WIDGET_CONFIG	= typeof _IW_WIDGET_CONFIG == 'undefined' ? {} : _IW_WIDGET_CONFIG;
	window._IW_WIDGET		= _IW_WIDGET;


	_IW_WIDGET.showWidget		= function showWidget() {
		_IW_WIDGET.$iframOuterWrap.show();
		_IW_WIDGET.$innerCont.addClass('open-iframe');
	}

	_IW_WIDGET.init	= function initWidget(widgetUrl) {
		if (_IW_WIDGET.inited) {
			_IW_WIDGET.$frame[0].src = widgetUrl;
			return;
		}
		_IW_WIDGET.inited = 1;
		var host			= window.location.hostname;
		var iwHost			= 'https://instawell.com';
		var iwStaticHost	= 'https://static-1.instawell.com';
		var embedUrl		= '#';

		if (/(iPad|iPhone|iPod)/g.test(navigator.userAgent)) {
			$('body').addClass('iw-ios-browser')
		}

		var $cont			= $('<div cf-app="instawell">').addClass('instawell-widget-cont').hide();
		var $innerCont		= $('<div>').addClass('instawell-widget-inner-cont');
		var $img			= $('<img src="' + iwStaticHost + '/assets/img/logo-heart-mark.png?v=1">').addClass('instawell-widget-image');
		var $close			= $('<img src="' + iwStaticHost + '/assets/img/close.png?v=1">').addClass('instawell-widget-close');
		var $iframOuterWrap = $('<div>').addClass('instawell-widget-iframe-outerwrap');
		var $iframeWrap		= $('<div>').addClass('instawell-widget-iframe-wrap');
		var $iframe			= $('<iframe del-scrolling="no"></iframe>');
		
		
		_IW_WIDGET.$frame			= $iframe;
		_IW_WIDGET.$iframOuterWrap	= $iframOuterWrap;
		_IW_WIDGET.$innerCont		= $innerCont;
		
		$iframeWrap.html($iframe)
		$iframOuterWrap.html($iframeWrap)

		$innerCont.html($close);
		$innerCont.prepend($img);
		$cont.append($innerCont);
		$('body').append($cont);


		$iframOuterWrap.hide();
		$cont.prepend($iframOuterWrap);
		
		$iframe[0].src = widgetUrl;
		_IW_WIDGET.iframeLoaded = true;

		function displayMessage(evt) {
			if (evt.data == 'app:login') {
				_IW_WIDGET.loadSession();
			}
		}
		if (window.addEventListener) {
			window.addEventListener("message", displayMessage, false);
		} else {
			window.attachEvent("onmessage", displayMessage);
		}

		$innerCont.on('click', function () {
			$innerCont.toggleClass('open-iframe');
			$iframOuterWrap.toggle();
		});


		//do the callbacks
		_IW_WIDGET_CONFIG.loaded && _IW_WIDGET_CONFIG.loaded();
	}
})(jQuery);
