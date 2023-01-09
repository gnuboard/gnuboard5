<?php
include_once('./_common.php');

$co_id = isset($_GET['co_id']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['co_id']) : 0;
$co_seo_title = isset($_GET['co_seo_title']) ? clean_xss_tags($_GET['co_seo_title'], 1, 1) : '';

//dbconfig파일에 $g5['content_table'] 배열변수가 있는지 체크
if( !isset($g5['content_table']) ){
    die('<meta charset="utf-8">관리자 모드에서 게시판관리->내용 관리를 먼저 확인해 주세요.');
}

// 내용
if($co_seo_title){
    $co = get_content_by_field($g5['content_table'], 'content', 'co_seo_title', generate_seo_title($co_seo_title));
    $co_id = isset($co['co_id']) ? $co['co_id'] : 0;
} else {
    $co = get_content_db($co_id);
}

if( ! (isset($co['co_seo_title']) && $co['co_seo_title']) && isset($co['co_id']) && $co['co_id'] ){
    seo_title_update($g5['content_table'], $co['co_id'], 'content');
}

if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/content.php');
    return;
}

if (! (isset($co['co_id']) && $co['co_id']))
    alert('등록된 내용이 없습니다.');

$g5['title'] = $co['co_subject'];

if ($co['co_include_head'] && is_include_path_check($co['co_include_head']))
    @include_once($co['co_include_head']);
else
    include_once('./_head.php');

// KVE-2019-0828 취약점 내용
$co['co_tag_filter_use'] = 1;
$str = conv_content($co['co_content'], $co['co_html'], $co['co_tag_filter_use']);

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
if(trim($co['co_skin']) == '')
    $co['co_skin'] = 'basic';

$content_skin_path = get_skin_path('content', $co['co_skin']);
$content_skin_url  = get_skin_url('content', $co['co_skin']);
$skin_file = $content_skin_path.'/content.skin.php';

if ($is_admin)
    echo run_replace('content_admin_button_html', '<div class="ctt_admin"><a href="'.G5_ADMIN_URL.'/contentform.php?w=u&amp;co_id='.$co_id.'" class="btn_admin btn"><span class="sound_only">내용 수정</span><i class="fa fa-cog fa-spin fa-fw"></i></a></div>', $co);
?>

<?php
if(is_file($skin_file)) {
    $himg = G5_DATA_PATH.'/content/'.$co_id.'_h';
    if (file_exists($himg)) // 상단 이미지
        echo run_replace('content_head_image_html', '<div id="ctt_himg" class="ctt_img"><img src="'.G5_DATA_URL.'/content/'.$co_id.'_h" alt=""></div>', $co);

    include($skin_file);

    $timg = G5_DATA_PATH.'/content/'.$co_id.'_t';
    if (file_exists($timg)) // 하단 이미지
        echo run_replace('content_tail_image_html', '<div id="ctt_timg" class="ctt_img"><img src="'.G5_DATA_URL.'/content/'.$co_id.'_t" alt=""></div>', $co);
} else {
    echo '<p>'.str_replace(G5_PATH.'/', '', $skin_file).'이 존재하지 않습니다.</p>';
}

if ($co['co_include_tail'] && is_include_path_check($co['co_include_tail']))
    @include_once($co['co_include_tail']);
else
    include_once('./_tail.php');