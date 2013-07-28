/**
 * Admin Edit Ad Blocks Page Javascript
 */

// closure
(function($) {

	$(function() {

		$('input#ad-units').naked_autosuggest_handler({
			'selected_list' : '#ad-units-list',
			'selected_field' : '#meta-ad-units',
			'autosuggest_url' : '/wp-admin/admin-ajax.php?action=get_ads'
		});

		$('form#edit-ad-block').naked_form_handler({
			'row' : '',
			'row_id_base' : '',
			'nonce' : naked_ads._naked_ads_nonce
		});

		$('form#edit-ad-blocks').naked_form_handler({
			'row' : '.ad-block',
			'row_id_base' : 'ad-block-',
			'nonce' : naked_ads._naked_ads_nonce
		});

	});

})(jQuery);
