<?php
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */
require_once('PiwigoPress_get.php');

global $wpdb; /* Need for recent thumbnail access */
extract($args);

$title = apply_filters('widget_title', empty($gallery['title']) ? '&nbsp;' : $gallery['title']);
if ( $title ) $title = $before_title . $title . $after_title;

$piwigo = empty($gallery['piwigo']) ? '' : $gallery['piwigo'];
$piwigo_url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $piwigo ;
if (!empty($gallery['external'])) $piwigo_url = $gallery['external'];
if (substr($piwigo_url,-1)!='/') $piwigo_url .= '/';

$thumbnail = empty($gallery['thumbnail']) ? true : (bool) $gallery['thumbnail'];

$thumbnail_size = empty($gallery['thumbnail_size']) ? 'sq' : $gallery['thumbnail_size'];
$format = empty($gallery['format']) ? 'any' : $gallery['format'];

$options = '';
$number = empty($gallery['number']) ? 1 : intval($gallery['number']);
$options .= '&per_page=' . intval($gallery['number']);
if (!empty($gallery['category'])) $options .= '&cat_id=' . intval($gallery['category']);
$from = empty($gallery['from']) ? '12' : intval($gallery['from']);
$r = (array) $wpdb->get_results('SELECT date_sub( date( now( ) ) , INTERVAL ' . $from . ' MONTH ) as begin');
$from = $r[0]->begin;
if (!empty($gallery['from'])) $options .= '&f_min_date_created=' . $from;
if  ($gallery['format']=='portrait') $options .= '&f_max_ratio=0.99';
if  ($gallery['format']=='landscape') $options .= '&f_min_ratio=1.01';

$PiwigoPress_divclass = empty($gallery['divclass']) ? ' class="PWGP_widget"' : (' class="' . $gallery['divclass'] .' PWGP_widget"');
$PiwigoPress_class = empty($gallery['class']) ? '' : $gallery['class'];
$mbcategories = empty($gallery['mbcategories']) ? '' : $gallery['mbcategories'];
$most_visited = empty($gallery['most_visited']) ? '' : $gallery['most_visited'];
$best_rated = empty($gallery['best_rated']) ? '' : $gallery['best_rated'];
$most_commented = empty($gallery['most_commented']) ? '' : $gallery['most_commented'];
$random = empty($gallery['random']) ? '' : $gallery['random'];
$recent_pics = empty($gallery['recent_pics']) ? '' : $gallery['recent_pics'];
$calendar = empty($gallery['calendar']) ? '' : $gallery['calendar'];
$tags = empty($gallery['tags']) ? '' : $gallery['tags'];
$comments = empty($gallery['comments']) ? '' : $gallery['comments'];
$lnktype = empty($gallery['lnktype']) ? 'picture' : $gallery['lnktype'];
$opntype = empty($gallery['opntype']) ? '_blank' : $gallery['opntype'];
$ordertype = empty($gallery['ordertype']) ? 'random' : $gallery['ordertype'];
$orderasc = empty($gallery['orderasc']) ? '' : $gallery['orderasc'];
$category = empty($gallery['category']) ? 0 : $gallery['category'];
if ( $category==0 and ($lnktype=='album' || $lnktype=='albumpicture')) $lnktype = 'picture';
$filter = empty($gallery['filter']) ? 'true' : $gallery['filter'];
$text = empty($gallery['text']) ? '' : $gallery['text'];
$text = ( $filter == 'true' ) ? wpautop( $text ) : $text;
$precode = empty($gallery['precode']) ? '' : $gallery['precode'];
$postcode = empty($gallery['postcode']) ? '' : $gallery['postcode'];

echo $before_widget;
echo $title;

if ($thumbnail) {
	// Make the Piwigo link
	$callstr = $piwigo_url . 'ws.php?method=pwg.categories.getImages&format=php' . $options . '&recursive=true&order=' . $ordertype;
	if ($orderasc == 'false') {
		$callstr .= '%20DESC';
	}
	//var_dump($callstr);
	$response = pwg_get_contents( $callstr );
	if (!is_wp_error($response)) {
		$thumbc = unserialize($response['body']);
		/* fix from http://wordpress.org/support/topic/piwigo-260-and-piwigopress-223
		** for piwigo 2.6
		$pictures = $thumbc["result"]["images"]["_content"];
		*/
		if (!empty($precode)) { echo $precode; }
		$pictures = $thumbc["result"]["images"];
		// var_dump($pictures);
		foreach ($pictures as $picture) {
			if (isset($picture['derivatives']['square']['url'])) {
				// set the default link target
				// since we are not sure whether there is a thumbnail (we removed f_with_thumbnail
				// in the API call), we set it either to the thumbnail or square, the later one
				// existing definitely
				if (isset($picture['derivatives']['thumb']['url'])) {
					$picture['tn_url'] = $picture['derivatives']['thumb']['url'] ;
				} else {
					$picture['tn_url'] = $picture['derivatives']['square']['url'] ;
				}
				if ($thumbnail_size == 'sq') $picture['tn_url'] = $picture['derivatives']['square']['url'] ;
				if ($thumbnail_size == 'sm') $picture['tn_url'] = $picture['derivatives']['small']['url'] ;
				if ($thumbnail_size == 'xs') $picture['tn_url'] = $picture['derivatives']['xsmall']['url'] ;
				if ($thumbnail_size == '2s') $picture['tn_url'] = $picture['derivatives']['2small']['url'] ;
				if ($thumbnail_size == 'me') $picture['tn_url'] = $picture['derivatives']['medium']['url'] ;
				if ($thumbnail_size == 'la') $picture['tn_url'] = $picture['derivatives']['large']['url'] ;
				if ($thumbnail_size == 'xl') $picture['tn_url'] = $picture['derivatives']['xlarge']['url'] ;
				if ($thumbnail_size == 'xx') $picture['tn_url'] = $picture['derivatives']['xxlarge']['url'] ;
			}
			//
			//var_dump($picture);
			// value of alt tag: title + comment (if present)
			$alt = htmlspecialchars($picture['name']);
			if (isset($picture['comment'])) $alt .= ( ' -- ' . htmlspecialchars($picture['comment']) );

			echo '<div' . $PiwigoPress_divclass . '>';
			if ( $lnktype=='picture' ) {
				echo '<a title="' . htmlspecialchars($picture['name']) . '" href="' 
					. $piwigo_url . 'picture.php?/' . $picture['id'] . '" target="' . $opntype . '"><img class="PWGP_thumb '
					. $PiwigoPress_class . '" src="' . $picture['tn_url'] . '" alt="' . $alt . '"/>';
			}
			if ( $lnktype=='album' ) {
				echo '<a title="' . htmlspecialchars($picture['name']) . '" href="' 
					. $piwigo_url . 'index.php?/category/' . $category . '" target="' . $opntype . '"><img class="PWGP_thumb '
					. $PiwigoPress_class . '" src="' . $picture['tn_url'] . '" alt="' . $alt . '"/>';
				$lnktype='picture';
			}
			if ( $lnktype=='albumpicture' ) {
				echo '<a title="' . htmlspecialchars($picture['name']) . '" href="' 
					. $piwigo_url . 'picture.php?/' . $picture['id'] . '/category/' . $category . '" target="' . $opntype . '"><img class="PWGP_thumb '
					. $PiwigoPress_class . '" src="' . $picture['tn_url'] . '" alt="' . $alt . '"/>';
			}
				
			if (isset( $picture['comment'] )) { 
				$picture['comment'] = stripslashes(htmlspecialchars(strip_tags($picture['comment'])));
				if (trim($picture['comment']) != '')
					echo '<blockquote class="PWGP_caption">' . $picture['comment'] . '</blockquote>'; 
			}
			if ( $lnktype!='none' ) echo '</a>';
			// if quering a remote piwigo, the "element_url" is not set
			// TODO ask why this is the case
			if (isset($picture['element_url'])) {
				echo '<a class="img_selector" name="' . $picture['element_url'] . '" rel="nofollow" href="javascript:void(0);" title="' 
				. $picture['width'] .'x' . $picture['height'] .'"></a>';
			}
			echo '</div>';
		}
		if (!empty($postcode)) { echo $postcode; }
		echo '<div class="textwidget">' . $text . '</div>';
	}
}

if ($mbcategories == 'true') {
	// Make the Piwigo category list
	$response = pwg_get_contents( $piwigo_url 
			. 'ws.php?method=pwg.categories.getList&format=php&public=true');
	if (!is_wp_error($response)) {
		$cats = unserialize($response['body']);
		echo '<ul style="clear: both;"><li>' . __('Pictures categories','piwigopress') . '<ul>';
		foreach ($cats["result"]["categories"] as $cat) {
			echo '<li><a title="' . $cat['name'] . '" href="' . $piwigo_url . 'index.php?category/' . $cat['id'] . '">' . $cat['name'] . '</a></li>';
		}
		echo '</ul></li></ul>';
	}
}

if ($most_visited == 'true' or $best_rated == 'true' or 
    $most_commented == 'true' or $random == 'true' or $recent_pics == 'true' or 
    $calendar == 'true' or $tags == 'true' or $comments == 'true') echo '<ul style="clear: both;">';

if ($most_visited == 'true') 
	echo '<li><a title="' . __('Most visited','piwigopress') . '" href="' . $piwigo_url . 'index.php?most_visited' . '">' . __('Most visited','piwigopress') . '</a></li>';

if ($best_rated == 'true') 
	echo '<li><a title="' . __('Best rated','piwigopress') . '" href="' . $piwigo_url . 'index.php?best_rated' . '">' . __('Best rated','piwigopress') . '</a></li>';

if ($most_commented == 'true') 
	echo '<li><a title="' . __('Most commented','piwigopress') . '" href="' . $piwigo_url . 'index.php?most_commented' . '">' . __('Most commented','piwigopress') . '</a></li>';

if ($random == 'true') 
	echo '<li><a title="' . __('Random','piwigopress') . '" href="' . $piwigo_url . 'random.php' . '">' . __('Random','piwigopress') . '</a></li>';

if ($recent_pics == 'true')
	echo '<li><a title="' . __('Recent pics','piwigopress') . '" href="' . $piwigo_url . 'index.php?recent_pics' . '">' . __('Recent pics','piwigopress') . '</a></li>';

if ($calendar == 'true') 
	echo '<li><a title="' . __('Calendar','piwigopress') . '" href="' . $piwigo_url . 'index.php?created-monthly-calendar' . '">' . __('Calendar','piwigopress') . '</a></li>';

if ($tags == 'true') 
	echo '<li><a title="' . __('Tags','piwigopress') . '" href="' . $piwigo_url . 'tags.php' . '">' . __('Tags','piwigopress') . '</a></li>';

if ($comments == 'true') 
	echo '<li><a title="' . __('Comments','piwigopress') . '" href="' . $piwigo_url . 'comments.php' . '">' . __('Comments','piwigopress') . '</a></li>';


if ($most_visited == 'true' or $best_rated == 'true' or 
    $most_commented == 'true' or $random == 'true' or $recent_pics == 'true' or 
    $calendar == 'true' or $tags == 'true' or $comments == 'true') echo '</ul>';
	
echo $after_widget;


