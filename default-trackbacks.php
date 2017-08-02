<?php
/*
Plugin Name: Default Trackbacks
Plugin URI: http://www.snippetit.com/2009/04/wordpress-plugin-default-trackbacks/
Description: This plugin will send trackbacks to URLs in the list ONLY when user publish a post or change the post status to publish.
Version: 2.0
Author: Low Sze Hau
Author URI: http://www.szehau.com/

*/
/* Copyright 2009 Low Sze Hau  (email : szehau.weblog@gmail.com) */

define( 'DEFAULT_TRACKBACKS_URLS_OPTION', 'default_trackbacks_urls_option' );
define( 'DEFAULT_TRACKBACKS_DEFAULT_OPTION', 'default_trackbacks_default_option' );

function default_tb_activate() {
	add_option( DEFAULT_TRACKBACKS_URLS_OPTION, '' );
	add_option( DEFAULT_TRACKBACKS_DEFAULT_OPTION, 'true' );
}

function default_tb_admin_menu_action() {
	// Add option page
	add_options_page( "Default Trackbacks", "Default Trackbacks", 10, __FILE__, 'default_tb_admin_menu_option' );
	
	// Add meta box
	if( function_exists( 'add_meta_box' ) ) {
		add_meta_box('default_tb', 'Default Trackbacks', 'default_tb_meta_box', 'post');
	} else {
		add_action('dbx_post_advanced', 'default_tb_meta_box_old' );
	}
}

/* Prints the inner fields for the custom post/page section */
function default_tb_meta_box() {
	global $post;
	
	$default_tb = get_option( DEFAULT_TRACKBACKS_DEFAULT_OPTION ) == 'true';
	
	?>
	<label><input type="checkbox" name="default_tb" 
	<?php if ( $default_tb ) { echo 'checked="checked"'; } ?>
	<?php if ( $post->post_status == 'publish' ) { echo 'disabled'; } ?>
	/> Send default trackbacks?</label>
	<?php
}

/* Prints the edit form for pre-WordPress 2.5 post/page */
function default_tb_meta_box_old() {
	global $post;
	
	$default_tb = get_option( DEFAULT_TRACKBACKS_DEFAULT_OPTION );
		
	if ( current_user_can( 'edit_posts' ) ) { ?>
	<fieldset id="sociableoption" class="dbx-box">
	<h3 class="dbx-handle">Default Trackbacks</h3>
	<div class="dbx-content">
		<label><input type="checkbox" name="default_tb" 
		<?php if ( $default_tb ) { echo 'checked="checked"'; } ?>
		<?php if ( $post->post_status == 'publish' ) { echo 'disabled'; } ?>
		/> Send default trackbacks?</label>
	</div>
	</fieldset>
	<?php 
	}
}

function default_tb_publish_post_action( $post ) {

	// Get URLs
	$urls_option = get_option( DEFAULT_TRACKBACKS_URLS_OPTION );
	
	// If default is set and urls is set 
	if ( isset( $_POST['default_tb'] ) && !is_null( $urls_option ) && strlen( $urls_option = trim( $urls_option ) ) > 0 && $post->post_type == 'post' ) {
	
		// Get title and excerpt;
		$has_excerpt = !is_null( $post->post_excerpt ) && trim( $post->post_excerpt ) > 0;
		$title = $post->post_title;
		$excerpt = $has_excerpt ? $post->post_excerpt : $post->post_content;
		
		// Process each url
		$urls = preg_split("/[\s]+/", $urls_option);
		foreach ($urls as $url) {
			// Send the trackback to the url
			trackback( $url, $title, $excerpt, $post->ID );
		}		
	}
	return $post->ID;
}

function default_tb_admin_menu_option() {
	
	// variables for the field and option names 
	$urls_field_name = "default_tb_urls";
	$default_field_name = "default_tb_default";
	
	// Success or error message;
	$message = '';
	
	// Check submitted data
	if( isset( $_POST[ $urls_field_name ] ) ) {
		
		// Trim it
		$urls_field_value = trim( $_POST[ $urls_field_name ] );
		$default_field_value = isset( $_POST[ $default_field_name ] ) ? 'true' : ' false';
		
		// Update value
		update_option( DEFAULT_TRACKBACKS_URLS_OPTION, $urls_field_value  );
		update_option( DEFAULT_TRACKBACKS_DEFAULT_OPTION, $default_field_value  );
		$message = '<div class="updated fade"><p><strong>'._( 'Options saved' ).'</strong></p></div>';
	}
	
	// Read in existing URLs from database
    $urls = get_option( DEFAULT_TRACKBACKS_URLS_OPTION );
	$default = get_option( DEFAULT_TRACKBACKS_DEFAULT_OPTION );
	echo $message;
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br /></div>';
	echo '<h2>Default Trackback</h2>';
	echo '<form name="default_tb" method="post" action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'">';
	echo '<h3>Instruction</h3>';
	echo '<p>';
	echo 'Default Trackback plugin is a plugin that sends trackbacks to the URLs in the list ONLY when you publish a post or set the post status to publish. ';
	echo '</p>';
	echo '<h3>URLs</h3>';
	echo '<p><textarea cols="100" rows="6" name="'.$urls_field_name.'">'.htmlspecialchars($urls).'</textarea>';
	echo '</p>';
	echo '<h3>Default Action</h3>';
	echo '<p><label><input type="checkbox" name="'.$default_field_name.'" value="yes" '.( $default == 'true' ? 'checked="checked"' : '' ).' /> Send trackbacks to the URLs whenever I publish a post.</label>';
	echo '</p>';
	echo '<p class="submit">';
	echo '<input type="submit" name="Submit" class="button-primary" value="'._( 'Save URLs' ).'" />';
	echo '</p>';
	echo '</form>';
	echo '</div>';
}

add_action( 'activate_default-trackaback/default-trackaback.php', 'default_tb_activate' );
add_action( 'admin_menu', 'default_tb_admin_menu_action' );
add_action( 'private_to_publish', 'default_tb_publish_post_action' );
add_action( 'draft_to_publish', 'default_tb_publish_post_action' );
add_action( 'new_to_publish', 'default_tb_publish_post_action' );
add_action( 'future_to_publish', 'default_tb_publish_post_action' );
add_action( 'pending_to_publish', 'default_tb_publish_post_action' );
?>