<?php
$sub_menu = '400440';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g4['title'] = '개인결제 관리';

if ($w == 'u') {
    $html_title = '개인결제 수정';

    $sql = " select * from {$g4['shop_personalpay_table']} where pp_id = '$pp_id' ";
    $pp = sql_fetch($sql);
    if (!$pp['pp_id']) alert('등록된 자료가 없습니다.');
}
else
{
    $html_title = '개인결제 입력';
    $pp['pp_use'] = 1;
}

if($popup == 'yes') {
    include_once(G4_PATH.'/head.sub.php');
    $pp['od_id'] = $od_id;
    $sql = " select od_id, od_name, (od_temp_amount - od_receipt_amount) as misu
                from {$g4['shop_order_table']}
                where od_id = '$od_id' ";
    $od = sql_fetch($sql);

    if(!$od['od_id'])
        alert_close('주문정보가 존재하지 않습니다.');

    $pp['pp_name'] = $od['od_name'];

    if($od['misu'] > 0)
        $pp['pp_amount'] = $od['misu'];
}
else
    include_once (G4_ADMIN_PATH.'/admin.head.php');
?>
<form name="fpersonalpayform" action="./personalpayformupdate.php" method="post" onsubmit="return form_check(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="pp_id" value="<?php echo $pp_id; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="popup" value="<?php echo $popup; ?>">

<section class="cbox">
    <h2><?php echo $html_title; ?></h2>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="pp_name">이름</label></th>
        <td><input type="text" name="pp_name" value="<?php echo $pp['pp_name']; ?>" id="pp_name" required class="required frm_input"></td>
    </tr>
    <tr>
        <th scope="row"><label for="pp_amount">주문금액</label></th>
        <td><input type="text" name="pp_amount" value="<?php echo $pp['pp_amount']; ?>" id="pp_amount" required class="required frm_input" size="15"> 원</td>
    </tr>
    <tr>
        <th scope="row"><label for="od_id">주문번호</label></th>
        <td><input type="text" name="od_id" value="<?php echo $pp['od_id'] ? $pp['od_id'] : ''; ?>" id="od_id" class="frm_input" size="20"></td>
    </tr>
    <tr>
        <th scope="row"><label for="pp_content">내용</label></th>
        <td><textarea name="pp_content" id="pp_content" rows="8"><?php echo $pp['pp_content']; ?></textarea></td>
    </tr>
    <?php if($popup != 'yes') { ?>
    <tr>
        <th scope="row"><label for="pp_receipt_amount">결제금액</label></th>
        <td><input type="text" name="pp_receipt_amount" value="<?php echo $pp['pp_receipt_amount'] ? $pp['pp_receipt_amount'] : ''; ?>" id="pp_receipt_amount" class="frm_input" size="15"> 원</td>
    </tr>
    <tr>
        <th scope="row"><label for="pp_settle_case">결제방법</label></th>
        <td>
            <select name="pp_settle_case" id="pp_settle_case">
                <option value="" <?php echo get_selected($pp['pp_settle_case'], ''); ?>>선택</option>
                <option value="무통장" <?php echo get_selected($pp['pp_settle_case'], '무통장'); ?>>무통장</option>
                <option value="계좌이체" <?php echo get_selected($pp['pp_settle_case'], '계좌이체'); ?>>계좌이체</option>
                <option value="가상계좌" <?php echo get_selected($pp['pp_settle_case'], '가상계좌'); ?>>가상계좌</option>
                <option value="신용카드" <?php echo get_selected($pp['pp_settle_case'], '신용카드'); ?>>신용카드</option>
                <option value="휴대폰" <?php echo get_selected($pp['pp_settle_case'], '휴대폰'); ?>>휴대폰</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="pp_receipt_time">결제일시</label></th>
        <td>
            <label for="pp_receipt_chk">현재 시간으로 설정</label>
            <input type="checkbox" name="pp_receipt_chk" id="pp_receipt_chk" value="<?php echo date("Y-m-d H:i:s", G4_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.pp_receipt_time.value=this.form.pp_receipt_chk.value; else this.form.pp_receipt_time.value = this.form.pp_receipt_time.defaultValue;"><br>
            <input type="text" name="pp_receipt_time" value="<?php echo is_null_time($pp['pp_receipt_time']) ? "" : $pp['pp_receipt_time']; ?>" id="pp_receipt_time" class="frm_input" maxlength="19">
        </td>
    </tr>
    <?php } ?>
    <tr>
        <th scope="row"><label for="pp_shop_memo">상점메모</label></th>
        <td><textarea name="pp_shop_memo" id="pp_shop_memo" rows="8"><?php echo $pp['pp_shop_memo']; ?></textarea></td>
    </tr>
    <tr>
        <th scope="row"><label for="pp_use">사용</label></th>
        <td>
            <select name="pp_use" id="pp_use">
                <option value="1" <?php echo get_selected($pp['pp_use'], 1); ?>>사용함</option>
                <option value="0" <?php echo get_selected($pp['pp_use'], 0); ?>>사용안함</option>
            </select>
        </td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="확인" class="btn_submit" accesskey="s">
        <?php if($popup == 'yes') { ?>
        <button type="button" onclick="self.close();">닫기</button>
        <?php } else { ?>
        <a href="./personalpaylist.php?<?php echo $qstr; ?>">목록</a>
        <?php } ?>
        <?php if($w == 'u') { ?>
        <a href="./personalpayformupdate.php?w=d&amp;pp_id=<?php echo $pp['pp_id']; ?>" onclick="return del_confirm();">삭제</a>
        <?php } ?>
    </div>
</section>
</form>

<script>
function form_check(f)
{
    if(f.pp_amount.value.replace(/[0-9]/g, "").length > 0) {
        alert("주문금액은 숫자만 입력해 주십시오");
        return false;
    }

    return true;
}

function del_confirm()
{
    return confirm("개인결제 정보를 삭제하시겠습니까?\n\n삭제한 정보는 복구할 수 없습니다.");
}
</script>

<?php
if($popup == 'yes')
    include_once(G4_PATH.'/tail.sub.php');
else
    include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>