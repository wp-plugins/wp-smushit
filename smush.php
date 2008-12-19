<?php
/**
 * Admin page for manually smushing an attachment.
 *
 * Expects an `attachment_ID` in the query string.
 *
 * @version 1.1
 * @package WP_SmushIt
 */

if ( !isset($_GET['attachment_ID']) )
	wp_die('Must provide attachment ID');

$attachment_ID = intval($_GET['attachment_ID']);


$original_meta = wp_get_attachment_metadata( $attachment_ID );

$new_meta = wp_smushit_resize_from_meta_data( $original_meta );

wp_update_attachment_metadata( $attachment_ID, $new_meta );

$sendback = wp_get_referer();
$sendback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $sendback);

// so we don't bounce around too much on wp-admin/upload.php
$sendback .= '#post-' . $attachment_ID;
wp_redirect($sendback);

exit(0);