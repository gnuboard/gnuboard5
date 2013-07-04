<?php
include_once('./_common.php');

if ($is_guest)
    alert_close('회원만 조회하실 수 있습니다.');

$g4['title'] = $member['mb_nick'].' 님의 쿠폰 내역';
include_once(G4_PATH.'/head.sub.php');

$sql = " select cp_id, cp_subject, cp_method, cp_target, cp_start, cp_end, cp_type, cp_amount
            from {$g4['shop_coupon_table']}
            where mb_id = '{$member['mb_id']}'
              and cp_used = '0'
              and cp_start <= '".G4_TIME_YMD."'
              and cp_end >= '".G4_TIME_YMD."'
            order by cp_no ";
$result = sql_query($sql);

$count = @mysql_num_rows($result);
if(!$count)
    alert_close('보유하신 쿠폰이 없습니다.');
?>

<!-- 쿠폰 내역 시작 { -->
<div id="coupon" class="new_win">
    <h1 id="new_win_title"><?php echo $g4['title'] ?></h1>

    <table class="basic_tbl">
    <thead>
    <tr>
        <th scope="col">쿠폰명</th>
        <th scope="col">적용대상</th>
        <th scope="col">할인금액</th>
        <th scope="col">사용기한</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($row['cp_method'] == 1) {
            $sql = " select ca_name from {$g4['shop_category_table']} where ca_id = '{$row['cp_target']}' ";
            $ca = sql_fetch($sql);
            $cp_target = $ca['ca_name'].'의 상품할인';
        } else if($row['cp_method'] == 2) {
            $cp_target = '결제금액 할인';
        } else if($row['cp_method'] == 3) {
            $cp_target = '배송비 할인';
        } else {
            $sql = " select it_name from {$g4['shop_item_table']} where it_id = '{$row['cp_target']}' ";
            $it = sql_fetch($sql);
            $cp_target = $it['it_name'].' 상품할인';
        }

        if($row['cp_type'])
            $cp_amount = $row['cp_amount'].'%';
        else
            $cp_amount = number_format($row['cp_amount']).'원';
    ?>
    <tr>
        <td><?php echo $row['cp_subject']; ?></td>
        <td><?php echo $cp_target; ?></td>
        <td class="td_bignum"><?php echo $cp_amount; ?></td>
        <td class="td_datetime"><?php echo substr($row['cp_start'], 2, 8); ?> ~ <?php echo substr($row['cp_end'], 2, 8); ?></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
    </table>

    <div class="btn_win"><a href="javascript:;" onclick="window.close();">창닫기</a></div>
</div>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>