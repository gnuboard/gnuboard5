<?
$sub_menu = "400640";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

for ($i=0; $i<count($_POST[it_id]); $i++) 
{
    $sql = " delete from $g4[yc4_event_item_table] 
              where ev_id = '$ev_id'
                and it_id = '{$_POST[it_id][$i]}' ";
    sql_query($sql);

    if ($_POST[ev_chk][$i]) 
    {
        $sql = "insert into $g4[yc4_event_item_table] 
                   set ev_id = '$ev_id',
                       it_id = '{$_POST[it_id][$i]}' ";
        sql_query($sql);
    }

}

goto_url("./itemeventlist.php?ev_id=$ev_id&sort1=$sort1&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&page=$page");
?>
