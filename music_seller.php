<?php
/*
Plugin Name: Music Seller
Plugin URI: http://shopfiles.com/
Description: A powerful tool for selling music with WordPress
Author: Deian Motov
Author URI:http://shopfiles.com
Version: 2.8
License: GPLv2
*/

include_once('functions.php');

include_once('music_seller_options.php');


/**
 * Adds a box to the main column on the Post and Page edit screens.
 */



add_action( 'add_meta_boxes', 'music_seller_add_meta_box' );
add_action("manage_posts_custom_column", "music_seller_order_custom_columns");
add_filter("manage_edit-music_seller_order_columns", "music_seller_order_columns");
add_action('admin_head', 'hide_that_stuff');
add_action('save_post', 'music_seller_save_custom_meta_data'); 
add_action('post_edit_form_tag', 'music_seller_update_edit_form');
add_action( 'init', 'music_seller_orders_post_type' );
add_action('plugins_loaded', 'music_seller_ipn');
add_action( 'init', 'music_seller_process_download', 100 );



?>