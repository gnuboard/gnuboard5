<?
$sub_menu = '400200';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

$category_path = G4_DATA_PATH."/category";

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

    $sql = " select MAX(SUBSTRING(ca_id,$len2,2)) as max_subid from {$g4['shop_category_table']}
              where SUBSTRING(ca_id,1,$len) = '$ca_id' ";
    $row = sql_fetch($sql);

    $subid = base_convert($row['max_subid'], 36, 10);
    $subid += 36;
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
        $sql = " select * from {$g4['shop_category_table']} where ca_id = '$ca_id' ";
        $ca = sql_fetch($sql);
        $html_title = $ca['ca_name'] . " 하위분류추가";
        $ca['ca_name'] = "";
    }
    else // 1단계 분류
    {
        $html_title = "1단계분류추가";
        $ca['ca_use'] = 1;
        $ca['ca_explan_html'] = 1;
        $ca['ca_img_width']  = $default['de_simg_width'];
        $ca['ca_img_height'] = $default['de_simg_height'];
        $ca['ca_list_mod'] = 4;
        $ca['ca_list_row'] = 5;
        $ca['ca_stock_qty'] = 99999;
    }
    $ca['ca_skin'] = "list.skin.10.php";
}
else if ($w == "u")
{
    $sql = " select * from {$g4['shop_category_table']} where ca_id = '$ca_id' ";
    $ca = sql_fetch($sql);
    if (!$ca['ca_id'])
        alert("자료가 없습니다.");

    $html_title = $ca['ca_name'] . " 수정";
    $ca['ca_name'] = get_text($ca['ca_name']);
}

$qstr = 'page='.$page.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2;

$g4['title'] = $html_title;
include_once (G4_ADMIN_PATH.'/admin.head.php');


$pg_anchor ="<ul class=\"anchor\">
<li><a href=\"#frm_basic\">필수입력</a></li>
<li><a href=\"#frm_optional\">선택입력</a></li>";
if ($w == "u") $pg_anchor .= "<li><a href=\"#frm_etc\">기타설정</a></li>";
$pg_anchor .= "</ul>";
?>

<form name="fcategoryform" action="./categoryformupdate.php" onsubmit="return fcategoryformcheck(this);" method="post" enctype="multipart/form-data">

<input type="hidden" name="codedup"  value="<?=$default['de_code_dup_use']?>">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="sort1" value="<?=$sort1?>">
<input type="hidden" name="sort2" value="<?=$sort2?>">
<input type="hidden" name="ca_explan_html" value="<?=$ca['ca_explan_html']?>">

<section id="frm_basic" class="cbox">
    <h2>필수입력</h2>
    <?=$pg_anchor?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="ca_id">분류코드</label></th>
        <td>
        <? if ($w == "") { ?>
            <?=help("자동으로 보여지는 분류코드를 사용하시길 권해드리지만 직접 입력한 값으로도 사용할 수 있습니다.\n분류코드는 나중에 수정이 되지 않으므로 신중하게 결정하여 사용하십시오.\n\n분류코드는 2자리씩 10자리를 사용하여 5단계를 표현할 수 있습니다.\n0~z까지 입력이 가능하며 한 분류당 최대 1296가지를 표현할 수 있습니다.\n그러므로 총 3656158440062976가지의 분류를 사용할 수 있습니다.");?>
            <input type="text" name="ca_id" value="<?=$subid?>" id="ca_id" class="frm_input" size="<?=$sublen?>" maxlength="<?=$sublen?>">
            <? if ($default['de_code_dup_use']) { ?><a href="javascript:;" onclick="codedupcheck(document.getElementById('ca_id').value)">코드 중복검사</a><? } ?>
        <? } else { ?>
            <input type="hidden" name="ca_id" value="<?=$ca['ca_id']?>">
            <span class="frm_ca_id"><?=$ca['ca_id']?></span>
            <a href="<?=G4_SHOP_URL?>/list.php?ca_id=<?=$ca_id?>" class="btn_frmline">미리보기</a>
            <a href="./categoryform.php?ca_id=<?=$ca_id?>&amp;<?=$qstr?>" class="btn_frmline">하위분류 추가</a>
            <a href="./itemlist.php?sca=<?=$ca['ca_id']?>" class="btn_frmline">상품리스트</a>
        <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_name">분류명</label></th>
        <td><input type="text" name="ca_name" value="<? echo $ca['ca_name'] ?>" id="ca_name" size="38" required class="required frm_input"></td>
    </tr>
    <tr>
        <th scope="row"><? if ($is_admin == 'super') { ?><label for="ca_mb_id"><? } ?>관리 회원아이디<? if ($is_admin == 'super') { ?></label><? } ?></th>
        <td>
            <? if ($is_admin == 'super') { ?>
                <input type="text" name="ca_mb_id" value="<?=$ca['ca_mb_id']?>" id="ca_mb_id" class="frm_input" maxlength="20">
            <? } else { ?>
                <input type="hidden" name="ca_mb_id" value="<?=$ca['ca_mb_id']?>">
                <?=$ca['ca_mb_id']?>
            <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_skin">출력스킨</label></th>
        <td>
            <?=help("기본으로 제공하는 스킨은 ".G4_SHOP_DIR."/list.skin.*.php 입니다.");?>
            <select id="ca_skin" name="ca_skin">
                <?=get_list_skin_options("^list.skin.(.*)\.php", G4_SHOP_PATH, $ca['ca_skin']); ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_img_width">출력이미지 폭</label></th>
        <td>
            <?=help("환경설정 &gt; 이미지(소) 폭이 기본값으로 설정됩니다.\n".G4_SHOP_URL."/list.php에서 출력되는 이미지의 폭입니다.");?>
            <input type="text" name="ca_img_width" value="<? echo $ca['ca_img_width'] ?>" id="ca_img_width" required class="required frm_input" size="5" > 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_img_height">출력이미지 높이</label></th>
        <td>
            <?=help("환경설정 &gt; 이미지(소) 높이가 기본값으로 설정됩니다.\n".G4_SHOP_URL."/list.php에서 출력되는 이미지의 높이입니다.");?>
            <input type="text" name="ca_img_height"  value="<? echo $ca['ca_img_height'] ?>" id="ca_img_height" required class="required frm_input" size="5" > 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_list_mod">1줄당 이미지수</label></th>
        <td>
            <?=help("1라인에 설정한 값만큼의 상품을 출력하지만 스킨에 따라 1라인에 하나의 상품만 출력할 수도 있습니다.");?>
            <input type="text" name="ca_list_mod" size="3" value="<? echo $ca['ca_list_mod'] ?>" id="ca_list_mod" required class="required frm_input"> 개
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_list_row">총라인수</label></th>
        <td>
            <?=help("한페이지에 몇라인을 출력할것인지를 설정합니다.\n한페이지에서 표시하는 상품수는 (1줄당 이미지수 x 총라인수) 입니다.");?>
            <input type="text" name="ca_list_row" value='<? echo $ca['ca_list_row'] ?>' id="ca_list_row" required class="required frm_input" size="3"> 라인
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_opt1_subject">옵션 제목 1</label></th>
        <td>
            <?=help("제조사, 원산지 이외 옵션을 사용하실 수 있습니다.\n이곳에 입력한 옵션 제목은 상품입력 시 옵션 제목1 로 기본입력됩니다.");?>
            <input type="text" name="ca_opt1_subject" value="<? echo $ca['ca_opt1_subject'] ?>" id="ca_opt1_subject" class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_opt2_subject">옵션 제목 2</label></th>
        <td>
            <?=help("제조사, 원산지 이외 옵션을 사용하실 수 있습니다.\n이곳에 입력한 옵션 제목은 상품입력 시 옵션 제목2 로 기본입력됩니다.");?>
            <input type="text" name="ca_opt2_subject" value="<? echo $ca['ca_opt2_subject'] ?>" id="ca_opt2_subject" class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_opt3_subject">옵션 제목 3</label></th>
        <td>
            <?=help("제조사, 원산지 이외 옵션을 사용하실 수 있습니다.\n이곳에 입력한 옵션 제목은 상품입력 시 옵션 제목3 으로 기본입력됩니다.");?>
            <input type="text" name="ca_opt3_subject" value="<? echo $ca['ca_opt3_subject'] ?>" id="ca_opt3_subject" class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_opt4_subject">옵션 제목 4</label></th>
        <td>
            <?=help("제조사, 원산지 이외 옵션을 사용하실 수 있습니다.\n이곳에 입력한 옵션 제목은 상품입력 시 옵션 제목4 로 기본입력됩니다.");?>
            <input type="text" name="ca_opt4_subject" value="<? echo $ca['ca_opt4_subject'] ?>" id="ca_opt4_subject" class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_opt5_subject">옵션 제목 5</label></th>
        <td>
            <?=help("제조사, 원산지 이외 옵션을 사용하실 수 있습니다.\n이곳에 입력한 옵션 제목은 상품입력 시 옵션 제목5 로 기본입력됩니다.");?>
            <input type="text" name="ca_opt5_subject" value="<? echo $ca['ca_opt5_subject'] ?>" id="ca_opt5_subject" class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_opt6_subject">옵션 제목 6</label></th>
        <td>
            <?=help("제조사, 원산지 이외 옵션을 사용하실 수 있습니다.\n이곳에 입력한 옵션 제목은 상품입력 시 옵션 제목6 으로 기본입력됩니다.");?>
            <input type="text" name="ca_opt6_subject" value="<? echo $ca['ca_opt6_subject'] ?>" id="ca_opt6_subject" class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_stock_qty">재고수량</label></th>
        <td>
            <?=help("상품의 기본재고 수량을 설정합니다.\n재고를 사용하지 않는다면 숫자를 크게 입력하여 주십시오. 예) 999999");?>
            <input type="text" name="ca_stock_qty" size="10" value="<? echo $ca['ca_stock_qty']; ?>" id="ca_stock_qty" class="frm_input"> 개
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_sell_email">판매자 E-mail</label></th>
        <td>
            <?=help("운영자와 판매자가 다른 경우에 사용합니다.\n이 분류에 속한 상품을 등록할 경우에 기본값으로 입력됩니다.");?>
            <input type="text" name="ca_sell_email" size="40" value="<? echo $ca['ca_sell_email'] ?>" id="ca_sell_email" class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_use">판매가능</label></th>
        <td>
            <?=help("재고가 없거나 일시적으로 판매를 중단하시려면 체크 해제하십시오.\n체크 해제하시면 상품 출력을 하지 않으며, 주문도 받지 않습니다.");?>
            <input type="checkbox" name="ca_use" <? echo ($ca['ca_use']) ? "checked" : ""; ?> value="1" id="ca_use">예
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_optional" class="cbox">
    <h2>선택 입력</h2>
    <?=$pg_anchor?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="ca_include_head">상단파일경로</label></th>
        <td>
            <?=help("입력하지 않으면 기본 상단 파일을 사용합니다.<br>상단 내용과 달리 PHP 코드를 사용할 수 있습니다.");?>
            <input type="text" name="ca_include_head" value="<?=$ca['ca_include_head']?>" id="ca_include_head" class="frm_input" size="60">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ca_include_tail">하단 파일 경로</label></th>
        <td>
            <?=help("입력하지 않으면 기본 하단 파일을 사용합니다.<br>하단 내용과 달리 PHP 코드를 사용할 수 있습니다.");?>
            <input type="text" name="ca_include_tail" value="<?=$ca['ca_include_tail']?>" id="ca_include_tail" class="frm_input" size="60">
        </td>
    </tr>
    <tr>
        <th scope="row">상단이미지</th>
        <td>
            <?=help("상품리스트 페이지 상단에 출력하는 이미지입니다.");?>
            <input type="file" name="ca_himg">
            <?
            $himg_str = "";
            $himg = "{$category_path}/{$ca['ca_id']}_h";
            if (file_exists($himg))
            {
                echo "<input type=checkbox name=ca_himg_del value='1'>삭제";
                $himg_str = "<img src='".G4_DATA_URL."/category/{$ca['ca_id']}_h' border=0>";
                //$size = getimagesize($himg);
                //echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('himg', $size[0], $size[1]);\">";
                //echo "<div id='himg' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$himg' border=1></div>";
            }
            ?>
        </td>
    </tr>
    <? if ($himg_str) { echo "<tr><td colspan=4>$himg_str</td></tr>"; } ?>
    <tr>
        <th scope="row">하단이미지</th>
        <td>
            <?=help("상품리스트 페이지 하단에 출력하는 이미지입니다.");?>
            <input type="file" name="ca_timg">
            <?
                $timg_str = "";
                $timg = "{$category_path}/{$ca['ca_id']}_t";
                if (file_exists($timg)) {
                echo "<input type=checkbox name=ca_timg_del value='1'>삭제";
                $timg_str = "<img src='".G4_DATA_URL."/category/{$ca['ca_id']}_t' border=0>";
                //$size = getimagesize($timg);
                //echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('timg', $size[0], $size[1]);\"><input type=checkbox name=ca_timg_del value='1'>삭제";
                //echo "<div id='timg' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$timg' border=1></div>";
            }
            ?>
        </td>
    </tr>
    <? if ($timg_str) { echo "<tr><td colspan=4>$timg_str</td></tr>"; } ?>
    <tr>
        <th scope="row">상단내용</th>
        <td>
            <?=help("상품리스트 페이지 상단에 출력하는 HTML 내용입니다.");?>
            <?=editor_html('ca_head_html', $ca['ca_head_html']);?>
        </td>
    </tr>
    <tr>
        <th scope="row">하단내용</th>
        <td>
            <?=help("상품리스트 페이지 하단에 출력하는 HTML 내용입니다.", -150);?>
            <?=editor_html('ca_tail_html', $ca['ca_tail_html']);?>
        </td>
    </tr>
    </tbody>
    </table>

</section>

<? if ($w == "u") { ?>
<section id="frm_etc" class="cbox">
    <h2>기타설정</h2>
    <?=$pg_anchor?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_13">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">하위분류</th>
        <td>
            <?=help("이 분류의 코드가 10 이라면 10 으로 시작하는 하위분류의 설정값을 이 분류와 동일하게 설정합니다.\n<strong>이 작업은 실행 후 복구할 수 없습니다.</strong>")?>
            <label for="sub_category">이 분류의 하위분류 설정을, 이 분류와 동일하게 일괄수정</label>
            <input type="checkbox" name="sub_category" value="1" id="sub_category" onclick="if (this.checked) if (confirm('이 분류에 속한 하위 분류의 속성을 똑같이 변경합니다.\n\n이 작업은 되돌릴 방법이 없습니다.\n\n그래도 변경하시겠습니까?')) return ; this.checked = false;">
        </td>
    </tr>
    </tbody>
    </table>
</section>
<? } ?>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./categorylist.php?<?=$qstr?>">목록</a>
</div>
</form>

<script>
function fcategoryformcheck(f)
{
    <?=get_editor_js('ca_head_html');?>
    <?=get_editor_js('ca_tail_html');?>

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
        alert("분류코드를 입력하십시오.");
        f.ca_id.focus();
        return;
    }

    $.post(
        "./codedupcheck.php",
        { ca_id: id },
        function(data) {
            if(data.name) {
                alert("코드 '"+data.code+"' 는 '".data.name+"' (으)로 이미 등록되어 있으므로\n\n사용하실 수 없습니다.");
                return false;
            } else {
                alert("'"+data.code+"' 은(는) 등록된 코드가 없으므로 사용하실 수 있습니다.");
                document.fcategoryform.codedup.value = '';
            }
        }, "json"
    );
}

/*document.fcategoryform.ca_name.focus(); 포커스 해제*/
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>