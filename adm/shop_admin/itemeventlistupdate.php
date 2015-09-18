<?php
$sub_menu = '500310';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

for ($i=0; $i<count($_POST['it_id']); $i++)
{
    $sql = " delete from {$g5['g5_shop_event_item_table']}
              where ev_id = '$ev_id'
                and it_id = '{$_POST['it_id'][$i]}' ";
    sql_query($sql);

    if ($_POST['ev_chk'][$i])
    {
        $sql = "insert into {$g5['g5_shop_event_item_table']}
                   set ev_id = '$ev_id',
                       it_id = '{$_POST['it_id'][$i]}' ";
        sql_query($sql);
    }

}

goto_url('./itemeventlist.php?ev_id='.$ev_id.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search.'&amp;page='.$page);
?>
