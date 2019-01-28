<?php
$sub_menu = '400200';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

check_admin_token();

for ($i=0; $i<count($_POST['ca_id']); $i++)
{
    if ($_POST['ca_mb_id'][$i])
    {
        $sql = " select mb_id from {$g5['member_table']} where mb_id = '".sql_real_escape_string($_POST['ca_mb_id'][$i])."' ";
        $row = sql_fetch($sql);
        if (!$row['mb_id'])
            alert("\'{$_POST['ca_mb_id'][$i]}\' 은(는) 존재하는 회원아이디가 아닙니다.", "./categorylist.php?$qstr");
    }
    
    $check_files =  array();
    
    if( !empty($_POST['ca_skin'][$i]) ){
        $check_files[] = $_POST['ca_skin'][$i];
    }

    if( !empty($_POST['ca_mobile_skin'][$i]) ){
        $check_files[] = $_POST['ca_mobile_skin'][$i];
    }

    foreach( $check_files as $file ){
        if( empty($file) ) continue;

        if( ! is_include_path_check($file) ){
            alert('오류 : 데이터폴더가 포함된 path 또는 잘못된 path 를 포함할수 없습니다.');
        }

        $file_ext = pathinfo($file, PATHINFO_EXTENSION);

        if( ! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || ! preg_match('/^.*\.(php|htm|html)$/i', $file) ) {
            alert('스킨 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
        }
    }

    $sql = " update {$g5['g5_shop_category_table']}
                set ca_name             = '".sql_real_escape_string($_POST['ca_name'][$i])."',
                    ca_mb_id            = '".sql_real_escape_string($_POST['ca_mb_id'][$i])."',
                    ca_use              = '".sql_real_escape_string($_POST['ca_use'][$i])."',
                    ca_list_mod         = '".sql_real_escape_string($_POST['ca_list_mod'][$i])."',
                    ca_cert_use         = '".sql_real_escape_string($_POST['ca_cert_use'][$i])."',
                    ca_adult_use        = '".sql_real_escape_string($_POST['ca_adult_use'][$i])."',
                    ca_skin             = '".sql_real_escape_string($_POST['ca_skin'][$i])."',
                    ca_mobile_skin      = '".sql_real_escape_string($_POST['ca_mobile_skin'][$i])."',
                    ca_skin_dir         = '".sql_real_escape_string($_POST['ca_skin_dir'][$i])."',
                    ca_mobile_skin_dir  = '".sql_real_escape_string($_POST['ca_mobile_skin_dir'][$i])."',
                    ca_img_width        = '".sql_real_escape_string($_POST['ca_img_width'][$i])."',
                    ca_img_height       = '".sql_real_escape_string($_POST['ca_img_height'][$i])."',
                    ca_list_row         = '".sql_real_escape_string($_POST['ca_list_row'][$i])."',
                    ca_mobile_list_mod  = '".sql_real_escape_string($_POST['ca_mobile_list_mod'][$i])."',
                    ca_mobile_list_row  = '".sql_real_escape_string($_POST['ca_mobile_list_row'][$i])."'
              where ca_id = '".sql_real_escape_string($_POST['ca_id'][$i])."' ";

    sql_query($sql);

}

goto_url("./categorylist.php?$qstr");
?>
