<?php
if (!defined('_GNUBOARD_')) exit;

$s3config_file = G5_DATA_PATH.'/'.G5_S3CONFIG_FILE;
if (file_exists($s3config_file)) {
    include_once($s3config_file);
    include_once(G5_LIB_PATH.'/common.lib.php');    // 공통 라이브러리
    require_once(G5_LIB_PATH.'/s3.lib.php');

    // s3 $g5 배열에 저장
    $g5['s3'] = new S3(G5_S3_ACCESS_KEY, G5_S3_SECRET_KEY, G5_S3_BUCKET_NAME);

    add_event('s3_move_uploaded_file', 's3_uploaded_file', 10, 2);

    function s3_uploaded_file($tmp_file, $dest_file) {
        global $g5;

        if($g5['s3'] == false) return false;

        $result = $g5['s3']->uploadFile($tmp_file, $dest_file);

        return $result;
    }
}
