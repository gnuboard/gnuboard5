<?php
$menu['menu300'] = array (
    array('300000', '게시판관리', ''.G5_ADMIN_URL.'/board_list.php', 'board'),
    array('300100', '게시판관리', ''.G5_ADMIN_URL.'/board_list.php', 'bbs_board'),
    array('300200', '게시판그룹관리', ''.G5_ADMIN_URL.'/boardgroup_list.php', 'bbs_group'),
    array('300300', '인기검색어관리', ''.G5_ADMIN_URL.'/popular_list.php', 'bbs_poplist', 1),
    array('300400', '인기검색어순위', ''.G5_ADMIN_URL.'/popular_rank.php', 'bbs_poprank', 1),
    array('300500', '1:1문의설정', ''.G5_ADMIN_URL.'/qa_config.php', 'qa'),
    array('300600', '내용관리', G5_ADMIN_URL.'/contentlist.php', 'scf_contents', 1),
    array('300700', 'FAQ관리', G5_ADMIN_URL.'/faqmasterlist.php', 'scf_faq', 1),
);
?>