<?php
/*
Plugin Name: PiwigoPress
Plugin URI: http://wordpress.org/plugins/piwigopress/
Description: PiwigoPress from any open API Piwigo gallery, swiftly includes your photos in Posts/Pages and/or add randomized thumbnails and menus in your sidebar.
Version: 2.33dev
Author: Norbert Preining
Author URI: http://www.preining.info/
Text Domain: piwigopress
Domain Path: /languages
*/
if (!defined('ABSPATH')) exit; /* Avoid unpredicted access */
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
if (!defined('PWGP_VERSION')) define('PWGP_VERSION','2.3.3dev');

load_plugin_textdomain('piwigopress', false, dirname (plugin_basename( __FILE__ ) ) . '/languages/');
add_shortcode('PiwigoPress', 'PiwigoPress_photoblog');

require_once('piwigopress_utils.php');
require_once('PiwigoPress_get.php');

function PiwigoPress_photoblog($parm) {
  $id_attr = isset($parm['id']) ? $parm['id'] : null;
  $ids = PiwigoPress_parseids($id_attr);
  if (is_string($ids)) {
    return "<!-- PiwigoPress 'id' attribute in error -->\n\n<br>"
      . esc_html( sprintf( __('PiwigoPress shortcode attribute "id" is invalid: %s', 'piwigopress'), $ids ) )
      . "<br>\n\n";
  }
  $str = '';
  foreach ($ids as $id) {
    $parm['id'] = $id;
    $str .= PiwigoPress_onephoto($parm);
  }
  return $str;
}

function PiwigoPress_parseids($idlist) {
  if ( $idlist == NULL ) {
    return 'id list is required';
  }
  $ids = preg_split("/,/", $idlist, NULL, PREG_SPLIT_NO_EMPTY);
  $result = array();
  foreach ($ids as $range) {
    $tokens = preg_split("/-/", $range);
    if (count($tokens) == 1) {
      $id = $tokens[0];
      if ( !is_numeric($id) or $id < 1) {
        return "id '$id' is not a positive number";
      }
      array_push($result, $id);
    }
    else if (count($tokens) == 2) {
      $begin = $tokens[0];
      if ( !is_numeric($begin) or $begin < 1) {
        return "id '$begin' is not a positive number";
      }
      $end = $tokens[1];
      if ( !is_numeric($end) or $end < 1) {
        return "id '$end' is not a positive number";
      }
      for ($id = (int)$begin; $id <= (int)$end; $id++) {
        array_push($result, $id);
      }
    }
    else {
      return 'id range must contain only one "-" char';
    }
  }
  return $result;
}

function PiwigoPress_onephoto($parm) {
  $default = array(
        'url' => '',
        'id' => 0,     // image_id
        'size' => 'la', // Default large
        'name' => 0,  // Generate picture name, can be 0, 1 or 'auto'
        'desc' => 0,   // Generate picture description
        'class' => '',  // Specific class
        'style' => '',  // Specific style
        'lnktype' => 'picture', // Default generated link
        'opntype' => '_blank', // Default open type
        'ordertype' => 'random', // Default sort order
        'orderasc' => 0, // Default sort order
    );
  $parm = array_change_key_case( (array) $parm );
  extract( shortcode_atts( $default, $parm ) );

  // Whitelist enum-style attributes so a hostile shortcode author can't
  // smuggle markup or weird values into URLs/attributes downstream.
  $size      = in_array( $size, array('sq','th','xs','2s','sm','me','la','xl','xx'), true ) ? $size : 'la';
  $lnktype   = in_array( $lnktype, array('none','album','picture','albumpicture'), true ) ? $lnktype : 'picture';
  $opntype   = in_array( $opntype, array('_blank','_self','_parent','_top'), true ) ? $opntype : '_blank';
  $ordertype = in_array( $ordertype, array('random','date_creation','date_available','rating_score','hit'), true ) ? $ordertype : 'random';
  $name      = in_array( $name, array(0,1,'0','1','auto'), true ) ? $name : 0;
  $class     = sanitize_html_class( $class );
  // safecss_filter_attr strips CSS that would break out (e.g. `expression()`,
  // `javascript:` urls) while keeping ordinary declarations.
  $style     = ( $style !== '' ) ? safecss_filter_attr( $style ) : '';

  $previous_url = get_option( 'PiwigoPress_previous_url' );
  if ($previous_url === false) {
    $previous_url = home_url( '/piwigo' );
    add_option( 'PiwigoPress_previous_url', $previous_url );
  }
  if ($url == '') $url = $previous_url;
  # do not force http:// protocol, but allow for both
  # local (starting with one slash) urls as well as
  # remote but same protocol (starting with //)
  if (substr($url,-1)!='/') $url .= '/';
  // Only persist the URL when it actually changed AND the writer is an
  // editor — avoids a DB write on every front-end shortcode render and
  // prevents low-privileged users from stomping the site-wide default.
  if ( $previous_url != $url && is_user_logged_in() && current_user_can( 'edit_others_posts' ) ) {
    update_option( 'PiwigoPress_previous_url', $url );
  }
  $deriv = array ( 'sq' => 'square', 'th' => 'thumb', 'sm' => 'small', 'xs' => 'xsmall', '2s' => '2small', 
           'me' => 'medium', 'la' => 'large', 'xl' => 'xlarge', 'xx' => 'xxlarge');
  $response = pwg_get_contents( $url . 'ws.php?method=pwg.images.getInfo&format=json&image_id=' . (int) $id);
  if (!is_wp_error($response)) {
    $thumbc = json_decode( wp_remote_retrieve_body($response), true );
    if (!is_array($thumbc)) $thumbc = array('stat' => 'fail', 'message' => 'invalid response');
    $div = '';
    if ($thumbc["stat"] == 'fail') {
      $div = "PiwigoPress warning: id=$id, http error: " . $thumbc['message'];
    } elseif (isset($thumbc["result"]['derivatives']['square']['url'])) {
      $picture = $thumbc["result"];
      $cats = array_reverse($picture['categories']);
      $picture['tn_url'] = $picture['derivatives'][$deriv[$size]]['url'] ;
      $catlink = '/category/' ;
      if (isset($cats[0]['permalink'])) {
        $catlink .= $cats[0]['permalink'];
      } else {
        $catlink .= $cats[0]['id'];
      }

      $targetlink = $picture['page_url'];
      if ( $lnktype == 'albumpicture' ) $targetlink .= $catlink ;
      $picturename = PWGP_getPictureName($picture, $name);

      // WPML compatibility, passing lang parameters to links URLs
      if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $post_language_details = apply_filters( 'wpml_post_language_details', NULL, get_the_ID() ) ;
        $catlink    = add_query_arg( 'lang', $post_language_details['locale'], trailingslashit( $catlink ) );
        $targetlink = add_query_arg( 'lang', $post_language_details['locale'], $targetlink );
      }

      if ( $lnktype == 'none' )
        $atag = NULL;
      elseif ( $lnktype == 'album' )
        $atag = '<a title="' . esc_attr($cats[0]['name']) . '" href="'
          . esc_url( $url . 'index.php?' . $catlink ) . '" target="' . esc_attr($opntype) . '">';
      else
        $atag = '<a title="' . esc_attr($picturename) . '" href="'
          . esc_url($targetlink) . '" target="' . esc_attr($opntype) . '">';

      $picturedesc = $picture['comment'];

      // value of alt tag: title + comment (if present)
      $alt = $picturename;
      if ($picturedesc) {
        if ($alt) $alt .= ' -- ';
        $alt .= $picturedesc;
      }

      if (!$desc) $picturedesc = NULL;
      $div = '<div class="PWGP_shortcode ' . esc_attr($class) . '">' . $atag. '<img class="PWGP_photo" src="' . esc_url($picture['tn_url']) . '" alt="' . esc_attr($alt) . '"/>';
      if ($picturename or $picturedesc) {
        $div .= '<blockquote class="PWGP_caption">';
        if ($picturename) {
          $div .= '<div class="PWGP_name">' . stripslashes(htmlspecialchars(strip_tags($picturename))) . '</div>';
        }
        if ($picturedesc) {
          $div .= '<div class="PWGP_desc">' . stripslashes(htmlspecialchars(strip_tags($picturedesc))) . '</div>';
        }
        $div .= '</blockquote>';
      }
      if ( $atag ) $div .= '</a>';
      $div .= "\n
      </div>";
    }
  }
  if ($style!='') $style = ' style="'.esc_attr($style).'"';
  $str =  '<div id="Photo-' . (int)$id . '-'. esc_attr($size) .'" class="PiwigoPress_photoblog"' . $style . '><!-- PiwigoPress Started -->';
  $str .=  $div . '</div><!-- PiwigoPress Ended -->';
  return $str;
}

class PiwigoPress extends WP_Widget
{
  function __construct(){
    $widget_ops = array('classname' => PWGP_NAME,
      'description' => __( "Adds a thumbnail and its link (to the picture) inside your blog sidebar.",'piwigopress') );
    $control_ops = array('width' => 780, 'height' => 300);
    WP_Widget::__construct(PWGP_NAME, PWGP_NAME, $widget_ops, $control_ops);
    // deprecated in 4.3.0
    // $this->WP_Widget(PWGP_NAME, PWGP_NAME, $widget_ops, $control_ops);
  }
  // Code generator
  function widget($args, $gallery){
    include 'PiwigoPress_widget.php';
  }
  function update($new_gallery, $old_gallery){
    $gallery = $old_gallery;
    $text  = function($k) use ($new_gallery) {
      return isset($new_gallery[$k]) ? strip_tags( stripslashes( (string) $new_gallery[$k] ) ) : '';
    };
    $tbool = function($k) use ($new_gallery) {
      $v = isset($new_gallery[$k]) ? strip_tags( stripslashes( (string) $new_gallery[$k] ) ) : '';
      return ($v === 'true') ? 'true' : 'false';
    };

    $gallery['title']          = $text('title');
    $gallery['thumbnail']      = !empty($new_gallery['thumbnail']);
    $gallery['thumbnail_size'] = $text('thumbnail_size');
    $gallery['format']         = $text('format');
    $gallery['piwigo']         = $text('piwigo');
    $gallery['external']       = esc_url_raw( $text('external') );
    $gallery['number']         = intval($text('number'));
    $gallery['category']       = intval($text('category'));
    $gallery['from']           = intval($text('from'));
    $gallery['divclass']       = $text('divclass');
    $gallery['class']          = $text('class');
    $gallery['most_visited']   = $tbool('most_visited');
    $gallery['best_rated']     = $tbool('best_rated');
    $gallery['most_commented'] = $tbool('most_commented');
    $gallery['random']         = $tbool('random');
    $gallery['recent_pics']    = $tbool('recent_pics');
    $gallery['calendar']       = $tbool('calendar');
    $gallery['tags']           = $tbool('tags');
    $gallery['comments']       = $tbool('comments');
    $gallery['mbcategories']   = $tbool('mbcategories');
    $gallery['allsel']         = $tbool('allsel');
    $gallery['filter']         = $tbool('filter');
    $gallery['lnktype']        = $text('lnktype');
    $gallery['opntype']        = $text('opntype');
    $gallery['ordertype']      = $text('ordertype');
    $gallery['orderasc']       = $tbool('orderasc');

    $raw = function($k) use ($new_gallery) {
      return isset($new_gallery[$k]) ? (string) $new_gallery[$k] : '';
    };
    if ( current_user_can('unfiltered_html') ) {
      $gallery['text']     = $raw('text');
      $gallery['precode']  = $raw('precode');
      $gallery['postcode'] = $raw('postcode');
    } else {
      // wp_filter_post_kses() expects slashed
      $gallery['text']     = stripslashes( wp_filter_post_kses( addslashes( $raw('text') ) ) );
      $gallery['precode']  = stripslashes( wp_filter_post_kses( addslashes( $raw('precode') ) ) );
      $gallery['postcode'] = stripslashes( wp_filter_post_kses( addslashes( $raw('postcode') ) ) );
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

// Enqueue the widget-form helper script on the widgets screen.
function PiwigoPress_widget_admin_assets( $hook ) {
  if ( $hook !== 'widgets.php' ) return;
  wp_enqueue_script( 'piwigopress_ws',
    plugins_url( 'js/piwigopress_widgets.js', __FILE__ ),
    array( 'jquery' ), PWGP_VERSION, true );
}
add_action( 'admin_enqueue_scripts', PWGP_NAME . '_widget_admin_assets' );

// Style allocation
function PiwigoPress_load_in_head() {
  if ( is_admin() ) {
    wp_enqueue_style( 'piwigopress_a-css',
      plugins_url( 'css/piwigopress_adm.min.css', __FILE__ ),
      array(), PWGP_VERSION );
  } else {
    wp_enqueue_style( 'piwigopress_c-css',
      plugins_url( 'css/piwigopress.css', __FILE__ ),
      array(), PWGP_VERSION );
  }
}
add_action( 'wp_enqueue_scripts', PWGP_NAME . '_load_in_head' );

// Script to be used
function PiwigoPress_load_in_footer() {
  wp_register_script( 'piwigopress_s', plugins_url( 'js/piwigopress.js', __FILE__ ), array('jquery'), PWGP_VERSION, true );
  if ( is_admin() ) {
    wp_register_script( 'piwigopress_a', plugins_url( 'js/piwigopress_adm.js', __FILE__ ),
      array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-droppable' ),
      PWGP_VERSION, true );
    wp_localize_script( 'piwigopress_a', 'PwgpAjax', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('piwigopress-admin-nonce')
    ));
    wp_enqueue_script( 'piwigopress_a' );
  }
  wp_enqueue_script( 'piwigopress_s' );
}
add_action( 'wp_enqueue_scripts', PWGP_NAME . '_load_in_footer' );

// proxy a categories call from another server
function PiwigoPress_ajax_categories() {
  $nonce = isset($_POST['nonce']) ? (string) $_POST['nonce'] : '';
  $raw_url = isset($_POST['url']) ? (string) $_POST['url'] : '';

  // Editor-or-above only; the endpoint performs an outbound HTTP fetch on
  // behalf of the user and reflects the response body, so we don't want to
  // expose it to Contributor accounts.
  if (!wp_verify_nonce($nonce, 'piwigopress-admin-nonce') ||
      !current_user_can('edit_others_posts')) {
    http_response_code(403);
    die;
  }

  $url = esc_url_raw( wp_unslash( $raw_url ) );
  if (!PWGP_url_is_safe($url)) {
    http_response_code(400);
    die('Refusing to fetch this URL.');
  }

  $url .= "ws.php?format=json&method=pwg.categories.getList&recursive=true";
  $response = pwg_get_contents($url);
  if (is_wp_error($response)) {
    http_response_code(400);
    echo 'Error: ' . esc_html( $response->get_error_message() );
  } else {
    $body = wp_remote_retrieve_body($response);
    // Decode/re-encode to make sure we only ever return well-formed JSON.
    $decoded = json_decode($body, true);
    header('Content-Type: application/json');
    echo wp_json_encode( is_array($decoded) ? $decoded : array('stat' => 'fail') );
  }
  exit;
}
add_action('wp_ajax_pwgp-categories', PWGP_NAME . '_ajax_categories');

// proxy thumbnails call from another server
function PiwigoPress_ajax_thumbnails() {
  $nonce = isset($_POST['nonce']) ? (string) $_POST['nonce'] : '';
  if (!wp_verify_nonce($nonce, 'piwigopress-admin-nonce') ||
      !current_user_can('edit_others_posts')) {
    http_response_code(403);
    die;
  }
  require_once('piwigopress_thumbnails_reloader.php');
  exit;
}
add_action('wp_ajax_pwgp-thumbnails', PWGP_NAME . '_ajax_thumbnails');

// Admin code only if distributed and in Admin
if ( is_admin() ) {
    require_once __DIR__ . '/piwigopress_admin.php';
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

