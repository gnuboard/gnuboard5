<?php
include_once('./_common.php');

$g5['title'] = '우편번호 검색';
include_once(G5_PATH.'/head.sub.php');

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {   //https 통신
    echo '<script src="https://spi.maps.daum.net/imap/map_js_init/postcode.js"></script>';
} else {  //http 통신
    echo '<script src="http://dmaps.daum.net/map_js_init/postcode.js"></script>';
}

include_once($member_skin_path.'/zip.skin.php');

echo '<script src="'.G5_JS_URL.'/zip.js"></script>';

include_once(G5_PATH.'/tail.sub.php');
?>
