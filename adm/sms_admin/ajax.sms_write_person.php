<?php
include_once("./_common.php");

$page_size = 10;
$colspan = 5;

auth_check($auth[$sub_menu], "r");

$g5['title'] = "휴대폰번호 관리";

if ($page < 1) $page = 1;

if (is_numeric($bg_no))
    $sql_group = " and bg_no='$bg_no' ";
else
    $sql_group = "";

if ($st == 'all') {
    $sql_search = "and (bk_name like '%{$sv}%' or bk_hp like '%{$sv}%')";
} else if ($st == 'name') {
    $sql_search = "and bk_name like '%{$sv}%'";
} else if ($st == 'hp') {
    $sql_search = "and bk_hp like '%{$sv}%'";
} else {
    $sql_search = '';
}

if ($ap > 0)
    $sql_korean = korean_index('bk_name', $ap-1);
else {
    $sql_korean = '';
    $ap = 0;
}

if ($no_hp == 'yes') {
    set_cookie('cookie_no_hp', 'yes', 60*60*24*365);
    $no_hp_checked = 'checked';
} else if ($no_hp == 'no') {
    set_cookie('cookie_no_hp', '', 0);
    $no_hp_checked = '';
} else {
    if (get_cookie('cookie_no_hp') == 'yes')
        $no_hp_checked = 'checked';
    else
        $no_hp_checked = '';
}

//if ($no_hp_checked == 'checked')
    $sql_no_hp = "and bk_hp <> '' and bk_receipt=1";

$total_res = sql_fetch("select count(*) as cnt from {$g5['sms5_book_table']} where 1 $sql_group $sql_search $sql_korean $sql_no_hp");
$total_count = $total_res['cnt'];

$total_page = (int)($total_count/$page_size) + ($total_count%$page_size==0 ? 0 : 1);
$page_start = $page_size * ( $page - 1 );

$vnum = $total_count - (($page-1) * $page_size);

$res = sql_fetch("select count(*) as cnt from {$g5['sms5_book_table']} where bk_receipt=1 $sql_group $sql_search $sql_korean $sql_no_hp");
$receipt_count = $res['cnt'];
$reject_count = $total_count - $receipt_count;

$res = sql_fetch("select count(*) as cnt from {$g5['sms5_book_table']} where mb_id='' $sql_group $sql_search $sql_korean $sql_no_hp");
$no_member_count = $res['cnt'];
$member_count = $total_count - $no_member_count;

$no_group = sql_fetch("select * from {$g5['sms5_book_group_table']} where bg_no=1");

$group = array();
$qry = sql_query("select * from {$g5['sms5_book_group_table']} where bg_no>1 order by bg_name");
while ($res = sql_fetch_array($qry)) array_push($group, $res);
?>
<div class="tbl_head01 tbl_wrap">
    <table>
    <thead>
    <tr>
        <th scope="col">
            <label for="all_checked" class="sound_only">회원 전체</label>
            <input type="checkbox" id="all_checked" onclick="sms_obj.book_all_checked(this.checked)">
        </th>
        <th scope="col">이름</th>
        <th scope="col">휴대폰번호</th>
        <th scope="col">등급</th>
        <th scope="col">추가</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!$total_count) { ?>
    <tr>
        <td colspan="<?php echo $colspan?>" class="td_mbstat">데이터가 없습니다.</td>
    </tr>
    <?php
    }
    $line = 0;
    $qry = sql_query("select * from {$g5['sms5_book_table']} where 1 $sql_group $sql_search $sql_korean $sql_no_hp order by bk_no desc limit $page_start, $page_size");
    while($res = sql_fetch_array($qry))
    {
        $bg = 'bg'.($line++%2);

        $tmp = sql_fetch("select bg_name from {$g5['sms5_book_group_table']} where bg_no='{$res['bg_no']}'");
        if (!$tmp)
            $group_name = '미분류';
        else
            $group_name = $tmp['bg_name'];
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <label for="bk_no_<?php echo $res['bk_no']; ?>" class="sound_only"><?php echo $res['bk_name']?></label>
            <input type="checkbox" name="bk_no" value="<?php echo $res['bk_no']?>" id="bk_no_<?php echo $res['bk_no']; ?>">
        </td>
        <!-- <td class="td_name"><?php echo $group_name?></td> -->
        <td class="td_mbname"><?php echo $res['bk_name']?></td>
        <td><?php echo $res['bk_hp']?></td>
        <!-- <td class="td_boolean"><?php echo $res['bk_receipt'] ? '수신' : '거부'?></td> -->
        <!-- <td class="td_boolean"><?php echo $res['bk_receipt'] ? '예' : ''?></td> -->
        <td class="td_boolean"><?php echo $res['mb_id'] ? '회원' : '비회원'?></td>
        <td class="td_mngsmall"><button type="button" class="btn_frmline" onclick="sms_obj.person_add(<?php echo $res['bk_no']?>, '<?php echo $res['bk_name']?>', '<?php echo $res['bk_hp']?>')">추가</button></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<div class="btn_list01 btn_list">
    <button type="button" onclick="sms_obj.person_multi_add()">선택추가</button>
    <button type="button" onclick="sms_obj.triggerclick('#book_group')" >그룹목록</button>
</div>

<nav class="pg_wrap">
    <span class="pg" id="person_pg"></span>
</nav>

<form name="search_form" id="sms_person_form" method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
<input type="hidden" name="total_pg" value="<?php echo $total_page?>">
<input type="hidden" name="page" value="<?php echo $page?>">
<input type="hidden" name="bg_no" value="<?php echo $bg_no?>">

<label for="bg_no" class="sound_only">그룹</label>
<select name="bg_no" id="bg_no">
    <option value="" <?php echo $bg_no?'':'selected'?>> 전체 </option>
    <option value="1" <?php echo $bg_no=='1'?'selected':''?>> <?php echo $no_group['bg_name']?> (<?php echo number_format($no_group['bg_receipt'])?> 명) </option>
    <?php for($i=0; $i<count($group); $i++) {?>
    <option value="<?php echo $group[$i]['bg_no']?>" <?php echo ($bg_no==$group[$i]['bg_no'])?'selected':''?>> <?php echo $group[$i]['bg_name']?> (<?php echo number_format($group[$i]['bg_receipt'])?> 명) </option>
    <?php } ?>
</select>

<label for="stt" class="sound_only">검색대상</label>
<select name="st" id="stt">
    <option value="all" <?php echo $st=='all'?'selected':''?>>이름 + 번호</option>
    <option value="name" <?php echo $st=='name'?'selected':''?>>이름</option>
    <option value="hp" <?php echo $st=='hp'?'selected':''?>>번호</option>
</select>

<label for="svv" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" size="15" name="sv" value="<?php echo $sv?>" id="svv" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">
</form>

<!--
총 건수 : <?php echo number_format($total_count)?> /
회원 : <?php echo number_format($member_count)?> /
비회원 : <?php echo number_format($no_member_count)?> /
수신 : <?php echo number_format($receipt_count)?> /
거부 : <?php echo number_format($reject_count)?>
-->