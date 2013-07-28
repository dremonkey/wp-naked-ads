
<?php
/**
 * Template page to import data
 *
 * @todo clean this up
 */
?>

<?php

$debug = true;

ini_set('auto_detect_line_endings', true);

if( isset( $_FILES['data'] ) ) {
	if( $_FILES['data']['error'] == UPLOAD_ERR_OK ) { 
	  
	  $handle = fopen($_FILES['data']['tmp_name'], 'rb'); 
	  
		$data = array();

		$model = naked_ads_model::get_instance();

	 	$row = 0;
		while ( ( $data = fgetcsv( $handle, 0, ",", '"', '\\' ) ) !== false ) {

			if( 0 == $row ) {
				$columns = $data;

				if( $debug ) {
					nu_debug( 'Import: Columns', $columns );
				}
			}
			else {
				$data = array_combine( $columns, $data );
				$ad['name']                 = $data['Name'];
				$ad['type']                 = 'ad_unit';
				$ad['meta']['size']         = $data['Size'];
				$ad['meta']['platform']     = $data['Target Platform'];
        $ad['meta']['description']  = $data['Description'];  
        

				$ads[$row] = $ad;

				$model->create( $ad );


				if( $debug ) {
					nu_debug( 'Import: Ad Row', $ad );
				}
			}

			$row++;
		}
		
		fclose($handle);
	}  
}

function array_combine_special( $a, $b ) {
  
  $acount = count($a);
  $bcount = count($b);

  var_dump( $acount );
  var_dump( $bcount );
  echo '<br/>';

  if ($acount > $bcount) {
    // how many fields are we missing at the end of the second array?
    // Add empty strings to ensure arrays $a and $b have same number of elements
    $more = $acount - $bcount;

    for($i = 0; $i < $more; $i++) {
        $b[] = "";
    }
  } 
  // more fields than headers
  else if ($acount < $bcount) {

    $more = $bcount - $acount;

    

    // fewer elements in the first array, add extra keys        
    // for($i = 0; $i < $more; $i++) {
    //     $key = 'extra_field_0' . $i;
    //     $a[] = $key;
    // }  
  }
  
  // return array_combine( $a, $b );
}

?>

<div class="wrap">

  <div class="pagetitle">
  	<?php screen_icon(); ?>
  	<h2><?php echo __('Ad Data Importer', 'naked_ads' ) ?></h2>

  	<p class="description">
      <?php _e( 'Currently only a DoubleClick CSV file can be imported correctly.', 'naked_ads' ) ?>
    </p>

  </div>

  <div id="msg-box"></div>

  <form enctype="multipart/form-data" action="<?php echo '/wp-admin/admin.php?page=import' ?>" method="POST">
  	<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
    Choose a csv file to import: <input name="data" type="file" />
		<input type="submit" value="Import" />
  </form>

</div>