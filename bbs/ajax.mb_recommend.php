<?php
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

$mb_recommend = trim($_POST["reg_mb_recommend"]);

if ($msg = valid_mb_id($mb_recommend)) {
    die("추천인의 아이디는 영문자, 숫자, _ 만 입력하세요.");
}
if (!($msg = exist_mb_id($mb_recommend))) {
    die("입력하신 추천인은 존재하지 않는 아이디 입니다.");
}
?>