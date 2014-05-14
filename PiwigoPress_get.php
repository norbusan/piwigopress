<?php
if (defined('PHPWG_ROOT_PATH')) return; /* Avoid direct usage under Piwigo */
if (!defined('PWGP_NAME')) return; /* Avoid unpredicted access */
//error_reporting(E_ALL);
if (!isset($pwg_mode)) $pwg_mode = ''; // Remind which process is working well
$pwg_prev_host = ''; // Remind last requested gallery 

function pwg_get_contents($url, $mode='', $timeout=5) {

	global $pwg_mode, $pwg_prev_host;

	$host = (strtolower(substr($url,0,7)) == 'http://') ? substr($url,7) : $url;
	$host = (strtolower(substr($host,0,8)) == 'https://') ? substr($host,8) : $host;
	$doc = substr($host, strpos($host, '/'));
	$host = substr($host, 0, strpos($host, '/'));

	if ($pwg_prev_host != $host) $pwg_mode = ''; // What was possible with one website could be different with another
	$pwg_prev_host = $host;
	if ($mode == '') $mode = $pwg_mode; // Can be forced by the requester
	// $mode = 'fs'; // Test purpose only =>  '' all, 'fs' fsockopen, 'ch' cURL
	echo "\n		<!-- *** PiwigoPress Getmode = " . $mode . " *** -->\n";
// 1 - The simplest solution: file_get_contents
// Contraint: php.ini
//      ; Whether to allow the treatment of URLs (like http:// or ftp://) as files.
//      allow_url_fopen = On
	if ( $mode == '' and true === (bool) ini_get('allow_url_fopen') ) { 
			$value = @file_get_contents($url);
			if ( $value !== false) return $value;
	}
	if ( $mode == '' ) $mode = 'fs';
// 2 - Often accepted access: fsockopen
	if ($mode == 'fs') {
		$fs = @fsockopen($host, 80, $errno, $errstr, $timeout);
		if ( $fs !== false ) { 
			fwrite($fs, 'GET ' . $doc . " HTTP/1.1\r\n");
			fwrite($fs, 'Host: ' . $host . "\r\n");
			fwrite($fs, "Connection: Close\r\n\r\n");
			stream_set_blocking($fs, TRUE);
			stream_set_timeout($fs,$timeout); // Again the $timeout on the get
			$info = stream_get_meta_data($fs);
			$value = '';
			while ((!feof($fs)) && (!$info['timed_out'])) {
							$value .= fgets($fs, 4096);
							$info = stream_get_meta_data($fs);
							flush();
			}
			$value = substr($value, strpos($value,'a:2:{s:4:"stat";'));
			$pwg_mode = 'fs';
			if ( $info['timed_out'] === false ) return $value;
		}
	}
// 3 - Sometime another solution: curl_exec
// See http://fr2.php.net/manual/en/curl.installation.php
	if ($mode !== 'Err' and function_exists('curl_init')) {
		$ch = @curl_init();
		@curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		@curl_setopt($ch, CURLOPT_URL, $url);
		@curl_setopt($ch, CURLOPT_HEADER, 1);
		@curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$value = @curl_exec($ch);
		$header_length = @curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$status = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
		@curl_close($value);
		if ($value !== false and $status >= 200 and $status < 400) {
				$value = substr($value, $header_length);
				$pwg_mode = 'ch';
				return $value;
		}
	}
	// No other solutions
	$return["stat"] = 'failed';
	if ($mode !== 'Err') { 
		echo "\n			<!== PiwigoPress_get: failed on file_get, fsockopen and cURL processes " 
			. "- No solution available on this website with its current configuration ==>\n";
	}
	$pwg_mode = 'Err';
	return serialize($return);
}
?>