<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

$test = "";
if ($default['de_card_test']) {
    if ($default['de_escrow_use'] == 1) { 
        // 일반결제 테스트
        $default['de_kcp_mid'] = "T0007";        
        $default['de_kcp_site_key'] = '2.mDT7R4lUIfHlHq4byhYjf__';
    } 
    else { 
        // 에스크로결제 테스트
        $default['de_kcp_mid'] = "T0000";        
        $default['de_kcp_site_key'] = '3grptw1.zW0GSo4PQdaGvsF__';
    }

    $test = "_test";
}
else {
    $default['de_kcp_mid'] = "SR".$default['de_kcp_mid'];
}

if (strtolower($g4['charset']) == 'utf-8')
    $js_url = "https://pay.kcp.co.kr/plugin/payplus{$test}_un.js";
else
    $js_url = "https://pay.kcp.co.kr/plugin/payplus{$test}.js";

/*
 * hashdata 암호화 (수정하지 마세요)
 *
 * hashdata 암호화 적용( site_cd + ordr_idxx + good_mny + timestamp + site_key )
 * site_cd : 사이트코드
 * ordr_idxx : 주문번호
 * good_mny : 결제금액 
 * timestamp : 타임스탬프
 * site_key : 사이트키
 *
 * hashdata 검증을 위한 
 * KCP에서 발급한 사이트키(site_key)를 반드시 입력해 주시기 바랍니다.
 */   

$site_cd   = trim($default['de_kcp_mid']);
$ordr_idxx = trim($od['od_id']);
$good_mny  = (int)$settle_amount;
$timestamp = $g4['server_time'];
$serverkey = $_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_ADDR']; // 사용자가 알수 없는 고유한 값들
//echo $serverkey;
$hashdata = md5($site_cd.$ordr_idxx.$good_mny.$timestamp.$serverkey);
?>

<script type="text/javascript" src="<?=$js_url?>"></script>
<script type="text/javascript">
// 플러그인 설치(확인)
StartSmartUpdate();

/*
function OpenWindow() 
{
    var form = document.order_info;

    if( document.Payplus.object == null ) {
        openwin = window.open( './kcp/chk_plugin.php', 'chk_plugin', 'width=420, height=100, top=300, left=300' );
    }

    if ( MakePayMessage( form ) == true ) {
        openwin = window.open( './kcp/proc_win.php', 'proc_win', 'width=420, height=100, top=300, left=300' );
        form.submit();
    }
}
*/

/* Payplus Plug-in 실행 */
function  jsf__pay( form )
{
    var RetVal = false;

    /* Payplus Plugin 실행 */
    if ( MakePayMessage( form ) == true )
    {
        //openwin = window.open( './kcp/proc_win.php', 'proc_win', 'width=420, height=100, top=300, left=300' );
        openwin = window.open( './kcp/proc_win.php', 'proc_win', 'width=420, height=100, top=300, left=300' );
        RetVal = true ;
    }
    
    else
    {
        /*  res_cd와 res_msg변수에 해당 오류코드와 오류메시지가 설정됩니다.
            ex) 고객이 Payplus Plugin에서 취소 버튼 클릭시 res_cd=3001, res_msg=사용자 취소
            값이 설정됩니다.
        */
        res_cd  = document.order_info.res_cd.value ;
        res_msg = document.order_info.res_msg.value ;

    }

    return RetVal ;
}
</script>

<form name="order_info" method="post" action='./kcp/pp_ax_hub.php'>
<!-- 사용자 변수 -->
<input type=hidden name='hashdata'      value='<?=$hashdata?>'>
<input type=hidden name='timestamp'     value='<?=$timestamp?>'>
<input type=hidden name='d_url'         value='<?=$g4['url']?>'>
<input type=hidden name='shop_dir'      value='<?=$g4['shop']?>'>
<input type=hidden name='on_uid'        value='<?=$_SESSION['ss_temp_on_uid']?>'>

<?
switch ($settle_case)
{
    case '계좌이체' :
        $settle_method = "010000000000";
        break;
    case '가상계좌' :
        $settle_method = "001000000000";
        break;
    case '휴대폰' :
        $settle_method = "000010000000";
        break;
    default : // 신용카드
        $settle_method = "100000000000";
        break;
}
?>
<!-- 
    2012.08.18 부터 개정 시행되는 '전자상거래 등에서의 소비자보호에 관한 법률'에 따른 코드 변경
    이용기간이 제한되는 컨텐츠 상품이나 정기 과금 상품 등에 한하여 '용역의 제공기간'을 
    표기/적용하여야 하며 이와 무관한 실물 배송상품 등의 결제에는 해당되지 않습니다. 
    0 : 일반결제
    good_expr의 나머지 적용 방식에 대해서는 KCP에서 제공하는 매뉴얼을 참고해 주세요.
-->
<input type=hidden name='good_expr'     value='0'>

<input type=hidden name='pay_method'    value='<?=$settle_method?>'>
<input type=hidden name='currency'      value='WON'>
<input type=hidden name='good_name'     value='<?=$goods?>'>
<input type=hidden name='good_mny'      value='<?=$good_mny?>'>
<input type=hidden name='buyr_name'     value='<?=addslashes($od['od_name'])?>' >
<input type=hidden name='buyr_mail'     value='<?=$od['od_email']?>'>
<input type=hidden name='buyr_tel1'     value='<?=$od['od_tel']?>'>
<input type=hidden name='buyr_tel2'     value='<?=$od['od_hp']?>'>

<input type=hidden name='quotaopt'      value='12'>

<input type=hidden name='rcvr_name'     value='<?=addslashes($od['od_b_name'])?>'>
<input type=hidden name='rcvr_tel1'     value='<?=$od['od_b_tel']?>'>
<input type=hidden name='rcvr_tel2'     value='<?=$od['od_b_hp']?>'>
<input type=hidden name='rcvr_mail'     value='<?=$od['od_email']?>'>
<input type=hidden name='rcvr_zipx'     value='<?=$od['od_b_zip1'].$od['od_b_zip2']?>'>
<input type=hidden name='rcvr_add1'     value='<?=addslashes($od['od_b_addr1'])?>'>
<input type=hidden name='rcvr_add2'     value='<?=addslashes($od['od_b_addr2'])?>'>

<?
$good_info = "";
$sql = " select a.ct_id,
                a.it_opt1,
                a.it_opt2,
                a.it_opt3,
                a.it_opt4,
                a.it_opt5,
                a.it_opt6,
                a.ct_amount,
                a.ct_point,
                a.ct_qty,
                a.ct_status,
                b.it_id,
                b.it_name,
                b.ca_id
           from $g4[yc4_cart_table] a, 
                $g4[yc4_item_table] b
          where a.on_uid = '$s_on_uid'
            and a.it_id  = b.it_id
          order by a.ct_id ";
$result = sql_query($sql);
for ($i=1; $row=mysql_fetch_array($result); $i++) 
{
    if ($i>1)
        $good_info .= chr(30);
    $good_info .= "seq=".$i.chr(31);
    $good_info .= "ordr_numb={$ordr_idxx}_".sprintf("%04d", $i).chr(31);
    $good_info .= "good_name=".addslashes($row['it_name']).chr(31);
    $good_info .= "good_cntx=".$row['ct_qty'].chr(31);
    $good_info .= "good_amtx=".$row['ct_amount'].chr(31);
}
?>

<!-- 필수 항목 -->

<!-- 요청종류 승인(pay)/취소,매입(mod) 요청시 사용 -->
<input type='hidden' name='req_tx'    value='pay'>
<!-- 테스트 결제시 : T0007 으로 설정, 리얼 결제시 : 부여받은 사이트코드 입력 -->
<input type='hidden' name='site_cd'   value='<?=$site_cd?>'>

<!-- MPI 결제창에서 사용 한글 사용 불가 -->
<input type='hidden' name='site_name' value='<?=$default[de_admin_company_name]?>'>
<!-- http://testpay.kcp.co.kr/Pay/Test/site_key.jsp 로 접속하신후 부여받은 사이트코드를 입력하고 나온 값을 입력하시기 바랍니다. -->
<input type='hidden' name='site_key'  value='<?=$default['de_kcp_site_key']?>'>

<!-- 필수 항목 : PULGIN 설정 정보 변경하지 마세요 -->
<input type='hidden' name='module_type' value='01'>

<input type='hidden' name='ordr_idxx' value='<?=$ordr_idxx?>'>

<!-- 에스크로 항목 -->

<!-- 에스크로 사용 여부 : 반드시 Y 로 세팅 -->
<input type='hidden' name='escw_used' value='Y'>

<!-- 에스크로 결제처리 모드 : 에스크로: Y, 일반: N, KCP 설정 조건: O -->
<input type='hidden' name='pay_mod' value='<?=($default[de_escrow_use]?"O":"N");?>'>

<!-- 배송 소요일 : 예상 배송 소요일을 입력 -->
<input type='hidden' name='deli_term' value='03'>

<!-- 장바구니 상품 개수 : 장바구니에 담겨있는 상품의 개수를 입력 -->
<input type='hidden' name='bask_cntx' value='<?=(int)($goods_count+1)?>'>

<!-- 장바구니 상품 상세 정보 (자바 스크립트 샘플(create_goodInfo()) 참고) -->
<input type='hidden' name='good_info' value='<?=$good_info?>'>

<!-- 필수 항목 : PLUGIN에서 값을 설정하는 부분으로 반드시 포함되어야 합니다. ※수정하지 마십시오.-->
<input type='hidden' name='res_cd'         value=''>
<input type='hidden' name='res_msg'        value=''>
<input type='hidden' name='tno'            value=''>
<input type='hidden' name='trace_no'       value=''>
<input type='hidden' name='enc_info'       value=''>
<input type='hidden' name='enc_data'       value=''>
<input type='hidden' name='ret_pay_method' value=''>
<input type='hidden' name='tran_cd'        value=''>
<input type='hidden' name='bank_name'      value=''>
<input type='hidden' name='use_pay_method' value=''>

<!-- 현금영수증 등록 창을 출력 여부 셋팅 - 5000원 이상 금액에만 보여지게 됩니다.-->
<input type="hidden" name="disp_tax_yn"     value="N">
<!-- 현금영수증 관련 정보 : PLUGIN 에서 내려받는 정보입니다 -->
<input type="hidden" name="cash_tsdtime"    value="">
<input type="hidden" name="cash_yn"         value="">
<input type="hidden" name="cash_authno"     value="">
<input type="hidden" name="cash_tr_code"    value="">
<input type="hidden" name="cash_id_info"    value="">

<!-- 계좌이체 서비스사 구분 -->
<input type="hidden" name="bank_issu"       value="">

<!-- 무이자 설정 -->
<input type="hidden" name="kcp_noint"       value="N">
<input type="hidden" name="kcp_noint_quota" value="">

<!-- 결제제외 카드 -->
<input type="hidden" name="not_used_card"   value="">

<p align="center"><input type="image" src="<?=$g4['shop_img_path']?>/btn_settle.gif" border="0"  onclick="return jsf__pay(this.form);" /></p>
</form>