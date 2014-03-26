<?php
$sub_menu = "300100";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

$g5['title'] = '게시판 복사';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="fboardcopy" id="fboardcopy" action="./board_copy_update.php" onsubmit="return fboardcopy_check(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>" id="bo_table">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption><?php echo $g5['title']; ?></caption>
        <tbody>
        <tr>
            <th scope="col">원본 테이블명</th>
            <td><?php echo $bo_table ?></td>
        </tr>
        <tr>
            <th scope="col"><label for="target_table">복사 테이블명<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="target_table" id="target_table" required class="required alnum_ frm_input" maxlength="20">영문자, 숫자, _ 만 가능 (공백없이)</td>
        </tr>
        <tr>
            <th scope="col"><label for="target_subject">게시판 제목<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="target_subject" value="[복사본] <?php echo $board['bo_subject'] ?>" id="target_subject" required class="required frm_input" maxlength="120"></td>
        </tr>
        <tr>
            <th scope="col">복사 유형</th>
            <td>
                <input type="radio" name="copy_case" value="schema_only" id="copy_case" checked>
                <label for="copy_case">구조만</label>
                <input type="radio" name="copy_case" value="schema_data_both" id="copy_case2">
                <label for="copy_case2">구조와 데이터</label>
            </td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" class="btn_submit" value="복사">
        <input type="button" class="btn_cancel" value="창닫기" onclick="window.close();">
    </div>

    </form>

</div>

<script>
function fboardcopy_check(f)
{
    if (f.bo_table.value == f.target_table.value) {
        alert("원본 테이블명과 복사할 테이블명이 달라야 합니다.");
        return false;
    }

    return true;
}
</script>


<?php
include_once(G5_PATH.'/tail.sub.php');
?>
