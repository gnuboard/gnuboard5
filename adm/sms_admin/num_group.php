<?php
$sub_menu = "900700";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g5['title'] = "휴대폰번호 그룹";

$res = sql_fetch("select count(*) as cnt from {$g5['sms5_book_group_table']}");
$total_count = $res['cnt'];

$no_group = sql_fetch("select * from {$g5['sms5_book_group_table']} where bg_no = 1");

$group = array();
$qry = sql_query("select * from {$g5['sms5_book_group_table']} where bg_no > 1 order by bg_name");
while ($res = sql_fetch_array($qry)) array_push($group, $res);

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>


<script>

function del(bg_no) {
    if (confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n삭제되는 그룹에 속한 자료는 '<?php echo $no_group['bg_name']?>'로 이동됩니다.\n\n그래도 삭제하시겠습니까?"))
        location.href = 'num_group_update.php?mw=d&bg_no=' + bg_no;
}

function move(bg_no, bg_name, sel) {
    var msg = '';
    if (sel.value)
    {
        msg  = "'" + bg_name + "' 그룹에 속한 모든 데이터를\n\n'";
        msg += sel.options[sel.selectedIndex].text + "' 그룹으로 이동하시겠습니까?";

        if (confirm(msg))
            location.href = 'num_group_move.php?bg_no=' + bg_no + '&move_no=' + sel.value;
        else
            sel.selectedIndex = 0;
    }
}

function empty(bg_no) {
    if (confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n그룹에 속한 데이터를 정말로 비우시겠습니까?"))
        location.href = 'num_group_update.php?mw=empty&bg_no=' + bg_no;
}

function num_group_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n삭제되는 그룹에 속한 자료는 '<?php echo $no_group['bg_name']?>'로 이동됩니다.\n\n그래도 삭제하시겠습니까?")) {
            f.w.value = "de";
        } else {
            return false;
        }
    }

    if(document.pressed == "선택비우기") {
        if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n그룹에 속한 데이터를 정말로 비우시겠습니까?")) {
            f.w.value = "em";
        } else {
            return false;
        }
    }

    return true;
}

</script>

<form name="group<?php echo $res['bg_no']?>" method="get" action="./num_group_update.php" class="local_sch02 local_sch">
<input type="hidden" name="bg_no" value="<?php echo $res['bg_no']?>">
<div>
    <label for="bg_name" class="sound_only">그룹추가<strong class="sound_only"> 필수</strong></label>
    <input type="text" id="bg_name" name="bg_name" required class="required frm_input">
    <input type="submit" value="그룹추가" class="btn_submit">
</div>
<div class="sch_last">
    <span>건수 : <?php echo $total_count; ?></span>
</div>
</form>

<div class="local_desc01 local_desc">
    <p>그룹명순으로 정렬됩니다.</p>
</div>

<form name="group_hp_form" id="group_hp_form" method="post" action="./num_group_update.php" onsubmit="return num_group_submit(this);">
<input type="hidden" name="w" value="u">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">그룹 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">그룹명</th>
        <th scope="col">총</th>
        <th scope="col">회원</th>
        <th scope="col">비회원</th>
        <th scope="col">수신</th>
        <th scope="col">거부</th>
        <th scope="col">이동</th>
        <th scope="col">보기</th>
     </tr>
     </thead>
     <tbody>
    <!-- 미분류 시작 -->
    <tr>
        <td></td>
        <td><?php echo $no_group['bg_name']?></td>
        <td class="td_num"><?php echo number_format($no_group['bg_count'])?></td>
        <td class="td_num"><?php echo number_format($no_group['bg_member'])?></td>
        <td class="td_num"><?php echo number_format($no_group['bg_nomember'])?></td>
        <td class="td_num"><?php echo number_format($no_group['bg_receipt'])?></td>
        <td class="td_num"><?php echo number_format($no_group['bg_reject'])?></td>
        <td class="td_mng">
            <label for="select_bg_no_999" class="sound_only">이동할 그룹</label>
            <select name="select_bg_no_999" id="select_bg_no_999" onchange="move(<?php echo $no_group['bg_no']?>, '<?php echo $no_group['bg_name']?>', this);" >
                <option value=""></option>
                <?php for ($i=0; $i<count($group); $i++) { ?>
                <option value="<?php echo $group[$i]['bg_no']?>"> <?php echo $group[$i]['bg_name']?> </option>
                <?php } ?>
            </select>
        </td>
        <td class="td_mng">
            <a href="./num_book.php?bg_no=1">보기</a>
        </td>
    </tr>
    <!-- 미분류 끝 -->
    <?php
    for ($i=0; $i<count($group); $i++) {
    $bg = 'bg'.(($i + 1)%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_mng">
            <input type="hidden" name="bg_no[<?php echo $i ?>]" value="<?php echo $group[$i]['bg_no']?>" id="bg_no_<?php echo $i ?>">
            <label for="chk_<?php echo $i ?>" class="sound_only">그룹명</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td>
            <label for="bg_name_<?php echo $i; ?>" class="sound_only">그룹명</label>
            <input type="text" name="bg_name[<?php echo $i; ?>]" value="<?php echo $group[$i]['bg_name']?>" id="bg_name_<?php echo $i; ?>" class="frm_input">
        </td>
        <td class="td_num"><?php echo number_format($group[$i]['bg_count'])?></td>
        <td class="td_num"><?php echo number_format($group[$i]['bg_member'])?></td>
        <td class="td_num"><?php echo number_format($group[$i]['bg_nomember'])?></td>
        <td class="td_num"><?php echo number_format($group[$i]['bg_receipt'])?></td>
        <td class="td_num"><?php echo number_format($group[$i]['bg_reject'])?></td>
        <td class="td_mbstat">
            <label for="select_bg_no_<?php echo $i; ?>" class="sound_only">이동할 그룹</label>
            <select name="select_bg_no[<?php echo $i ?>]" id="select_bg_no_<?php echo $i; ?>" onchange="move(<?php echo $group[$i]['bg_no']?>, '<?php echo $group[$i]['bg_name']?>', this);" >
                <option value=""></option>
                <option value="<?php echo $no_group['bg_no']?>"><?php echo $no_group['bg_name']?></option>
                <?php for ($j=0; $j<count($group); $j++) { ?>
                <?php if ($group[$i]['bg_no']==$group[$j]['bg_no']) continue; ?>
                <option value="<?php echo $group[$j]['bg_no']?>"> <?php echo $group[$j]['bg_name']?> </option>
                <?php } ?>
            </select>
        </td>
        <td class="td_mng">
            <a href="./num_book.php?bg_no=<?php echo $group[$i]['bg_no']?>">보기</a>
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<div class="btn_list01 btn_list">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
    <input type="submit" name="act_button" value="선택비우기" onclick="document.pressed=this.value">
</div>

</form>
<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>