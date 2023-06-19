<?php
$sub_menu = '400800';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$mb_name = isset($_REQUEST['mb_name']) ? clean_xss_tags($_REQUEST['mb_name'], 1, 1) : '';

$html_title = '회원검색';

$g5['title'] = $html_title;
include_once(G5_PATH.'/head.sub.php');

$sql_common = " from {$g5['member_table']} ";
$sql_where = " where mb_id <> '{$config['cf_admin']}' and mb_leave_date = '' and mb_intercept_date ='' ";

if($mb_name){
    $mb_name = preg_replace('/\!\?\*$#<>()\[\]\{\}/i', '', strip_tags($mb_name));
    $sql_where .= " and mb_name like '%".sql_real_escape_string($mb_name)."%' ";
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common . $sql_where;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select mb_id, mb_name
            $sql_common
            $sql_where
            order by mb_id
            limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = 'mb_name='.urlencode($mb_name);
?>

<div id="sch_member_frm" class="new_win scp_new_win">
    <h1>쿠폰 적용 회원선택</h1>

    <form name="fmember" method="get">
    <div id="scp_list_find">
        <label for="mb_name">회원이름</label>
        <input type="text" name="mb_name" id="mb_name" value="<?php echo get_text($mb_name); ?>" class="frm_input" size="20">
        <input type="submit" value="검색" class="btn_frmline">
    </div>
    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th>회원이름</th>
            <th>회원아이디</th>
            <th>선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
        ?>
        <tr>
            <td class="td_mbname"><?php echo get_text($row['mb_name']); ?></td>
            <td class="td_left"><?php echo $row['mb_id']; ?></td>
            <td class="scp_find_select td_mng td_mng_s"><button type="button" class="btn btn_03" onclick="sel_member_id('<?php echo $row['mb_id']; ?>');">선택</button></td>
        </tr>
        <?php
        }

        if($i ==0)
            echo '<tr><td colspan="3" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    </form>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr1.'&amp;page='); ?>

    <div class="btn_confirm01 btn_confirm win_btn">
        <button type="button" onclick="window.close();" class="btn_close btn">닫기</button>
    </div>
</div>

<script>
function sel_member_id(id)
{
    var f = window.opener.document.fcouponform;
    f.mb_id.value = id;

    window.close();
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');