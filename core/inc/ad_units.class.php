<?php

require_once( dirname(__FILE__) . '/ad_unit.class.php' );

class naked_ad_units extends nu_singleton
{
	private $ads = array();
	private $ads_by_id = array();
	private $ads_by_delta = array();

	protected function __construct()
	{
		$this->model = naked_ads_model::get_instance();
		$this->ads = $this->model->retrieve();
		$this->init();
	}


	private function init()
	{
		foreach( $this->ads as $ad ) {
			// map ads to id
			$this->ads_by_id[ $ad->id ] = new naked_ad_unit( $ad );
			// map ads to delta
			$this->ads_by_delta[ $ad->delta ] = new naked_ad_unit( $ad );
		} 
	}


	/**
	 * Retrieves all ads
	 */
	public function get_ads( $by = 'id' )
	{
		if( $by == 'delta' )
			return $this->ads_by_delta;
		else
			return $this->ads_by_id;

	}


	/**
	 * Retrieves a single ad by id or delta
	 */
	public function get_ad( $mixed ) 
	{
		if( is_numeric( $mixed ) ) {
			return $this->ads_by_id[ $mixed ];
		}
		else if( is_string( $mixed ) ) {
			return $this->ads_by_delta[ $mixed ];
		}
	}
}	