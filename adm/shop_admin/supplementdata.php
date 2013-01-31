<?php
include_once('./_common.php');

if(!$makemode) {
    $list = array();
    $sql = " select sp_id, sp_amount, sp_qty, sp_notice, sp_use
                from `{$g4['yc4_supplement_table']}`
                where it_id = '$it_id'
                order by sp_no asc ";
    $result = sql_query($sql);

    // 옵션정보
    for($i = 0; $row = sql_fetch_array($result); $i++) {
        $list[$i] = $row;
    }

    $option_count = count($list);

    if(!$option_count) {
        exit;
    }
} else {
    // 옵션명 중복체크
    $arr_subj = $_POST['sp_subject'];
    $arr_uniq = array_unique($arr_subj);
    if(count($arr_subj) != count($arr_uniq)) {
        echo '동일한 옵션명이 있습니다.';
        exit;
    }
}
?>

<table width="650" cellpadding="0" cellspacing="0" border="0">
<input type="hidden" name="it_id" value="<? echo $it_id; ?>" />
<input type="hidden" name="w" value="<? echo $w; ?>" />
<input type="hidden" name="makemode" value="<? echo $makemode; ?>" />
<tr>
    <td colspan="6" height="50">
    <b>추가금액</b> <input type="text" name="common_amount" value="" size="5" />&nbsp;&nbsp;&nbsp;<b>재고수량</b> <input type="text" name="common_qty" value="" size="5" />&nbsp;&nbsp;&nbsp;<b>통보수량</b> <input type="text" name="common_notice" value="" size="5" />
    &nbsp;&nbsp;&nbsp;<b>사용여부</b> <select name="common_use">
        <option value=''>선택</option>
        <option value="1">Y</option>
        <option value="0">N</option>
    </select>&nbsp;&nbsp;&nbsp;<button id="common_modify" type="button"> 일괄수정 </button>
    </td>
</tr>
<tr>
    <td width="50"><input type="checkbox" name="all_check" value="1" /></td>
    <th width="150" align="center">추가옵션명</th>
    <th width="150" align="center">추가옵션항목</th>
    <th width="75">추가금액</th>
    <th width="75">재고수량</th>
    <th width="75">통보수량</th>
    <th width="75">사용여부</th>
</tr>
<?php
if($option_count) {
    $str = '';
    for($i = 0; $i < $option_count; $i++) {
        $opt = explode(chr(30), $list[$i]['sp_id']);
        $str .= '<tr>';
        $sp_id = $list[$i]['sp_id'];

        $str .= '<td><input type="checkbox" name="list_check[]" value="1" /><input type="hidden" name="sp_id[]" value="'. $sp_id . '" /></td>';

        if(trim($opt[0]) && trim($opt[1])) {
            $str .= '<td>' . $opt[0] . '</td>';
            $str .= '<td>' . $opt[1] . '</td>';
        }

        if($list[$i]['sp_use']) {
            $sp_use1 = ' selected="selected"';
            $sp_use0 = '';
        } else {
            $sp_use1 = '';
            $sp_use0 = ' selected="selected"';
        }

        $str .= '<td><input type="text" name="sp_amount[]" value="' . $list[$i]['sp_amount'] . '" size="5" /></td>';
        $str .= '<td><input type="text" name="sp_qty[]" value="' . $list[$i]['sp_qty'] . '" size="5" /></td>';
        $str .= '<td><input type="text" name="sp_notice[]" value="' . $list[$i]['sp_notice'] . '" size="5" /></td>';
        $str .= '<td><select name="sp_use[]"><option value="1"'.$sp_use1.'>Y</option><option value="0"'.$sp_use0.'>N</option></select>';
        $str .= '</tr>';
    }
} else {
    $str = '';
    $sp_subj_count = count($_POST['sp_subject']);

    for($i = 0; $i < $sp_subj_count; $i++) {
        $str .= '<tr>';
        $sp_subj = $_POST['sp_subject'][$i];
        $sp_opt = explode(',', $_POST['sp_option'][$i]);
        $sp_opt_count = count($sp_opt);

        for($k = 0; $k < $sp_opt_count; $k++) {
            $sp_id = $sp_subj . chr(30) . $sp_opt[$k];
            $str .= '<td><input type="checkbox" name="list_check[]" value="1" /><input type="hidden" name="sp_id[]" value="'. $sp_id . '" /></td>';
            $str .= '<td>' . $sp_subj . '</td>';
            $str .= '<td>' . $sp_opt[$k] . '</td>';
            $str .= '<td><input type="text" name="sp_amount[]" value="0" size="5" /></td>';
            $str .= '<td><input type="text" name="sp_qty[]" value="0" size="5" /></td>';
            $str .= '<td><input type="text" name="sp_notice[]" value="0" size="5" /></td>';
            $str .= '<td><select name="sp_use[]"><option value="1">Y</option><optoin value="0">N</option></select>';
            $str .= '</tr>';
        }
    }
}

echo $str;
?>
<tr>
    <td colspan="6" height="50"><button type="button" id="option_item_delete"> 선택삭제 </button></td>
</tr>
<tr>
    <td colspan="6" height="50" align="center"><input type="submit" value=" 옵션변경 " /></td>
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
            $('input[name^=sp_amount]').val(common_amount);
        }
        if(common_qty) {
            $('input[name^=sp_qty]').val(common_qty);
        }
        if(common_notice) {
            $('input[name^=sp_notice]').val(common_notice);
        }
        if(common_use) {
            $('select[name^=sp_use]').val(common_use);
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

                var sp_id = $tr.find('input[name^=sp_id]').val();
                $.post(
                    './supplementdelete.php',
                    { it_id: '<?php echo $it_id; ?>', sp_id: sp_id }
                );

                $tr.remove();
            });
        }
    });
});
</script>