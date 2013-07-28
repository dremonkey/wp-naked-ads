<?php

/**
 * @file
 *
 *	Ads Controller. This handles all initial setup for the plugin like
 *	creating the menu items and capability.
 *	
 *	Settings are handled through the settings controller
 */

class naked_ads_controller
{
	public static $capability = 'edit_naked_ads';
	public static $menu_slug = 'ad-units';
	public static $new_ad_submenu_slug = 'new-ad-unit';
	public static $ad_blocks_submenu_slug = 'ad-blocks';
	public static $new_ad_block_submenu_slug = 'new-ad-block';

	public function __construct()
	{
		$this->plugin_url = plugin_dir_url( 'naked-ads' ) . 'naked-ads/';
		$this->model = naked_ads_model::get_instance();

		add_filter( 'nu_load_js', array( &$this, 'init_js' ) );
		add_action( 'nu_after_load_js', array( &$this, 'set_js_vars' ) );
		add_filter( 'nu_load_css', array( &$this, 'init_css') );

		// initialize the widgets
		add_action('widgets_init', array( &$this, 'init_widgets') );

		// register the top banner and bottom banner ad areas
		if( naked_ads_get_option( 'add_top_banner' ) )
			add_action( 'init', array( &$this, 'init_top_banner_region') );

		if( naked_ads_get_option( 'add_bottom_banner' ) )
			add_action( 'init', array( &$this, 'init_bottom_banner_region') );

		if( is_admin() ) {
			// Create our admin page
			add_action( 'admin_menu', array( &$this, 'admin_reg_page' ) );

			// add ajax calls
			add_action( 'wp_ajax_edit_ad_units',  array( &$this, 'ajax_edit_ad_units' ) );
			add_action( 'wp_ajax_edit_ad_unit',  array( &$this, 'ajax_edit_ad_unit' ) );

			add_action( 'wp_ajax_edit_ad_blocks',  array( &$this, 'ajax_edit_ad_blocks' ) );
			add_action( 'wp_ajax_edit_ad_block',  array( &$this, 'ajax_edit_ad_block' ) );

			add_action( 'wp_ajax_get_ads',  array( &$this, 'ajax_get_ads' ) );
		}
	}


	/**
	 * Registers and lazy loads the javascript
	 */
	public function init_js( $scripts )
	{
		// base directory where the script files are
		$base_dir = $this->plugin_url . 'inc/js/';

		// basename for registration. should be similar to the plugin name.
		$basename = 'naked_ads';

		// Register Front End Scripts
		nu_lazy_load_js::reg_js( $base_dir . 'ads.js', array('jquery'), '0.1', true, $basename );

		/**
		 * Register Admin Scripts
		 *
		 * @note
		 * 	files prefixed with 'nu' are registered in the naked-utils plugin
		 */
		$deps = array(
			'nu-admin-jquery_naked_autosuggest_handler', 
			'nu-admin-jquery_naked_form_handler'
		);
		
		nu_lazy_load_js::reg_js( $base_dir . 'admin/ad_units.js', $deps, '0.1', false, $basename );
		nu_lazy_load_js::reg_js( $base_dir . 'admin/ad_blocks.js', $deps, '0.1', false, $basename );

		// Lazy load Front End Scripts if dfp option(s) are set
		if( naked_ads_settings_controller::has_dfp_options() ) {
			$scripts['sitewide'][] = 'naked_ads-ads';
		}

		// Lazy load Admin Scripts
		$scripts['admin'][self::$menu_slug][] = 'naked_ads-admin-ad_units';
		$scripts['admin'][self::$new_ad_submenu_slug][] = 'naked_ads-admin-ad_units';
		$scripts['admin'][self::$ad_blocks_submenu_slug][] = 'naked_ads-admin-ad_blocks';
		$scripts['admin'][self::$new_ad_block_submenu_slug][] = 'naked_ads-admin-ad_blocks';

		return $scripts;
	}


	/**
	 * Used to add javascript variables to a page
	 *
	 * Generates something that looks like this:
	 * 
	 *  <script type="text/javascript">
	 *	var naked_ads = {
     *		dfp_id: 123456
	 *	};
	 *  </script>
	 *
	 * @see http://www.garyc40.com/2010/03/5-tips-for-using-ajax-in-wordpress/
	 *
	 * @uses wp_localize_script()
	 */
	public function set_js_vars()
	{
		if( is_admin() ) {
			// create a nonce for the admin ad forms
			$nonce = wp_create_nonce( 'ajax_naked_ads_nonce' ); 

			$data = array(
				'_naked_ads_nonce' => $nonce,
			);

			wp_localize_script( 'naked_ads-admin-ad_units', 'naked_ads', $data );
			wp_localize_script( 'naked_ads-admin-ad_blocks', 'naked_ads', $data );
		}
		else {
			$options = get_option( naked_ads_settings_controller::$general_options_key );
			if( is_array( $options ) ) {
				extract( $options );
			
				$data = array(
					'dfp_id' => $dfp_id,
					'dfp_network_code' => $dfp_network_code,  
				);

				wp_localize_script( 'naked_ads-ads', 'naked_ads_settings', $data );
			}
		}
	}


	/**
	 * Registers and lazy loads the css
	 */
	public function init_css( $styles )
	{
		// base directory where the script files are
		$base = $this->plugin_url . 'inc/css/';

		// basename for registration. should be similar to the plugin name.
		$basename = 'naked_ads';

		nu_lazy_load_css::reg_css( $base . 'admin.css', null, '0.1', 'all', $basename );

		// lazy load the styles
		$styles['admin'][self::$menu_slug][] = 'naked_ads-admin';
		$styles['admin'][self::$new_ad_submenu_slug][] = 'naked_ads-admin';
		$styles['admin'][self::$ad_blocks_submenu_slug][] = 'naked_ads-admin';
		$styles['admin'][self::$new_ad_block_submenu_slug][] = 'naked_ads-admin';

		return $styles; 
	}


	public function init_widgets() 
 	{	
  	register_widget('ad_block_widget');
  }


  public function init_top_banner_region()
	{
		// Top Widget Area - not really a sidebar - for Top Ad Banner
		$args = array(
			'name' 					=> 'Top Banner Region',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' 	=> '</div>',
			'before_title'	=> '<h2 class="widgettitle">',
			'after_title' 	=> '</h2>'
		);

		register_sidebar( $args );

		// place the top banner area at the top of the page
		add_action( 'before', array( &$this, 'insert_top_banner_region' ) );
	}


	public function init_bottom_banner_region()
	{
		// Bottom Widget Area - not really a sidebar - for Top Ad Banner
		$args = array(
			'name' 					=> 'Bottom Banner Region',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' 	=> '</div>',
			'before_title'	=> '<h2 class="widgettitle">',
			'after_title' 	=> '</h2>'
		);

		register_sidebar( $args );

		// place the top banner area at the top of the page
		add_action( 'after', array( &$this, 'insert_bottom_banner_region' ) );
	}


	public function insert_top_banner_region()
	{
		// get the view
		$tpl_path = dirname(__DIR__) . '/views/region_top_banner.php';
		include( $tpl_path );
	}


	public function insert_bottom_banner_region()
	{
		// get the view
		$tpl_path = dirname(__DIR__) . '/views/region_bottom_banner.php';
		include( $tpl_path );
	}


	public function admin_reg_page() 
	{
		global $wp_version;

		// Add the new capability required to access this page.
		$this->admin_set_access();

		$page_title = __( 'Ads Manager', 'naked_ads' );
		$menu_title = __( 'Ads', 'naked_ads' );
		$capability = self::$capability;
		$menu_slug 	= self::$menu_slug;
		$callback 	= array( &$this, 'admin_manage_ads_page' );
		$icon_url 	= $this->plugin_url . 'inc/img/ic_menu_ads.png';
		$position	= null;

    	$page = add_object_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url, $position );

	    // for submenus set $parent_slug to $menu_slug
	    $parent_slug = $menu_slug;

	    $submenus = array(
	    	'ads_list' => array(
	    		'parent_slug' => $parent_slug,
	    		'page_title'	=> __('Ads Manager', 'naked_ads'),
	    		'menu_title'	=> __('All Ads', 'naked_ads'),
	    		'capability' 	=> $capability,
				'menu_slug' 	=> $parent_slug,
				'callback' 		=> array( &$this, 'admin_manage_ads_page' ),
	    	),
	    	'ad_new' => array(
	    		'parent_slug' => $parent_slug,
	    		'page_title'	=> __('New Ad', 'naked_ads'),
	    		'menu_title'	=> __('New Ad', 'naked_ads'),
	    		'capability' 	=> $capability,
	    		'menu_slug' 	=> self::$new_ad_submenu_slug,
	    		'callback' 		=> array( &$this, 'admin_new_ad_page' ),
	    	),
	    	'ad_block' => array(
	    		'parent_slug' => $parent_slug,
	    		'page_title'	=> __('Ad Blocks', 'naked_ads'),
	    		'menu_title'	=> __('Ad Blocks', 'naked_ads'),
	    		'capability' 	=> $capability,
	    		'menu_slug' 	=> self::$ad_blocks_submenu_slug,
	    		'callback' 		=> array( &$this, 'admin_manage_ad_blocks_page' ),
	    	),
	    	'ad_block_new' => array(
	    		'parent_slug' => $parent_slug,
	    		'page_title'	=> __('New Ad Block', 'naked_ads'),
	    		'menu_title'	=> __('New Ad Block', 'naked_ads'),
	    		'capability' 	=> $capability,
	    		'menu_slug' 	=> self::$new_ad_block_submenu_slug,
	    		'callback' 		=> array( &$this, 'admin_new_ad_block_page' ),
	    	)
	    );

		$submenus = apply_filters( 'naked_ads_submenus', $submenus, $parent_slug, $capability );

	    foreach ( $submenus as $submenu ) {
	    	extract( $submenu );
	    	add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
	  	}
	}


	private function admin_set_access()
	{
		$roles = array( 'root', 'administrator' );

		$roles = apply_filters(  'naked_ads_access' , $roles );

		foreach( $roles as $r ){
			$role = get_role( $r );
			if( $role ) $role->add_cap( self::$capability );	
		}
	}


	/**
	 * admin_display_page()
	 *
	 * Outputs the HTML for the Ad Units Management page.
	 */
	public function admin_manage_ads_page()
	{	
		// set up the vars
		$instance = naked_ad_units::get_instance();
		$ad_units = $instance->get_ads();
		$vars = $this->_get_template_vars();
		extract( $vars );
		
		// $options_page_key = naked_ads_settings_controller::$options_page_key;

		// get the view
		$tpl_path = dirname(__DIR__) . '/views/ad_units.php';
		include( $tpl_path );
	}


	/**
	 * Outputs the HTML for the Ad Unit Edit / New Ad Unit Page
	 */
	public function admin_new_ad_page()
	{
		// set up the vars
		$vars = $this->_get_template_vars();
		extract( $vars );

		// get the view
		$tpl_path = dirname(__DIR__) . '/views/edit_ad_unit.php';
		include( $tpl_path );
	}


	/**
	 * Outputs the HTML for the Ad Blocks Management page
	 */
	public function admin_manage_ad_blocks_page()
	{
		// set up the vars
		$instance = naked_ad_blocks::get_instance();
		$ad_blocks = $instance->get_ad_blocks();
		$vars = $this->_get_template_vars( 'ad_block' );
		extract( $vars );

		$tpl_path = dirname(__DIR__) . '/views/ad_blocks.php';
		include( $tpl_path );
	}


	/**
	 * Ouputs the HTML for the Ad Block Edit / New Ad Block page
	 */
	public function admin_new_ad_block_page()
	{
		// set up the vars
		$vars = $this->_get_template_vars( 'ad_block' );
		extract( $vars );

		// get the view
		$tpl_path = dirname(__DIR__) . '/views/edit_ad_block.php';
		include( $tpl_path );
	}


	private function _get_template_vars( $page='ad_unit' )
	{
		$id = isset( $_GET['id'] ) ?  $_GET['id'] : '';
		$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

		$vars['action'] = $action;

		if( $page == 'ad_unit' ) {

			// set defaults
			$vars['id'] = '';
			$vars['ad_unit'] = new naked_ad_unit();

			$vars['section_list']  = $this->_get_sections();
			$vars['category_list'] = $this->_get_categories();

			if( $id && $action='edit' ) {
				$ad_units = naked_ad_units::get_instance();
				$ad_unit = $ad_units->get_ad( $id );

				$vars['id'] = $id;
				$vars['ad_unit'] = $ad_unit;
			}
		
		}
		elseif ( $page == 'ad_block' ) {

			// set defaults
			$vars['id'] = '';
			$vars['ad_block'] = new naked_ad_block(); // empty ad block object

			if( $id && $action='edit' ) {
				$ad_blocks = naked_ad_blocks::get_instance();

				$ad_block = $ad_blocks->get_ad_block( $id );

				$vars['id'] = $id;
				$vars['ad_block'] = $ad_block;
			}
		}
		
		return $vars;
	}


	private function _get_categories()
	{
		$args = array(
			'type'                     => 'post',
			'child_of'                 => 0,
			'parent'                   => '',
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 1,
			'hierarchical'             => 1,
			'exclude'                  => '',
			'include'                  => '',
			'number'                   => '',
			'taxonomy'                 => 'category',
			'pad_counts'               => false 
		);

		$categories = get_categories( $args );

		foreach( $categories as $cat ) {
			$r[$cat->cat_ID] = $cat->name;
		}

		return $r;
	}

	/**
	 * @return list of site 'sections' that can be used to control what ads are shown
	 */
	private function _get_sections()
	{
		$args=array(
  		'public'   => true,
  		'_builtin' => false
		); 
		
		$custom_post_types = get_post_types( $args, 'names' );

		$sections = array(
			'home', // homepage / frontpage
			'article', // any posts page (archive or single)    
			'image' // any attachment image page (archive or single)
		);

		$sections = array_merge( $sections, $custom_post_types );

		return $sections;
	}


	/**
	 AJAX
	 */
	public function ajax_edit_ad_units()
	{
		if( empty($_POST) ) 
			die('No data sent');

		$nonce = $_POST['nonce'];
		if( !wp_verify_nonce( $nonce, 'ajax_naked_ads_nonce' ) )
			die( 'Failed security check. Cannot modify ad units.' );

		$this->_edit_ad_object();
	}  

	public function ajax_edit_ad_unit()
	{
		$page = self::$menu_slug;

		if( empty($_POST) ) 
			die('No data sent');

		$nonce = $_POST['nonce'];
		if( !wp_verify_nonce( $nonce, 'ajax_naked_ads_nonce' ) )
			die( 'Failed security check. Cannot modify the ad unit.' );

		$this->_edit_ad_object( $page );
	}


	public function ajax_edit_ad_blocks()
	{
		if( empty($_POST) ) 
			die('No data sent');

		$nonce = $_POST['nonce'];
		if( !wp_verify_nonce( $nonce, 'ajax_naked_ads_nonce' ) )
			die( 'Failed security check. Cannot modify ad blocks.' );

		$this->_edit_ad_object();
	}  


	public function ajax_edit_ad_block()
	{
		$page = self::$ad_blocks_submenu_slug;

		if( empty($_POST) ) 
			die('No data sent');

		$nonce = $_POST['nonce'];
		if( !wp_verify_nonce( $nonce, 'ajax_naked_ads_nonce' ) )
			die( 'Failed security check. Cannot modify the ad block.' );

		$this->_edit_ad_object( $page );
	}


	private function _edit_ad_object( $page=null )
	{		
		if( !current_user_can( self::$capability ) )
			die( 'You cannot do this' );
			
		$model = naked_ads_model::get_instance();
		$data = $_POST;
		$action = $data['action'];

		// data cleanup
		unset( $data['action'] );
		$r['data'] = $data;

		// if $action is for a list page
		if( $action == 'edit_ad_blocks' || $action == 'edit_ad_units' ) {
			
			// currently only deletes can be done from these pages
			if( !empty( $data['delete_ids'] ) ) {
				$ids = explode( ',' , $data['delete_ids'] );

				$type = $action == 'edit_ad_blocks' ? 'ad_block' : 'ad_unit'; 

				$response = $model->delete( $ids, $type );

				if( !is_numeric( $response ) ) {
					$r['status'] = 'error';
					$r['msg'] = $response ? $response : '';
				}
				else {
					if( $response === 0 ) {
						$r['status'] = 'warning';
						$r['msg'] = __( 'Nothing changed', 'naked_ads' );
					}
					else {
						$r['status'] = 'success';
						$r['msg'] = __( 'Successfully updated', 'naked_ads' );
					}
				}
			}
			else {
				$r['status'] = 'warning';
				$r['msg'] = __( 'Nothing to submit. You didn\'t change anything', 'naked_ads' );
			}
		}
		// if $action is for a single item and id already exists, we are updating
		elseif( isset( $data['id'] ) 
				&& ( $action == 'edit_ad_block' || $action == 'edit_ad_unit' ) ) {
			//updating
			$response = $model->update( $data );

			if( !is_numeric( $response ) ) {
				$r['status'] = 'error';
				$r['msg'] = $response ? $response : '';
			}
			else {
				if( $response === 0 ) {
					$r['status'] = 'warning';
					$r['msg'] = __( 'Nothing changed', 'naked_ads' );
				}
				else {
					$r['status'] = 'success';
					$r['msg'] = __( 'Successfully updated', 'naked_ads' );
				}
			}

		}
		// if $action is for a single item and no id then create a new ad object
		elseif( $action == 'edit_ad_block' || $action == 'edit_ad_unit' ) {
			// create new
			$response = $model->create( $data );

			if( !is_numeric( $response ) ) {
				$r['status'] = 'error';
				$r['msg'] = $response ? $response : '';
			}
			else {
				$r['status'] = 'success';
				$r['msg'] = __( 'Successfully created', 'naked_ads' );
				$r['id'] = $response;	
				
				$redirect_url = admin_url('admin.php') . '?page=' . $page . '&action=edit&id=' . $response;

				$r['redirect'] = $redirect_url;
			}
		}

		echo json_encode( $r );

		// make sure to die/exit at the end or this will not work
		die;
	}


	public function ajax_get_ads()
	{
		$s = stripslashes( $_GET['q'] );

		if ( false !== strpos( $s, ',' ) ) {
			$s = explode( ',', $s );
			$s = $s[count( $s ) - 1];
		}
		$s = trim( $s );
		if ( strlen( $s ) < 2 )
			die; // require 2 chars for matching

		$instance = naked_ad_units::get_instance();
		$ads = $instance->get_ads();

		$results = array();
		foreach ( $ads as $ad ) {
			if( strpos( strtolower( $ad->name ), $s ) !== false )
				$results[] = $ad->name;
		}

		echo implode( "\n", $results );
		die;
	}
}