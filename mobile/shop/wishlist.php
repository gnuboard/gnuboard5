<?php
include_once('./_common.php');

// 테마에 wishlist.php 있으면 include
if(defined('G5_THEME_SHOP_PATH')) {
    $theme_wishlist_file = G5_THEME_MSHOP_PATH.'/wishlist.php';
    if(is_file($theme_wishlist_file)) {
        include_once($theme_wishlist_file);
        return;
        unset($theme_wishlist_file);
    }
}

$g5['title'] = "위시리스트";
include_once(G5_MSHOP_PATH.'/_head.php');
?>

<div id="sod_ws">

    <form name="fwishlist" method="post" action="./cartupdate.php">
    <input type="hidden" name="act"       value="multi">
    <input type="hidden" name="sw_direct" value="">
    <input type="hidden" name="prog"      value="wish">
    <ul id="wish_li">
    <?php
        $sql = " select a.wi_id, a.wi_time, b.*
                   from {$g5['g5_shop_wish_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                  where a.mb_id = '{$member['mb_id']}'
                  order by a.wi_id desc ";
        $result = sql_query($sql);
        for ($i=0; $row = sql_fetch_array($result); $i++) {

            $out_cd = '';
            $sql = " select count(*) as cnt from {$g5['g5_shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' ";
            $tmp = sql_fetch($sql);
            if(isset($tmp['cnt']) && $tmp['cnt'])
                $out_cd = 'no';

            $it_price = get_price($row);

            if ($row['it_tel_inq']) $out_cd = 'tel_inq';

            $image = get_it_image($row['it_id'], 70, 70);
    ?>

        <li>
            <div class="wish_img"><a href="<?php echo shop_item_url($row['it_id']); ?>"><?php echo $image; ?></a></div>
            <div class="wish_info">
                <a href="<?php echo shop_item_url($row['it_id']); ?>" class="wish_prd"><?php echo stripslashes($row['it_name']); ?></a>
                <span class="info_price"><?php echo display_price(get_price($row), $row['it_tel_inq'])."\n"; ?></span>
                <span class="info_date"><?php echo substr($row['wi_time'], 2, 17); ?></span>
            
                <div class="wish_chk">
                    <?php if(is_soldout($row['it_id'])) { // 품절검사?>
                    <span class="sold_out">품절</span>
                    <?php } else { //품절이 아니면 체크할수 있도록한다 ?>
					<div class="chk_box">
                    	<input type="checkbox" name="chk_it_id[<?php echo $i; ?>]" value="1" id="chk_it_id_<?php echo $i; ?>" onclick="out_cd_check(this, '<?php echo $out_cd; ?>');" class="selec_chk">
                    	<label for="chk_it_id_<?php echo $i; ?>"><span></span><b class="sound_only"><?php echo $row['it_name']; ?></b></label>
                    </div>
                    <?php } ?>
                    <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
                    <input type="hidden" name="io_type[<?php echo $row['it_id']; ?>][0]" value="0">
                    <input type="hidden" name="io_id[<?php echo $row['it_id']; ?>][0]" value="">
                    <input type="hidden" name="io_value[<?php echo $row['it_id']; ?>][0]" value="<?php echo $row['it_name']; ?>">
                    <input type="hidden" name="ct_qty[<?php echo $row['it_id']; ?>][0]" value="1">
                </div>
                <span class="wish_del"><a href="<?php echo G5_SHOP_URL; ?>/wishupdate.php?w=d&amp;wi_id=<?php echo $row['wi_id']; ?>"><i class="fa fa-trash" aria-hidden="true"></i><span class="sound_only">삭제</span></a></span>
            </div>

        </li>
        <?php
        }
        if ($i == 0)
            echo '<li class="empty_table">위시리스트가 비었습니다.</li>';
        ?>
    </ul>

    <div id="sod_ws_act">
        <button type="submit" class="btn02" onclick="return fwishlist_check(document.fwishlist,'direct_buy');">바로구매</button>
        <button type="submit" class="btn01" onclick="return fwishlist_check(document.fwishlist,'');">장바구니</button>
    </div>
    </form>
</div>

<script>
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
            return false;
        }

        if (act == "direct_buy")
        {
            f.sw_direct.value = 1;
        }
        else
        {
            f.sw_direct.value = 0;
        }

        return true;
    }
//-->
</script>

<?php
include_once(G5_MSHOP_PATH.'/_tail.php');