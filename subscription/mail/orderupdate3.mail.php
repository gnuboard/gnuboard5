<?php //판매자님께 ?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $config['cf_title']; ?> - 주문 알림 메일</title>
</head>

<?php
$cont_st = 'margin:0 auto 20px;width:94%;border:0;border-collapse:collapse';
$caption_st = 'padding:0 0 5px;font-weight:bold';
$th_st = 'padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa;text-align:left';
$td_st = 'padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9';
$empty_st = 'padding:30px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;text-align:center';
$ft_a_st = 'display:block;padding:30px 0;background:#484848;color:#fff;text-align:center;text-decoration:none';
?>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="margin:0 0 20px;padding:30px 30px 20px;background:#f7f7f7;color:#555;font-size:1.4em">
            <?php echo $config['cf_title'];?> - 주문이 접수되었습니다.
        </h1>

        <p style="<?php echo $cont_st; ?>">
            <strong>주문번호 <?php echo $od_id; ?></strong><br>
            본 메일은 <?php echo G5_TIME_YMDHIS; ?> (<?php echo get_yoil(G5_TIME_YMDHIS); ?>)을 기준으로 작성되었습니다.
        </p>

        <table style="<?php echo $cont_st; ?>">
        <caption style="<?php echo $caption_st; ?>"> 주문 내역</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">상품명</th>
            <td style="<?php echo $td_st; ?>"><a href="<?php echo shop_item_url($list[$i]['it_id']); ?>" target="_blank" style="text-decoration:none"><span style="display:inline-block;vertical-align:middle"><?php echo $list[$i]['it_simg']; ?></span> <?php echo $list[$i]['it_name']; ?></a></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">판매가격</th>
            <td style="<?php echo $td_st; ?>"><?php echo display_price($list[$i]['ct_price']); ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">선택옵션 </th>
            <td style="<?php echo $td_st; ?>"><?php echo $list[$i]['it_opt']; ?></td>
        </tr>
        </tbody>
        </table>

        <table style="<?php echo $cont_st; ?>">
        <caption style="<?php echo $caption_st; ?>">주문하신 분 정보</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">이름</th>
            <td style="<?php echo $td_st; ?>"><?php echo $od_name; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">전화번호</th>
            <td style="<?php echo $td_st; ?>"><?php echo $od_tel; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">핸드폰</th>
            <td style="<?php echo $td_st; ?>"><?php echo $od_hp; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">주소</th>
            <td style="<?php echo $td_st; ?>"><?php echo sprintf("(%s%s)", $od_zip1, $od_zip2).' '.print_address($od_addr1, $od_addr2, $od_addr3, $od_addr_jibeon); ?></td>
        </tr>

        <?php if ($od_hope_date) { ?>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">희망배송일</th>
            <td style="<?php echo $td_st; ?>"><?php echo $od_hope_date;?> (<?php echo get_yoil($od_hope_date);?>)</td>
        </tr>
        <?php } ?>
        </tbody>
        </table>


        <table style="<?php echo $cont_st; ?>">
        <caption style="<?php echo $caption_st; ?>">배송지 정보</caption>
        <colgroup>
            <col style="width:130px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">이 름</th>
            <td style="<?php echo $td_st; ?>"><?php echo $od_b_name; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">전화번호</th>
            <td style="<?php echo $td_st; ?>"><?php echo $od_b_tel; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">핸드폰</th>
            <td style="<?php echo $td_st; ?>"><?php echo $od_b_hp; ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">주소</th>
            <td style="<?php echo $td_st; ?>"><?php echo sprintf("(%s%s)", $od_b_zip1, $od_b_zip2).' '.print_address($od_b_addr1, $od_b_addr2, $od_b_addr3, $od_b_addr_jibeon); ?></td>
        </tr>
        <tr>
            <th scope="row" style="<?php echo $th_st; ?>">전하실 말씀</th>
            <td style="<?php echo $td_st; ?>"><?php echo $od_memo; ?></td>
        </tr>
        </tbody>
        </table>

        <p style="<?php echo $cont_st; ?>">이 주문과 관련된 내용은 <b><?php echo  $default['de_admin_company_tel']; ?></b>로 연락주시기 바랍니다.</p>

        <a href="<?php echo G5_SHOP_URL.'/'; ?>" target="_blank" style="<?php echo $ft_a_st; ?>"><?php echo $config['cf_title']; ?></a>

</body>
</html>
