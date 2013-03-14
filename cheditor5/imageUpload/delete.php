<?php
require_once("_config.php");
// ---------------------------------------------------------------------------

$delete = trim($_GET['img']);

// 파일의 경로에서 파일명만 얻어낸다. \ 나 / 는 제거된다.
preg_match('/[0-9a-z_]+\.(gif|png|jpe?g)$/i', $delete, $m);
$delete = $m[0];

// 파일의 아이피 부분만 잘라내서 자신의 아이피인지 비교한다.
list($ip2long, $filename) = explode('_', $delete);
if ($ip2long == md5($_SERVER['REMOTE_ADDR'])) {
    $filepath = sprintf("%s/%s", SAVE_DIR, $delete);
    $r = unlink($filepath);
    echo $r ? true : false;
}
else {
    echo false;
}
?>