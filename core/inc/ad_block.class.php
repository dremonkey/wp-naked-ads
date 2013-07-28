<?php

class naked_ad_block
{
	private static $debug = false;

	public $id = null;
	public $delta = '';
	public $name = '';
	public $description = '';
	public $size = '';
	public $ad_units = array();
	public $ad_unit_edit_links = array();
	public $display_multiple = 0;

	// other properties
	public $edit_link = '';

	public function __construct( $ad_block=null )
	{
		if( null == $ad_block )
			return $this;

		// unpack the metadata
		$meta = unserialize( $ad_block->meta );

		// set all the class properties/variables
		$this->id 		= $ad_block->id;
		$this->name 	= $ad_block->name;
		$this->delta 	= $ad_block->delta;

		$this->description 		= isset( $meta['description'] ) ? $meta['description'] : '';
		$this->display_multiple = isset( $meta['display_multiple'] ) ? $meta['display_multiple'] : 0;
		
		$this->_set_ad_units( $meta );
		$this->_set_edit_link();
	}


	public function _set_ad_units( $meta )
	{
		if( !empty( $meta['ad_units'] ) ) {
			$ad_units = explode(',', $meta['ad_units'] );

			foreach( $ad_units as $name ) {
				$instance = naked_ad_units::get_instance();
				$delta = nu_utils::clean_string( $name );
				$this->ad_units[] = $ad_unit = $instance->get_ad( $delta );

				// ad unit name : ad unit edit link map
				$this->ad_unit_edit_links[ $name ] = $ad_unit->edit_link;
			}		
		}
	}


	private function _set_edit_link()
	{
		$page = naked_ads_controller::$ad_blocks_submenu_slug;
		$this->edit_link = admin_url('admin.php') . '?page=' . $page . '&action=edit&id=' . $this->id;
		return;
	}


	/**
	 * Retrieves the correct ad unit to place in the ad block depending on
	 * the which conditions are met
	 *
	 * This should be called from the page that the ad is to be displayed on
	 *
	 * @uses is_image_section()
	 * @uses is_article_section()
	 * @uses is_section()
	 */
	public function get_ad_units_to_display( $the_platform='web' )
	{
		global $post;

		$ads = array(); // array of matching ad_units to display

		foreach( $this->ad_units as $ad_unit )
		{	
			$platform		= $ad_unit->platform;
			$sections 		= $ad_unit->sections;
			$categories 	= $ad_unit->categories;
			$tags 			= $ad_unit->tags;
			$postIDs 		= $ad_unit->postIDs;

			// if no conditions are set then this is the default ad unit
			if( empty( $postIDs ) 
					&& empty( $tags ) 
					&& empty( $categories ) 
					&& empty( $sections ) )
			{
				$ads[ $platform ]['default'][] = $ad_unit;
			}

			// check postids
			if( is_array( $postIDs ) && !empty( $postIDs ) ) {
				if( is_single() ) {
					foreach( $postIDs as $postID ) {
						if( $postID == $post->ID )
							$ads[ $platform ]['postIDs'][] = $ad_unit;
					}
				}
			}

			// check tags
			// @todo verify that this works
			if( is_array( $tags ) && !empty( $tags ) ) {
				if( is_single() ) {
					$posttags = get_the_tags( $post->ID );
					foreach( $tags as $tag ) {
						foreach( $posttags as $ptag ) {
							if( $tag == $ptag->name )
								$ads[ $platform ]['tags'][] = $ad_unit;
						}
					}
				}
			}

			// check categories
			// @todo verify that this works
			if( is_array( $categories ) && !empty( $categories ) ) {
				if( !is_home() && !is_front_page() ) {
					$postcats = get_the_category();
					foreach( $categories as $cat ) {
						foreach( $postcats as $pcat ) {
							if( $cat == $pcat->cat_name ) {
								$ads[ $platform ]['cats'][] = $ad_unit;
							}
						}
					}
				}
			}

			// check sections
			if( is_array($sections) && !empty($sections) ) {
				foreach( $sections as $section ) {
					// get the ad unit for the home section
					if( 'home' == $section 
							&& ( is_home() || is_front_page() ) ) {
							$ads[ $platform ]['section']['home'][] = $ad_unit;
					} 
					
					// check if images or single image page
					if( 'image' == $section ) {
						if( $this->is_image_section() ) {
							$ads[ $platform ]['section']['image'][] = $ad_unit;
						}
					}

					// check news section (basically any archive or single page ). excludes the home, images, and 
					// custom post type sections
					if( 'article' == $section ) {
							if( $this->is_article_section() ) {
								$ads[ $platform ]['section']['article'][] = $ad_unit;
							}
					}

					// get the ad unit for each custom post type section
					if( 'home' != $section 
							&& 'image' != $section 
							&& 'article' != $section
						  && $this->is_section( $section ) )  {
						
						$ads[ $platform ]['section'][ $section ][] = $ad_unit;
					}
				}
			}
		} // end foreach

		// allow themes or other plugins to change which ad unit is to be displayed
		$ads = apply_filters( 'naked_ads_ad_units_to_display', $ads, $this->ad_units );

		if( self::$debug )
			nu_debug( $this->name . ' Ads Array', $ads );

		if( !empty( $ads ) ) {
			// if the ad block is set to display multiple items, return everything in the array
			if( $this->display_multiple ) {
				if( isset( $ads[ $the_platform ] ) ) {
					$ads = $this->_get_all_matching_ads( $ads[ $the_platform ] );
					return $ads;
				}
			}
			// otherwise return only the most specific ad
			else {
				if( isset( $ads[ $the_platform ] ) ) {
					$ad = $this->_get_most_specific_ad( $ads[ $the_platform ] );
					return $ad;
				}
			}
		}

		return null;
	}


	/**
	 * Convenience function to retrieve the mobile ads that will be displayed within a specific ad block
	 */
	public function get_mobile_ad_units_to_display()
	{
		return $this->get_ad_units_to_display('mobile');
	}


	private function _get_all_matching_ads( $ads )
	{
		if( !$ads ) return;
		
		// merge all the subarrays and return
		$all = array();
		foreach( $ads as $key=>$ad_set ) {
			if( 'section' == $key ) {
				foreach( $ad_set as $section_ads ) {
					// section contains subarrays so we need merge all those subarrays
					$all = array_merge( $all, $section_ads );
				}
			}
			else {
				$all = array_merge( $all, $ad_set );
			}
		}

		return $all;
	}


	/**
	 * @uses _get_most_specific_section_ad()
	 */
	private function _get_most_specific_ad( $ads=array() )
	{
		$ad = array(); // ad to return

		// the order of importance ( most specific ->least specific )
		$order = array( 'postIDs', 'tags', 'cats', 'section', 'default' );
		foreach( $order as $key ) {
			if( isset( $ads[ $key ] ) ) {
				// return the first ad of the first matched ad array 
				if( 'section' == $key ) {
					$ad[] = $this->_get_most_specific_section_ad( $ads[ 'section' ] );
				}
				else {
					$ad[] = array_shift( $ads[ $key ] );
				}
				break;
			}
		}

		return $ad;
	}


	private function _get_most_specific_section_ad( $section_ads )
	{
		$sorted = array();
		// section contains subarrays so we need to grab the most relevant
		// ad from the subarray. the article section is the most general and
		// therefore the least relevant.
		foreach( $section_ads as $section=>$ads ) {
			foreach( $ads as $ad ) {
				// if the article section append to the end of the array
				if( 'article' == $section ) {	
					$sorted[] = $ad;
				}
				// if any other section prepend to the front of the array
				else {
					array_unshift( $sorted, $ad );
				}
			}
		}

		if( self::$debug ) {
			echo 'sorted section ads: ';
			nu_debug::var_dump( $sorted );
		}

		// return the first ad
		return array_shift( $sorted );
	}


	public function is_image_section()
	{
		global $post;

		$bool = false;
		if( $post && wp_attachment_is_image( $post->ID ) ) {
			$bool = true;
		}

		$bool = apply_filters( 'naked_ads_is_image_section', $bool );

		return $bool;
	}


	public function is_article_section()
	{
		global $post;

		$bool = false;
		if( $post && !is_front_page() && !is_home()
				&& !$this->is_image_section()
				&&( is_archive() || is_single() ) ) {
			$bool = true;
		}

		$bool = apply_filters( 'naked_ads_is_article_section', $bool );

		return $bool;
	}


	public function is_section( $section )
	{
		global $post;

		$bool = false;
		
		if( $post && is_post_type_archive( $section ) 
				|| ( get_post_type() == $section && is_single() ) ) {
			$bool = true;
		}

		$bool = apply_filters( "naked_ads_is_{$section}_section", $bool, $section );

		if( self::$debug ) {
			echo 'is section: ' . $section;
			nu_debug::var_dump( $bool );
		}

		return $bool;
	}


	public static function get_create_new_link()
	{
		$page = naked_ads_controller::$new_ad_block_submenu_slug;
		return admin_url('admin.php') . '?page=' . $page;;
	}
}


/**
Template Tags
*/

/**
 * Retrieves the full html for a single ad block (with an ad inside)
 *
 * @param $the_block (mixed - str or int)
 *	The id or delta of the ad block that you want to retrieve and display
 */
function naked_get_ad_block( $the_block ) 
{
	$ad_blocks = naked_ad_blocks::get_instance();
	$ad_block = $ad_blocks->get_ad_block( $the_block );

	if( !isset( $ad_block ) ) {
		$text = "Ad block $the_block does not exist.";
		echo $text;
		return;
	}

	$ad_units = $ad_block->get_ad_units_to_display();
	$mobile_ad_units = $ad_block->get_mobile_ad_units_to_display();

	// template variables
	$ad_unit_id_base = $ad_block->delta;

	$classes = 'ad-block';

	// get the view
	$tpl_path = dirname(__DIR__) . '/views/widgets/ad_block.php';
	include( $tpl_path );
}


/**
 * If the ad block exists and contains ads, return true
 */
function naked_ad_block_exists( $the_block )
{
	$ad_blocks = naked_ad_blocks::get_instance();
	$ad_block = $ad_blocks->get_ad_block( $the_block );

	if( isset( $ad_block ) && !empty( $ad_block->ad_units ) ) return true;

	return false;
}