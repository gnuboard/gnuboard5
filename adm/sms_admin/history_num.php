<?php
$sub_menu = '900400';
include_once('./_common.php');

$page_size = 20;
$colspan = 10;

auth_check($auth[$sub_menu], "r");

$g5['title'] = "문자전송 내역 (번호별)";

if ($page < 1) $page = 1;

if ($st && trim($sv))
    $sql_search = " and $st like '%$sv%' ";
else
    $sql_search = "";

$total_res = sql_fetch("select count(*) as cnt from {$g5['sms5_history_table']} where 1 $sql_search");
$total_count = $total_res['cnt'];

$total_page = (int)($total_count/$page_size) + ($total_count%$page_size==0 ? 0 : 1);
$page_start = $page_size * ( $page - 1 );

$vnum = $total_count - (($page-1) * $page_size);

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<form name="search_form" method="get" action="<?echo $_SERVER['SCRIPT_NAME']?>" class="local_sch01 local_sch" >
<label for="st" class="sound_only">검색대상</label>
<select name="st" id="st">
    <option value="hs_name"<?php echo get_selected('hs_name', $st); ?>>이름</option>
    <option value="hs_hp"<?php echo get_selected('hs_hp', $st); ?>>휴대폰번호</option>
    <option value="bk_no"<?php echo get_selected('bk_no', $st); ?>>고유번호</option>
</select>
<label for="sv" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="sv" value="<?php echo $sv; ?>" id="sv" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">
</form>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">그룹</th>
        <th scope="col">이름</th>
        <th scope="col">회원ID</th>
        <th scope="col">전화번호</th>
        <th scope="col">전송일시</th>
        <th scope="col">예약</th>
        <th scope="col">전송</th>
        <th scope="col">메세지</th>
        <th scope="col">관리</th>
     </tr>
     </thead>
     <tbody>
        <?php if (!$total_count) { ?>
        <tr>
            <td colspan="<?php echo $colspan; ?>" class="empty_table" >
                데이터가 없습니다.
            </td>
        </tr>
    <?php
    }
    $qry = sql_query("select * from {$g5['sms5_history_table']} where 1 $sql_search order by hs_no desc limit $page_start, $page_size");
    while($res = sql_fetch_array($qry)) {
        $bg = 'bg'.($line++%2);

        $write = sql_fetch("select * from {$g5['sms5_write_table']} where wr_no='{$res['wr_no']}' and wr_renum=0");
        $group = sql_fetch("select * from {$g5['sms5_book_group_table']} where bg_no='{$res['bg_no']}'");
        if ($group)
            $bg_name = $group['bg_name'];
        else
            $bg_name = '없음';

        if ($res['mb_id'])
            $mb_id = '<a href="'.G5_ADMIN_URL.'/member_form.php?w=u&amp;mb_id='.$res['mb_id'].'">'.$res['mb_id'].'</a>';
        else
            $mb_id = '비회원';
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_num"><?php echo $vnum--; ?></td>
        <td class="td_mbname"><?php echo $bg_name; ?></td>
        <td class="td_mbname"><a href="./num_book_write.php?w=u&amp;bk_no=<?php echo $res['bk_no']; ?>"><?php echo $res['hs_name']; ?></a></td>
        <td class="td_mbid"><?php echo $mb_id; ?></td>
        <td class="td_numbig"><?php echo $res['hs_hp']; ?></td>
        <td class="td_datetime"><?php echo date('Y-m-d H:i', strtotime($write['wr_datetime']))?></td>
        <td class="td_boolean"><?php echo $write['wr_booking']!='0000-00-00 00:00:00'?"<span title='{$write['wr_booking']}'>예약</span>":'';?></td>
        <td class="td_boolean"><?php echo $res['hs_flag']?'성공':'실패'?></td>
        <td><span title="<?php echo $write['wr_message']?>"><?php echo $write['wr_message']?></span></td>
        <td class="td_mngsmall">
            <a href="./history_view.php?page=<?php echo $page; ?>&amp;st=<?php echo $st; ?>&amp;sv=<?php echo $sv; ?>&amp;wr_no=<?php echo $res['wr_no']; ?>">수정</a>
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME']."?st=$st&amp;sv=$sv&amp;page="); ?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>