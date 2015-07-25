<?php

if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */

function PWGP_secure($data) {
  if ( get_magic_quotes_gpc() ) return urldecode ( trim ( htmlentities ( stripslashes( trim($data) ) , ENT_NOQUOTES ) ) );
  else return urldecode ( trim ( htmlentities ( trim($data) , ENT_NOQUOTES ) ) );
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
