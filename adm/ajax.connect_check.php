<?php
    include_once ("./_common.php");

    try {
        $username = isset($_POST['username']) ? $_POST['username'] : null;
        $userpassword = isset($_POST['password']) ? $_POST['password'] : null;
        $port = isset($_POST['port']) ? $_POST['port'] : null;

        if($username == null) throw new Exception("ftp 계정명을 입력해주세요");
        if($userpassword == null) throw new Exception("ftp 비밀번호를 입력해주세요");
        if($port == null) throw new Exception("프로토콜을 선택해주세요");

        $conn_result = $g5_update->connect($_SERVER['HTTP_HOST'], $port, $username, $userpassword);
        if($conn_result == false) throw new Exception("연결에 실패했습니다.");

        $data = array();
        $data['error']    = 0;
        $data['message']  = "성공적으로 연결되었습니다.";
    } catch (Exception $e) {
        $data = array();
        $data['code']    = $e->getCode();
        $data['message'] = $e->getMessage();
    }

    die(json_encode($data));
?>