<?php
// Enqueue the script, in the footer
add_action('wp', 'phc_branches_post_type_js');
function phc_branches_post_type_js() {
    global $post;
    $pattern= get_shortcode_regex();

    if( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
        && array_key_exists( 2, $matches )
        && in_array( 'branches_with_image_map', $matches[2] ) ){
		// Enqueue the Imagemapster Script
		$imagemapster_dir= plugins_url("phc-branches-post-type/js/imagemapster/");
		wp_enqueue_style('meta_box_css', plugins_url("phc-branches-post-type/css/meta-box.css"));
		wp_enqueue_script('pch_imagemapster_when_js', $imagemapster_dir . "redist/when.js");
		wp_enqueue_script('pch_imagemapster_core_js', $imagemapster_dir . "core.js");
		wp_enqueue_script('pch_imagemapster_graphics_js', $imagemapster_dir . "graphics.js");
		wp_enqueue_script('pch_imagemapster_mapimage_js', $imagemapster_dir . "mapimage.js");
		wp_enqueue_script('pch_imagemapster_mapdata_js', $imagemapster_dir . "mapdata.js");
		wp_enqueue_script('pch_imagemapster_areadata_js', $imagemapster_dir . "areadata.js");
		wp_enqueue_script('pch_imagemapster_areacorners_js', $imagemapster_dir . "areacorners.js");
		wp_enqueue_script('pch_imagemapster_scale_js', $imagemapster_dir . "scale.js");
		wp_enqueue_script('pch_imagemapster_tooltip_js', $imagemapster_dir . "tooltip.js");
		// Ajax Support
		wp_enqueue_script('jquery_blockUI', plugins_url("phc-branches-post-type/js/jquery.blockUI.js"));
		wp_enqueue_script('phc_branches_post_type', plugins_url("phc-branches-post-type/js/phc-branches-post-type.js"));
	
	// Get current page protocol
	$protocol = isset( $_SERVER['HTTPS']) ? 'https://' : 'http://';
	// Output admin-ajax.php URL with same protocol as current page
	$params = array(
	'ajaxurl'=>admin_url('admin-ajax.php', $protocol),
	'loading_text'=>'<h1>Loading...</h1>',
	'feedback_selector'=>'#pch-bpt-wim-post-html',
	);
	wp_localize_script('phc_branches_post_type', 'phc_branches_post_type_params', $params);
    }
}

// Ajax handler
add_action('wp_ajax_phc_branches_post_type_ajax', 'phc_branches_post_type_ajax');
function phc_branches_post_type_ajax(){
	global $post;
	extract($_POST);
	
	// Setup Query
	$args= array(
	'hide_empty'=>FALSE,	
	);	
	if( !empty($province) ){
		$args['slug']= $province;
	}
	$provinces= get_terms('provinces', $args);
	if( count($provinces) > 1 ){
		$province_title= get_option('pch_bpt_wim_description_all');
		$terms= array();
		foreach( $provinces as $item ){
			$province[]= $item->slug;
		}
	}else{
		$province_title= $provinces[0]->description;
		$province= array($province);
	}

	$args= array(
	'post_type'=>'branches',
	'tax_query'=>array(
		array(
		'taxonomy'=>'provinces',
		'field'=>'slug',
		'terms'=>$province
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
	}
	else{
		// no posts found
		$post_html= '<div class="pch-bpt-wim-no-posts">' . __("No Posts Found") . '</div>';
	}
	// Restore original Post Data 
	wp_reset_postdata();
	
	$html= <<<PHC
	<div class="pch-bpt-wim-post-html-title">
	<h3>$province_title</h3>
	</div>
	<div class="pch-bpt-wim-post-html-content">
	$post_html
	</div>
PHC;
	
	$res= array('type'=>'html', 'html'=>$html);
	
	// Output Data
	echo json_encode($res);
	exit;
}
?>