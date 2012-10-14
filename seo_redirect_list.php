<?php

require_once(dirname(__FILE__).'../../../../wp-admin/admin.php'); 

global $wpdb;

$table_name = $wpdb->prefix."slug_history";
$my_redirects = $wpdb->get_results("SELECT * FROM $table_name");

if ($_GET["delete_id"] != "") {
  $sql = "DELETE FROM $table_name WHERE post_id=".$_GET["delete_id"]." AND url='".$_GET["delete_url"]."';";
  $wpdb->query($sql);
  wp_redirect("".get_option("siteurl")."/wp-admin/admin.php?page=wp-seo-redirect-301/seo_redirect_list.php", 200);
}

wp_enqueue_script('jquery');

?>

<script language="javascript">
  jQuery(function() {
    jQuery("a.delete").click(function() {
      if (!confirm("Are you sure?")) {
        return false;
      }
    });
  });
</script>

<h2>SEO Redirect 301</h2>
<div class="postbox " style="display: block; ">
<div class="inside">
		<table class="data">
			<tbody>	
			  <?php foreach($my_redirects as $redirect) { ?>
			    <?php if ((get_permalink($redirect->post_id) != "") && ($redirect->url != get_permalink($redirect->post_id))) { ?>
  			    <tr>
  			      <td><a target="_blank" href="<?php echo($redirect->url); ?>"><?php echo($redirect->url); ?></a></td>
  			      <td><strong style="margin: 0 10px;">redirects to</strong></td>
  			      <td><a target="_blank" href="<?php echo(get_permalink($redirect->post_id)); ?>"><?php echo(get_permalink($redirect->post_id)); ?></a></td>
  			      <td><a class="delete" href="<?php echo(get_option("siteurl")); ?>/wp-admin/admin.php?page=wp-seo-redirect-301/seo_redirect_list.php&delete_id=<?php echo($redirect->post_id); ?>&delete_url=<?php echo($redirect->url); ?>">Delete</a></td>
  			    </tr>
  			  <?php } ?>
			  <?php } ?>

			</tbody>
		</table>

</div>
</div>

