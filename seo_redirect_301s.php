<?php
/*
Plugin Name: SEO Redirect 301s
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Records urls and if a pages url changes, system redirects old url to the updated url.
Version: 1.2
Author: Tom Skroza
Author URI: 
License: GPL2
*/

add_action('admin_menu', 'register_seo_redirect_301_page');

function register_seo_redirect_301_page() {
   add_menu_page('SEO Redirect 301', 'SEO Redirect 301', 'manage_options', 'wp-seo-redirect-301/seo_redirect_list.php', '',  '', 180);
}

add_action( 'save_post', 'save_current_slug' );
function save_current_slug( $postid ) {
	global $wpdb;
  $table_name = $wpdb->prefix . "slug_history";  
  $post_table = $wpdb->prefix . "posts";
  $my_revision = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $post_table WHERE post_type='revision' AND id= %d", $postid) );
  $my_post = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $post_table WHERE post_type IN ('page', 'post') AND id=%d", $my_revision->post_parent) );
  $rows_affected = $wpdb->insert( $table_name, array( 'post_id' => $my_post->ID, 'url' => get_permalink( $my_post->ID )) );
	$child_pages = get_posts( array('post_type' => 'page','post_parent' => $my_post->ID,'orderby' => 'menu_order'));
	foreach ($child_pages as $child_page) {
		$rows_affected = $wpdb->insert( $table_name, array( 'post_id' => $child_page->ID, 'url' => get_permalink( $child_page->ID )) );
	}
}


function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

add_action( 'template_redirect', 'slt_theme_filter_404', 0 );  
function slt_theme_filter_404() {  
    
    global $wpdb, $wp_query, $post;
        // Get the name of the current template. 
        $template_name = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );
        if ( $template_name == "") { 
           // Template is blank, which means page does not exist and is a 404. 
            $wp_query->is_404 = false;  
            $wp_query->is_archive = true;  
            $wp_query->is_post_type_archive = true;  
            $post = new stdClass();  
            $post->post_type = $wp_query->query['post_type']; 
    
           $table_name = $wpdb->prefix . "slug_history";
    
           // Look for old url in slug history table and find out the page id.
           $sql = "SELECT * FROM $table_name where url='".curPageURL()."'";
            $results=$wpdb->get_results($sql);
           if ($wpdb->num_rows > 0) {
             // Now that we have page id, we can now find the current url.
             foreach ($results as $row){
               wp_redirect(get_option('siteurl')."/?page_id=".$row->post_id, 301);
             }
           } 
           
           // TODO: Future functionality. Basically assign a url to be used to redirect to a page of developers choosing.
           // else if($_SERVER["REQUEST_URI"] == "/wordpress/carsrewrwrwe/") {
           //              wp_redirect(get_option('siteurl')."/cars", 301);
           //            }
        }  
}  

function seo_redirect_301_activate() {
   global $wpdb;
   $table_name = $wpdb->prefix . "slug_history";
   $sql = "CREATE TABLE $table_name (
post_id mediumint(9) NOT NULL,
url VARCHAR(255) DEFAULT '' NOT NULL,
UNIQUE KEY post_id (post_id, url)
);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

}
register_activation_hook( __FILE__, 'seo_redirect_301_activate' );

?>