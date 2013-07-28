<?php 
/**
 * @file
 *	Naked Ads Settings page view (template).
 */

$tabs 		 = $this->tabs;
$default_tab = self::$general_options_key;
$page_key    = self::$options_page_key;

?>

<div class="wrap">
    <?php $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab; ?>

    <?php screen_icon(); ?>
   
   	<h2><?php echo __( 'Naked Ads Settings' , 'naked_ads' ) ?></h2>
   	<h2 class="subsubsub nav-tab-wrapper">
    <?php foreach ( $tabs as $tab_key => $tab_caption ) : ?>
        <?php 

        $active = $current_tab == $tab_key ? 'nav-tab-active' : ''; 
        $link = '?page=' . $page_key . '&tab=' . $tab_key;

        ?>
        <a class="nav-tab <?php echo $active ?>" href="<?php echo $link ?>"><?php echo $tab_caption ?></a>
    <?php endforeach; ?>
    </h2>

    <form method="post" action="options.php" style="clear:both">
        <?php settings_fields( $current_tab ); ?>
        <?php do_settings_sections( $current_tab ); ?>
        <?php submit_button(); ?>
    </form>
</div>