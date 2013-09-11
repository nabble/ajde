/**
 * Main JS application
 */

;
(function($) {
	
	var bootstrap = function() {	
		
		// Loading?
		$(window).on("beforeunload", function(e) {
			if ($.active > 0) {
				return 'Operation in progress, navigating away may lead to errors.';
			}
		});     
		
		// Fastclick
		new FastClick(document.body);
        
        // Chosen
		setTimeout(function() {
			$("select.chosen").chosen({
				allow_single_deselect: true
			});
		}, 100);
		
		// Picker
		$("input[type=radio], input[type=checkbox]").not('.nopicker').filter(':visible').picker();
		$("input[type=radio], input[type=checkbox]").not('.nopicker').filter('.picker:hidden').picker();
		
		// Form validation with Twitter Bootstrap
		$('form').attr('novalidate', 'novalidate');
		$('input, select, textarea').not('[type=submit]').jqBootstrapValidation({
			autoAdd: {
				helpBlocks: false
			}
		});
	
		// Toggled menu
		$('.menu-toggle').on('click', function() {
			if ($('.row-fluid > div:eq(0)').hasClass('span2')) {
				$.cookie('collapsed-menu', 1);
				$('.row-fluid > div:eq(0)').removeClass('span2').addClass('span0');
				$('.row-fluid > div:eq(1)').removeClass('span10').addClass('span11');
			} else {
				$.cookie('collapsed-menu', 0);
				$('.row-fluid > div:eq(0)').removeClass('span0 no-animation').addClass('span2');
				$('.row-fluid > div:eq(1)').removeClass('span11 no-animation').addClass('span10');
			}
		});
		if ($.cookie('collapsed-menu') == 1) {
			$('.row-fluid > div').addClass('no-animation');
			$('.menu-toggle').trigger('click');			
			setTimeout(function() {
				$(window).trigger('resize');
			}, 0);
		}
		
		// Hide spinning loading thingy on window blur, as it consumes too much CPU on some systems
		$(window).on('blur', function() {
			$('#loading').hide();
		}).on('focus', function() {
			$('#loading').show();
		});
		
		// Quick node search from top bar
		if ($.fn.autocomplete) {
			var autocomplete_timer;
			var can_search = false;
			var autocomplete_options = {
				serviceUrl: 'admin/node:search.json',
				minChars: 2,
				delimiter: '',
				maxHeight: 400,
				width: 300,
				zIndex: 9999,
				deferRequestBy: 200, //miliseconds
				params: { }, //aditional parameters
				noCache: false, //default is false, set to true to disable caching
				appendTo: $('#node-search-results'),
				onSearchStart: function(query) {
					if (!can_search) {
						return false;
					}
					var that = this;
					$(this).addClass('loading');
					setTimeout(function() {
						$(that).removeClass('loading');
					}, 2000); // remove loading after 2s for cached requests
				},
				onSearchComplete: function(query) {
					$(this).removeClass('loading');
				},
				// callback function:
				onSelect: function(suggestion) {
					window.location.href = 'admin/node:view?edit=' + suggestion.data;
				}
			};
			var autocomplete_instance = $('#node-search').autocomplete(autocomplete_options);
			$('#node-search').on('keyup', function() {
				can_search = false;
				clearTimeout(autocomplete_timer);
				autocomplete_timer = setTimeout(function() {
					can_search = true;
				}, 100);
			});
		}
		
	};

	$(document).ready(bootstrap);

})(jQuery);