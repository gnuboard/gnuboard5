<?php
$sub_menu = '400430';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '취소교환반품내역';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = "  from {$g4['shop_request_table']} a
                    left join {$g4['shop_order_table']} b on ( a.od_id = b.od_id ) ";
$sql_search = " where rq_parent = '0' ";
$rq_type_text = '요청';

if($rq_type) {
    $sql_search .= " and rq_type = '$rq_type' ";

    switch($rq_type) {
        case 1:
            $rq_type_text = '교환요청';
            break;
        case 2:
            $rq_type_text = '반품요청';
            break;
        default:
            $rq_type_text = '취소요청';
            break;
    }
}
if ($stx != '') {
    if ($sfl != '') {
        $sql_search .= " and $sfl like '%$stx%' ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sfl == '')  $sfl = 'a.od_id';
if (!$sst) {
    $sst = 'rq_id';
    $sod = 'desc';
}

$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == '') { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select a.*, b.od_temp_amount, b.od_name
          $sql_common
          order by $sst $sod, a.rq_id desc
          limit $from_record, $rows ";
$result = sql_query($sql);

$qstr = $qstr.'&amp;save_stx='.$stx;

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">
<input type="hidden" name="rq_type" value="<?php echo $rq_type; ?>">

<fieldset>
    <legend>요청내역 검색</legend>

    <span>
        <?php echo $listall; ?>
        전체 <?php echo $rq_type_text; ?>내역 <?php echo $total_count; ?>건
    </span>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="a.od_id" <?php echo get_selected($sfl, 'a.od_id'); ?>>주문번호</option>
        <option value="od_name" <?php echo get_selected($sfl, 'od_name'); ?>>주문자명</option>
    </select>

    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx; ?>" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">

</fieldset>

</form>

<section class="cbox">
    <h2><?php echo $rq_type_text; ?>내역 목록</h2>

    <ul class="sort_odr">
        <li><a href="<?php echo $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;rq_type=0'; ?>">취소요청</a></li>
        <li><a href="<?php echo $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;rq_type=1'; ?>">교환요청</a></li>
        <li><a href="<?php echo $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;rq_type=2'; ?>">반품요청</a></li>
    </ul>

    <form name="frequestlist" method="post" action="./orderrequestdelete.php" onsubmit="return frequestlist_submit(this);" autocomplete="off">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="rq_type" value="<?php echo $rq_type; ?>">

    <table class="frm_basic">
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">요청내역 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">구분</th>
        <th scope="col">주문번호</th>
        <th scope="col">주문금액</th>
        <th scope="col">주문자</th>
        <th scope="col">상품</th>
        <th scope="col">접수일</th>
        <th scope="col">처리일</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        switch($row['rq_type']) {
            case 0:
                $type = '취소';
                break;
            case 1:
                $type = '교환';
                break;
            case 2:
                $type = '반품';
                break;
            default:
                $type = '';
                break;
        }

        $cart = explode(',', $row['ct_id']);
        $cart_count = count($cart);
        $sql = " select it_name, ct_option from {$g4['shop_cart_table']} where ct_id = '{$cart[0]}' ";
        $ct = sql_fetch($sql);
        $it_name = $ct['it_name']. ' '.$ct['ct_option'];
        if($cart_count > 1)
            $it_name .= '외 '.($cart_count - 1).'건';

        $reg_date = substr($row['rq_time'], 2, 8);
        $done_date = '&nbsp;';
        $sql = " select rq_time
                    from {$g4['shop_request_table']}
                    where rq_parent = '{$row['rq_id']}'
                      and rq_status <> '0'
                    order by rq_id desc
                    limit 1 ";
        $tmp = sql_fetch($sql);
        if($tmp['rq_time'])
            $done_date = substr($tmp['rq_time'], 2, 8);

        $order_href = './orderform.php?od_id='.$row['od_id'].'&amp;rq_id='.$row['rq_id'].'&amp;rq_type='.$rq_type.'&amp;'.$qstr;
    ?>

    <tr>
        <td class="td_chk">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $it_name.' '.$type ?> 요청</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i; ?>">
            <input type="hidden" name="rq_id[<?php echo $i; ?>]" value="<?php echo $row['rq_id']; ?>">
        </td>
        <td class="td_smallstat"><?php echo $type; ?></td>
        <td class="td_odrnum3"><?php echo $row['od_id']; ?></td>
        <td class="td_bignum"><?php echo number_format($row['od_temp_amount']); ?></td>
        <td class="td_name"><?php echo $row['od_name']; ?></td>
        <td><?php echo $it_name; ?></td>
        <td class="td_date"><?php echo $reg_date; ?></td>
        <td class="td_date"><?php echo $done_date; ?></td>
        <td class="td_smallmng">
            <a href="<?php echo $order_href; ?>"><span class="sound_only"><?php echo $it_name. ' '.$type.'요청'; ?> </span>보기</a>
            <a href="./orderrequestdelete.php?w=d&amp;rq_id=<?php echo $row['rq_id']; ?>&amp;<?php echo $qstr; ?>" onclick="return del_confirm();"><span class="sound_only"><?php echo $it_name. ' '.$type.'요청'; ?> </span>삭제</a>
        </td>
    </tr>

    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="9" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
    </div>
    </form>

</section>

<?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
function frequestlist_submit(f)
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

function del_confirm()
{
    return confirm('해당 요청을 삭제하시겠습니까?');
}
</script>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>