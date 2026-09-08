<?php
include_once('./_common.php');

// 그누보드4 자료 이전은 최고관리자에게만 허용되는 별도 용도이다.
$is_import_token = $bo_table === 'g4_import' && $is_admin === 'super';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$bo_table ||
    (!$is_import_token && (empty($board['bo_table']) || $board['bo_table'] !== $bo_table)))
   die(json_encode(array('error'=>'게시판 정보가 올바르지 않습니다.', 'url'=>G5_URL)));

check_request_origin();

set_session('ss_write_'.$bo_table.'_token', '');

$token = get_write_token($bo_table);

die(json_encode(array('error'=>'', 'token'=>$token, 'url'=>'')));
