<?php
include_once('./_common.php');

if($is_guest)
    exit;

// 상품정보
$it_id = $_POST['it_id'];
$sql = " select it_id, ca_id, ca_id2, ca_id3 from {$g4['shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

// 상품 총 금액
$uq_id = get_session('ss_uq_id');
$sql = " select SUM( IF(io_type = '1', io_price * ct_qty, (ct_price + io_price) * ct_qty)) as sum_price
            from {$g4['shop_cart_table']}
            where uq_id = '$uq_id'
              and it_id = '$it_id' ";
$ct = sql_fetch($sql);
$item_price = $ct['sum_price'];

// 쿠폰정보
$sql = " select *
            from {$g4['shop_coupon_table']}
            where mb_id = '{$member['mb_id']}'
              and cp_used = '0'
              and cp_start <= '".G4_TIME_YMD."'
              and cp_end >= '".G4_TIME_YMD."'
              and cp_minimum <= '$item_price'
              and (
                    ( cp_method = '0' and cp_target = '{$it['it_id']}' )
                    OR
                    ( cp_method = '1' and ( cp_target IN ( '{$it['ca_id']}', '{$it['ca_id2']}', '{$it['ca_id3']}' ) ) )
                  ) ";
$result = sql_query($sql);
$count = mysql_num_rows($result);
?>

<div id="it_coupon_frm">
    <?php if($count > 0) { ?>
    <ul>
        <li>
            <span>쿠폰명</span>
            <span>할인금액</span>
            <span>적용</span>
        </li>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $dc = 0;
            if($row['cp_type']) {
                $dc = floor(($item_price * ($row['cp_amount'] / 100)) / $row['cp_trunc']) * $row['cp_trunc'];
            } else {
                $dc = $row['cp_amount'];
            }

            if($row['cp_maximum'] && $dc > $row['cp_maximum'])
                $dc = $row['cp_maximum'];
        ?>
        <li>
            <input type="hidden" name="f_cp_id[]" value="<?php echo $row['cp_id']; ?>">
            <input type="hidden" name="f_cp_amt[]" value="<?php echo $dc; ?>">
            <input type="hidden" name="f_cp_subj[]" value="<?php echo $row['cp_subject']; ?>">
            <span><?php echo get_text($row['cp_subject']); ?></span>
            <span><?php echo number_format($dc); ?></span>
            <span><button type="button" class="cp_apply">적용</button></span>
        </li>
        <?php
        }
        ?>
    </ul>
    <?php
    } else {
        echo '사용할 수 있는 쿠폰이 없습니다.';
    }
    ?>
    <div>
        <button type="button" id="it_coupon_close">닫기</button>
        <?php if($count > 0) { ?>
        <button type="button" id="it_coupon_cancel">쿠폰적용취소</button>
        <?php } ?>
    </div>
</div>