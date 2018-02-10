<?php
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */

function pwg_ret_protocol() {
        if ( isset($_SERVER['HTTPS']) ) {
                if ( 'on' == strtolower($_SERVER['HTTPS']) )
                        return "https:";
                if ( '1' == $_SERVER['HTTPS'] )
                        return "https:";
        } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
                return "https:";
        }
        return "http:";
}

function pwg_get_contents($url) {

	# support same-host piwigo
	if (substr($url,0,2) == '//') {
	  # support protocol ignorant URLs
	  $fullurl = pwg_ret_protocol() . $url;
	} elseif ((strtolower(substr($url,0,7)) !== 'http://') and 
	    (strtolower(substr($url,0,8)) !== 'https://')) {
	  # support local path only
	  $fullurl = pwg_ret_protocol() . "//" . $_SERVER['HTTP_HOST'] . $url;
	} else {
	  $fullurl = $url;
	}

	echo ("<!-- DEBUG pwg_get_contents: calling wp_remote_get fullurl = $fullurl -->\n");
	return wp_remote_get($fullurl);
}

