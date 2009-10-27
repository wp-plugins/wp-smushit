<?php
	$wp_smushit_gif_to_png = intval(get_option('wp_smushit_gif_to_png'));
?>
<div class="wrap">
<h2>WP Smush.it</h2>
<form method="post" action="options.php">
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="wp_smushit_gif_to_png" />
<?php wp_nonce_field('update-options'); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('When Smush.it suggests converting a GIF to a PNG file&hellip;', WP_SMUSHIT_DOMAIN); ?></th>
<td>
	<select name="wp_smushit_gif_to_png" id="wp_smushit_gif_to_png">
		<option value="1"<?php echo  $wp_smushit_gif_to_png == 1 ? ' selected="selected"' : ''; ?>><?php _e('Overwrite the GIF with a PNG', WP_SMUSHIT_DOMAIN); ?></option>
		<option value="0"<?php echo  $wp_smushit_gif_to_png == 0 ? ' selected="selected"' : ''; ?>><?php _e('Leave the GIF alone', WP_SMUSHIT_DOMAIN); ?></option>
	</select>
</td>
</tr>
</table>
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" /></p>

<?php
//echo get_template_directory()  .'/style.css';
//$theme_data = get_theme_data( get_template_directory() .'/style.css');
//var_dump($theme_data);
?>

<!--<p><a href="admin.php?action=wp_smushit_theme&amp;theme=<?php echo urlencode(get_template_directory()); ?>">Smush current theme</a> (<code><?php echo get_current_theme(); ?></code>)</p>-->


</form>
</div>