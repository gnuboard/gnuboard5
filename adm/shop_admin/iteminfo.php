<?
include_once('./_common.php');
include_once(G4_LIB_PATH.'/iteminfo.lib.php');

$it_id = trim($_GET['it_id']);
if ($_GET['gubun']) {
    $gubun = $_GET['gubun'];
} else {
    $sql = " select ii_gubun from {$g4['shop_item_info_table']} where it_id = '$it_id' group by ii_gubun ";
    $row = sql_fetch($sql);
    $gubun = $row['ii_gubun'] ? $row['ii_gubun'] : "wear";
}

$null_text = "상품페이지 참고";

$g4['title'] = "상품요약정보 설정";
if($gubun)
    $g4['title'] .= ' : '.$item_info[$gubun]['title'];

include_once(G4_PATH.'/head.sub.php');
?>

<form id="fiteminfo" method="post" action="#" onsubmit="return fiteminfo_submit(this)">
<input type="hidden" name="it_id" value="<?=$it_id?>">

<div class="cbox">
    <h1>상품요약정보 설정</h1>
    <p>모든 필드를 반드시 입력하셔야 합니다.</p>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="gubun">상품군</label></th>
        <td>
            <?=help("상품군을 선택하면 자동으로 페이지가 전환됩니다.")?>
            <select id="gubun" name="gubun" onchange="location.href='?it_id=<?=$it_id?>&amp;gubun='+this.value;">
                <option value="">상품군을 선택하세요.</option>
                <?
                foreach($item_info as $key=>$value) {
                    $opt_value = $key;
                    $opt_text  = $value['title'];
                    echo '<option value="'.$opt_value.'" '.get_selected($gubun, $opt_value).'>'.$opt_text.'</option>'.PHP_EOL;
                }
                ?>
            </select>
        </td>
    </tr>
    <?
    $article = $item_info[$gubun]['article'];
    if ($article) {
        foreach($article as $key=>$value) {
            $el_name    = $key;
            $el_title   = $value[0];
            $el_example = $value[1];

            $sql = " select ii_value from {$g4['shop_item_info_table']} where it_id = '$it_id' and ii_gubun = '$gubun' and ii_article = '$key' ";
            $row = sql_fetch($sql);
            if ($row['ii_value']) $el_value = $row['ii_value'];
    ?>

    <tr>
        <th scope="row"><label for="<?=$el_name.$i?>"><?=$el_title?></label></th>
        <td>
        <input type="hidden" name="<?=$el_name?>[]" value="<?=$el_title?>">
        <? if ($el_example != "") echo help($el_example); ?>
        <input type="text" name="<?=$el_name?>[]" value="<?=$el_value?>" id="<?=$el_name.$i?>" required class="frm_input required" />
        </td>
    </tr>
    <?
        }
    }
    ?>
    <tr>
        <th>빈 칸 일괄채우기</th>
        <td>
            <?=help("상품페이지에 상품요약정보가 포함되어 있어 생략 가능한 경우 선택하십시오.")?>
            <label for="null">비어있는 칸을 &quot;<?=$null_text?>&quot;로 채우기</label>
            <input type="checkbox" id="null">
        </td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="입력" class="btn_submit">
        <button type="button" onclick="javascript:window.close()">창닫기</button>
    </div>
</div>

</form>

<script>
$(function(){
    $("#null").click(function(){
        var $f = $("#fiteminfo input[type=text], #fiteminfo textarea");
        if (this.checked) {
            $.each($f, function(){
                if ($(this).val() == "") {
                    $(this).val("<?=$null_text?>");
                }
            });
        } else {
            $.each($f, function(){
                if ($(this).val() == "<?=$null_text?>") {
                    $(this).val("");
                }
            });
        }
    });
});

function fiteminfo_submit(f)
{
    f.action = "./iteminfoupdate.php";
    return true;
}
</script>

<?
include_once(G4_PATH.'/tail.sub.php');
?>