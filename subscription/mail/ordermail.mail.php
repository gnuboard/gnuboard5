<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $config['cf_title'];?> - 주문내역 처리 안내</title>
</head>

<?php
$cont_st = 'margin:0 auto 20px;width:94%;border:0;border-collapse:collapse';
$caption_st = 'padding:0 0 5px;font-weight:bold';
$th_st = 'padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa';
$td_st = 'padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9';
$ft_a_st = 'display:block;padding:30px 0;background:#484848;color:#fff;text-align:center;text-decoration:none';
?>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="margin:0 0 20px;padding:30px 30px 20px;background:#f7f7f7;color:#555;font-size:1.4em">
            <?php echo $config['cf_title'];?> - 주문내역 처리 안내
        </h1>

        <?php if (count($cart_list)) { ?>
        <table style="<?php echo $cont_st; ?>">
        <caption style="<?php echo $caption_st; ?>">주문상품 확인</caption>
        <colgroup>
            <col>
            <col>
            <col style="width:70px">
        </colgroup>
        <thead>
        <tr>
            <th scope="col" style="<?php echo $th_st; ?>">품명</th>
            <th scope="col" style="<?php echo $th_st; ?>">옵션</th>
            <th scope="col" style="<?php echo $th_st; ?>">상태</th>
        </tr>
        <thead>
        <tbody>
        <?php for ($h=0; $h<count($cart_list); $h++) { ?>
        <tr>
            <td style="<?php echo $td_st; ?>"><a href="<?php echo shop_item_url($cart_list[$h]['it_id']); ?>" target="_blank" style="text-decoration:none"><?php echo $cart_list[$h]['it_name']; ?></a></td>
            <td style="<?php echo $td_st; ?>;text-align:center"><?php echo $cart_list[$h]['it_opt']; ?></td>
            <td style="<?php echo $td_st; ?>;text-align:center"><?php echo $cart_list[$h]['ct_status']; ?></td>
        </tr>
        <?php } // end for ?>
        </tbody>
        </table>
        <?php } // end if ?>

        <?php if (isset($card_list) && is_array($card_list) && $card_list) { ?>
        <table style="<?php echo $cont_st; ?>">
        <caption style="<?php echo $caption_st; ?>">신용카드 결제 확인</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">승인일시</th>
            <td style="<?php echo $td_st; ?>"><?php echo $card_list['od_receipt_time']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">승인금액</th>
            <td style="<?php echo $td_st; ?>"><?php echo $card_list['od_receipt_price']; ?></td>
        </tr>
        </tbody>
        </table>
        <?php } ?>


        <?php if (count($bank_list)) { ?>
        <table style="<?php echo $cont_st; ?>">
        <caption style="<?php echo $caption_st; ?>">무통장 입금 확인 완료</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">확인일시</th>
            <td style="<?php echo $td_st; ?>"><?php echo $bank_list['od_receipt_time']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">입금액</th>
            <td style="<?php echo $td_st; ?>"><?php echo $bank_list['od_receipt_price']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">입금자명</th>
            <td style="<?php echo $td_st; ?>"><?php echo $bank_list['od_deposit_name']; ?></td>
        </tr>
        </tbody>
        </table>
        <?php } ?>

        <?php if (count($point_list)) { ?>
        <table style="<?php echo $cont_st; ?>">
        <caption style="<?php echo $caption_st; ?>">포인트 결제 확인</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">확인일시</th>
            <td style="<?php echo $td_st; ?>"><?php echo $point_list['od_time']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">포인트</th>
            <td style="<?php echo $td_st; ?>"><?php echo $point_list['od_receipt_point']; ?></td>
        </tr>
        </tbody>
        </table>
        <?php } ?>

        <?php if (count($delivery_list)) { ?>
        <table style="<?php echo $cont_st; ?>">
        <caption style="<?php echo $caption_st; ?>">배송 안내</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">배송회사</th>
            <td style="<?php echo $td_st; ?>"><?php echo $delivery_list['dl_company']; ?> <?php echo $delivery_list['dl_inquiry']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">운송장번호</th>
            <td style="<?php echo $td_st; ?>"><?php echo $delivery_list['od_invoice']; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">배송일시</th>
            <td style="<?php echo $td_st; ?>"><?php echo $delivery_list['od_invoice_time']; ?></td>
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

        <a href="<?php echo G5_SHOP_URL ?>" target="_blank" style="<?php echo $ft_a_st; ?>"><?php echo $config['cf_title'] ?></a>
    </div>
</div>

</body>
</html>
