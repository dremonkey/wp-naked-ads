<?php

/**
 * @file
 *	Configures the database.
 *	- creates new tables
 *	- updates existing tables
 * 	- deletes tables (* not implemented yet)
 */

class naked_ads_db_config
{
	public static $table_name = 'naked_ads';

	private static $db_version = 1;

	public static function init()
	{
		global $wpdb;

		// check if it is a network activation - if so, run the activation function for each blog id
		if ( function_exists('is_multisite') && is_multisite() ) {
		
			if ( is_network_admin() ) {
		    $old_blog = $wpdb->blogid;

				// Get all blog ids
				$blogids = $wpdb->get_col( $wpdb->prepare("SELECT blog_id FROM $wpdb->blogs") );
				
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					self::create();
				}

				switch_to_blog( $old_blog );
				return;
			}
		}

		self::create();
	}


	/**
	 * Create the naked_ads table
	 *
	 * ---- Columns ----
	 * 	id  		: primary identifier
	 *	name 		: human readable name for this data object
	 *	delta 	: machine safe name. created from the 'name' and must be unique by type
	 *	type 		: type descriptor i.e. ad_unit or ad_block
	 *	parents : serialized array indicating which parent(s) this object has. If set to 0, 
	 *						this object has no parents. Only an ad_unit should have parents
	 *	meta 		: additional metadata i.e. description, display conditions, etc.
	 */
	private static function create()
	{
		global $wpdb;

		$table = $wpdb->prefix . self::$table_name;

		$engine = "ENGINE=MyISAM";

		if ( !empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( !empty( $wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

		$comments = array(
			'id' 		=> 'primary identifier',
			'name' 	=> 'human readable name',
			'delta' => 'machine safe name created from the human readable name and must be unique by type',
			'type' 	=> 'type descriptor i.e. ad_unit or ad_block',
			'meta' 	=> 'additional metadata i.e. description, display conditions, etc'
		);

		// create the table
		$sql = $wpdb->prepare( 
			"CREATE TABLE IF NOT EXISTS {$table} (
			  id int(20) NOT NULL AUTO_INCREMENT COMMENT %s,
			  name varchar(255) NOT NULL COMMENT %s,
			  delta varchar(255) NOT NULL COMMENT %s,
			  type varchar(20) NOT NULL COMMENT %s,
			  meta longtext NOT NULL COMMENT %s,
			  PRIMARY KEY (id),
			  UNIQUE (delta, type)
			) {$engine} {$charset_collate}", 
				$comments['id'], 
				$comments['name'], 
				$comments['delta'], 
				$comments['type'],
				$comments['meta']
		);

		update_option( 'naked_ads_db_version', self::$db_version );

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		dbDelta($sql);
	}


	public static function delete()
	{

	}


	/**
	 * Used to update the data structure if it has changed. Currently not used.
	 *
	 * -- Not yet tested --
	 */
	public static function update()
	{
		$old_version = get_site_option('naked_ads_db_version');
		$new_version = self::$db_version;

		// if the version number hasn't change stop now
    if( $new_version == $old_version )
    	return;

    // do version 1 to 1.1 update
    if( 1 == $old_version ) {

 		}
	}
}