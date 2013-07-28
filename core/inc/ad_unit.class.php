<?php

class naked_ad_unit
{
	public $id 				= null;
	public $delta 			= '';
	public $name 			= '';
	public $description 	= '';
	public $size 			= '';
	public $width 			= '';
	public $height 			= '';
	public $platform 		= '';
	public $ad_blocks 		= array();

	// display conditions
	public $sections 	= array();
	public $categories 	= array();
	public $tags 		= array();
	public $postIDs 	= array();

	// other properties
	public $edit_link 	= '';


	public function __construct( $ad=null )
	{
		if( null == $ad ) 
			return $this;
		
		// unpack the metadata
		$meta = unserialize( $ad->meta );

		// set all the class properties/variables
		$this->id 		= $ad->id;
		$this->name 	= $ad->name;
		$this->delta 	= $ad->delta;

		$this->description 	= $meta['description'];
		// $this->size		 			= $meta['size'];
		$this->platform 	= $meta['platform'];
		$this->ad_blocks 	= isset( $meta['ad_blocks'] ) ? $meta['ad_blocks'] : array();

		$this->set_conditions( $meta );

		$this->set_edit_link();

		$this->_set_ad_unit_size( $meta );
	}


	public function set_edit_link()
	{
		$page = naked_ads_controller::$menu_slug;
		$this->edit_link = admin_url('admin.php') . '?page=' . $page . '&action=edit&id=' . $this->id;
		return;
	}


	private function _set_ad_unit_size( $meta )
	{
		// size is a string formatted like this: WIDTHxHEIGHT i.e. 728x90
		$size = $this->size = $meta['size'];
		$dimensions = explode( 'x', $size );

		$this->width 	= $dimensions[0];
		$this->height 	= $dimensions[1];
	}


	public function set_conditions( $meta )
	{	
		// set the sections
		if( !empty( $meta['conditions']['sections'] ) )
			$this->sections = $meta['conditions']['sections'];

		// set the categories
		if( !empty( $meta['conditions']['categories'] ) )
			$this->categories = $meta['conditions']['categories'];

		// set the tags
		if( !empty( $meta['conditions']['tags'] ) )
			$this->tags = explode(',', $meta['conditions']['tags'] );		

		// set the postIDs
		if( !empty( $meta['conditions']['postIDs'] ) )
			$this->postIDs = explode(',', $meta['conditions']['postIDs'] );
	}


	public static function get_create_new_link()
	{
		$page = naked_ads_controller::$new_ad_submenu_slug;
		return admin_url('admin.php') . '?page=' . $page;;
	}


	public static function get_import_link()
	{
		$page = naked_ads_data_import_controller::$import_data_submenu_slug;
		return admin_url('admin.php') . '?page=' . $page;;
	}
}