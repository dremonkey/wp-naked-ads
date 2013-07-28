<?php
/**
 * Template file for an ad_block widget
 */
?>

<?php if( isset( $ad_units ) && !empty( $ad_units ) ) : ?>

	<?php if( isset( $before_widget ) ) echo $before_widget ?>

	<?php if( isset( $title ) && !$hide_title ) : ?>

		<?php echo $before_title . $title . $after_title ?>

	<?php endif; ?>

	<div class="<?php if( isset( $classes ) ) echo $classes ?>">

		<?php foreach ( $ad_units as $i => $ad_unit ) : ?>

			<?php $height 	= $ad_unit->height; ?>
			<?php $width 	= $ad_unit->width; ?>

			<div id="<?php echo $ad_unit_id_base . '-ad-' . $i ?>" class="ad" style="width:<?php echo $width . 'px' ?>; height:<?php echo $height . 'px' ?>">

				<?php if( ad_block_widget::is_placeholder( $ad_unit ) ) : ?>
					<a href="mailto:ads@soompi.com" class="placeholder" style="line-height:<?php echo $height . 'px' ?>"><?php echo __( 'Become a Sponsor', 'naked' ) ?></a>
				<?php else : ?>
					<span class="ad-unit-name" style="display:none;">
						<?php echo $ad_unit->name ?>
					</span>
					<span class="mobile-ad-unit-name" style="display:none;">
						<?php if( $mobile_ad_units[ $i ] ) echo $mobile_ad_units[ $i ]->name ?>
					</span>
					<!-- Ad will be inserted here using javascript -->
				<?php endif; ?>

			</div>
		<?php endforeach ?>
	</div>

	<?php if( isset( $after_widget ) ) echo  $after_widget ?>

<?php endif; ?>