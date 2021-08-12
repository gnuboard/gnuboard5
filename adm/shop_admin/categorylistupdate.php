<?php
$sub_menu = '400200';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$post_ca_id_count = (isset($_POST['ca_id']) && is_array($_POST['ca_id'])) ? count($_POST['ca_id']) : 0;

for ($i=0; $i<$post_ca_id_count; $i++)
{
    $str_ca_mb_id = isset($_POST['ca_mb_id'][$i]) ? strip_tags(clean_xss_attributes($_POST['ca_mb_id'][$i])) : '';

    if ($str_ca_mb_id)
    {
        $sql = " select mb_id from {$g5['member_table']} where mb_id = '".sql_real_escape_string($str_ca_mb_id)."' ";
        $row = sql_fetch($sql);
        if (!$row['mb_id'])
            alert("\'{$str_ca_mb_id}\' 은(는) 존재하는 회원아이디가 아닙니다.", "./categorylist.php?$qstr");
    }
    
    $check_files =  array();
    
    if( !empty($_POST['ca_skin'][$i]) ){
        $check_files[] = $_POST['ca_skin'][$i];
    }

    if( !empty($_POST['ca_mobile_skin'][$i]) ){
        $check_files[] = $_POST['ca_mobile_skin'][$i];
    }

    if( !empty($_POST['ca_skin_dir'][$i]) ){
        if( preg_match('#\.+(\/|\\\)#', $_POST['ca_skin_dir'][$i]) ){
            alert('PC 스킨폴더명에 포함될수 없는 문자가 들어있습니다.');
        }
    }

    if( !empty($_POST['ca_mobile_skin_dir'][$i]) ){
        if( preg_match('#\.+(\/|\\\)#', $_POST['ca_mobile_skin_dir'][$i]) ){
            alert('모바일 스킨폴더명에 포함될수 없는 문자가 들어있습니다.');
        }
    }

    foreach( $check_files as $file ){
        if( empty($file) ) continue;

        if( preg_match('#\.+(\/|\\\)#', $file) ){
            alert('스킨파일명에 포함될수 없는 문자가 들어있습니다.');
        }

        if( ! is_include_path_check($file) ){
            alert('오류 : 데이터폴더가 포함된 path 또는 잘못된 path 를 포함할수 없습니다.');
        }

        $file_ext = pathinfo($file, PATHINFO_EXTENSION);

        if( ! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || ! preg_match('/^.*\.(php|htm|html)$/i', $file) ) {
            alert('스킨 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
        }
    }
    
    $p_ca_name = is_array($_POST['ca_name']) ? strip_tags(clean_xss_attributes($_POST['ca_name'][$i])) : '';
    
    $posts = array();

    $check_keys = array('ca_mb_id', 'ca_id', 'ca_use', 'ca_list_mod', 'ca_cert_use', 'ca_adult_use', 'ca_skin', 'ca_mobile_skin', 'ca_skin_dir', 'ca_mobile_skin_dir', 'ca_img_width', 'ca_img_height', 'ca_list_row', 'ca_mobile_list_mod', 'ca_mobile_list_row');

    foreach($check_keys as $key){
        $posts[$key] = (isset($_POST[$key]) && isset($_POST[$key][$i])) ? $_POST[$key][$i] : '';
    }
    
    $sql = " update {$g5['g5_shop_category_table']}
                set ca_name             = '".$p_ca_name."',
                    ca_mb_id            = '".sql_real_escape_string(strip_tags(clean_xss_attributes($posts['ca_mb_id'])))."',
                    ca_use              = '".sql_real_escape_string(strip_tags($posts['ca_use']))."',
                    ca_list_mod         = '".sql_real_escape_string(strip_tags($posts['ca_list_mod']))."',
                    ca_cert_use         = '".sql_real_escape_string(strip_tags($posts['ca_cert_use']))."',
                    ca_adult_use        = '".sql_real_escape_string(strip_tags($posts['ca_adult_use']))."',
                    ca_skin             = '".sql_real_escape_string(strip_tags($posts['ca_skin']))."',
                    ca_mobile_skin      = '".sql_real_escape_string(strip_tags($posts['ca_mobile_skin']))."',
                    ca_skin_dir         = '".sql_real_escape_string(strip_tags($posts['ca_skin_dir']))."',
                    ca_mobile_skin_dir  = '".sql_real_escape_string(strip_tags($posts['ca_mobile_skin_dir']))."',
                    ca_img_width        = '".sql_real_escape_string(strip_tags($posts['ca_img_width']))."',
                    ca_img_height       = '".sql_real_escape_string(strip_tags($posts['ca_img_height']))."',
                    ca_list_row         = '".sql_real_escape_string(strip_tags($posts['ca_list_row']))."',
                    ca_mobile_list_mod  = '".sql_real_escape_string(strip_tags($posts['ca_mobile_list_mod']))."',
                    ca_mobile_list_row  = '".sql_real_escape_string(strip_tags($posts['ca_mobile_list_row']))."'
              where ca_id = '".sql_real_escape_string($posts['ca_id'])."' ";

    sql_query($sql);

}

goto_url("./categorylist.php?$qstr");