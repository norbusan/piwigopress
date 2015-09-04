<?php
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */

	wp_register_script( 'piwigopress_ws', plugins_url( 'piwigopress/js/piwigopress_widgets.js'), array('jquery'), PWGP_VERSION );
	wp_enqueue_script( 'piwigopress_ws' );
	// Defaults
	$gallery = wp_parse_args( (array) $gallery, array('title'=>__('Random picture'), 
		'thumbnail'=> true, 'thumbnail_size' => 'sq', 'format'=>'any', 'piwigo'=>'', 
		'external'=>'http://piwigo.org/demo/', 'number'=>1, 'category'=>0, 'from'=> 12, 'divclass'=>'', 
		'class'=>'',  'most_visited' => 'true', 'best_rated' => 'true',
		'most_commented' => 'true', 'random' => 'true', 'recent_pics' => 'true',
		'calendar' => 'true', 'tags' => 'true', 'comments' => 'true', 'allsel' => 'true', 
		'mbcategories' => 'true', 'filter' => 'true', 'text' => '', 'lnktype' => 'picture',
		'opntype' => '_blank', 'ordertype' => 'random', 'orderasc' => 'false', 'precode' => '', 'postcode' => '',
		 ) );

	$title = htmlspecialchars($gallery['title']);
	$thumbnail = (bool) $gallery['thumbnail'];
	$thumbnail_size = htmlspecialchars($gallery['thumbnail_size']);
	$format = htmlspecialchars($gallery['format']);
	$piwigo = htmlspecialchars($gallery['piwigo']);
	$external = htmlspecialchars($gallery['external']);
	$number = intval($gallery['number']);
	$category = intval($gallery['category']);
	$from = intval($gallery['from']);
	$divclass = htmlspecialchars($gallery['divclass']);
	$class = htmlspecialchars($gallery['class']);
	$mbcategories = (htmlspecialchars($gallery['mbcategories']) == 'true') ? 'checked="checked"':'';
	$most_visited = (htmlspecialchars($gallery['most_visited']) == 'true') ? 'checked="checked"':'';
	$best_rated = (htmlspecialchars($gallery['best_rated']) == 'true') ? 'checked="checked"':'';
	$most_commented = (htmlspecialchars($gallery['most_commented']) == 'true') ? 'checked="checked"':'';
	$random = (htmlspecialchars($gallery['random']) == 'true') ? 'checked="checked"':'';
	$recent_pics = (htmlspecialchars($gallery['recent_pics']) == 'true') ? 'checked="checked"':'';
	$calendar = (htmlspecialchars($gallery['calendar']) == 'true') ? 'checked="checked"':'';
	$tags = (htmlspecialchars($gallery['tags']) == 'true') ? 'checked="checked"':'';
	$comments = (htmlspecialchars($gallery['comments']) == 'true') ? 'checked="checked"':'';
	$menu = $mbcategories . $most_visited . $best_rated . $most_commented . $random . $recent_pics . $calendar . $tags . $comments;
	$true = 'truetruetruetruetruetruetruetruetrue';
	if ( $menu == $true ) $gallery['allsel'] = 'true';
	else $gallery['allsel'] = 'false';
	$filter = (htmlspecialchars($gallery['filter']) == 'true') ? 'checked="checked"':'';
	$text = htmlspecialchars($gallery['text']);
	$precode = htmlspecialchars($gallery['precode']);
	$postcode = htmlspecialchars($gallery['postcode']);
	$lnktype = htmlspecialchars($gallery['lnktype']);
	$opntype = htmlspecialchars($gallery['opntype']);
	$ordertype = htmlspecialchars($gallery['ordertype']);
	$orderasc = (htmlspecialchars($gallery['orderasc']) == 'true') ? 'checked="checked"':'';
	$allsel = (htmlspecialchars($gallery['allsel']) == 'true') ? 'checked="checked"':'';
	$allchk = (htmlspecialchars($gallery['allsel']) == 'true') ? 'display: none;':'';
	$allunchk = (htmlspecialchars($gallery['allsel']) == 'true') ? '':'display: none;';
	
	// Options
	echo '<div class="PWGP_widget">
		<p style="text-align:right;">
			<label>' . __('Title','piwigopress') . ' 
			<input style="width: 250px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title')	. '" type="text" value="' . $title . '" />
			</label><br>';
 	// Piwigo directory
	echo '<label>' . __('<strong>Local</strong> directory (if local)','piwigopress') 
	. ' <input style="width: 200px;" id="' . $this->get_field_id('piwigo') . '" name="' . $this->get_field_name('piwigo')
	. '" type="text" value="' . $piwigo . '" /></label><br>';
	// External website
	echo '<label>' . __('(or) <strong>External</strong> gallery URL','piwigopress') 
	. ' <input style="width: 250px;" id="' . $this->get_field_id('external') . '" name="' . $this->get_field_name('external')
	. '" type="text" value="' . $external . '" /></label></p>';

	// Thumbnail
	echo '<table>
	<tr>
		<td class="tn_size">
		<fieldset style="text-align:right;" class="edge">
			<legend><span> ' . __('Size') . ' </span></legend>
			<label for="'. $this->get_field_id('thumbnail_size') .'">' . __('Square','piwigopress') . ' </label> 
			<input type="radio" value="sq" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'sq',false) . '><br>
			<label style="display: inline-block; width: 120px;" for="'. $this->get_field_id('thumbnail_size') .'">' . __('Thumbnail','piwigopress') . ' </label> 
			<input type="radio" value="th" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'th',false) . '><br>
			<label for="'. $this->get_field_id('thumbnail_size') .'">' . __('XXS - tiny','piwigopress') . ' </label> 
			<input type="radio" value="2s" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'2s',false) . '><br>
			<label style="display: inline-block; width: 120px;" for="'. $this->get_field_id('thumbnail_size') .'">' . __('XS - extra small','piwigopress') . ' </label> 
			<input type="radio" value="xs" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'xs',false) . '><br>';
			if ( in_array($thumbnail_size, array('sq','th','2s','xs')) ) {
				echo '
			<label style="display: inline-block; width: 120px;" for="'. $this->get_field_id('thumbnail_size') .'">
			<a class="_more" rel="nofollow" href="javascript:void(0);" title="' . __('Select a larger sized picture','piwigopress') . '">' . __('Large sizes','piwigopress') . '</a> </label>
			<span class="hidden"> ';
			}
			echo '
			<label style="display: inline-block; width: 120px;" for="'. $this->get_field_id('thumbnail_size') .'">' . __('S - small','piwigopress') . ' </label> 
			<input type="radio" value="sm" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'sm',false) . '><br>
			<label style="display: inline-block; width: 120px;" for="'. $this->get_field_id('thumbnail_size') .'">' . __('M - medium','piwigopress') . ' </label> 
			<input type="radio" value="me" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'me',false) . '><br>
			<label style="display: inline-block; width: 120px;" for="'. $this->get_field_id('thumbnail_size') .'">' . __('L - large','piwigopress') . ' </label> 
			<input type="radio" value="la" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'la',false) . '><br>
			<label style="display: inline-block; width: 120px;" for="'. $this->get_field_id('thumbnail_size') .'">' . __('XL - extra large','piwigopress') . ' </label> 
			<input type="radio" value="xl" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'xl',false) . '><br>
			<label style="display: inline-block; width: 120px;" for="'. $this->get_field_id('thumbnail_size') .'">' . __('XXL - huge','piwigopress') . ' </label> 
			<input type="radio" value="xx" id="'. $this->get_field_id('thumbnail_size') .'" class="post-format" name="'. $this->get_field_name('thumbnail_size') .'" ' 
				. checked($thumbnail_size,'xx',false) . '>';
			if ( in_array($thumbnail_size, array('sq','th','2s','xs')) ) {
				echo '</span>';
			}
			echo '
		</fieldset><br>
		<fieldset class="edge">
			<legend><span> ' . __('Open type') . ' </span></legend>
			<label>' . __('New window/tab','piwigopress') . ' </label> 
			<input type="radio" value="_blank" id="'. $this->get_field_id('opntype') .'" class="post-format" name="'. $this->get_field_name('opntype') .'" ' 
				. checked($opntype,'_blank',false) . '><br>
			<label>' . __('Same window/tab','piwigopress') . ' </label> 
			<input type="radio" value="_self" id="'. $this->get_field_id('opntype') .'" class="post-format" name="'. $this->get_field_name('opntype') .'" ' 
				. checked($opntype,'_self',false) . '>
		</fieldset><br>
		<fieldset class="edge">
			<legend><span> ' . __('Format') . ' </span></legend>
			<label>' . __('Any orientation','piwigopress') . ' </label> 
			<input type="radio" value="any" id="'. $this->get_field_id('format') .'" class="post-format" name="'. $this->get_field_name('format') .'" ' 
				. checked($format,'any',false) . '><br>
			<label>' . __('Portrait only','piwigopress') . ' </label> 
			<input type="radio" value="portrait" id="'. $this->get_field_id('format') .'" class="post-format" name="'. $this->get_field_name('format') .'" ' 
				. checked($format,'portrait',false) . '><br>
			<label>' . __('Landscape only','piwigopress') . ' </label> 
			<input type="radio" value="landscape" id="'. $this->get_field_id('format') .'" class="post-format" name="'. $this->get_field_name('format') .'" ' 
				. checked($format,'landscape',false) . '>
		</fieldset></td>';
	// Orientation
		echo '<td>
		<fieldset class="edge">
			<legend><span> ' . __('Link type') . ' </span></legend>
			<label>' . __('No link','piwigopress') . ' </label> 
			<input type="radio" value="none" id="'. $this->get_field_id('lnktype') .'" class="post-format" name="'. $this->get_field_name('lnktype') .'" ' 
				. checked($lnktype,'none',false) . '><br>
			<label>' . __('Album page if one selected','piwigopress') . ' </label> 
			<input type="radio" value="album" id="'. $this->get_field_id('lnktype') .'" class="post-format" name="'. $this->get_field_name('lnktype') .'" ' 
				. checked($lnktype,'album',false) . '><br>
			<label>' . __('Picture page','piwigopress') . ' </label> 
			<input type="radio" value="picture" id="'. $this->get_field_id('lnktype') .'" class="post-format" name="'. $this->get_field_name('lnktype') .'" ' 
				. checked($lnktype,'picture',false) . ' title="' . __('Forced option if selected album id is 0 (see below)') . '"><br>
			<label>' . __('Picture page in Album','piwigopress') . ' </label> 
			<input type="radio" value="albumpicture" id="'. $this->get_field_id('lnktype') .'" class="post-format" name="'. $this->get_field_name('lnktype') .'" ' 
				. checked($lnktype,'albumpicture',false) . '>
		</fieldset></br>
		<fieldset class="edge">
			<legend><span> ' . __('Sort order') . ' </span></legend>
			<label>' . __('Random','piwigopress') . ' </label> 
			<input type="radio" value="random" id="'. $this->get_field_id('ordertype') .'" class="post-format" name="'. $this->get_field_name('ordertype') .'" ' 
				. checked($ordertype,'random',false) . '><br>
			<label>' . __('Creation date','piwigopress') . ' </label> 
			<input type="radio" value="date_creation" id="'. $this->get_field_id('ordertype') .'" class="post-format" name="'. $this->get_field_name('ordertype') .'" ' 
				. checked($ordertype,'date_creation',false) . '><br>
			<label>' . __('Availability date','piwigopress') . ' </label> 
			<input type="radio" value="date_available" id="'. $this->get_field_id('ordertype') .'" class="post-format" name="'. $this->get_field_name('ordertype') .'" ' 
				. checked($ordertype,'date_available',false) . '><br>
			<label>' . __('Rating score','piwigopress') . ' </label> 
			<input type="radio" value="rating_score" id="'. $this->get_field_id('ordertype') .'" class="post-format" name="'. $this->get_field_name('ordertype') .'" ' 
				. checked($ordertype,'rating_score',false) . '><br>
			<label>' . __('Hits','piwigopress') . ' </label> 
			<input type="radio" value="hit" id="'. $this->get_field_id('ordertype') .'" class="post-format" name="'. $this->get_field_name('ordertype') .'" ' 
				. checked($ordertype,'hit',false) . '>
		</fieldset>
		<fieldset style="text-align:right;" class="edge">
			<label>' . __('Ascending order','piwigopress') . ' <input id="' . $this->get_field_id('orderasc') . '" name="' . $this->get_field_name('orderasc')
			. '" type="checkbox" value="true" ' . $orderasc . '/></label>
		</fieldset>
		</td>';
	// The categories menu
	echo '<td>
		<fieldset style="text-align:right;" class="edge">
			<legend><span> ' . __('Menu') . ' </span></legend>
			<label><span style="' . $allchk . '">' . __('Select all','piwigopress') . '</span><span style="' . $allunchk . '">' . __('Unselect all','piwigopress') . '</span>' 
			. ' <input id="' . $this->get_field_id('allsel') . '" class="MenuSel" name="' . $this->get_field_name('allsel')
			. '" type="checkbox" value="true" ' . $allsel . '/></label><br>
			<label>' . __('Album menu','piwigopress') . ' <input id="' . $this->get_field_id('mbcategories') . '" name="' . $this->get_field_name('mbcategories')
			. '" type="checkbox" value="true" ' . $mbcategories . '/></label><br>
			<label>' . __('Most visited','piwigopress') . ' <input id="' . $this->get_field_id('most_visited') . '" name="' . $this->get_field_name('most_visited')
			. '" type="checkbox" value="true" ' . $most_visited . '/></label><br>
			<label>' . __('Best rated','piwigopress') . ' <input id="' . $this->get_field_id('best_rated') . '" name="' . $this->get_field_name('best_rated')
			. '" type="checkbox" value="true" ' . $best_rated . '/></label><br>
			<label>' . __('Most commented','piwigopress') . ' <input id="' . $this->get_field_id('most_commented') . '" name="' . $this->get_field_name('most_commented')
			. '" type="checkbox" value="true" ' . $most_commented . '/></label><br>
			<label>' . __('Random','piwigopress') . ' <input id="' . $this->get_field_id('random') . '" name="' . $this->get_field_name('random')
			. '" type="checkbox" value="true" ' . $random . '/></label><br>
			<label>' . __('Recent pics','piwigopress') . ' <input id="' . $this->get_field_id('recent_pics') . '" name="' . $this->get_field_name('recent_pics')
			. '" type="checkbox" value="true" ' . $recent_pics . '/></label><br>
			<label>' . __('Calendar','piwigopress') . ' <input id="' . $this->get_field_id('calendar') . '" name="' . $this->get_field_name('calendar','piwigopress')
			. '" type="checkbox" value="true" ' . $calendar . '/></label><br>
			<label>' . __('Tags','piwigopress') . ' <input id="' . $this->get_field_id('tags') . '" name="' . $this->get_field_name('tags')
			. '" type="checkbox" value="true" ' . $random . '/></label><br>
			<label>' . __('Comments','piwigopress') . ' <input id="' . $this->get_field_id('comments') . '" name="' . $this->get_field_name('comments','piwigopress')
			. '" type="checkbox" value="true" ' . $tags . '/></label></fieldset></td></tr><tr>';
	// from
	echo '<td style="text-align:right;"><label>' . __('Since X months (0=all)','piwigopress') 
	. ' <input style="width: 30px; text-align: right;" id="' . $this->get_field_id('from') . '" name="' . $this->get_field_name('from')
	. '" type="text" value="' . $from . '" /></label></td>';
	// Selected category
	echo '<td style="text-align:center;">
		<label>' . __('Album id (0=all)','piwigopress') 
	. ' <input style="width: 45px; text-align: center;" id="' . $this->get_field_id('category') . '" name="' . $this->get_field_name('category')
	. '" type="text" value="' . $category . '" title="' . __('If Album id = 0 (all): Link type "album" is going to switch to "picture"') 
	. '" /></label></td>';
	// number of pictures
	echo '<td style="text-align:right;"><label>' . __('Number of pictures (0=none)','piwigopress') 
		. ' <input style="width: 30px; text-align: right;" id="' . $this->get_field_id('number') . '" name="' . $this->get_field_name('number')
		. '" type="text" value="' . $number . '" /></label>
		</td></tr></table>';

		// Pre Post code
	echo '<table><tr><td style="text-align: right; width: 20%; padding: 0;"><br>' . __('Widget photo(s) pre-code','piwigopress') . '&nbsp;</label></td>';
	echo '<td><textarea class="widefat" rows="3" cols="20" id="' . $this->get_field_id('precode') . '" name="' . $this->get_field_name('precode') . '">' . $precode .'</textarea></label></td></tr>';
	echo '<tr><td style="text-align: right; width: 20%; padding: 0;"><br>' . __('Widget photo(s) post-code','piwigopress') . '&nbsp;</label></td>';
	echo '<td><textarea class="widefat" rows="3" cols="20" id="' . $this->get_field_id('postcode') . '" name="' . $this->get_field_name('postcode') . '">' . $postcode .'</textarea></label></td></tr>';
	echo '</table>';

	// Styling

		// Caption
	echo '<table><tr><td style="text-align: right; width: 20%; padding: 0;"><br>' . __('Caption','piwigopress') . '<br><label>' 
	. __('Automatically add paragraphs', 'piwigopress') . '&nbsp; 
	<input id="' . $this->get_field_id('filter') .'" name="' . $this->get_field_name('filter') . '" type="checkbox" value="true" ' . $filter . ' />
		</label></td><td>'
	. ' <textarea class="widefat" rows="6" cols="20" id="' . $this->get_field_id('text') . '" name="' . $this->get_field_name('text') . '">' . $text .'</textarea>
		</label></td></tr></table>';

	// Styling
	echo '<table>
	<tr>
		<td style="text-align: right;"><label>' . __('CSS DIV class','piwigopress') 
	. ' <input style="width: 200px;" id="' . $this->get_field_id('divclass') . '" name="' . $this->get_field_name('divclass')
	. '" type="text" value="' . $divclass . '" /></label></td>
		<td style="text-align: right;"><label>' . __('CSS IMG class','piwigopress') 
	. ' <input style="width: 200px;" id="' . $this->get_field_id('class') . '" name="' . $this->get_field_name('class')
	. '" type="text" value="' . $class . '" /></label></td>
	</tr></table>
	</div>';

