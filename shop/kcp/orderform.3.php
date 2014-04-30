<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// kcp 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use']) {
?>

<!-- Payplus Plug-in 설치 안내 시작 { -->
<p id="display_setup_message" class="display_setup_message" style="display:block">
    <span class="red">결제를 계속 하시려면 상단의 노란색 표시줄을 클릭</span>하시거나 <a href="https://pay.kcp.co.kr/plugin_new/file/KCPPluginSetup.exe" onclick="return get_intall_file();"><b><u>[수동설치]</u></b></a>를 눌러 다운로드 된 Payplus Plug-in을 설치하시기 바랍니다.<br>
    [수동설치]를 눌러 설치하신 경우 <span class="red bold">새로고침(F5)키</span>를 눌러 진행하시기 바랍니다.<br>
    새로고침(F5) 한후에도 계속 설치파일이 다운로드 되거나 결제가 되지 않으면 브라우저를 새로 열어서 주문해 주시기 바랍니다.<br>
    브라우저가 익스플로러가 아닌 경우 Payplus Plug-in 설치에 문제가 있을수 있음을 알려 드립니다.
</p>
<!-- } Payplus Plug-in 설치 안내 끝 -->
<?php } ?>

<div id="display_pay_button" class="btn_confirm" style="display:none">
    <input type="submit" value="주문하기" class="btn_submit">
    <a href="javascript:history.go(-1);" class="btn01">취소</a>
</div>
<div id="display_pay_process" style="display:none">
    <img src="<?php echo G5_URL; ?>/shop/img/loading.gif" alt="">
    <span>주문완료 중입니다. 잠시만 기다려 주십시오.</span>
</div>

<?php
// 무통장 입금만 사용할 때는 주문하기 버튼 보이게
if(!($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'])) {
?>
<script>
document.getElementById("display_pay_button").style.display = "" ;
</script>
<?php } ?>