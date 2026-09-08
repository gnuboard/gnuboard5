<?php
include_once('./_common.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$is_member ||
    !isset($_POST['token_case']) || $_POST['token_case'] !== 'qa_write') {
    die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.', 'token'=>'', 'url'=>'')));
}

check_request_origin();
$token = _token();
set_session('ss_qa_write_token', $token);
die(json_encode(array('error'=>'', 'token'=>$token, 'url'=>'')));
