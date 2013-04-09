<?
$sub_menu = '400720';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "w");

$html_title = "새창";
if ($w == "u")
{
    $html_title .= " 수정";
    $sql = " select * from {$g4['shop_new_win_table']} where nw_id = '$nw_id' ";
    $nw = sql_fetch($sql);
    if (!$nw['nw_id']) alert("등록된 자료가 없습니다.");
}
else
{
    $html_title .= " 입력";
    $nw['nw_disable_hours'] = 24;
    $nw['nw_left']   = 10;
    $nw['nw_top']    = 10;
    $nw['nw_width']  = 450;
    $nw['nw_height'] = 500;
    $nw['nw_content_html'] = 2;
}

$g4['title'] = $html_title;
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<form name="frmnewwin" action="./newwinformupdate.php" onsubmit="return frmnewwin_check(this);" method="post">
<input type="hidden" name="w" value="<? echo $w ?>">
<input type="hidden" name="nw_id" value="<? echo $nw_id ?>">

<section class="cbox">
    <h2>새창 입력 수정</h2>
    <p><?=help("쇼핑몰 초기화면 접속 시 자동으로 뜰 새창을 설정/관리합니다.")?></p>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_5">
        <col class="grid_3">
        <col class="grid_5">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="nw_disable_hours">시간</label></th>
        <td colspan="3"><input type="text" name="nw_disable_hours" value="<? echo $nw['nw_disable_hours'] ?>" id="nw_disable_hours" required class="frm_input required" size="5"> 시간 동안 다시 띄우지 않음</td>
    </tr>
    <tr>
        <th scope="row"><label for="nw_begin_time">시작일시</label></th>
        <td>
            <input type="text" name="nw_begin_time" value="<? echo $nw['nw_begin_time'] ?>" id="nw_begin_time" required class="frm_input required" size="21" maxlength="19">
            <input type="checkbox" name="nw_begin_chk" value="<? echo date("Y-m-d 00:00:00", G4_SERVER_TIME); ?>" id="nw_begin_chk" onclick="if (this.checked == true) this.form.nw_begin_time.value=this.form.nw_begin_chk.value; else this.form.nw_begin_time.value = this.form.nw_begin_time.defaultValue;">
            <label for="nw_begin_chk">오늘</label>
        </td>
        <th scope="row"><label for="nw_end_time">종료일시</label></th>
        <td>
            <input type="text" name="nw_end_time" value="<? echo $nw['nw_end_time'] ?>" id="nw_end_time" required class="frm_input requried" size="21" maxlength="19">
            <input type="checkbox" name="nw_end_chk" value="<? echo date("Y-m-d 23:59:59", G4_SERVER_TIME+(60*60*24*7)); ?>" id="nw_end_chk" onclick="if (this.checked == true) this.form.nw_end_time.value=this.form.nw_end_chk.value; else this.form.nw_end_time.value = this.form.nw_end_time.defaultValue;">
            <label for="nw_end_chk">오늘+7일</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="nw_left">창위치 왼쪽</label></th>
        <td>
           <input type="text" name="nw_left" value="<? echo $nw['nw_left'] ?>" id="nw_left" required class="frm_input requried" size="5">
        </td>
        <th scope="row"><label for="nw_top">창위치 위</label></th>
        <td>
            <input type="text" name="nw_top" value="<? echo $nw['nw_top'] ?>" id="nw_top" required class="frm_input requried"  size="5">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="nw_width">창크기 폭</label></th>
        <td>
            <input type="text" name="nw_width" value="<? echo $nw['nw_width'] ?>" id="nw_width" required class="frm_input requried" size="5">
        </td>
        <th scope="row"><label for="nw_height">창크기 높이</label></th>
        <td>
            <input type="text" name="nw_height" value="<? echo $nw['nw_height'] ?>" id="nw_height" required class="frm_input requried" size="5">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="nw_subject">창제목</label></th>
        <td colspan="3">
            <input type="text" name="nw_subject" size="80" value="<? echo stripslashes($nw['nw_subject']) ?>" id="nw_subject" required class="frm_input requried">
        </td>
    </tr>
    <tr>
        <th scope="row">내용</th>
        <td colspan="3"><?=editor_html('nw_content', $nw['nw_content']);?></td>
    </tr>
    </tbody>
    </table>
</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./newwinlist.php">목록</a>
</div>
</form>

<script>
function frmnewwin_check(f)
{
    errmsg = "";
    errfld = "";

    <?=get_editor_js('nw_content');?>

    check_field(f.nw_subject, "제목을 입력하세요.");

    if (errmsg != "") {
        alert(errmsg);
        errfld.focus();
        return false;
    }
    return true;
}

// document.frmnewwin.nw_subject.focus();
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
