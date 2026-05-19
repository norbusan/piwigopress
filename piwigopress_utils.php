<?php

if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */

function PWGP_secure($data) {
  // magic_quotes_gpc was removed in PHP 5.4 and the function in PHP 8.0;
  // WordPress already slashes $_POST, so we wp_unslash to undo that.
  return esc_url_raw( trim( wp_unslash( (string) $data ) ) );
}

// Reject URLs that would let a logged-in user pivot to internal/loopback/
// link-local hosts or non-HTTP schemes (SSRF guard for the AJAX endpoints).
// Returns true if $url is safe to fetch, false otherwise. Path-only URLs
// (e.g. "/piwigo/") are allowed and resolved against the WP host by
// pwg_get_contents().
function PWGP_url_is_safe($url) {
  if (!is_string($url) || $url === '') return false;
  // Local path — handled by home_url() in pwg_get_contents, always safe.
  if (substr($url, 0, 1) === '/' && substr($url, 0, 2) !== '//') return true;

  $normalized = (substr($url, 0, 2) === '//') ? 'http:' . $url : $url;
  $parts = wp_parse_url($normalized);
  if (!$parts || empty($parts['host'])) return false;
  if (!isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http','https'), true)) return false;

  $host = strtolower($parts['host']);
  // Resolve to IPs. gethostbynamel returns IPv4 only; that's enough to
  // block the common metadata/loopback/private cases.
  $ips = @gethostbynamel($host);
  if (!$ips) {
    // Could be an IPv6 literal or unresolvable host — try filter_var.
    $ip = filter_var(trim($host, '[]'), FILTER_VALIDATE_IP);
    $ips = $ip ? array($ip) : array();
  }
  if (!$ips) return false;
  foreach ($ips as $ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
      return false;
    }
  }
  return true;
}

//  $mode can be 0 (don't use name), 1 (always use name) and 'auto' (use name if it's not camera-generated)
function PWGP_getPictureName($picture, $mode) {
  if (!$mode) return NULL;

  $picturename = $picture['name'];

  // If image has no expicitly set name, Piwigo will use file name w/o extension instead.
  // Some applications will upload files with names equal to the file name.
  // We don't want to show such names. However, some users may give meaningful filenames
  // to the images - so we only apply this logic if image name looks like camera-generated:
  // i.e. it consists of one word of latin letters, digits and underscores, followed by
  // a set of optional image extension prefixes.
  if ($mode == 'auto' && preg_match('/^[A-Za-z]*[_0-9]+(\.(jpg|JPG|png|PNG|jpeg|JPEG))?$/', $picturename)) {
    $picturefile = $picture['file'];
    if ($picturefile) {
      $lastdot = strrpos($picturefile, '.');
      # if lastdot === 0, we don't want to skip if - no oversight here
      if ($lastdot and (substr($picturefile, 0, $lastdot) == $picturename) or ($picturefile == $picturename)) {
        return NULL;
      }
    }
  }
  return $picturename;
}

# vim:set expandtab tabstop=2 shiftwidth=2 autoindent smartindent: #
