<?
$sub_menu = "400200";
include_once("./_common.php");
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

$category_path = "{$g4['path']}/data/category";

$sql_common = " from {$g4['shop_category_table']} ";
if ($is_admin != 'super')
    $sql_common .= " where ca_mb_id = '{$member['mb_id']}' ";

if ($w == "")
{
    if ($is_admin != 'super' && !$ca_id)
        alert("최고관리자만 1단계 분류를 추가할 수 있습니다.");

    $len = strlen($ca_id);
    if ($len == 10)
        alert("분류를 더 이상 추가할 수 없습니다.\\n\\n5단계 분류까지만 가능합니다.");

    $len2 = $len + 1;

    $sql = " select MAX(SUBSTRING(ca_id,$len2,2)) as max_subid from {$g4['shop_category_table']} where SUBSTRING(ca_id,1,$len) = '$ca_id' ";
    $row = sql_fetch($sql);

    if ($row['max_subid']) {
        $subid = base_convert($row['max_subid'], 36, 10);
        $subid += 36;
    } else {
        $subid = 36;
    }
    if ($subid >= 36 * 36)
    {
        //alert("분류를 더 이상 추가할 수 없습니다.");
        // 빈상태로
        $subid = "  ";
    }
    $subid = base_convert($subid, 10, 36);
    $subid = substr("00" . $subid, -2);
    $subid = $ca_id . $subid;

    $sublen = strlen($subid);

    if ($ca_id) // 2단계이상 분류
    {
<<<<<<< HEAD
        $sql = " select * from {$g4['shop_category_table']} where ca_id = '$ca_id' ";
=======
        $sql = " select * from $g4[shop_category_table] where ca_id = '$ca_id' ";
>>>>>>> ddb1dec36c49f24441636f5e3dcb1e1db20a0d2b
        $ca = sql_fetch($sql);
        $html_title = $ca['ca_name'] . " 하위분류추가";
        $ca['ca_name'] = "";
    }
    else // 1단계 분류
    {
        $html_title = "1단계분류추가";
        $ca['ca_use'] = 1;
        $ca['ca_menu'] = 1;
        $ca['ca_explan_html'] = 1;
        $ca['ca_img_width']  = $default['de_simg_width'];
        $ca['ca_img_height'] = $default['de_simg_height'];
        $ca['ca_list_mod'] = 4;
        $ca['ca_list_row'] = 5;
        $ca['ca_stock_qty'] = 99999;
    }
    $ca[ca_skin] = "list.skin.10.php";
}
else if ($w == "u")
{
<<<<<<< HEAD
    $sql = " select * from {$g4['shop_category_table']} where ca_id = '$ca_id' ";
=======
    $sql = " select * from $g4[shop_category_table] where ca_id = '$ca_id' ";
>>>>>>> ddb1dec36c49f24441636f5e3dcb1e1db20a0d2b
    $ca = sql_fetch($sql);
    if (!$ca[ca_id])
        alert("자료가 없습니다.");

    $html_title = $ca['ca_name'] . " 수정";
    $ca['ca_name'] = get_text($ca['ca_name']);
}

$qstr = "page=$page&amp;sort1=$sort1&amp;sort2=$sort2";

$g4['title'] = $html_title;
include_once(G4_ADMIN_PATH."/admin.head.php");
?>

<form id="fcategoryform" name="fcategoryform" method="post" action="./categoryformupdate.php" enctype="multipart/form-data" onsubmit="return fcategoryformcheck(this);">
<input type="hidden" name="codedup" value="<?=$default['de_code_dup_use']?>">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="sort1" value="<?=$sort1?>">
<input type="hidden" name="sort2" value="<?=$sort2?>">

<table>
<caption>기본 입력</caption>
<tbody>
<tr>
    <th scope="row">분류코드</th>
    <td colspan="3">
    <? if ($w == "") { ?>
        <?=help("자동으로 보여지는 분류코드를 사용하시길 권해드리지만 직접 입력한 값으로도 사용할 수 있습니다.\n분류코드는 나중에 수정이 되지 않으므로 신중하게 결정하여 사용하십시오.\n\n분류코드는 2자리씩 10자리를 사용하여 5단계를 표현할 수 있습니다.\n0~z까지 입력이 가능하며 한 분류당 최대 1296가지를 표현할 수 있습니다.\n그러므로 총 3656158440062976가지의 분류를 사용할 수 있습니다.");?>
        <input type="text" id="ca_id" name="ca_id" size="<?=$sublen?>" maxlength="<?=$sublen?>" minlength="<?=$sublen?>" class="nospace alnum" value="<?=$subid?>" title="분류코드">
        <? if ($default['de_code_dup_use']) { ?><a href="javascript:;" onclick="codedupcheck(document.getElementById('ca_id').value)">코드 중복검사</a><? } ?>
    <? } else { ?>
        <input type="hidden" id="ca_id" name="ca_id" value="<?=$ca['ca_id']?>">
        <?=$ca['ca_id']?>
        <? echo icon("미리보기", G4_SHOP_URL."/list.php?ca_id=$ca_id"); ?>
        <? echo "<a href=\"./categoryform.php?ca_id=$ca_id&amp;$qstr\" title=\"하위분류 추가\"><img src=\"".G4_ADMIN_URL."/img/icon_insert.gif\" alt=\"\"></a>"; ?>
        <a href="./itemlist.php?sca=<?=$ca['ca_id']?>">상품리스트</a>
    <? } ?>
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_name">분류명<strong class="sound_only">필수</strong></label></th>
    <td colspan="3"><input type="text" id="ca_name" name="ca_name" value="<?=$ca['ca_name']?>" size="38" required></td>
</tr>
<tr>
    <th scope="row"><? if($is_admin == 'super') {?><label for="ca_mb_id"><? } ?>관리 회원아이디<? if($is_admin == 'super') {?></label><? } ?></th>
    <td colspan="3">
        <?
        if ($is_admin == 'super')
            echo "<input type=\"text\" id=\"ca_mb_id\" name=\"ca_mb_id\" value=\"{$ca['ca_mb_id']}\"maxlength=\"20\">";
        else
            echo "<input type=\"hidden\" id=\"ca_mb_id\" name=\"ca_mb_id\" value=\"{$ca[ca_mb_id]}\">{$ca['ca_mb_id']}";
        ?>
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_skin">출력스킨</label></th>
    <td colspan="3">
        <?=help("기본으로 제공하는 스킨은 $g4[shop]/list.skin.*.php 입니다.");?>
        <select id="ca_skin" id="ca_skin" name="ca_skin">
        <?=get_list_skin_options("^list.skin.(.*)\.php", G4_SHOP_PATH); ?>
        </select>
        <script>document.getElementById('ca_skin').value='<?=$ca[ca_skin]?>';</script>
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_img_width">출력이미지 폭<strong class="sound_only">필수</strong></label></th>
    <td>
        <?=help("환경설정 > 이미지(소) 폭, 높이가 기본값으로 설정됩니다.\n\n$g4[shop_url]/list.php에서 출력되는 이미지의 폭과 높이입니다.");?>
        <input type="text" id="ca_img_width" name="ca_img_width" size="5" value="<?=$ca['ca_img_width'] ?>" required> 픽셀
    </td>
    <th scope="row"><label for="ca_img_height">출력이미지 높이<strong class="sound_only">필수</strong></label></th>
    <td>
        <input type="text" id="ca_img_height" name="ca_img_height" size="5" value="<?=$ca['ca_img_height'] ?>" required> 픽셀
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_list_mod">1라인 이미지수<strong class="sound_only">필수</strong></label></th>
    <td>
        <?=help("1라인에 설정한 값만큼의 상품을 출력하지만 스킨에 따라 1라인에 하나의 상품만 출력할 수도 있습니다.");?>
        <input type="text" id="ca_list_mod" name="ca_list_mod" size="3" value="<?=$ca['ca_list_mod']?>" required> 개
    </td>
    <th scope="row"><label for="ca_list_row">총라인수<strong class="sound_only">필수</label></th>
    <td>
        <?=help("한페이지에 몇라인을 출력할것인지를 설정합니다.\n\n한페이지에서 표시하는 상품수는 (1라인 이미지수 x 총라인수) 입니다.");?>
        <input type="text" id="ca_list_row" name="ca_list_row" size="3" value="<?=$ca['ca_list_row']?>" required> 라인
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_opt1_subject">옵션 제목 1</label></th>
    <td>
        <?=help("제조사, 원산지 이외의 총 6개 옵션을 사용하실 수 있습니다.\n\n분류별로 다른 옵션 제목을 미리 설정할 수 있습니다.\n\n이곳에 입력한 값은 상품입력에서 옵션 제목으로 기본입력됩니다.");?>
        <input type="text" id="ca_opt1_subject" name="ca_opt1_subject" value="<?=$ca['ca_opt1_subject']?>">
    </td>
    <th scope="row"><label for="ca_opt2_subject">옵션 제목 2</label></th>
    <td><input type="text" id="ca_opt2_subject" name="ca_opt2_subject" value="<?=$ca['ca_opt2_subject'] ?>"></td>
</tr>
<tr>
    <th scope="row"><label for="ca_opt3_subject">옵션 제목 3</label></th>
    <td><input type="text" id="ca_opt3_subject" name="ca_opt3_subject" value="<?=$ca['ca_opt3_subject']?>"></td>
    <th scope="row"><label for="ca_opt4_subject">옵션 제목 4</label></th>
    <td><input type="text" id="ca_opt4_subject" name="ca_opt4_subject" value="<?=$ca['ca_opt4_subject']?>"></td>
</tr>
<tr>
    <th scope="row"><label for="ca_opt5_subject">옵션 제목 5</label></th>
    <td><input type="text" id="ca_opt5_subject" name="ca_opt5_subject" value="<?=$ca['ca_opt5_subject']?>"></td>
    <th scope="row"><label for="ca_opt6_subject">옵션 제목 6</label></th>
    <td><input type="text" id="ca_opt6_subject" name="ca_opt6_subject" value="<?=$ca['ca_opt6_subject']?>"></td>
</tr>
<tr>
    <th scope="row"><label for="ca_stock_qty">재고수량</label></th>
    <td colspan="3">
        <?=help("상품의 기본재고 수량을 설정합니다.\n재고를 사용하지 않는다면 숫자를 크게 입력하여 주십시오.\n예)999999");?>
        <input type="text" id="ca_stock_qty" name="ca_stock_qty" size="10" value="<?=$ca['ca_stock_qty']?>"> 개
    </td>
</tr>
<input type="hidden" id="ca_explan_html" name="ca_explan_html" value="<?=$ca['ca_explan_html']?>">
<tr>
    <th scope="row"><label for="ca_sell_email">판매자 E-mail</label></th>
    <td colspan="3">
        <?=help("운영자와 판매자가 다른 경우에 사용합니다.\n이 분류에 속한 상품을 등록할 경우에 기본값으로 입력됩니다.");?>
        <input type="text" id="ca_sell_email" name="ca_sell_email" size="40" value="<?=$ca['ca_sell_email'] ?>">
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_menu">메뉴표시</label></th>
    <td>
        <?=help("메뉴에 분류명을 표시합니다.");?>
        <input type="checkbox" id="ca_menu" name="ca_menu" <?=($ca['ca_menu']) ? "checked" : ""; ?> value='1'>
        예
    </td>
    <th scope="row"><label for="ca_use">판매가능</label></th>
    <td>
        <?=help("잠시 판매를 중단하거나 재고가 없을 경우에 체크하면 이 분류명과 이 분류에 속한 상품은 출력하지 않으며 주문도 할 수 없습니다.");?>
        <input type="checkbox" id="ca_use" name="ca_use" <?=($ca['ca_use']) ? "checked" : ""; ?> value='1'>예
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_nocoupon">쿠폰사용제외</label></th>
    <td colspan="3">
        <?=help("체크하면 이 분류명과 이 분류에 속한 상품은 쿠폰을 사용할 수 없습니다.");?>
        <input type="checkbox" id="ca_nocoupon" name="ca_nocoupon" value="1" <?=($ca['ca_nocoupon']) ? "checked" : ""; ?> />예
    </td>
</tr>
</tbody>
</table>

<table>
<caption>선택 입력</caption>
<tbody>
<tr>
    <th scope="row"><label for="ca_include_head">상단 파일 경로</label></th>
    <td colspan="3">
        <?=help("분류별로 상단+좌측의 내용이 다를 경우 상단+좌측 디자인 파일의 경로를 입력합니다.<br>입력이 없으면 기본 상단 파일을 사용합니다.<br>상단 내용과 달리 PHP 코드를 사용할 수 있습니다.");?>
        <input type="text" id="ca_include_head" name="ca_include_head" size="60" value="<?=$ca['ca_include_head']?>">
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_include_tail">하단 파일 경로</label></th>
    <td colspan="3">
        <?=help("분류별로 하단+우측의 내용이 다를 경우 하단+우측 디자인 파일의 경로를 입력합니다.<br>입력이 없으면 기본 하단 파일을 사용합니다.<br>하단 내용과 달리 PHP 코드를 사용할 수 있습니다.");?>
        <input type="text" id="ca_include_tail" name="ca_include_tail" size="60" value="<?=$ca['ca_include_tail']?>">
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_himg">상단이미지</label></th>
    <td colspan="3">
        <?=help("상품리스트 페이지 상단에 출력하는 이미지입니다.");?>
        <input type="file" id="ca_himg" name="ca_himg" size="40">
        <?
        $himg_str = "";
        $himg = "{$category_path}/{$ca['ca_id']}_h";
        if (file_exists($himg))
        {
            echo "<input type=\"checkbox\" id=\"ca_himg_del\" name=\"ca_himg_del\" value=\"1\"> <label for=\"ca_himg_del\">삭제</label>";
            $himg_str = "<img src=\"$himg\" alt=\"\">";
            //$size = getimagesize($himg);
            //echo "<img src=\"$g4['admin_path']/img/icon_viewer.gif\" onclick=\"imageview('himg', $size[0], $size[1]);\" alt=\"\">";
            //echo "<div id=\"himg\" style=\"left:0; top:0; z-index:+1; display:none; position:absolute;\"><img src=\"$himg\" alt=\"\"></div>";
        }
        ?>
    </td>
</tr>
<? if ($himg_str) { echo "<tr><td colspan=4>$himg_str</td></tr>"; } ?>

<tr>
    <th scope="row"><label for="ca_timg">하단이미지</label></th>
    <td colspan="3">
        <?=help("상품리스트 페이지 하단에 출력하는 이미지입니다.");?>
        <input type="file" id="ca_timg" name="ca_timg" size="40">
        <?
        $timg_str = "";
        $timg = "{$category_path}/{$ca['ca_id']}_t";
        if (file_exists($timg)) {
            echo "<input type=\"checkbox\" id=\"ca_timg_del\" name=\"ca_timg_del\" value=\"1\"> <label for=\"ca_timg_del\">삭제</label>";
            $timg_str = "<img src=\"$timg\" alt=\"\">";
            //$size = getimagesize($timg);
            //echo "<img src=\"$g4['admin_path']/img/icon_viewer.gif\" onclick=\"imageview('timg', $size[0], $size[1]);\"> <input type=\"checkbox\" id=\"ca_timg_del\" name=\"ca_timg_del\" value=\"1\"> <label for=\"ca_timg_del\">삭제</label>";
            //echo "<div id=\"timg\" style=\"left:0; top:0; z-index:+1; display:none; position:absolute;\"><img src=\"$timg\" alt=\"\"></div>";
        }
        ?>
    </td>
</tr>
<? if ($timg_str) { echo "<tr><td colspan=\"4\">$timg_str</td></tr>"; } ?>

<tr>
    <th scope="row"><label for="ca_head_html">상단 내용</label></th>
    <td colspan="3">
        <?=help("상품리스트 페이지 상단에 출력하는 HTML 내용입니다.", -150);?>
        <?=editor_html("ca_head_html", $ca['ca_head_html']);?>
    </td>
</tr>
<tr>
    <th scope="row"><label for="ca_tail_html">하단 내용</label></th>
    <td colspan="3">
        <?=help("상품리스트 페이지 하단에 출력하는 HTML 내용입니다.", -150);?>
        <?=editor_html("ca_tail_html", $ca['ca_tail_html']);?>
    </td>
</tr>
</tbody>
</table>


<? if ($w == "u") { ?>
<table>
<caption>기타</caption>
<tbody>
<tr>
    <th scope="row"><label for="sub_category">하위분류</label></th>
    <td colspan="3">
        <?=help("이 분류의 코드가 10 이라면 10 으로 시작하는 하위분류의 설정값을 이 분류와 동일하게 설정합니다.", 0, -100);?>
        <input type="checkbox" id="sub_category" name="sub_category" value="1" onclick="if (this.checked) if (confirm('이 분류에 속한 하위 분류의 속성을 똑같이 변경합니다.\n\n이 작업은 되돌릴 방법이 없습니다.\n\n그래도 변경하시겠습니까?')) return ; this.checked = false;">
        <label for="sub_category">이 분류의 설정과 같은 설정으로 반영</label>
    </td>
</tr>
</tbody>
</table>
<? } ?>

<div class="btn_confirm">
    <input type="submit" class="btn_submit" accesskey="s" value="확인">
    <a href="./categorylist.php?<?=$qstr?>">목록으로</a>
</div>
</form>

<script>
function fcategoryformcheck(f)
{
    <?=get_editor_js("ca_head_html");?>
    <?=get_editor_js("ca_tail_html");?>

    if (f.w.value == "") {
        if (f.codedup.value == '1') {
            alert("코드 중복검사를 하셔야 합니다.");
            return false;
        }
    }

    return true;
}

function codedupcheck(id)
{
    if (!id) {
        alert('분류코드를 입력하십시오.');
        document.fcategoryform.ca_id.focus();
        return;
    }

    $.post(
        "./codedupcheck.php",
        { ca_id: id },
        function(data)
        {
            if(data) {
                alert("코드 "+id+" 는 '"+data+"' (으)로 이미 등록되어 있으므로\n\n사용하실 수 없습니다.");
                return false;
            } else {
                alert("'"+id+"' 은(는) 등록된 코드가 없으므로 사용하실 수 있습니다.");
                document.fcategoryform.codedup.value = "";
            }
        }
    );
}

document.fcategoryform.ca_name.focus();
</script>

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>