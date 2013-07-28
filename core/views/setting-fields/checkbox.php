<?php
/**
 * Template for a standard form checkbox field
 */

$checked = checked( '1', $value, false );

?>

<div class="field-wrapper">
	<label class="description" for="<?php echo $name ?>">
		<input name="<?php echo $name ?>" type="checkbox" value="1" <?php echo $checked ?> />
		<?php echo $desc ?>
	</label>
</div>