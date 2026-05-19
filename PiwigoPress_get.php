<?php
if (!defined('ABSPATH')) exit;
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */

function pwg_ret_protocol() {
        // Honour reverse-proxy X-Forwarded-Proto via WP's is_ssl().
        return is_ssl() ? 'https:' : 'http:';
}

function pwg_get_contents($url) {

	# support same-host piwigo
	if (substr($url,0,2) == '//') {
	  # support protocol ignorant URLs
	  $fullurl = pwg_ret_protocol() . $url;
	} elseif ((strtolower(substr($url,0,7)) !== 'http://') and
	    (strtolower(substr($url,0,8)) !== 'https://')) {
	  # support local path only — derive host from WP, not from the Host: header
	  $fullurl = home_url( $url );
	} else {
	  $fullurl = $url;
	}

	// wp_safe_remote_get applies the http_request_host_is_external /
	// reject_unsafe_urls filters; combined with redirection=0 this prevents
	// SSRF via 30x redirects to internal hosts.
	return wp_safe_remote_get( $fullurl, array(
	  'timeout'     => 5,
	  'redirection' => 0,
	) );
}

