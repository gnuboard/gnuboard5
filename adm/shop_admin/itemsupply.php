<?php
include_once('./_common.php');

$ps_run = false;
$post_it_id = isset($_POST['it_id']) ? safe_replace_regex($_POST['it_id'], 'it_id') : '';

if(isset($it['it_id']) && $it['it_id']) {
    $sql = " select * from {$g5['g5_shop_item_option_table']} where io_type = '1' and it_id = '{$it['it_id']}' order by io_no asc ";
    $result = sql_query($sql);
    if(sql_num_rows($result))
        $ps_run = true;
} else if(!empty($_POST)) {
    $subject_count = (isset($_POST['subject']) && is_array($_POST['subject'])) ? count($_POST['subject']) : 0;
    $supply_count = (isset($_POST['supply']) && is_array($_POST['supply'])) ? count($_POST['supply']) : 0;

    if(!$subject_count || !$supply_count) {
        echo '추가옵션명과 추가옵션항목을 입력해 주십시오.';
        exit;
    }

    $ps_run = true;
}

if($ps_run) {
?>
<div class="sit_option_frm_wrapper">
    <table>
    <caption>추가옵션 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="spl_chk_all" class="sound_only">전체 추가옵션</label>
            <input type="checkbox" name="spl_chk_all" value="1">
        </th>
        <th scope="col">옵션명</th>
        <th scope="col">옵션항목</th>
        <th scope="col">상품금액</th>
        <th scope="col">재고수량</th>
        <th scope="col">통보수량</th>
        <th scope="col">사용여부</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(isset($it['it_id']) && $it['it_id']) {
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
        <td class="td_chk">
            <input type="hidden" name="spl_id[]" value="<?php echo $spl_id; ?>">
            <label for="spl_chk_<?php echo $i; ?>" class="sound_only"><?php echo $spl_subject.' '.$spl; ?></label>
            <input type="checkbox" name="spl_chk[]" id="spl_chk_<?php echo $i; ?>" value="1">
        </td>
        <td class="spl-subject-cell"><?php echo $spl_subject; ?></td>
        <td class="spl-cell"><?php echo $spl; ?></td>
        <td class="td_numsmall">
            <label for="spl_price_<?php echo $i; ?>" class="sound_only">상품금액</label>
            <input type="text" name="spl_price[]" value="<?php echo $spl_price; ?>" id="spl_price_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num">
            <label for="spl_stock_qty_<?php echo $i; ?>" class="sound_only">재고수량</label>
            <input type="text" name="spl_stock_qty[]" value="<?php echo $spl_stock_qty; ?>" id="spl_stock_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num">
            <label for="spl_noti_qty_<?php echo $i; ?>" class="sound_only">통보수량</label>
            <input type="text" name="spl_noti_qty[]" value="<?php echo $spl_noti_qty; ?>" id="spl_noti_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_mng">
            <label for="spl_use_<?php echo $i; ?>" class="sound_only">사용여부</label>
            <select name="spl_use[]" id="spl_use_<?php echo $i; ?>">
                <option value="1" <?php echo get_selected('1', $spl_use); ?>>사용함</option>
                <option value="0" <?php echo get_selected('0', $spl_use); ?>>사용안함</option>
            </select>
        </td>
    </tr>
    <?php
        } // for
    } else {
        for($i=0; $i<$subject_count; $i++) {
            $spl_subject = isset($_POST['subject'][$i]) ? preg_replace(G5_OPTION_ID_FILTER, '', strip_tags(trim(stripslashes($_POST['subject'][$i])))) : '';
            $spl_val = isset($_POST['supply'][$i]) ? explode(',', preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['supply'][$i])))) : '';
            $spl_count = count($spl_val);

            for($j=0; $j<$spl_count; $j++) {
                $spl = isset($spl_val[$j]) ? strip_tags(trim($spl_val[$j])) : '';
                if($spl_subject && strlen($spl)) {
                    $spl_id = $spl_subject.chr(30).$spl;
                    $spl_price = 0;
                    $spl_stock_qty = 9999;
                    $spl_noti_qty = 100;
                    $spl_use = 1;

                    // 기존에 설정된 값이 있는지 체크
                    if(isset($_POST['w']) && $_POST['w'] === 'u') {
                        $sql = " select io_price, io_stock_qty, io_noti_qty, io_use
                                    from {$g5['g5_shop_item_option_table']}
                                    where it_id = '{$post_it_id}'
                                      and io_id = '".sql_real_escape_string($spl_id)."'
                                      and io_type = '1' ";
                        $row = sql_fetch($sql);

                        if($row) {
                            $spl_price = (int)$row['io_price'];
                            $spl_stock_qty = (int)$row['io_stock_qty'];
                            $spl_noti_qty = (int)$row['io_noti_qty'];
                            $spl_use = (int)$row['io_use'];
                        }
                    }
    ?>
    <tr>
        <td class="td_chk">
            <input type="hidden" name="spl_id[]" value="<?php echo get_text($spl_id); ?>">
            <label for="spl_chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($spl_subject.' '.$spl); ?></label>
            <input type="checkbox" name="spl_chk[]" id="spl_chk_<?php echo $i; ?>" value="1">
        </td>
        <td class="spl-subject-cell"><?php echo get_text($spl_subject); ?></td>
        <td class="spl-cell"><?php echo $spl; ?></td>
        <td class="td_numsmall">
            <label for="spl_price_<?php echo $i; ?>" class="sound_only">상품금액</label>
            <input type="text" name="spl_price[]" value="<?php echo $spl_price; ?>" id="spl_price_<?php echo $i; ?>" class="frm_input" size="9">
        </td>
        <td class="td_num">
            <label for="spl_stock_qty_<?php echo $i; ?>" class="sound_only">재고수량</label>
            <input type="text" name="spl_stock_qty[]" value="<?php echo $spl_stock_qty; ?>" id="spl_stock_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num">
            <label for="spl_noti_qty_<?php echo $i; ?>" class="sound_only">통보수량</label>
            <input type="text" name="spl_noti_qty[]" value="<?php echo $spl_noti_qty; ?>" id="spl_noti_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_mng">
            <label for="spl_use_<?php echo $i; ?>" class="sound_only">사용여부</label>
            <select name="spl_use[]" id="spl_use_<?php echo $i; ?>">
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
</div>

<div class="btn_list01 btn_list">
    <button type="button" id="sel_supply_delete" class="btn btn_02">선택삭제</button>
</div>

<fieldset>
    <?php echo help('전체 추가 옵션의 상품금액, 재고/통보수량 및 사용여부를 일괄 적용할 수 있습니다.  단, 체크된 수정항목만 일괄 적용됩니다.'); ?>
    <label for="spl_com_price">상품금액</label>
    <label for="spl_com_price_chk" class="sound_only">상품금액일괄수정</label><input type="checkbox" name="spl_com_price_chk" value="1" id="spl_com_price_chk" class="spl_com_chk">
    <input type="text" name="spl_com_price" value="0" id="spl_com_price" class="frm_input" size="9">
    <label for="spl_com_stock">재고수량</label>
    <label for="spl_com_stock_chk" class="sound_only">재고수량일괄수정</label><input type="checkbox" name="spl_com_stock_chk" value="1" id="spl_com_stock_chk" class="spl_com_chk">
    <input type="text" name="spl_com_stock" value="0" id="spl_com_stock" class="frm_input" size="5">
    <label for="spl_com_noti">통보수량</label>
    <label for="spl_com_noti_chk" class="sound_only">통보수량일괄수정</label><input type="checkbox" name="spl_com_noti_chk" value="1" id="spl_com_noti_chk" class="spl_com_chk">
    <input type="text" name="spl_com_noti" value="0" id="spl_com_noti" class="frm_input" size="5">
    <label for="spl_com_use">사용여부</label>
    <label for="spl_com_use_chk" class="sound_only">사용여부일괄수정</label><input type="checkbox" name="spl_com_use_chk" value="1" id="spl_com_use_chk" class="spl_com_chk">
    <select name="spl_com_use" id="spl_com_use">
        <option value="1">사용함</option>
        <option value="0">사용안함</option>
    </select>
    <button type="button" id="spl_value_apply" class="btn_frmline">일괄적용</button>
</fieldset>
<?php
}