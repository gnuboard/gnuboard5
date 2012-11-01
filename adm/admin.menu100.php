<?
$menu['menu100'] = array (
    array('100000', '환경설정', '', 'config'),
    array('', '기본환경설정', ''.$g4['admin_path'].'/config_form.php', 'cf_basic'),
    array('', '관리권한설정', ''.$g4['admin_path'].'/auth_list.php', 'cf_auth'),
    array('100300', '메일 테스트', ''.$g4['admin_path'].'/sendmail_test.php', 'cf_mailtest'),
    //array('100400', '버전정보', ''.$g4['admin_path'].'/version.php', 'cf_version'),
    array('100500', 'phpinfo()', ''.$g4['admin_path'].'/phpinfo.php', 'cf_phpinfo'),
    //array('100600', '업그레이드', ''.$g4['admin_path'].'/upgrade.php', 'cf_upgrade'),
    //array('100700', '복구/최적화', ''.$g4['admin_path'].'/repair.php', 'cf_repair'),
    //array('100800', '세션 삭제', ''.$g4['admin_path'].'/session_delete.php', 'cf_delete'),
    array('100900','캐쉬삭제','#', 'cf_cache'),
    array('', 'phpMyAdmin', ''.$g4['path'].'/'.$g4['phpmyadmin_dir'].'', 'cf_phpmyadmin')
);
?>