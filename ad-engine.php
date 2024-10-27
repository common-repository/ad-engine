<?php
/*
Plugin Name: Ad Engine
Plugin URI: http://www.oriontechnologysolutions.com/web-design/ad-engine
Description: Ad Engine Let's you insert image or text ads using shortcode or widgets.
Version: 0.8
Author: Orion Technology Solutions
Author URI: http://www.oriontechnologysolutions.com/
*/
if(!version_compare(PHP_VERSION, '5.0.0', '>=')) {
	add_action('admin_notices', 'ad_engine_php5required');

	function wpwundergroundphp5error() {
		$out = '<div class="error" id="messages"><p>';
		$out .= 'Ad-Engine requires PHP5. Your server is running PHP4. Please ask your hosting company to upgrade your server to PHP5. It should be free.';
		$out .= '</p></div>';
		echo $out;
	}
	return;
}


$ad_engine_types = Array(
  Array('id' => 0, 'name' => 'image')
, Array('id' => 1, 'name' => 'text')
);

$ad_engine_ad_deck = Array();
$ad_slot = 0;

// Ad Comparison
function ad_engine_ad_cmp($a, $b) {
    if ( $a['cat_name'] == $b['cat_name']) {
      if ( $a['co_name'] == $b['co_name']) {
        if ( $a['ad_name'] == $b['ad_name']) {
          return 0;
        }
        return ($a['ad_name'] < $b['ad_name']) ? -1 : 1;
      }
      return ($a['co_name'] < $b['co_name']) ? -1 : 1;
    }
    return ($a['cat_name'] < $b['cat_name']) ? -1 : 1;
}

function ad_engine_get_ad_deck() {
  $jads = ad_engine_get_counts();
  $ad_deck = Array();
  $iter = 0;

  while($iter <= $jads['ads']) {
    $tmp = get_option('ad_engine_' . $iter);
    if(isset($tmp['ad_name']))
      $ad_deck[] = $tmp;
    $iter++;
  }
  uasort($ad_deck, ad_engine_ad_cmp);
  return $ad_deck;
}

// Ad Group Compare function (Used for Sort)
function ad_engine_category_cmp($a, $b) {
    if ($a['name'] == $b['name']) {
        return 0;
    }
    return ($a['name'] < $b['name']) ? -1 : 1;
}

function ad_engine_get_cat_deck() {
  $jads = ad_engine_get_counts();
  $cat_deck = Array();
  $iter = 0;

  while($iter <= $jads['categories']) {
    $tmp = get_option('ad_engine_cat_' . $iter);
    if(isset($tmp['id']))
      $cat_deck[] = $tmp;
    $iter++;
  }
  uasort($cat_deck, ad_engine_category_cmp);
  return $cat_deck;
}

// Client Group Compare function (Used for Sort)
function ad_engine_client_cmp($a, $b) {
    if ($a['name'] == $b['name']) {
        return 0;
    }
    return ($a['name'] < $b['name']) ? -1 : 1;
}

function ad_engine_get_client_deck() {
  $jads = ad_engine_get_counts();
  $client_deck = Array();
  $iter = 0;

  while($iter <= $jads['clients']) {
    $tmp = get_option('ad_engine_client_' . $iter);
    if(isset($tmp['co_id']))
      $client_deck[] = $tmp;
    $iter++;
  }
  uasort($client_deck, ad_engine_client_cmp);
  return $client_deck;
}


// Compare items in an array used by the dropdown function
function ad_engine_dropdown_cmp($a, $b) {
    if ($a['name'] == $b['name']) {
        return 0;
    }
    return ($a['name'] < $b['name']) ? -1 : 1;
}

function ad_engine_form_dropdown($label, $id, $list, $value, $name = '') {
    uasort($list, ad_engine_dropdown_cmp);
    if($name == '') {
        $name = $id;
    }
    $output = sprintf("<dt>%s:</dt><dd><select name='%s' id='%s'>\n", $label, $name, $id);
    foreach($list as $item) {
        if($item['id'] == $value)
            $output .= sprintf("<option value='%d' selected='selected'>%s</option>\n", $item['id'], $item['name']);
        else
            $output .= sprintf("<option value='%d'>%s</option>\n", $item['id'], $item['name']);
    }
    $output .= "</select></dd>\n";
    return($output);
}

function ad_engine_get_counts() {
  $jads = get_option ('ad_engine');
  if(!isset($jads['clients']))
    $jads['clients'] = 0;
  if(!isset($jads['categories']))
    $jads['categories'] = 0;
  if(!isset($jads['ads']))
    $jads['ads'] = 0;

  return($jads);
}
function ad_engine_ad_data($ad_id) {
    $tmp = get_option('ad_engine_' . $ad_id);
    $wud = wp_upload_dir();
    $fdir = $wud['basedir'] . "/ad_engine";
    $wdir = $wud['baseurl'] . "/ad_engine";

    $fname = sprintf("%s/%d-ad.png", $fdir, $tmp['ad_id']);
    $wname = sprintf("%s/%d-ad.png", $wdir, $tmp['ad_id']);

    $rval = Array();
    if(file_exists($fname))
      $rval['img'] = $wname;
    else
      $rval['img'] = "";

    if(isset($tmp['ad_text']))
      $rval['text'] = $tmp['ad_text'];
    else
      $rval['text'] = "";

    $tmp['ad_shown']++;
	ad_update_count($ad_id, 1);
    update_option('ad_engine_' . $tmp['ad_id'] , $tmp);

    return($rval);
  }

function ad_engine_ad_form($client_list, $ad) {
  global $ad_engine_types;
  $cat_deck = ad_engine_get_cat_deck();
  $client_deck = ad_engine_get_client_deck();
  $output = '
    <dl>
      <dt>Name</dt>
      <dd><input name="ad_name" type="input" size="50" value="' . $ad['ad_name'] . '"></dd>';
  $output .= ad_engine_form_dropdown("Ad Group", "ad_cat", $cat_deck, $ad['ad_cat']);
  $output .= ad_engine_form_dropdown("Client", "ad_client", $client_list, $ad['ad_client']);
  $output .= '<dt>Link</dt>
      <dd><input  name="ad_link" "type="input" size="50" value="' . $ad['ad_link'] . '"></dd>';

  //$output .= ad_engine_form_dropdown("Ad Type", "ad_type", $ad_engine_types, $ad['ad_type']);

  $wud = wp_upload_dir();
  $fname = $wud['baseurl'] . "/ad_engine/" . $ad['ad_id'] . "-ad.png";
  $output .= '
      <dt>Text</dt>
      <dd><input  name="ad_text" "type="input" size="50" value="' . $ad['ad_text'] . '"></dd>
      <dt>Image</dt>
      <dd><input type="file" name="ad_image" id="ad_image"></dd><dd><img src="' . $fname . '"></dd>
      <dt>Impressions purchased ( 0 to disable, -1 for infinite)</dt>
      <dd><input  name="ad_imp" "type="input" size="10" value="' . $ad['ad_imp'] . '"></dd>
      <dt>Impressions Shown</dt>
      <dd><input  name="ad_shown" "type="input" size="10" value="' . $ad['ad_shown'] . '"></dd>
      <dt>Clicks</dt>
      <dd><input  name="ad_clicks" "type="input" size="10" value="' . $ad['ad_clicks'] . '"></dd>
    </dl>
';
  return $output;
}

function ad_engine_new_form() {
  global $_GET;
  $jads = ad_engine_get_counts();
  $new_ad = Array(
  );
  $client_list = Array();

  $iter = 1;
  while($iter <= $jads['clients']) {
    $tmp = get_option('ad_engine_client_' . $iter);
    $client_list[] = Array('id' => $tmp['co_id'], 'name' => $tmp['name']);
    $iter++;
  }

  $output = '<form method="POST" action="'.admin_url("admin.php?page=ad-engine-menu"). '" id="ad_engine_form" enctype="multipart/form-data">';
  $output .= ad_engine_ad_form( $client_list, $new_ad);
  $output .= '<input type="hidden" name="ad_shown" value="0">
    <input name="new_ad" type="submit" value="Create Ad">
    <input name="cancel" type="submit" value="Cancel">
</form>';
  echo $output;
}

function ad_engine_edit_form() {
  global $_GET;
  $jads = ad_engine_get_counts();
  $ad = get_option('ad_engine_' . $_GET['id']);
  $cat = get_option('ad_engine_cat_' . $ad['ad_cat']);
  //$cat_list = Array(Array('id' => $cat['id'], 'name' => $cat['name']));
  $cat_list = Array();
  $client = get_option('ad_engine_client_' . $ad['ad_client']);
  //$client_list = Array(Array('id' => $client['co_id'], 'name' => $client['name']));
  $client_list = Array();

  $iter = 1;
  while($iter <= $jads['categories']) {
    $tmp = get_option('ad_engine_cat_' . $iter);
    if(isset($tmp['id'])) {
      $cat_list[] = Array('id' => $tmp['id'], 'name' => $tmp['name']);
    }
    $iter++;
  }

  $iter = 1;
  while($iter <= $jads['clients']) {
    $tmp = get_option('ad_engine_client_' . $iter);
    if(isset($tmp['co_id'])) {
      $client_list[] = Array('id' => $tmp['co_id'], 'name' => $tmp['name']);
    }
    $iter++;
  }

  $output = '<form method="POST" action="'.admin_url("admin.php?page=ad-engine-menu"). '" id="ad_engine_form" enctype="multipart/form-data">';
  $output .= ad_engine_ad_form( $client_list, $ad);
  $output .= ' <input type="hidden" name="ad_id" value="' . $ad['ad_id'] . '">
    <input name="save_ad" type="submit" value="Update Ad">
    <input name="cancel" type="submit" value="Cancel">
</form>';

  echo $output;
}

function ad_engine_form() {
	global $_POST, $wpdb;
	$jads = ad_engine_get_counts();
	$ad_deck = ad_engine_get_ad_deck();
	$dstamp = date("Y-m-d");

	$output = "<div class='wrap'><h2>";
	$output .= __("Advertisements","Advertisements");
	$output .= "<a class=\"button add-new-h2\" href=\"" . admin_url("admin.php?page=ad-engine-new") ."\">Add New</a>";
	$output .= "</h2><p><script type='text/javascript'>
function confirm_delete(ad) {
	rval = confirm(\"Really delete the ad \\n\" + ad.ad_name);
	if(rval == true) {
		document.location = '" . admin_url("admin.php?page=ad-engine-menu&action=ad_delete&id=") . "' + ad.ad_id;
	}
}
</script>";
	$output .= "\n<table class='widefat post fixed'>\n<thead><tr><th>Name</th><th>Ad Group</th><th>Client</th><th>Impressions<br/>Today</th><th>Impressions<br/>Total</th><th>Clicks<br />Today</th><th>Clicks<br />Total</th><th>Actions</th></tr></thead><tbody>\n";

	for($iter = 0; $iter < count($ad_deck); $iter++) {
	  $cl = get_option('ad_engine_client_' . $ad_deck[$iter]['ad_client']);
	  $ct = get_option('ad_engine_cat_' . $ad_deck[$iter]['ad_cat']);
	  $ad_deck[$iter]['cat_name'] = $ct['name'];
	  $ad_deck[$iter]['co_name']  = $cl['name'];
	}
	uasort($ad_deck, ad_engine_ad_cmp);

	while($ad = array_shift($ad_deck)) {
		$dimps = $wpdb->get_var('SELECT count from ad_engine where type=1 AND dstamp="' . $dstamp . '" AND id = ' . $ad['ad_id']);
		if(!isset($dimps))
			$dimps = 0;
		$timps = $wpdb->get_var('SELECT sum(count) from ad_engine where type=1 AND id = ' . $ad['ad_id']);
		if(!isset($timps))
			$timps = 0;
		$dclicks = $wpdb->get_var('SELECT count from ad_engine where type=2 AND dstamp="' . $dstamp . '" AND id = ' . $ad['ad_id']);
		if(!isset($dclicks))
			$dclicks = 0;
		$tclicks = $wpdb->get_var('SELECT sum(count) from ad_engine where type=2 AND id = ' . $ad['ad_id']);
		if(!isset($tclicks))
			$tclicks = 0;

		$output .= sprintf("\n<tr><td><a href='%s'>%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s / %s</td><td>%d (%0.2f%%)</td><td>%d (%0.2f%%)</td><td><a href='%s'>CSV</a> <a class='submitdelete' onclick='confirm_delete(%s)' href='javascript:'>Delete</a></td></tr>"
	                     , admin_url("admin.php?page=ad-engine-edit&id=" . $ad['ad_id'])
	                     , $ad['ad_name']
	                     , $ad['cat_name']
	                     , $ad['co_name']
	                     , $dimps
	                     , $timps
	                     , $ad['ad_imp']
	                     , $dclicks
	                     , ($dimps) ? ($dclicks / $dimps * 100) : 0
	                     , $tclicks
			     , ($timps) ? ($tclicks / $timps * 100) : 0
	                     , site_url("?ae_get_csv=" . $ad['ad_id'])
	                     , json_encode($ad) 
	                    );
	}
	$output .= '</tbody></table></div>';
	echo $output;
}

function ad_engine_client_edit_form() {
  global $_GET;
  $jads = ad_engine_get_counts();
  $nClient = get_option('ad_engine_client_' . $_GET['id']);
?>
  <form method="POST" action="<?php echo admin_url("admin.php?page=ad-engine-client-menu")?>">
    <dl>
      <dt>Client</dt>
      <dd><input  name="name"  type="input" size="50" value="<?php echo $nClient['name']?>"></dd>
      <dt>Contact Name</dt>
      <dd><input  name="ct_name"  type="input" size="50" value="<?php echo $nClient['ct_name']?>"></dd>
      <dt>Contact Email</dt>
      <dd><input  name="ct_email" type="input" size="50" value="<?php echo $nClient['ct_email']?>"></dd>
      <dt>Contact Phone</dt>
      <dd><input  name="ct_phone" type="input" size="50" value="<?php echo $nClient['ct_phone']?>"></dd>
    </dl>
    <input type="hidden" name="co_id" value="<?php echo $nClient['co_id']?>">
    <input name="update_client" type="submit" value="Update Client">
    <input name="cancel" type="submit" value="Cancel">
  </form>
<?php
}

function ad_engine_client_new_form() {
  $jads = ad_engine_get_counts();
  $output = '
  <form method="POST" action="'.admin_url("admin.php?page=ad-engine-client-menu"). '">
    <dl>
      <dt>Client</dt>
      <dd><input name="name" type="input" size="50"></dd>
      <dt>Contact Name</dt>
      <dd><input  name="ct_name" type="input" size="50"></dd>
      <dt>Contact Email</dt>
      <dd><input  name="ct_email" type="input" size="50"></dd>
      <dt>Contact Phone</dt>
      <dd><input  name="ct_phone" "type="input" size="50"></dd>
    </dl>
    <input name="new_client" type="submit" value="Create Client">
    <input name="cancel" type="submit" value="Cancel">
  </form>
';
  echo $output;
}

function ad_engine_client_form() {
  global $_POST;
  $jads = ad_engine_get_counts();
  $client_deck = ad_engine_get_client_deck();
?>
  <div class="wrap">
    <h2><?php echo __("Clients","Clients")?>
      <a class="button add-new-h2" href="<?php echo admin_url("admin.php?page=ad-engine-client-new")?>">Add New</a>
    </h2>
    <table class='widefat post fixed'>
      <thead><tr><th>Client</th><th>Contact Name</th><th>Contact Email</th><th>Contact Phone</th><th>Actions</th></tr></thead>
      <tbody>
<?php
  while($client = array_shift($client_deck)) {
    printf("\n<tr><td><a href='%s'>%s</a></td><td>%s</td><td>%s</td><td>%s</td><td><a href='%s' class='submitdelete'>Delete</a></td></tr>"
           , admin_url("admin.php?page=ad-engine-client-edit&id=" . $client['co_id'])
           , $client['name']
           , $client['ct_name']
           , $client['ct_email']
           , $client['ct_phone']
           , admin_url("admin.php?page=ad-engine-client-menu&action=client_delete&id=" . $client['co_id'])
           );
  }
?>
  </tbody></table></div>
<?php
}

function ad_engine_category_edit_form() {
  global $_GET;
  $jads = ad_engine_get_counts();
  $ad_group = get_option('ad_engine_cat_' . $_GET['id']);
?>
  <form method="POST" action="<?php echo admin_url("admin.php?page=ad-engine-category-menu")?>" id="ad_engine_form">
    <dl>
      <dt>Ad Group</dt>
      <dd><input  name="name"  type="input" size="50" value="<?php echo $ad_group['name']?>"></dd>
    </dl>
    <p>
      <input id="<?php echo $ad_group['carousel']; ?>" name="cat_carousel" type="checkbox" <?php echo ($ad_group['cat_carousel'] == "on") ? 'checked="checked" ': '' ;  ?> />
      <label for="carousel"><?php _e('Ad Carousel'); ?></label>
    </p>
    <input type="hidden" name="id" value="<?php echo $ad_group['id'] ?>">
    <input name="save_category" type="submit" value="Update Ad Group">
    <input name="cancel" type="submit" value="Cancel">
  </form>
<?php
}

function ad_engine_category_new_form() {
  $jads = ad_engine_get_counts();
?>
  <form method="POST" action="<?php echo admin_url("admin.php?page=ad-engine-category-menu")?> ">
    <dl>
      <dt>Ad Group</dt>
      <dd><input name="name" type="input" size="50"></dd>
    </dl>
    <p>
      <input id="<?php echo $ad_group['carousel']; ?>" name="cat_carousel" type="checkbox" <?php echo ($ad_group['cat_carousel'] == "on") ? 'checked="checked" ': '' ;  ?> />
      <label for="carousel"><?php _e('Ad Carousel'); ?></label>
    </p>
    <input name="new_category" type="submit" value="Create Ad Group">
    <input name="cancel" type="submit" value="Cancel">
  </form>
<?php
}

function ad_engine_category_form() {
	global $_POST;
	$jads = ad_engine_get_counts();
	$cat_deck = ad_engine_get_cat_deck();

	$output = "<div class=\"wrap\"><h2>";
	$output .= __("Ad Groups","Ad Groups");
	$output .= "<a class=\"button add-new-h2\" href=\"" . admin_url("admin.php?page=ad-engine-category-new") ."\">Add New</a>";
	$output .= "</h2><p>";
	$output .= "\n<table class='widefat post fixed'>\n<thead><tr><th>Ad Group Name</th><th>Action</th></tr></thead><tbody>\n";

	$max = count($cat_deck);
	$iter = 0;
	while($category = array_shift($cat_deck)) {
		$output .= sprintf("\n<tr><td><a href='%s'>%s</a></td><td><a href='%s' class='submitdelete'>Delete</a></td></tr>"
		                  , admin_url("admin.php?page=ad-engine-category-edit&amp;id=" . $category['id']), $category['name']
		                  , admin_url("admin.php?page=ad-engine-category-menu&amp;action=cat_delete&amp;id=" . $category['id'])
		                  );
	}
	$output .= '</tbody></table>';
	echo $output;
}


function ad_engine_menu() {
  $capability = 'moderate_comments';
  add_menu_page( 'Advertisements' , 'Ad Engine' , $capability , 'ad-engine-menu' , 'ad_engine_form');
  add_submenu_page( 'ad-engine-menu' ,'New Ad' ,'New Ad' , $capability , 'ad-engine-new' , 'ad_engine_new_form');
  add_submenu_page( 'ad-engine-menu' ,'Edit Ad' ,'Edit Ad' , $capability , 'ad-engine-edit' , 'ad_engine_edit_form');

  add_submenu_page( 'ad-engine-menu' ,'Clients' ,'Clients' , $capability , 'ad-engine-client-menu' , 'ad_engine_client_form');
  add_submenu_page( 'ad-engine-client-menu' ,'New Client' ,'New Client' , $capability , 'ad-engine-client-new' , 'ad_engine_client_new_form');
  add_submenu_page( 'ad-engine-client-menu' ,'Edit Client' ,'Edit Client' , $capability , 'ad-engine-client-edit' , 'ad_engine_client_edit_form');

  add_submenu_page( 'ad-engine-menu' ,'Ad Groups' ,'Ad Groups' , $capability , 'ad-engine-category-menu' , 'ad_engine_category_form');
  add_submenu_page( 'ad-engine-category-menu' ,'New Ad Group' ,'New Ad Group' , $capability , 'ad-engine-category-new' , 'ad_engine_category_new_form');
  add_submenu_page( 'ad-engine-category-menu' ,'Edit Ad Group' ,'Edit Ad Group' , $capability , 'ad-engine-category-edit' , 'ad_engine_category_edit_form');
}
if (is_admin()) {
  add_action('admin_menu', 'ad_engine_menu');
}

function ad_update_count($id, $type) {
	global $wpdb;
	$dstamp = date("Y-m-d");
	$sql = 'select count from ad_engine where dstamp="' . $dstamp . '" AND type='. $type . ' AND id=' . intval($id);
	$count = $wpdb->get_var($wpdb->prepare($sql));
	if(isset($count))
		$sql = 'update ad_engine set count = ' . ($count + 1) . ' where dstamp="' . $dstamp . '" AND type=' . $type . ' AND id=' . intval($id);
	else
		$sql = 'INSERT INTO ad_engine set count = 1, dstamp="' . $dstamp . '", type=' . $type . ', id=' . intval($id);
	$wpdb->query($wpdb->prepare($sql));
}

function ad_engine_init() {
	global $wpdb;
	if (!is_admin()) {
		wp_enqueue_script('jquery');
	}

	$jads = ad_engine_get_counts();

	$table = $wpdb->get_var('SHOW TABLES LIKE "ad_engine"');
	if($table == '') {
		$wpdb->query('CREATE TABLE ad_engine ( id INT, dstamp DATE, count INT, type INT)');
	}

	if(isset($_GET['cancel']) || isset($_POST['cancel'])) {
		return;
	}
	else if(isset($_GET['ae_get_csv'])) {
		header('Content-type: text/plain');
		printf('"Date","Impressions","Clicks"');
		$ad_metrics = $wpdb->get_results($wpdb->prepare('SELECT * from ad_engine where id=' . $_GET['ae_get_csv'] . ' order by dstamp,type limit 1000;'));
		$dstamp = 0;
		foreach($ad_metrics as $daily) {
			if($dstamp != $daily->dstamp) {
				if($type == 2)
					echo ',0';
				$type = 1;
				$dstamp = $daily->dstamp;
				echo "\n\"" . $dstamp . '"';
				
			}
			if($daily->type == $type) {
				$type++;
				echo ",\"" . $daily->count . '"';
			}
			else
				echo "\"0\",\"" . $daily->count . '"';
		}
		if($type == 2)
			echo ',"0"';
		die();
	}
	else if(isset($_GET['ae_ad_clicked'])) {
		ad_update_count($_GET['ae_ad_clicked'], 2);
		die();
	}
	else if(isset($_GET['ae_get_data'])) {
		echo json_encode(ad_engine_ad_data($_GET['ae_get_data']));
		die();
	}
	else if(isset($_GET['ae_redirect'])) {
		$tmp = get_option('ad_engine_' . intval($_GET['ae_redirect']));
		$tmp['ad_id']     = intval($_GET['ae_redirect']);
		if(isset($tmp['ad_clicks']))
			$tmp['ad_clicks']++;
		else
		 $tmp['ad_clicks'] = 1;
		update_option('ad_engine_' . $tmp['ad_id'] , $tmp);
		header('Location: ' . $tmp['ad_link']);
		die();
	}
	// Client Processing
	else if(isset($_POST['save_ad']) || isset($_POST['new_ad'])) {
		$nClient = Array();
		$nClient['ad_name']   = esc_html(stripslashes($_POST['ad_name']), ENT_QUOTES);
		$nClient['ad_cat']    = esc_html(stripslashes($_POST['ad_cat']), ENT_QUOTES);
		$nClient['ad_client'] = esc_html(stripslashes($_POST['ad_client']), ENT_QUOTES);
		$nClient['ad_imp']    = esc_html(stripslashes($_POST['ad_imp']), ENT_QUOTES);
		$nClient['ad_shown']  = esc_html(stripslashes($_POST['ad_shown']), ENT_QUOTES);
		$nClient['ad_clicks'] = esc_html(stripslashes($_POST['ad_clicks']), ENT_QUOTES);
		$nClient['ad_text']   = esc_html(stripslashes($_POST['ad_text']), ENT_QUOTES);
		$nClient['ad_link']   = esc_html(stripslashes($_POST['ad_link']), ENT_QUOTES);
		if(isset($_POST['save_ad'])) {
			$nClient['ad_id'] = $_POST['ad_id'];
		}
		else {
			$jads['ads']++;
			$nClient['ad_id'] = $jads['ads'];
		}
		update_option('ad_engine_' . $nClient['ad_id'] , $nClient);
	}
	else if(isset($_GET['action']) && ($_GET['action'] == "ad_delete")) {
		delete_option('ad_engine_' . $_GET["id"]);
	}
	// Client Processing
	else if(isset($_POST['update_client'])) {
		$nClient = Array();
		$nClient['name']  = esc_html(stripslashes($_POST['name']), ENT_QUOTES);
		$nClient['ct_name']  = esc_html(stripslashes($_POST['ct_name']), ENT_QUOTES);
		$nClient['ct_email'] = esc_html(stripslashes($_POST['ct_email']), ENT_QUOTES);
		$nClient['ct_phone'] = esc_html(stripslashes($_POST['ct_phone']), ENT_QUOTES);
		$nClient['co_id']    = $_POST['co_id'];
		$nClient['id']    = $_POST['co_id'];
		update_option('ad_engine_client_' . $nClient['co_id'] , $nClient, ENT_QUOTES);
	}
	else if(isset($_POST['new_client'])) {
		$nClient = Array();
		$nClient['name']  = esc_html(stripslashes($_POST['name']), ENT_QUOTES);
		$nClient['ct_name']  = esc_html(stripslashes($_POST['ct_name']), ENT_QUOTES);
		$nClient['ct_email'] = esc_html(stripslashes($_POST['ct_email']), ENT_QUOTES);
		$nClient['ct_phone'] = esc_html(stripslashes($_POST['ct_phone']), ENT_QUOTES);
		$jads['clients']++;
		$nClient['co_id'] = $jads['clients'];
		$nClient['id'] = $jads['clients'];
		update_option('ad_engine_client_' . $nClient['id'] , $nClient);
	}
	else if(isset($_GET['action']) && ($_GET['action'] == "client_delete")) {
		delete_option('ad_engine_client_' . $_GET['id']);
	}
	// Ad Group Processing
	else if(isset($_POST['new_category']) || isset($_POST['save_category'])) {
		$nClient = Array();
		$nClient['name'] = esc_html(stripslashes($_POST['name']), ENT_QUOTES);
		$nClient['cat_carousel']   = $_POST['cat_carousel'];
		if(isset($_POST['new_category'])) {
			$jads['categories']++;
			$nClient['id']   = $jads['categories'];
		}
		else {
			$nClient['id']   = $_POST['id'];
		}
		update_option('ad_engine_cat_' . $nClient['id'] , $nClient);
	}
	else if(isset($_GET['action']) && ($_GET['action'] == "cat_delete")) {
		delete_option('ad_engine_cat_' . $_GET['id'] );
	}

	if(isset($_FILES['ad_image'])) {
		$wud = wp_upload_dir();
		$dir = $wud['basedir'] . "/ad_engine";

		if(! is_dir($dir))
			mkdir($dir, 755, true);

		$fname = sprintf("%s/%d-ad.png", $dir, $nClient['ad_id']);
		move_uploaded_file($_FILES['ad_image']['tmp_name'], $fname);
	}
	update_option('ad_engine', $jads);
}
add_action('init', 'ad_engine_init', 1);
function ad_engine_admin_style() {
?>
<style type="text/css">
#wpbody-content tr:nth-child(odd) {
    background: #eee;
}


</style>
<?php
}
add_action('admin_head', 'ad_engine_admin_style', 1);

function ad_engine_get_ads($category) {
	global $ad_engine_ad_deck;
	$jads = ad_engine_get_counts();
	$iter = 1;

	if(count($ad_engine_ad_deck[$category]) == 0) {
		while($iter <= $jads['ads']) {
			$tmp = get_option('ad_engine_' . $iter);

			if(($category == $tmp['ad_cat']) && 
			  (($tmp['ad_shown'] < $tmp['ad_imp']) || ($tmp['ad_imp'] == -1))) { 
				$ad_engine_ad_deck[$category][] = $tmp;
			}
			$iter++;
		}
		//uasort($ad_engine_ad_deck, ad_engine_ad_cmp);
		shuffle($ad_engine_ad_deck[$category]);
	}
	return $ad_engine_ad_deck;
}

class ad_engine extends WP_Widget {
    /** constructor */
    function ad_engine() {
        parent::WP_Widget(false, $name = 'Ad Engine');
        add_shortcode('ad-engine', array(&$this, 'shortcode'));
    }

	function shortcode($atts) {
		extract(shortcode_atts(array(
			'ad_group' => 1,
			'align' => 'left',
		), $atts));
		global $ad_slot;
		$title = "Advertisement";

		$jads = ad_engine_get_counts();
		$iter = 0;
		while($iter <= $jads['categories']) {
			$tmp = get_option('ad_engine_cat_' . $iter);
			if(isset($tmp['id']) &&
			  (strtolower($tmp['name']) == strtolower($ad_group))
			) {
			$ag = $tmp;
			}
			$iter++;
		}
		$ag_id = $ag['id'];

		if($atts['align'] == 'left')
			$class = 'alignleft';
		else if($atts['align'] == 'right')
			$class = 'alignright';
		else
			$class = 'alignleft';

		$output = " <div id='ad_engine_slot_" . $ad_slot . "' class='" . $class . "'>
		<h3 class='ad_engine_title'>" . $title . "</h3>" .  $this->show_ad($ag_id) . "</div>";
		return $output;
	}
	function show_ad($category) {
		global $ad_engine_ad_deck;
		global $ad_slot;
		$output = '';

		// This function sets global variable ad_engine_ad_deck 
		ad_engine_get_ads($category);
		
		// Undecided on whether to nofollow this link or not.
		$ag = get_option('ad_engine_cat_' . $category);
		if($ag['cat_carousel'] == "on") {
			$r_ad = $ad_engine_ad_deck[$category][0];
			for($iter = 0 ; $iter < count($ad_engine_ad_deck[$category]); $iter++) {
				$ad_list[] = $ad_engine_ad_deck[$category][$iter]['ad_id'];
			}
			$javascript = "<script type='text/javascript'>
            ad_engine_slot[$ad_slot] = " . json_encode($ad_list) . ";
            ad_engine_count[$ad_slot] = 1;
            setInterval(function() { rotate_ad($ad_slot) }, 5000);
            </script>";
		}
		else {
			$r_ad = array_shift($ad_engine_ad_deck[$category]);
			$javascript = '<!-- No JavaScript -->';
		}
		$ad_data = ad_engine_ad_data($r_ad['ad_id']);
		$fname = $ad_data['img'];
		$text = $ad_data['text'];
		if(isset($fname) && $fname != '') {
			$img = "<img src='" . $fname . "' />";
		}
		if(isset($text) && $text != '') {
			$caption = "<span class='ad_caption'>" . $text . "</span>";
		}

                /* reference <a id='ad_engine_ad_%d' target='_blank' href='%s?ae_redirect=%d'>%s%s</a> */
		$output .= sprintf("%s<ul class='ad'>
                <li class='image'>
                  <a id='ad_engine_ad_%d' target='_blank' href='%s' onclick='jQuery.get(\"%s/?ae_ad_clicked=%s\")'>%s%s</a>
                </li>
              </ul>"
		     , $javascript
		     , $ad_slot
		     , $r_ad['ad_link']
		     , site_url()
		     , $r_ad['ad_id']
		     , $img
		     , $caption
		    );
		$ad_slot++;
		return $output;
	}

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract($args);

        $title = "Advertisement";
        echo $before_widget;
        echo $before_title . $title . $after_title;

        echo $this->show_ad($instance['category']);
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    /** @see WP_Widget::form */
	function form($instance) {
		global $_GET;
		$cat_deck = ad_engine_get_cat_deck();

		$category = esc_attr($instance['category']);
		echo "<dl>" . ad_engine_form_dropdown('Ad Group' , $this->get_field_id('category'), $cat_deck, $category, $this->get_field_name('category')) . "</dl>";
	}

} // class ad_engine

add_action('widgets_init', create_function('', 'return register_widget("ad_engine");'));

function ad_engine_carousel() {
?>
  <script type='text/javascript'>
    ad_engine_slot = [];
    ad_engine_count = [];

    function ad_engine_get_data(ad_id, slot) {
      jQuery.getJSON("/?ae_get_data=" + ad_id,
function(data) {
  jQuery("#ad_engine_ad_" + slot + " img").attr("src", data.img);
  jQuery("#ad_engine_ad_" + slot + " span").text(data.text);
  jQuery("#ad_engine_ad_" + slot).attr("href", "/?ae_redirect=" + ad_id);
}
);
    }
    function rotate_ad(slot) {
      ad_engine_get_data(ad_engine_slot[slot][ad_engine_count[slot]], slot);
      ad_engine_count[slot]++;
      if(ad_engine_count[slot] == ad_engine_slot[slot].length) {
        ad_engine_count[slot] = 0;
      }
    }
  </script>
<?php
}
add_action('wp_head', 'ad_engine_carousel');

?>
