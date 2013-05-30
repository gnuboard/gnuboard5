<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined("_ORDERINQUIRY_")) exit; // 개별 페이지 접근 불가
?>

<?php if (!$limit) { ?>총 <?php echo $cnt; ?> 건<?php } ?>

<table class="basic_tbl">
<thead>
<tr>
    <th scope="col">주문번호</th>
    <th scope="col">주문일시</th>
    <th scope="col">주문금액</th>
    <th scope="col">입금액</th>
</tr>
</thead>
<tbody>
<?php
$sql = " select a.od_id,
                a.*, "._MISU_QUERY_."
           from {$g4['shop_order_table']} a
           left join {$g4['shop_cart_table']} b on (b.uq_id=a.uq_id)
          where a.mb_id = '{$member['mb_id']}'
          group by a.od_id
          order by a.od_id desc
          $limit ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
?>

<tr>
    <td>
        <input type="hidden" name="ct_id[<?php echo $i; ?>]" value="<?php echo $row['ct_id']; ?>">
        <a href="<?php echo G4_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uq_id=<?php echo $row['uq_id']; ?>"><?php echo $row['od_id']; ?></a>
    </td>
    <td class="td_datetime"><?php echo substr($row['od_time'],0,16); ?> (<?php echo get_yoil($row['od_time']); ?>)</td>
    <td class="td_bignum"><?php echo display_price($row['orderamount']); ?></td>
    <td class="td_stat"><?php echo display_price($row['receiptamount']); ?></td>
</tr>

<?php
}

if ($i == 0)
    echo '<tr><td colspan="4" class="empty_table">주문 내역이 없습니다.</td></tr>';
?>
</tbody>
</table>
