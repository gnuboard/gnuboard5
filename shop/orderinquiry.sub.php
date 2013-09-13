<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined("_ORDERINQUIRY_")) exit; // 개별 페이지 접근 불가
?>

<!-- 주문 내역 목록 시작 { -->
<?php if (!$limit) { ?>총 <?php echo $cnt; ?> 건<?php } ?>

<table class="basic_tbl">
<thead>
<tr>
    <th scope="col">주문서번호</th>
    <th scope="col">주문일시</th>
    <th scope="col">상품수</th>
    <th scope="col">주문금액</th>
    <th scope="col">쿠폰</th>
    <th scope="col">입금액</th>
    <th scope="col">미입금액</th>
</tr>
</thead>
<tbody>
<?php
$sql = " select *,
            (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
           from {$g5['g5_shop_order_table']}
          where mb_id = '{$member['mb_id']}'
          group by od_id
          order by od_id desc
          $limit ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
?>

<tr>
    <td>
        <input type="hidden" name="ct_id[<?php echo $i; ?>]" value="<?php echo $row['ct_id']; ?>">
        <a href="<?php echo G5_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>"><?php echo $row['od_id']; ?></a>
    </td>
    <td><?php echo substr($row['od_time'],0,16); ?> (<?php echo get_yoil($row['od_time']); ?>)</td>
    <td class="td_num"><?php echo $row['od_cart_count']; ?></td>
    <td class="td_bignum"><?php echo display_price($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']); ?></td>
    <td class="td_bignum"><?php echo display_price($row['couponprice']); ?></td>
    <td class="td_bignum"><?php echo display_price($row['od_receipt_price']); ?></td>
    <td class="td_bignum"><?php echo display_price($row['od_misu']); ?></td>
</tr>

<?php
}

if ($i == 0)
    echo '<tr><td colspan="7" class="empty_table">주문 내역이 없습니다.</td></tr>';
?>
</tbody>
</table>
<!-- } 주문 내역 목록 끝 -->