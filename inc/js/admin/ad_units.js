/**
 * Admin Edit Ad Page Javascript
 */

// closure
(function($) {

	$(function() {

		$('input#tags').naked_autosuggest_handler({
			'selected_list' : '#tags-list',
			'selected_field' : '#meta-conditions-tags',
			'autosuggest_url' : '/wp-admin/admin-ajax.php?action=ajax-tag-search&tax=post_tag'
		});

		$('form#edit-ad-unit').naked_form_handler({
			'row' : '',
			'row_id_base' : '',
			'nonce' : naked_ads._naked_ads_nonce
		});

		$('form#edit-ad-units').naked_form_handler({
			'row' : '.ad-unit',
			'row_id_base' : 'ad-unit-',
			'nonce' : naked_ads._naked_ads_nonce
		});

	});

})(jQuery);
