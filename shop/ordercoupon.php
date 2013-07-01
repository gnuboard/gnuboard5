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

<!-- 쿠폰 선택 시작 { -->
<div id="od_coupon_frm">
    <?php if($count > 0) { ?>
    <table class="basic_tbl">
    <caption>쿠폰 선택</caption>
    <thead>
    <tr>
        <th scope="col">쿠폰명</th>
        <th scope="col">할인금액</th>
        <th scope="col">적용</th>
    </tr>
    </thead>
    <tbody>
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
    <tr>
        <td>
            <input type="hidden" name="o_cp_id[]" value="<?php echo $row['cp_id']; ?>">
            <input type="hidden" name="o_cp_amt[]" value="<?php echo $dc; ?>">
            <input type="hidden" name="o_cp_subj[]" value="<?php echo $row['cp_subject']; ?>">
            <?php echo get_text($row['cp_subject']); ?>
        </td>
        <td class="td_bignum"><?php echo number_format($dc); ?></td>
        <td class="td_smallmng"><button type="button" class="od_cp_apply btn_frmline">적용</button></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
    </table>
    <?php
    } else {
        echo '<p>사용할 수 있는 쿠폰이 없습니다.</p>';
    }
    ?>
    <div class="btn_confirm">
        <button type="button" id="od_coupon_close" class="btn_submit">닫기</button>
    </div>
</div>
<!-- } 쿠폰 선택 끝 -->