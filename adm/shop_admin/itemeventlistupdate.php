<?php
$sub_menu = '500310';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

$post_it_id_count = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? count($_POST['it_id']) : 0;

for ($i=0; $i<$post_it_id_count; $i++)
{
    $iit_id = isset($_POST['it_id'][$i]) ? preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$i]) : '';

    $sql = " delete from {$g5['g5_shop_event_item_table']}
              where ev_id = '$ev_id'
                and it_id = '{$iit_id}' ";
    sql_query($sql);

    if (isset($_POST['ev_chk'][$i]) && $_POST['ev_chk'][$i])
    {
        $sql = "insert into {$g5['g5_shop_event_item_table']}
                   set ev_id = '$ev_id',
                       it_id = '{$iit_id}' ";
        sql_query($sql);
    }

}

goto_url('./itemeventlist.php?ev_id='.$ev_id.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search.'&amp;page='.$page);