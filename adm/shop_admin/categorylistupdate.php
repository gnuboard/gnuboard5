<?
$sub_menu = "400200";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

for ($i=0; $i<count($_POST['ca_id']); $i++) {
    $sql = " update $g4[yc4_category_table]
                set ca_name       = '{$_POST[ca_name][$i]}',
                    ca_menu       = '{$_POST[ca_menu][$i]}',
                    ca_use        = '{$_POST[ca_use][$i]}',
                    ca_stock_qty  = '{$_POST[ca_stock_qty][$i]}'
              where ca_id = '{$_POST[ca_id][$i]}' ";
    sql_query($sql);

}

goto_url("./categorylist.php?page=$page");
?>
