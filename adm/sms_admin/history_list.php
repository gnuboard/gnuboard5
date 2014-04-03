<?php
$sub_menu = "900400";
include_once("./_common.php");

$page_size = 20;
$colspan = 11;

auth_check($auth[$sub_menu], "r");

$g5['title'] = "문자전송 내역";

if ($page < 1) $page = 1;

if ($st && trim($sv))
    $sql_search = " and wr_message like '%$sv%' ";
else
    $sql_search = "";

$total_res = sql_fetch("select count(*) as cnt from {$g5['sms5_write_table']} where wr_renum=0 $sql_search");
$total_count = $total_res[cnt];

$total_page = (int)($total_count/$page_size) + ($total_count%$page_size==0 ? 0 : 1);
$page_start = $page_size * ( $page - 1 );

$vnum = $total_count - (($page-1) * $page_size);

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<form name="search_form" id="search_form" action=<?php echo $_SERVER['PHP_SELF'];?> class="local_sch01 local_sch" method="get">

<label for="st" class="sound_only">검색대상</label>
<select name="st" id="st">
    <option value="wr_message" <?php echo $st=="wr_message"?"selected":""?>>메세지</option>
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
        <th scope="col">메세지</th>
        <th scope="col">회신번호</th>
        <th scope="col">전송일시</th>
        <th scope="col">예약</th>
        <th scope="col">총건수</th>
        <th scope="col">성공</th>
        <th scope="col">실패</th>
        <th scope="col">중복</th>
        <th scope="col">재전송</th>
        <th scope="col">관리</th>
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
    $qry = sql_query("select * from {$g5['sms5_write_table']} where wr_renum=0 $sql_search order by wr_no desc limit $page_start, $page_size");
    while($res = sql_fetch_array($qry)) {
        $bg = 'bg'.($line++%2);
        $tmp_wr_memo = @unserialize($res['wr_memo']);
        $dupli_count = $tmp_wr_memo['total'] ? $tmp_wr_memo['total'] : 0;
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_numsmall"><?php echo $vnum--?></td>
        <td><span title="<?php echo $res['wr_message']?>"><?php echo $res['wr_message']?></span></td>
        <td class="td_numbig"><?php echo $res['wr_reply']?></td>
        <td class="td_datetime"><?php echo date('Y-m-d H:i', strtotime($res['wr_datetime']))?></td>
        <td class="td_boolean"><?php echo $res['wr_booking']!='0000-00-00 00:00:00'?"<span title='{$res['wr_booking']}'>예약</span>":'';?></td>
        <td class="td_num"><?php echo number_format($res['wr_total'])?></td>
        <td class="td_num"><?php echo number_format($res['wr_success'])?></td>
        <td class="td_num"><?php echo number_format($res['wr_failure'])?></td>
        <td class="td_num"><?php echo $dupli_count;?></td>
        <td class="td_num"><?php echo number_format($res['wr_re_total'])?></td>
        <td class="td_mngsmall">
            <a href="./history_view.php?page=<?php echo $page;?>&amp;st=<?php echo $st;?>&amp;sv=<?php echo $sv;?>&amp;wr_no=<?php echo $res['wr_no'];?>">수정</a>
            <!-- <a href="./history_del.php?page=<?php echo $page;?>&amp;st=<?php echo $st;?>&amp;sv=<?php echo $sv;?>&amp;wr_no=<?php echo $res['wr_no'];?>">삭제</a> -->
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF']."?st=$st&amp;sv=$sv&amp;page="); ?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>