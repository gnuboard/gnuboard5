<?
$sub_menu = "300100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

// DHTML 에디터 사용 필드 추가 : 061021
sql_query(" ALTER TABLE `{$g4['board_table']}` ADD `bo_use_dhtml_editor` TINYINT NOT NULL AFTER `bo_use_secret` ", false);
// RSS 보이기 사용 필드 추가 : 061106
sql_query(" ALTER TABLE `{$g4['board_table']}` ADD `bo_use_rss_view` TINYINT NOT NULL AFTER `bo_use_dhtml_editor` ", false);

$sql_common = " from {$g4['board_table']} a ";
$sql_search = " where (1) ";

if ($is_admin != "super") {
    $sql_common .= " , {$g4['group_table']} b ";
    $sql_search .= " and (a.gr_id = b.gr_id and b.gr_admin = '{$member['mb_id']}') ";
}

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "bo_table" :
            $sql_search .= " ($sfl like '$stx%') ";
            break;
        case "a.gr_id" :
            $sql_search .= " ($sfl = '$stx') ";
            break;
        default :
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "a.gr_id, a.bo_table";
    $sod = "asc";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '<a href="'.$_SERVER['PHP_SELF'].'">처음</a>';

$g4['title'] = '게시판관리';
include_once('./admin.head.php');

$colspan = 8;
?>

<script>
var list_update_php = 'board_list_update.php';
var list_delete_php = 'board_list_delete.php';
</script>

<div id="bo_search">
    <span><?=$listall?> 게시판수 : <?=number_format($total_count)?>개</span>
    <form id="fsearch" name="fsearch" method="get">
    <select id="sfl" name="sfl">
        <option value="bo_table">TABLE</option>
        <option value="bo_subject">제목</option>
        <option value="a.gr_id">그룹ID</option>
    </select>
    <input type="text" id="stx" name="stx" required value="<?=$stx?>">
    <input type="submit" value="검색">
    </form>
</div>

<button id="bo_add">게시판 생성</button>

<form id="fboardlist" name="fboardlist" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<table>
<caption>
각 게시판의 검색사용을 체크하시면 전체 검색 시 결과에 반영됩니다.<br>
검색순서는 전체 검색 시 결과의 우선 순위를 설정합니다.<br>
여러개의 게시판 설정을 한번에 바꾸실 때는 게시판 선택기능을 이용하세요.
</caption>
<thead>
<tr>
    <th scope="col" id="th_group"><?=subject_sort_link('a.gr_id')?>그룹</a></th>
    <th scope="col" id="th_table"><?=subject_sort_link('bo_table')?>TABLE</a></th>
    <th scope="col" id="th_skin"><?=subject_sort_link('bo_skin', '', 'desc')?>스킨</a></th>
    <th scope="col" id="th_subject"><?=subject_sort_link('bo_subject')?>제목</a></th>
    <th scope="col" id="th_check"><input type="checkbox" id="chkall" name="chkall" value="1" onclick="check_all(this.form)"></th>
    <th scope="col" id="th_point">포인트</th>
    <th scope="col" id="th_search">검색</th>
	<th scope="col" id="th_control">관리</th>
</tr>
</thead>
<tbody>
<?
// 스킨디렉토리
$skin_options = '';
$arr = get_skin_dir('board');
for ($k=0; $k<count($arr); $k++)
{
    $option = $arr[$k];
    if (strlen($option) > 10)
        $option = substr($arr[$k], 0, 18) . '…';

    $skin_options .= '<option value="'.$arr[$k].'">'.$option.'</option>';
}

for ($i=0; $row=sql_fetch_array($result); $i++) {
    $s_upd = '<a href="./board_form.php?w=u&amp;bo_table='.$row['bo_table'].'&amp;'.$qstr.'">수정</a>';
    $s_del = "";
    if ($is_admin == 'super') {
        //$s_del = '<a href="javascript:del(\'./board_delete.php?bo_table='.$row[bo_table].'&amp;'.$qstr.'\');">삭제</a>';
        $s_del = '<a href="javascript:post_delete(\'board_delete.php\', \''.$row['bo_table'].'\');">삭제</a>';
    }
    $s_copy = '<a href="javascript:board_copy(\''.$row['bo_table'].'\');">복사</a>';
    ?>

    <tr>
        <td headers="th_group" class="td_group">
            <?if ($is_admin == 'super'){?>
                <label for="gr_id_<?=$i?>">그룹선택</label>
                <?=get_group_select('gr_id[$i]', $row['gr_id'])?>
            <?}else{?>
                <input type="hidden" name="gr_id[<?=$i?>]" value="<?=$row['gr_id']?>"><?=$row['gr_subject']?>
            <?}?>
        </td>
        <td headers="th_table" class="td_table"><a href="<?=$g4['bbs_path']?>/board.php?bo_table=<?=$row['bo_table']?>"><?=$row['bo_table']?></a></td>
        <td headers="th_skin" class="td_skin">
            <label for="bo_skin_<?=$i?>">스킨선택</label>
            <select id="bo_skin_<?=$i?>" name="bo_skin[<?=$i?>]">
                <?=$skin_options?>
            </select>
            <script>document.getElementById("bo_skin_<?=$i?>").value="<?=$row['bo_skin']?>";</script>
        </td>
        <td headers="th_subject" class="td_subject"><input type="text" id="bo_subject[<?=$i?>]" name="bo_subject[<?=$i?>]" value="<?=get_text($row['bo_subject'])?>"></td>
        <td headers="th_check" class="td_check">
            <input type="hidden" id="board_table" name="board_table[<?=$i?>]" value="<?=$row['bo_table']?>">
            <label for="chk_<?=$i?>">게시판선택</label>
            <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>">
        </td>
        <td headers="th_point" class="td_point">
            <label for="bo_read_point_<?=$i?>">읽기포인트</label>
            <input type="text" id="bo_read_point_<?=$i?>" name="bo_read_point[<?=$i?>]" value="<?=$row['bo_read_point']?>">
            <label for="bo_write_point_<?=$i?>">쓰기포인트</label>
            <input type="text" id="bo_write_point_<?=$i?>" name="bo_write_point[<?=$i?>]" value="<?=$row['bo_write_point']?>">
            <label for="bo_comment_point_<?=$i?>">댓글포인트</label>
            <input type="text" id="bo_comment_point_<?=$i?>" name="bo_comment_point[<?=$i?>]" value="<?=$row['bo_comment_point']?>">
            <label for="bo_download_point_<?=$i?>">다운포인트</label>
            <input type="text" id="bo_download_point_<?=$i?>" name="bo_download_point[<?=$i?>]" value="<?=$row['bo_download_point']?>">
        </td>
        <td headers="th_search" class="td_search">
            <label for="bo_use_search_<?=$i?>">검색사용</label>
            <input type="checkbox" id="bo_use_search_<?=$i?>" name="bo_use_search[<?=$i?>]" <?=$row['bo_use_search']?"checked":""?> value="1">
            <label for="bo_order_search_<?=$i?>">검색순서</label>
            <input type="text" id="bo_order_search_<?=$i?>" name="bo_order_search[<?=$i?>]" value="<?=$row['bo_order_search']?>">
        </td>
        <td headers="th_control" class="td_control"><?=$s_upd?> <?=$s_del?> <?=$s_copy?></td>
    </tr>
    <?
}
if ($i == 0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<div class="btn_list">
    <input type="button" value="선택수정" onclick="btn_check(this.form, 'update')">
    <?if ($is_admin == 'super') {?><input type="button" value="선택삭제" onclick="btn_check(this.form, 'delete')"><?}?>
</div>

<?
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page=');
?>
<div class="paginate">
    <?=$pagelist?>
</div>

<?
if ($stx)
    echo '<script>document.fsearch.sfl.value = "'.$sfl.'";</script>';
?>
</form>

<script>
function board_copy(bo_table) {
    window.open("./board_copy.php?bo_table="+bo_table, "BoardCopy", "left=10,top=10,width=500,height=200");
}
</script>

<script>
// POST 방식으로 삭제
function post_delete(action_url, val)
{
	var f = document.fpost;

	if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        f.bo_table.value = val;
		f.action = action_url;
		f.submit();
	}
}
</script>

<form id="fpost" name="fpost" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<input type="hidden" name="bo_table">
</form>

<?
include_once('./admin.tail.php');
?>
