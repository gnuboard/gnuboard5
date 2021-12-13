<?php
$sub_menu = "200100";
include_once("./_common.php");


auth_check_menu($auth, $sub_menu, 'w');


$g5['title'] = "엑셀 회원 데이터 업로드";
include_once('./admin.head.php');
?>

<!-- start publishing  -->
<form name="mlistexcel" method="post" action="./member_excel_upload_update.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">
    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="win_btn btn_confirm">
        <input type="submit" class="btn_submit btn">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>

</form>
<!-- end publishing -->

<?php
include_once ('./admin.tail.php');
?>