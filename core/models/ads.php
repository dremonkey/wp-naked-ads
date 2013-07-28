<?php

/**
 * @file
 *
 *	Ads Model. Contains all functions that interface with the db
 */
	
class naked_ads_model extends nu_singleton
{
	private $db_table = '';

	protected function __construct()
	{
		$this->set_var('db_table');
	}


	public function set_var( $type, $value=null )
	{	
		global $wpdb;

		switch( $type ) {
			case 'db_table' :
				$this->db_table = $wpdb->prefix . naked_ads_db_config::$table_name; 
				break;
		}
	}


	public function retrieve( $type='ad_unit', $cached=true )
	{
		global $wpdb;

		$key = 'naked_ads_' . $type;
		$data = array(); // return object

		if( $cached )
			$data = get_transient( $key );
		
		if ( empty( $data ) ) {
			$expiration = 60 * 60 * 24 * 30; // 1 month
			$sql = $wpdb->prepare( "SELECT * FROM {$this->db_table} WHERE type=%s", $type );
			$data = $wpdb->get_results( $sql );
			set_transient( $key, $data, $expiration );
		}

		return $data;
	}


	public function create( $data )
	{
		global $wpdb;

		extract( $data );

		if( empty( $name ) ) 
			return new WP_Error( 'Failed to create Ad', __( "You must set a name", 'naked_ads' ) );

		if( empty( $type ) ) 
			return new WP_Error( 'Failed to create Ad', __( "You must set a type", 'naked_ads' ) );

		$delta = $this->get_delta( $name, $type );

		if( !$delta )
			return new WP_Error( 'Failed to create Ad', __( "Use a different name. The name $name has already been used.", 'naked_ads' ) );

		/** 
		 * prepare the data 
		 */
		if( is_array( $meta ) )
			$meta = serialize( $meta );

		// add the data. $wpdb->insert returns false if data could not be inserted.
		$success = $wpdb->insert( $this->db_table, 
			array( 
				'name' 		=> $name,
				'delta' 	=> $delta,
				'type' 		=> $type,
				'meta' 		=> $meta
			),
			array( '%s','%s','%s', '%s' ) 
		);

		if( $success ) {
			// reset the cache
			$key = 'naked_ads_' . $type;
			delete_transient( $key );
			return $wpdb->insert_id;
		}
		else {
			var_dump( $wpdb->last_query );
			$wpdb->print_error();
		}

		return false;
	}


	public function update( $data )
	{
		global $wpdb;

		extract( $data );

		// var_dump( $data );

		if( empty( $name ) ) 
			return __( "You must set a name", 'naked_ads' );

		$delta = $this->get_delta( $name, $type, $id );

		if( !$delta )
			return __( "Use a different name. The name $name has already been used.", 'naked_ads' );

		/** 
		 * prepare the data 
		 */
		if( is_array( $meta ) )
			$meta = serialize( $meta );

		// add the data
		$success = $wpdb->update( $this->db_table, 
			array( 
				'name' 		=> $name,
				'delta' 	=> $delta,
				'type' 		=> $type,
				'meta' 		=> $meta
			), 
			array( 'id' => $id ), // where
			array( '%s','%s','%s', '%s' ), // format 
			array( '%d') // where format
		);

		if( $success ) {
			// reset the cache
			$key = 'naked_ads_' . $type;

			// check if the transient exists
			if( get_transient( $key ) ) {
				// if it exists then delete it
				if( delete_transient( $key ) )
					return $success;
				else
					return new WP_Error( 'Failed to delete transient cache', sprintf( __( "Failed to delete transient cache: %s", 'naked_ads' ), $key ) );
			}

			// if the transient doesn't exist just return
			return $success;
		} 
		elseif ( $success === 0 ) {
			return $success;
		}
		else {
			var_dump( $wpdb->last_query );
			$wpdb->print_error();
		}

		return false;		
	}


	/**
	 * @param $mixed
	 *	Accepted data types are:
	 *		array 	- a combination of names, deltas, or ids
	 *		string 	- a name or delta
	 *		int 		- an id
	 *
	 * @param $type (string)
	 *	This value is needed if an item is being deleted by delta because deltas
	 *	are only unique among items of the same type
	 */
	public function delete( $mixed, $type=null )
	{
		global $wpdb;

		if( is_array( $mixed ) ) {

			$ids = array(); // used to store $ids
			$deltas = array();

			foreach( $mixed as $ad ) {
				
				if( is_numeric( $ad ) ) $ids[] = $ad;
				
				if( $type ) {
					if( is_string( $ad ) ) {
						// create the delta 
						$delta = nu_utils::clean_string( $ad );

						// check to see if it exists
						if( $this->delta_exists( $delta, $type ) ) $deltas[] = "'" . $delta . "'";
					}
				}
			}

			$ids = implode(',' , $ids);
			$deltas = implode(',' , $deltas);

			if( !empty( $ids ) && !empty( $deltas ) ) {
				// construct where statement if both $ids and $deltas are not empty
				$sql = $wpdb->prepare( "DELETE FROM {$this->db_table} WHERE type = %s AND ( id IN ({$ids}) || delta in ({$deltas}) )", $type );
			} 
			else {
				// construct where statement if either $ids and $deltas are not empty
				if( !empty( $ids ) ) 
					$sql = $wpdb->prepare( "DELETE FROM {$this->db_table} WHERE id IN ({$ids})" );
				else if ( !empty( $deltas ) )
					$sql = $wpdb->prepare( "DELETE FROM {$this->db_table} WHERE type = %s AND delta IN ({$deltas})", $type );
			}
		}
		else if ( is_numeric( $mixed ) ) {
			$sql = $wpdb->prepare( "DELETE FROM {$this->db_table} WHERE id = %d", $mixed );
		}
		else if ( is_string( $mixed ) ) {
			// deleting an ad object by name/delta
			$delta = nu_utils::clean_string( $mixed );
			if( $type ) {
				if( $this->delta_exists( $delta, $type ) )
					$sql = $wpdb->prepare( "DELETE FROM {$this->db_table} WHERE type = %s AND delta = %s", $delta );
			} 
			else {
				return __( 'The ad object type must be specified when deleting by delta', 'naked_ads' );
			}
		}

		// execute the query
		$success = $wpdb->query( $sql );

		// var_dump( $success );
		if( $success !== false ) {
			
			if( $type ) {
				// if $type reset the cache for that type
				$key = 'naked_ads_' . $type;
				
				// check if the transient exists
				if( get_transient( $key ) && !delete_transient( $key ) )
					return new WP_Error( 'Failed to delete transient cache', sprintf( __( "Failed to delete transient cache: %s", 'naked_ads' ), $key ) );
			}
			else {
				// delete both the caches
				if( get_transient( $key ) && !delete_transient( 'naked_ads_ad_block' ) )
					return new WP_Error( 'Failed to delete transient cache', __( "Failed to delete transient cache: naked_ads_ad_block", 'naked_ads' ) );
				
				if( get_transient( $key ) && !delete_transient( 'naked_ads_ad_unit' ) )
					return new WP_Error( 'Failed to delete transient cache', __( "Failed to delete transient cache: naked_ads_ad_unit", 'naked_ads' ) );
			}

			return $success;
		}

		return false;
	}


	/**
	 * Takes the human readable name and converts it into a machine
	 * safe name (delta)
	 *
	 * @param $id (int)
	 * 	if $id is set then we are updating
	 */
	private function get_delta( $name, $type, $id=null )
	{
		$delta = nu_utils::clean_string( $name );

		// check to see if the delta already exists
		if( !$this->delta_exists( $delta, $type, $id ) )
			return $delta;
		
		return false;	
	}


	/**
 	 * Checks to see if the delta is already in use.
 	 */
	private function delta_exists( $delta, $type, $id=null ) 
	{
		global $wpdb;

		if( $id )
			$query = $wpdb->prepare( "SELECT 1 FROM {$this->db_table} WHERE delta=%s AND type=%s AND id!=%d", $delta, $type, $id );
		else
			$query = $wpdb->prepare( "SELECT 1 FROM {$this->db_table} WHERE delta=%s AND type=%s", $delta, $type );	
		
		$exists = $wpdb->query( $query );

		// var_dump( $query );

	  return $exists;
	}
}