<?
include_once("./_common.php");

function make_order($order) 
{
    return str_pad($order, 4, "_", STR_PAD_LEFT);
}

$ca_id = trim($_REQUEST['ca_id']);
$order = (int)$_REQUEST['order'];

// 1, 5, 9, 13, 17
$pos = strlen($ca_id) * 2 - 3;

$piece_order = make_order($order);

if ($pos == 1) {
    $sql = " update {$g4['yc4_category_table']} set ca_order = concat(left('$piece_order',4),mid(ca_order,$pos+4,20-($pos+4))) where ca_id like '$ca_id%' ";
} else {
    $sql = " update {$g4['yc4_category_table']} set ca_order = concat(mid(ca_order,1,$pos-1),'$piece_order',mid(ca_order,$pos+4,20-($pos+4-1))) where ca_id like '$ca_id%' ";
}
sql_query($sql, true);
?>