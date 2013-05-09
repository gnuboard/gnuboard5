<?php
include_once('./_common.php');

// 모바일 쇼핑몰 카테고리 선택시에만 사용함
if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/category.php');
    return;
}
?>