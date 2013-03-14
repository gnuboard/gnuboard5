<?
include_once("./_common.php");
include_once("$g4[path]/lib/iteminfo.lib.php");
include_once("$g4[path]/head.sub.php");

$it_id = trim($_GET['it_id']);
if ($_GET['gubun']) {
    $gubun = $_GET['gubun'];
} else {
    $sql = " select ii_gubun from {$g4['yc4_item_info_table']} where it_id = '$it_id' group by ii_gubun ";
    $row = sql_fetch($sql);
    $gubun = $row['ii_gubun'] ? $row['ii_gubun'] : "wear";
}

$null_text = "상품페이지 참고";
?>
<style>
.confirm {text-align:center}
.confirm input {padding:3px}
</style>

<form id="fiteminfo" method="post" action="#" onsubmit="return fiteminfo_submit(this)">
<input type="hidden" name="it_id" value="<?=$it_id?>">
<div style="width:95%;padding:10px">
<div style="float:left;"><?=subtitle("요약상품정보")?></div>
<div style="float:right;">(모든필드 필수입력)</div>
<table width=100% cellpadding=0 cellspacing=0 border=0>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#FFFFFF></colgroup>
<tbody>
<tr><td colspan=2 height=2 bgcolor=0E87F9></td></tr>
<tr><td colspan=2 height=5></td></tr>
<tr class=ht>
    <td style='padding:3px;' valign='top' width='25%'><b>상품군</b></td>
    <td style='padding:3px;' valign='top'>
        <div style="float:left;">
        <select id="gubun" name="gubun" onchange="location.href='?it_id=<?=$it_id?>&amp;gubun='+this.value;">
        <option value="">상품군을 선택하세요.</option>
        <?
        foreach($item_info as $key=>$value) {
            $opt_value = $key;
            $opt_text  = $value['title'];
            echo "<option value='$opt_value'>$opt_text</option>\n";
        }
        ?>
        </select>
        <script>document.getElementById("gubun").value="<?=$gubun?>";</script>
        </div>
        <div style="float:right;"><label><input type="checkbox" id="null" />비어있는 필드를 "<?=$null_text?>"로 채우기</label></div>
    </td>
</tr>
<?
$article = $item_info[$gubun]['article'];
if ($article) {
    foreach($article as $key=>$value) {
        $el_name    = $key;
        $el_title   = $value[0];
        $el_example = $value[1];

        $sql = " select ii_value from {$g4['yc4_item_info_table']} where it_id = '$it_id' and ii_gubun = '$gubun' and ii_article = '$key' ";
        $row = sql_fetch($sql);
        if ($row['ii_value']) $el_value = $row['ii_value'];

        echo "<tr class='ht'>\n";
        echo "<td style='padding:3px;' valign='top'><b>$el_title</b></td>\n";
        echo "<td style='padding:3px;' valign='top'>";
        echo "<input type='hidden' name='{$el_name}[]' value='$el_title' />";
        echo "<input type='text' name='{$el_name}[]' value='$el_value' class='ed' style='width:99%;' required itemname='$el_title' />";
        if ($el_example != "") {
            echo "<p style=\"margin:2px 0;padding:0\">$el_example</p>";
        }
        echo "</td>\n";
        echo "</tr>\n";
    } 
}
?>
</tbody>
</table>

<p class="confirm">
    <input type="submit" value="입력">
    <input type="button" value="창닫기" onclick="javascript:window.close()">
</p>
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
include_once("$g4[path]/tail.sub.php");
?>