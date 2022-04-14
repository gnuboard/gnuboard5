<?php
include_once ("./_common.php");

try {
    $admin_password = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
    if($admin_password == '') throw new Exception("관리자패스워드가 입력되지 않았습니다.");

    // 관리자 비밀번호 비교
    $admin = get_admin('super');
    if(!check_password($admin_password, $admin['mb_password'])) throw new Exception("관리자 비밀번호가 일치하지 않습니다.");

    $data = array();
    $data['error']      = 0;
    $data['item']       = "success";

} catch (Exception $e) {
    $data = array();
    $data['error']   = 1;
    $data['code']    = $e->getCode();
    $data['message'] = $e->getMessage();
}

die(json_encode($data));
?>