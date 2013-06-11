<?php
$sub_menu = '400650';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$token = get_token();

// 사용하지 않고 사용종료일이 초과된 쿠폰삭제
$end_date = date('Y-m-d', (G4_SERVER_TIME - (86400 * 7)));
$sql = " delete from {$g4['shop_coupon_table']} where cp_used = '0' and cp_end < '$end_date' ";
sql_query($sql);

$sql_common = " from {$g4['shop_coupon_table']} ";

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
    $sst  = "cp_no";
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

$g4['title'] = '쿠폰관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$colspan = 8;
?>

<form name="fsearch" id="fsearch" method="get">
<fieldset>
    <legend>쿠폰 검색</legend>
    <span>
        <?php echo $listall ?>
        전체 <?php echo number_format($total_count) ?> 건
    </span>
    <select name="sfl" title="검색대상">
        <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>회원아이디</option>
        <option value="cp_subject"<?php echo get_selected($_GET['sfl'], "cp_subject"); ?>>쿠폰이름</option>
        <option value="cp_id"<?php echo get_selected($_GET['sfl'], "cp_id"); ?>>쿠폰코드</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
    <input type="submit" class="btn_submit" value="검색">
</fieldset>
</form>

<section id="scp_list" class="cbox">
    <h2>쿠폰 내역</h2>

    <div id="btn_add">
        <a href="./couponform.php" id="coupon_add">쿠폰 추가</a>
    </div>

    <form name="fcouponlist" id="fcouponlist" method="post" action="./couponlist_delete.php" onsubmit="return fcouponlist_submit(this);">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="token" value="<?php echo $token; ?>">

    <table class="tbl_pt_list">
    <thead>
    <tr>
        <th scope="col"><input type="checkbox" name="chkall" value="1" id="chkall" title="현재 페이지 쿠폰 내역 전체선택" onclick="check_all(this.form)"></th>
        <th scope="col">쿠폰코드</th>
        <th scope="col">쿠폰이름</th>
        <th scope="col">적용대상</th>
        <th scope="col"><?php echo subject_sort_link('mb_id') ?>회원아이디</a></th>
        <th scope="col"><?php echo subject_sort_link('cp_end') ?>사용기한</a></th>
        <th scope="col"><?php echo subject_sort_link('cp_used') ?>사용</a></th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        if ($i==0 || ($row2['mb_id'] != $row['mb_id'])) {
            $sql2 = " select mb_id, mb_name, mb_nick, mb_email, mb_homepage from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
            $row2 = sql_fetch($sql2);
        }

        $mb_nick = get_sideview($row['mb_id'], $row2['mb_nick'], $row2['mb_email'], $row2['mb_homepage']);

        switch($row['cp_method']) {
            case '0':
                $sql3 = " select it_name from {$g4['shop_item_table']} where it_id = '{$row['cp_target']}' ";
                $row3 = sql_fetch($sql3);
                $cp_target = get_text($row3['it_name']);
                break;
            case '1':
                $sql3 = " select ca_name from {$g4['shop_category_table']} where ca_id = '{$row['cp_target']}' ";
                $row3 = sql_fetch($sql3);
                $cp_target = get_text($row3['ca_name']);
                break;
            case '2':
                $cp_target = '결제금액';
                break;
            case '3':
                $cp_target = '배송비';
                break;
        }

        $link1 = '<a href="./orderform.php?od_id='.$row['od_id'].'">';
        $link2 = '</a>';
    ?>

    <tr>
        <td class="td_chk">
            <input type="hidden" id="cp_id_<?php echo $i; ?>" name="cp_id[<?php echo $i; ?>]" value="<?php echo $row['cp_id']; ?>">
            <input type="checkbox" id="chk_<?php echo $i; ?>" name="chk[]" value="<?php echo $i; ?>" title="내역선택">
        </td>
        <td class="scp_list_id"><?php echo $row['cp_id']; ?></td>
        <td class="scp_list_name"><?php echo $row['cp_subject']; ?></td>
        <td><?php echo $cp_target; ?></td>
        <td class="td_name sv_use"><div><?php echo $row['mb_id']; ?></div></td>
        <td class="td_time"><?php echo substr($row['cp_start'], 2, 8); ?> ~ <?php echo substr($row['cp_end'], 2, 8); ?></td>
        <td class="td_boolean"><?php echo $row['cp_used'] ? '예' : '아니오'; ?></td>
        <td class="td_mng sv_use">
            <div class="sel_wrap">
                <button type="button" class="sel_btn">관리하기</button>
                <ul class="sel_ul">
                    <li class="sel_li"><a href="./couponform.php?w=u&amp;cp_id=<?php echo $row['cp_id']; ?>&amp;<?php echo $qstr; ?>" class="sel_a"><span class="sound_only"><?php echo $row['cp_id']; ?> </span>수정</a></li>
                </ul>
            </div>
        </td>
    </tr>

    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
    </div>

    </form>
</section>

<script>
function fcouponlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>