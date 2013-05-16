<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*
    $s_page 는 cart.php 일때 수량의 수정, 물품의 삭제를 위한 변수이다.
               orderinquiryview.php 일때 배송상태등을 나타내는 변수이다.

    $s_uq_id 는 유일한 키
*/

if ($s_page == 'cart.php' || $s_page == 'orderinquiryview.php')
    $colspan = 7;
else
    $colspan = 6;
?>

<form name="frmcartlist" method="post">
<table class="basic_tbl">
<thead>
<tr>
    <th scope="col">상품이미지</th>
    <th scope="col">상품명</th>
    <th scope="col">수량</th>
    <th scope="col">판매가</th>
    <th scope="col">소계</th>
<?php
if ($s_page == 'cart.php') {
    echo '<th scope="col">포인트</th>';
    echo '<th scope="col">삭제</th>';
}
?>
</tr>
</thead>
<tbody>
<?php
$tot_point = 0;
$tot_sell_amount = 0;
$tot_cancel_amount = 0;

$goods = $goods_it_id = "";
$goods_count = -1;

// $s_uq_id 로 현재 장바구니 자료 쿼리
$sql = " select a.ct_id,
                a.it_opt1,
                a.it_opt2,
                a.it_opt3,
                a.it_opt4,
                a.it_opt5,
                a.it_opt6,
                a.ct_price,
                a.ct_point,
                a.ct_qty,
                a.ct_status,
                b.it_id,
                b.it_name,
                b.ca_id
           from {$g4['shop_cart_table']} a,
                {$g4['shop_item_table']} b
          where a.uq_id = '$s_uq_id'
            and a.it_id  = b.it_id
          order by a.ct_id ";
$result = sql_query($sql);

$good_info = '';

for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    if (!$goods)
    {
        //$goods = addslashes($row[it_name]);
        //$goods = get_text($row[it_name]);
        $goods = preg_replace("/\'|\"|\||\,|\&|\;/", "", $row['it_name']);
        $goods_it_id = $row['it_id'];
    }
    $goods_count++;

    // 에스크로 상품정보
    if($s_page == 'orderform.php' && $default['de_escrow_use']) {
        if ($i>0)
            $good_info .= chr(30);
        $good_info .= "seq=".($i+1).chr(31);
        $good_info .= "ordr_numb={$od_id}_".sprintf("%04d", $i).chr(31);
        $good_info .= "good_name=".addslashes($row['it_name']).chr(31);
        $good_info .= "good_cntx=".$row['ct_qty'].chr(31);
        $good_info .= "good_amtx=".$row['ct_price'].chr(31);
    }

    if ($i==0) { // 계속쇼핑
        $continue_ca_id = $row['ca_id'];
    }

    if ($s_page == 'cart.php' || $s_page == 'orderinquiryview.php') { // 링크를 붙이고
        $a1 = '<a href="./item.php?it_id='.$row[it_id].'"><b>';
        $a2 = '</b></a>';
        $image = get_it_image($row['it_id'], 70, 70);
    } else { // 붙이지 않고
        $a1 = '<b>';
        $a2 = '</b>';
        $image = get_it_image($row['it_id'], 50, 50);
    }

    $it_name = $a1 . stripslashes($row['it_name']) . $a2;
    if ($row['it_opt1'] || $row['it_opt2'] || $row['it_opt3'] || $row['it_opt4'] || $row['it_opt5'] || $row['it_opt6']) { // 상품에 옵션이 있다면
        $it_name .= '<div class="sod_bsk_itopt">'.print_item_options($row['it_id'], $row['it_opt1'], $row['it_opt2'], $row['it_opt3'], $row['it_opt4'], $row['it_opt5'], $row['it_opt6']).'</div>';
    }

    $point       = $row['ct_point'] * $row['ct_qty'];
    $sell_amount = $row['ct_price'] * $row['ct_qty'];
?>

<tr>
    <td class="sod_bsk_img"><?php echo $image; ?></td>
    <td>
        <input type="hidden" name="ct_id[<?php echo $i; ?>]"    value="<?php echo $row['ct_id']; ?>">
        <input type="hidden" name="it_id[<?php echo $i; ?>]"    value="<?php echo $row['it_id']; ?>">
        <input type="hidden" name="ap_id[<?php echo $i; ?>]"    value="<?php echo $row['ap_id']; ?>">
        <input type="hidden" name="bi_id[<?php echo $i; ?>]"    value="<?php echo $row['bi_id']; ?>">
        <input type="hidden" name="it_name[<?php echo $i; ?>]"  value="<?php echo get_text($row['it_name']); ?>">
        <?php echo $it_name; ?>
    </td>

    <?php
    // 수량, 입력(수량)
    if ($s_page == "cart.php")
        echo '<td class="td_num"><input type="text" name="ct_qty['.$i.']" value="'.$row['ct_qty'].'" id="ct_qty_'.$i.'" class="frm_input" size="4" maxlength="6" autocomplete="off"></td>';
    else
        echo '<td class="td_num">'.$row['ct_qty'].'</td>';
    ?>

    <td class="td_bignum"><?php echo number_format($row['ct_price']); ?></td>
    <td class="td_bignum"><?php echo number_format($sell_amount); ?></td>

    <?php
    if ($s_page == 'cart.php') {
        echo '<td class="td_bignum"><?php echo number_format($point); ?></td>';
        echo '<td class="td_smallmng"><a href="'.G4_SHOP_URL.'/cartupdate.php?act=d&amp;ct_id='.$row['ct_id'].'">삭제</a></td>';
    }
    ?>
</tr>

<?php
    //$tot_point       += $point;
    //$tot_sell_amount += $sell_amount;

    if ($row['ct_status'] == '취소' || $row['ct_status'] == '반품' || $row['ct_status'] == '품절') {
        $tot_cancel_amount += $sell_amount;
    }
    else {
        $tot_point       += $point;
        $tot_sell_amount += $sell_amount;
    }

    // 배송비가 넘어왔다면
    if ($_POST['od_send_cost']) {
        $send_cost = (int)$_POST['od_send_cost'];
    } else {
        // 배송비 계산
        if ($default['de_send_cost_case'] == '없음')
            $send_cost = 0;
        else {
            // 배송비 상한 : 여러단계의 배송비 적용 가능
            $send_cost_limit = explode(";", $default['de_send_cost_limit']);
            $send_cost_list  = explode(";", $default['de_send_cost_list']);
            $send_cost = 0;
            for ($k=0; $k<count($send_cost_limit); $k++) {
                // 총판매금액이 배송비 상한가 보다 작다면
                if ($tot_sell_amount < $send_cost_limit[$k]) {
                    $send_cost = $send_cost_list[$k];
                    break;
                }
            }
        }

        // 이미 주문된 내역을 보여주는것이므로 배송비를 주문서에서 얻는다.
        $sql = "select od_send_cost from {$g4['shop_order_table']} where od_id = '$od_id' ";
        $row = sql_fetch($sql);
        if ($row['od_send_cost'] > 0)
            $send_cost = $row['od_send_cost'];
    }
} // for 끝

if ($i == 0) echo '<tr><td colspan="'.$colspan.'" class="empty_table">장바구니에 담긴 상품이 없습니다.</td></tr>';
?>
</tbody>
</table>

<?php if ($goods_count) $goods .= ' 외 '.$goods_count.'건'; ?>

<?php
// 배송비가 0 보다 크다면 (있다면)
if ($send_cost > 0)
{
?>

<div id="sod_bsk_dvr" class="sod_bsk_tot">
    <span>배송비</span>
    <strong><?php echo number_format($send_cost); ?> 원</strong>
</div>

<?php } ?>

<?php
// 총계 = 주문상품금액합계 + 배송비
$tot_amount = $tot_sell_amount + $send_cost;
if ($tot_amount > 0) {
?>

<div id="sod_bsk_cnt" class="sod_bsk_tot">
    <span>총계</span>
    <strong><?php echo number_format($tot_amount); ?> 원 <?php echo number_format($tot_point); ?> 점</strong>
</div>

<?php } ?>

<?php if ($s_page == 'cart.php') { ?>
<div id="sod_bsk_act">
    <?php if ($i == 0) { ?>
    <a href="<?php echo G4_SHOP_URL; ?>/" class="btn01">쇼핑 계속하기</a>
    <?php } else { ?>
    <input type="hidden" name="url" value="<?php echo G4_SHOP_URL; ?>/orderform.php">
    <input type="hidden" name="act" value="">
    <input type="hidden" name="records" value="<?php echo $i; ?>">
    <p>장바구니의 상품을 주문하시려면 <strong>주문하기</strong>를 클릭하세요. <strong>비우기</strong>는 장바구니의 상품을 모두 비웁니다.</p>
    <a href="<?php echo G4_SHOP_URL; ?>/list.php?ca_id=<?php echo $continue_ca_id; ?>" class="btn01">쇼핑 계속하기</a>
    <a href="javascript:form_check('buy');" class="btn02">주문하기</a>
    <a href="javascript:form_check('allupdate');" class="btn01">수량변경</a>
    <a href="javascript:form_check('alldelete');" class="btn01">비우기</a>
    <?php } ?>
</div>
<?php } ?>

</form>

<?php
if ($s_page == 'cart.php') {
    if ($i != 0) {
?>
<script>
function form_check(act) {
    var f = document.frmcartlist;
    var cnt = f.records.value;

    if (act == "buy")
    {
        f.act.value = act;

        <?php
        if (get_session('ss_mb_id')) // 회원인 경우
        {
            echo "f.action = '".G4_SHOP_URL."/orderform.php';";
            echo "f.submit();";
        }
        else
            echo "document.location.href = '".G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL."/orderform.php")."';";
        ?>
    }
    else if (act == "alldelete")
    {
        f.act.value = act;
        f.action = "<?php echo G4_SHOP_URL; ?>/cartupdate.php";
        f.submit();
    }
    else if (act == "allupdate")
    {
        for (i=0; i<cnt; i++)
        {
            //if (f.elements("ct_qty[" + i + "]").value == "")
            if (document.getElementById('ct_qty_'+i).value == '')
            {
                alert("수량을 입력해 주십시오.");
                //f.elements("ct_qty[" + i + "]").focus();
                document.getElementById('ct_qty_'+i).focus();
                return;
            }
            //else if (isNaN(f.elements("ct_qty[" + i + "]").value))
            else if (isNaN(document.getElementById('ct_qty_'+i).value))
            {
                alert("수량을 숫자로 입력해 주십시오.");
                //f.elements("ct_qty[" + i + "]").focus();
                document.getElementById('ct_qty_'+i).focus();
                return;
            }
            //else if (f.elements("ct_qty[" + i + "]").value < 1)
            else if (document.getElementById('ct_qty_'+i).value < 1)
            {
                alert("수량은 1 이상 입력해 주십시오.");
                //f.elements("ct_qty[" + i + "]").focus();
                document.getElementById('ct_qty_'+i).focus();
                return;
            }
        }
        f.act.value = act;
        f.action = "<?php echo G4_SHOP_URL; ?>/cartupdate.php";
        f.submit();
    }

    return true;
}
</script>
<?php
    }
}
?>
