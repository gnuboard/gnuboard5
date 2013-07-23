<?php
$sub_menu = "200200";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'd');

check_token();

$count = count($_POST['chk']);
if(!$count)
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");

for ($i=0; $i<$count; $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $sql = " select * from {$g4['point_table']} where po_id = '{$_POST['po_id'][$k]}' ";
    $row = sql_fetch($sql);

    if(!$row['po_id'])
        continue;

    // 삭제 내역이 차감일 경우 그에 해당하는 포인트 추가 지급하고
    // 아니면 만료되지 않았을 경우 사용포인트가 있으면 남은 포인트에 반영
    if($row['po_point'] < 0) {
        if($row['po_expire_date'] == '0000-00-00' || $row['po_expire_date'] >= G4_TIME_YMD) {
            $mb_id = $row['mb_id'];
            $po_point = abs($row['po_point']);

            delete_use_point($mb_id, $po_point);
        }
    } else {
        if($row['po_expired'] == 0 && $row['po_use_point'] > 0) {
            insert_use_point($row['mb_id'], $row['po_use_point'], $row['po_id']);
        }
    }

    // 포인트 내역삭제
    $sql = " delete from {$g4['point_table']} where po_id = '{$_POST['po_id'][$k]}' ";
    sql_query($sql);

    $sum_point = get_point_sum($_POST['mb_id'][$k]);

    $sql= " update {$g4['member_table']} set mb_point = '{$sum_point}' where mb_id = '{$_POST['mb_id'][$k]}' ";
    sql_query($sql);
}

goto_url('./point_list.php?'.$qstr);
?>
