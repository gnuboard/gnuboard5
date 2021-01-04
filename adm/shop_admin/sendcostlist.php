<?php
$sub_menu = '400750';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$sql_common = " from {$g5['g5_shop_sendcost_table']} ";

$sql_search = " where (1) ";
$sql_order = " order by sc_id desc ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$g5['title'] = '추가배송비관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
?>

<section id="scp_list">
    <h2>추가배송비 내역</h2>

    <form name="fsendcost" id="fsendcost" method="post" action="./sendcostupdate.php" onsubmit="return fsendcost_submit(this);">
    <input type="hidden" name="w" value="d">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="token" value="">
    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption>추가배송비 내역</caption>
        <thead>
        <tr>
            <th scope="col">
                <label for="chkall" class="sound_only">내역 전체</label>
                <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
            </th>
            <th scope="col">지역명</th>
            <th scope="col">우편번호</th>
            <th scope="col">추가배송비</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
        ?>
        <tr class="<?php echo $bg; ?>">
            <td class="td_chk">
                <input type="hidden" id="sc_id_<?php echo $i; ?>" name="sc_id[<?php echo $i; ?>]" value="<?php echo $row['sc_id']; ?>">
                <input type="checkbox" id="chk_<?php echo $i; ?>" name="chk[]" value="<?php echo $i; ?>" title="내역선택">
            </td>
            <td class="td_left"><?php echo $row['sc_name']; ?></td>
            <td class="td_postalbig"><?php echo $row['sc_zip1'].' ~ '.$row['sc_zip2']; ?></td>
            <td class="td_sendcost_add"><?php echo number_format($row['sc_price']); ?></td>
        </tr>
        <?php
        }

        if ($i == 0)
            echo '<tr><td colspan="4" class="empty_table">자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>

    <div class="btn_list01 btn_list">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_frmline">
    </div>

    </form>
</section>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<section id="sendcost_postal">
    <h2 class="h2_frm">추가배송비 등록</h2>

    <form name="fsendcost2" method="post" id="fsendcost2" action="./sendcostupdate.php" autocomplete="off">
    <input type="hidden" name="token" value="">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>추가배송비 등록</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="sc_name">지역명<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="sc_name" value="" id="sc_name" class="required frm_input" size="30" required></td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_zip1">우편번호 시작<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="sc_zip1" id="sc_zip1" required class="required frm_input" size="10"> (입력 예 : 01234)
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_zip2">우편번호 끝<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="sc_zip2" id="sc_zip2" required class="required frm_input" size="10"> (입력 예 : 01234)
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_price">추가배송비<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" name="sc_price" id="sc_price" size="8" required class="required frm_input"> 원</td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="확인" class="btn_submit btn">
    </div>

    </form>

</section>

<script>
function fsendcost_submit(f)
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
include_once(G5_ADMIN_PATH.'/admin.tail.php');