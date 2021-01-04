<?php
include_once('./_common.php');

$pattern = '#[/\'\"%=*\#\(\)\|\+\&\!\$~\{\}\[\]`;:\?\^\,]#';
$it_id  = isset($_POST['it_id']) ? preg_replace($pattern, '', $_POST['it_id']) : '';

$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' and it_use = '1' ";
$it = sql_fetch($sql);
$it_point = get_item_point($it);

if(!$it['it_id'])
    die('no-item');

// 장바구니 자료
$cart_id = get_session('ss_cart_id');
$sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$cart_id' and it_id = '$it_id' order by io_type asc, ct_id asc ";
$result = sql_query($sql);

// 판매가격
$sql2 = " select ct_price, it_name, ct_send_cost from {$g5['g5_shop_cart_table']} where od_id = '$cart_id' and it_id = '$it_id' order by ct_id asc limit 1 ";
$row2 = sql_fetch($sql2);

if(!sql_num_rows($result))
    die('no-cart');
?>

<h2>상품옵션수정</h2>
<!-- 장바구니 옵션 시작 { -->
<form name="foption" method="post" action="<?php echo G5_SHOP_URL; ?>/cartupdate.php" onsubmit="return formcheck(this);">
<input type="hidden" name="act" value="optionmod">
<input type="hidden" name="it_id[]" value="<?php echo $it['it_id']; ?>">
<input type="hidden" id="it_price" value="<?php echo $row2['ct_price']; ?>">
<input type="hidden" name="ct_send_cost" value="<?php echo $row2['ct_send_cost']; ?>">
<input type="hidden" name="sw_direct">
<?php
if(defined('G5_THEME_USE_OPTIONS_TRTD') && G5_THEME_USE_OPTIONS_TRTD){
    $option_1 = get_item_options($it['it_id'], $it['it_option_subject'], '');
} else {
    // 선택 옵션 ( 기존의 tr td 태그로 가져오려면 'div' 를 '' 로 바꾸거나 또는 지워주세요 )
    $option_1 = get_item_options($it['it_id'], $it['it_option_subject'], 'div');
}
if($option_1) {
?>
<section class="option_wr">
    <h3>선택옵션</h3>

    <?php // 선택옵션
    echo $option_1;
    ?>

</section>
<?php
}
?>

<?php
if(defined('G5_THEME_USE_OPTIONS_TRTD') && G5_THEME_USE_OPTIONS_TRTD){
    $option_2 = get_item_supply($it['it_id'], $it['it_supply_subject'], '');
} else {
    // 추가 옵션 ( 기존의 tr td 태그로 가져오려면 'div' 를 '' 로 바꾸거나 또는 지워주세요 )
    $option_2 = get_item_supply($it['it_id'], $it['it_supply_subject'], 'div');
}
if($option_2) {
?>
<section class="option_wr">
    <h3>추가옵션</h3>

    <?php // 추가옵션
    echo $option_2;
    ?>

</section>
<?php
}
?>

<div id="sit_sel_option">
	<h3>선택옵션</h3>
    <ul id="sit_opt_added">
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if(!$row['io_id'])
                $it_stock_qty = get_it_stock_qty($row['it_id']);
            else
                $it_stock_qty = get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);

            if($row['io_price'] < 0)
                $io_price = '('.number_format($row['io_price']).'원)';
            else
                $io_price = '(+'.number_format($row['io_price']).'원)';

            $cls = 'opt';
            if($row['io_type'])
                $cls = 'spl';
        ?>
        <li class="sit_<?php echo $cls; ?>_list">
            <input type="hidden" name="io_type[<?php echo $it['it_id']; ?>][]" value="<?php echo $row['io_type']; ?>">
            <input type="hidden" name="io_id[<?php echo $it['it_id']; ?>][]" value="<?php echo $row['io_id']; ?>">
            <input type="hidden" name="io_value[<?php echo $it['it_id']; ?>][]" value="<?php echo $row['ct_option']; ?>">
            <input type="hidden" class="io_price" value="<?php echo $row['io_price']; ?>">
            <input type="hidden" class="io_stock" value="<?php echo $it_stock_qty; ?>">
            <div class="opt_name">
                <span class="sit_opt_subj"><?php echo $row['ct_option']; ?></span>
            </div>
            <div class="opt_count">
                <button type="button" class="sit_qty_minus btn_frmline"><i class="fa fa-minus" aria-hidden="true"></i><span class="sound_only">감소</span></button>
                <label for="ct_qty_<?php echo $i; ?>" class="sound_only">수량</label>
                <input type="text" name="ct_qty[<?php echo $it['it_id']; ?>][]" value="<?php echo $row['ct_qty']; ?>" id="ct_qty_<?php echo $i; ?>" class="num_input" size="5">
                <button type="button" class="sit_qty_plus btn_frmline"><i class="fa fa-plus" aria-hidden="true"></i><span class="sound_only">증가</span></button>
                <span class="sit_opt_prc"><?php echo $io_price; ?></span>
                <button type="button" class="sit_opt_del"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">삭제</span></button>
            </div>

        </li>
        <?php
        }
        ?>
    </ul>
</div>

<div id="sit_tot_price"></div>

<div class="btn_confirm">
    <button type="submit" class="btn_submit">확인</button>
    <button type="button" id="mod_option_close" class="btn_close"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></button>
</div>
</form>

<script>
function formcheck(f)
{
    var val, io_type, result = true;
    var sum_qty = 0;
    var min_qty = parseInt(<?php echo $it['it_buy_min_qty']; ?>);
    var max_qty = parseInt(<?php echo $it['it_buy_max_qty']; ?>);
    var $el_type = $("input[name^=io_type]");

    $("input[name^=ct_qty]").each(function(index) {
        val = $(this).val();

        if(val.length < 1) {
            alert("수량을 입력해 주십시오.");
            result = false;
            return false;
        }

        if(val.replace(/[0-9]/g, "").length > 0) {
            alert("수량은 숫자로 입력해 주십시오.");
            result = false;
            return false;
        }

        if(parseInt(val.replace(/[^0-9]/g, "")) < 1) {
            alert("수량은 1이상 입력해 주십시오.");
            result = false;
            return false;
        }

        io_type = $el_type.eq(index).val();
        if(io_type == "0")
            sum_qty += parseInt(val);
    });

    if(!result) {
        return false;
    }

    if(min_qty > 0 && sum_qty < min_qty) {
        alert("선택옵션 개수 총합 "+number_format(String(min_qty))+"개 이상 주문해 주십시오.");
        return false;
    }

    if(max_qty > 0 && sum_qty > max_qty) {
        alert("선택옵션 개수 총합 "+number_format(String(max_qty))+"개 이하로 주문해 주십시오.");
        return false;
    }

    return true;
}
</script>
<!-- } 장바구니 옵션 끝 -->