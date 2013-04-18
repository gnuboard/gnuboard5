<?
$sub_menu = '400700';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

// 상단, 하단 파일경로 필드 추가
$sql = " ALTER TABLE `{$g4['shop_content_table']}`  ADD `co_include_head` VARCHAR( 255 ) NOT NULL ,
                                                ADD `co_include_tail` VARCHAR( 255 ) NOT NULL ";
sql_query($sql, false);

$html_title = "내용";
$g4['title'] = $html_title.' 관리';

if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g4['shop_content_table']} where co_id = '$co_id' ";
    $co = sql_fetch($sql);
    if (!$co['co_id'])
        alert('등록된 자료가 없습니다.');
}
else
{
    $html_title .= ' 입력';
    $co['co_html'] = 2;
}

include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<form name="frmcontentform" action="./contentformupdate.php" onsubmit="return frmcontentform_check(this);" method="post" enctype="MULTIPART/FORM-DATA" >
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="co_html" value="1">

<section class="cbox">
    <h2>내용 입력</h2>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="co_id">ID</label></th>
        <td>
            <?=help('20자 이내의 영문자, 숫자, _ 만 가능합니다.')?>
            <input type="text" value="<? echo $co['co_id'] ?>" name="co_id" id ="co_id" required <?=$readonly ?> class="required <?=$readonly ?> frm_input" size="20" maxlength="20">
            <? if ($w == 'u') { ?><a href="<?=G4_SHOP_URL?>/content.php?co_id=<?=$co_id?>" class="btn_frmline">내용확인</a><? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="co_subject">제목</label></th>
        <td><input type="text" name="co_subject" value="<?=htmlspecialchars2($co['co_subject'])?>" id="co_subject" required class="frm_input required" size="90"></td>
    </tr>
    <tr>
        <th scope="row">내용</th>
        <td><?=editor_html('co_content', $co['co_content']);?></td>
    </tr>
    <tr>
        <th scope="row"><label for="co_include_head">상단 파일 경로</label></th>
        <td>
            <?=help("설정값이 없으면 기본 상단 파일을 사용합니다.");?>
            <input type="text" name="co_include_head" value="<?=$co['co_include_head']?>" id="co_include_head" class="frm_input" size="60">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="co_include_tail">하단 파일 경로</label></th>
        <td>
            <?=help("설정값이 없으면 기본 하단 파일을 사용합니다.");?>
            <input type="text" name="co_include_tail" value="<?=$co['co_include_tail']?>" id="co_include_tail" class="frm_input" size="60">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="co_himg">상단이미지</label></th>
        <td>
            <input type="file" name="co_himg" id="co_himg">
            <?
            $himg = G4_DATA_PATH.'/content/'.$co['co_id'].'_h';
            if (file_exists($himg)) {
                echo '<input type="checkbox" name="co_himg_del" value="1" id="co_himg_del"> <label for="co_himg_del">삭제</label>';
                $himg_str = '<img src="'.G4_DATA_URL.'/content/'.$co['co_id'].'_h" alt="">';
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
        <th scope="row"><label for="co_timg">하단이미지</label></th>
        <td>
            <input type="file" name="co_timg" id="co_timg">
            <?
            $timg = G4_DATA_PATH.'/content/'.$co['co_id'].'_t';
            if (file_exists($timg)) {
                echo '<input type="checkbox" name="co_timg_del" value="1" id="co_timg_del"> <label for="co_timg_del">삭제</label>';
                $timg_str = '<img src="'.G4_DATA_URL.'/content/'.$co['co_id'].'_t" alt="">';
            }
            if ($timg_str) {
                echo '<div class="banner_or_img">';
                echo $timg_str;
                echo '</div>';
            }
            ?>
        </td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="확인" class="btn_submit" accesskey="s">
        <a href="./contentlist.php">목록</a>
    </div>

</section>
</form>

<script>
function frmcontentform_check(f)
{
    errmsg = "";
    errfld = "";

    <?=get_editor_js('co_content');?>

    check_field(f.co_id, "ID를 입력하세요.");
    check_field(f.co_subject, "제목을 입력하세요.");
    check_field(f.co_content, "내용을 입력하세요.");

    if (errmsg != "") {
        alert(errmsg);
        errfld.focus();
        return false;
    }
    return true;
}
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
