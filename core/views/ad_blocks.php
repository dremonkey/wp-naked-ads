<?php
/**
 * @file
 *	Ads Admin Page Template
 */
?>

<?php if( !$id && ( !$action || $action == 'edit' ) ) : ?>

  <!-- The Ad Blocks Manager List Page -->
  <div class="wrap">

    <div class="pagetitle">
    	<?php screen_icon(); ?>
    	<h2><?php echo __('Ad Blocks', 'naked_ads' ) ?></h2>

      <p class="description">
        <?php _e( 'Ad Blocks serve as wrappers for individual ad units. In the template, ad blocks are placed on the page and the most appropriate ad unit (based on page condition like category, tags, etc) that has been assigned to the ad block is used to fill the ad block. An ad block will only serve one ad at a time.', 'naked_ads' ) ?>
      </p>
    </div>

    <div id="msg-box"></div>

    <table class="widefat fixed" cellpadding="0">
      <thead>
        <tr>
        	<th class="column-title" style="width:20%;" scope="col">
        		<?php echo __('Ad Block', 'naked_ads') ?>
        	</th>
        	<th class="column-title" style="width:30%;" scope="col">
        		<?php echo __('Description', 'naked_ads') ?>
        	</th>
          <th class="column-title" style="width:40%;" scope="col">
            <?php echo __('Ad Units', 'naked_ads') ?>
          </th>
          <th class="column-title actions" style="width:10%;" scope="col">
            <?php echo __('Actions', 'naked_ads') ?>
          </th>
        </tr>
      </thead>
      <tbody>
      	<?php if( $ad_blocks ) : ?>
      		<?php foreach( $ad_blocks as $id=>$ad_block ) : ?>
      			<tr id="ad-block-<?php echo $ad_block->id ?>" class="ad-block">
      				<td class="name"><a href="<?php echo $ad_block->edit_link ?>"><?php echo $ad_block->name ?></a></td>
              <td class="description"><?php echo $ad_block->description ?></td>
              <td class="ad-units">
                <?php foreach( $ad_block->ad_unit_edit_links as $ad_unit=>$link ) : ?>
                  <a class="ad-unit" href="<?php echo $link ?>"><?php echo $ad_unit ?></a>
                <?php endforeach; ?>
              </td>
              <td class="actions">
                <a id="delete-<?php echo $ad_block->id ?>" class="delete" href="#"><?php echo __('delete', 'naked_ads') ?></a>
              </td>
      			</tr>
      		<?php endforeach; ?>
      	<?php else : ?>
      		<tr>
      			<td class="colspanchange" colspan="3">
      				<?php echo sprintf( __('There are no ad blocks to display yet. <a href="%s">Add</a> your first ad block.'), naked_ad_block::get_create_new_link() ) ?>
      			</td>
      		</tr>
      	<?php endif; ?>
      </tbody>
    </table>

    <form action="/" name="edit_ad_blocks" id="edit-ad-blocks">
      <input class="button-primary" type="submit" value="Update" />
      <input type="hidden" name="type" value="ad_block" />
      <input type="hidden" name="delete_ids" value="" />
      <input type="hidden" name="action" value="edit_ad_blocks" />
    </form>

  </div><!-- /.wrap -->

<?php else : ?>

  <!-- The Edit Ad Page -->
  <?php include('edit_ad_block.php') ?>

<?php endif; ?>