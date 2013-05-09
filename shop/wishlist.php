<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/wishlist.php');
    return;
}

if (!$is_member)
    goto_url(G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL.'/mypage.php'));

$g4['title'] = "보관함";
include_once('./_head.php');
?>

<div id="sod_ws">

    <form name="fwishlist" method="post" action="">
    <input type="hidden" name="act"       value="multi">
    <input type="hidden" name="sw_direct" value="">
    <input type="hidden" name="prog"      value="wish">

    <table class="basic_tbl">
    <thead>
    <tr>
        <th scope="col">선택</th>
        <th scope="col">이미지</th>
        <th scope="col">상품명</th>
        <th scope="col">보관일시</th>
        <th scope="col">삭제</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = " select *
               from {$g4['shop_wish_table']} a,
                    {$g4['shop_item_table']} b
              where a.mb_id = '{$member['mb_id']}'
                and a.it_id  = b.it_id
              order by a.wi_id desc ";
    $result = sql_query($sql);
    for ($i=0; $row = mysql_fetch_array($result); $i++) {

        $out_cd = '';
        for($k=1; $k<=6; $k++){
            $opt = trim($row['it_opt'.$k]);
            if(preg_match("/\n/", $opt)||preg_match("/;/" , $opt)) {
                $out_cd = 'no';
                break;
            }
        }

        $it_amount = get_amount($row);

        if ($row['it_tel_inq']) $out_cd = 'tel_inq';

        $image = get_it_image($row['it_id'].'_s', 70, 70, '');

        $s_del = '';
    ?>

    <tr>
        <td class="td_chk">
            <?php
            // 품절검사
            $it_stock_qty = get_it_stock_qty($row['it_id']);
            if($it_stock_qty <= 0)
            {
            ?>
            품절
            <input type="hidden" name="it_id[<?php echo $i; ?>]">
            <?php } else { //품절이 아니면 체크할수 있도록한다 ?>
            <input type="checkbox" name="it_id[<?php echo $i; ?>]"     value="<?php echo $row['it_id']; ?>" onclick="out_cd_check(this, '<?php echo $out_cd; ?>');">
            <?php } ?>
            <input type="hidden"   name="it_name[<?php echo $i; ?>]"   value="<?php echo $row['it_name']; ?>">
            <input type="hidden"   name="it_amount[<?php echo $i; ?>]" value="<?php echo $it_amount; ?>">
            <input type="hidden"   name="it_point[<?php echo $i; ?>]"  value="<?php echo $row['it_point']; ?>">
            <input type="hidden"   name="ct_qty[<?php echo $i; ?>]"    value="1">
        </td>
        <td class="sod_ws_img"><?php echo $image; ?></td>
        <td><a href="./item.php?it_id=<?php echo $row['it_id']; ?>"><?php echo stripslashes($row['it_name']); ?></a></td>
        <td class="td_datetime"><?php echo $row['wi_time']; ?></td>
        <td class="td_smallmng"><a href="./wishupdate.php?w=d&amp;wi_id=<?php echo $row['wi_id']; ?>">삭제</a></td>
    </tr>
    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="5" class="empty_table">보관함이 비었습니다.</td></tr>';
    ?>
    </tr>
    </table>
    </form>

    <div id="sod_ws_act">
        <a href="javascript:fwishlist_check(document.fwishlist,'');" class="btn01">장바구니 담기</a>
        <a href="javascript:fwishlist_check(document.fwishlist,'direct_buy');" class="btn02">주문하기</a>
    </div>
</div>

<script language="JavaScript">
<!--
    function out_cd_check(fld, out_cd)
    {
        if (out_cd == 'no'){
            alert("옵션이 있는 상품입니다.\n\n상품을 클릭하여 상품페이지에서 옵션을 선택한 후 주문하십시오.");
            fld.checked = false;
            return;
        }

        if (out_cd == 'tel_inq'){
            alert("이 상품은 전화로 문의해 주십시오.\n\n장바구니에 담아 구입하실 수 없습니다.");
            fld.checked = false;
            return;
        }
    }

    function fwishlist_check(f, act)
    {
        var k = 0;
        var length = f.elements.length;

        for(i=0; i<length; i++) {
            if (f.elements[i].checked) {
                k++;
            }
        }

        if(k == 0)
        {
            alert("상품을 하나 이상 체크 하십시오");
            return;
        }

        if (act == "direct_buy")
        {
            f.sw_direct.value = 1;
        }
        else
        {
            f.sw_direct.value = 0;
        }

        f.action="./cartupdate.php";

        f.submit();
    }
//-->
</script>

<?php
include_once('./_tail.php');
?>