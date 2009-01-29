<?php
/**
 * Integrate the Smush.it API into WordPress.
 * @version 1.2
 * @package WP_SmushIt
 */
/*
Plugin Name: WP Smush.it
Plugin URI: http://dialect.ca/code/wp-smushit/
Description: Reduce image file sizes and improve performance using the <a href="http://smush.it/">Smush.it</a> API within WordPress.
Author: Dialect
Version: 1.2
Author URI: http://dialect.ca/?wp_smush_it
*/

if ( !class_exists('Services_JSON') ) {
	require_once('JSON/JSON.php');
}


/**
 * Constants
 */

define('SMUSHIT_REQ_URL', 'http://smush.it/ws.php?img=%s');

define('SMUSHIT_BASE_URL', 'http://smush.it/');

define('WP_SMUSHIT_DOMAIN', 'wp_smushit');

define('WP_SMUSHIT_UA', 'WP Smush.it/1.2 (+http://dialect.ca/code/wp-smushit)');

define('WP_SMUSHIT_GIF_TO_PNG', intval(get_option('wp_smushit_gif_to_png')));

define('WP_SMUSHIT_PLUGIN_DIR', dirname(plugin_basename(__FILE__)));

if ( !defined('WP_CONTENT_URL') )
	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');

if ( !defined('WP_CONTENT_DIR') )
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content' );

/**
 * Hooks
 */

register_activation_hook(__FILE__,'wp_smushit_install');

add_filter('wp_generate_attachment_metadata', 'wp_smushit_resize_from_meta_data');

add_filter('manage_media_columns', 'wp_smushit_columns');
add_action('manage_media_custom_column', 'wp_smushit_custom_column', 10, 2);
add_action('admin_menu', 'wp_smushit_add_pages');
add_action('admin_init', 'wp_smushit_init');



/**
 * Process an image with Smush.it.
 *
 * Returns an array of the $file $results.
 *
 * @param   string $file            Full path to the image file
 * @returns array
 */
function wp_smushit($file) {
	// dont't run on localhost
	if( '127.0.0.1' == $_SERVER['SERVER_ADDR'] )
		return array($file, __('Not processed (local file)', WP_SMUSHIT_DOMAIN));


	$file_path = $file;
	$file_url = '';

	if ( 0 === strpos($file, WP_CONTENT_DIR) ) {
		// WordPress < 2.6.2: $file is already an absolute path
		$file_url = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $file );
	} else {
		// WordPress >= 2.6.2: determine the absolute $file_path and $file_url
		$uploads = wp_upload_dir();
		$file_path = trailingslashit( $uploads['basedir'] ) . $file;
		$file_url  = trailingslashit( $uploads['baseurl'] ) . $file;
	}

	$data = wp_smushit_post($file_url);
	
	if ( false === $data )
		return array($file, __('Error posting to Smush.it', WP_SMUSHIT_DOMAIN));


	// make sure the response looks like JSON -- added 2008-12-19 when
	// Smush.it was returning PHP warnings before the JSON output
	if ( strpos( trim($data), '{' ) != 0 )
		return array($file, __('Bad response from Smush.it', WP_SMUSHIT_DOMAIN));


	// read the JSON response
	if ( function_exists('json_decode') ) {
		$data = json_decode( $data );
	} else {
		$json = new Services_JSON();
		$data = $json->decode($data);
	}

	if ( -1 == intval($data->dest_size) )
		return array($file, __('No savings', WP_SMUSHIT_DOMAIN));

	if ( !$data->dest ) {
		$err = ($data->error ? $data->error : 'Unknown error');
		return array($file, __($err, WP_SMUSHIT_DOMAIN) );
	}

	$processed_url = SMUSHIT_BASE_URL . $data->dest;


	$temp_file = wp_smushit_download($processed_url);
	
	if ( false === $temp_file )
		return array($file, __('Error updating file', WP_SMUSHIT_DOMAIN) );

	// check if Smush.it converted a GIF to a PNG
	if( 1 == WP_SMUSHIT_GIF_TO_PNG && wp_smushit_did_gif_to_png($file, $data->dest) ) {
		$file = preg_replace('/.gif$/i', '.png', $file);
		$file_path = preg_replace('/.gif$/i', '.png', $file_path);

		if ( has_filter('wp_update_attachment_metadata', 'wp_smushit_update_attachment') === false )
			add_filter('wp_update_attachment_metadata', 'wp_smushit_update_attachment', 10, 2);
	}

	@rename( $temp_file, $file_path );

	$results_msg = sprintf(__("Reduced by %01.1f%%", WP_SMUSHIT_DOMAIN), $data->percent);

	return array($file, $results_msg);
}




/**
 * Update the attachment's meta data after being smushed.
 *
 * This is only needed when GIFs become PNGs so we add the filter near
 * the end of `wp_smushit()`. It's used by the `wp_update_attachment_metadata`
 * hook, which is called after the `wp_generate_attachment_metadata` on upload.
 */
function wp_smushit_update_attachment($data, $ID) {
	$orig_file = get_attached_file( $ID );

	if( wp_smushit_did_gif_to_png($orig_file,  $data['file']) ) {
		update_attached_file( $ID, $data['file'] );

		// get_media_item() uses the GUID for a display title so we should
		// update the GUID here
		$post = get_post( $ID );
		$guid = preg_replace('/.gif$/i', '.png', $post->guid);

		wp_update_post( array('ID' => $ID,
		                      'post_mime_type' => 'image/png',
		                      'guid' => $guid) );
	}

	return $data;
}


/**
 * Read the image paths from an attachment's meta data and process each image
 * with wp_smushit().
 *
 * This method also adds a `wp_smushit` meta key for use in the media library.
 *
 * Called after `wp_generate_attachment_metadata` is completed.
 */
function wp_smushit_resize_from_meta_data($meta) {
	list($meta['file'], $meta['wp_smushit']) = wp_smushit($meta['file']);

	if ( !isset($meta['sizes']) )
		return $meta;

	$base_dir = dirname($meta['file']) . '/';

	foreach($meta['sizes'] as $size => $data) {
		list($smushed_file, $results) = wp_smushit($base_dir . $data['file']);
		$smushed_file = str_replace($base_dir, '', $smushed_file);
		$meta['sizes'][$size]['file'] = $smushed_file;
		$meta['sizes'][$size]['wp_smushit'] = $results;
	}

	return $meta;
}


/**
 * Print column header for Smush.it results in the media library using
 * the `manage_media_columns` hook.
 */
function wp_smushit_columns($defaults) {
	$defaults['smushit'] = 'Smush.it';
	return $defaults;
}

/**
 * Print column data for Smush.it results in the media library using
 * the `manage_media_custom_column` hook.
 */
function wp_smushit_custom_column($column_name, $id) {
    if( $column_name == 'smushit' ) {
    	$data = wp_get_attachment_metadata($id);
    	if ( isset($data['wp_smushit']) && !empty($data['wp_smushit']) )
    		print $data['wp_smushit'];
    	else
    		print __('Not processed', WP_SMUSHIT_DOMAIN);

    	printf("<br><a href=\"admin.php?page=%s/smush.php&amp;attachment_ID=%d&amp;noheader\">%s</a>",
		         WP_SMUSHIT_PLUGIN_DIR,
		         $id,
		         __('Smush.it now!', WP_SMUSHIT_DOMAIN));
    }
}

/**
 * Compare file names to see if the extension changed from `.gif` to `.png`.
 *
 * @returns bool
 */
function wp_smushit_did_gif_to_png($orig, $new) {
	return (0 === stripos(strrev($new), 'gnp.') &&
	        0 === stripos(strrev($orig), 'fig.') );

}

function wp_smushit_install() {
	add_option('wp_smushit_gif_to_png', 0);
}

function wp_smushit_init() {
	load_plugin_textdomain(WP_SMUSHIT_DOMAIN);
}

function wp_smushit_add_pages() {
	add_options_page(__('WP Smush.it Options', WP_SMUSHIT_OPTIONS), 'WP Smush.it', 8, dirname(__FILE__) . '/options.php');
}

function wp_smushit_options() {
	include_once 'options.php';
}




































/**
 * Download a remote file to a temp file.
 *
 * Used to download a processed image from Smush.it.
 *
 * @param   string          $remote_file     URL of the file to download
 * @return  string|boolean  Returns the temp file path on success or else FALSE
 */
function wp_smushit_download($remote_file) {
	$temp_file = tempnam( WP_CONTENT_DIR, '___' );

	if ( function_exists('wp_remote_get') ) {
		// try using WordPress's built-in HTTP class
		$response = wp_remote_get($remote_file, array('user-agent' => WP_SMUSHIT_UA));

		if ( 200 != wp_remote_retrieve_response_code($response) )
			return false;

		$data =  wp_remote_retrieve_body($response);
		
		if ( false === file_put_contents($temp_file, $data) )
			return false;

	} else {
		// try using 'fopen' via 'copy'
		if( !@copy( $remote_file, $temp_file ) )
			return false;
	}

	chmod( $temp_file, 0644 );
	
	return $temp_file;
}

















/**
 * Post an image to Smush.it.
 *
 * @param   string          $file_url     URL of the file to send to Smush.it
 * @return  string|boolean  Returns the JSON response on success or else FALSE
 */
function wp_smushit_post($file_url) {
	$req = sprintf( SMUSHIT_REQ_URL, urlencode( $file_url ) );

	$data = false;

	if ( function_exists('wp_remote_get') ) {
		$response = wp_remote_get($req, array('user-agent' => WP_SMUSHIT_UA));

		if ( 200 != wp_remote_retrieve_response_code($response) )
			return false;

		$data =  wp_remote_retrieve_body($response);
	} else {
		if ( ! function_exists('fopen') || (function_exists('ini_get') && true != ini_get('allow_url_fopen')) ) {
			$err = __('Remote fopen is not enabled (<a href="http://dialect.ca/code/wp-smushit/#fopen_note" target="_blank">more info</a>)', WP_SMUSHIT_DOMAIN);
			wp_die($err);
			return false;
		}

		$fh = @fopen( $req, 'r' ); // post to Smush.it

		if ( !$fh )
			return false;

		$data = stream_get_contents( $fh );
		fclose( $fh );
	}

	return $data;
}