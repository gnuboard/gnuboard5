<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $config['cf_title'];?> 주문내역 처리 안내</title>
</head>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="margin:0 0 20px;padding:30px 30px 20px;background:#f7f7f7;color:#555;font-size:1.4em">
            <?php echo $config['cf_title'];?> 주문내역 처리 안내
        </h1>

        <?php if (count($cart_list)) { ?>
        <table style="margin:0 auto 20px;width:94%;border:0;border-collapse:collapse">
        <caption style="padding:0 0 5px;font-weight:bold">주문상품 확인</caption>
        <colgroup>
            <col>
            <col style="width:100px">
            <col style="width:70px">
            <col style="width:50px">
        </colgroup>
        <thead>
        <tr>
            <th scope="col" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">품명</th>
            <th scope="col" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">옵션</th>
            <th scope="col" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">상태</th>
            <th scope="col" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">수량</th>
        </tr>
        <thead>
        <tbody>
        <?php for ($i=0; $i<count($cart_list); $i++) { ?>
        <tr>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><a href="<?php echo G4_SHOP_URL; ?>/item.php?it_id=<?php echo $cart_list[$i]['it_id']; ?>" target="_blank" style="text-decoration:none"><?php echo $cart_list[$i]['it_name']; ?></a></td>
            <td style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;text-align:center"><?php echo $cart_list[$i]['it_opt']; ?></td>
            <td style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;text-align:center"><?php echo $cart_list[$i]['ct_status']; ?></td>
            <td style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;text-align:center"><?php echo $cart_list[$i]['ct_qty']; ?></td>
        </tr>
        <?php } // end for ?>
        </tbody>
        </table>
        <?php } // end if ?>

        <?php if (count($card_list)) { ?>
        <table style="margin:0 auto 20px;width:94%;border:0;border-collapse:collapse">
        <caption style="padding:0 0 5px;font-weight:bold">신용카드 결제 확인</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">승인일시</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $card_list['od_card_time']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">승인금액</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $card_list['od_receipt_card']; ?></td>
        </tr>
        </tbody>
        </table>
        <?php } ?>


        <?php if (count($bank_list)) { ?>
        <table style="margin:0 auto 20px;width:94%;border:0;border-collapse:collapse">
        <caption style="padding:0 0 5px;font-weight:bold">무통장 입금 확인 완료</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">확인일시</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $bank_list['od_bank_time']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">입금액</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $bank_list['od_receipt_bank']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">입금자명</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $bank_list['od_deposit_name']; ?></td>
        </tr>
        </tbody>
        </table>
        <?php } ?>

        <?php if (count($point_list)) { ?>
        <table style="margin:0 auto 20px;width:94%;border:0;border-collapse:collapse">
        <caption style="padding:0 0 5px;font-weight:bold">포인트 결제 확인</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">확인일시</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $point_list['od_time']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">포인트</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $point_list['od_receipt_point']; ?></td>
        </tr>
        </tbody>
        </table>
        <?php } ?>

        <?php if (count($delivery_list)) { ?>
        <table style="margin:0 auto 20px;width:94%;border:0;border-collapse:collapse">
        <caption style="padding:0 0 5px;font-weight:bold">배송 안내</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">배송회사</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><a href="<?php echo $delivery_list['dl_url']; ?>" target="_blank" style="text-decoration:none"><?php echo $delivery_list['dl_company']; ?></a></td>
        </tr>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">운송장번호</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $delivery_list['od_invoice']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">배송일시</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $delivery_list['od_invoice_time']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="padding:5px 0;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa">대표전화</th>
            <td style="padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9"><?php echo $delivery_list['dl_tel']; ?></td>
        </tr>
        </tbody>
        </table>
        <?php } ?>

        <?php if ($addmemo) { ?>
        <p style="margin:0 auto 20px;width:94%">
            <strong>전달사항</strong>
            <?php echo $addmemo; ?>
        </p>
        <?php } ?>

        <a href="<?php echo G4_SHOP_URL ?>" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center"><?php echo $config['cf_title'] ?></a>
    </div>
</div>

</body>
</html>
