<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g4['title'] = '엑셀파일로 상품 일괄 등록';
include_once(G4_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g4['title']; ?></h1>

    <p class="new_win_desc">
        엑셀파일을 이용하여 상품을 일괄등록할 수 있습니다.<br>
        형식은 <strong>상품일괄등록용 엑셀파일</strong>을 다운로드하여 상품 정보를 입력하시면 됩니다.<br>
        수정 완료 후 엑셀파일을 업로드하시면 상품이 일괄등록됩니다.<br>
        엑셀파일을 저장하실 때는 Excel 97 - 2003 통합문서 (*.xls) 로 저장하셔야 합니다.
    </p>

    <p class="new_win_desc">
        <a href="<?php echo G4_URL; ?>/<?php echo G4_LIB_DIR; ?>/Excel/itemexcel.xls">상품일괄등록용 엑셀파일 다운로드</a>
    </p>

    <form name="fitemexcel" method="post" action="./itemexcelupdate.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">

    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div class="btn_confirm">
        <input type="submit" value="상품 엑셀파일 등록" class="btn_submit">
        <button type="button" onclick="window.close();">닫기</button>
    </div>

    </form>

</div>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>