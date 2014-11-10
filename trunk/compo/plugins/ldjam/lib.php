<?php
defined('ABSPATH') or die("No.");
// - ----------------------------------------------------------------------------------------- - //
// My little collection of helper functions //
// - ----------------------------------------------------------------------------------------- - //
global $has_apcu;
$has_apcu = function_exists('apcu_fetch');	// Check if APCu is available (memory caching) //
// - ----------------------------------------------------------------------------------------- - //
function to_bool( $value ) {
	$ret = strtoupper($value) === "FALSE" ? false : true;
	if ( $ret ) {
		return (bool)$value;
	}
	return $ret;
}
// - ----------------------------------------------------------------------------------------- - //
function to_slug( $string, $maxLength=260, $separator='_' ) {
	$url = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
	$url = preg_replace('/[^a-zA-Z0-9 -]/', '', $url);
	$url = trim(substr(strtolower($url), 0, $maxLength));
	$url = preg_replace('/[s' . $separator . ']+/', $separator, $url);
	return $url;
}
// - ----------------------------------------------------------------------------------------- - //
?>
