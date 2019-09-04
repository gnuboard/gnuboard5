<?php
$sub_menu = "100290";
include_once('./_common.php');

check_demo();

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

check_admin_token();

// 이전 메뉴정보 삭제
$sql = " delete from {$g5['menu_table']} ";
sql_query($sql);

$group_code = null;
$primary_code = null;
$count = count($_POST['code']);

for ($i=0; $i<$count; $i++)
{
    $_POST = array_map_deep('trim', $_POST);
    
    $_POST['me_link'][$i] = is_array($_POST['me_link']) ? clean_xss_tags($_POST['me_link'][$i], 1) : '';

    $code    = is_array($_POST['code']) ? strip_tags($_POST['code'][$i]) : '';
    $me_name = is_array($_POST['me_name']) ? strip_tags($_POST['me_name'][$i]) : '';
    $me_link = (preg_match('/^javascript/i', $_POST['me_link'][$i]) || preg_match('/script:/i', $_POST['me_link'][$i])) ? G5_URL : strip_tags($_POST['me_link'][$i]);
    
    if(!$code || !$me_name || !$me_link)
        continue;

    $sub_code = '';
    if($group_code == $code) {
        $sql = " select MAX(SUBSTRING(me_code,3,2)) as max_me_code
                    from {$g5['menu_table']}
                    where SUBSTRING(me_code,1,2) = '$primary_code' ";
        $row = sql_fetch($sql);

        $sub_code = base_convert($row['max_me_code'], 36, 10);
        $sub_code += 36;
        $sub_code = base_convert($sub_code, 10, 36);

        $me_code = $primary_code.$sub_code;
    } else {
        $sql = " select MAX(SUBSTRING(me_code,1,2)) as max_me_code
                    from {$g5['menu_table']}
                    where LENGTH(me_code) = '2' ";
        $row = sql_fetch($sql);

        $me_code = base_convert($row['max_me_code'], 36, 10);
        $me_code += 36;
        $me_code = base_convert($me_code, 10, 36);

        $group_code = $code;
        $primary_code = $me_code;
    }

    // 메뉴 등록
    $sql = " insert into {$g5['menu_table']}
                set me_code         = '".$me_code."',
                    me_name         = '".$me_name."',
                    me_link         = '".$me_link."',
                    me_target       = '".sql_real_escape_string(strip_tags($_POST['me_target'][$i]))."',
                    me_order        = '".sql_real_escape_string(strip_tags($_POST['me_order'][$i]))."',
                    me_use          = '".sql_real_escape_string(strip_tags($_POST['me_use'][$i]))."',
                    me_mobile_use   = '".sql_real_escape_string(strip_tags($_POST['me_mobile_use'][$i]))."' ";
    sql_query($sql);
}

goto_url('./menu_list.php');
?>
