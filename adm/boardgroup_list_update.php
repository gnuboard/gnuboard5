<?
$sub_menu = "300200";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'w');

for ($i=0; $i<count($_POST['chk']); $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $sql = " update {$g4['group_table']}
                set gr_subject    = '{$_POST['gr_subject'][$k]}',
                    gr_use        = '{$_POST['gr_use'][$k]}',
                    gr_admin      = '{$_POST['gr_admin'][$k]}',
                    gr_use_access = '{$_POST['gr_use_access'][$k]}'
              where gr_id         = '{$_POST['gr_id'][$k]}' ";
    if ($is_admin != 'super')
        $sql .= " and gr_admin    = '{$_POST['gr_admin'][$k]}' ";
    sql_query($sql);
}

goto_url('./boardgroup_list.php?'.$qstr);
?>
