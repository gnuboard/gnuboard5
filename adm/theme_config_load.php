<?php
$sub_menu = "100280";
include_once('./_common.php');

$data = array();
$data['error'] = '';

$data['error'] = auth_check_menu($auth, $sub_menu, 'w', true);
if($data['error'])
    die(json_encode($data));

if(!$config['cf_theme']) {
    $data['error'] = '사용 중인 테마가 없습니다.';
    die(json_encode($data));
}

$theme_dir = get_theme_dir();
if(!in_array($config['cf_theme'], $theme_dir)) {
    $data['error'] = $config['cf_theme'].' 테마는 설치된 테마가 아닙니다.';
    die(json_encode($data));
}

$type = $_POST['type'];
$arr_type = array('board', 'conf_skin', 'conf_member', 'shop_skin', 'shop_pc_index', 'shop_mobile_index', 'shop_etc', 'shop_event', 'shop_category');
if(!in_array($type, $arr_type)) {
    $data['error'] = '올바른 방법으로 이용해 주십시오.';
    die(json_encode($data));
}

if($type == 'board') {
    $keys = run_replace('theme_config_load_keys', array('bo_gallery_cols', 'bo_gallery_width', 'bo_gallery_height', 'bo_mobile_gallery_width', 'bo_mobile_gallery_height', 'bo_image_width'), $type);
    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if($tconfig[$val]) {
            $data[$val] = (int)preg_replace('#[^0-9]#', '', $tconfig[$val]);
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 게시판 이미지 설정이 없습니다.';
} else if($type == 'conf_skin') {
    $keys = run_replace('theme_config_load_keys', array('cf_new_skin', 'cf_mobile_new_skin', 'cf_search_skin', 'cf_mobile_search_skin', 'cf_connect_skin', 'cf_mobile_connect_skin', 'cf_faq_skin', 'cf_mobile_faq_skin'), $type);

    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if($tconfig[$val]) {
            $data[$val] = preg_match('#^theme/.+$#', $tconfig[$val]) ? $tconfig[$val] : 'theme/'.$tconfig[$val];
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 기본환경 스킨 설정이 없습니다.';
} else if($type == 'conf_member') {
    $keys = run_replace('theme_config_load_keys', array('cf_member_skin', 'cf_mobile_member_skin'), $type);

    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if($tconfig[$val]) {
            $data[$val] = preg_match('#^theme/.+$#', $tconfig[$val]) ? $tconfig[$val] : 'theme/'.$tconfig[$val];
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 기본환경 회원스킨 설정이 없습니다.';
} else if($type == 'shop_skin') {
    $keys = run_replace('theme_config_load_keys', array('de_shop_skin', 'de_shop_mobile_skin'), $type);

    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if($tconfig[$val]) {
            $data[$val] = preg_match('#^theme/.+$#', $tconfig[$val]) ? $tconfig[$val] : 'theme/'.$tconfig[$val];
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 쇼핑몰 스킨 설정이 없습니다.';
} else if($type == 'shop_pc_index') {
    $keys = array();
    for($i=1; $i<=5; $i++) {
        $keys[] = 'de_type'.$i.'_list_use';
        $keys[] = 'de_type'.$i.'_list_skin';
        $keys[] = 'de_type'.$i.'_list_mod';
        $keys[] = 'de_type'.$i.'_list_row';
        $keys[] = 'de_type'.$i.'_img_width';
        $keys[] = 'de_type'.$i.'_img_height';
    }
	
	$keys = run_replace('theme_config_load_keys', $keys, $type);
    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if(strlen($tconfig[$val])) {
            $data[$val] = trim($tconfig[$val]);
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 테마 설정이 없습니다.';
} else if($type == 'shop_mobile_index') {
    $keys = array();
    for($i=1; $i<=5; $i++) {
        $keys[] = 'de_mobile_type'.$i.'_list_use';
        $keys[] = 'de_mobile_type'.$i.'_list_skin';
        $keys[] = 'de_mobile_type'.$i.'_list_mod';
        $keys[] = 'de_mobile_type'.$i.'_list_row';
        $keys[] = 'de_mobile_type'.$i.'_img_width';
        $keys[] = 'de_mobile_type'.$i.'_img_height';
    }
	
	$keys = run_replace('theme_config_load_keys', $keys, $type);
    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if(strlen($tconfig[$val])) {
            $data[$val] = trim($tconfig[$val]);
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 테마 설정이 없습니다.';
} else if($type == 'shop_etc') {
    $keys = run_replace('theme_config_load_keys', array('de_rel_list_use', 'de_rel_list_skin', 'de_rel_list_mod', 'de_rel_img_width', 'de_rel_img_height', 'de_mobile_rel_list_use', 'de_mobile_rel_list_skin', 'de_mobile_rel_list_mod', 'de_mobile_rel_img_width', 'de_mobile_rel_img_height', 'de_search_list_skin', 'de_search_list_mod', 'de_search_list_row', 'de_search_img_width', 'de_search_img_height', 'de_mobile_search_list_skin', 'de_mobile_search_list_mod', 'de_mobile_search_list_row', 'de_mobile_search_img_width', 'de_mobile_search_img_height', 'de_mimg_width', 'de_mimg_height', 'de_listtype_list_mod', 'de_listtype_list_row', 'de_mobile_listtype_list_mod', 'de_mobile_listtype_list_row'), $type);

    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if(strlen($tconfig[$val])) {
            $data[$val] = trim($tconfig[$val]);
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 테마 설정이 없습니다.';
} else if($type == 'shop_event') {
    $keys = run_replace('theme_config_load_keys', array('ev_skin', 'ev_img_width', 'ev_img_height', 'ev_list_mod', 'ev_list_row', 'ev_mobile_skin', 'ev_mobile_img_width', 'ev_mobile_img_height', 'ev_mobile_list_mod', 'ev_mobile_list_row'), $type);

    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if(strlen($tconfig[$val])) {
            $data[$val] = trim($tconfig[$val]);
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 테마 설정이 없습니다.';
} else if($type == 'shop_category') {
    $keys = run_replace('theme_config_load_keys', array('ca_skin', 'ca_img_width', 'ca_img_height', 'ca_list_mod', 'ca_list_row', 'ca_mobile_skin', 'ca_mobile_img_width', 'ca_mobile_img_height', 'ca_mobile_list_mod', 'ca_mobile_list_row'), $type);

    $tconfig = get_theme_config_value($config['cf_theme'], implode(',', $keys));

    $i = 0;
    foreach($keys as $val) {
        if(strlen($tconfig[$val])) {
            $data[$val] = trim($tconfig[$val]);
            $i++;
        }
    }

    if($i == 0)
        $data['error'] = '적용할 테마 설정이 없습니다.';
}

die(json_encode($data));
