<?
include_once('./_common.php');
include_once(G4_LIB_PATH.'/iteminfo.lib.php');

// 기존의 상품요약정보를 삭제하고 다시 만든다.
sql_query(" delete from {$g4['shop_item_info_table']} where it_id = '{$_POST['it_id']}' ");

$gubun = "";
foreach ($_POST as $key=>$value) {
    if ($key == "it_id") continue;
    if ($key == "gubun") {
        $gubun = $value;
        continue;
    }

    $sql = " insert {$g4['shop_item_info_table']}
                set it_id = '{$_POST['it_id']}',
                    ii_gubun = '$gubun',
                    ii_article = '$key',
                    ii_title = '$value[0]',
                    ii_value = '$value[1]' ";
    sql_query($sql);
}

$item_info_gubun = item_info_gubun($gubun);
$item_info_gubun .= $item_info_gubun ? " 등록됨" : "";

include_once(G4_PATH.'/head.sub.php');
?>
<script type="text/javascript">
    opener.document.getElementById("item_info_gubun").innerHTML = "<?=$item_info_gubun?>";
    window.close();
</script>
<?
include_once(G4_PATH.'/tail.sub.php');
?>