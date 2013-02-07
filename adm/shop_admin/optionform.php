<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$html_title = '선택옵션설정';

if ($is_admin != 'super')
{
    $sql = " select it_id from `{$g4['shop_item_table']}` a, `{$g4['shop_category_table']}` b
              where a.it_id = '$it_id'
                and a.ca_id = b.ca_id
                and b.ca_mb_id = '{$member['mb_id']}' ";
    $row = sql_fetch($sql);
    if (!$row[it_id])
        alert("\'{$member[mb_id]}\' 님께서 수정 할 권한이 없는 상품입니다.");
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
-->
</style>

<div id="container">
    <form id="optionform">
    <table>
    <tr>
        <th width="150">옵션명</th>
        <th width="450">옵션항목(,로 구분)</th>
    </tr>
    <tr>
        <td><input type="text" id="it_opt1_subject" name="it_opt1_subject" class="it_opt_subject" value="" size="15" /></td>
        <td><input type="text" id="it_opt1" name="it_opt1" class="it_opt" value="" size="50" /></td>
    </tr>
    <tr>
        <td><input type="text" id="it_opt2_subject" name="it_opt2_subject" class="it_opt_subject" value="" size="15" /></td>
        <td><input type="text" id="it_opt2" name="it_opt2" class="it_opt" value="" size="50" /></td>
    </tr>
    <tr>
        <td><input type="text" id="it_opt3_subject" name="it_opt3_subject" class="it_opt_subject" value="" size="15" /></td>
        <td><input type="text" id="it_opt3" name="it_opt3" class="it_opt" value="" size="50" /></td>
    </tr>
    <tr>
        <td colspan="2" height="50" align="center" /><input type="submit" value=" 목록생성 " /></td>
    </tr>
    </table>
    </form>
    <div id="OptTable">
    <form id="foptiontable" method="post" action="./optionformupdate.php">
    </form>
    </div>
</div>

<script>
var it_id = '<?php echo $it_id; ?>';
$(document).ready(function() {
    var $opener = window.opener;

    $('input[name=it_opt1_subject]').val($opener.$('input[name=it_opt1_subject]').val());
    $('input[name=it_opt2_subject]').val($opener.$('input[name=it_opt2_subject]').val());
    $('input[name=it_opt3_subject]').val($opener.$('input[name=it_opt3_subject]').val());
    $('input[name=it_opt1]').val($opener.$('input[name=it_opt1]').val());
    $('input[name=it_opt2]').val($opener.$('input[name=it_opt2]').val());
    $('input[name=it_opt3]').val($opener.$('input[name=it_opt3]').val());

    optionTableMake('');

    $('form#optionform').submit(function() {
        // 첫번째 옵션명 체크
        if(!$.trim($(this).find('input.it_opt_subject:first').val())) {
            alert('옵션명을 입력해 주십시오.');
            $('input.it_opt_subject:first').focus();
            return false;
        }

        optionTableMake('create');

        return false;
    });

    $('form#foptiontable').submit(function() {
        // 옵션 테이블의 항목체크
        var opt1_subject = opt2_subject = opt3_subject = '';
        var opt1 = opt2 = opt3 = str = '';
        var $cell_opt1 = $('td.cell-opt1');
        var $cell_opt2 = $('td.cell-opt2');
        var $cell_opt3 = $('td.cell-opt3');
        var dblcheck = false;

        if($cell_opt1.size() > 0) {
            $cell_opt1.each(function() {
                var opt = $(this).text();
                if(opt1 == '') {
                    opt1 = opt;
                    return true;
                } else {
                    str = opt1.split(',');
                    for(i=0; i<str.length; i++) {
                        if(str[i] == opt) {
                            dblcheck = true;
                            break;
                        } else {
                            dblcheck = false;
                            continue;
                        }
                    }

                    if(dblcheck) {
                        return true;
                    } else {
                        opt1 += ','+opt;
                    }
                }
            });
        }

        if($cell_opt2.size() > 0) {
            $cell_opt2.each(function() {
                var opt = $(this).text();
                if(opt2 == '') {
                    opt2 = opt;
                    return true;
                } else {
                    str = opt2.split(',');
                    for(i=0; i<str.length; i++) {
                        if(str[i] == opt) {
                            dblcheck = true;
                            break;
                        } else {
                            dblcheck = false;
                            continue;
                        }
                    }

                    if(dblcheck) {
                        return true;
                    } else {
                        opt2 += ','+opt;
                    }
                }
            });
        }

        if($cell_opt3.size() > 0) {
            $cell_opt3.each(function() {
                var opt = $(this).text();
                if(opt3 == '') {
                    opt3 = opt;
                    return true;
                } else {
                    str = opt3.split(',');
                    for(i=0; i<str.length; i++) {
                        if(str[i] == opt) {
                            dblcheck = true;
                            break;
                        } else {
                            dblcheck = false;
                            continue;
                        }
                    }

                    if(dblcheck) {
                        return true;
                    } else {
                        opt3 += ','+opt;
                    }
                }
            });
        }

        if(opt1) {
            opt1_subject = $('input[name=it_opt1_subject]').val();
        }
        if(opt2) {
            opt2_subject = $('input[name=it_opt2_subject]').val();
        }
        if(opt3) {
            opt3_subject = $('input[name=it_opt3_subject]').val();
        }

        $opener.$('input[name=it_opt1_subject]').val(opt1_subject);
        $opener.$('input[name=it_opt2_subject]').val(opt2_subject);
        $opener.$('input[name=it_opt3_subject]').val(opt3_subject);
        $opener.$('input[name=it_opt1]').val(opt1);
        $opener.$('input[name=it_opt2]').val(opt2);
        $opener.$('input[name=it_opt3]').val(opt3);

        return true;
    });
});

function optionTableMake(makemode)
{
    var it_opt_subject = new Array();
    var it_opt = new Array();
    var option_count = 0;
    var option_error = false;

    var $form = $('form#optionform');
    var $opt_subject = $form.find('input.it_opt_subject');

    $opt_subject.each(function(index) {
        var subj = $.trim($(this).val());
        var item = $.trim($('input.it_opt:eq(' + index +')').val()).replace(/\,$/, '');

        // 다음 줄의 옵션 정보 구함
        var $nextsubj = $(this).closest('tr').next();
        var nextcount = $nextsubj.has('input.it_opt_subject').length;
        var nsubj = '';
        var nitem = '';

        if(nextcount) {
            nsubj = $.trim($nextsubj.find('input.it_opt_subject').val());
            nitem = $.trim($nextsubj.find('input.it_opt').val());
        }

        // 다음 줄의 옵션 정보에 옵션명이나 옵션 항목이 있을 때 현재 줄의 옵션 정보는 필수 입력
        if(makemode) { // 테이블 새로 생성시만 체크
            if(nsubj != '' || nitem != '') {
                if(subj == '' && item == '') {
                    alert('옵션명과 옵션항목을 입력해 주십시오.');
                    $('input.it_opt_subject:eq(' + index + ')').focus();
                    option_error = true;
                    return false;
                }
            }

            if(subj == '') {
                if(item != '') {
                    alert('옵션명을 입력해 주십시오.');
                    $('input.it_opt_subject:eq(' + index + ')').focus();
                    option_error = true;
                    return false;
                }
            } else {
                if(item == '') {
                    alert('옵션항목을 입력해 주십시오.');
                    $('input.it_opt:eq(' + index + ')').focus();
                    option_error = true;
                    return false;
                }
            }
        }

        if(!option_error) {
            // 동일한 옵션명이 있는지
            if(makemode) {
                if(subj && $.inArray(subj, it_opt_subject) > -1) {
                    alert('동일한 옵션명이 있습니다.');
                    option_error = true;
                    return false;
                }
            }

            it_opt_subject.push(subj);
            it_opt.push(item);

            if(subj != '' && item != '') {
                option_count++;
            }
        }
    });

    if(option_error) {
        return false;
    }

    $.post(
        './optiondata.php',
        { it_id: it_id, w: '<? echo $w; ?>', makemode: makemode, option_count: option_count, 'option_subject[]': it_opt_subject, 'option_item[]': it_opt },
        function(data) {
            $('#OptTable form').empty().html(data);
        }
    );
}
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>