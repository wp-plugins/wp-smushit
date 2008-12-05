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
<th scope="row">When Smush.it suggests converting a GIF to a PNG file&hellip;</th>
<td>
	<select name="wp_smushit_gif_to_png" id="wp_smushit_gif_to_png">
		<option value="1"<?php echo  $wp_smushit_gif_to_png == 1 ? ' selected="selected"' : ''; ?>>Overwrite the GIF with a PNG</option>
		<option value="0"<?php echo  $wp_smushit_gif_to_png == 0 ? ' selected="selected"' : ''; ?>>Leave the GIF alone</option>
	</select>
</td>
</tr>
</table>
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" /></p>
</form>
</div>