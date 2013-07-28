<?php
/**
 * Data Import Controller
 */

class naked_ads_data_import_controller
{
	public static $import_data_submenu_slug = 'import';

	public function __construct()
	{
		if( is_admin() ) {
			// Create our admin page
			add_filter( 'naked_ads_submenus', array( &$this, 'admin_reg_submenu' ), 10, 3 );
		}
	}


	public function admin_reg_submenu( $submenus, $parent_slug, $capability ) 
	{
	    $submenus['import'] = array(
	    	'parent_slug' 	=> $parent_slug,
	    	'capability' 	=> $capability,
	    	'page_title'	=> __( 'Import Ad Units', 'naked_ads' ),
	    	'menu_title' 	=> __( 'Import', 'naked_ads' ),
	    	'menu_slug'  	=> self::$import_data_submenu_slug,
	    	'callback' 	 	=> array( &$this, 'admin_import_data_page' ),
	    );

	    return $submenus;
	}


	/**
	 * Outputs the HTML for the Ad Unit Import Data Page
	 */
	public function admin_import_data_page()
	{
		// get the view
		$tpl_path = dirname(__DIR__) . '/views/import.php';
		include( $tpl_path );
	}
}