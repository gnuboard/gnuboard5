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
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == '') $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$mb = array();
if ($sfl == 'mb_id' && $stx)
    $mb = get_member(isset($stx));

$g4['title'] = '포인트관리';
include_once ('./admin.head.php');

$colspan = 8;
?>

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
    <span>
        <?=$listall?>
        전체 <?=number_format($total_count)?> 건
        <?
        if (isset($mb['mb_id']) && $mb['mb_id']) {
            echo '&nbsp;(' . $mb['mb_id'] .' 님 포인트 합계 : ' . number_format($mb['mb_point']) . '점)';
        } else {
            $row2 = sql_fetch(" select sum(po_point) as sum_point from {$g4['point_table']} ");
            echo '&nbsp;(전체 합계 '.number_format($row2['sum_point']).'점)';
        }
        ?>
        <? if ($is_admin == 'super') { ?><!-- <a href="javascript:point_clear();">포인트정리</a> --><? } ?>
    </span>
    <select name="sfl" title="검색대상">
        <option value="mb_id"<?=get_selected($_GET['sfl'], "mb_id");?>>회원아이디</option>
        <option value="po_content"<?=get_selected($_GET['sfl'], "po_content");?>>내용</option>
    </select>
    <input type="text" name="stx" class="required frm_input" required value="<?=$stx?>" title="검색어(필수)">
    <input type="submit" class="btn_submit" value="검색">
</fieldset>
</form>

<section class="cbox">
    <h2>포인트 내역</h2>

    <form id="fpointlist" name="fpointlist" method="post">
    <input type="hidden" name="sst" value="<?=$sst?>">
    <input type="hidden" name="sod" value="<?=$sod?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <input type="hidden" name="token" value="<?=$token?>">

    <table class="tbl_pt_list">
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
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        if ($i==0 || ($row2['mb_id'] != $row['mb_id'])) {
            $sql2 = " select mb_id, mb_name, mb_nick, mb_email, mb_homepage, mb_point from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
            $row2 = sql_fetch($sql2);
        }

        $mb_nick = get_sideview($row['mb_id'], $row2['mb_nick'], $row2['mb_email'], $row2['mb_homepage']);

        $link1 = $link2 = '';
        if (!preg_match("/^\@/", $row['po_rel_table']) && $row['po_rel_table']) {
            $link1 = '<a href="'.G4_BBS_URL.'/board.php?bo_table='.$row['po_rel_table'].'&amp;wr_id='.$row['po_rel_id'].'" target="_blank">';
            $link2 = '</a>';
        }
    ?>

    <tr>
        <td class="td_chk">
            <input type="hidden" id="mb_id_<?=$i?>" name="mb_id[<?=$i?>]" value="<?=$row['mb_id']?>">
            <input type="hidden" id="po_id_<?=$i?>" name="po_id[<?=$i?>]" value="<?=$row['po_id']?>">
            <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>" title="내역선택">
        </td>
        <td class="td_mbid"><a href="?sfl=mb_id&amp;stx=<?=$row['mb_id']?>"><?=$row['mb_id']?></a></td>
        <td class="td_mbname"><?=$row2['mb_name']?></td>
        <td class="td_name"><div><?=$mb_nick?></div></td>
        <td class="td_time"><?=$row['po_datetime']?></td>
        <td class="td_pt_log"><?=$link1?><?=$row['po_content']?><?=$link2?></td>
        <td class="td_num td_pt"><?=number_format($row['po_point'])?></td>
        <td class="td_bignum td_pt"><?=number_format($row2['mb_point'])?></td>
    </tr>

    <?
    }

    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <button onclick="btn_check(this.form, 'delete')">선택삭제</button>
    </div>

    </form>
</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");?>

<section id="point_mng" class="cbox">
    <h2>개별회원 포인트 증감 설정</h2>

    <form id="fpointlist2" name="fpointlist2" method="post" action="./point_update.php" autocomplete="off">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="sst" value="<?=$sst?>">
    <input type="hidden" name="sod" value="<?=$sod?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <input type="hidden" name="token" value="<?=$token?>">

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">회원아이디<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="mb_id" name="mb_id" class="required frm_input" required value="<?=$mb_id?>"></td>
    </tr>
    <tr>
        <th scope="row"><label for="po_content">포인트 내용<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="po_content" name="po_content" class="required frm_input" required size="80"></td>
    </tr>
    <tr>
        <th scope="row"><label for="po_point">포인트<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="po_point" name="po_point" class="required frm_input" required></td>
    </tr>
    </tbody>
    </table>

    <fieldset id="admin_confirm">
        <legend>XSS 혹은 CSRF 방지</legend>
        <p>관리자 권한을 탈취당하는 경우를 대비하여 패스워드를 다시 한번 확인합니다.</p>
        <label for="admin_password">관리자패스워드<strong class="sound_only">필수</strong></label>
        <input type="password" id="admin_password" name="admin_password" class="required frm_input" required>
    </fieldset>

    <div class="btn_confirm">
        <input type="submit" class="btn_submit" value="확인">
    </div>

    </form>

</fieldset>

<?
include_once ('./admin.tail.php');
?>
