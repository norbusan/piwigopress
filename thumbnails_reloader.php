<?php

if (!defined('PWGP_NAME')) define('PWGP_NAME','PiwigoPress');
else return;
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
// error_reporting(E_ALL);
// No WordPress routines available below like load_plugin_textdomain
if (!function_exists('pwg_get_contents')) include 'PiwigoPress_get.php';
$url = PWGP_secure($_GET['url']); // Sanitised

if ( isset($_GET['loadcats']) ) {
    // for loading of categories
    
    $fullurl = $url . "ws.php?format=json&method=pwg.categories.getList&recursive=true";
    $response = pwg_get_contents( $fullurl );
    echo $response;
} else {
    // for loading of thumbnails
    // echo '<li> REQUEST_URI = '.$_SERVER['REQUEST_URI'].'</li>\n';
    
    $loaded = ( isset($_GET['loaded']) ) ? ((int) $_GET['loaded']) : 0; 
    $catid  = ( isset($_GET['category']) ) ? ((int) $_GET['category']) : 0;
    $recur  = ( isset($_GET['recursive']) ) ? ((int) $_GET['recursive']) : 1;
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
        $response = pwg_get_contents( $url.'ws.php?method=pwg.categories.getImages'.$catstr.'&recursive='.$recur.'&format=php&per_page='.$by.'&order=id%20desc&page='.$page );
        $page++;
        $thumbc = unserialize($response);
        if ($thumbc["stat"] == 'ok') {
            /* try to fix loading for piwigo 2.6
            ** $pictures = $thumbc["result"][ "images"]["_content"]; */
            $pictures = $thumbc["result"][ "images"];
            if (!isset($pictures[0]['derivatives']['square']['url'])) {
                echo "<li>Warning: No available public picture or Piwigo release < 2.4.x</li>\n"; // How POedit that ???
                return;
            }
            echo "\n\n";
            foreach ($pictures as $picture) {
                $thumburl = $picture['derivatives']['square']['url'] ;
                echo "\n
           <li class=\"img-shadow\">\n         
              <img src=\"" . $thumburl . '" title="[PiwigoPress id='
                . $picture['id'] . " url='" . $url . "'] " . $picture['name'] . "\"/>\n
           </li>\n";
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

}

function PWGP_secure($data) {
    if ( get_magic_quotes_gpc() ) return urldecode ( trim ( htmlentities ( stripslashes( trim($data) ) , ENT_NOQUOTES ) ) ); 
    else return urldecode ( trim ( htmlentities ( trim($data) , ENT_NOQUOTES ) ) );
}
    
# vim:set expandtab tabstop=2 shiftwidth=2 autoindent smartindent: #
?>
