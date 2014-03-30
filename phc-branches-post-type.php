<?php
/*
Plugin Name: Branches Post Type with ImageMapper
Plugin URI: http://www.phantasmacode.com
Description: Creates Branches Post Type with ImageMapper.
Version: 1.0.0
Author: Joko Wandiro
Author URI: http://www.phantasmacode.com
*/
define('PCH_BPT_WIM_PATH_DIR', plugin_dir_path(__FILE__));
define('PCH_BPT_WIM_PATH_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );
define('PCH_BPT_WIM_NAME', "Branches Post Type with ImageMapper");
define("PCH_BPT_WIM_VERSION", "1.0");
define('PCH_BPT_WIM_IDENTIFIER', "pch_bpt_wim");
define("PCH_BPT_WIM_POST_TYPE", "branches");
define("PCH_BPT_WIM_PAGE_TITLE_IMAGE_MAP", "Image Map Settings");

// Set up the branches post types.
add_action('init', 'phc_branches_post_type');

// Registers post types. 
function phc_branches_post_type(){
	// Set up the arguments for the 'music_album' post type.
	$album_args= array(
	'public'=>true,
	'query_var'=>'branches',
	'rewrite'=>array(
		'slug'=>'branches',
		'with_front'=>false,
	),
	'supports'=>array(
		'title',
		'custom-fields'
	),
	'menu_icon'=>plugins_url("img/branches.png", __FILE__),
	'labels'=>array(
		'name'=>'Branches',
		'singular_name'=>'Branch',
		'add_new'=>'Add New Branch',
		'add_new_item'=>'Add New Branch',
		'edit_item'=>'Edit Branch',
		'new_item'=>'New Branch',
		'view_item'=>'View Branch',
		'search_items'=>'Search Branches',
		'not_found'=>'No Branches Found',
		'not_found_in_trash'=>'No Branches Found In Trash'
	),
	);
	
	// Register the music album post type. 
	register_post_type('branches', $album_args);
}

// Set up the taxonomies. 
add_action('init', 'phc_branches_register_taxonomies');

// Registers taxonomies.
function phc_branches_register_taxonomies() {
	// Set up the provinces taxonomy arguments
	$artist_args = array(
	'hierarchical'=>false,
	'query_var'=>'provinces',
	'show_tagcloud'=>true,
	'rewrite'=>array(
		'slug'=>'provinces',
		'with_front'=>false
	),
	'labels'=>array(
		'name'=>'Provinces',
		'singular_name'=>'Province',
		'edit_item'=>'Edit Province',
		'update_item'=>'Update Province',
		'add_new_item'=>'Add New Province',
		'new_item_name'=>'New Province Name',
		'all_items'=>'All Provinces',
		'search_items'=>'Search Provinces',
		'popular_items'=>'Popular Provinces',
		'separate_items_with_commas'=>'Separate provinces with commas',
		'add_or_remove_items'=>'Add or remove provinces',
		'choose_from_most_used'=>'Choose from the most popular provinces',
	),
	);
	// Register the provinces taxonomy
	register_taxonomy('provinces', array('branches'), $artist_args);
}

// Start Add Meta Box - Custom Field
add_action("admin_init", "branches_add_meta");
function branches_add_meta(){
	add_meta_box("branch-meta", "Branch Info", "branch_meta_options", "branches", "normal", "high");
}

function branch_meta_options(){
	global $post;
	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
		return $post_id;
	}
	
	$custom= get_post_custom($post->ID);
	$address= $custom["address"][0];
	$telephone= $custom["telephone"][0];
	$fax= $custom["fax"][0];
	$email= $custom["email"][0];
	
	wp_enqueue_style('meta_box_css', plugins_url("css/meta-box.css", __FILE__), FALSE);
?>
<div class="branch-extras meta-box-wrapper">
	<div>
	<label><?php _e("Address", "phc_branches_post_type"); ?>:</label>
	<textarea name="address" cols="10" rows="10"><?php echo esc_attr($address); ?></textarea>
	</div>
	<div>
	<label><?php _e("Telephone", "phc_branches_post_type"); ?>: </label>
	<input type="text" name="telephone" value="<?php echo esc_attr($telephone); ?>" />
	</div>
	<div>
	<label><?php _e("Fax", "phc_branches_post_type"); ?>: </label>
	<input type="text" name="fax" value="<?php echo esc_attr($fax); ?>" />
	</div>
	<div>
	<label><?php _e("Email", "phc_branches_post_type"); ?>: </label>
	<input type="text" name="email" value="<?php echo esc_attr($email); ?>" />
	</div>
</div>
<?php
}
// End Add Meta Box - Custom Field

// Start Save Post
add_action('save_post', 'branch_save_extras');
function branch_save_extras(){
	global $post;
	
	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
		return $post_id;
	}else{
		// Validate Data
		
		// Save Data
		update_post_meta($post->ID, "address", esc_textarea($_POST['address']));
		update_post_meta($post->ID, "telephone", sanitize_text_field($_POST['telephone']));
		update_post_meta($post->ID, "fax", sanitize_text_field($_POST['fax']));
		update_post_meta($post->ID, "email", sanitize_email($_POST['email']));
	}
}
// End Save Post

// Start Modification Columns for Products
// Make Sortable Columns
add_filter('manage_edit-branches_sortable_columns', 'branches_sortable_columns');
function branches_sortable_columns($columns){
	$columns= array(
	"title"=>"title",
	"address"=>"address",
	"telephone"=>"telephone",
	"fax"=>"fax",
	"email"=>"email",
	);	
	
	return $columns;
}

function branches_column_orderby($vars){
	$pattern_fields= "#address|telephone|fax|email#";
	if( isset($vars['orderby']) && preg_match($pattern_fields, $vars['orderby']) ){
		$sort= array('orderby'=>'meta_value', 'meta_key'=>'address');
		$vars = array_merge($vars, $sort);
	}
	
    return $vars;
}
add_filter('request', 'branches_column_orderby');

// Modification Columns
add_filter("manage_edit-branches_columns", "branches_edit_columns");
function branches_edit_columns($columns){
	$columns= array(
	"cb"=>"<input type=\"checkbox\" />",
	"title"=>"Title",
	"address"=>"Address",
	"telephone"=>"Telephone",
	"fax"=>"Fax",
	"email"=>"Email",
	);
	
	return $columns;
}

// Set Value for Custom Column
add_action("manage_branches_posts_custom_column", 
"branches_custom_columns");
function branches_custom_columns($columns){
	global $post;
	$custom= get_post_custom();
	switch( $columns ){
		case "title":
			echo $custom["title"][0];
			break;
		case "address":
			echo nl2br($custom["address"][0]);
			break;
		case "telephone":
			echo $custom["telephone"][0];
			break;
		case "fax":
			echo $custom["fax"][0];
			break;
		case "email":
			echo $custom["email"][0];
			break;
	}
}
// End Modification Columns for Products 

add_action('admin_menu', 'phc_branches_post_type_create_menu');
function phc_branches_post_type_create_menu(){
	$capability= "manage_options";
	$menu_slug= "branches_imagemap_settings";
	$function= "phc_branches_post_type_setting_imagemapper";
	add_submenu_page('edit.php?post_type=' . PCH_BPT_WIM_POST_TYPE, PCH_BPT_WIM_PAGE_TITLE_IMAGE_MAP, 
	PCH_BPT_WIM_PAGE_TITLE_IMAGE_MAP, $capability, $menu_slug, $function);
	
	add_action('admin_init', 'phc_branches_post_type_register_settings');
}

function phc_branches_post_type_register_settings(){
	register_setting('pch_bpt_wim_settings_group', 'pch_bpt_wim_image_map');
	register_setting('pch_bpt_wim_settings_group', 'pch_bpt_wim_province_name');
	register_setting('pch_bpt_wim_settings_group', 'pch_bpt_wim_description_all');
}

function phc_branches_post_type_setting_imagemapper(){
	wp_enqueue_style('meta_box_css', plugins_url("css/meta-box.css", __FILE__), FALSE);
	$args= array(
	'hide_empty'=>FALSE
	);
	$provinces= get_terms('provinces', $args);
	$image_map_id= get_option('pch_bpt_wim_image_map');
	$province_values= get_option('pch_bpt_wim_province_name');
	if( !empty($image_map_id) ){
		$image_attachment= wp_get_attachment_image_src($image_map_id, 'full');
		$image_attachment= ($image_attachment) ? $image_attachment : array('');
	}
?>
	<div class="wrap" id="<?php echo PCH_BPT_WIM_IDENTIFIER; ?>">
	<?php screen_icon('generic'); ?>
	<h2><?php _e("Image Map Settings"); ?></h2>
	<form method="POST" action="options.php">
	<?php settings_fields('pch_bpt_wim_settings_group'); ?>
	<div class="alert alert-info" id="shortcode-desc">
	shortcode <strong>[branches_with_image_map]</strong> add it to your post / page.
	</div>
	<table class="form-table">
    <tr valign="top">
	    <th scope="row">
		<h3><?php _e("Image Map"); ?></h3>
		<div>
		<input id="upload_image_button" type="button" value="Select Image" class="upload_image_button button-secondary" />
		</div>
		</th>
   	</tr>
    <tr valign="top">
	    <th scope="row" colspan="2">
		<input type="hidden" name="pch_bpt_wim_image_map" value="<?php echo get_option('pch_bpt_wim_image_map'); ?>" />
		<img id="pch_bpt_wim_image_map_src" src="<?php echo $image_attachment[0]; ?>">
		</th>
   	</tr>
	<tr valign="top">
	    <th scope="row" colspan="2">
		<h3><?php _e("Set Description for All Province"); ?><span>( <?php _e("If there's no selected province"); ?> )
		</span></h3>
		</th>
   	</tr>
    <tr valign="top">
	    <th scope="row" colspan="2">
		<textarea name="pch_bpt_wim_description_all" cols="10" rows="10"><?php echo get_option('pch_bpt_wim_description_all'); ?></textarea>
		</th>
   	</tr>	
    <tr valign="top">
	    <th scope="row" colspan="2">
		<h3><?php _e("Set Coordinates"); ?></h3>
		<div class="alert alert-info">
		<div><?php _e("Number of Coordinates based on Provinces."); ?></div>
		<div><?php _e("You can set multiple coordinates for single province ( values separated by hash sign - # )."); ?></div>
		<div>
		<?php _e("What's coordinates and how to get it. 
		You can using <strong>image map editor</strong> and copy / paste it."); ?>
		</div>
		</div>
		</th>
   	</tr>
	<?php
	foreach( $provinces as $province ){
		$term_id= $province->term_id;
		$value= ( isset($province_values[$term_id]) ) ? $province_values[$term_id] : "";
	?>
	<tr valign="top">
	    <th scope="row">
		<?php echo $province->name; ?>
		</th>
		</th>
	    <td>
		<textarea name="pch_bpt_wim_province_name[<?php echo $term_id; ?>]" cols="10" rows="10"><?php echo $value; ?></textarea>		
	   	</td>
   	</tr>
	<?php
	}
	?>
	<tr valign="top">
		<td>
		<input type="submit" name="save" value="Save" class="button-primary" />
		</td>
	</tr>
	</table>
	</form>
	</div>
<?php
}

add_action('admin_print_scripts', 'pch_bpt_wim_admin_scripts');
function pch_bpt_wim_admin_scripts(){
	wp_enqueue_style('meta_box_css', plugins_url("css/meta-box.css", __FILE__), FALSE);
}

add_action('admin_print_scripts-branches_page_branches_imagemap_settings', 'pch_bpt_wim_media_admin_scripts');
function pch_bpt_wim_media_admin_scripts(){
	wp_enqueue_media();
	wp_enqueue_script('pch_meta_image_js', plugins_url("js/meta-image.js", __FILE__));
}

add_shortcode('branches_with_image_map', 'pch_bpt_wim_branches_with_image_map');
function pch_bpt_wim_branches_with_image_map(){
	$html= pch_bpt_wim_branches_with_image_map_html();
	
	return $html;
}

function pch_bpt_wim_branches_with_image_map_html(){
	global $post;
	
	$args= array(
	'hide_empty'=>FALSE
	);
	$provinces= get_terms('provinces', $args);
	$image_map_id= get_option('pch_bpt_wim_image_map');
	$province_title= get_option('pch_bpt_wim_description_all');
	$province_values= get_option('pch_bpt_wim_province_name');
	if( !empty($image_map_id) ){
		$image_attachment= wp_get_attachment_image_src($image_map_id, 'full');
		$image_attachment= ($image_attachment) ? $image_attachment : array('', '', '');
	}
	
	$terms= array();
	foreach( $provinces as $province ){
		$terms[]= $province->slug;
	}
	
	$args= array(
	'post_type'=>'branches',
	'tax_query'=>array(
		array(
		'taxonomy'=>'provinces',
		'field'=>'slug',
		'terms'=>$terms
		),
	)
	);
	$posts= new WP_Query($args);
	
	$post_html= '';
	if( $posts->have_posts() ){
		$post_html= '<ul>';
		$ct= 0;
		$li_class_now= "pch-bpt-wim-bg-grey";
		while( $posts->have_posts() ){
			$posts->the_post();
			if( $ct == 2 ){
				if( $li_class_now == "pch-bpt-wim-bg-grey" ){
					$li_class_now=  "pch-bpt-wim-bg-darkgrey";
				}else{
					$li_class_now= "pch-bpt-wim-bg-grey";
				}
				$ct= 0;
			}
			$custom= get_post_custom($post->ID);
			$address= nl2br($custom["address"][0]);
			$telephone= $custom["telephone"][0];
			$fax= $custom["fax"][0];
			$email= $custom["email"][0];
			$post_html.= '<li class="' . $li_class_now . '">';
			$post_html.= '<div class="pch-bpt-wim-title"><a href="' . get_permalink() . '">' . get_the_title(). '</a></div>';
			$post_html.= '<div class="pch-bpt-wim-address">' . $address . '</div>';
			$post_html.= '<div class="pch-bpt-wim-telephone">' . $telephone . '</div>';
			$post_html.= '<div class="pch-bpt-wim-fax">' . $fax . '</div>';
			$post_html.= '<div class="pch-bpt-wim-email">' . $email . '</div>';	
			$post_html.= '</li>';
			$ct++;
		}
		$post_html.= '</ul>';
	}else{
		// no posts found
		$post_html= _e("No Posts Found");
	}
	/* Restore original Post Data */
	wp_reset_postdata();
	
	$image_map= wp_get_attachment_image($image_map_id, 'full');
	$image_map_filename= basename($image_attachment[0], ".png");
	$map_areas= "";
	foreach( $provinces as $province ){
		$term_id= $province->term_id;
		$value= ( isset($province_values[$term_id]) ) ? $province_values[$term_id] : "";
		$arr_province_value= explode("#", $value);
		foreach( $arr_province_value as $area ){
			if( ! empty($area) ){
				$map_areas.= '<area shape="poly" province="' . $province->slug . '" coords="' . $area . '" href="#" />';
			}
		}
	}

	$html= <<<PHC
	<script type="text/javascript">

	jQuery(document).ready(function($) {
		function state_change(data) {
		}
		$('img').mapster({
			noHrefIsMask: false,
			isMask: true,
			onStateChange: state_change,
			fillColor: '414042',
			fillOpacity: 1,
			mapKey: "province",
			stroke:true,
			strokeWidth: 2,		
			strokeColor: '414042',
	        isSelectable: true,
	        singleSelect: true,
			render_select: {
				fillColor: '414042',
				fillOpacity: 1
			},
            onClick: function (e) {
//				console.log(e.key);
//				console.log(e.selected);
//				console.log(e.listTarget);
				selected= e.selected;
				if( selected ){
					Ajax.get_data_based_province(e.key);
				}else{
					Ajax.get_data_based_province("");
				}
            },
		});
		
		var resizeTime = 100;     // total duration of the resize effect, 0 is instant
		var resizeDelay = 100;    // time to wait before checking the window size again
		                          // the shorter the time, the more reactive it will be.
		                          // short or 0 times could cause problems with old browsers.
		                          
		// Resize the map to fit within the boundaries provided

		function resize(maxWidth,maxHeight) {
		     var image =  $('img'),
		        imgWidth = image.width(),
		        imgHeight = image.height(),
		        newWidth=0,
		        newHeight=0;

		    if (imgWidth/maxWidth>imgHeight/maxHeight) {
		        newWidth = maxWidth;
		    } else {
		        newHeight = maxHeight;
		    }
		    image.mapster('resize',newWidth,newHeight,resizeTime);   
		}

		// Track window resizing events, but only actually call the map resize when the
		// window isn't being resized any more

		function onWindowResize() {
		    
		    var curWidth = $(window).width(),
		        curHeight = $(window).height(),
		        checking=false;
		    if (checking) {
		        return;
		            }
		    checking = true;
		    window.setTimeout(function() {
		        var newWidth = $(window).width(),
		           newHeight = $(window).height();
		        if (newWidth === curWidth &&
		            newHeight === curHeight) {
		            resize(newWidth,newHeight); 
		        }
		        checking=false;
		    },resizeDelay );
		}

		$(window).bind('resize',onWindowResize);
	});
	</script>
	<div>
	<img width="$image_attachment[1]" height="$image_attachment[2]" src="$image_attachment[0]" 
	class="attachment-full" alt="$image_map_filename" usemap="#Map">
	<map name="Map" id="Map">
	$map_areas
	</div>
	<div id="pch-bpt-wim-post-html">
	<div class="pch-bpt-wim-post-html-title">
	<h3>$province_title</h3>
	</div>
	<div class="pch-bpt-wim-post-html-content">
	$post_html
	</div>
	</div>
PHC;

	return $html;
}

// Load Ajax Script
require_once(PCH_BPT_WIM_PATH_DIR . "/inc/phc-branches-post-type-ajax.php");
?>