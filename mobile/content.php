<?php
include_once('./_common.php');

if (! (isset($co['co_id']) && $co['co_id']))
    alert('등록된 내용이 없습니다.');

$g5['title'] = $co['co_subject'];
include_once('./_head.php');

$co_content = $co['co_mobile_content'] ? $co['co_mobile_content'] : $co['co_content'];
$str = conv_content($co_content, $co['co_html'], $co['co_tag_filter_use']);

// $src 를 $dst 로 변환
$src = $dst = array();
$src[] = "/{{쇼핑몰명}}|{{홈페이지제목}}/";
$dst[] = $config['cf_title'];
if(isset($default) && isset($default['de_admin_company_name'])){
    $src[] = "/{{회사명}}|{{상호}}/";
    $dst[] = isset($default['de_admin_company_name']) ? $default['de_admin_company_name'] : '';
    $src[] = "/{{대표자명}}/";
    $dst[] = isset($default['de_admin_company_owner']) ? $default['de_admin_company_owner'] : '';
    $src[] = "/{{사업자등록번호}}/";
    $dst[] = isset($default['de_admin_company_saupja_no']) ? $default['de_admin_company_saupja_no'] : '';
    $src[] = "/{{대표전화번호}}/";
    $dst[] = isset($default['de_admin_company_tel']) ? $default['de_admin_company_tel'] : '';
    $src[] = "/{{팩스번호}}/";
    $dst[] = isset($default['de_admin_company_fax']) ? $default['de_admin_company_fax'] : '';
    $src[] = "/{{통신판매업신고번호}}/";
    $dst[] = isset($default['de_admin_company_tongsin_no']) ? $default['de_admin_company_tongsin_no'] : '';
    $src[] = "/{{사업장우편번호}}/";
    $dst[] = isset($default['de_admin_company_zip']) ? $default['de_admin_company_zip'] : '';
    $src[] = "/{{사업장주소}}/";
    $dst[] = isset($default['de_admin_company_addr']) ? $default['de_admin_company_addr'] : '';
    $src[] = "/{{운영자명}}|{{관리자명}}/";
    $dst[] = isset($default['de_admin_name']) ? $default['de_admin_name'] : '';
    $src[] = "/{{운영자e-mail}}|{{관리자e-mail}}/i";
    $dst[] = isset($default['de_admin_email']) ? $default['de_admin_email'] : '';
    $src[] = "/{{정보관리책임자명}}/";
    $dst[] = isset($default['de_admin_info_name']) ? $default['de_admin_info_name'] : '';
    $src[] = "/{{정보관리책임자e-mail}}|{{정보책임자e-mail}}/i";
    $dst[] = isset($default['de_admin_info_email']) ? $default['de_admin_info_email'] : '';
}
$str = preg_replace($src, $dst, $str);

// 스킨경로
if(trim($co['co_mobile_skin']) == '')
    $co['co_mobile_skin'] = 'basic';

$content_skin_path = get_skin_path('content', $co['co_mobile_skin']);
$content_skin_url  = get_skin_url('content', $co['co_mobile_skin']);
$skin_file = $content_skin_path.'/content.skin.php';

if(is_file($skin_file)) {
    include($skin_file);
} else {
    echo '<p>'.str_replace(G5_PATH.'/', '', $skin_file).'이 존재하지 않습니다.</p>';
}

include_once('./_tail.php');