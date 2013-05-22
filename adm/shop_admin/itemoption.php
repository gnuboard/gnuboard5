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

<div id="sit_option_frm_wrapper">
    <table>
    <thead>
    <tr>
        <th scope="col">
            <label for="opt_chk_all" class="sound_only">전체 옵션 선택</label>
            <input type="checkbox" name="opt_chk_all" value="1" id="opt_chk_all">
        </th>
        <th scope="col">옵션</th>
        <th scope="col">추가금액</th>
        <th scope="col">재고수량</th>
        <th scope="col">통보수량</th>
        <th scope="col">사용여부</th>
    </tr>
    </thead>
    <tbody>
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
        <td class="td_chk">
            <input type="hidden" name="opt_id[]" value="<?php echo $opt_id; ?>">
            <input type="checkbox" name="opt_chk[]" value="1">
        </td>
        <td class="opt-cell"><?php echo $opt_1; if ($opt_2) echo ' <small>&gt;</small> '.$opt_2; if ($opt_3) echo ' <small>&gt;</small> '.$opt_3; ?></td>
        <td class="td_bignum"><input type="text" name="opt_price[]" value="<?php echo $opt_price; ?>" class="frm_input" size="9"></td>
        <td class="td_num"><input type="text" name="opt_stock_qty[]" value="<?php echo $opt_stock_qty; ?>" class="frm_input" size="5"></td>
        <td class="td_num"><input type="text" name="opt_noti_qty[]" value="<?php echo $opt_noti_qty; ?>" class="frm_input" size="5"></td>
        <td class="td_mng">
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
                    $opt_1 = strip_tags(trim($opt1[$i]));
                    $opt_2 = strip_tags(trim($opt2[$j]));
                    $opt_3 = strip_tags(trim($opt3[$k]));

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
        <td class="td_chk">
            <input type="hidden" name="opt_id[]" value="<?php echo $opt_id; ?>">
            <input type="checkbox" name="opt_chk[]" value="1">
        </td>
        <td class="opt1-cell"><?php echo $opt_1; if ($opt_2) echo ' <small>&gt;</small> '.$opt_2; if ($opt_3) echo ' <small>&gt;</small> '.$opt_3; ?></td>
        <td class="td_bignum"><input type="text" name="opt_price[]" value="<?php echo $opt_price; ?>" class="frm_input" size="9"></td>
        <td class="td_num"><input type="text" name="opt_stock_qty[]" value="<?php echo $opt_stock_qty; ?>" class="frm_input" size="5"></td>
        <td class="td_num"><input type="text" name="opt_noti_qty[]" value="<?php echo $opt_noti_qty; ?>" class="frm_input" size="5"></td>
        <td class="td_mng">
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
</div>

<div class="btn_list">
    <input type="button" value="선택삭제" id="sel_option_delete">
</div>

<fieldset>
    <legend>옵션 일괄 적용</legend>
    <?php echo help('전체 옵션의 추가금액, 재고/통보수량 및 사용여부를 일괄 적용할 수 있습니다.'); ?>
    <label for="opt_com_price">추가금액</label><input type="text" name="opt_com_price" value="0" id="opt_com_price" class="frm_input" size="5">
    <label for="opt_com_stock">재고수량</label><input type="text" name="opt_com_stock" value="0" id="opt_com_stock" class="frm_input" size="5">
    <label for="opt_com_noti">통보수량</label><input type="text" name="opt_com_noti" value="0" id="opt_com_noti" class="frm_input" size="5">
    <label for="opt_com_use">사용여부</label>
    <select name="opt_com_use" id="opt_com_use">
        <option value="1">사용함</option>
        <option value="0">사용안함</option>
    </select>
    <button type="button" id="opt_value_apply" class="btn_frmline">일괄적용</button>
</fieldset>
<?php
}
?>