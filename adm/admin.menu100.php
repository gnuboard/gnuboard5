<?
$menu["menu100"] = array (
    array("100000", "환경설정", ""),
    array("", "기본환경설정", "$g4[admin_path]/config_form.php"),
    array("", "관리권한설정", "$g4[admin_path]/auth_list.php"),
    array("100300", "메일 테스트", "$g4[admin_path]/sendmail_test.php"),
    array("-"),
    array("100400", "버전정보", "$g4[admin_path]/version.php"),
    array("100500", "phpinfo()", "$g4[admin_path]/phpinfo.php"),
    array("-"),
    array("100600", "업그레이드", "$g4[admin_path]/upgrade.php"),
    array("100700", "복구/최적화", "$g4[admin_path]/repair.php"),
    array("100800", "세션 삭제", "$g4[admin_path]/session_delete.php"),
    array("-"),
    array("", "phpMyAdmin", "$g4[path]/$g4[phpmyadmin_dir]")
);
?>