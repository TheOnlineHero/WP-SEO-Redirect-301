<?php
/*
Plugin Name: SEO Redirect 301s
Plugin URI: http://wordpress.org/extend/plugins/wp-seo-redirect-301/
Description: Records urls and if a pages url changes, system redirects old url to the updated url.
Version: 2.2.3
Author: Tom Skroza
License: GPL2
*/

if (!class_exists("TomM8")) {
  require_once("lib/tom-m8te.php");
}

function seo_redirect_301_activate() {
  global $wpdb;
  $table_name = $wpdb->prefix . "slug_history";
  $checktable = $wpdb->query("SHOW TABLES LIKE '$table_name'");
  if ($checktable == 0) {
    $sql = "CREATE TABLE $table_name (
    post_id mediumint(9) NOT NULL,
    url VARCHAR(255) DEFAULT '' NOT NULL,
    UNIQUE KEY post_id (post_id, url)
    );";
    $wpdb->query($sql); 
  }
}
register_activation_hook( __FILE__, 'seo_redirect_301_activate' );

add_action('admin_menu', 'register_seo_redirect_301_page');

function register_seo_redirect_301_page() {
   add_menu_page('SEO Redirect 301', 'SEO Redirect 301', 'manage_options', 'wp-seo-redirect-301/seo_redirect_list.php', '',  '', 180);
}

add_action("admin_init", "seo_redirect_301_register_style_scripts");
function seo_redirect_301_register_style_scripts() {
  wp_register_style("seo-301-redirect", plugins_url("/css/style.css", __FILE__));
  wp_enqueue_style("seo-301-redirect");
}

add_action( 'admin_init', 'register_seo_redirect_301_install_dependency_settings' );
function register_seo_redirect_301_install_dependency_settings() {

  if (defined("DISABLE_301_SITEMAP") && DISABLE_301_SITEMAP) {
    // Do nothing.
  } else {
    // Check to see if file exists.
    if (!file_exists(ABSPATH."/301-sitemap.xml")) {

      // File does not exist, so create file.
      seo_redirect_301_do_this_daily(); 
    }
  }

}

add_action( 'save_post', 'seo_redirect_save_current_slug' );
// Save history of slugs/permalinks for the saved page and child pages.
function seo_redirect_save_current_slug( $postid ) {
  $abcTom = new TomM8();
  
  $my_revision = $abcTom->get_row("posts", "*", "post_type='revision' AND ID=".$postid);
  if ($my_revision != null) {
    $my_post = $abcTom->get_row("posts", "*", "post_type IN ('page', 'post') AND ID=".$my_revision->post_parent);

    if ($abcTom->get_row("slug_history", "*", "post_id='".$my_post->ID."' AND url='".get_permalink( $my_post->ID )."'") == null) {
      $abcTom->insert_record("slug_history", array( 'post_id' => $my_post->ID, 'url' => get_permalink( $my_post->ID )));
    }

    $child_pages = get_posts( array('post_type' => 'page','post_parent' => $my_post->ID,'orderby' => 'menu_order'));
    foreach ($child_pages as $child_page) {
      if ($abcTom->get_row("slug_history", "*", "post_id='".$child_page->ID."' AND url='".get_permalink( $child_page->ID )."'") == null) {
        $abcTom->insert_record("slug_history", array( 'post_id' => $child_page->ID, 'url' => get_permalink( $child_page->ID )));
      }
    } 
  }

}

// GET the current url.
function seo_redirect_curl_page_url() {
 $pageURL = 'http';
 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 $port = ':'.$_SERVER['SERVER_PORT'];
 if ($port === ':80' || $port === ':443') {
    $port = '';
 }
 $pageURL .= $_SERVER["SERVER_NAME"].$port.preg_replace("/\/$/", "", $_SERVER["REQUEST_URI"]);
 return $pageURL;
}

add_action('template_redirect', 'seo_redirect_slt_theme_filter_404');  
// Check if page exists.
function seo_redirect_slt_theme_filter_404() {  
  $abcTom = new TomM8();
  // Check if url has a query string.
  if (!preg_match("/\?/", seo_redirect_curl_page_url())) {
    // Url does not have a query string.

    // Check to see if the current page is not the front page. If its the front page, it obviously exists, so don't bother redirecting it.
    if (!is_front_page()) {
      global $wp_query, $post;

      // Get the name of the current template. 
      $template_name = get_post_meta( get_the_id(), '_wp_page_template', true );

      $post_template_name = "";
      $page_slug = str_replace(get_option("siteurl"), "", $abcTom->get_current_url());
      $page_slug = preg_replace("/\?(.+)*$/", "", $page_slug);
      $args=array(
        'name' => $page_slug,
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 1
      );
      $my_posts = get_posts($args);
      if( $my_posts ) {
        if ($my_posts[0]->ID != "") {
          $post_template_name = $my_posts[0]->ID;
        }
      }

      $acceptable_values = array("page", "post");

      // Check if page exists.
      if ((($template_name == "" && $post_template_name == "") || !in_array($wp_query->post->post_type, $acceptable_values))) {

        // Template is blank, which means page does not exist and is a 404. 
        $seo_redirect_curl_page_url = preg_replace("/\/\?(.+)|\?(.+)/", "", seo_redirect_curl_page_url()); // Remove query string temp.
        $matches = array();
        $query_string = preg_match("/\?(.+)/", seo_redirect_curl_page_url(), $matches); // Get query string

        // Try to find record of a page with the current url (with no query string).
        $row = $abcTom->get_row("slug_history", "*", "post_id <> 0 AND url='".$seo_redirect_curl_page_url."/'");
        if ($row->post_id == "") {
          $row = $abcTom->get_row("slug_history", "*", "post_id <> 0 AND url='".$seo_redirect_curl_page_url."'");
        }

        if ($row != null) {

          // Record found, find id of old url, now use id to find current slug/permalink.
          $post_row = $abcTom->get_row("posts", "*", "ID=".$row->post_id);
          // Test to see if url is still the current url.
          if (str_replace(":443", "", $abcTom->get_current_url()) != get_permalink($row->post_id)) {
            // The url isn't current, so redirect.
            $transfer_query_string = "";
            if ($matches) {
              // Query string should be sent to new url.
              $transfer_query_string = "?".$matches[0];
            }

            $tmp = get_permalink($row->post_id);
            // Test if new url exists and is ok.
            if (empty($tmp)) {
              // If something has gone wrong, send to home page instead of blank screen.
              $url = home_url( '/' );
              wp_redirect($url.$transfer_query_string,301);
            } else {
              // Redirect to new url.
              wp_redirect(get_permalink($row->post_id).$transfer_query_string,301);
            }

            exit;
          } else {
            // url is still current so therefore, don't render 404 page.
          }
        } else {
          // Continue as 404, we can't find the page so do nothing.
        }
      } else {
        // Page exists, so it's not a 404.
      }
    }  
  }  else {
    // url has a query string so do nothing.
  }     
}  

if (isset($_GET["post"]) && $_GET["post"] != "") {
  add_action( 'add_meta_boxes', 'seo_redirect_admin_page_widget_box' );
}
function seo_redirect_admin_page_widget_box() {
  $abcTom = new TomM8();
  if (isset($_GET["delete_url"]) && isset($_GET["post"])) {
    $record = $abcTom->get_row("slug_history", array("post_id", "url"), "post_id=".$_GET["post"]."&url='".$_GET["delete_url"]."'");
    // Check if slug history record exists
    if ($record) {
      // slug history record does exist so attempt to delete it.
      // TomM8::delete_record("slug_history", "post_id=".$_GET["post"]." AND url='".$_GET["delete_url"]."'");
      global $wpdb;
      $wpdb->delete($wpdb->prefix."slug_history", array("post_id" => sanitize_text_field($_GET["post"]), "url" => sanitize_text_field($_GET["delete_url"])), array('%d','%s')); 
    }
  }
  
  $screens = array( 'post', 'page' );
  foreach ($screens as $screen) {
      add_meta_box(
          'seo_redirect_admin_widget_id',
          __( 'SEO Redirect 301s', 'seo_redirect_url' ),
          'seo_redirect_inner_custom_box',
          $screen
      );
  }
}

/* Prints the box content */
function seo_redirect_inner_custom_box( $post ) {
  $abcTom = new TomM8();
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'seo_redirect_noncename' );  
  
  $my_redirects = $abcTom->get_results("slug_history", "*", "post_id=".$post->ID);
  ?>
  <script>
  function updateAndRefresh() {
    let currentId = jQuery("#post_ID").val();

    let protocol = location.protocol;
    let domain = location.hostname;
    let port = location.port;

    let url = protocol+'//'+domain;
    if (port != '80' && port != '443') {
      url += ':'+port;
    }

    url += '/'+jQuery('#seo_redirect_url').val();

    jQuery('.components-button.editor-post-publish-button.editor-post-publish-button__button.is-primary').click();
    let c=setInterval(function() {
      if (!jQuery('.components-button.editor-post-publish-button.editor-post-publish-button__button.is-primary').hasClass('is-busy')) {
        jQuery("table.slug-history tbody").append("<tr><td><a target='_blank' href='"+url+"'>"+url+"</a></td><td><a class='delete' href='/wp-admin/post.php?post="+currentId+"&action=edit&delete_url="+url+"'>Delete</a></td></tr>");
        jQuery("table.slug-history tfoot").remove();
        clearInterval(c);
      }
    },1000);
  }
  </script>
  <p>
    <label style="display: block;" for="seo_redirect_url">Please type in a custom url that you want to use to redirect to this page:</label>
    <br/>
    <span style="background: #cac9c9; padding: 10px;">
      <?php echo(get_option("siteurl")); ?>/<input type="text" name="seo_redirect_url" id="seo_redirect_url" />
      <a href="#" class="components-button is-primary" onclick="updateAndRefresh();">Submit</a>
    </span>
  </p>
  <h4><span>These URLs redirect to this page</span></h4>
  <table class="data slug-history">
    <tbody> 
      <?php 
        $record_count = 0;
        foreach($my_redirects as $redirect) { ?>
        <?php if ((get_permalink($redirect->post_id) != "") && (preg_replace("/\/$/", "", $redirect->url) != preg_replace("/\/$/", "", get_permalink($redirect->post_id)))) { 
          $record_count++;
          ?>
          <tr>
            <td><a target="_blank" href="<?php echo($redirect->url); ?>"><?php echo($redirect->url); ?></a></td>
            <td><a class="delete" href="<?php echo(get_option("siteurl")); ?>/wp-admin/post.php?post=<?php echo($redirect->post_id); ?>&action=edit&delete_url=<?php echo($redirect->url); ?>">Delete</a></td>
          </tr>
        <?php } ?>
      <?php } ?>          
    </tbody>
    <?php if ($record_count == 0) { ?>
      <tfoot>
        <tr>
          <td colspan="4">You haven't changed the page/post slug names or created a custom url yet.</td>
        </tr>
      </tfoot>  
    <?php } ?>
  </table>
  <?php

}


/* Do something with the data entered */
add_action( 'save_post', 'seo_redirect_save_postdata' );
/* When the post is saved, saves our custom data */
function seo_redirect_save_postdata( $post_id ) {

  // First we need to check if the current user is authorised to do this action. 
  if ( 'page' == $_REQUEST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // Secondly we need to check if the user intended to change this value.
  if ( ! isset( $_POST['seo_redirect_noncename'] ) || ! wp_verify_nonce( $_POST['seo_redirect_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  // Thirdly we can save the value to the database

  //if saving in a custom table, get post_ID
  $post_ID = $_POST['post_ID'];
  //sanitize user input
  $redirect_url = get_option("siteurl")."/".sanitize_text_field( $_POST['seo_redirect_url'] );

  if ($_POST['seo_redirect_url'] != "") {
    $abcTom = new TomM8();
    $abcTom->insert_record("slug_history", array("post_id" => $post_ID, "url" => $redirect_url));
  }

}


add_action( 'wp', 'seo_redirect_301_setup_schedule' );
/**
 * On an early action hook, check if the hook is scheduled - if not, schedule it.
 */
function seo_redirect_301_setup_schedule() {
  if ( ! wp_next_scheduled( 'seo_redirect_301_daily_event' ) ) {
    wp_schedule_event( time(), 'daily', 'seo_redirect_301_daily_event');
  }
}


add_action( 'seo_redirect_301_daily_event', 'seo_redirect_301_do_this_daily' );
/**
 * On the scheduled action hook, run a function.
 */
function seo_redirect_301_do_this_daily() {
  $abcTom = new TomM8();
  if (defined("DISABLE_301_SITEMAP") && DISABLE_301_SITEMAP) {
    // Do nothing.
  } else {
    $my_redirects = $abcTom->get_results("slug_history", "*", "");
    $content = "<?xml version='1.0' encoding='UTF-8'?><urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'>";
      foreach ($my_redirects as $redirect) {
        $the_url = preg_replace("/\?(.+)*/", "", $redirect->url);
        if ($the_url != "" && $the_url != get_option("siteurl") && $the_url != get_option("siteurl")."/") {
          $content .= 
          "<url> 
            <loc>".$the_url."</loc>
            <lastmod>".gmdate( 'Y-m-d')."T".gmdate( 'H:i')."+00:00</lastmod> 
            <changefreq>daily</changefreq> 
            <priority>0.6</priority> 
          </url>";
        }
      }
    $content .= "</urlset>";
    $abcTom->write_to_file($content, ABSPATH."/301-sitemap.xml");    
  }
  

}

// Deletes a record from the database. Returns a sql delete query object.
function delete_slug_record_by_id($delete_id, $delete_url) {
  global $wpdb;
  $table_name_prefix = $wpdb->prefix."slug_history";
  return $wpdb->query("DELETE FROM $table_name_prefix WHERE post_id=".sanitize_text_field($delete_id)." AND url='".esc_url($delete_url)."'");
}


?>