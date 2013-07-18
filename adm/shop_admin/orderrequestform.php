<?php
$sub_menu = '400430';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

// 요청정보
if(empty($rq)) {
    $sql = " select * from {$g4['shop_request_table']} where rq_id = '$rq_id' ";
    $rq = sql_fetch($sql);
}

if(!$rq['rq_id']) {
    if(!isset($_POST['rq_id']))
        alert('등록된 자료가 없습니다.');
    else
        die('등록된 자료가 없습니다.');
}

switch($rq['rq_type']) {
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

$item = explode(',', $rq['ct_id']);
if(!count($item)) {
    if(!isset($_POST['rq_id']))
        alert($type.'요청된 상품이 없습니다.');
    else
        die($type.'요청된 상품이 없습니다.');
}

// 주문정보
if(empty($od)) {
    $sql = " select * from {$g4['shop_order_table']} where od_id = '{$rq['od_id']}' ";
    $od = sql_fetch($sql);
}

if(!$od['od_id']) {
    if(!isset($_POST['rq_id']))
        alert('주문정보가 존재하지 않습니다.');
    else
        die('주문정보가 존재하지 않습니다.');
}

// 요청내용
$cus_content = conv_content($rq['rq_content'], 0);
$cus_status = '';

if(!$rq['rq_parent']) {
    switch($rq['rq_status']) {
        case 1:
            $cus_status = '처리완료';
            break;
        case 99:
            $cus_status = '고객취소';
            break;
        case 100:
            $cus_status = '처리불가';
            break;
        default:
            break;
    }
}

if($rq['rq_parent']) {
    $sql = " select rq_content from {$g4['shop_request_table']} where rq_id = '{$rq['rq_parent']}' ";
    $cus = sql_fetch($sql);
    $cus_content = conv_content($cus['rq_content'], 0);

    switch($cus['rq_status']) {
        case 1:
            $cus_status = '처리완료';
            break;
        case 99:
            $cus_status = '고객취소';
            break;
        case 100:
            $cus_status = '처리불가';
            break;
        default:
            break;
    }
}

// 요청상품
$sql = " select ct_id, it_id, it_name, ct_option, ct_price, ct_qty, io_type, io_price, ct_status
            from {$g4['shop_cart_table']}
            where uq_id = '{$od['uq_id']}'
            order by ct_id ";
$result = sql_query($sql);

$rq_qstr = "sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;save_stx=$save_stx&amp;page=$page&amp;rq_type=$rq_type";
?>

<section id="sodr_requset_content">
    <h3><?php echo $type; ?>요청 내용</h3>
    <p>
        <?php echo $cus_content; ?>
    </p>
</section>

<section id="sodr_request_log">
    <h3><?php echo $type; ?>요청 처리내역</h3>
    <p>처리내역을 클릭하시면 상세 내용을 확인할 수 있습니다.</p>

    <div id="sodr_request_log_wrap">
        <?php
        $c_rq_id = $rq_id;
        if($rq['rq_parent'])
            $c_rq_id = $rq['rq_parent'];

        $sql = " select rq_id, rq_content, rq_time from {$g4['shop_request_table']} where rq_parent = '$c_rq_id' order by rq_id desc ";
        $result = sql_query($sql);

        for($i=0; $row=sql_fetch_array($result); $i++) {
        ?>
        <p>
            <span><b><?php echo $row['rq_time']; ?></b> <?php echo $row['rq_content']; ?></span>
            <button id="rq_id_<?php echo $row['rq_id']; ?>" class="od_request_list">상세보기</button>
        </p>
        <?php
        }

        if($i == 0)
            echo '<p>처리내역이 없습니다.</p>';
        ?>
    </div>
</section>

<section id="sodr_request_handle">
    <h3><?php echo $type; ?>요청 처리</h3>

    <form name="forderrequest" id="forderrequest" method="post" action="./orderrequestformupdate.php">
    <input type="hidden" name="rq_id" value="<?php echo $rq['rq_parent'] ? $rq['rq_parent'] : $rq['rq_id']; ?>">
    <input type="hidden" name="disp_list" value="<?php echo $disp_list; ?>">
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <td><label for="rq_status">상태</label></td>
        <td>
            <select name="rq_status" id="rq_status">
                <option value="0"<?php echo get_selected($rq['rq_status'], '0'); ?>>선택</option>
                <option value="1"<?php echo get_selected($rq['rq_status'], '1'); ?>><?php echo $type; ?>요청 처리완료</option>
                <option value="99"<?php echo get_selected($rq['rq_status'], '99'); ?>>고객취소</option>
                <option value="100"<?php echo get_selected($rq['rq_status'], '100'); ?>>처리불가</option>
            </select>
        </td>
    </tr>
    <?php if($rq['rq_type'] == 0) { // 취소요청 ?>
    <tr>
        <td><label for="rq_amount1">환불금액</label></td>
        <td>
            <input type="text" name="rq_amount1" id="rq_amount1" value="<?php echo $rq['rq_amount1'] ? $rq['rq_amount1'] : ''; ?>" class="frm_input" size="15"> 원
        </td>
    </tr>
    <tr>
        <td><label for="rq_account">환불계좌</label></td>
        <td>
            <input type="text" name="rq_account" id="rq_account" value="<?php echo $rq['rq_account']; ?>" class="frm_input" size="30">
        </td>
    </tr>
    <?php } // 취소요청 끝 ?>
    <?php if($rq['rq_type'] == 1) { // 교환요청 ?>
    <tr>
        <td><label for="rq_item">교환상품</label></td>
        <td>
            <textarea name="rq_item" id="rq_item"><?php echo $rq['rq_item']; ?></textarea>
        </td>
    </tr>
    <tr>
        <td><label for="dl_company">배송회사</label></td>
        <td>
            <select name="dl_id" id="dl_id">
                <option value="">선택</option>
                <?php
                $sql = "select * from {$g4['shop_delivery_table']} order by dl_order desc, dl_id desc ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++) {
                ?>
                <option value="<?php echo $row['dl_id']; ?>" <?php echo get_selected($rq['dl_company'], $row['dl_id']); ?>><?php echo $row['dl_company']; ?></option>
                <?php
                }
                mysql_free_result($result);
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><label for="rq_invoice">운송장번호</label></td>
        <td>
            <input type="text" name="rq_invoice" id="rq_invoice" value="<?php echo $rq['rq_invoice']; ?>" class="frm_input" size="30">
        </td>
    </tr>
    <tr>
        <td><label for="rq_amount1">상품차액</label></td>
        <td>
            <input type="text" name="rq_amount1" id="rq_amount1" value="<?php echo $rq['rq_amount1'] ? $rq['rq_amount1'] : ''; ?>" class="frm_input" size="15"> 원
        </td>
    </tr>
    <tr>
        <td><label for="rq_account">차액입금계좌</label></td>
        <td>
            <input type="text" name="rq_account" id="rq_account" value="<?php echo $rq['rq_account']; ?>" class="frm_input" size="30">
        </td>
    </tr>
    <?php } // 교환요청 끝 ?>
    <?php if($rq['rq_type'] == 2) { // 반품요청 ?>
    <tr>
        <td><label for="rq_amount1">환불금액</label></td>
        <td>
            <input type="text" name="rq_amount1" id="rq_amount1" value="<?php echo $rq['rq_amount1'] ? $rq['rq_amount1'] : ''; ?>" class="frm_input" size="15"> 원
        </td>
    </tr>
    <tr>
        <td><label for="rq_account">환불계좌</label></td>
        <td>
            <input type="text" name="rq_account" id="rq_account" value="<?php echo $rq['rq_account']; ?>" class="frm_input" size="30">
        </td>
    </tr>
    <?php } // 반품요청 끝 ?>
    <?php
    if($od['od_settle_case'] == '신용카드' || $od['od_settle_case'] == '계좌이체') {
        if($od['od_tax_flag']) {
    ?>
    <tr>
        <td><label for="rq_amount2">과세금액 부분취소</label></td>
        <td>
            <input type="text" name="rq_amount2" id="rq_amount2" value="<?php echo $rq['rq_amount2'] ? $rq['rq_amount2'] : ''; ?>" class="frm_input" size="15"> 원
        </td>
    </tr>
    <tr>
        <td><label for="rq_amount3">비과세금액 부분취소</label></td>
        <td>
            <input type="text" name="rq_amount3" id="rq_amount3" value="<?php echo $rq['rq_amount3'] ? $rq['rq_amount3'] : ''; ?>" class="frm_input" size="15"> 원
        </td>
    </tr>
    <?php } else { ?>
    <tr>
        <td><label for="rq_amount2">금액 부분취소</label></td>
        <td>
            <input type="text" name="rq_amount2" id="rq_amount2" value="<?php echo $rq['rq_amount2'] ? $rq['rq_amount2'] : ''; ?>" class="frm_input" size="15"> 원
        </td>
    </tr>
    <?php
        }
    }
    ?>
    <tr>
        <td><label for="rq_content">처리내용</label></td>
        <td>
            <textarea name="rq_content" id="rq_content"><?php echo $rq['rq_parent'] ? $rq['rq_content'] : ''; ?></textarea>
        </td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <button type="submit" id="request_submit" class="btn_submit">확인</button>
        <a href="./orderrequestlist.php?<?php echo $rq_qstr; ?>">요청목록</a>
    </div>

    </form>
</section>

<script>
$(function() {
    $("form[name=forderrequest]").submit(function(e) {
        e.preventDefault();

        <?php if($cus_status) { ?>
        if(!confirm("<?php echo $cus_status; ?> 상태의 요청내역입니다.\n추가로 처리내용을 입력하시겠습니까?"))
            return false;
        <?php } ?>

        $.post(
            "./orderrequestformupdate.php",
            $(this).serialize(),
            function(data) {
                if(data != "")
                    alert(data);
                else
                    document.location.reload();
            }
        );

        return false;
    });

    $(".od_request_list").click(function() {
        var rq_id = $(this).attr("id").replace(/[^0-9]/g, "");
        $.post(
            "./orderrequestform.php",
            { rq_id: rq_id },
            function(data) {
                $("#order_request").html(data);
            }
        );
    });
});
</script>