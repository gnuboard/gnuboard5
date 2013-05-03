<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 휴대폰번호 포맷(010-0000-0000) 변경해서 기록
// $reg_mb_hp 는 rigister_form_update.head.skin.php 파일
$sql = " update {$g4['member_table']} set mb_hp = '$reg_mb_hp' where mb_id = '$mb_id' ";
sql_query($sql);

/* =========================================================================== */
/* = 휴대폰인증 및 성인인증                                                  = */
/* =========================================================================== */
if(get_session('ss_kcpcert_no')) {
    $sql = " update {$g4['member_table']}
                set mb_hp_certify   = '".get_session('ss_hp_certify')."',
                    mb_adult        = '".get_session('ss_adult')."'
                where mb_id = '$mb_id' ";
    sql_query($sql);
}
/* =========================================================================== */
?>