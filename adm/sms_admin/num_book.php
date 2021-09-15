<?php
$sub_menu = "900800";
include_once("./_common.php");

$page_size = 20;
$colspan = 9;

auth_check_menu($auth, $sub_menu, "r");

$token = get_token();

$g5['title'] = "휴대폰번호 관리";

if ($page < 1) $page = 1;

$bg_no = isset($_REQUEST['bg_no']) ? preg_replace('/[^0-9]/i', '', $_REQUEST['bg_no']) : '';
$st = isset($_REQUEST['st']) ? preg_replace('/[^a-z0-9]/i', '', $_REQUEST['st']) : '';

$sql_korean = $sql_group = $sql_search = $sql_no_hp = '';

if (is_numeric($bg_no) && $bg_no)
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

$ap = isset($_GET['ap']) ? (int) $_GET['ap'] : 0;
$no_hp = isset($_GET['no_hp']) ? preg_replace('/[^0-9a-z_]/i', '', $_GET['no_hp']) : 0;

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

if ($no_hp_checked == 'checked')
    $sql_no_hp = "and bk_hp <> ''";

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

$no_group = sql_fetch("select * from {$g5['sms5_book_group_table']} where bg_no = 1");

$group = array();
$qry = sql_query("select * from {$g5['sms5_book_group_table']} where bg_no>1 order by bg_name");
while ($res = sql_fetch_array($qry)) array_push($group, $res);

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<script>

function book_all_checked(chk)
{
    if (chk) {
        jQuery('[name="bk_no[]"]').attr('checked', true);
    } else {
        jQuery('[name="bk_no[]"]').attr('checked', false);
    }
}

function no_hp_click(val)
{
    var url = './num_book.php?bg_no=<?php echo $bg_no?>&st=<?php echo $st?>&sv=<?php echo $sv?>';

    if (val == true)
        location.href = url + '&no_hp=yes';
    else
        location.href = url + '&no_hp=no';
}
</script>

<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">업데이트 </span><span class="ov_num"><?php echo isset($sms5['cf_datetime']) ? $sms5['cf_datetime'] : ''; ?></span></span>
    <span class="btn_ov01"><span class="ov_txt"> 건수  </span><span class="ov_num"><?php echo number_format($total_count)?>명</span></span>
    <span class="btn_ov01"><span class="ov_txt"> 회원  </span><span class="ov_num"> <?php echo number_format($member_count)?>명</span></span>
    <span class="btn_ov01"><span class="ov_txt"> 비회원  </span><span class="ov_num"> <?php echo number_format($no_member_count)?>명</span></span>
    <span class="btn_ov01"><span class="ov_txt"> 수신  </span><span class="ov_num"> <?php echo number_format($receipt_count)?>명</span></span>
    <span class="btn_ov01"><span class="ov_txt"> 거부  </span><span class="ov_num"> <?php echo number_format($reject_count)?>명</span></span>
</div>

<form name="search_form" method="get" action="<?php echo $_SERVER['SCRIPT_NAME']?>" class="local_sch01 local_sch">
<input type="hidden" name="bg_no" value="<?php echo $bg_no?>" >
<label for="st" class="sound_only">검색대상</label>
<select name="st" id="st">
    <option value="all"<?php echo get_selected('all', $st); ?>>이름 + 휴대폰번호</option>
    <option value="name"<?php echo get_selected('name', $st); ?>>이름</option>
    <option value="hp" <?php echo get_selected('hp', $st); ?>>휴대폰번호</option>
</select>
<label for="sv" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="sv" value="<?php echo $sv?>" id="sv" required class="frm_input required">
<input type="submit" value="검색" class="btn_submit">
</form>

<form name="search_form" class="local_sch01 local_sch">
<label for="bg_no" class="sound_only">그룹명</label>
<select name="bg_no" id="bg_no" onchange="location.href='<?php echo $_SERVER['SCRIPT_NAME']?>?bg_no='+this.value;">
    <option value=""<?php echo get_selected('', $bg_no); ?>> 전체 </option>
    <option value="<?php echo $no_group['bg_no']?>"<?php echo get_selected($no_group['bg_no'], $bg_no); ?>> <?php echo $no_group['bg_name']?> (<?php echo number_format($no_group['bg_count'])?> 명) </option>
    <?php for($i=0; $i<count($group); $i++) {?>
    <option value="<?php echo $group[$i]['bg_no']?>"<?php echo get_selected($group[$i]['bg_no'], $bg_no);?>> <?php echo $group[$i]['bg_name']?> (<?php echo number_format($group[$i]['bg_count'])?> 명) </option>
    <?php } ?>
</select>
<input type="checkbox" name="no_hp" id="no_hp" <?php echo $no_hp_checked?> onclick="no_hp_click(this.checked)">
<label for="no_hp">휴대폰 소유자만 보기</label>
</form>



<form name="hp_manage_list" id="hp_manage_list" method="post" action="./num_book_multi_update.php" onsubmit="return hplist_submit(this);" >
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="token" value="<?php echo $token; ?>">
<input type="hidden" name="sw" value="">
<input type="hidden" name="atype" value="del">
<input type="hidden" name="str_query" value="<?php echo clean_query_string($_SERVER['QUERY_STRING']); ?>" >

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chk_all" class="sound_only">현재 페이지 전체</label>
            <input type="checkbox" id="chk_all" onclick="book_all_checked(this.checked)">
        </th>
        <th scope="col">번호</th>
        <th scope="col">그룹</th>
        <th scope="col">이름</th>
        <th scope="col">휴대폰</th>
        <th scope="col">수신</th>
        <th scope="col">아이디</th>
        <th scope="col">업데이트</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!$total_count) { ?>
    <tr>
        <td colspan="<?php echo $colspan?>" class="empty_table">데이터가 없습니다.</td>
    </tr>
    <?php
    }
    $line = 0;
    $qry = sql_query("select * from {$g5['sms5_book_table']} where 1 $sql_group $sql_search $sql_korean $sql_no_hp order by bk_no desc limit $page_start, $page_size");
    // while($res = sql_fetch_array($qry))
    for ($i=0; $res = sql_fetch_array($qry); $i++) 
    {
        $bg = 'bg'.($line++%2);

        $tmp = sql_fetch("select bg_name from {$g5['sms5_book_group_table']} where bg_no='{$res['bg_no']}'");
        $group_name = $tmp['bg_name'];
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <label for="bk_no_<?php echo $i; ?>" class="sound_only"><?php echo $group_name?>의 <?php echo get_text($res['bk_name']) ?></label>
            <input type="checkbox" name="bk_no[]" value="<?php echo $res['bk_no']?>" id="bk_no_<?php echo $i; ?>">
        </td>
        <td class="td_num"><?php echo number_format($vnum--)?></td>
        <td><?php echo $group_name?></td>
        <td class="td_mbname"><?php echo get_text($res['bk_name']) ?></td>
        <td class="td_numbig"><?php echo $res['bk_hp']?></td>
        <td class="td_boolean"><?php echo $res['bk_receipt'] ? '<font color=blue>수신</font>' : '<font color=red>거부</font>'?></td>
        <td class="td_mbid"><?php echo $res['mb_id'] ? $res['mb_id'] : '비회원'?></td>
        <td class="td_datetime"><?php echo $res['bk_datetime']?></td>
        <td class="td_mng td_mng_l">
            <a href="./num_book_write.php?w=u&amp;bk_no=<?php echo $res['bk_no']?>&amp;page=<?php echo $page?>&amp;bg_no=<?php echo $bg_no?>&amp;st=<?php echo $st?>&amp;sv=<?php echo $sv?>&amp;ap=<?php echo $ap?>" class="btn btn_03">수정</a>
            <a href="./sms_write.php?bk_no=<?php echo $res['bk_no']?>" class="btn btn_02">보내기</a>
            <a href="./history_num.php?st=hs_hp&amp;sv=<?php echo $res['bk_hp']?>" class="btn btn_02">내역</a>
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>
<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="수신허용" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="수신거부" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택이동" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택복사" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./num_book_write.php?page=<?php echo $page?>&amp;bg_no=<?php echo $bg_no?>" class="btn btn_01">번호추가</a>
</div>
</form>
<script>
function hplist_submit(f){
    if (!is_checked("bk_no[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }
    if(document.pressed == "선택이동") {
        select_copy("move", f);
        return;
    }
    if(document.pressed == "선택복사") {
        select_copy("copy", f);
        return;
    }
    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }
    if(document.pressed == "수신허용") {
        f.atype.value="receipt";
    }
    if(document.pressed == "수신거부") {
        f.atype.value="reject";
    }
    return true;
}
// 선택한 이모티콘 그룹 이동
function select_copy(sw, f) {
    if( !f ){
        var f = document.emoticonlist;
    }
    if (sw == "copy")
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./num_book_move.php";
    f.submit();
}
</script>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME']."?bg_no=$bg_no&amp;st=$st&amp;sv=$sv&amp;ap=$ap&amp;page="); ?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');