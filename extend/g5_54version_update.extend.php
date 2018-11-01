<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

put_event('member_login_check', 'g54_userlogin_after', 10, 2);

// 5.3 버전에서 
function g54_userlogin_after($mb, $link){
    global $g5;

    if(isset($mb['mb_id']) && $mb['mb_id']){

        $mb_img_paths = array(G5_DATA_PATH.'/member/'.substr($mb['mb_id'], 0, 2).'/', G5_DATA_PATH.'/member_image/'.substr($mb['mb_id'], 0, 2).'/');
        
        $mb_id = $mb['mb_id'];

        foreach( $mb_img_paths as $path ){
            $before_file = $path.$mb_id.'.gif';
            $after_file = $path.get_mb_icon_name($mb_id).'.gif';
            
            // 회원 아이콘 또는 프로필 파일 이름을 변경
            if( ($before_file !== $after_file) && file_exists($before_file) && ! file_exists($after_file) ){
                @rename($before_file, $after_file);
            }
        }
    }
}
?>