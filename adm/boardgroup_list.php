<?
$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

$sql_common = " from {$g4['group_table']} ";

$sql_search = " where (1) ";
if ($is_admin != 'super')
    $sql_search .= " and (gr_admin = '{$member['mb_id']}') ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "gr_id" :
        case "gr_admin" :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($sst)
    $sql_order = " order by {$sst} {$sod} ";
else
    $sql_order = " order by gr_id asc ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">처음</a>';

$g4['title'] = '게시판그룹설정';
include_once('./admin.head.php');

$colspan = 8;
?>

<script>
var list_update_php = "./boardgroup_list_update.php";
</script>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>그룹 검색</legend>
    <span>
        <?=$listall?>
        생성된 그룹수 <?=number_format($total_count)?>개
    </span>
    <label for="sfl">검색대상</label>
    <select id="sfl" name="sfl">
        <option value="gr_subject">제목</option>
        <option value="gr_id">ID</option>
        <option value="gr_admin">그룹관리자</option>
    </select>
    <label for="stx">검색어</label>
    <input type="text" id="stx" name="stx" required value="<?=$stx?>">
    <input type="submit" class="fieldset_submit" value="검색">
</fieldset>
</form>

<?if ($is_admin == 'super') {?><a href="./boardgroup_form.php" id="bo_gr_add">게시판그룹 추가</a><?}?>

<form id="fboardgrouplist" name="fboardgrouplist" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<table class="tbl_gr_list">
<caption>
게시판그룹 목록
<p>
    접근사용 옵션을 설정하시면 관리자가 지정한 회원만 해당 그룹에 접근할 수 있습니다.<br>
    접근사용 옵션은 해당 그룹에 속한 모든 게시판에 적용됩니다.
</p>
</caption>
<thead>
<tr>
    <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 그룹 전체선택" onclick="check_all(this.form)"></th>
    <th scope="col"><?=subject_sort_link('gr_id')?>그룹아이디</a></th>
    <th scope="col"><?=subject_sort_link('gr_subject')?>제목</a></th>
    <th scope="col"><?=subject_sort_link('gr_admin')?>그룹관리자</a></th>
    <th scope="col">게시판</th>
    <th scope="col">접근사용</th>
    <th scope="col">접근회원수</th>
    <th scope="col">관리</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 접근회원수
    $sql1 = " select count(*) as cnt from {$g4['group_member_table']} where gr_id = '{$row['gr_id']}' ";
    $row1 = sql_fetch($sql1);

    // 게시판수
    $sql2 = " select count(*) as cnt from {$g4['board_table']} where gr_id = '{$row['gr_id']}' ";
    $row2 = sql_fetch($sql2);

    $s_upd = '<a href="./boardgroup_form.php?$qstr&amp;w=u&amp;gr_id='.$row['gr_id'].'">수정</a>';
    $s_del = '';
    if ($is_admin == 'super') {
        //$s_del = '<a href="javascript:del(\'./boardgroup_delete.php?$qstr&gr_id='.$row[gr_id].'\');">삭제</a>';
        $s_del = '<a href="javascript:post_delete(\'boardgroup_delete.php\', \''.$row['gr_id'].'\');">삭제</a>';
    }
?>

<tr>
    <td class="td_chk">
        <input type="hidden" id="gr_id" name="gr_id[<?=$i?>]" value="<?=$row['gr_id']?>">
        <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>" title="<?=$row['gr_subject']?> 그룹선택">
    </td>
    <td><a href="<?=$g4['bbs_path']?>/group.php?gr_id=<?=$row['gr_id']?>"><?=$row['gr_id']?></a></td>
    <td>
        <input type="text" id="gr_subject_<?=$i?>" name="gr_subject[<?=$i?>]" value="<?=get_text($row['gr_subject'])?>" title="그룹제목 수정">
    </td>
    <td>
    <?if ($is_admin == 'super'){?>
        <input type="text" id="gr_admin" name="gr_admin[<?=$i?>]" value="<?=$row['gr_admin']?>" title="그룹관리자 수정" maxlength="20">
    <?}else{?>
        <input type="hidden" name="gr_admin[<?=$i?>]" value="<?=$row['gr_admin']?>"><td><?=$row['gr_admin']?>
    <?}?>
    </td>
    <td><a href="./board_list.php?sfl=a.gr_id&amp;stx=<?=$row['gr_id']?>"><?=$row2[cnt]?></a></td>
    <td><input type="checkbox" id="gr_use_access" name="gr_use_access[<?=$i?>]" <?=$row[gr_use_access]?'checked':''?> value="1" title="선택 시 접근회원 사용"></td>
    <td><a href="./boardgroupmember_list.php?gr_id=<?=$row['gr_id']?>"><?=$row1[cnt]?></a></td>
    <td class="td_mng"><?=$s_upd?> <?=$s_del?></td>
</tr>

<?
    }
if ($i == 0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</table>

<div class="btn_list">
    <input type="button" value="선택수정" onclick="btn_check(this.form, 'update')">
    <!-- <input type="button" value="선택삭제" onclick="btn_check(this.form, 'delete')"> -->
    <a href="./boardgroup_form.php">게시판그룹 추가</a>
</div>

<?
$pagelist = get_paging($config[cf_write_pages], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page=');
?>
<div class="pg">
    <?=$pagelist?>
</div>

<?
if ($stx)
    echo '<script>document.fsearch.sfl.value = "'.$sfl.'";</script>';
?>
</form>

<script>
// POST 방식으로 삭제
function post_delete(action_url, val)
{
    var f = document.fpost;

    if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        f.gr_id.value = val;
        f.action      = action_url;
        f.submit();
    }
}
</script>

<form id="fpost" name="fpost" method="post">
<input type="hidden" name="sst"   value="<?=$sst?>">
<input type="hidden" name="sod"   value="<?=$sod?>">
<input type="hidden" name="sfl"   value="<?=$sfl?>">
<input type="hidden" name="stx"   value="<?=$stx?>">
<input type="hidden" name="page"  value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<input type="hidden" name="gr_id">
</form>

<?
include_once('./admin.tail.php');
?>
