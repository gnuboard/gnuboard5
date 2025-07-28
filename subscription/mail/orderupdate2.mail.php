<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
//고객님께
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $config['cf_title']; ?> - 정기구독 신청 확인</title>
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
        <h1 style="<?php echo $headerH1Style; ?>"><?php echo $config['cf_title'];?> - 정기구독 신청이 완료되었습니다!</h1>
    </div>
    <div style="<?php echo $contentStyle; ?>">
        <h2 style="<?php echo $contentH2Style; ?>">구독번호 <?php echo $od_id; ?></h2>
        <p style="<?php echo $textStyle; ?>">본 메일은 <?php echo G5_TIME_YMDHIS; ?> (<?php echo get_yoil(G5_TIME_YMDHIS); ?>)을 기준으로 작성되었습니다.</p>

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
                    <td style="<?php echo $tdStyle; ?>"><?php echo display_price($list[$i]['ct_price']); ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>

        <div style="<?php echo $sectionStyle; ?>">
            <h3 style="<?php echo $sectionH3Style; ?>">정기구독 내역</h3>
            <table style="<?php echo $tableStyle; ?>">
                <tr>
                    <th style="<?php echo $thStyle; ?>">구독번호</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $od_id; ?></td>
                </tr>
                <?php if ($od_send_cost > 0) { // 배송비가 있다면 ?>
                <tr>
                    <th scope="row" style="<?php echo $th_st; ?>">배송비</th>
                    <td style="<?php echo $td_st; ?>"><?php echo display_price($od_send_cost); ?></td>
                </tr>
                <?php } ?>

                <?php if ($od_send_cost2 > 0) { // 추가배송비가 있다면 ?>
                <tr>
                    <th scope="row" style="<?php echo $th_st; ?>">추가배송비</th>
                    <td style="<?php echo $td_st; ?>"><?php echo display_price($od_send_cost2); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th style="<?php echo $thStyle; ?>">결제예정금액</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo display_price($od_receipt_price); ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">주문합계</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo display_price($ttotal_price); ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">결제카드정보</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo print_subscription_card_info($od); ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">배송주기</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $od_deliverys; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">총 이용횟수</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $od_usages; ?></td>
                </tr>
                <?php if ($od_hope_date) { ?>
                <tr>
                    <th style="<?php echo $thStyle; ?>">희망배송일</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo date('Y-m-d', strtotime($od_hope_date)); ?> (<?php echo get_yoil($od_hope_date);?>)</td>
                </tr>
                <?php } ?>
                <tr>
                    <th style="<?php echo $thStyle; ?>">첫 결제일(예정)</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo date('Y-m-d', strtotime($nextBillingDate)); ?></td>
                </tr>
            </table>
        </div>

        <div style="<?php echo $sectionStyle; ?>">
            <h3 style="<?php echo $sectionH3Style; ?>">배송지 정보</h3>
            <table style="<?php echo $tableStyle; ?>">
                <tr>
                    <th style="<?php echo $thStyle; ?>">이 름</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $od_b_name; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">연락처</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $od_b_tel; ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">주소</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo sprintf("(%s%s)", $od_b_zip1, $od_b_zip2).' '.print_address($od_b_addr1, $od_b_addr2, $od_b_addr3, $od_b_addr_jibeon); ?></td>
                </tr>
                <tr>
                    <th style="<?php echo $thStyle; ?>">전하실 말씀</th>
                    <td style="<?php echo $tdStyle; ?>"><?php echo $od_memo; ?></td>
                </tr>
            </table>
        </div>
        
        <a href="<?php echo G5_SUBSCRIPTION_URL.'/subscription_list.php'; ?>" target="_blank" style="<?php echo $buttonStyle; ?>">내 계정에서 구독 관리</a>
    </div>
    <div style="<?php echo $footerStyle; ?>">
        <p style="<?php echo $footerLinkStyle; ?>">
            <a href="<?php echo G5_URL; ?>" style="<?php echo $linkStyle; ?>"><?php echo $config['cf_title'];?> - 웹사이트 방문</a>
        </p>
    </div>
</div>

</body>
</html>
