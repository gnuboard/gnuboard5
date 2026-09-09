<?php
if (!defined('_GNUBOARD_')) exit;

// 이전 호출부가 남아 있어도 PG 취소 성공으로 처리하지 않는다.
$pg_res_cd = 'SIRK_RETIRED';
$pg_res_msg = 'SIRK 전용 카카오페이의 자동 취소 지원이 종료되었습니다. 이니시스 상점관리자에서 실제 취소 후 주문 상태와 환불금액을 확인해 주십시오.';
