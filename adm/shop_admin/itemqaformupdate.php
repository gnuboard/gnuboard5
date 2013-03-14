<?
$sub_menu = "400660";
include_once("./_common.php");

check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

$iq = sql_fetch(" select * from $g4[yc4_item_qa_table] where iq_id = '$iq_id' ");
if (!$iq[iq_id]) {
    alert("등록된 자료가 없습니다.");
}

$qstr = "page=$page&sort1=$sort1&sort2=$sort2";

if ($w == "u") {
    $sql = "update $g4[yc4_item_qa_table]
               set iq_subject = '$iq_subject',
                   iq_question = '$iq_question',
                   iq_answer = '$iq_answer'
             where iq_id = '$iq_id' ";
    sql_query($sql);

    goto_url("./itemqaform.php?w=$w&iq_id=$iq_id&$qstr");
} else if ($w == "d") {
    $sql = "delete from $g4[yc4_item_qa_table] where iq_id = '$iq_id' ";
    sql_query($sql);

    goto_url("./itemqalist.php?$qstr");
} else {
    alert();
}
?>
