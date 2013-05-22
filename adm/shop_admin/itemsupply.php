<?php
include_once('./_common.php');

$ps_run = false;

if($it['it_id']) {
    $sql = " select * from {$g4['shop_item_option_table']} where io_type = '1' and it_id = '{$it['it_id']}' order by io_no asc ";
    $result = sql_query($sql);
    if(mysql_num_rows($result))
        $ps_run = true;
} else if(!empty($_POST)) {
    $ps_run = true;
    $subject_count = count($_POST['subject']);
}

if($ps_run) {
?>

<table class="frm_tbl">
<tbody>
<tr>
    <td><input type="checkbox" name="spl_chk_all" value="1"></td>
    <td>옵션명</td>
    <td>옵션항목</td>
    <td>추가금액</td>
    <td>재고수량</td>
    <td>통보수량</td>
    <td>사용여부</td>
</tr>
<?php
if($it['it_id']) {
    for($i=0; $row=sql_fetch_array($result); $i++) {
        $spl_id = $row['io_id'];
        $spl_val = explode(chr(30), $spl_id);
        $spl_subject = $spl_val[0];
        $spl = $spl_val[1];
        $spl_price = $row['io_price'];
        $spl_stock_qty = $row['io_stock_qty'];
        $spl_noti_qty = $row['io_noti_qty'];
        $spl_use = $row['io_use'];
?>
<tr>
    <input type="hidden" name="spl_id[]" value="<?php echo $spl_id; ?>">
    <td><input type="checkbox" name="spl_chk[]" value="1"></td>
    <td><?php echo $spl_subject; ?></td>
    <td><?php echo $spl; ?></td>
    <td><input type="text" name="spl_price[]" value="<?php echo $spl_price; ?>" class="frm_input" size="5"></td>
    <td><input type="text" name="spl_stock_qty[]" value="<?php echo $spl_stock_qty; ?>" class="frm_input" size="5"></td>
    <td><input type="text" name="spl_noti_qty[]" value="<?php echo $spl_noti_qty; ?>" class="frm_input" size="5"></td>
    <td>
        <select name="spl_use[]">
            <option value="1" <?php echo get_selected('1', $spl_use); ?>>사용함</option>
            <option value="0" <?php echo get_selected('0', $spl_use); ?>>사용안함</option>
        </select>
    </td>
</tr>
<?php
    } // for
} else {
    for($i=0; $i<$subject_count; $i++) {
        $spl_subject = trim($_POST['subject'][$i]);
        $spl_val = explode(',', trim($_POST['supply'][$i]));
        $spl_count = count($spl_val);

        for($j=0; $j<$spl_count; $j++) {
            $spl = trim($spl_val[$j]);
            if($spl_subject && $spl) {
                $spl_id = $spl_subject.chr(30).$spl;
                $spl_price = 0;
                $spl_stock_qty = 0;
                $spl_noti_qty = 0;
                $spl_use = 1;
?>
<tr>
    <input type="hidden" name="spl_id[]" value="<?php echo $spl_id; ?>">
    <td><input type="checkbox" name="spl_chk[]" value="1"></td>
    <td><?php echo $spl_subject; ?></td>
    <td><?php echo $spl; ?></td>
    <td><input type="text" name="spl_price[]" value="<?php echo $spl_price; ?>" class="frm_input" size="5"></td>
    <td><input type="text" name="spl_stock_qty[]" value="<?php echo $spl_stock_qty; ?>" class="frm_input" size="5"></td>
    <td><input type="text" name="spl_noti_qty[]" value="<?php echo $spl_noti_qty; ?>" class="frm_input" size="5"></td>
    <td>
        <select name="spl_use[]">
            <option value="1" <?php echo get_selected('1', $spl_use); ?>>사용함</option>
            <option value="0" <?php echo get_selected('0', $spl_use); ?>>사용안함</option>
        </select>
    </td>
</tr>
<?php
            } // if
        } // for
    } // for
}
?>
</tbody>
</table>
<div><button type="button" id="sel_supply_delete">선택삭제</button></div>
<div>
    <label for="spl_com_price">추가금액</label><input type="text" name="spl_com_price" value="0" id="spl_com_price" class="frm_input" size="5">
    <label for="spl_com_stock">재고수량</label><input type="text" name="spl_com_stock" value="0" id="spl_com_stock" class="frm_input" size="5">
    <label for="spl_com_noti">통보수량</label><input type="text" name="spl_com_noti" value="0" id="spl_com_noti" class="frm_input" size="5">
    <label for="spl_com_use">사용여부</label>
    <select name="spl_com_use" id="spl_com_use">
        <option value="1">사용함</option>
        <option value="0">사용안함</option>
    </select>
    <button type="button" id="spl_value_apply">일괄적용</button>
</div>
<?php
}
?>