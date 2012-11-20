<?
$sub_menu = "200200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

$sql_common = " from {$g4['point_table']} ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_id' :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "po_id";
    $sod = "desc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == '') $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '<a href="'.$_SERVER['PHP_SELF'].'">처음</a>';

if ($sfl == 'mb_id' && $stx)
    $mb = get_member($stx);

$g4['title'] = '포인트관리';
include_once ('./admin.head.php');

$colspan = 8;
?>

<script src="<?=$g4['path']?>/js/sideview.js"></script>
<script>
var list_update_php = '';
var list_delete_php = 'point_list_delete.php';
</script>

<script>
function point_clear()
{
    if (confirm('포인트 정리를 하시면 최근 50건 이전의 포인트 부여 내역을 삭제하므로 포인트 부여 내역을 필요로 할때 찾지 못할 수도 있습니다. 그래도 진행하시겠습니까?'))
    {
        document.location.href = "./point_clear.php?ok=1";
    }
}
</script>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>포인트 내역 검색</legend>
    <div>
        <span><?=$listall?></span>
        건수 : <?=number_format($total_count)?>
        <?
        if ($mb['mb_id'])
            echo '&nbsp;(' . $mb['mb_id'] .' 님 포인트 합계 : ' . number_format($mb[mb_point]) . '점)';
        else {
            $row2 = sql_fetch(" select sum(po_point) as sum_point from {$g4['point_table']} ");
            echo '&nbsp;(전체 포인트 합계 : ' . number_format($row2['sum_point']) . '점)';
        }
        ?>
        <? if ($is_admin == 'super') { ?><!-- <a href="javascript:point_clear();">포인트정리</a> --><? } ?>
    </div>
    <label for="sfl">검색대상</label>
    <select id="sfl" name="sfl">
        <option value="mb_id">회원아이디</option>
        <option value="po_content">내용</option>
    </select>
    <label for="stx">검색어</label>
    <input type="text" id="stx" name="stx" required value="<?=$stx?>">
    <input type="submit" value="검색">
</fieldset>
</form>

<form id="fpointlist" name="fpointlist" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">

<table>
<caption>
    포인트 내역
</caption>
<thead>
<tr>
    <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 포인트 내역 전체선택" onclick="check_all(this.form)"></th>
    <th scope="col"><?=subject_sort_link('mb_id')?>회원아이디</a></th>
    <th scope="col">이름</th>
    <th scope="col">별명</th>
    <th scope="col"><?=subject_sort_link('po_datetime')?>일시</a></th>
    <th scope="col"><?=subject_sort_link('po_content')?>포인트 내용</a></th>
    <th scope="col"><?=subject_sort_link('po_point')?>포인트</a></th>
    <th scope="col">포인트합</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ($row2['mb_id'] != $row['mb_id'])
    {
        $sql2 = " select mb_id, mb_name, mb_nick, mb_email, mb_homepage, mb_point from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
        $row2 = sql_fetch($sql2);
    }

    $mb_nick = get_sideview($row['mb_id'], $row2['mb_nick'], $row2['mb_email'], $row2['mb_homepage']);

    $link1 = $link2 = '';
    if (!preg_match("/^\@/", $row['po_rel_table']) && $row['po_rel_table'])
    {
        $link1 = '<a href="'.$g4['bbs_path'].'/board.php?bo_table='.$row['po_rel_table'].'&amp;wr_id='.$row['po_rel_id'].'" target="_blank">';
        $link2 = '</a>';
    }
?>

<tr>
    <td>
        <input type="hidden" id="mb_id_<?=$i?>" name="mb_id[<?=$i?>]" value="<?=$row['mb_id']?>">
        <input type="hidden" id="po_id_<?=$i?>" name="po_id[<?=$i?>]" value="<?=$row[po_id]?>">
        <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>" title="내역선택">
    </td>
    <td><a href="?sfl=mb_id&amp;stx=<?=$row['mb_id']?>"><?=$row['mb_id']?></a></td>
    <td><?=$row2['mb_name']?></td>
    <td><?=$mb_nick?></td>
    <td><?=$row['po_datetime']?></td>
    <td><?=$link1?><?=$row['po_content']?><?=$link2?></td>
    <td><?=number_format($row[po_point])?></td>
    <td><?=number_format($row2[mb_point])?></td>
</tr>

<?
}

if ($i == 0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<div class="btn_list">
    <input type="button" value="선택삭제" onclick="btn_check(this.form, 'delete')">
</div>

<?
$pagelist = get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&amp;page=");
?>
<div class="paginate">
    <?=$pagelist?>
</div>

<?
if ($stx)
    echo '<script>document.fsearch.sfl.value = \''.$sfl.'\';</script>'.PHP_EOL;

if (strstr($sfl, 'mb_id'))
    $mb_id = $stx;
else
    $mb_id = '';
?>
</form>

<?$colspan=5?>

<form id="fpointlist2" name="fpointlist2" method="post" onsubmit="return fpointlist2_submit(this);" autocomplete="off">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">

<table>
<caption>특정 회원의 포인트 증감 설정</caption>
<tbody>
<tr>
    <th scope="row"><label for="mb_id">회원아이디</label></th>
    <td><input type="text" id="mb_id" name="mb_id" required value="<?=$mb_id?>"></td>
</tr>
<tr>
    <th scope="row"><label for="po_content">포인트 내용</label></th>
    <td><input type="text" id="po_content" name="po_content" required></td>
</tr>
<tr>
    <th scope="row"><label for="po_point">포인트</label></th>
    <td><input type="text" id="po_point" name="po_point" required></td>
</tr>
<tr>
    <th scope="row"><label for="admin_password">관리자패스워드</label></th>
    <td><input type="password" id="admin_password" name="admin_password" required></td>
</tr>
</tbody>
</table>

<div class="btn_confirm">
    <input type="submit" value="확인">
</div>
</form>


<script>
function fpointlist2_submit(f)
{
    f.action = "./point_update.php";
    return true;
}
</script>

<?
include_once ('./admin.tail.php');
?>
