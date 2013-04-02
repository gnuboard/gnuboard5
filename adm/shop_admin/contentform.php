<?
$sub_menu = '400700';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

// 상단, 하단 파일경로 필드 추가
$sql = " ALTER TABLE `{$g4['yc4_content_table']}`	ADD `co_include_head` VARCHAR( 255 ) NOT NULL ,
												ADD `co_include_tail` VARCHAR( 255 ) NOT NULL ";
sql_query($sql, false);

$html_title = "내용";

if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g4['yc4_content_table']} where co_id = '$co_id' ";
    $co = sql_fetch($sql);
    if (!$co['co_id'])
        alert('등록된 자료가 없습니다.');
}
else
{
    $html_title .= ' 입력';
    $co['co_html'] = 2;
}

$g4['title'] = $html_title;
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<form name="frmcontentform" action="./contentformupdate.php" onsubmit="return frmcontentform_check(this);" method="post" enctype="MULTIPART/FORM-DATA" >
<input type="hidden" name="w" value="<? echo $w?>">
<input type="hidden" name="co_html" value="1">

<section class="cbox">
    <h2>내용 입력</h2>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="co_id">ID</label></th>
        <td>
            <input type="text" value="<? echo $co['co_id'] ?>" name="co_id" id ="co_id" required class="required frm_input" size="20" maxlength="20" <? echo $readonly ?>>
            <? if ($w == 'u') { echo icon("보기", "$g4[shop_path]/content.php?co_id=$co_id"); } ?>
            (영문자, 숫자, _ 만 가능; 20자 이내; 공란 불가)
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="co_subject">제목</label></th>
        <td>
            <input type="text" name="co_subject" value="<?=htmlspecialchars2($co['co_subject'])?>" id="co_subject" required class="frm_input required">
        </td>
    </tr>
    <tr>
        <th scope="row">내용</th>
        <td>
            <?=editor_html('co_content', $co['co_content']);?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="co_include_head">상단 파일 경로</label></th>
        <td>
            <?=help("내용별로 상단+좌측의 내용이 다를 경우 상단+좌측 디자인 파일의 경로를 입력합니다.\n입력이 없으면 기본 상단 파일을 사용합니다.\n상단 내용과 달리 PHP 코드를 사용할 수 있습니다.\n");?>
            <input type="text" name="co_include_head" value="<?=$co['co_include_head']?>" id="co_include_head" class="frm_input" size="60">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="co_include_tail">하단 파일 경로</label></th>
        <td>
            <?=help("내용별로 하단+우측의 내용이 다를 경우 하단+우측 디자인 파일의 경로를 입력합니다.\n입력이 없으면 기본 하단 파일을 사용합니다.\n하단 내용과 달리 PHP 코드를 사용할 수 있습니다.\n");?>
            <input type="text" name="co_include_tail" value="<?=$co['co_include_tail']?>" id="co_include_tail" class="frm_input" size="60"> 
        </td>
    </tr>
    <tr>
        <th scope="row">상단이미지</th>
        <td>
            <input type="file" name="co_himg">
            <?
            $himg = G4_DATA_PATH."/content/{$co['co_id']}_h";
            if (file_exists($himg)) {
                echo "<input type=checkbox name=co_himg_del value='1'>삭제";
                $himg_str = "<img src='".G4_DATA_URL."/content/{$co['co_id']}_h' border=0>";
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">하단이미지</th>
        <td>
            <input type="file" name="co_timg">
            <?
            $timg = G4_DATA_PATH."/content/{$co['co_id']}_t";
            if (file_exists($timg)) {
                echo "<input type=checkbox name=co_timg_del value='1'>삭제";
                $timg_str = "<img src='".G4_DATA_URL."/content/{$co['co_id']}_t' border=0>";
            }
            ?>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./contentlist.php">목록</a>
</div>
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

/*<? if ($w == "u") { ?>
    document.frmcontentform.co_subject.focus();
<? } else { ?>
    document.frmcontentform.co_id.focus();
<? } ?> 김혜련 2013-04-02 포커스해제*/
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
