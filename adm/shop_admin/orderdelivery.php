<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '엑셀 배송일괄처리';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>
            엑셀파일을 이용하여 배송정보를 일괄등록할 수 있습니다.<br>
            형식은 <strong>배송처리용 엑셀파일</strong>을 다운로드하여 배송 정보를 입력하시면 됩니다.<br>
            수정 완료 후 엑셀파일을 업로드하시면 배송정보가 일괄등록됩니다.<br>
            엑셀파일을 저장하실 때는 <strong>Excel 97 - 2003 통합문서 (*.xls)</strong> 로 저장하셔야 합니다.<br>
            주문상태가 준비이고 미수금이 0인 주문에 한해 엑셀파일이 생성됩니다.
        </p>

        <p>
            <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/orderdeliveryexcel.php">배송정보 일괄등록용 엑셀파일 다운로드</a>
        </p>
    </div>

    <form name="forderdelivery" method="post" action="./orderdeliveryupdate.php" enctype="MULTIPART/FORM-DATA" autocomplete="off">

    <div id="excelfile_upload">
        <label for="excelfile">파일선택</label>
        <input type="file" name="excelfile" id="excelfile">
    </div>

    <div id="excelfile_input">
        <input type="checkbox" name="od_send_mail" value="1" id="od_send_mail" checked="checked">
        <label for="od_send_mail">배송안내 메일</label>
        <input type="checkbox" name="send_sms" value="1" id="od_send_sms" checked="checked">
        <label for="od_send_sms">배송안내 SMS</label>
        <input type="checkbox" name="send_escrow" value="1" id="od_send_escrow">
        <label for="od_send_escrow">에스크로배송등록</label>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="배송정보 등록" class="btn_submit">
        <button type="button" onclick="window.close();">닫기</button>
    </div>

    </form>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>