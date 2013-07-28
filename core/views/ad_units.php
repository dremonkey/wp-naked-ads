<?php
/**
 * Ad Unit Admin Page Template
 *
 * Variables set in controllers/ads.php
 */
?>

<?php if( !$id && ( !$action || $action == 'edit' ) ) : ?>

  <!-- The Ad Manager List Page -->
  <div class="wrap">

    <div class="pagetitle">
    	<?php screen_icon(); ?>
    	<h2><?php echo __('Ads', 'naked_ads' ) ?></h2>

      <div id="msg-box"></div>
    </div>

    <table class="widefat fixed" cellpadding="0">
      <thead>
        <tr>
        	<th class="column-title" style="width:20%;" scope="col">
        		<?php echo __('Ad Unit', 'naked_ads') ?>
        	</th>
        	<th class="column-title" style="width:30%;" scope="col">
        		<?php echo __('Description', 'naked_ads') ?>
        	</th>
          <th class="column-title" style="width:40%;" scope="col">
            <?php echo __('Conditions', 'naked_ads') ?>
          </th>
          <th class="column-title actions" style="width:10%;" scope="col">
            <?php echo __('Actions', 'naked_ads') ?>
          </th>
        </tr>
      </thead>
      <tbody>
      	<?php if( $ad_units ) : ?>
      		<?php foreach( $ad_units as $ad_unit ) : ?>
      			<tr id="ad-unit-<?php echo $ad_unit->id ?>" class="ad-unit">
      				<td class="name"><a href="<?php echo $ad_unit->edit_link ?>"><?php echo $ad_unit->name ?></a></td>
              <td class="description"><?php echo $ad_unit->description ?></td>
              <td class="conditions">
                <!-- section conditions -->
                <?php foreach( $ad_unit->sections as $section ) :  ?>
                  <span class="condition section"><?php echo $section ?></span>
                <?php endforeach; ?>
                <?php foreach( $ad_unit->categories as $catID ) :  ?>
                  <span class="condition category"><?php echo get_cat_name( $catID ) ?></span>
                <?php endforeach; ?>
                <?php foreach( $ad_unit->tags as $tag ) :  ?>
                  <span class="condition tag"><?php echo $tag ?></span>
                <?php endforeach; ?>
                <?php foreach( $ad_unit->postIDs as $postID ) :  ?>
                  <span class="condition postID"><?php echo $postID ?></span>
                <?php endforeach; ?>
              </td>
              <td class="actions">
                <a id="delete-<?php echo $ad_unit->id ?>" class="delete" href="#"><?php echo __('delete', 'naked_ads') ?></a>
              </td>
      			</tr>
      		<?php endforeach; ?>
      	<?php else : ?>
      		<tr>
      			<td class="colspanchange" colspan="3">
      				<?php echo sprintf( __( 'There are no ads to display yet. You must <a href="%s">create</a> or <a href="%s">import</a> ad(s) before anything will be displayed here', 'naked_ads' ), naked_ad_unit::get_create_new_link(), naked_ad_unit::get_import_link() ) ?>
      			</td>
      		</tr>
      	<?php endif; ?>
      </tbody>
    </table>

  </div><!-- /.wrap -->

  <form action="/" name="edit_ad_units" id="edit-ad-units">
    <input class="button-primary" type="submit" value="Update" />
    <input type="hidden" name="type" value="ad_unit" />
    <input type="hidden" name="delete_ids" value="" />
    <input type="hidden" name="action" value="edit_ad_units" />
  </form>

<?php else : ?>

  <!-- The Edit Ad Page -->
  <?php include('edit_ad_unit.php') ?>

<?php endif; ?>