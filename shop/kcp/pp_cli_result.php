<?php
include_once './_common.php';
require_once(G5_SHOP_PATH.'/settle_kcp.inc.php');

    /* ============================================================================== */
    /* =   PAGE : 결과 처리 PAGE                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2007   KCP Inc.   All Rights Reserverd.                   = */
    /* ============================================================================== */
?>
<?php
    /* ============================================================================== */
    /* =   01. KCP 지불 서버 정보 설정                                              = */
    /* = -------------------------------------------------------------------------- = */
    if ($default['de_card_test']) {
        $g_conf_pa_url    = "testpaygw.kcp.co.kr"; // ※ 테스트: testpaygw.kcp.co.kr, 리얼: paygw.kcp.co.kr
        $g_conf_pa_port   = "8090";                // ※ 테스트: 8090,                리얼: 8090
    }
    else {
        $g_conf_pa_url    = "paygw.kcp.co.kr";
        $g_conf_pa_port   = "8090";
    }

    $g_conf_tx_mode   = 0;
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   지불 결과                                                                = */
    /* = -------------------------------------------------------------------------- = */
    $req_tx     = isset($_POST["req_tx"]) ? $_POST["req_tx"] : '';                             // 요청 종류
    $bSucc      = isset($_POST["bSucc"]) ? $_POST["bSucc"] : '';                             // DB처리 여부
    $trad_time  = isset($_POST["trad_time"]) ? $_POST["trad_time"] : '';                             // 원거래 시각
    /* = -------------------------------------------------------------------------- = */
    $ordr_idxx  = isset($_POST["ordr_idxx"]) ? $_POST["ordr_idxx"] : '';                             // 주문번호
    $buyr_name  = isset($_POST["buyr_name"]) ? $_POST["buyr_name"] : '';                             // 주문자 이름
    $buyr_tel1  = isset($_POST["buyr_tel1"]) ? $_POST["buyr_tel1"] : '';                             // 주문자 전화번호
    $buyr_mail  = isset($_POST["buyr_mail"]) ? $_POST["buyr_mail"] : '';                             // 주문자 메일
    $good_name  = isset($_POST["good_name"]) ? $_POST["good_name"] : '';                             // 주문상품명
    $comment    = isset($_POST["comment"]) ? $_POST["comment"] : '';                             // 비고
    /* = -------------------------------------------------------------------------- = */
    $corp_type     = isset($_POST["corp_type"]) ? $_POST["corp_type"] : '';                      // 사업장 구분
    $corp_tax_type = isset($_POST["corp_tax_type"]) ? $_POST["corp_tax_type"] : '';                      // 과세/면세 구분
    $corp_tax_no   = isset($_POST["corp_tax_no"]) ? $_POST["corp_tax_no"] : '';                      // 발행 사업자 번호
    $corp_nm       = isset($_POST["corp_nm"]) ? $_POST["corp_nm"] : '';                      // 상호
    $corp_owner_nm = isset($_POST["corp_owner_nm"]) ? $_POST["corp_owner_nm"] : '';                      // 대표자명
    $corp_addr     = isset($_POST["corp_addr"]) ? $_POST["corp_addr"] : '';                      // 사업장 주소
    $corp_telno    = isset($_POST["corp_telno"]) ? $_POST["corp_telno"] : '';                      // 사업장 대표 연락처
    /* = -------------------------------------------------------------------------- = */
    $tr_code    = isset($_POST["tr_code"]) ? $_POST["tr_code"] : '';                             // 발행용도
    $id_info    = isset($_POST["id_info"]) ? $_POST["id_info"] : '';                             // 신분확인 ID
    $amt_tot    = isset($_POST["amt_tot"]) ? $_POST["amt_tot"] : '';                             // 거래금액 총 합
    $amt_sup    = isset($_POST["amt_sup"]) ? $_POST["amt_sup"] : '';                             // 공급가액
    $amt_svc    = isset($_POST["amt_svc"]) ? $_POST["amt_svc"] : '';                             // 봉사료
    $amt_tax    = isset($_POST["amt_tax"]) ? $_POST["amt_tax"] : '';                             // 부가가치세
    /* = -------------------------------------------------------------------------- = */
    $pay_type      = isset($_POST["pay_type"]) ? $_POST["pay_type"] : '';                      // 결제 서비스 구분
    $pay_trade_no  = isset($_POST["pay_trade_no"]) ? $_POST["pay_trade_no"] : '';                      // 결제 거래번호
    /* = -------------------------------------------------------------------------- = */
    $mod_type   = isset($_POST["mod_type"]) ? $_POST["mod_type"] : '';                             // 변경 타입
    $mod_value  = isset($_POST["mod_value"]) ? $_POST["mod_value"] : '';                             // 변경 요청 거래번호
    $mod_gubn   = isset($_POST["mod_gubn"]) ? $_POST["mod_gubn"] : '';                             // 변경 요청 거래번호 구분
    $mod_mny    = isset($_POST["mod_mny"]) ? $_POST["mod_mny"] : '';                             // 변경 요청 금액
    $rem_mny    = isset($_POST["rem_mny"]) ? $_POST["rem_mny"] : '';                             // 변경처리 이전 금액
    /* = -------------------------------------------------------------------------- = */
    $res_cd     = isset($_POST["res_cd"]) ? clean_xss_tags(strip_tags($_POST["res_cd"])) : '';                             // 응답코드
    $res_msg    = isset($_POST["res_msg"]) ? clean_xss_tags(strip_tags($_POST["res_msg"])) : '';                             // 응답메시지
    $cash_no    = isset($_POST["cash_no"]) ? clean_xss_tags(strip_tags($_POST["cash_no"])) : '';                             // 현금영수증 거래번호
    $receipt_no = isset($_POST["receipt_no"]) ? clean_xss_tags(strip_tags($_POST["receipt_no"])) : '';                             // 현금영수증 승인번호
    $app_time   = isset($_POST["app_time"]) ? clean_xss_tags(strip_tags($_POST["app_time"])) : '';                             // 승인시간(YYYYMMDDhhmmss)
    $reg_stat   = isset($_POST["reg_stat"]) ? clean_xss_tags(strip_tags($_POST["reg_stat"])) : '';                             // 등록 상태 코드
    $reg_desc   = isset($_POST["reg_desc"]) ? clean_xss_tags(strip_tags($_POST["reg_desc"])) : '';                             // 등록 상태 설명
    /* ============================================================================== */

    $req_tx_name = "";

    if( $req_tx == "pay" )
    {
        $req_tx_name = "등록";
    }
    else if( $req_tx == "mod" )
    {
        $req_tx_name = "변경/조회";
    }
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>현금영수증발급 <?php echo $req_tx_name; ?> | <?php echo $config['cf_title']; ?></title>
<?php
echo '<link rel="stylesheet" href="'.G5_CSS_URL.'/'.(G5_IS_MOBILE?'mobile':'default').'_shop.css">'.PHP_EOL;
?>
<style>
.tbl_head01 tbody th {padding-right:10px;text-align:right}
</style>
<script>
    //현금영수증 연동 스크립트
    function receiptView(auth_no)
    {
        var receiptWin = "<?php echo G5_CASH_RECEIPT_URL.$default['de_kcp_mid'].'&orderid='.$ordr_idxx.'&bill_yn=Y&authno='; ?>"+auth_no;
        window.open(receiptWin , "" , "width=360, height=647")
    }
</script>
</head>
<body>

<div id="kcp_req_rx" class="new_win">
    <h1 id="win_title">현금영수증 <?php echo $req_tx_name; ?> - KCP Online Payment System</h1>


    <div class="tbl_head01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
<?php
    if ($req_tx == "pay")                          // 거래 구분 : 등록
    {
        if (!$bSucc == "false")                    // 업체 DB 처리 정상
        {
            if ($res_cd == "0000")                 // 정상 승인
            {
?>
        <tr>
            <th scope="row">결과코드</th>
            <td><?php echo $res_cd; ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo $res_msg; ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 거래번호</th>
            <td><?php echo $cash_no; ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo $receipt_no; ?></td>
        </tr>
        <tr>
            <th scope="row">등록 상태 코드</th>
            <td><?php echo $reg_stat; ?></td>
        </tr>
        <tr>
            <th scope="row">등록 상태 설명</th>
            <td><?php echo $reg_desc; ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6",$app_time); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 URL</th>
            <td>
                <button type="button" name="receiptView" class="btn_frmline" onClick="javascript:receiptView('<?php echo $receipt_no; ?>')">영수증 확인</button>
                <p>영수증 확인은 실 등록의 경우에만 가능합니다.</p>
            </td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
<?php
            }
            else                                       // 승인 실패
            {
?>
        <tr>
            <th scope="row">결과코드</th>
            <td><?php echo $res_cd; ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo $res_msg; ?></td>
        </tr>
<?php
            }

        }
        else                                           // 업체 DB 처리 실패
        {
?>
        <tr>
            <th scope="row">취소 결과코드</th>
            <td><?php echo $res_cd; ?></td>
        </tr>
        <tr>
            <th scope="row">취소 결과 메세지</th>
            <td><?php echo $res_msg; ?></td>
        </tr>
        <tr>
            <th scope="row">상세메세지</th>
            <td>
<?php
            if ($res_cd == "0000")
            {
                echo "결제는 정상적으로 이루어졌지만 쇼핑몰에서 결제 결과를 처리하는 중 오류가 발생하여 시스템에서 자동으로 취소 요청을 하였습니다. <br> 쇼핑몰로 전화하여 확인하시기 바랍니다.";
            }
            else
            {
                echo "결제는 정상적으로 이루어졌지만 쇼핑몰에서 결제 결과를 처리하는 중 오류가 발생하여 시스템에서 자동으로 취소 요청을 하였으나, <br> <b>취소가 실패 되었습니다.</b><br> 쇼핑몰로 전화하여 확인하시기 바랍니다.";
            }
?>
            </td>
        </tr>
<?php
        }

    }
    else if ($req_tx == "mod")                     // 거래 구분 : 조회/취소 요청
    {
        if ($res_cd == "0000")
        {
?>
        <tr>
            <th scope="row">결과코드</th>
            <td><?php echo $res_cd; ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo $res_msg; ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 거래번호</th>
            <td><?php echo $cash_no; ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 승인번호</th>
            <td><?php echo $receipt_no; ?></td>
        </tr>
        <tr>
            <th scope="row">등록 상태 코드</th>
            <td><?php echo $reg_stat; ?></td>
        </tr>
        <tr>
            <th scope="row">등록 상태 설명</th>
            <td><?php echo $reg_desc; ?></td>
        </tr>
        <tr>
            <th scope="row">승인시간</th>
            <td><?php echo preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time); ?></td>
        </tr>
        <tr>
            <th scope="row">현금영수증 URL</th>
            <td>
                <input type="button" name="receiptView" value="영수증 확인" class="box" onClick="javascript:receiptView('<?php echo $receipt_no; ?>')">
                <p>영수증 확인은 실 등록의 경우에만 가능합니다.</p>
            </td>
        </tr>
<?php
        }
        else
        {
?>
        <tr>
            <th scope="row">결과코드</th>
            <td><?php echo $res_cd; ?></td>
        </tr>
        <tr>
            <th scope="row">결과 메세지</th>
            <td><?php echo $res_msg; ?></td>
        </tr>
<?php
        }
    }
?>
        </tbody>
        </table>
    </div>

</div>

</body>
</html>
