<?php
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */
require_once('PiwigoPress_get.php');

if(!class_exists('PiwigoPress_Admin')){
  class PiwigoPress_Admin {

    function PiwigoPress_Admin(){
      add_filter( 'media_buttons_context', array(&$this,'Add_new_button'), 9999 );
      add_action( 'in_admin_header', PWGP_NAME . '_load_in_head' ); 
      add_action( 'in_admin_footer', PWGP_NAME . '_load_in_footer' ); 
       
      add_action( 'save_post',  array(&$this, 'Save_options'));
    }
    function Save_options( $post_ID ) {
      if (array_key_exists('piwigopress_url', $_POST)) {
        $PWGP_options = serialize(array(
          'previous_url'     =>  (string) $_POST['piwigopress_url'],
          'thumbnail_size'   =>  (string) $_POST['thumbnail_size'],
          'desc_check'    =>  (bool) $_POST['desc_check'],
          'display_name'    =>  (string) $_POST['display_name'],
          'photo_class'    =>  (string) $_POST['photo_class'],
          'link_type'      =>  (string) $_POST['link_type'],
          'open_type'      =>  (string) $_POST['open_type'],
          ));
        # support local path, too
        if ( strlen($_POST['piwigopress_url']) > 1 ) update_option( 'PiwigoPress_previous_options', $PWGP_options );
      }
    }
    function Add_new_button($context=''){
      $PWGP_options = serialize(array(
        'previous_url'     => 'http://piwigo.org/demo/',
        'thumbnail_size'   => 'me',
        'desc_check'    => (bool) true,
        'display_name'    => '0',
        'photo_class'    => 'img-shadow',
        'link_type'      => 'picture',
        'open_type'      => '_blank',
      ));
      $previous_options = get_option( 'PiwigoPress_previous_options', $PWGP_options );
      extract( unserialize($previous_options) );
      if ( !in_array($thumbnail_size, array('sq','th','xs','2s','sm','me','la','xl','xx'))) $thumbnail_size='la';
      if ( !in_array($display_name, array('0','1','auto'))) $display_name='0';
      $url  = __('Piwigo gallery URL:','piwigopress');
      $actions = __('Actions:','piwigopress');
      $recommendation = __('Folder URL of any up-to-date Piwigo Gallery with public photos and opened webservices (MUST END with a "/")','piwigopress');
      $load = __('Start with 5 recent pics','piwigopress');
      $loaddesc = __('Load or reload the 5 most latest squared thumbnails from the url (might be changed or not)','piwigopress');
      $loadcat = __('Load list of albums','piwigopress');
      $loadcatdesc = __('After loading the list of albums, photos can be loaded from one of them','piwigopress');
      $allcats = __('All albums/photos','piwigopress');
      $more = __('Get more','piwigopress');
      $moredesc = __('Getting more squared thumbnails: starts by 5, then 5, 10, 10, 10, 10, 50, and continues by 100','piwigopress');
      $hide = __('Hide 50%','piwigopress');
      $hidedesc = __('Hide 50% or less of current available squared thumbnails','piwigopress');
      $show = __('Show hidden','piwigopress');
      $showdesc  = __('Display all currently hidden squared thumbnails','piwigopress');
      $drop = __('Drop one or several thumbnails over the blue border here above !','piwigopress');
      $loadreq = __('Loaded, hidden & dropped vs. Requested thumbnails: ','piwigopress');
      $desc_options = __('Shortcode options','piwigopress');
      $catiddesc = __("This is the Album ID from where pictures will be obtained; use 0 for 'all albums' (most recent pictures first, regardless of album), use a specific Album ID number (e.g. 123) to get pictures from the specific album with that ID (CatID=123 in the example)",'piwigopress');
      $Lib_size =  __('Picture size','piwigopress');
      $Lib_sq =  __('Square','piwigopress');
      $Lib_th =  __('Thumbnail','piwigopress');
      $Lib_xs =  __('XS - extra small','piwigopress');
      $Lib_2s =  __('XXS - tiny','piwigopress');
      $Lib_sm =  __('S - small','piwigopress');
      $Lib_me =  __('M - medium','piwigopress');
      $Lib_la =  __('L - large','piwigopress');
      $Lib_xl =  __('XL - extra large','piwigopress');
      $Lib_xx =  __('XXL - huge','piwigopress');
      $csq = checked($thumbnail_size,'sq',false);
      $cth = checked($thumbnail_size,'th',false);
      $cxs = checked($thumbnail_size,'xs',false);
      $c2s = checked($thumbnail_size,'2s',false);
      $csm = checked($thumbnail_size,'sm',false);
      $cme = checked($thumbnail_size,'me',false);
      $cla = checked($thumbnail_size,'la',false);
      $cxl = checked($thumbnail_size,'xl',false);
      $cxx = checked($thumbnail_size,'xx',false);
      $Lib_disp_name = __('Display name','piwigopress');
      $Lib_name_off = __('off  - don\'t diplay name', 'piwigopress');
      $Lib_name_on  = __('on   - diplay name', 'piwigopress');
      $Lib_name_auto = __('auto - display not auto-gen names', 'piwigopress');
      $dispnoff = checked($display_name,'0',false);
      $dispnon  = checked($display_name,'1',false);
      $dispnauto  = checked($display_name,'auto',false);
      $descrip_check = checked($desc_check,true,false);
      $Lib_desc =  __('Display description','piwigopress');
      $Lib_CSS_div =  __('CSS DIV class','piwigopress');
      $Gen_insert =  __('Generate and insert','piwigopress');
      $gendesc = __('Generate shortcodes of all dropped squared thumbnails','piwigopress');
      $Reset_drop =  __('Reset dropping zone','piwigopress');
      $rstdesc = __('Remove all squared thumbnails from the dropping zone','piwigopress');

      if ( !in_array($link_type, array('album','none','picture','albumpicture'))) $link_type='picture';
      $clnkno  = checked($link_type,'none',false);
      $clnkalb = checked($link_type,'album',false);
      $clnkpic = checked($link_type,'picture',false);
      $clnkalbpic = checked($link_type,'albumpicture',false);
      $Lib_lnktype =  __('Link type','piwigopress');
      $Lib_link_no  =  __('No link','piwigopress');
      $Lib_link_alb =  __('Album page','piwigopress');
      $Lib_link_pic =  __('Picture page','piwigopress');
      $Lib_link_albpic = __('Picture in album page', 'piwigopress');

      if ( !in_array($open_type, array('_blank','_self'))) $open_type='_blank';
      $copnnew  = checked($open_type,'_blank',false);
      $copnsel = checked($open_type,'_new',false);
      $Lib_opntype =  __('Open type','piwigopress');
      $Lib_open_new = __('New window/tab','piwigopress');
      $Lib_open_self = __('Same window/tab','piwigopress');
      
      echo <<<EOF
<div id="PWGP_Gal_finder" style="display:none">
  <label id="PWGP_label">$url
  <input id="PWGP_finder" type="text" value="$previous_url" name="piwigopress_url" style="width: 250px;" title="$recommendation">
  &nbsp;  <select id="PWGP_catscroll" name="piwigo_catsel" rel="nofollow" href="javascript:void(0);" class="hidden"><option value="0">$allcats</option></select>
  &nbsp;  <a id="PWGP_loadcat" rel="nofollow" href="javascript:void(0);" class="button" title="$loadcatdesc">$loadcat</a></label>
  <br><label id="PWGP_label">$actions <a id="PWGP_more" rel="nofollow" href="javascript:void(0);" class="hidden button" title="$moredesc">$more</a>
  <a id="PWGP_hide" rel="nofollow" href="javascript:void(0);" class="hidden button" title="$hidedesc">$hide</a>
  <a id="PWGP_show" rel="nofollow" href="javascript:void(0);" class="hidden button" title="$showdesc">$show</a>
  <a id="PWGP_load" rel="nofollow" href="javascript:void(0);" class="button" title="$loaddesc">$load</a></label>
  <ul class='hidden'></ul>
  <div class="PWGP_system" style="display:none">
    <div class="PWGP_gallery">
      <ul id="PWGP_dragger"> 
      </ul>
      <span id="PWGP_show_stats" class="hidden">$loadreq<span id="PWGP_stats"> </span></span>  &nbsp;    &nbsp;  

      <img id="PWGP_Load_Active" src="../wp-content/plugins/piwigopress/img/LoadActive.gif" style="display: none;"/>

      <div id="PWGP_dropping"> 
        <ul class='gallery ui-helper-reset'></ul>
      </div>

      <h3><span>$drop</span></h3>
    </div>
  </div>
  <div id="PWGP_short_option">
    <div class="legend">$desc_options</div>
    <table class="sel_size">
      <tr>
        <td>&nbsp;</td>
        <td>
          <div class="legend">$Lib_size</div>
          <div class="fieldset" style="text-align:right; min-width: 400px;">
            <table id="thumbnail_size">
              <tr>
                <td>
                  <label>$Lib_sq &nbsp; <input type="radio" value="sq" class="post-format" name="thumbnail_size" $csq></label><br>  
                  <label>$Lib_th &nbsp; <input type="radio" value="th" class="post-format" name="thumbnail_size" $cth></label><br>  
                  <label>$Lib_xs &nbsp; <input type="radio" value="xs" class="post-format" name="thumbnail_size" $cxs></label><br>  
                  <label>$Lib_2s &nbsp; <input type="radio" value="2s" class="post-format" name="thumbnail_size" $c2s></label><br>  
                  <label>$Lib_sm &nbsp; <input type="radio" value="sm" class="post-format" name="thumbnail_size" $csm></label><br>  
                </td>
                <td>
                  <label>$Lib_me &nbsp; <input type="radio" value="me" class="post-format" name="thumbnail_size" $cme></label><br>  
                  <label>$Lib_la &nbsp; <input type="radio" value="la" class="post-format" name="thumbnail_size" $cla></label><br>  
                  <label>$Lib_xl &nbsp; <input type="radio" value="xl" class="post-format" name="thumbnail_size" $cxl></label><br>  
                  <label>$Lib_xx &nbsp; <input type="radio" value="xx" class="post-format" name="thumbnail_size" $cxx></label>
                </td>
              </tr>
            </table>
          </div><br>
          <div style="text-align:right;">
            <label>$Lib_desc  &nbsp; 
              <input id="desc_check" style="width: 30px;" name="desc_check" type="checkbox" $descrip_check value="true" />
            </label>
          </div>
        </td>
        <td>
          <div class="legend">$Lib_opntype</div>
          <div id="open_type" class="fieldset" style="text-align:right; min-width:200px;">
            <label>$Lib_open_new &nbsp; <input type="radio" value="_blank" class="post-format" name="open_type" $copnnew></label><br>  
            <label>$Lib_open_self &nbsp; <input type="radio" value="_self" class="post-format" name="open_type" $copnsel></label><br>  
          </div><br>
          <div class="legend">$Lib_disp_name</div>
          <div id="display_name" class="fieldset" style="text-align:right; min-width: 400px;">
            <label>$Lib_name_off &nbsp; <input type="radio" value="0" class="post-format" name="display_name" $dispnoff></label><br>  
            <label>$Lib_name_on &nbsp; <input type="radio" value="1" class="post-format" name="display_name" $dispnon></label><br>  
            <label>$Lib_name_auto &nbsp; <input type="radio" value="auto" class="post-format" name="display_name" $dispnauto></label><br>  
          </div>
        </td>
        <td>
          <div class="legend">$Lib_lnktype</div>
          <div id="link_type" class="fieldset" style="text-align:right;">
            <label>$Lib_link_no &nbsp; <input type="radio" value="none" class="post-format" name="link_type" $clnkno></label><br>  
            <label>$Lib_link_alb &nbsp; <input type="radio" value="album" class="post-format" name="link_type" $clnkalb></label><br>  
            <label>$Lib_link_pic &nbsp; <input type="radio" value="picture" class="post-format" name="link_type" $clnkpic></label><br>  
            <label>$Lib_link_albpic &nbsp; <input type="radio" value="albumpicture" class="post-format" name="link_type" $clnkalbpic></label><br>  
          </div><br>
          
          <div style="text-align:right;">
            <label>$Lib_CSS_div   &nbsp;<br>
              <input id="photo_class" style="width: 300px;" name="photo_class" type="text" value="$photo_class" />
            </label>
          </div>
        </td>
      </tr>
      <tr>
        <td></td><td>
          <a id="PWGP_rst" rel="nofollow" href="javascript:void(0);" class="hidden button" title="$rstdesc">$Reset_drop</a>
        </td>
        <td>
          <a id="PWGP_Gen" rel="nofollow" href="javascript:void(0);" class="hidden button" title="$gendesc">$Gen_insert</a>
        </td>
      </tr>
    </table>
  </div>
</div>
EOF;
      return $context . '<a id="PWGP_button" rel="nofollow" href="javascript:void(0);" 
      title="'. __('Insert a PiwigoPress shortcode from a Piwigo dragged photo','piwigopress') . '">
      <img src="../wp-content/plugins/piwigopress/img/PiwigoPress.png"/></a>';
    }
  }
}
if (!is_object($PWG_Adm)) {
  $PWG_Adm = new PiwigoPress_Admin();
}

# vim:set expandtab tabstop=2 shiftwidth=2 autoindent smartindent: #
