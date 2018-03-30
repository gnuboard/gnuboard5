<?php
include_once('../../common.php');

// 커뮤니티 사용여부
if(G5_COMMUNITY_USE === false) {
    define('_SHOP_', true);
}
?>