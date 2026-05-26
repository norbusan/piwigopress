<?php
if (!defined('ABSPATH')) exit;
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */

function pwg_ret_protocol() {
        // Honour reverse-proxy X-Forwarded-Proto via WP's is_ssl().
        return is_ssl() ? 'https:' : 'http:';
}

function pwg_resolve_url( $url ) {
	# support same-host piwigo
	if ( substr($url,0,2) == '//' ) {
	  # support protocol ignorant URLs
	  return pwg_ret_protocol() . $url;
	}
	if ( (strtolower(substr($url,0,7)) !== 'http://') &&
	     (strtolower(substr($url,0,8)) !== 'https://') ) {
	  # Local path: resolve against the WordPress host only (NOT the full
	  # home_url, which may include a subdirectory like /blog and would
	  # turn "/photo/" into "/blog/photo/"). We use the scheme+host parsed
	  # out of home_url() instead of $_SERVER['HTTP_HOST'] so the Host
	  # header cannot be poisoned to redirect outbound fetches.
	  $home = wp_parse_url( home_url() );
	  $base = ( isset($home['scheme']) ? $home['scheme'] : 'http' ) . '://'
	        . ( isset($home['host']) ? $home['host'] : '' )
	        . ( isset($home['port']) ? ':' . (int) $home['port'] : '' );
	  return $base . ( $url[0] === '/' ? $url : '/' . $url );
	}
	return $url;
}

function pwg_get_contents($url) {
	$fullurl = pwg_resolve_url( $url );
	// wp_safe_remote_get applies http_request_host_is_external /
	// reject_unsafe_urls on the initial URL. Allow a small number of
	// redirects so real Piwigo galleries (HTTP→HTTPS, missing trailing
	// slash) still work; the URL guard above rejects private/loopback
	// hosts, which is the primary SSRF concern.
	return wp_safe_remote_get( $fullurl, array(
	  'timeout'     => 5,
	  'redirection' => 3,
	) );
}

