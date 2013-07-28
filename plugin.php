<?php
 
/*
Plugin Name: Naked Ads
Description: This is an ad management plugin designed to work with Google DFP. This plugin (like the rest of the 'naked' series) utilizes classes found in the 'naked-utils' plugin so make sure that is installed.
Author: Andre Deutmeyer
Version: 0.1
*/

// include files that will be used on activation
require_once( dirname(__FILE__) . '/core/config/database.php' );


/** 
 * 
 * files are added through the 'plugins_loaded' hook so that we
 * can ensure that naked-utils is loaded before trying to use
 * classes and function declared there. 
 */
add_action( 'plugins_loaded', 'naked_ads_init' );

// warn if naked-utils is not installed
add_action( 'admin_notices', 'naked_ads_activation_notice');


/**
 * Initializes all files
 */
function naked_ads_init()
{
	// only load these if naked-utils is active otherwise we get all sorts of errors
	if( class_exists( 'nu_singleton' ) ) {

		// include the models
		require_once( dirname(__FILE__) . '/core/models/ads.php' );

		// include the controllers
		require_once( dirname(__FILE__) . '/core/controllers/settings.php' );
		require_once( dirname(__FILE__) . '/core/controllers/ads.php' );
		require_once( dirname(__FILE__) . '/core/controllers/import.php' );

		// include classes
		require_once( dirname(__FILE__) . '/core/inc/ad_units.class.php' );
		require_once( dirname(__FILE__) . '/core/inc/ad_blocks.class.php' );
		require_once( dirname(__FILE__) . '/core/inc/ad_widgets.class.php' );

		// run any update functions
		naked_ads_db_config::update();

		// instantiate the classes
		if( is_admin() ) {
			new naked_ads_settings_controller;
		}

		new naked_ads_controller;
		new naked_ads_data_import_controller;
	}
}


function naked_ads_activation_notice()
{
	if( !defined ( 'NAKED_UTILS' ) ) {
		if( current_user_can( 'install_plugins' ) ) {
			echo '<div class="error"><p>';
      printf( __('Naked Ads requires Naked Utils to work. Please make sure that you have installed and activated <a href="%s">Naked Utils</a>. They are like peas in a pod.', 'naked_ads' ), '#' );
      echo "</p></div>";
		}
	}
}


register_activation_hook( __FILE__ , array( 'naked_ads_db_config', 'init' ) );
register_deactivation_hook( __FILE__ , array( 'naked_ads_db_config', 'delete' ) );