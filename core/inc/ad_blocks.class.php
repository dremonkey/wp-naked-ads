<?php

require_once( dirname(__FILE__) . '/ad_block.class.php' );

class naked_ad_blocks extends nu_singleton
{
	private $ad_blocks = array();
	private $ad_blocks_by_id = array();
	private $ad_blocks_by_delta = array();

	protected function __construct()
	{
		$this->model = naked_ads_model::get_instance();
		$this->ad_blocks = $this->model->retrieve( 'ad_block' );
		
		foreach( $this->ad_blocks as $ad_block ) {
			// map ads to id
			$this->ad_blocks_by_id[ $ad_block->id ] = new naked_ad_block( $ad_block );
			// map ads to delta
			$this->ad_blocks_by_delta[ $ad_block->delta ] = new naked_ad_block( $ad_block );
		} 
	}


	/**
	 * Retrieves all ads
	 */
	public function get_ad_blocks( $by = 'id' )
	{
		if( $by == 'delta' )
			return $this->ad_blocks_by_delta;
		else
			return $this->ad_blocks_by_id;
	}


	/**
	 * Retrieves a single ad by id or delta
	 */
	public function get_ad_block( $mixed ) 
	{
		if( is_numeric( $mixed ) ) {
			return $this->ad_blocks_by_id[ $mixed ];
		}
		else if( is_string( $mixed ) ) {
			return $this->ad_blocks_by_delta[ $mixed ];
		}
	}
}