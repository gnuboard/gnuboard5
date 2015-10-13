<?php
$menu["menu900"] = array (
    array('900000', 'SMS 관리', ''.G5_SMS5_ADMIN_URL.'/config.php', 'sms5'),
    array('900100', 'SMS 기본설정', ''.G5_SMS5_ADMIN_URL.'/config.php', 'sms5_config'),
    array('900200', '회원정보업데이트', ''.G5_SMS5_ADMIN_URL.'/member_update.php', 'sms5_mb_update'),
    array('900300', '문자 보내기', ''.G5_SMS5_ADMIN_URL.'/sms_write.php', 'sms_write'),
    array('900400', '전송내역-건별', ''.G5_SMS5_ADMIN_URL.'/history_list.php', 'sms_history' , 1),
    array('900410', '전송내역-번호별', ''.G5_SMS5_ADMIN_URL.'/history_num.php', 'sms_history_num' , 1),
    array('900500', '이모티콘 그룹', ''.G5_SMS5_ADMIN_URL.'/form_group.php' , 'emoticon_group'),
    array('900600', '이모티콘 관리', ''.G5_SMS5_ADMIN_URL.'/form_list.php', 'emoticon_list'),
    array('900700', '휴대폰번호 그룹', ''.G5_SMS5_ADMIN_URL.'/num_group.php' , 'hp_group', 1),
    array('900800', '휴대폰번호 관리', ''.G5_SMS5_ADMIN_URL.'/num_book.php', 'hp_manage', 1),
    array('900900', '휴대폰번호 파일', ''.G5_SMS5_ADMIN_URL.'/num_book_file.php' , 'hp_file', 1)
);
?>