<?php
if (!defined('_GNUBOARD_')) {
    exit; // 개별 페이지 접근 불가
}

function social_log_file_delete($second = 0)
{
    $files = glob(G5_DATA_PATH . '/tmp/social_*');
    if (is_array($files)) {
        $before_time  = $second ? G5_SERVER_TIME - $second : 0;
        foreach ($files as $social_log_file) {
            $modification_time = filemtime($social_log_file); // 파일접근시간

            if ($before_time && $modification_time > $before_time) {
                continue;
            }

            unlink($social_log_file);
        }
    }
}
