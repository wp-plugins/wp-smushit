=== WP Smush.it ===
Plugin Name: WP Smush.it
Version: 1.1.2
Author: Dialect
Author URI: http://dialect.ca/?wp_smush_it
Contributors: alexdunae
Tags: images, image, attachments, attachment
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 1.1.2

Reduce image file sizes and improve performance using the <a href="http://smush.it/">Smush.it</a> API within WordPress.

== Description ==

Yahoo's excellent <a href="http://developer.yahoo.com/performance/">Exceptional Performance series</a> recommends <a href="http://developer.yahoo.com/performance/rules.html#opt_images">optimizing images</a> in several lossless ways:

* stripping meta data from JPEGs
* optimizing JPEG compression
* converting certain GIFs to indexed PNGs
* stripping the un-used colours from indexed images

<a href="http://smush.it/">Smush.it</a> offers an API that performs these optimizations (except for stripping JPEG meta data) automatically, and this plugin seamlessly integrates Smush.it with WordPress.

= How does it work? =
Every image you add to a page or post will be automatically run through Smush.it behind the scenes.  You don&rsquo;t have to do anything different.

*N. B. In some cases GIFs should be replaced with PNG files.  You can control this behaviour on the `Options` page.  It is off by default.*

= Existing images =
You can also run your existing images through Smush.it via the WordPress `Media Library`.  Click on the `Smush.it now!` link for any image you'd like to smush.

= Privacy = 
Be sure you&rsquo;re comfortable with Smush.it&rsquo;s privacy policy (found on their <a href="http://smush.it/faq.php">FAQ</a>).

== Screenshots ==

1. See the savings from Smush.it in the Media Library.

== Installation ==

1. Upload the `wp-smushit` plugin to your `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Done!

== Contact and Credits ==

Written by Alex Dunae at Dialect ([dialect.ca](http://dialect.ca/?wp_smush_it), e-mail 'alex' at 'dialect dot ca'), 2008.

WP Smush.it includes a copy of the [PEAR JSON library](http://pear.php.net/pepr/pepr-proposal-show.php?id=198) written by Michal Migurski.

Smush.it was created by [Nicole Sullivan](http://www.stubbornella.org/content/) and [Stoyan Stefanov](http://phpied.com/).
