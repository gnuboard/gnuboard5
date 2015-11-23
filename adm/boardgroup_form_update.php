<?php
$sub_menu = "300200";
include_once('./_common.php');

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');

if ($is_admin != 'super' && $w == '') alert('최고관리자만 접근 가능합니다.');

check_admin_token();

if (!preg_match("/^([A-Za-z0-9_]{1,10})$/", $_POST['gr_id']))
    alert('그룹 ID는 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (10자 이내)');

if (!$gr_subject) alert('그룹 제목을 입력하세요.');

$sql_common = " gr_subject = '{$_POST['gr_subject']}',
                gr_device = '{$_POST['gr_device']}',
                gr_admin  = '{$_POST['gr_admin']}',
                gr_1_subj = '{$_POST['gr_1_subj']}',
                gr_2_subj = '{$_POST['gr_2_subj']}',
                gr_3_subj = '{$_POST['gr_3_subj']}',
                gr_4_subj = '{$_POST['gr_4_subj']}',
                gr_5_subj = '{$_POST['gr_5_subj']}',
                gr_6_subj = '{$_POST['gr_6_subj']}',
                gr_7_subj = '{$_POST['gr_7_subj']}',
                gr_8_subj = '{$_POST['gr_8_subj']}',
                gr_9_subj = '{$_POST['gr_9_subj']}',
                gr_10_subj = '{$_POST['gr_10_subj']}',
                gr_1 = '{$_POST['gr_1']}',
                gr_2 = '{$_POST['gr_2']}',
                gr_3 = '{$_POST['gr_3']}',
                gr_4 = '{$_POST['gr_4']}',
                gr_5 = '{$_POST['gr_5']}',
                gr_6 = '{$_POST['gr_6']}',
                gr_7 = '{$_POST['gr_7']}',
                gr_8 = '{$_POST['gr_8']}',
                gr_9 = '{$_POST['gr_9']}',
                gr_10 = '{$_POST['gr_10']}' ";
if (isset($_POST['gr_use_access']))
    $sql_common .= ", gr_use_access = '{$_POST['gr_use_access']}' ";
else
    $sql_common .= ", gr_use_access = '' ";

if ($w == '') {

    $sql = " select count(*) as cnt from {$g5['group_table']} where gr_id = '{$_POST['gr_id']}' ";
    $row = sql_fetch($sql);
    if ($row['cnt'])
        alert('이미 존재하는 그룹 ID 입니다.');

    $sql = " insert into {$g5['group_table']}
                set gr_id = '{$_POST['gr_id']}',
                     {$sql_common} ";
    sql_query($sql);

} else if ($w == "u") {

    $sql = " update {$g5['group_table']}
                set {$sql_common}
                where gr_id = '{$_POST['gr_id']}' ";
    sql_query($sql);

} else {
    alert('제대로 된 값이 넘어오지 않았습니다.');
}

goto_url('./boardgroup_form.php?w=u&amp;gr_id='.$gr_id.'&amp;'.$qstr);
?>
