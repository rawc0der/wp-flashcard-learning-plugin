<div class="wrap">
    <h2>Flashcards</h2>
    <input type="hidden" id="siteurl" value="<?php echo site_url('/'); ?>">
   	
    <input type="hidden" id="color_n" value="<?php echo get_option( 'color_n'); ?>">
    <input type="hidden" id="color_p" value="<?php echo get_option( 'color_p'); ?>">
    <input type="hidden" id="color_l" value="<?php echo get_option( 'color_l'); ?>">
   
    <?php if(current_user_can('edit_posts')) { ?>
    <form method="post" action="options.php"> 
        <?php  @settings_fields('wp_plugin_template-group'); ?>
        <?php  @do_settings_fields('wp_plugin_template-group'); ?>

        <?php  do_settings_sections('wp_plugin_template'); ?>

        <?php  @submit_button(); ?>
    </form>
    <div id="FlashcardLibrary">
		<div class="ftitle"> <h3>Flashcard Collection</h3> </div>
		<div>
			<p id="WordCollection"></p>
		</div>
	</div>
	<?php } else { 

		$no_cards = get_user_meta(get_current_user_id(), 'no_cards', true);
		if ($no_cards == "") {
			$no_cards = get_option('no_cards');
		}
		
		?>
		<div> 
			<label for="no">Cards per Session:</label><input id="no" type="text" size="4" value="<?php echo $no_cards; ?>">
			<input id="changeNo" type="button" value="Save">
		</div>
		<div id="FlashcardLibrary">
			<div class="ftitle"> <h3>Flashcard Collection</h3> </div>
			<div>
				<p id="WordCollection"></p>
			</div>
		</div>
		<?php 
			$uid = get_current_user_id(); 
			$user_words = get_user_meta($uid  ); 
		?>
	<?php }?>
</div>