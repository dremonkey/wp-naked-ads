<?php
/**
 * Template for ad_block settings
 */
?>

<!-- Title Field -->
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>">
		<?php echo __( 'Title', 'naked_ads' ) ?>:
	</label>
	<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo $instance['title'] ?>"/>
</p>

<!-- Hide Title Field -->
<p>
	<label for="<?php echo $this->get_field_id('hide_title'); ?>">
		<?php echo __( 'Should the title be hidden?') ?>
	</label>
	<?php $checked = checked( $instance['hide_title'], 1, false ) ?>
	<input type="checkbox" id="<?php echo $this->get_field_id('hide_title'); ?>" name="<?php echo $this->get_field_name('hide_title'); ?>" value="1" <?php echo $checked ?> />
</p>

<!-- Ad Block Field -->
<p>
	<label for="<?php echo $this->get_field_id('ad_block_id'); ?>">
		<?php echo __( 'Ad Block', 'naked_ads' ) ?>:
	</label>
	<select id="<?php echo $this->get_field_id('ad_block_id') ?>" name="<?php echo $this->get_field_name('ad_block_id') ?>" class="widefat">
		<?php foreach( $ad_blocks as $id=>$ad_block ) : ?>
			<?php $selected = $instance['ad_block_id'] == $id ? 'selected="yes"' : ''; ?>
			<option value="<?php echo $id ?>" <?php echo $selected ?> ><?php echo $ad_block->name ?></option>
		<?php endforeach; ?>
	</select>
</p>

<!-- Special Classes Field -->
<p>
	<label for="<?php echo $this->get_field_id('classes'); ?>">
		<?php echo __( 'Classes:', 'naked_ads' ) ?>
	</label>
	<input type="text" id="<?php echo $this->get_field_id('classes'); ?>" name="<?php echo $this->get_field_name('classes'); ?>" value="<?php echo $instance['classes'] ?>" class="widefat" />
	<span class="description"><?php echo __( 'List of special classes. Separate classes with a space.', 'naked_ads' ) ?></span>
</p>