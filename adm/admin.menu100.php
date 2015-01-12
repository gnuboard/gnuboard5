<?php
$menu['menu100'] = array (
    array('100000', '환경설정', G5_ADMIN_URL.'/config_form.php',   'config'),
    array('', '기본환경설정', G5_ADMIN_URL.'/config_form.php',   'cf_basic'),
    array('', '관리권한설정', G5_ADMIN_URL.'/auth_list.php',     'cf_auth'),
    array('', '메뉴설정', G5_ADMIN_URL.'/menu_list.php',     'cf_menu', 1),
    array('100300', '메일 테스트', G5_ADMIN_URL.'/sendmail_test.php', 'cf_mailtest'),
    array('100310', '팝업레이어관리', G5_ADMIN_URL.'/newwinlist.php', 'scf_poplayer'),
    array('100800', '세션파일 일괄삭제',G5_ADMIN_URL.'/session_file_delete.php', 'cf_session', 1),
    array('100900', '캐시파일 일괄삭제',G5_ADMIN_URL.'/cache_file_delete.php',   'cf_cache', 1),
    array('100910', '캡챠파일 일괄삭제',G5_ADMIN_URL.'/captcha_file_delete.php',   'cf_captcha', 1),
    array('100920', '썸네일파일 일괄삭제',G5_ADMIN_URL.'/thumbnail_file_delete.php',   'cf_thumbnail', 1),
    array('100500', 'phpinfo()',        G5_ADMIN_URL.'/phpinfo.php',       'cf_phpinfo'),
    array('100400', '부가서비스', G5_ADMIN_URL.'/service.php', 'cf_service')
);
?>