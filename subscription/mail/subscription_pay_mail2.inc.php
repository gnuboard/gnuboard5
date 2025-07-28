<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
//고객님께
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $config['cf_title']; ?> - 정기구독 결제 내역 안내</title>
</head>

<?php
// Define common styles as PHP variables
$containerStyle = "max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);";
$headerStyle = "background-color: #2c3e50; color: #ffffff; padding: 20px; text-align: center;";
$headerH1Style = "margin: 0; font-size: 24px;";
$contentStyle = "padding: 30px;";
$contentH2Style = "font-size: 20px; color: #333333; margin-top: 0;";
$textStyle = "color: #333333;";
$sectionStyle = "margin: 20px 0;";
$sectionH3Style = "font-size: 16px; color: #333333;";
$tableStyle = "width: 100%; border-collapse: collapse; margin: 10px 0;";
$thStyle = "padding: 10px; border-bottom: 1px solid #eeeeee; text-align: left; background-color: #f8f8f8; color: #333333;";
$tdStyle = "padding: 10px; border-bottom: 1px solid #eeeeee; text-align: left; color: #333333;";
$linkStyle = "color: #3498db; text-decoration: none;";
$buttonStyle = "display: inline-block; padding: 12px 24px; background-color: #3498db; color: #ffffff; text-decoration: none; border-radius: 4px; margin: 20px 0; text-align: center;";
$footerStyle = "background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 14px; color: #666666;";
$footerTextStyle = "margin: 0;";
$footerLinkStyle = "margin: 5px 0;";
?>

<body>

<div style="<?php echo $containerStyle; ?>">
    <div style="<?php echo $headerStyle; ?>">
        <h1 style="<?php echo $headerH1Style; ?>"><?php echo $config['cf_title'];?> - 정기구독 결제 내역 안내</h1>
    </div>
    <div style="<?php echo $contentStyle; ?>">
        <?php if ($pay['py_test']) { ?>
            <p style="color:red;background:#ffecec;padding:1em;">이 결제는 테스트로 결제되었습니다. 실제결제가 아닙니다.</p>
        <?php } ?>
        <p style="<?php echo $textStyle; ?>">정기구독 결제가 성공적으로 처리되었습니다. 아래에서 결제 및 구독 내역을 확인해 주세요.</p>
        
        <div style="<?php echo $sectionStyle; ?>">
            <h3 style="<?php echo $sectionH3Style; ?>">상품정보</h3>
            <table style="<?php echo $tableStyle; ?>">
                <?php for ($i=0; $i<count($list); $i++) { ?>
                <tr>
                    <th style="<?php echo $thStyle; ?>">상품명</th>
                    <td style="<?php echo $tdStyle; ?>"><a href="<?php echo subscription_item_url($list[$i]['it_id']); ?>" target="_blank" style="text-decoration:none"><span style="display:inline-block;vertical-align:middle"><?php echo $list[$i]['it_simg']; ?></span> <?php echo $list[$i]['it_name']; ?></a></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">선택옵션</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $list[$i]['it_opt']; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">상품가격</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo display_price($list[$i]['pb_price']); ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>

        <div style="<?php echo $sectionStyle; ?>">
            <h3 style="<?php echo $sectionH3Style; ?>">정기구독 내역</h3>
            <table style="<?php echo $tableStyle; ?>">
                <tr>
                    <th style="<?php echo $thStyle; ?>">주문번호</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $pay['subscription_pg_id']; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">승인번호</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $pay['py_app_no']; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">결제회차</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $pay['py_round_no']; ?> 회차</td>
                </tr>
                <?php if ($py_send_cost > 0) { // 배송비가 있다면 ?>
                <tr>
                    <th scope="row" style="<?php echo $th_st; ?>">배송비</th>
                    <td style="<?php echo $td_st; ?>"><?php echo display_price($py_send_cost); ?></td>
                </tr>
                <?php } ?>

                <?php if ($py_send_cost2 > 0) { // 추가배송비가 있다면 ?>
                <tr>
                    <th scope="row" style="<?php echo $th_st; ?>">추가배송비</th>
                    <td style="<?php echo $td_st; ?>"><?php echo display_price($py_send_cost2); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th style="<?php echo $thStyle; ?>">결제금액</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo display_price((int)$pay['py_receipt_price']); ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">주문합계</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo display_price($ttotal_price); ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">결제날짜</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $pay['py_receipt_time']; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">결제카드정보</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo print_subscription_card_info($pay); ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">배송주기</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $od_deliverys; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">총 이용횟수</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $od_usages; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">다음결제일(예정)</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo date('Y-m-d', strtotime($nextBillingDate)); ?> (<?php echo $pay['py_round_no'] + 1; ?> 회차)</td>
                </tr>
            </table>
        </div>

        <div style="<?php echo $sectionStyle; ?>">
            <h3 style="<?php echo $sectionH3Style; ?>">배송지 정보</h3>
            <table style="<?php echo $tableStyle; ?>">
                <tr>
                    <th style="<?php echo $thStyle; ?>">이 름</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $pay['py_b_name']; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">연락처</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $pay['py_b_tel']; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">주소</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo sprintf("(%s)", $pay['py_b_zip']).' '.print_address($pay['py_b_addr1'], $pay['py_b_addr2'], $pay['py_b_addr3'], $pay['py_b_addr_jibeon']); ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">전하실 말씀</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo conv_content($pay['py_memo'], 0); ?></td>
                </tr>
            </table>
        </div>
        
        <a href="<?php echo G5_SUBSCRIPTION_URL.'/orderinquiryview.php?od_id='.$pay['od_id'].'#sod_fin_dvr_list'; ?>" target="_blank" style="<?php echo $buttonStyle; ?>">내 계정에서 정기결제 내역 확인</a>
    </div>
    <div style="<?php echo $footerStyle; ?>">
        <p style="<?php echo $footerLinkStyle; ?>">
            <a href="<?php echo G5_URL; ?>" style="<?php echo $linkStyle; ?>"><?php echo $config['cf_title'];?> - 웹사이트 방문</a>
        </p>
    </div>
</div>

</body>
</html>
