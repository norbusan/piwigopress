<?php

if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */

function PWGP_secure($data) {
    if ( get_magic_quotes_gpc() ) return urldecode ( trim ( htmlentities ( stripslashes( trim($data) ) , ENT_NOQUOTES ) ) );
    else return urldecode ( trim ( htmlentities ( trim($data) , ENT_NOQUOTES ) ) );
}
