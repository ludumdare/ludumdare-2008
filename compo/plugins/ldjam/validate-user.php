<?php
// Check wordpress credentials //
require_once __DIR__."/../../../wp-load.php";

echo wp_get_current_user();

// Set Cookie //
//setcookie( "lusha", "1234", time()+2*24*60*60, "/", ".ludumdare.com" );

// Redirect //
//header("Location: http://theme.ludumdare.com");

//echo '<!doctype html>';
//echo '<html><head><meta http-equiv="Location" content="http://example.com/"></head>';
//echo '<body><a href="http://theme.ludumdare.com">http://theme.ludumdare.com</a></body></html>';

die();
