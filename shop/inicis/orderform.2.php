<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<?php /* 주문폼 자바스크립트 에러 방지를 위해 추가함 */ ?>
<input type="hidden" name="good_mny"    value="<?php echo $tot_price; ?>">
<?php
if($default['de_tax_flag_use']) {
?>
    <input type="hidden" name="comm_tax_mny"	  value="<?php echo $comm_tax_mny; ?>">         <!-- 과세금액    -->
    <input type="hidden" name="comm_vat_mny"      value="<?php echo $comm_vat_mny; ?>">         <!-- 부가세	    -->
    <input type="hidden" name="comm_free_mny"     value="<?php echo $comm_free_mny; ?>">        <!-- 비과세 금액 -->
<?php
}
?>

<input type="hidden" name="goodname"    value="<?php echo $goods; ?>">
<input type="hidden" name="buyername"   value="">
<input type="hidden" name="buyeremail"  value="">
<input type="hidden" name="parentemail" value="">
<input type="hidden" name="buyertel"    value="">
<input type="hidden" name="recvname"    value="">
<input type="hidden" name="recvtel"     value="">
<input type="hidden" name="recvaddr"    value="">
<input type="hidden" name="recvpostnum" value="">

<!-- 기타설정 -->
<input type="hidden" name="currency"    value="WON">

<!-- 결제방법 -->
<input type="hidden" name="gopaymethod" value="">

<!--
SKIN : 플러그인 스킨 칼라 변경 기능 - 6가지 칼라(ORIGINAL, GREEN, ORANGE, BLUE, KAKKI, GRAY)
HPP : 컨텐츠 또는 실물 결제 여부에 따라 HPP(1)과 HPP(2)중 선택 적용(HPP(1):컨텐츠, HPP(2):실물).
Card(0): 신용카드 지불시에 이니시스 대표 가맹점인 경우에 필수적으로 세팅 필요 ( 자체 가맹점인 경우에는 카드사의 계약에 따라 설정) - 자세한 내용은 메뉴얼  참조.
OCB : OK CASH BAG 가맹점으로 신용카드 결제시에 OK CASH BAG 적립을 적용하시기 원하시면 "OCB" 세팅 필요 그 외에 경우에는 삭제해야 정상적인 결제 이루어짐.
no_receipt : 은행계좌이체시 현금영수증 발행여부 체크박스 비활성화 (현금영수증 발급 계약이 되어 있어야 사용가능)
-->
<input type="hidden" name="acceptmethod" value="HPP(2):Card(0):no_receipt:cardpoint<?php echo $useescrow; ?>">


<!--
상점 주문번호 : 무통장입금 예약(가상계좌 이체),전화결재 관련 필수필드로 반드시 상점의 주문번호를 페이지에 추가해야 합니다.
결제수단 중에 은행 계좌이체 이용 시에는 주문 번호가 결제결과를 조회하는 기준 필드가 됩니다.
상점 주문번호는 최대 40 BYTE 길이입니다.
주의:절대 한글값을 입력하시면 안됩니다.
-->
<input type="hidden" name="oid" value="<?php echo $od_id; ?>">


<!--
플러그인 좌측 상단 상점 로고 이미지 사용
이미지의 크기 : 90 X 34 pixels
플러그인 좌측 상단에 상점 로고 이미지를 사용하실 수 있으며,
주석을 풀고 이미지가 있는 URL을 입력하시면 플러그인 상단 부분에 상점 이미지를 삽입할수 있습니다.
-->
<!--input type="hidden" name="ini_logoimage_url"  value="http://[사용할 이미지주소]"-->

<!--
좌측 결제메뉴 위치에 이미지 추가
이미지의 크기 : 단일 결제 수단 - 91 X 148 pixels, 신용카드/ISP/계좌이체/가상계좌 - 91 X 96 pixels
좌측 결제메뉴 위치에 미미지를 추가하시 위해서는 담당 영업대표에게 사용여부 계약을 하신 후
주석을 풀고 이미지가 있는 URL을 입력하시면 플러그인 좌측 결제메뉴 부분에 이미지를 삽입할수 있습니다.
-->
<!--input type="hidden" name="ini_menuarea_url" value="http://[사용할 이미지주소]"-->

<!--
플러그인에 의해서 값이 채워지거나, 플러그인이 참조하는 필드들
삭제/수정 불가
uid 필드에 절대로 임의의 값을 넣지 않도록 하시기 바랍니다.
-->
<input type="hidden" name="ini_encfield" value="">
<input type="hidden" name="ini_certid" value="">
<input type="hidden" name="quotainterest" value="">
<input type="hidden" name="paymethod" value="">
<input type="hidden" name="cardcode" value="">
<input type="hidden" name="cardquota" value="">
<input type="hidden" name="rbankcode" value="">
<input type="hidden" name="reqsign" value="DONE">
<input type="hidden" name="encrypted" value="">
<input type="hidden" name="sessionkey" value="">
<input type="hidden" name="uid" value="">
<input type="hidden" name="sid" value="">
<input type="hidden" name="version" value="4000">
<input type="hidden" name="clickcontrol" value="">