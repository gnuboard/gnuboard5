<?
$sub_menu = "400200";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

for ($i=0; $i<count($_POST[ca_id]); $i++) 
{
    if ($_POST[ca_mb_id][$i])
    {
        $sql = " select mb_id from $g4[member_table] where mb_id = '{$_POST[ca_mb_id][$i]}' ";
        $row = sql_fetch($sql);
        if (!$row[mb_id])
            alert("\'{$_POST[ca_mb_id][$i]}\' 은(는) 존재하는 회원아이디가 아닙니다.", "./categorylist.php?page=$page&sort1=$sort1&sort2=$sort2");
    }

    $sql = " update $g4[yc4_category_table]
                set ca_name       = '{$_POST[ca_name][$i]}',
                    ca_mb_id      = '{$_POST[ca_mb_id][$i]}',
                    ca_use        = '{$_POST[ca_use][$i]}',
                    ca_stock_qty  = '{$_POST[ca_stock_qty][$i]}'
              where ca_id = '{$_POST[ca_id][$i]}' ";
    sql_query($sql);

}

goto_url("./categorylist.php?page=$page&sort1=$sort1&sort2=$sort2");
?>
