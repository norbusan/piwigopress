<?php

if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */
// error_reporting(E_ALL);
require_once('PiwigoPress_get.php');

$url = isset($_POST['url']) ? esc_url_raw( wp_unslash( (string) $_POST['url'] ) ) : '';
if (!PWGP_url_is_safe($url)) {
  http_response_code(400);
  return;
}

$loaded = ( isset($_POST['loaded']) ) ? ((int) $_POST['loaded']) : 0;
$catid  = ( isset($_POST['category']) ) ? ((int) $_POST['category']) : 0;
$recur  = ( isset($_POST['recursive']) ) ? ((int) $_POST['recursive']) : 1;
$recur  = ( $recur==1 ? "true" : "false" ) ;

$by = 5;
if ($loaded > 9) $by = 10;
if ($loaded > 49) $by = 50;
if ($loaded > 99) $by = 100; // More we loaded larger next step might be

$more = $by; // So more by 5, 5, 10, 10, 10, 10, 50, 100, 100, ...

$page = floor( $loaded / $by ); // From page

$loop = floor( $more / $by ); // 1, Only one loop now
for ($i = 1; $i <= $loop; $i++) {
    if ($catid == 0) {
        $catstr = "" ;
    } else {
        $catstr = "&cat_id=$catid" ;
    }
    // echo "<li>loading : ".$url."ws.php?method=pwg.categories.getImages".$catstr.'&recursive='.$recur.'&format=php&per_page='.$by.'&order=id%20desc&page='.$page."</li>\n";
    $response = pwg_get_contents( $url.'ws.php?method=pwg.categories.getImages'.$catstr.'&recursive='.$recur.'&format=json&per_page='.$by.'&order=id%20desc&page='.$page );
    $page++;
    if (!is_wp_error($response)) {
        $thumbc = json_decode( wp_remote_retrieve_body($response), true );
        if (!is_array($thumbc) || !isset($thumbc['result']['images'])) {
            echo '<li>' . esc_html__( 'Warning: invalid response from Piwigo', 'piwigopress' ) . "</li>\n";
            return;
        }
        $pictures = $thumbc['result']['images'];
        if (!isset($pictures[0]['derivatives']['square']['url'])) {
            echo '<li>' . esc_html__( 'Warning: No available public picture or Piwigo release < 2.4.x', 'piwigopress' ) . "</li>\n";
            return;
        }
        echo "\n\n";
        foreach ($pictures as $picture) {
            $thumburl = isset($picture['derivatives']['square']['url']) ? $picture['derivatives']['square']['url'] : '';
            $pid   = isset($picture['id']) ? (int) $picture['id'] : 0;
            $pname = isset($picture['name']) ? (string) $picture['name'] : '';
            echo "\n       <li class=\"img-shadow\">\n"
               . '          <img src="' . esc_url($thumburl) . '" title="'
               . esc_attr( '[PiwigoPress id=' . $pid . " url='" . $url . "'] " . $pname )
               . "\"/>\n       </li>\n";
        }
        echo "\n\n";
    }
}
return;
/* TODO Seeing full pic */
// <a class=\"over\" href=\"javascript:void(0);\"><span>\n
// <img src=\"" . $picture['derivatives']['medium']['url'] . '"
// title="[PiwigoPress id=' . $picture['id'] . " url='" . $url . "'] " . $picture['name'] . "\">
// <br>" . $picture['comment'] . "\n
// </span>\n
// </a>\n

# vim:set expandtab tabstop=2 shiftwidth=2 autoindent smartindent: #

