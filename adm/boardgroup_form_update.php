<?php
$sub_menu = "300200";
include_once('./_common.php');

if ($w == 'u')
    check_demo();

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super' && $w == '') alert('최고관리자만 접근 가능합니다.');

check_admin_token();

$gr_id = isset($_POST['gr_id']) ? $_POST['gr_id'] : '';

if (!preg_match("/^([A-Za-z0-9_]{1,10})$/", $gr_id))
    alert('그룹 ID는 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (10자 이내)');

if (!$gr_subject) alert('그룹 제목을 입력하세요.');

$posts = array();

$check_keys = array(
'gr_subject'=>'',
'gr_device'=>'',
'gr_admin'=>'',
);

for($i=1;$i<=10;$i++){
    $check_keys['gr_'.$i.'_subj'] = isset($_POST['gr_'.$i.'_subj']) ? $_POST['gr_'.$i.'_subj'] : '';
    $check_keys['gr_'.$i] = isset($_POST['gr_'.$i]) ? $_POST['gr_'.$i] : '';
}

foreach( $check_keys as $key=>$value ){
    if( $key === 'gr_subject' ){
        $posts[$key] = isset($_POST[$key]) ? strip_tags(clean_xss_attributes($_POST[$key])) : '';
    } else {
        $posts[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
    }
}

$sql_common = " gr_subject = '{$posts['gr_subject']}',
                gr_device = '{$posts['gr_device']}',
                gr_admin  = '{$posts['gr_admin']}',
                gr_1_subj = '{$posts['gr_1_subj']}',
                gr_2_subj = '{$posts['gr_2_subj']}',
                gr_3_subj = '{$posts['gr_3_subj']}',
                gr_4_subj = '{$posts['gr_4_subj']}',
                gr_5_subj = '{$posts['gr_5_subj']}',
                gr_6_subj = '{$posts['gr_6_subj']}',
                gr_7_subj = '{$posts['gr_7_subj']}',
                gr_8_subj = '{$posts['gr_8_subj']}',
                gr_9_subj = '{$posts['gr_9_subj']}',
                gr_10_subj = '{$posts['gr_10_subj']}',
                gr_1 = '{$posts['gr_1']}',
                gr_2 = '{$posts['gr_2']}',
                gr_3 = '{$posts['gr_3']}',
                gr_4 = '{$posts['gr_4']}',
                gr_5 = '{$posts['gr_5']}',
                gr_6 = '{$posts['gr_6']}',
                gr_7 = '{$posts['gr_7']}',
                gr_8 = '{$posts['gr_8']}',
                gr_9 = '{$posts['gr_9']}',
                gr_10 = '{$posts['gr_10']}' ";
if (isset($_POST['gr_use_access']))
    $sql_common .= ", gr_use_access = '{$_POST['gr_use_access']}' ";
else
    $sql_common .= ", gr_use_access = '' ";

if ($w == '') {

    $sql = " select count(*) as cnt from {$g5['group_table']} where gr_id = '{$gr_id}' ";
    $row = sql_fetch($sql);
    if ($row['cnt'])
        alert('이미 존재하는 그룹 ID 입니다.');

    $sql = " insert into {$g5['group_table']}
                set gr_id = '{$gr_id}',
                     {$sql_common} ";
    sql_query($sql);

} else if ($w == "u") {

    $sql = " update {$g5['group_table']}
                set {$sql_common}
                where gr_id = '{$gr_id}' ";
    sql_query($sql);

} else {
    alert('제대로 된 값이 넘어오지 않았습니다.');
}

run_event('admin_boardgroup_form_update', $gr_id, $w);

goto_url('./boardgroup_form.php?w=u&amp;gr_id='.$gr_id.'&amp;'.$qstr);