<?php
/*
Plugin Name: PiwigoPress
Plugin URI: http://wordpress.org/extend/plugins/piwigopress/
Description: PiwigoPress from any open API Piwigo gallery, swiftly includes your photos in Posts/Pages and/or add randomized thumbnails and menus in your sidebar.
Version: 2.28
Author: Norbert Preining
Author URI: http://www.preining.info/
*/
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid Automatic install under Piwigo */
/*  Copyright 2009-2012  VDigital  (email : vpiwigo[at]gmail[dot]com)
    Copyright 2014-2015  Norbert Preining <norbert@preining.info>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if (!defined('PWGP_NAME')) define('PWGP_NAME','PiwigoPress');
if (!defined('PWGP_VERSION')) define('PWGP_VERSION','2.2.8');

load_plugin_textdomain('pwg', false, dirname (plugin_basename( __FILE__ ) ) . '/languages/');
add_shortcode('PiwigoPress', 'PiwigoPress_photoblog');

function PiwigoPress_photoblog($parm) {
	$default = array(
				'url' => '',
				'id' => 0, 		// image_id
				'size' => 'la', // Default large
				'desc' => 0, 	// Generate picture description
				'class' => '',	// Specific class
				'style' => '',	// Specific style
				'lnktype' => 'picture', // Default generated link 
				'opntype' => '_blank' // Default open type
		);
	$parm = array_change_key_case( $parm );
	extract( shortcode_atts( $default, $parm ) );
	$previous_url = get_option( 'PiwigoPress_previous_url' );
	if ($previous_url === false) {
		$previous_url = $_SERVER['HTTP_HOST'] . '/piwigo';
		add_option( 'PiwigoPress_previous_url', $previous_url );
	}
	if ($url == '') $url = $previous_url;
	# do not force http:// protocol, but allow for both
	# local (starting with one slash) urls as well as
	# remote but same protocol (starting with //)
	#if (substr($url,0,4)!='http') $url = 'http://' . $url;
	if (substr($url,-1)!='/') $url .= '/';
	if ($previous_url != $url) update_option( 'PiwigoPress_previous_url', $url );
	if ( !is_numeric($id) or $id < 1) {
		return "<!-- PiwigoPress 'id' attribute in error -->\n\n<br>" 
		. __('PiwigoPress shortcode attribute "id" is required and must be positive.','pwg') . "<br>\n\n" ;
	}
	$deriv = array ( 'sq' => 'square', 'th' => 'thumb', 'sm' => 'small', 'xs' => 'xsmall', '2s' => '2small', 
					 'me' => 'medium', 'la' => 'large', 'xl' => 'xlarge', 'xx' => 'xxlarge');
	if (!function_exists('pwg_get_contents')) include 'PiwigoPress_get.php';
	$response = pwg_get_contents( $url . 'ws.php?method=pwg.images.getInfo&format=php&image_id=' . $id);
	$thumbc = unserialize($response);
	if ($thumbc["stat"] == 'ok') {
		$picture = $thumbc["result"];
		//var_dump($picture);
		if (isset($picture['derivatives']['square']['url'])) {
			$cats = array_reverse($picture['categories']);
			$picture['tn_url'] = $picture['derivatives'][$deriv[$size]]['url'] ;
			$catlink = '/category/' ;
			if (isset($cats[0]['permalink'])) {
				$catlink .= $cats[0]['permalink'];
			} else {
				$catlink .= $cats[0]['id'];
			}
			$targetlink = $url . 'picture.php?/' . $picture['id'];
			if ( $lnktype == 'albumpicture' ) $targetlink .= $catlink ;
			$atag = '<a title="' . htmlspecialchars($picture['name']) . '" href="' 
				. $targetlink . '" target="' . $opntype . '">';
			if ( $lnktype == 'none' ) $atag = '';
			if ( $lnktype == 'album' ) {
				$atag = '<a title="' . htmlspecialchars($cats[0]['name']) . '" href="' 
				. $url . 'index.php?' . $catlink . '" target="' . $opntype . '">';
			}
			// value of alt tag: title + comment (if present)
			$alt = htmlspecialchars($picture['name']);
			if (isset($picture['comment'])) $alt .= ( ' -- ' . htmlspecialchars($picture['comment']) );
			$div = '<div class="PWGP_shortcode ' . $class . '">' . $atag. '<img  class="PWGP_photo" src="' . $picture['tn_url'] . '" alt="' . $alt . '"/>';
			if (isset( $picture['comment'] ) and $desc) { 
				$picture['comment'] = stripslashes(htmlspecialchars(strip_tags($picture['comment'])));
				$div .= '<blockquote class="PWGP_caption">' . $picture['comment'] . '</blockquote>'; 
			}
			if ( $lnktype != 'none' ) $div .= '</a>';
			$div .= "\n
			</div>";
		}
	}
	if ($style!='') $style = ' style="'.$style.'"';
	$str =  '<div id="Photo-' . $id . '-'. $size .'" class="PiwigoPress_photoblog"' . $style . '><!-- PiwigoPress Started -->';
	$str .=  $div . '</div><!-- PiwigoPress Ended -->';
	return $str;
}

class PiwigoPress extends WP_Widget
{
	function PiwigoPress(){
		$widget_ops = array('classname' => PWGP_NAME,
			'description' => __( "Adds a thumbnail and its link (to the picture) inside your blog sidebar.",'pwg') );
		$control_ops = array('width' => 780, 'height' => 300);
		$this->WP_Widget(PWGP_NAME, PWGP_NAME, $widget_ops, $control_ops);
	}
	// Code generator
	function widget($args, $gallery){
		include 'PiwigoPress_widget.php';
	}
	function update($new_gallery, $old_gallery){
		$gallery = $old_gallery;
		$gallery['title'] = strip_tags(stripslashes($new_gallery['title']));
		$gallery['thumbnail'] = (bool) $new_gallery['thumbnail'];
		$gallery['thumbnail_size'] = strip_tags(stripslashes($new_gallery['thumbnail_size']));
		$gallery['format'] = strip_tags(stripslashes($new_gallery['format']));
		$gallery['piwigo'] = strip_tags(stripslashes($new_gallery['piwigo']));
		$gallery['external'] = strip_tags(stripslashes($new_gallery['external']));
		$gallery['number'] = intval(strip_tags(stripslashes($new_gallery['number'])));
		$gallery['category'] = intval(strip_tags(stripslashes($new_gallery['category'])));
		$gallery['from'] = intval(strip_tags(stripslashes($new_gallery['from'])));
		$gallery['divclass'] = strip_tags(stripslashes($new_gallery['divclass']));
		$gallery['class'] = strip_tags(stripslashes($new_gallery['class']));
		$gallery['most_visited'] = (strip_tags(stripslashes($new_gallery['most_visited'])) == 'true') ? 'true':'false';
		$gallery['best_rated'] = (strip_tags(stripslashes($new_gallery['best_rated'])) == 'true') ? 'true':'false';
		$gallery['most_commented'] = (strip_tags(stripslashes($new_gallery['most_commented'])) == 'true') ? 'true':'false';
		$gallery['random'] = (strip_tags(stripslashes($new_gallery['random'])) == 'true') ? 'true':'false';
		$gallery['recent_pics'] = (strip_tags(stripslashes($new_gallery['recent_pics'])) == 'true') ? 'true':'false';
		$gallery['calendar'] = (strip_tags(stripslashes($new_gallery['calendar'])) == 'true') ? 'true':'false';
		$gallery['tags'] = (strip_tags(stripslashes($new_gallery['tags'])) == 'true') ? 'true':'false';
		$gallery['comments'] = (strip_tags(stripslashes($new_gallery['comments'])) == 'true') ? 'true':'false';
		$gallery['mbcategories'] = (strip_tags(stripslashes($new_gallery['mbcategories'])) == 'true') ? 'true':'false';
		$gallery['allsel'] = (strip_tags(stripslashes($new_gallery['allsel'])) == 'true') ? 'true':'false';
		$gallery['filter'] = (strip_tags(stripslashes($new_gallery['filter'])) == 'true') ? 'true':'false';
		$gallery['lnktype'] = strip_tags(stripslashes($new_gallery['lnktype']));
		$gallery['opntype'] = strip_tags(stripslashes($new_gallery['opntype']));
		if ( current_user_can('unfiltered_html') ) {
			$gallery['text'] =  $new_gallery['text'];
			$gallery['precode'] =  $new_gallery['precode'];
			$gallery['postcode'] =  $new_gallery['postcode'];
		} else {
			// wp_filter_post_kses() expects slashed
			$gallery['text'] = stripslashes( wp_filter_post_kses( addslashes($new_gallery['text']) ) );
			$gallery['precode'] = stripslashes( wp_filter_post_kses( addslashes($new_gallery['precode']) ) );
			$gallery['postcode'] = stripslashes( wp_filter_post_kses( addslashes($new_gallery['postcode']) ) );
		}
		return $gallery;
	}
	function form($gallery){
		// Options
		include 'PiwigoPress_options.php';
	}
}

// Register 
function PiwigoPress_Init() {
			register_widget('PiwigoPress');
}
add_action('widgets_init', PWGP_NAME . '_Init');

// Style allocation
function PiwigoPress_load_in_head() {
	if (defined('PWGP_CSS_FILE')) return; // Avoid several links to CSS in case of several usage of PiwigoPress... 
	define('PWGP_CSS_FILE','');
	if ( is_admin() ) {
		echo '<link media="all" type="text/css" href="' . 
			plugins_url( 'piwigopress/css/piwigopress_adm.min.css?ver=') . PWGP_VERSION . '" id="piwigopress_a-css" rel="stylesheet">'; // that's fine
	}
	else {
		echo '<link media="all" type="text/css" href="' . 
			plugins_url( 'piwigopress/css/piwigopress.css?ver=') . PWGP_VERSION . '" id="piwigopress_c-css" rel="stylesheet">'; // that's fine
	}
}
add_action('wp_head',  PWGP_NAME . '_load_in_head');

// Script to be used
function PiwigoPress_load_in_footer() {
	/* Scripts */
	wp_register_script( 'piwigopress_s', plugins_url( 'piwigopress/js/piwigopress.js'), array('jquery'), PWGP_VERSION );
	wp_enqueue_script( 'jquery'); // include it even if it's done
	if ( is_admin() ) {
		wp_register_script( 'piwigopress_a', plugins_url( 'piwigopress/js/piwigopress_adm.min.js'), array('jquery'), PWGP_VERSION );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'piwigopress_a' );
	}
	wp_enqueue_script( 'piwigopress_s' );
}
add_action('wp_footer',  PWGP_NAME . '_load_in_footer');

function PiwigoPress_register_plugin() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
        return;
    add_action('admin_head', PWGP_NAME . '_load_in_head');
}

add_action('init', PWGP_NAME . '_register_plugin');

// Admin code only if distributed and in Admin
if ( is_admin() ) {
		@include 'piwigopress_admin.php';
}
function piwigopress_plugin_links($links, $file) {  
	$plugin = plugin_basename(__FILE__);  
  
	if ($file == $plugin) // only for this plugin  
		return array_merge( $links,   
			// array( sprintf( '<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings') ) ),  
			array( '<a href="http://wordpress.org/extend/plugins/piwigopress/faq/" target="_blank">' . __('FAQ') . '</a>' ),  
			array( '<a href="http://wordpress.org/support/plugin/piwigopress" target="_blank">' . __('PiwigoPress Support') . '</a>' ),  
			array( '<a href="http://piwigo.org/" target="_blank">' . __('Piwigo site') . '</a>' )  
		);  
	return $links;  
}  
  
add_filter( 'plugin_row_meta', PWGP_NAME . '_plugin_links', 10, 2 );  
# vim:set expandtab tabstop=2 shiftwidth=2 autoindent smartindent: #
?>
