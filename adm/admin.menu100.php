<?
$menu['menu100'] = array (
    array('100000', '환경설정',         G4_ADMIN_URL.'/config_form.php',   'config'),
    array('',       '기본환경설정',     G4_ADMIN_URL.'/config_form.php',   'cf_basic'),
    array('',       '관리권한설정',     G4_ADMIN_URL.'/auth_list.php',     'cf_auth'),
    array('100300', '메일 테스트',      G4_ADMIN_URL.'/sendmail_test.php', 'cf_mailtest'),
    //array('100400', '버전정보', G4_ADMIN_URL.'/version.php', 'cf_version'),
    array('100500', 'phpinfo()',        G4_ADMIN_URL.'/phpinfo.php',       'cf_phpinfo'),
    //array('100600', '업그레이드', G4_ADMIN_URL.'/upgrade.php', 'cf_upgrade'),
    //array('100700', '복구/최적화', G4_ADMIN_URL.'/repair.php', 'cf_repair'),
    array('100800', '세션파일 일괄삭제',G4_ADMIN_URL.'/session_file_delete.php', 'cf_session'),
    array('100900', '캐쉬파일 일괄삭제',G4_ADMIN_URL.'/cache_file_delete.php',   'cf_cache'),
    //array('', 'phpMyAdmin', ''.$g4['path'].'/'.$g4['phpmyadmin_dir'].'', 'cf_phpmyadmin')
);
?>