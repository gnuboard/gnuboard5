<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$wr_1) {
    $wr_1 = $_REQUEST['wr_1'] ? $_REQUEST['wr_1'] : get_session("ss_itemuse_wr_1");
    if (!$wr_1) {
        alert("wr_1 에 상품코드를 넘겨주세요.");
    }
}

set_session("ss_itemuse_wr_1", $wr_1);
?>