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
			<?php printf( __('%s Ad Unit', 'naked_ads' ), $text ); ?>
		</h2>

		<div id="msg-box"></div>
	</div>

	<form action="/" name="edit_ad_unit" id="edit-ad-unit">
		
		<fieldset>

			<h3>General Information</h3>

			<div class="fields">
				
				<div class="field-wrapper">
					<label>Ad Unit Name</label>
					<p class="description">The name of this Ad Unit. This must be the same as the name defined in Google DFP.</p>
					<input type="text" name="name" tabindex="1" id="title" value="<?php echo $ad_unit->name ?>"/>
				</div>
				
				<div class="field-wrapper">
					<label>Description</label>
					<p class="description">The description is not displayed anywhere except in the admin section.</p>
					<textarea name="meta[description]" tabindex="2" rows="2" cols="60"><?php echo $ad_unit->description ?></textarea>
				</div>
				
				<div class="field-wrapper">
					<label>Ad Unit Size</label>
					<p class="description"><?php echo __( 'The size of the ad unit. The size should be formatted as follows: [width] x [height] - i.e. 728x90', 'naked_ads' ) ?></p>
					<input type="text" name="meta[size]" tabindex="2" id="size" value="<?php echo $ad_unit->size ?>"/>
				</div>

				<div class="field-wrapper">
					<label>Target Platform</label>
					<p class="description"><?php echo __( 'The platform that this ad is targetted at, either web or mobile', 'naked_ads' ) ?></p>
					<div class="checkboxes horizontal">
						<span>
							<input type="radio" name="meta[platform]" value="web" <?php checked( $ad_unit->platform, 'web' ); ?> />
							<?php echo __( 'web', 'naked_ads' ) ?>
						</span>
						<span>
							<input type="radio" name="meta[platform]" value="mobile" <?php checked( $ad_unit->platform, 'mobile' ); ?> /> 
							<?php echo __( 'mobile', 'naked_ads' ) ?>
						</span>
					</div>
				</div>

			</div><!-- /.fields -->

		</fieldset>

		<fieldset>

			<h3><span>Display Conditions</span></h3>
			
			<div class="fields">

				<div class="field-wrapper">
					<label>Sections</label>
					<p class="description">Choose the section(s) this ad should appear in</p>
					<div class="checkboxes horizontal">
						<?php foreach( $section_list as $section ) : ?>
							
							<?php 
								// $sections is the array of previously saved sections	
								$checked = in_array( $section, $ad_unit->sections ) ? 'checked' : ''; 
							?>

							<span>
								<input type="checkbox" name="meta[conditions][sections][]" value="<?php echo $section ?>" <?php echo $checked ?> /><?php echo $section ?>
							</span>

						<?php endforeach; ?>
					</div>
				</div>


				<div class="field-wrapper">
						<label>Categories</label>
						<p class="description">Choose the categories this ad should appear in</p>
						<div class="checkboxes vertical">
							<?php foreach( $category_list as $catID=>$category ) : ?>
								<?php 
									// $categories is the array of previously saved categories
									if( $action == 'edit' )	
										$checked = in_array( $catID, $ad_unit->categories ) ? 'checked' : ''; 
								?>

								<span>
									<input type="checkbox" name="meta[conditions][categories][]" value="<?php echo $catID ?>" <?php echo $checked ?> /><?php echo $category ?>
								</span>

							<?php endforeach; ?>
						</div>
				</div>

				<div class="field-wrapper">
					<label>Tags</label>
					<p class="description">Separate tags with a comma. These tags will be used to determine if this ad should be displayed.</p>
					<input type="text" name="" id="tags" tabindex="3" value=""/>
					<input type="button" class="button" value="Add" />
					<div id="tags-list" class="autosuggest-selected">
						<!-- content inserted here using javascript -->
					</div>
				</div>

				<div class="field-wrapper">
					<label>PostID(s)</label>
					<p class="description">Separate PostIDs with a comma. These postIDs will be used to determine if this ad should be displayed</p>
					<?php $postIDs = $action == 'edit' ? implode( ',' , $ad_unit->postIDs ) : ''; ?>
					<input type="text" name="meta[conditions][postIDs]" tabindex="4" value="<?php echo $postIDs ?>"/>
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

		<?php $tags = $action == 'edit' ? implode( ',' , $ad_unit->tags ) : ''; ?>
		<input type="hidden" id="meta-conditions-tags" name="meta[conditions][tags]" value="<?php echo $tags ?>"/>

		<input type="hidden" name="type" value="ad_unit" />
	  <input type="hidden" name="action" value="edit_ad_unit" />

	</form>

</div>