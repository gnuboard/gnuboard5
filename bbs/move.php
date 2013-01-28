<?
include_once('./_common.php');

if ($sw == 'move')
    $act = '이동';
else if ($sw == 'copy')
    $act = '복사';
else
    alert('sw 값이 제대로 넘어오지 않았습니다.');

// 게시판 관리자 이상 복사, 이동 가능
if ($is_admin != 'board' && $is_admin != 'group' && $is_admin != 'super')
    alert_close("게시판 관리자 이상 접근이 가능합니다.");

$g4['title'] = '게시물 ' . $act;
include_once(G4_PATH.'/head.sub.php');

$wr_id_list = '';
if ($wr_id)
    $wr_id_list = $wr_id;
else {
    $comma = '';
    for ($i=0; $i<count($_POST['chk_wr_id']); $i++) {
        $wr_id_list .= $comma . $_POST['chk_wr_id'][$i];
        $comma = ',';
    }
}

$sql = " select * from {$g4['board_table']} a,
            {$g4['group_table']} b
            where a.gr_id = b.gr_id
            and bo_table <> '$bo_table' ";
if ($is_admin == 'group')
    $sql .= " and b.gr_admin = '{$member['mb_id']}' ";
else if ($is_admin == 'board')
    $sql .= " and a.bo_admin = '{$member['mb_id']}' ";
$sql .= " order by a.gr_id, a.bo_order_search, a.bo_table ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $list[$i] = $row;
}
?>

<div id="copymove" class="new_win">
    <h1><?=$g4['title']?></h1>

    <form name="fboardmoveall" method="post" action="./move_update.php" onsubmit="return fboardmoveall_submit(this);">
    <input type="hidden" name="sw" value="<?=$sw?>">
    <input type="hidden" name="bo_table" value="<?=$bo_table?>">
    <input type="hidden" name="wr_id_list" value="<?=$wr_id_list?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="spt" value="<?=$spt?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <input type="hidden" name="act" value="<?=$act?>">
    <input type="hidden" name="url" value="<?=$_SERVER['HTTP_REFERER']?>">
    <table>
    <caption><?=$act?>할 게시판을 한개 이상 선택하여 주십시오.</caption>
    <thead>
    <tr>
        <th scope="col">선택</th>
        <th scope="col">게시판</th>
    </tr>
    </thead>
    <tbody>
    <? for ($i=0; $i<count($list); $i++) { ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" id="chk<?=$i?>" name="chk_bo_table[]" value="<?=$list[$i]['bo_table']?>">
        </td>
        <td>
            <label for="chk<?=$i?>">
            <?
            echo $list[$i]['gr_subject'] . " &gt; ";
            $save_gr_subject = $list[$i]['gr_subject'];
            ?>
            <?=$list[$i]['bo_subject']?> (<?=$list[$i]['bo_table']?>)
            </label>
        </td>
    </tr>
    <? } ?>
    </tbody>
    </table>

    <div class="btn_win btn_confirm">
        <input type="submit" id="btn_submit" class="btn_submit" value="<?=$act?>">
    </div>
    </form>

</div>

<script>
$(function() {
    $(".btn_win").append("<a class=\"btn_cancel\">창닫기</a>");

    $(".btn_win a").click(function() {
        window.close();
    });
});

function fboardmoveall_submit(f)
{
    var check = false;

    if (typeof(f.elements['chk_bo_table[]']) == 'undefined')
        ;
    else {
        if (typeof(f.elements['chk_bo_table[]'].length) == 'undefined') {
            if (f.elements['chk_bo_table[]'].checked)
                check = true;
        } else {
            for (i=0; i<f.elements['chk_bo_table[]'].length; i++) {
                if (f.elements['chk_bo_table[]'][i].checked) {
                    check = true;
                    break;
                }
            }
        }
    }

    if (!check) {
        alert('게시물을 '+f.act.value+'할 게시판을 한개 이상 선택해 주십시오.');
        return false;
    }

    document.getElementById('btn_submit').disabled = true;

    f.action = './move_update.php';
    return true;
}
</script>

</td></tr></table>

<?
include_once(G4_PATH.'/tail.sub.php');
?>
