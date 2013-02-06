<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$html_title = '추가옵션설정';

if ($is_admin != 'super')
{
    $sql = " select it_id from `{$g4['yc4_item_table']}` a, `{$g4['yc4_category_table']}` b
              where a.it_id = '$it_id'
                and a.ca_id = b.ca_id
                and b.ca_mb_id = '{$member['mb_id']}' ";
    $row = sql_fetch($sql);
    if (!$row[it_id])
        alert("\'{$member[mb_id]}\' 님께서 수정 할 권한이 없는 상품입니다.");
}

$sql = " select sp_id from {$g4['yc4_supplement_table']} where it_id = '$it_id' order by sp_id asc ";
$result = sql_query($sql);
$spl_count = mysql_num_rows($result);

if($spl_count) {
    $spl_subject = array();
    $spl_item = array();
    $idx = 0;
    $str = '';
    $deli = '';

    for($i=0; $row=sql_fetch_array($result); $i++) {
        $opt = explode(chr(30), $row['sp_id']);

        if(!in_array($opt[0], $spl_subject)) {
            if($i > 0) {
                $idx++;
                $str = '';
                $deli = '';
            }

            $spl_subject[$idx] = $opt[0];
        }

        $str .= $deli . $opt[1];
        $deli = ',';

        $spl_item[$idx] = $str;
    }
}

$g4['title'] = $html_title;
include_once (G4_PATH.'/head.sub.php');
?>

<style type="text/css">
<!--
#container { width: 650px; margin: 0 auto; }
#container ul { margin: 0; padding: 0; list-style: none; }
#container form { display: inline; }
#container .col1 { width: 300px; }
#container .col2 { width: 150px; }
#container .col3 { width: 100px; }
#container .option_item_delete { cursor: pointer; }
#container #AddRow { cursor: pointer; }
#container .RowRemove { cursor: pointer; }
-->
</style>

<div id="container">
    <form id="supplementform">
    <table>
    <tr>
        <th width="150">옵션명</th>
        <th width="450">옵션항목(,로 구분)</th>
    </tr>
    <?
    if(!$spl_count) {
    ?>
    <tr>
        <td><input type="text" id="sp_subject[]" name="sp_subject[]" class="sp_subject" value="<? echo $spl_subject[$i]; ?>" size="15" /></td>
        <td><input type="text" id="sp_option[]" name="sp_option[]" class="sp_option" value="<? echo $spl_item[$i]; ?>" size="50" /></td>
    </tr>
    <?
    }
    ?>
    <?
    if($spl_count) {
        $rm_btn = '';
        for($i=0;$i<count($spl_subject); $i++) {
            if($i > 0) {
                $rm_btn = ' <span class="RowRemove">삭제</span>';
            }
    ?>
    <tr>
        <td><input type="text" id="sp_subject[]" name="sp_subject[]" class="sp_subject" value="<? echo $spl_subject[$i]; ?>" size="15" /></td>
        <td><input type="text" id="sp_option[]" name="sp_option[]" class="sp_option" value="<? echo $spl_item[$i]; ?>" size="50" /><? echo $rm_btn; ?></td>
    </tr>
    <?
        }
    }
    ?>
    <tr>
        <td colspan="2" align="right"><span id="AddRow">입력행추가</span></td>
    </tr>
    <tr>
        <td colspan="2" height="50" align="center" /><input type="submit" value=" 목록생성 " /></td>
    </tr>
    </table>
    </form>
    <div id="OptTable">
    <form id="fsupplementtable" method="post" action="./supplementformupdate.php">
    </form>
    </div>
</div>

<script>
$(document).ready(function() {
    supplementTableMake('');

    // 입력행추가
    $('#AddRow').click(function() {
        var tr_content = '<tr>';
        tr_content += '<td><input type="text" id="sp_subject[]" name="sp_subject[]" class="sp_subject" value="" size="15" /></td>';
        tr_content += '<td><input type="text" id="sp_option[]" name="sp_option[]" class="sp_option" value="" size="50" /> <span class="RowRemove">삭제</span></td>';
        tr_content += '</tr>';

        $('#AddRow').closest('tr').before(tr_content);
    });

    // 입력행제거
    $('.RowRemove').live('click', function() {
        $(this).closest('tr').remove();
    });

    $('form#supplementform').submit(function() {
        // 첫번째 옵션명 체크
        if(!$.trim($(this).find('input.sp_subject:first').val())) {
            alert('옵션명을 입력해 주십시오.');
            $('input.sp_subject:first').focus();
            return false;
        }

        // 옵션명과 옵션항목이 있는지 체크
        var err = false;
        $('input.sp_subject').each(function(index) {
            var subj = $.trim($(this).val());
            var opt = $.trim($('input.sp_option:eq('+index+')').val());

            if(subj != '' && opt == '') {
                alert('옵션항목을 입력해 주세요.');
                err = true;
                $('input.sp_option:eq('+index+')').focus();
                return false;
            }

            if(subj == '' && opt != '') {
                alert('옵션명을 입력해 주세요.');
                err = true;
                $(this).focus();
                return false;
            }
        });

        if(!err) {
            supplementTableMake('create');
        }

        return false;
    });
});

function supplementTableMake(makemode)
{
    var it_id = '<?php echo $it_id; ?>';
    var sp_subject = new Array();
    var sp_option = new Array();
    var $sp_subject = $('form#supplementform').find('input.sp_subject');
    var $sp_option = $('form#supplementform').find('input.sp_option');
    var dbcheck = false;

    $sp_subject.each(function(index) {
        var subj = $.trim($(this).val());
        var opt = $.trim($sp_option.eq(index).val());

        if(subj != '' && opt != '') {
            // 동일한 옵션명이 있는지
            if(makemode) {
                if(subj && $.inArray(subj, sp_subject) > -1) {
                    alert('동일한 옵션명이 있습니다.');
                    dbcheck = true;
                    return false;
                }
            }

            sp_subject.push(subj);
            sp_option.push(opt);
        }
    });

    if(dbcheck) {
        return false;
    }

    $.post(
        './supplementdata.php',
        { it_id: it_id, w: '<? echo $w; ?>', makemode: makemode, 'sp_subject[]': sp_subject, 'sp_option[]': sp_option },
        function(data) {
            $('#OptTable form').empty().html(data);
        }
    );
}
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>