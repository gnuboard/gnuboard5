<?php
include_once('./_common.php');

$po_run = false;

if($it['it_id']) {
    $opt_subject = explode(',', $it['it_option_subject']);
    $opt1_subject = $opt_subject[0];
    $opt2_subject = $opt_subject[1];
    $opt3_subject = $opt_subject[2];

    $sql = " select * from {$g4['shop_item_option_table']} where io_type = '0' and it_id = '{$it['it_id']}' order by io_no asc ";
    $result = sql_query($sql);
    if(mysql_num_rows($result))
        $po_run = true;
} else if(!empty($_POST)) {
    $po_run = true;

    $opt1_subject = trim($_POST['opt1_subject']);
    $opt2_subject = trim($_POST['opt2_subject']);
    $opt3_subject = trim($_POST['opt3_subject']);

    $opt1_val = trim($_POST['opt1']);
    $opt2_val = trim($_POST['opt2']);
    $opt3_val = trim($_POST['opt3']);
    $opt1_count = $opt2_count = $opt3_count = 0;

    if($opt1_val) {
        $opt1 = explode(',', $opt1_val);
        $opt1_count = count($opt1);
    }

    if($opt2_val) {
        $opt2 = explode(',', $opt2_val);
        $opt2_count = count($opt2);
    }

    if($opt3_val) {
        $opt3 = explode(',', $opt3_val);
        $opt3_count = count($opt3);
    }
}

if($po_run) {
?>

<table class="frm_tbl">
<tbody>
<tr>
    <td rowspan="2"><input type="checkbox" name="opt_chk_all" value="1"></td>
    <td colspan="3">옵션항목</td>
    <td rowspan="2">추가금액</td>
    <td rowspan="2">재고수량</td>
    <td rowspan="2">통보수량</td>
    <td rowspan="2">사용여부</td>
</tr>
<tr>
    <td><?php echo $opt1_subject; ?></td>
    <td><?php echo $opt2_subject; ?></td>
    <td><?php echo $opt3_subject; ?></td>
</tr>
<?php
if($it['it_id']) {
    for($i=0; $row=sql_fetch_array($result); $i++) {
        $opt_id = $row['io_id'];
        $opt_val = explode(chr(30), $opt_id);
        $opt_1 = $opt_val[0];
        $opt_2 = $opt_val[1];
        $opt_3 = $opt_val[2];
        $opt_price = $row['io_price'];
        $opt_stock_qty = $row['io_stock_qty'];
        $opt_noti_qty = $row['io_noti_qty'];
        $opt_use = $row['io_use'];
?>
<tr>
    <input type="hidden" name="opt_id[]" value="<?php echo $opt_id; ?>">
    <td><input type="checkbox" name="opt_chk[]" value="1"></td>
    <td class="opt1-cell"><?php echo $opt_1; ?></td>
    <td class="opt2-cell"><?php echo $opt_2; ?></td>
    <td class="opt3-cell"><?php echo $opt_3; ?></td>
    <td><input type="text" name="opt_price[]" value="<?php echo $opt_price; ?>" class="frm_input" size="5"></td>
    <td><input type="text" name="opt_stock_qty[]" value="<?php echo $opt_stock_qty; ?>" class="frm_input" size="5"></td>
    <td><input type="text" name="opt_noti_qty[]" value="<?php echo $opt_noti_qty; ?>" class="frm_input" size="5"></td>
    <td>
        <select name="opt_use[]">
            <option value="1" <?php echo get_selected('1', $opt_use); ?>>사용함</option>
            <option value="0" <?php echo get_selected('0', $opt_use); ?>>사용안함</option>
        </select>
    </td>
</tr>
<?php
    } // for
} else {
    for($i=0; $i<$opt1_count; $i++) {
        $j = 0;
        do {
            $k = 0;
            do {
                $opt_1 = trim($opt1[$i]);
                $opt_2 = trim($opt2[$j]);
                $opt_3 = trim($opt3[$k]);

                $opt_id = $opt_1;
                if($opt_2)
                    $opt_id .= chr(30).$opt_2;
                if($opt_3)
                    $opt_id .= chr(30).$opt_3;
                $opt_price = 0;
                $opt_stock_qty = 0;
                $opt_noti_qty = 0;
                $opt_use = 1;
?>
<tr>
    <input type="hidden" name="opt_id[]" value="<?php echo $opt_id; ?>">
    <td><input type="checkbox" name="opt_chk[]" value="1"></td>
    <td class="opt1-cell"><?php echo $opt_1; ?></td>
    <td class="opt2-cell"><?php echo $opt_2; ?></td>
    <td class="opt3-cell"><?php echo $opt_3; ?></td>
    <td><input type="text" name="opt_price[]" value="<?php echo $opt_price; ?>" class="frm_input" size="5"></td>
    <td><input type="text" name="opt_stock_qty[]" value="<?php echo $opt_stock_qty; ?>" class="frm_input" size="5"></td>
    <td><input type="text" name="opt_noti_qty[]" value="<?php echo $opt_noti_qty; ?>" class="frm_input" size="5"></td>
    <td>
        <select name="opt_use[]">
            <option value="1" <?php echo get_selected('1', $opt_use); ?>>사용함</option>
            <option value="0" <?php echo get_selected('0', $opt_use); ?>>사용안함</option>
        </select>
    </td>
</tr>
<?php
                $k++;
            } while($k < $opt3_count);

            $j++;
        } while($j < $opt2_count);
    } // for
}
?>
</tbody>
</table>
<div><button type="button" id="sel_option_delete">선택삭제</button></div>
<div>
    <label for="opt_com_price">추가금액</label><input type="text" name="opt_com_price" value="0" id="opt_com_price" class="frm_input" size="5">
    <label for="opt_com_stock">재고수량</label><input type="text" name="opt_com_stock" value="0" id="opt_com_stock" class="frm_input" size="5">
    <label for="opt_com_noti">통보수량</label><input type="text" name="opt_com_noti" value="0" id="opt_com_noti" class="frm_input" size="5">
    <label for="opt_com_use">사용여부</label>
    <select name="opt_com_use" id="opt_com_use">
        <option value="1">사용함</option>
        <option value="0">사용안함</option>
    </select>
    <button type="button" id="opt_value_apply">일괄적용</button>
</div>
<?php
}
?>