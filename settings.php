<?php

  function wp_smushit_register_settings() {
    add_settings_section( 'wp_smushit_settings',
    		'WP Smush.it',
    		'wp_smushit_settings_section_callback_function',
    		'media' );

     	add_settings_field( 'wp_smushit_auto',
    		'Automatic smushing',
    		'wp_smushit_auto_setting_callback_function',
    		'media',
    		'wp_smushit_settings' );

   	register_setting( 'media', 'wp_smushit_auto');
  }
  add_action('admin_init', 'wp_smushit_register_settings');

  function wp_smushit_settings_section_callback_function() {
  }

  function wp_smushit_auto_setting_callback_function() {
  	echo '<input name="wp_smushit_auto" id="wp_smushit_auto" type="checkbox" value="1" class="code" ' . checked( 1, get_option('wp_smushit_auto'), false ) . ' /> Automatically process images on upload?';
  }