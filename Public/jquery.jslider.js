/*
 * JuheSlider
 * Author:JONEDOO
 * Http://www.juhe.cn
 */
(function($) {
	$.fn.jSlider = function(options) {
		var defaults = {
			pause: 3000,
			fadeOutTime: 500,
			fadeInTime: 500,
			naviSlider: 'naviSlider',
			prev: '.prev',
			next: '.next'
		}

		var options = $.extend(defaults, options);

		var obj = $(this);

		obj.find('li:first').show(); //显示第一个item

		var itemCount = obj.children('li').length;

		var naviObj = $("#" + options.naviSlider);

		var i = 0; //初始化

		naviObj.children('li').click(function() {
			//var index = $(this).text();
			clearInterval(interval);
			var index = $(this).attr("sIndex");
			i = parseInt(index) - 1;
			showSlider(i);
			interval = setInterval(function() {
				showSlider(i);
			}, options.pause);
		})

		$(options.prev).on('click', function(event) {
			event.preventDefault();
			clearInterval(interval);
			i = (i - 1 < 0) ? itemCount - 1 : i - 1;
			showSlider(i);
			interval = setInterval(function() {
				showSlider(i);
			}, options.pause);
		});

		$(options.next).on('click', function(event) {
			event.preventDefault();
			clearInterval(interval);
			i = i >= (itemCount - 1) ? 0 : i + 1;
			showSlider(i);
			interval = setInterval(function() {
				showSlider(i);
			}, options.pause);
		});

		var showSlider = function(next) {
			obj.children('li').filter(":visible").fadeOut(options.fadeOutTime).parent().children().eq(next).fadeIn(options.fadeInTime);
			naviObj.children('li').removeClass('on').eq(next).addClass('on');
		}

		var interval = setInterval(function() {
			i = i >= (itemCount - 1) ? 0 : i + 1;
			showSlider(i);
		}, options.pause);
	}
})(jQuery);