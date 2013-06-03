<?php
$sub_menu = "400490";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

$sql_common = " from {$g4['shop_mileage_table']} ";

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
    $sst  = "ml_id";
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
    $mb = get_member($stx);

$g4['title'] = '마일리지관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$colspan = 8;
?>

<script>
var list_update_php = '';
var list_delete_php = 'mileagelist_delete.php';
</script>

<form name="fsearch" id="fsearch" method="get">
<fieldset>
    <legend>마일리지 내역 검색</legend>
    <span>
        <?php echo $listall ?>
        전체 <?php echo number_format($total_count) ?> 건
        <?php
        if (isset($mb['mb_id']) && $mb['mb_id']) {
            echo '&nbsp;(' . $mb['mb_id'] .' 님 마일리지 합계 : ' . number_format($mb['mb_mileage']) . '점)';
        } else {
            $row2 = sql_fetch(" select sum(ml_point) as sum_mileage from {$g4['shop_mileage_table']} ");
            echo '&nbsp;(전체 합계 '.number_format($row2['sum_mileage']).'점)';
        }
        ?>
    </span>
    <select name="sfl" title="검색대상">
        <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>회원아이디</option>
        <option value="sp_content"<?php echo get_selected($_GET['sfl'], "ml_content"); ?>>내용</option>
    </select>
    <input type="text" name="stx" value="<?php echo $stx ?>" title="검색어(필수)" required class="required frm_input">
    <input type="submit" class="btn_submit" value="검색">
</fieldset>
</form>

<section class="cbox">
    <h2>마일리지 내역</h2>

    <form name="fpointlist" id="fpointlist" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="token" value="<?php echo $token; ?>">

    <table class="tbl_pt_list">
    <thead>
    <tr>
        <th scope="col"><input type="checkbox" name="chkall" value="1" id="chkall" title="현재 페이지 마일리지 내역 전체선택" onclick="check_all(this.form)"></th>
        <th scope="col"><?php echo subject_sort_link('mb_id') ?>회원아이디</a></th>
        <th scope="col">이름</th>
        <th scope="col">별명</th>
        <th scope="col"><?php echo subject_sort_link('ml_datetime') ?>일시</a></th>
        <th scope="col"><?php echo subject_sort_link('ml_content') ?>마일리지 내용</a></th>
        <th scope="col"><?php echo subject_sort_link('ml_point') ?>마일리지</a></th>
        <th scope="col">마일리지합</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        if ($i==0 || ($row2['mb_id'] != $row['mb_id'])) {
            $sql2 = " select mb_id, mb_name, mb_nick, mb_email, mb_homepage, mb_mileage from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
            $row2 = sql_fetch($sql2);
        }

        $mb_nick = get_sideview($row['mb_id'], $row2['mb_nick'], $row2['mb_email'], $row2['mb_homepage']);

        $link1 = '<a href="./orderform.php?od_id='.$row['od_id'].'">';
        $link2 = '</a>';
    ?>

    <tr>
        <td class="td_chk">
            <input type="hidden" id="mb_id_<?php echo $i ?>" name="mb_id[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>">
            <input type="hidden" id="ml_id_<?php echo $i ?>" name="ml_id[<?php echo $i ?>]" value="<?php echo $row['ml_id'] ?>">
            <input type="checkbox" id="chk_<?php echo $i ?>" name="chk[]" value="<?php echo $i ?>" title="내역선택">
        </td>
        <td class="td_mbid"><a href="?sfl=mb_id&amp;stx=<?php echo $row['mb_id'] ?>"><?php echo $row['mb_id'] ?></a></td>
        <td class="td_mbname"><?php echo $row2['mb_name'] ?></td>
        <td class="td_name sv_use"><div><?php echo $mb_nick ?></div></td>
        <td class="td_time"><?php echo $row['ml_datetime'] ?></td>
        <td class="td_pt_log"><?php echo $link1 ?><?php echo $row['ml_content'] ?><?php echo $link2 ?></td>
        <td class="td_num td_pt"><?php echo number_format($row['ml_point']) ?></td>
        <td class="td_bignum td_pt"><?php echo number_format($row2['mb_mileage']) ?></td>
    </tr>

    <?php
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

<?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<section id="point_mng" class="cbox">
    <h2>개별회원 마일리지 증감 설정</h2>

    <form name="fmileagelist2" method="post" id="fmileagelist2" action="./mileageupdate.php" autocomplete="off">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="token" value="<?php echo $token; ?>">

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">회원아이디<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="mb_id" value="<?php echo $mb_id ?>" id="mb_id" class="required frm_input" required></td>
    </tr>
    <tr>
        <th scope="row"><label for="ml_content">마일리지 내용<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="ml_content" id="ml_content" required class="required frm_input" size="80"></td>
    </tr>
    <tr>
        <th scope="row"><label for="ml_point">마일리지<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="ml_point" id="ml_point" required class="required frm_input"></td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="확인" class="btn_submit">
    </div>

    </form>

</section>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
