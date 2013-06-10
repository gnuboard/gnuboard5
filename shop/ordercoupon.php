<?php
include_once('./_common.php');

if($is_guest)
    exit;

$amount = $_POST['amount'];

// 쿠폰정보
$sql = " select *
            from {$g4['shop_coupon_table']}
            where mb_id = '{$member['mb_id']}'
              and cp_method = '2'
              and cp_start <= '".G4_TIME_YMD."'
              and cp_end >= '".G4_TIME_YMD."'
              and cp_used = '0'
              and cp_minimum <= '$amount' ";
$result = sql_query($sql);
$count = mysql_num_rows($result);
?>

<div id="od_coupon_frm">
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
                $dc = floor(($amount * ($row['cp_amount'] / 100)) / $row['cp_trunc']) * $row['cp_trunc'];
            } else {
                $dc = $row['cp_amount'];
            }

            if($row['cp_maximum'] && $dc > $row['cp_maximum'])
                $dc = $row['cp_maximum'];
        ?>
        <li>
            <input type="hidden" name="o_cp_id[]" value="<?php echo $row['cp_id']; ?>">
            <input type="hidden" name="o_cp_amt[]" value="<?php echo $dc; ?>">
            <input type="hidden" name="o_cp_subj[]" value="<?php echo $row['cp_subject']; ?>">
            <span><?php echo get_text($row['cp_subject']); ?></span>
            <span><?php echo number_format($dc); ?></span>
            <span><button type="button" class="od_cp_apply">적용</button></span>
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
        <button type="button" id="od_coupon_close">닫기</button>
        <?php if($count > 0) { ?>
        <button type="button" id="od_coupon_cancel">쿠폰적용취소</button>
        <?php } ?>
    </div>
</div>