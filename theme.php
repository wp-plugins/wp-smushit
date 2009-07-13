<?php
/**
 * Admin page for smushing all the images in a given WordPress theme.
 *
 * Expects a `theme` in the query string.
 *
 * @version 1.3
 * @package WP_SmushIt
 */

// TODO: move writable and is_file checks to smushit();

ob_start();
 wp_enqueue_script( 'common'); 
	$theme = null;
	$theme_path = null;
	$theme_url = null;

	if ( isset($_GET['theme']) && !empty($_GET['theme']) ) {

		$theme = attribute_escape($_GET['theme']);
		$theme_path = get_theme_root() . '/' . $theme;
		$theme_url = get_theme_root_uri() . '/' . $theme;
	}

?>
<div class="wrap">
<div id="icon-plugins" class="icon32"><br /></div><h2>WP Smush.it: Smush Theme Assets</h2>

<?php 
	// Smush files
	if (isset($_POST['action']) && $_POST['action'] == 'smush_theme' ):

		check_admin_referer('wp-smushit_smush-theme' . $theme);


?>

<p>Processing files in the <strong><?php echo $theme; ?></strong> theme.</p>

<?php

	foreach($_POST['smushitlink'] as $l) {
			// decode and sanitize the file path
			$asset_url = base64_decode($l);
			$asset_path = str_replace($theme_url, $theme_path, $asset_url);
			$asset_path = realpath($asset_path);

			// check the file is within the current theme directory
			if ( 0 != stripos($asset_path, $theme_path) ) {
				print "<p><span class='code'>$asset_path</span> is outside of the theme directory.</p>\n";
				continue;
			}

			// check that the file exists
			if ( FALSE === file_exists($asset_path) || FALSE === is_file($asset_path) ) {
				print "<p>Could not find <span class='code'>$asset_path</span>.</p>\n";
				continue;
			}

			// check that the file is writable			
			if ( FALSE === is_writable($asset_path) ) {
				print "<p><span class='code'>$asset_path</span> is not writable.</p>\n";
				continue;			
			}
		
			print "<p>Smushing <span class='code'>$asset_url</span><br/>";
		
			list($processed_path, $msg) = wp_smushit($asset_path, $asset_url);

			echo "<em>&#x2013; $msg</em>.</p>\n";
			ob_flush();
		}

 			
?>

<p>Finished processing all the files in the <strong><?php echo $theme; ?></strong> theme.</p>

<p><strong>Actions:</strong> <a href="<?php echo '?page=' . basename(dirname(__FILE__)) . '/theme.php'; ?>" title="Return to Plugin Installer" target="_parent">Smush another theme&rsquo;s assets</a></p>

<!-- <p><strong>Actions:</strong> <a href="plugins.php?action=activate&amp;plugin=google-sitemap-generator%2Fsitemap.php&amp;_wpnonce=2b4ca1722c" title="Activate this plugin" target="_parent">Activate Plugin</a> | <a href="http://localhost/wp28/wp-admin/plugin-install.php" title="Return to Plugin Installer" target="_parent">Return to Plugin Installer</a></p> -->


<?php 
	// Select files to smush
	elseif( $theme ):
		$td = get_theme_data($theme_path  . '/style.css');

		$handle = opendir($theme_path);
		if ( FALSE === $handle ) {
			wp_die('Error opening ' . $theme_path);
		}
?>

<form method="post" action="">
<input type="hidden" name="action" value="smush_theme"/>
<?php 
	if ( function_exists('wp_nonce_field') ) wp_nonce_field('wp-smushit_smush-theme' . $theme);
?>

<table class="widefat fixed" cellspacing="0">
	<thead>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<th scope="col" id="name" class="manage-column column-name" style="">File name</th>
	</tr>
	<tbody>
<?php

    while (false !== ($file = readdir($handle))) {

		if ( preg_match('/\.(jpg|jpeg|png|gif)$/i', $file) < 1 ) {
			continue;
		}
		
		$file_url = $theme_url . '/' . $file;
		
		
?>
	<tr id="asdasdasd" valign="middle">
		<th scope="row" class="check-column"><input type="checkbox" name="smushitlink[]" value="<?php echo attribute_escape(base64_encode($file_url)); ?>" /></th>
		<td class="column-name"><strong><a class='row-title' href='<?php echo $file_url; ?>'><?php echo $file_url; ?></a></strong></td>
	</tr>
<?php

    }
    closedir($handle);
?>
</table>

<input type="submit">
</form>




<?php else: ?>


<p>Select a theme.</p>
<ul>
<?php 
	$themes = get_themes();
	
	foreach($themes as $t) {
	
		printf("\t<li><a href=\"?page=%s&amp;theme=%s\">%s</a></li>\n",
				basename(dirname(__FILE__)) . '/theme.php',
				$t['Template'],
				$t['Name']);
				
	
	}
	
	//var_dump($themes);
?>
</ul>
<?php endif; ?>
</div>

<?php 

exit;


/*

div class="wrap">	<div id="icon-plugins" class="icon32"><br /></div>
<h2>Installing Plugin: Google XML Sitemaps 3.1.4</h2><p>Downloading install package from <span class="code">http://downloads.wordpress.org/plugin/google-sitemap-generator.3.1.4.zip</span>.</p>
<p>Unpacking the package.</p>

<p>Installing the plugin.</p>
<p>Successfully installed the plugin <strong>Google XML Sitemaps 3.1.4</strong>.</p>
<p><strong>Actions:</strong> <a href="plugins.php?action=activate&amp;plugin=google-sitemap-generator%2Fsitemap.php&amp;_wpnonce=2b4ca1722c" title="Activate this plugin" target="_parent">Activate Plugin</a> | <a href="http://localhost/wp28/wp-admin/plugin-install.php" title="Return to Plugin Installer" target="_parent">Return to Plugin Installer</a></p>
</div>
**/