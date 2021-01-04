<?php
require_once("config.php");

if(!function_exists('ft_nonce_is_valid')){
    include_once('../editor.lib.php');
}

$filesrc = isset($_POST["filesrc"]) ? preg_replace("/[ #\&\+\-%@=\/\\\:;,\'\"\^`~|\!\?\*$#<>()\[\]\{\}]/", "", $_POST["filesrc"]) : '';

if( !$filesrc || ! preg_match('=^[^/?*;:{}\\\\]+\.[^/?*;:{}\\\\]+$=', $filesrc) || ! preg_match('/\.(gif|jpe?g|bmp|png)$/i', $filesrc) ){
    die( false );
}

$is_editor_upload = false;

$get_nonce = get_session('nonce_'.FT_NONCE_SESSION_KEY);

if( $get_nonce && ft_nonce_is_valid( $get_nonce, 'cheditor' ) ){
    $is_editor_upload = true;
}

if( !$is_editor_upload ){
   die( false );
}

// ---------------------------------------------------------------------------

$file_arr = explode('_', $filesrc );

if( $file_arr[1] !== che_get_file_passname() ){
    die( false );
}

$filepath = SAVE_DIR . '/' . $filesrc;
$r = false;

if( function_exists('run_event') ){
    run_event('delete_editor_file', $filepath, $r);
}

if (file_exists($filepath)) {
	$r = unlink($filepath);
	if ($r) {
		$thumbPath = dirname($filepath) . DIRECTORY_SEPARATOR . "thumb_" . basename($filepath);
		if (file_exists($thumbPath)) {
			unlink($thumbPath);
		}
	}
}

echo $r ? true : false;