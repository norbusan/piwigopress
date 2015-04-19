<?php

function PWGP_secure($data) {
    if ( get_magic_quotes_gpc() ) return urldecode ( trim ( htmlentities ( stripslashes( trim($data) ) , ENT_NOQUOTES ) ) );
    else return urldecode ( trim ( htmlentities ( trim($data) , ENT_NOQUOTES ) ) );
}
