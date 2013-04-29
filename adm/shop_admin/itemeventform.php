<?
$sub_menu = '400630';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

$html_title = "이벤트";
$g4['title'] = $html_title.' 관리';

if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g4['shop_event_table']} where ev_id = '$ev_id' ";
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

if(!isset($ev['ev_subject_strong'])) {
    sql_query(" ALTER TABLE `{$g4['shop_event_table']}`
                    ADD `ev_subject_strong` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ev_subject` ", false);
}

include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<form name="feventform" action="./itemeventformupdate.php" onsubmit="return feventform_check(this);" method="post" enctype="MULTIPART/FORM-DATA">
<input type="hidden" name="w" value="<?=$w ?>">
<input type="hidden" name="ev_id" value="<?=$ev_id ?>">

<section class="cbox">
    <h2><?=$html_title?></h2>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <? if ($w == "u") { ?>
    <tr>
        <th>이벤트번호</th>
        <td>
            <span class="frm_ev_id"><?=$ev_id?></span>
            <a href="<?=G4_SHOP_URL?>/event.php?ev_id=<?=$ev['ev_id']?>" class="btn_frmline">이벤트바로가기</a>
        </td>
    </tr>
    <? } ?>
    <tr>
        <th scope="row"><label for="ev_skin">출력스킨</label></th>
        <td>
            <?=help('기본으로 제공하는 스킨은 '.G4_SHOP_DIR.'/list.skin.*.php 입니다.'.PHP_EOL.G4_SHOP_DIR.'/list.php&amp;skin=userskin.php 처럼 직접 만든 스킨을 사용할 수도 있습니다.');?>
            <select name="ev_skin" id="ev_skin">
                <?  echo get_list_skin_options("^list\.skin\.(.*)\.php", G4_SHOP_PATH, $ev['ev_skin']); ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_img_width">출력이미지 폭</label></th>
        <td>
              <input type="text" name="ev_img_width" value="<?=$ev['ev_img_width'] ?>" id="ev_img_width" class="frm_input" size="5"> 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_img_height">출력이미지 높이</label></th>
        <td>
          <input type="text" name="ev_img_height" value="<?=$ev['ev_img_height'] ?>" id="ev_img_height" class="frm_input" size="5"> 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_list_mod">1줄당 이미지 수</label></th>
        <td>
            <?=help("1행에 설정한 값만큼의 상품을 출력합니다. 스킨 설정에 따라 1행에 하나의 상품만 출력할 수도 있습니다.", 50);?>
            <input type="text" name="ev_list_mod" value="<?=$ev['ev_list_mod'] ?>" id="ev_list_mod" class="frm_input" size="3"> 개
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_list_row">이미지 줄 수</label></th>
        <td>
            <?=help("한 페이지에 출력할 이미지 줄 수를 설정합니다.\n한 페이지에 표시되는 상품수는 (1줄당 이미지 수 x 줄 수) 입니다.");?>
            <input type="text" name="ev_list_row" value="<?=$ev['ev_list_row'] ?>" id="ev_list_row" class="frm_input" size="3"> 줄
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_use">사용</label></th>
        <td>
            <?=help("사용하지 않으면 레이아웃의 이벤트 메뉴 및 이벤트 관련 페이지에 접근할 수 없습니다.");?>
            <select name="ev_use" id="ev_use">
                <option value="1" <?=get_selected($ev['ev_use'], 1)?>>사용</option>
                <option value="0" <?=get_selected($ev['ev_use'], 0)?>>사용안함</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_subject">이벤트제목</label></th>
        <td>
            <input type="text" name="ev_subject" value="<? echo htmlspecialchars2($ev['ev_subject']) ?>" id="ev_subject" required class="required frm_input"  size="60">
            <label for="ev_subject_strong">제목 강조</label>
            <input type="checkbox" name="ev_subject_strong" value="1" id="ev_subject_strong" <?php if($ev['ev_subject_strong']) echo 'checked="checked"'; ?>>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_mimg">배너이미지</label></th>
        <td>
            <?=help("쇼핑몰 레이아웃에서 글자 대신 이미지로 출력할 경우 사용합니다.");?>
            <input type="file" name="ev_mimg" id="ev_mimg">
            <?
            $mimg_str = "";
            $mimg = G4_DATA_PATH.'/event/'.$ev['ev_id'].'_m';
            if (file_exists($mimg)) {
                $size = @getimagesize($mimg);
                if($size[0] && $size[0] > 750)
                    $width = 750;
                else
                    $width = $size[0];

                echo '<input type="checkbox" name="ev_mimg_del" value="1" id="ev_mimg_del"> <label for="ev_mimg_del">삭제</label>';
                $mimg_str = '<img src="'.G4_DATA_URL.'/event/'.$ev['ev_id'].'_m" width="'.$width.'" alt="">';
            }
            if ($mimg_str) {
                echo '<div class="banner_or_img">';
                echo $mimg_str;
                echo '</div>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_himg">상단이미지</label></th>
        <td>
            <?=help("이벤트 페이지 상단에 업로드 한 이미지를 출력합니다.");?>
            <input type="file" name="ev_himg" id="ev_himg">
            <?
            $himg_str = "";
            $himg = G4_DATA_PATH.'/event/'.$ev['ev_id'].'_h';
            if (file_exists($himg)) {
                $size = @getimagesize($himg);
                if($size[0] && $size[0] > 750)
                    $width = 750;
                else
                    $width = $size[0];

                echo '<input type="checkbox" name="ev_himg_del" value="1" id="ev_himg_del"> <label for="ev_himg_del">삭제</label>';
                $himg_str = '<img src="'.G4_DATA_URL.'/event/'.$ev['ev_id'].'_h" width="'.$width.'" alt="">';
            }
            if ($himg_str) {
                echo '<div class="banner_or_img">';
                echo $himg_str;
                echo '</div>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="ev_timg">하단이미지</label></th>
        <td>
            <?=help("이벤트 페이지 하단에 업로드 한 이미지를 출력합니다.");?>
            <input type="file" name="ev_timg" id="ev_timg">
            <?
            $timg_str = "";
            $timg = G4_DATA_PATH.'/event/'.$ev['ev_id'].'_t';
            if (file_exists($timg)) {
                $size = @getimagesize($timg);
                if($size[0] && $size[0] > 750)
                    $width = 750;
                else
                    $width = $size[0];
                echo '<input type="checkbox" name="ev_timg_del" value="1" id="ev_timg_del"> <label for="ev_timg_del">삭제</label>';
                $timg_str = '<img src="'.G4_DATA_URL.'/event/'.$ev['ev_id'].'_t" width="'.$width.'" alt="">';
            }
            if ($timg_str) {
                echo '<div class="banner_or_img">';
                echo $timg_str;
                echo '</div>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">상단내용</th>
        <td>
            <?=editor_html('ev_head_html', $ev['ev_head_html']);?>
        </td>
    </tr>
    <tr>
        <th scope="row">하단내용</th>
        <td>
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
