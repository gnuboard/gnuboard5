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
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g4['title'] = '게시판관리';
include_once('./admin.head.php');

$colspan = 8;
?>

<script>
var list_update_php = 'board_list_update.php';
var list_delete_php = 'board_list_delete.php';
</script>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>게시판 검색</legend>
    <span>
        <?=$listall?>
        생성된 게시판수 <?=number_format($total_count)?>개
    </span>
    <label for="sfl">검색대상</label>
    <select id="sfl" name="sfl">
        <option value="bo_table">TABLE</option>
        <option value="bo_subject">제목</option>
        <option value="a.gr_id">그룹ID</option>
    </select>
    <input type="text" name="stx" required value="<?=$stx?>" title="검색어">
    <input type="submit" class="fieldset_submit" value="검색">
</fieldset>
</form>

<?if ($is_admin == 'super') {?>
<div id="btn_add">
    <a href="./board_form.php" id="bo_add">게시판 추가</a>
</div>
<?}?>

<form id="fboardlist" name="fboardlist" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<table class="tbl_bo_list">
<caption>
생성된 게시판 목록
<p>
    여러개의 게시판 설정을 한번에 바꾸실 때는 게시판 체크기능을 이용하세요.
</p>
</caption>
<thead>
<tr>
    <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 게시판 전체선택" onclick="check_all(this.form)"></th>
    <th scope="col"><?=subject_sort_link('a.gr_id')?>그룹</a></th>
    <th scope="col"><?=subject_sort_link('bo_table')?>TABLE</a></th>
    <th scope="col"><?=subject_sort_link('bo_skin', '', 'desc')?>스킨</a></th>
    <th scope="col"><?=subject_sort_link('bo_subject')?>제목</a></th>
    <th scope="col">포인트</th>
    <th scope="col">검색</th>
    <th scope="col">관리</th>
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
    <td>
        <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>" title="<?=get_text($row['bo_subject'])?> 게시판선택">
    </td>
    <td>
        <?if ($is_admin == 'super'){?>
            <?=get_group_select('gr_id[$i]', $row['gr_id'])?>
        <?}else{?>
            <input type="hidden" name="gr_id[<?=$i?>]" value="<?=$row['gr_id']?>"><?=$row['gr_subject']?>
        <?}?>
    </td>
    <td class="td_boid">
        <input type="hidden" name="board_table[<?=$i?>]" value="<?=$row['bo_table']?>">
        <a href="<?=$g4['bbs_path']?>/board.php?bo_table=<?=$row['bo_table']?>"><?=$row['bo_table']?></a>
    </td>
    <td>
        <select id="bo_skin_<?=$i?>" name="bo_skin[<?=$i?>]">
            <?=$skin_options?>
        </select>
        <script>document.getElementById("bo_skin_<?=$i?>").value="<?=$row['bo_skin']?>";</script>
    </td>
    <td><input type="text" id="bo_subject[<?=$i?>]" name="bo_subject[<?=$i?>]" value="<?=get_text($row['bo_subject'])?>" title="게시판제목" size="20"></td>
    <td>
        <label for="bo_read_point_<?=$i?>">읽기</label>
        <input type="text" id="bo_read_point_<?=$i?>" name="bo_read_point[<?=$i?>]" value="<?=$row[bo_read_point]?>" size="2">
        <label for="bo_write_point_<?=$i?>">쓰기</label>
        <input type="text" id="bo_write_point_<?=$i?>" name="bo_write_point[<?=$i?>]" value="<?=$row[bo_write_point]?>" size="2">
        <label for="bo_comment_point_<?=$i?>">댓글</label>
        <input type="text" id="bo_comment_point_<?=$i?>" name="bo_comment_point[<?=$i?>]" value="<?=$row[bo_comment_point]?>" size="2">
        <label for="bo_download_point_<?=$i?>">다운</label>
        <input type="text" id="bo_download_point_<?=$i?>" name="bo_download_point[<?=$i?>]" value="<?=$row[bo_download_point]?>" size="2">
    </td>
    <td>
        <label for="bo_use_search_<?=$i?>">사용</label>
        <input type="checkbox" id="bo_use_search_<?=$i?>" name="bo_use_search[<?=$i?>]" <?=$row[bo_use_search]?"checked":""?> value="1">
        <label for="bo_order_search_<?=$i?>">순서</label>
        <input type="text" id="bo_order_search_<?=$i?>" name="bo_order_search[<?=$i?>]" value="<?=$row[bo_order_search]?>" size="1">
    </td>
    <td><?=$s_upd?> <?=$s_del?> <?=$s_copy?></td>
</tr>
<?
}
if ($i == 0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<div class="btn_list">
    <button onclick="btn_check(this.form, 'update')">선택수정</button>
    <?if ($is_admin == 'super') {?>
    <button onclick="btn_check(this.form, 'delete')">선택삭제</button>
    <a href="./board_form.php">게시판추가</a>
    <?}?>
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
