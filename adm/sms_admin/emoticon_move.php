<?php
$sub_menu = "900600";
include_once('./_common.php');

if ($sw != 'move'){
    alert('sw 값이 제대로 넘어오지 않았습니다.');
}

auth_check($auth[$sub_menu], "r");

$g5['title'] = '이모티콘그룹 이동';
include_once(G5_PATH.'/head.sub.php');

$fo_no_list = implode(',', $_POST['fo_no']);

$sql = " select * from {$g5['sms5_form_group_table']} order by fg_no ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $list[$i] = $row;
}
?>

<div id="copymove" class="new_win">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>

    <form name="fboardmoveall" method="post" action="./emoticon_move_update.php" onsubmit="return fboardmoveall_submit(this);">
    <input type="hidden" name="sw" value="<?php echo $sw ?>">
    <input type="hidden" name="fo_no_list" value="<?php echo $fo_no_list ?>">
    <input type="hidden" name="url" value="<?php echo $_SERVER['HTTP_REFERER'] ?>">

    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption>이동할 그룹을 한개 이상 선택하여 주십시오.</caption>
        <thead>
        <tr>
            <th scope="col">선택</th>
            <th scope="col">그룹</th>
        </tr>
        </thead>
        <tbody>
        <?php for ($i=0; $i<count($list); $i++) { ?>
        <tr>
            <td class="td_chk">
                <input type="radio" value="<?php echo $list[$i]['fg_no'] ?>" id="chk<?php echo $i ?>" name="chk_fg_no[]">
            </td>
            <td>
                <label for="chk<?php echo $i ?>"><?php echo $list[$i]['fg_name'] ?></label>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>

    <div class="win_btn">
        <input type="submit" value="이동" id="btn_submit" class="btn_submit">
        <button type="button" class="btn_cancel">창닫기</button>
    </div>
    </form>

</div>

<script>
(function($) {
    $(".win_btn button").click(function(e) {
        window.close();
        return false;
    });
})(jQuery);

function all_checked(sw) {
    var f = document.fboardmoveall;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_fg_no[]")
            f.elements[i].checked = sw;
    }
}

function fboardmoveall_submit(f)
{
    var check = false;

    if (typeof(f.elements['chk_fg_no[]']) == 'undefined')
        ;
    else {
        if (typeof(f.elements['chk_fg_no[]'].length) == 'undefined') {
            if (f.elements['chk_fg_no[]'].checked)
                check = true;
        } else {
            for (i=0; i<f.elements['chk_fg_no[]'].length; i++) {
                if (f.elements['chk_fg_no[]'][i].checked) {
                    check = true;
                    break;
                }
            }
        }
    }

    if (!check) {
        alert('이모티콘을 '+f.act.value+'할 그룹을 한개 이상 선택해 주십시오.');
        return false;
    }

    document.getElementById('btn_submit').disabled = true;

    return true;
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
