<?
$menu['menu100'] = array (
    array('100000', '환경설정',         $g4['admin_url'].'/config_form.php',   'config'),
    array('',       '기본환경설정',     $g4['admin_url'].'/config_form.php',   'cf_basic'),
    array('',       '관리권한설정',     $g4['admin_url'].'/auth_list.php',     'cf_auth'),
    array('100300', '메일 테스트',      $g4['admin_url'].'/sendmail_test.php', 'cf_mailtest'),
    //array('100400', '버전정보', $g4['admin_url'].'/version.php', 'cf_version'),
    array('100500', 'phpinfo()',        $g4['admin_url'].'/phpinfo.php',       'cf_phpinfo'),
    //array('100600', '업그레이드', $g4['admin_url'].'/upgrade.php', 'cf_upgrade'),
    //array('100700', '복구/최적화', $g4['admin_url'].'/repair.php', 'cf_repair'),
    array('100800', '세션파일 일괄삭제',$g4['admin_url'].'/session_file_delete.php', 'cf_session'),
    array('100900', '캐쉬파일 일괄삭제',$g4['admin_url'].'/cache_file_delete.php',   'cf_cache'),
    //array('', 'phpMyAdmin', ''.$g4['path'].'/'.$g4['phpmyadmin_dir'].'', 'cf_phpmyadmin')
);
?>