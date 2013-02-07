<?php
include_once('./_common.php');

if(!$option_count) {
    exit;
}

if($makemode) {
    // 옵션명 중복체크
    $arr_subj = array_unique($option_subject);
    if($option_count > 1 && count($arr_subj) != count($option_subject)) {
        echo '동일한 옵션명이 있습니다.';
        exit;
    }
} else {
    $list = array();
    $sql = " select opt_id, opt_amount, opt_qty, opt_notice, opt_use
                from `{$g4['shop_option_table']}`
                where it_id = '$it_id'
                order by opt_no asc ";
    $result = sql_query($sql);

    $rec_count = mysql_num_rows($result);

    // 옵션정보
    if($rec_count) {
            for($i = 0; $row = sql_fetch_array($result); $i++) {
            $list[$i] = $row;
        }

        $opt = explode(chr(30), $list[0]['opt_id']);
        $option_count = count($opt);
        $option_list = count($list);
    }
}
?>

<table width="650">
<input type="hidden" name="it_id" value="<? echo $it_id; ?>" />
<input type="hidden" name="w" value="<? echo $w; ?>" />
<input type="hidden" name="makemode" value="" />
<tr>
    <td colspan="<?php echo ($option_count + 5); ?>" height="50">
    <b>추가금액</b> <input type="text" name="common_amount" value="" size="5" />&nbsp;&nbsp;&nbsp;<b>재고수량</b> <input type="text" name="common_qty" value="" size="5" />&nbsp;&nbsp;&nbsp;<b>통보수량</b> <input type="text" name="common_notice" value="" size="5" />
    &nbsp;&nbsp;&nbsp;<b>사용여부</b> <select name="common_use">
        <option value=''>선택</option>
        <option value="1">Y</option>
        <option value="0">N</option>
    </select>&nbsp;&nbsp;&nbsp;<button id="common_modify" type="button"> 일괄수정 </button>
    </td>
</tr>
<tr>
    <td rowspan="2" width="50"><input type="checkbox" name="all_check" value="1" /></td>
    <th colspan="<?php echo $option_count; ?>" align="center">옵션항목</th>
    <th rowspan="2" width="75">추가금액</th>
    <th rowspan="2" width="75">재고수량</th>
    <th rowspan="2" width="75">통보수량</th>
    <th rowspan="2" width="75">사용여부</th>
</tr>
<tr>
    <?php
    for($i = 0; $i < $option_count; $i++) {
    ?>
    <td class="col<?php echo $option_count; ?>" align="center"><?php echo get_text($option_subject[$i]); ?></td>
    <?php
    }
    ?>
</tr>
<?php
if($rec_count) {
    $str = '';
    for($i = 0; $i < $option_list; $i++) {
        $opt = explode(chr(30), $list[$i]['opt_id']);
        $str .= '<tr>';
        $opt_id = $list[$i]['opt_id'];

        $str .= '<td><input type="checkbox" name="list_check[]" value="1" /><input type="hidden" name="opt_id[]" value="'. $opt_id . '" /></td>';

        if(trim($opt[0])) {
            $str .= '<td class="cell-opt1">' . $opt[0] . '</td>';
        }

        if(trim($opt[1])) {
            $str .= '<td class="cell-opt2">' . $opt[1] . '</td>';
        }

        if(trim($opt[2])) {
            $str .= '<td class="cell-opt3">' . $opt[2] . '</td>';
        }

        if($list[$i]['opt_use']) {
            $opt_use1 = ' selected="selected"';
            $opt_use0 = '';
        } else {
            $opt_use1 = '';
            $opt_use0 = ' selected="selected"';
        }

        $str .= '<td><input type="text" name="opt_amount[]" value="' . $list[$i]['opt_amount'] . '" size="5" /></td>';
        $str .= '<td><input type="text" name="opt_qty[]" value="' . $list[$i]['opt_qty'] . '" size="5" /></td>';
        $str .= '<td><input type="text" name="opt_notice[]" value="' . $list[$i]['opt_notice'] . '" size="5" /></td>';
        $str .= '<td><select name="opt_use[]"><option value="1"'.$opt_use1.'>Y</option><option value="0"'.$opt_use0.'>N</option></select>';
        $str .= '</tr>';
    }
} else {
    $str = '';
    $opt1_item = explode(',', $option_item[0]);
    $opt2_item = explode(',', $option_item[1]);
    $opt3_item = explode(',', $option_item[2]);

    $opt1_item_count = count($opt1_item);
    $opt2_item_count = count($opt2_item);
    $opt3_item_count = count($opt3_item);

    for($i = 0; $i < $opt1_item_count; $i++) {
        for($j = 0; $j < $opt2_item_count; $j++) {
            for($k = 0; $k < $opt3_item_count; $k++) {
                $str .= '<tr>';
                $opt_id = '';

                if(trim($opt1_item[$i])) {
                    $str1 = '<td class="cell-opt1">' . $opt1_item[$i] . '</td>';
                    $opt_id .= $opt1_item[$i];
                }

                if(trim($opt2_item[$j])) {
                    $str2 = '<td class="cell-opt2">' . $opt2_item[$j] . '</td>';
                    $opt_id .= chr(30) . $opt2_item[$j];
                }

                if(trim($opt3_item[$k])) {
                    $str3 = '<td class="cell-opt3">' . $opt3_item[$k] . '</td>';
                    $opt_id .= chr(30) . $opt3_item[$k];
                }

                $str .= '<td><input type="checkbox" name="list_check[]" value="1" /><input type="hidden" name="opt_id[]" value="'. $opt_id . '" /></td>';
                $str .= $str1 . $str2 . $str3;
                $str .= '<td><input type="text" name="opt_amount[]" value="0" size="5" /></td>';
                $str .= '<td><input type="text" name="opt_qty[]" value="0" size="5" /></td>';
                $str .= '<td><input type="text" name="opt_notice[]" value="0" size="5" /></td>';
                $str .= '<td><select name="opt_use[]"><option value="1">Y</option><optoin value="0">N</option></select>';
                $str .= '</tr>';
            }
        }
    }
}

echo $str;
?>
<tr>
    <td colspan="<?php echo ($option_count + 5); ?>" height="50"><button type="button" id="option_item_delete"> 선택삭제 </button></td>
</tr>
<tr>
    <td colspan="<?php echo ($option_count + 5); ?>" height="50" align="center"><input type="submit" value=" 옵션변경 " /></td>
</tr>
</table>

<script>
$(document).ready(function() {
    // 모두선택
    $('input[name=all_check]').click(function() {
        if($(this).is(':checked')) {
            $('input[name^=list_check]').attr('checked', true);
        } else {
            $('input[name^=list_check]').attr('checked', false);
        }
    });

    // 일괄수정
    $('button#common_modify').click(function() {
        var common_amount = $.trim($('input[name=common_amount]').val());
        var common_qty = $.trim($('input[name=common_qty]').val());
        var common_notice = $.trim($('input[name=common_notice]').val());
        var common_use = $('select[name=common_use]').val();

        if(common_amount == '' && common_qty == '' && common_notice == '' && common_use == '') {
            alert('추가금액, 재고수량, 통보수량, 사용여부 중 1개 이상의 값을 입력해 주세요.');
            return false;
        }

        if(common_amount) {
            $('input[name^=opt_amount]').val(common_amount);
        }
        if(common_qty) {
            $('input[name^=opt_qty]').val(common_qty);
        }
        if(common_notice) {
            $('input[name^=opt_notice]').val(common_notice);
        }
        if(common_use) {
            $('select[name^=opt_use]').val(common_use);
        }
    });

    // 옵션항목삭제
    $('#option_item_delete').click(function() {
        var $selected_option = $('input[name^=list_check]:checked');
        if($selected_option.size() < 1) {
            alert('삭제할 옵션항목을 1개 이상 선택해 주세요.');
            return false;
        }

        if(confirm('선택 옵션항목을 삭제하시겠습니까?')) {
            $selected_option.each(function() {
                var $tr = $(this).closest('tr');

                var opt_id = $tr.find('input[name^=opt_id]').val();
                $.post(
                    './optiondelete.php',
                    { it_id: '<?php echo $it_id; ?>', opt_id: opt_id }
                );

                $tr.remove();
            });
        }
    });
});
</script>