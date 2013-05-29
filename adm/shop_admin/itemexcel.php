<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g4['titile'] = '상품 엑셀일괄등록';
include_once(G4_PATH.'/head.sub.php');
?>

<p>
엑셀파일을 이용하여 상품을 일괄등록할 수 있습니다. 상품일괄등록용 엑셀파일을 다운로드 후 상품 정보를 입력합니다.
수정이 완료된 엑셀파일을 업로드하시면 상품이 일괄등록됩니다. 엑셀파일을 저장하실 때는 Excel 97 - 2003 통합문서 (*.xls) 로 저장하셔야 합니다.
</p>
<p>
<a href="<?php echo G4_URL; ?>/<?php echo G4_LIB_DIR; ?>/Excel/itemexcel.xls">엑세파일 다운로드</a>
</p>
<form name="fitemexcel" method="post" action="./itemexcelupdate.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">
<table>
<tr>
    <td>파일선택</td>
    <td><input type="file" name="excelfile"></td>
</tr>
<tr>
    <td colspan="2">
        <input type="submit" value="상품등록">
        <button type="button" onclick="window.close();">닫기</button>
    </td>
</tr>
</table>
</form>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>