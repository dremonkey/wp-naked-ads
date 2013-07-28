<?php
/**
 * @file
 *	Edit Single Ad Admin Page Template
 */
?>

<div class="wrap">

	<div class="pagetitle">
		<?php screen_icon(); ?>
		
		<h2>
			<?php $text = $action == 'edit' ? 'Edit' : 'Add New'; ?>
			<?php printf( __('%s Ad Block', 'naked_ads' ), $text ); ?>
		</h2>

		<div id="msg-box"></div>
	</div>

	<form action="/" name="edit_ad_block" id="edit-ad-block">

		<fieldset>

			<div class="fields">
				
				<div class="field-wrapper">
					<label>Ad Block Name</label>
					<p class="description"><?php echo __( 'The name of this Ad Block.', 'naked_ads' ) ?></p>
					<input type="text" name="name" tabindex="1" id="title" value="<?php echo $ad_block->name ?>"/>
				</div>

				<div class="field-wrapper">
					<label>Ad Block Description</label>
					<p class="description"><?php echo __( 'The description is not displayed anywhere except in the admin section.', 'naked_ads' ) ?></p>
					<textarea name="meta[description]" tabindex="2" rows="2" cols="60"><?php echo $ad_block->description ?></textarea>
				</div>
		
				<div class="field-wrapper">
					<label>Ad Units</label>
					<p class="description"><?php echo __( 'List of ad units that this ad block will handle. Separate ad units with a comma.', 'naked_ads' ) ?></p>
					<input type="text" name="meta[ad_units]" id="ad-units" tabindex="3" value=""/>
					<input type="button" class="button" value="Add" />
					<div id="ad-units-list" class="autosuggest-selected">
						<!-- content inserted here using javascript -->
					</div>
				</div>

				<div class="field-wrapper">
					<label>Display Multiple Ad Units</label>
					<input type="checkbox" name="meta[display_multiple]" id="display_multiple" tabindex="4" value="1" <?php echo checked( $ad_block->display_multiple, 1 ); ?> />
					<span class="description"><?php echo __( 'Check to display multiple ad units in this ad block', 'naked_ads' ) ?></span>
				</div>

			</div><!-- /.fields -->

		</fieldset>

		<div class="submitbox">
			<?php if( $action == 'edit' ) : ?>
				<input class="button-primary" type="submit" value="Update" />
		  	<input type="hidden" name="id" value="<?php echo $id ?>" />
		  <?php else : ?>
		  	<input class="button-primary" type="submit" value="Save" />
			<?php endif; ?>
		</div>
		
		<?php $ad_units = $action == 'edit' ? implode( ',' , array_keys( $ad_block->ad_unit_edit_links ) ) : ''; ?>
		<input type="hidden" id="meta-ad-units" name="meta[ad_units]" value="<?php echo $ad_units ?>"/>

		<input type="hidden" name="type" value="ad_block" />
		<input type="hidden" name="action" value="edit_ad_block" />
	
	</form>

</div>