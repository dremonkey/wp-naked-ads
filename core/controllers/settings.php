<?php
/**
 * Controller for the Options Page
 */

class naked_ads_settings_controller extends nu_singleton
{
	public static $options_page_key = 'naked_ads';

	// must be unique
	public static $general_options_key 	= 'naked_ads_general_options';

  private $tabs 					= array();
  private $general_options 			= array(); // stores the saved options

	public function __construct()
	{
		$this->views_dir = dirname(__DIR__) . '/views/';

		add_action( 'admin_menu', array( &$this, 'add_options_menu' ) );
		add_action( 'admin_init', array( &$this, 'reg_general_settings' ) );

		// warn if settings are not set
		add_action( 'admin_notices', array( &$this, 'show_notice') );
	}


	public function show_notice()
	{
		global $blog_id;

		if( !self::has_dfp_options() ) {
			$url = get_admin_url( $blog_id, 'options-general.php?page=' . self::$options_page_key );
			if( current_user_can( naked_ads_controller::$capability ) ) {
				echo '<div class="updated"><p>';
	      		printf( __('Naked Ads needs your DFP network code to work. You can set it on the <a href="%s">settings page</a>.', 'naked_ads' ), $url );
	      		echo "</p></div>";
			}
		}
	}


	/**
	 * Helper function used to determine whether or not the necessary DFP
	 * id or network code has been set.
	 *
	 * @return (bool)
	 * 	returns true if either dfp_id or dfp_network_code is set
	 */
	public static function has_dfp_options()
	{
		$options = (array) get_option( self::$general_options_key );

		$is_set = false;
		foreach( $options as $key=>$option ) {
			if( $key == 'dfp_id' || $key == 'dfp_network_code' ) {
				if( !empty( $option ) ) 
					$is_set = true;
			}
		}

		return $is_set;
	}


	public function add_options_menu() 
	{
		$page_title = 'Ad Manager Settings';
		$menu_title = 'Ad Manager';
		$capability = 'naked_ads_access';
		$menu_slug 	= self::$options_page_key;
		$callback 	= array( &$this, 'get_options_page' );

  		add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback );
	}


	public function get_options_page() 
	{
    	$tpl_path = $this->views_dir . 'settings.php';
			include( $tpl_path );
	}


	public function reg_general_settings()
	{
		$this->tabs[self::$general_options_key] = 'General';

		// Register the settings. All settings will be stored in one options field as an array.
    	register_setting( self::$general_options_key, self::$general_options_key );

    // Add the setting section(s)
    $sections = array(
    	'section_general' => array(
    		'title' 	=> '',
    		'callback' 	=> array( &$this, 'get_section_desc' ),
    		'page'		=> self::$general_options_key,
    	),
    );
    
    foreach( $sections as $id=>$section ) {
    	extract( $section );
    	add_settings_section( $id, $title, $callback, $page );	
    }
    
    // Add the form fields for each section
    $fields = array(
    	'dfp_id' => array(
    		'title' 		=> 'Google DFP Account ID',
    		'callback' 	=> array( &$this, 'build_form_fields' ),
    		'page' 			=> self::$general_options_key,
    		'section' 	=> 'section_general',
    		'args' 			=> array(
    			'id' 	 => 'dfp_id',
    			'type' => 'text-input',
    			'desc' => __('Your Google DFP Account ID should look similar to this: <strong>ca-pub-0000000000000000</strong>', 'naked_ads'),
    		)	
    	),
 		'dfp_network_code' => array(
 			'title' 	=> 'Google DFP Network Code',
    		'callback' 	=> array( &$this, 'build_form_fields' ),
 			'page' 		=> self::$general_options_key,
 			'section' 	=> 'section_general',
 			'args' 	=> array(
 				'id' 	=> 'dfp_network_code',
 				'type' 	=> 'text-input',
 				'desc' 	=> sprintf( __( 'Your Google DFP Network Code. This is a 4 digit (for DFP Premium Users) or longer (for DFP SB Users) number that can be found when you generate your DFP ad tags. For more information on how to locate your Network Code see <a target="_blank" href="%s">this DFP support page</a>', 'naked_ads' ), 'http://support.google.com/dfp_sb/bin/answer.py?hl=en&answer=1651549' ),
 				)
 			),
 			// add a top of the page widget area for the top ad banner
 			'add_top_banner' => array(
 				'title' 	=> __( 'Top Banner Region', 'naked-ads' ),
 				'callback' 	=> array( &$this, 'build_form_fields' ),
 				'page' 		=> self::$general_options_key,
    			'section' 	=> 'section_general',
    			'args' 		=> array(
    				'id' 		=> 'add_top_banner',
 					'type' 		=> 'checkbox',
 					'desc' 		=> __( 'Add a new widget area to the top of your site where the top banner ad can be placed. This is optional. Any existing widget area will also work.' , 'naked_ads' ),
    			)
 			),
 			// add a widget area in the footer for the bottom ad banner
 			'add_bottom_banner' => array(
 				'title' 	=> __( 'Bottom Banner Region', 'naked-ads' ),
 				'callback' 	=> array( &$this, 'build_form_fields' ),
 				'page' 		=> self::$general_options_key,
    			'section' 	=> 'section_general',
    			'args' 	=> array(
    				'id' 	 	=> 'add_bottom_banner',
 					'type' 		=> 'checkbox',
 					'desc' 		=> __( 'Add a new widget area to the bottom of your site (in the footer) where the bottom banner ad can be placed. This is optional. Any existing widget area will also work.' , 'naked_ads' ),
    			)
 			),
    );

    foreach( $fields as $id=>$field ) {
			extract( $field );
    	add_settings_field( $id, $title, $callback, $page, $section, $args );
    }
	}

	public function get_section_desc( $args )
	{
		// var_dump( $args );
		$id = $args['id'];

		$descriptions = array(
			'section_dfp' => '',
		);

		echo $descriptions[$id];
	}


	public function build_form_fields( $args ) 
	{
		extract( $args );

		// prepare the template variables
		$name 	 = self::$general_options_key . "[$id]";
		$options = self::get_options();
		$value 	 = esc_attr( $options[$id] );

		$tpl_path = $this->views_dir . 'setting-fields/'. $type .'.php';
		include( $tpl_path );
	}


	/**
 	 Helper functions
	 */


 	/**
	 * Returns an array of all theme options. If an option has been previously set, the 
	 * stored option value will be return. If not, then the default option value will be 
	 * returned.
	 *
	 * @uses get_option()
	 */
	public static function get_options() 
	{
    $options = (array) get_option( self::$general_options_key );

    // Merge with defaults
    $options = array_merge( 
    	array(
        'dfp_id' 			=> '',
        'dfp_network_code' 	=> '',
        'add_top_banner' 	=> 0,
        'add_bottom_banner' => 0
    	), 
    	$options 
    );

    return $options;
	}


	/**
	 * Retrieves a single option value. If an option has been previously set, the 
	 * stored option value will be return. If not, then the default option value will be 
	 * returned.
	 */
	public static function get_option_value( $option )
	{
		$options = self::get_options();
		// $option = strval( $option );
		$value = $options[ $option ] ? $options[ $option ] : null;
		return $value;
	}
}


/**
 Template Tags
 */


/**
 * Convenience function used to retrieve a single option value.
 *
 * @uses naked_ads_settings_controller::get_option_value
 */
function naked_ads_get_option( $option )
{
	return naked_ads_settings_controller::get_option_value( $option );
}