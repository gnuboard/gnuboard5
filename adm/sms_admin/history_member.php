<?php
$sub_menu = "900410";
include_once("./_common.php");

$page_size = 20;
$colspan = 7;

auth_check($auth[$sub_menu], "r");

$g5['title'] = "문자전송 내역 (회원)";

if ($page < 1) $page = 1;

if ($st && trim($sv))
    $sql_search = " and $st like '%$sv%' ";
else
    $sql_search = "";

$total_res = sql_fetch("select count(*) as cnt from {$g5['sms5_member_history_table']} where 1 $sql_search");
$total_count = $total_res['cnt'];

$total_page = (int)($total_count/$page_size) + ($total_count%$page_size==0 ? 0 : 1);
$page_start = $page_size * ( $page - 1 );

$vnum = $total_count - (($page-1) * $page_size);

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<form name="search_form" method="get" action="<?php echo $_SEVER['PHP_SELF']?>" class="local_sch01 local_sch">
<label for="st" class="sound_only">검색대상</label>
<select name="st" id="st">
    <option value="mb_id" <?php echo $st=='mh_name'?'selected':''?>>아이디</option>
    <option value="mh_hp" <?php echo $st=='mh_hp'?'selected':''?>>받는번호</option>
    <option value="mh_reply" <?php echo $st=='mh_reply'?'selected':''?>>보내는번호</option>
</select>
<label for="sv" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="sv" value="<?php echo $sv ?>" id="sv" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">
</form>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">회원</th>
        <th scope="col">보내는번호</th>
        <th scope="col">받는번호</th>
        <th scope="col">전송일시</th>
        <th scope="col">예약</th>
        <th scope="col">Log</th>
     </tr>
     </thead>
     <tbody>
     <?php if (!$total_count) { ?>
    <tr>
        <td colspan="<?php echo $colspan?>" class="empty_table" >
            데이터가 없습니다.
        </td>
    </tr>
    <?php
    }
    $qry = sql_query("select * from {$g5['sms5_member_history_table']} where 1 $sql_search order by mh_no desc limit $page_start, $page_size");
    while($row = sql_fetch_array($qry)) {
        $bg = 'bg'.($line++%2);

        $mb = get_member($row['mb_id']);
        $mb_id = get_sideview($row['mb_id'], $mb['mb_nick']);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_num"><?php echo $vnum--?></td>
        <td class="td_mbid"><?php echo $mb_id?></td>
        <td class="td_numbig"><?php echo $row['mh_reply']?></td>
        <td class="td_numbig"><?php echo $row['mh_hp']?></td>
        <td class="td_datetime"><?php echo $row['mh_datetime']?></td>
        <td class="td_boolean"><?php echo $row['mh_booking']!='0000-00-00 00:00:00'?"<span title='{$row['mh_booking']}'>예약</span>":'';?></td>
        <td><?php echo $row['mh_log']?></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF']."?st=$st&amp;sv=$sv&amp;page="); ?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>