<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if(empty($rq)) {
    $sql = " select * from {$g4['shop_request_table']} where rq_id = '$rq_id' ";
    $rq = sql_fetch($sql);
}

$sql = " select ct_id, it_id, it_name, ct_option, ct_price, ct_qty, io_type, io_price, ct_status, ct_notax
            from {$g4['shop_cart_table']}
            where uq_id = '{$od['uq_id']}'
            order by ct_id ";
$result = sql_query($sql);
?>

<section id="sodr_request_item">
    <h3><?php echo $type; ?>요청 상품</h3>
    <table>
    <thead>
    <tr>
        <th scope="col">상품명</th>
        <th scope="col">옵션항목</th>
        <th scope="col">판매가</th>
        <th scope="col">수량</th>
        <th scope="col">소계</th>
        <th scope="col">상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $excp = 0;
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if(!in_array($row['ct_id'], $item)) {
            $excp++;
            continue;
        }

        $image = get_it_image($row['it_id'], 50, 50);

        if($row['io_type']) {
            $price = $row['io_price'];
            $tot_price = $row['io_price'] * $row['ct_qty'];
        } else {
            $price = $row['ct_price'] + $row['io_price'];
            $tot_price = ($row['ct_price'] + $row['io_price']) * $row['ct_qty'];
        }
    ?>
    <tr>
        <td>
            <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>"><?php echo $image; ?> <?php echo stripslashes($row['it_name']); ?></a>
            <?php if($default['de_tax_flag_use'] && $row['ct_notax']) echo '[비과세상품]'; ?>
        </td>
        <td><?php echo $row['ct_option']; ?></td>
        <td><?php echo number_format($price); ?></td>
        <td><?php echo number_format($row['ct_qty']); ?></td>
        <td><?php echo number_format($tot_price); ?></td>
        <td><?php echo $row['ct_status']; ?></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
    </table>
</section>

<div id="order_request">
    <?php
    // 요청 처리폼 include
    $rq_qstr = "sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;save_stx=$save_stx&amp;page=$page&amp;rq_type=$rq_type";
    include_once('./orderrequestform.php');
    ?>
</div>