<?php
/**
 * Admin page for smushing all the images in a given WordPress theme.
 *
 * Expects a `theme` in the query string.
 *
 * @version 1.3
 * @package WP_SmushIt
 */

?>
<div class="wrap">
<div id="icon-plugins" class="icon32"><br /></div><h2>WP Smush.it: Smush Theme Assets</h2>

<?php if ( isset($_GET['theme']) && !empty($_GET['theme']) ): 

		$theme = $_GET['theme'];
		$theme_path = get_theme_root() . '/' . $theme;
		$theme_url = get_theme_root_uri() . '/' . $theme;
		//print $theme_path;
		$td = get_theme_data($theme_path  . '/style.css');
		//var_dump($td);


?>
<p>Processing files in the <strong><?php echo $td['Title']; ?></strong> theme.</p>

<?php


if ($handle = opendir($theme_path)) {
    while (false !== ($file = readdir($handle))) {

		if ( preg_match('/\.(jpg|jpeg|png|gif)$/i', $file) > 0 ) {
			 echo "<p>Smushing <span class='code'>$theme_url/$file</span><br/>";
			 echo "<em>&#x2013; saved 4.2 KB (5.3%)</em>.</p>\n";
 			 ob_flush();
		}
    }
    closedir($handle);
}





?>

<p>Finished processing all the files in the <strong><?php echo $td['Title']; ?></strong> theme.</p>
<!-- <p><strong>Actions:</strong> <a href="plugins.php?action=activate&amp;plugin=google-sitemap-generator%2Fsitemap.php&amp;_wpnonce=2b4ca1722c" title="Activate this plugin" target="_parent">Activate Plugin</a> | <a href="http://localhost/wp28/wp-admin/plugin-install.php" title="Return to Plugin Installer" target="_parent">Return to Plugin Installer</a></p> -->

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