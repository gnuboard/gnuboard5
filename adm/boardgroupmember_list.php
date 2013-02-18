<?
$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$gr = get_group($gr_id);
if (!$gr['gr_id']) {
    alert('존재하지 않는 그룹입니다.');
}

$sql_common = " from {$g4['group_member_table']} a
                         left outer join {$g4['member_table']} b on (a.mb_id = b.mb_id) ";

$sql_search = " where gr_id = '{$gr_id}' ";
// 회원아이디로 검색되지 않던 오류를 수정
if (isset($stx) && $stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "gm_datetime";
    $sod = "desc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$g4['title'] = $gr['gr_subject'].' 그룹 접근가능회원';
include_once('./admin.head.php');

$colspan = 7;
?>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" name="gr_id" value="<?=$gr_id?>">
<fieldset>
    <legend><?=$gr['gr_subject']?>(아이디 <?=$gr['gr_id']?>)에서 검색</legend>
    <label for="sfl">검색대상</label>
    <select id="sfl" name="sfl">
        <option value="a.mb_id"<?=get_selected($_GET['sfl'], "a.mb_id")?>>회원아이디</option>
    </select>
    <input type="text" id="stx" name="stx" class="required frm_input" required value="<? echo $stx ?>" title="검색어(필수)">
    <input type="submit" class="btn_submit" value="검색">
</fieldset>
</form>

<section class="cbox">
    <h2><?=$gr['gr_subject']?> 그룹에 접근가능한 회원 목록 (그룹아이디:<?=$gr['gr_id']?>)</h2>
    <form id="fboardgroupmember" name="fboardgroupmember" method="post" action="./boardgroupmember_update.php" onsubmit="return fboardgroupmember_submit(this);">
    <input type="hidden" name="sst" value="<?=$sst?>">
    <input type="hidden" name="sod" value="<?=$sod?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <input type="hidden" name="token" value="<?=$token?>">
    <input type="hidden" name="gr_id" value="<?=$gr_id?>">
    <input type="hidden" name="w" value="ld">
    <table>
    <thead>
    <tr>
        <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 접근가능회원 전체선택" onclick="check_all(this.form)"></th>
        <th scope="col">그룹</th>
        <th scope="col"><?=subject_sort_link('b.mb_id', 'gr_id='.$gr_id)?>회원아이디</a></th>
        <th scope="col"><?=subject_sort_link('b.mb_name', 'gr_id='.$gr_id)?>이름</a></th>
        <th scope="col"><?=subject_sort_link('b.mb_nick', 'gr_id='.$gr_id)?>별명</a></th>
        <th scope="col"><?=subject_sort_link('b.mb_today_login', 'gr_id='.$gr_id)?>최종접속</a></th>
        <th scope="col"><?=subject_sort_link('a.gm_datetime', 'gr_id='.$gr_id)?>처리일시</a></th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 접근가능한 그룹수
        $sql2 = " select count(*) as cnt from {$g4['group_member_table']} where mb_id = '{$row['mb_id']}' ";
        $row2 = sql_fetch($sql2);
        $group = "";
        if ($row2['cnt'])
            $group = '<a href="./boardgroupmember_form.php?mb_id='.$row['mb_id'].'">'.$row2['cnt'].'</a>';

        //$s_del = '<a href="javascript:post_delete(\'boardgroupmember_update.php\', \''.$row['gm_id'].'\');">삭제</a>';

        $mb_nick = get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);
    ?>
    <tr>
        <td class="td_chk"><input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$row['gm_id']?>" title="<?=$row['mb_nick']?> 회원 선택"></td>
        <td class="td_grid"><?=$group?></td>
        <td class="td_mbid"><?=$row['mb_id']?></td>
        <td class="td_mbname"><?=$row['mb_name']?></td>
        <td class="td_name"><?=$mb_nick?></td>
        <td class="td_time"><?=substr($row['mb_today_login'],2,8)?></td>
        <td class="td_time"><?=$row['gm_datetime']?></td>
    </tr>
    <?
    }

    if ($i == 0)
    {
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <input type="submit" name="" value="선택삭제">
    </div>
    </form>
</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;gr_id=$gr_id&page=");?>

<script>
function fboardgroupmember_submit(f)
{
    if (!is_checked("chk[]")) {
        alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    return true;
}
</script>

<?
include_once('./admin.tail.php');
?>
