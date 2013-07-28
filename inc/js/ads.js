/**
 * Frontend Naked Ads Javascript
 *
 * Used to output the desired ads to the page
 */

// whether or not this is being loaded in an iframe
var inIframe = top !== self;

// Variables for Google Ad Tags
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];

// document ready + closure
jQuery(function($) {

	// async load Google Ad Tags
	window.google_ads =
	{
		debug: false,
		ad_blocks: null,
		network_code: null,

		init: function() 
		{
			// set on the plugin option page
			google_ads.network_code = naked_ads_settings.dfp_network_code;

			google_ads.async_load();
			google_ads.define_ad_slots();
			google_ads.insert_ad_units();
		},

		async_load: function()
		{
			var gads = document.createElement('script');
			gads.async = true;
			gads.type = 'text/javascript';
			var useSSL = 'https:' == document.location.protocol;
			gads.src = (useSSL ? 'https:' : 'http:') + '//www.googletagservices.com/tag/js/gpt.js';
			var node = document.getElementsByTagName('script')[0];
			node.parentNode.insertBefore(gads, node);
		},

		define_ad_slots: function()
		{
			if( google_ads.debug ) {
				console.log( naked_ads_settings );
				console.log( google_ads );
			}

			googletag.cmd.push(function() {

				/**
				 * prepare the variables
				 *
				 * @note naked_ads_settings is set by the naked_ads_controller class in 
				 * controllers/ads.php
				 */

				var ad_blocks = google_ads.get_ad_blocks();
				var network_code = google_ads.network_code;

				for (index in ad_blocks) {
					var ad_block = ad_blocks[index];
					var ad_units = ad_block.ad_units;

					for (index in ad_units) {
						var ad_unit = ad_units[index];

						// if the ad unit name doesn't exist, this is just a placeholder so don't try to defineSlot. If you do, none of the ads will load.
						if (ad_unit.name !== undefined) {
							var slot = '/' + network_code + '/' + ad_unit.name;
							var slot_id = ad_unit.id;

							if( google_ads.debug ) {
								console.log(ad_unit, slot, slot_id, ad_unit.width, ad_unit.height);
							}

							googletag.defineSlot(slot, [ad_unit.width, ad_unit.height], slot_id).addService(googletag.pubads());
						}
					}
				}

				googletag.pubads().enableSingleRequest();
				googletag.enableServices();

				if( google_ads.debug )
					console.log( 'googletag event log:', googletag.getEventLog() );
			});
		},

		get_ad_blocks: function()
		{
			if( $.isEmptyObject( google_ads.ad_blocks ) == false )
				return google_ads.ad_blocks;
			
			ad_blocks = {};

			// find each .ad-placement on the page
			$('.ad-placement').each(function(index ) {

				ad_blocks[index] = {};

				// get ad_block ad_units
				ad_units = {};

				$(this).find('.ad').each(function(index ) {

					ad_units[index] = {};

					// get ad_block ad_unit name
					var name = '';
					if (google_ads.is_mobile()) {
						name = $(this).find('.mobile-ad-unit-name').text();
					}
					else {
						name = $(this).find('.ad-unit-name').text();
					}

					name = $.trim(name);

					if( google_ads.debug ) {
						console.log(name);
					}

					// if the ad unit name doesn't exist, this is just a placeholder
					if (name) {

						// grab the id of this ad-placement. this will be used later to insert the
						// javascript (that will load the ad) at the correct location
						ad_units[index].id = $(this).attr('id');
						ad_units[index].name = name;

						var width = parseInt($(this).width());
						var height = parseInt($(this).height());

						if( google_ads.debug ) {
							console.log(name, width, height);
						}

						// set ad_unit dimensions
						ad_units[index].width = width;
						ad_units[index].height = height;
					}

				});

				ad_blocks[index].ad_units = ad_units;

			});

			if( google_ads.debug ) {
				console.log(ad_blocks);
			}

			google_ads.ad_blocks = ad_blocks;

			return ad_blocks;
		},

		insert_ad_units: function()
		{
			var ad_blocks = google_ads.get_ad_blocks();

			for (index in ad_blocks) {
				var ad_block = ad_blocks[index];
				var ad_units = ad_block.ad_units;

				for (index in ad_units) {
					var ad_unit = ad_units[index];
					var slot_id = ad_unit.id;

					var script = '<script>googletag.cmd.push(function() { googletag.display("' + slot_id + '"); })</script>';

					$('#' + slot_id).append(script);

					if( google_ads.debug ) {
						console.log( $('#' + slot_id), script );
					}
				}
			}
		},

		show_takeover: function()
		{
			$placement = $('.takeover .ad-placement');
			$iframe = $placement.find('iframe');

			if ($iframe.length) {
				$ad = $iframe.contents().find('body').html();

				if( google_ads.debug ) {
					console.log( $iframe, $iframe.contents(), $ad );
				}

				if ($ad) {
					clearInterval(intervalID);
					var height = $placement.find('.ad').innerHeight();
					$placement.animate({ height: height });
				}
			}

			// failsafe. stop the check after 5 seconds if the iframe still has not been inserted
			window.setTimeout(function() {
				clearInterval(intervalID);
			}, 5000);
		},

		/**
		 * Convenience funciton to determine if currently being viewed by a mobile device
		 *
		 * Currently all this does is check the window size. This should probably replaced with
		 * user agent detection in the future.
		 *
		 * @return (bool) - true if mobile
		 */
		is_mobile: function()
		{
			if ($(window).width() <= 480) {
				return true;
			}

			return false;
		}
	};

	if( !inIframe )
		google_ads.init();

	$(window).load(function() {
		if( !inIframe )
			intervalID = setInterval(google_ads.show_takeover, 1000);
	});

});
