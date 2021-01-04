<?php
include_once('./_common.php');

$count = (isset($_POST['chk_wr_id']) && is_array($_POST['chk_wr_id'])) ? count($_POST['chk_wr_id']) : 0;
$post_btn_submit = isset($_POST['btn_submit']) ? clean_xss_tags($_POST['btn_submit'], 1, 1) : '';

if(!$count) {
    alert(addcslashes($post_btn_submit, '"\\/').' 하실 항목을 하나 이상 선택하세요.');
}

if($post_btn_submit === '선택삭제') {
    include './delete_all.php';
} else if($post_btn_submit === '선택복사') {
    $sw = 'copy';
    include './move.php';
} else if($post_btn_submit === '선택이동') {
    $sw = 'move';
    include './move.php';
} else {
    alert('올바른 방법으로 이용해 주세요.');
}