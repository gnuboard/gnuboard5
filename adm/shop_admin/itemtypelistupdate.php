<?
$sub_menu = "400610";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

for ($i=0; $i<count($_POST[it_id]); $i++) 
{
    $sql = "update $g4[yc4_item_table] 
               set it_type1 = '{$_POST[it_type1][$i]}',
                   it_type2 = '{$_POST[it_type2][$i]}',
                   it_type3 = '{$_POST[it_type3][$i]}',
                   it_type4 = '{$_POST[it_type4][$i]}',
                   it_type5 = '{$_POST[it_type5][$i]}'
             where it_id = '{$_POST[it_id][$i]}' ";
    sql_query($sql);
}

//goto_url("./itemtypelist.php?sort1=$sort&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&page=$page");
goto_url("itemtypelist.php?sca=$sca&sst=$sst&sod=$sod&sfl=$sfl&stx=$stx&page=$page");
?>
