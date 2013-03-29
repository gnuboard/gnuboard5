<?
$sub_menu = '400630';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

$html_title = "이벤트 ";

if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g4['yc4_event_table']} where ev_id = '$ev_id' ";
    $ev = sql_fetch($sql);
    if (!$ev['ev_id'])
        alert("등록된 자료가 없습니다.");
}
else
{
    $html_title .= " 입력";
    $ev['ev_skin'] = 0;
    $ev['ev_use'] = 1;

    // 1.03.00
    // 입력일 경우 기본값으로 대체
    $ev['ev_img_width']  = $default['de_simg_width'];
    $ev['ev_img_height'] = $default['de_simg_height'];
    $ev['ev_list_mod'] = 4;
    $ev['ev_list_row'] = 5;
}

$g4['title'] = $html_title;
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>
<form name="feventform" action="./itemeventformupdate.php" onsubmit="return feventform_check(this);" method="post" enctype="MULTIPART/FORM-DATA">
<input type="hidden" name="w" value="<? echo $w ?>">
<input type="hidden" name="ev_id" value="<? echo $ev_id ?>">

<section class="cbox">
    <h2>이벤트 입력 수정</h2>
    <?=$pg_anchor?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_5">
        <col class="grid_3">
        <col class="grid_5">
    </colgroup>
    <tbody>
    <? if ($w == "u") { ?>
    <tr>
        <th>이벤트번호</th>
        <td>
            <?
            echo $ev_id;
            echo "&nbsp;&nbsp;&nbsp;";
            echo icon("보기", G4_SHOP_URL."/event.php?ev_id={$ev['ev_id']}");
            ?>
        </td>
    </tr>
    <? } ?>
    <tr>
        <th scope="row"><label for="ev_skin">출력스킨</label></th>
        <td colspan="3">
            <?=help("기본으로 제공하는 스킨은 $cart_dir/list.skin.*.php 입니다.\n\n$cart_dir/list.php&skin=userskin.php 처럼 직접 만든 스킨을 사용할 수도 있습니다.");?>
            <select name="ev_skin" id="ev_skin">
                <?  echo get_list_skin_options("^list\.skin\.(.*)\.php", G4_SHOP_PATH); ?>
            </select>
            <? if ($w == 'u') { ?>
            <script>document.all.ev_skin.value='<?=$ev['ev_skin']?>';</script>
            <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_img_width">출력이미지 폭</label></th>
        <td>
              <input type="text" name="ev_img_width" value="<? echo $ev['ev_img_width'] ?>" id="ev_img_width" class="frm_input" size="5"> 픽셀
        </td>
        <th scope="row"><label for="ev_img_height">출력이미지 높이</label></th>
        <td>
          <input type="text" name="ev_img_height" value="<? echo $ev['ev_img_height'] ?>" id="ev_img_height" class="frm_input" size="5"> 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_list_mod">1라인 이미지수</label></th>
        <td>
            <?=help("1라인에 설정한 값만큼의 상품을 출력하지만 스킨에 따라 1라인에 하나의 상품만 출력할 수도 있습니다.", 50);?>
            <input type="text" name="ev_list_mod" value="<? echo $ev['ev_list_mod'] ?>" id="ev_list_mod" class="frm_input" size="3"> 개
        </td>
        <th scope="row"><label for="ev_list_row">총라인수</label></th>
        <td>
            <?=help("한페이지에 몇라인을 출력할것인지를 설정합니다.\n\n한페이지에서 표시하는 상품수는 (1라인 이미지수 x 총라인수) 입니다.");?>
            <input type="text" name="ev_list_row" value="<? echo $ev['ev_list_row'] ?>" id="ev_list_row" class="frm_input" size="3"> 라인
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_use">사용</label></th>
        <td colspan="3">
            <?=help("사용하지 않으면 왼쪽의 이벤트 메뉴와 이벤트리스트 페이지에 접근할 수 없습니다.");?>
            <select name="ev_use" id="ev_use">
                <option value="1">예</option>
                <option value="0">아니오</option>
            </select>
            <script>document.all.ev_use.value='<?=$ev['ev_use']?>';</script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_subject">이벤트제목</label></th>
        <td colspan="3">
            <input type="text" name="ev_subject" value="<? echo htmlspecialchars2($ev['ev_subject']) ?>" id="ev_subject" required class="required frm_input"  size="60">
        </td>
    </tr>
    <tr>
        <th scope="row">메뉴이미지</th>
        <td colspan="3">
            <?=help("쇼핑몰 왼쪽 메뉴에 텍스트 메뉴 대신 이미지로 넣을 경우 사용합니다.");?>
            <input type="file" name="ev_mimg">
            <?
            $mimg_str = "";
            $mimg = G4_DATA_PATH."/event/{$ev['ev_id']}_m";
            if (file_exists($mimg)) {
                echo "<input type=\"checkbox\" name=\"ev_mimg_del\" value=\"1\">삭제";
                $mimg_str = "<img src='".G4_DATA_URL."/event/{$ev['ev_id']}_m' border=\"0\">";
            }
            ?>
        </td>
    </tr>
    <? if ($mimg_str) { echo "<tr><td></td><td colspan=\"3\">$mimg_str</td></tr>"; } ?>
    <tr>
        <th scope="row">상단이미지</th>
        <td colspan="3">
            <?=help("이벤트 페이지 상단에 업로드 한 이미지를 출력합니다.");?>
            <input type="file" name="ev_himg">
            <?
            $himg_str = "";
            $himg = G4_DATA_PATH."/event/{$ev['ev_id']}_h";
            if (file_exists($himg)) {
                echo "<input type=\"checkbox\" name=\"ev_himg_del\" value='1'>삭제";
                $himg_str = "<img src='".G4_DATA_URL."/event/{$ev['ev_id']}_h' border=\"0\">";
            }
            ?>
        </td>
    </tr>
    <? if ($himg_str) { echo "<tr><td colspan=\"4\">$himg_str</td></tr>"; } ?>
    <tr>
        <th scope="row">하단이미지</th>
        <td colspan="3">
            <?=help("이벤트 페이지 하단에 업로드 한 이미지를 출력합니다.");?>
            <input type="file" name="ev_timg">
            <?
            $timg_str = "";
            $timg = G4_DATA_PATH."/event/{$ev['ev_id']}_t";
            if (file_exists($timg)) {
                echo "<input type=\"checkbox\" name=\"ev_timg_del\" value='1'>삭제";
                $timg_str = "<img src='".G4_DATA_URL."/event/{$ev['ev_id']}_t' border=\"0\">";
            }
            ?>
        </td>
    </tr>
    <? if ($timg_str) { echo "<tr><td colspan=\"4\">$timg_str</td></tr>"; } ?>
    <tr>
        <th scope="row">상단내용</th>
        <td colspan="3">
            <?=editor_html('ev_head_html', $ev['ev_head_html']);?>
        </td>
    </tr>
    <tr>
        <th scope="row">하단내용</th>
        <td colspan="3">
            <?=editor_html('ev_tail_html', $ev['ev_tail_html']);?>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./itemevent.php">목록</a>
</div>
</form>

<script>
function feventform_check(f)
{
    <?=get_editor_js('ev_head_html');?>
    <?=get_editor_js('ev_tail_html');?>

    return true;
}

/* document.feventform.ev_subject.focus(); 포커스해제*/
</script>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
