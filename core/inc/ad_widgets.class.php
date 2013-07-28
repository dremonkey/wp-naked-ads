<?php

/**
 * ad_block_widget
 * 
 * Used to place an ad block on the page
 */
class ad_block_widget extends WP_Widget
{
	public static $base_id = 'ad-placement';
 
  public function __construct()
  {
  	$base_id = self::$base_id;
  	$name = __( 'Ad Placement', 'naked_ads' );

  	$widget_ops = array(
  		// classname that will be added to the widget on the frontend
    	'classname' => 'ad-placement',
    	// description displayed in admin
    	'description' => __( "Used to place an ad block", 'naked_ads' ) 
    );

  	parent::__construct( $base_id, $name, $widget_ops );
  }

	/**
	 * Displays the Widget
	 */
	public function widget( $args, $instance )
	{
		extract( $args );

		$ad_block_id = $instance[ 'ad_block_id' ];
		$ad_blocks = naked_ad_blocks::get_instance();
		$ad_block = $ad_blocks->get_ad_block( $ad_block_id );

		if( !isset( $ad_block ) ) {
			echo "Ad block $ad_block_id does not exist.";
			return;
		}

		$ad_units = $ad_block->get_ad_units_to_display();
		$mobile_ad_units = $ad_block->get_mobile_ad_units_to_display();

		// template variables
		$title =  $instance[ 'title' ];
		$hide_title = $instance[ 'hide_title' ];
		$ad_unit_id_base = $ad_block->delta;

		// set the id of the widget wrapper to the ad_block delta
		$before_widget = $this->_get_before_widget( $before_widget, $ad_block->delta, $hide_title );

		// set ad-block classes
		$classes = $this->_get_classes( $instance, $ad_block );

		// get the view
		$tpl_path = dirname(__DIR__) . '/views/widgets/ad_block.php';
		include( $tpl_path );
	}

	/**
     * Saves the widgets settings.
     */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;	

		$keys = array(
			'title' 		=> 'string',
			'ad_block_id'	=> 'int',
			'hide_title'	=> 'int',
			'classes' 		=> 'string'
		);

		foreach( $keys as $key=>$data_type ) {
			$instance[ $key ] = $this->sanitize( $new_instance[ $key ], $data_type ); 
		}
		
		return $instance;
	}


	private function sanitize( $data, $data_type )
	{
		switch( $data_type ) {
			case 'int':
				$data = intval( $data );
				break;
			case 'string':
				$data = trim( $data );
				$data = strip_tags( $data );
				break;
		}

		return $data;
	}


	/**
	 * Creates the edit form for the widget.
	 *
	 */
	public function form( $instance )
	{
		// Defaults
		$defaults = array(
			'title' => '',
			'hide_title' => 0,
			'ad_block_id' => 0,
			'classes' => '',
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		$ad_blocks = naked_ad_blocks::get_instance()->get_ad_blocks();

		// get the view
		$tpl_path = dirname(__DIR__) . '/views/widgets/ad_block_form.php';
		include( $tpl_path );
	}


	/**
	 * Returns true if the current ad unit is just placeholder
	 *
	 * To make a unit a placeholder, just make sure that the ad unit has 'placeholder'
	 * as part of its name
	 */
	public static function is_placeholder( $ad_unit ) 
	{
		$name = strtolower( $ad_unit->name );
		return strrpos( $name, 'placeholder' ) !== false;
	}


	/**
	 * @todo fix how the class is set so that the 'no-title/show-title' class is only 
	 * 	added to the first element rather than all of them.
	 */
	private function _get_before_widget( $before_widget, $delta, $hide_title ) 
	{
		// set the id
		$before_widget = preg_replace( '/(?<=id=")[\w-]+(?=")/', $delta, $before_widget );

		$class = $hide_title ? 'no-title' : 'show-title';

		// set the class
		if( false === strpos( $before_widget, 'class' ) ) {
			$before_widget = str_replace( '>', 'class="'. $class . '">', $before_widget );
		}
		else {
			$before_widget = str_replace( 'class="', 'class="'. $class . ' ', $before_widget );
		}

	  	return $before_widget;
	}


	private function _get_classes( $instance, $ad_block )
	{
		// default class values
		$classes = $ad_block->display_multiple ? 'ad-block multiple-ads ' : 'ad-block ';

		if( isset( $instance['classes'] ) )
			$classes .= $instance['classes'];
 
		return $classes;
	}
}