<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 이전 상품보기
$sql = " select it_id, it_name from {$g5['g5_shop_item_table']}
          where it_id > '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id asc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $prev_title = '이전상품보기 '.$row['it_name'];
    $prev_href = '<a href="'.G5_SHOP_URL.'/item.php?it_id='.$row['it_id'].'">';
    $prev_href = '</a>';
} else {
    $prev_title = '';
    $prev_href = '';
    $prev_href2 = '';
}

// 다음 상품보기
$sql = " select it_id, it_name from {$g5['g5_shop_item_table']}
          where it_id < '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id desc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $next_title = '다음 상품 '.$row['it_name'];
    $next_href = '<a href="'.G5_SHOP_URL.'/item.php?it_id='.$row['it_id'].'">';
    $next_href2 = '</a>';
} else {
    $next_title = '';
    $next_href = '';
    $next_href2 = '';
}
?>

<link rel="stylesheet" href="<?php echo G5_MSHOP_SKIN_URL; ?>/style.css">

<form name="fitem" action="<?php echo $action_url; ?>" method="post" onsubmit="return fitem_submit(this);">
<input type="hidden" name="it_id[]" value="<?php echo $it['it_id']; ?>">
<input type="hidden" name="sw_direct">
<input type="hidden" name="url">

<div id="sit_ov_wrap">
    <div id="sit_pvi">
        <button type="button" id="sit_pvi_prev" class="sit_pvi_btn">이전</button>
        <button type="button" id="sit_pvi_next" class="sit_pvi_btn">다음</button>
        <?php
        // 이미지(중) 썸네일
        $thumb_count = 0;
        for ($i=1; $i<=10; $i++)
        {
            if(!$it['it_img'.$i])
                continue;

            if($thumb_count == 0) echo '<ul id="sit_pvi_slide">';
            $thumb = get_it_thumbnail($it['it_img'.$i], 280, 280);

            if(!$thumb)
                continue;

            echo '<li>';
            echo '<a href="'.G5_SHOP_URL.'/largeimage.php?it_id='.$it['it_id'].'&amp;no='.$i.'" class="popup_item_image slide_img" target="_blank">'.$thumb.'</a>';
            echo '</li>';

            $thumb_count++;
        }
        if ($thumb_count > 0) echo '</ul>';
        ?>
        <script>
        $(function() {
            var time = 500;
            var idx = idx2 = 0;
            var slide_width = $("#sit_pvi_slide").width();
            var slide_count = $("#sit_pvi_slide li").size();
            $("#sit_pvi_slide li:first").css("display", "block");
            if(slide_count > 1)
                $(".sit_pvi_btn").css("display", "inline");

            $("#sit_pvi_prev").click(function() {
                if(slide_count > 1) {
                    idx2 = (idx - 1) % slide_count;
                    if(idx2 < 0)
                        idx2 = slide_count - 1;
                    $("#sit_pvi_slide li:hidden").css("left", "-"+slide_width+"px");
                    $("#sit_pvi_slide li:eq("+idx+")").filter(":not(:animated)").animate({ left: "+="+slide_width+"px" }, time, function() {
                        $(this).css("display", "none").css("left", "-"+slide_width+"px");
                    });
                    $("#sit_pvi_slide li:eq("+idx2+")").css("display", "block").filter(":not(:animated)").animate({ left: "+="+slide_width+"px" }, time,
                        function() {
                            idx = idx2;
                        }
                    );
                }
            });

            $("#sit_pvi_next").click(function() {
                if(slide_count > 1) {
                    idx2 = (idx + 1) % slide_count;
                    $("#sit_pvi_slide li:hidden").css("left", slide_width+"px");
                    $("#sit_pvi_slide li:eq("+idx+")").filter(":not(:animated)").animate({ left: "-="+slide_width+"px" }, time, function() {
                        $(this).css("display", "none").css("left", slide_width+"px");
                    });
                    $("#sit_pvi_slide li:eq("+idx2+")").css("display", "block").filter(":not(:animated)").animate({ left: "-="+slide_width+"px" }, time,
                        function() {
                            idx = idx2;
                        }
                    );
                }
            });
        });
        </script>
    </div>

    <section id="sit_ov">
        <h2>상품간략정보 및 구매기능</h2>
        <strong id="sit_title"><?php echo stripslashes($it['it_name']); ?></strong><br>
        <span id="sit_desc"><?php echo $it['it_basic']; ?></span>
        <?php if ($score = get_star_image($it['it_id'])) { ?>
        <div id="sit_star_sns">
            <?php
            $sns_title = get_text($it['it_name']).' | '.get_text($config['cf_title']);
            $sns_url  = G5_SHOP_URL.'/item.php?it_id='.$it['it_id'];
            ?>
            고객선호도 <span>별<?php echo $score?>개</span>
            <img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $score?>.png" alt="" class="sit_star">
            <?php echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_fb2.png'); ?>
            <?php echo get_sns_share_link('twitter', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_twt2.png'); ?>
            <?php echo get_sns_share_link('googleplus', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_goo2.png'); ?>
        </div>
        <?php } ?>
        <table class="sit_ov_tbl">
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <?php if ($it['it_maker']) { ?>
        <tr>
            <th scope="row">제조사</th>
            <td><?php echo $it['it_maker']; ?></td>
        </tr>
        <?php } ?>

        <?php if ($it['it_origin']) { ?>
        <tr>
            <th scope="row">원산지</th>
            <td><?php echo $it['it_origin']; ?></td>
        </tr>
        <?php } ?>

        <?php if ($it['it_brand']) { ?>
        <tr>
            <th scope="row">브랜드</th>
            <td><?php echo $it['it_brand']; ?></td>
        </tr>
        <?php } ?>

        <?php if ($it['it_model']) { ?>
        <tr>
            <th scope="row">모델</th>
            <td><?php echo $it['it_model']; ?></td>
        </tr>
        <?php } ?>

        <?php if ($it['it_tel_inq']) { // 전화문의일 경우 ?>

        <tr>
            <th scope="row">판매가격</th>
            <td>전화문의</td>
        </tr>

        <?php } else { // 전화문의가 아닐 경우?>
        <?php if ($it['it_cust_price']) { // 1.00.03?>
        <tr>
            <th scope="row">시중가격</th>
            <td><?php echo display_price($it['it_cust_price']); ?></td>
        </tr>
        <?php } // 전화문의 끝?>

        <tr>
            <th scope="row">판매가격</th>
            <td>
                <?php echo number_format(get_price($it)); ?> 원
                <input type="hidden" id="it_price" value="<?php echo get_price($it); ?>">
            </td>
        </tr>

        <?php
        /* 재고 표시하는 경우 주석 해제
        <tr>
            <th scope="row">재고수량</th>
            <td><?php echo number_format(get_it_stock_qty($it_id)); ?> 개</td>
        </tr>
        */
        ?>

        <?php if ($config['cf_use_point']) { // 포인트 사용한다면 ?>
        <tr>
            <th scope="row"><label for="disp_point">포인트</label></th>
            <td>
                <?php
                $it_point = get_item_point($it);
                echo number_format($it_point);
                ?> 점
            </td>
        </tr>
        <?php } ?>
        <?php if($default['de_send_cost_case'] == '개별' && $it['it_sc_type'] != 0) { ?>
        <tr>
            <th><label for="ct_send_cost">배송비결제</label></th>
            <td>
                <?php
                if($it['it_sc_method'] == 2) {
                ?>
                <select name="ct_send_cost" id="ct_send_cost">
                    <option value="0">주문시 결제</option>
                    <option value="1">수령후 지불</option>
                </select>
                <?php
                }
                ?>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>

        <?php
        $option_1 = get_item_options($it['it_id'], $it['it_option_subject']);
        if($option_1) {
        ?>
        <section>
            <h3>선택옵션</h3>
            <table class="sit_ov_tbl">
            <colgroup>
                <col class="grid_3">
                <col>
            </colgroup>
            <tbody>
            <?php // 선택옵션
            echo $option_1;
            ?>
            </tbody>
            </table>
        </section>
        <?php
        }
        ?>

        <?php
        $option_2 = get_item_supply($it['it_id'], $it['it_supply_subject']);
        if($option_2) {
        ?>
        <section>
            <h3>추가옵션</h3>
            <table class="sit_ov_tbl">
            <colgroup>
                <col class="grid_3">
                <col>
            </colgroup>
            <tbody>
            <?php // 추가옵션
            echo $option_2;
            ?>
            </tbody>
            </table>
        </section>
        <?php
        }
        ?>

        <?php } // 전화문의가 아닐 경우 끝?>

        <div id="sit_sel_option">
        <?php if(!$option_1 && !$option_2) { ?>
            <ul id="sit_opt_added">
                <li class="sit_opt_list">
                    <input type="hidden" name="io_type[<?php echo $it_id; ?>][]" value="0">
                    <input type="hidden" name="io_id[<?php echo $it_id; ?>][]" value="">
                    <input type="hidden" name="io_value[<?php echo $it_id; ?>][]" value="<?php echo $it['it_name']; ?>">
                    <input type="hidden" class="io_price" value="0">
                    <input type="hidden" class="io_stock" value="<?php echo $it['it_stock_qty']; ?>">
                    <span class="sit_opt_subj"><?php echo $it['it_name']; ?></span>
                    <span class="sit_opt_prc">(+0원)</span>
                    <div>
                        <input type="text" name="ct_qty[<?php echo $it_id; ?>][]" value="1" class="frm_input" size="5">
                        <button type="button" class="sit_qty_plus btn_frmline">증가</button>
                        <button type="button" class="sit_qty_minus btn_frmline">감소</button>
                    </div>
                </li>
            </ul>
            <script>
            $(function() {
                price_calculate();
            });
            </script>
        <?php } ?>
        </div>

        <div id="sit_tot_price"></div>

        <?php if ($it['it_use']) { ?>
        <ul id="sit_ov_btn">
            <?php if (!$it['it_tel_inq']) { ?>
            <li><input type="submit" onclick="document.pressed=this.value;" value="바로구매" id="sit_btn_buy"></li>
            <li><input type="submit" onclick="document.pressed=this.value;" value="장바구니" id="sit_btn_cart"></li>
            <?php } ?>

            <li><a href="javascript:item_wish(document.fitem, '<?php echo $it['it_id']; ?>');" id="sit_btn_wish">위시리스트</a></li>
            <li><a href="javascript:popup_item_recommend('<?php echo $it['it_id']; ?>');" id="sit_btn_rec">추천하기</a></li>
        </ul>
        <?php } ?>

        <script>
        // 상품보관
        function item_wish(f, it_id)
        {
            f.url.value = "<?php echo G5_SHOP_URL; ?>/wishupdate.php?it_id="+it_id;
            f.action = "<?php echo G5_SHOP_URL; ?>/wishupdate.php";
            f.submit();
        }

        // 추천메일
        function popup_item_recommend(it_id)
        {
            if (!g5_is_member)
            {
                if (confirm("회원만 추천하실 수 있습니다."))
                    document.location.href = "<?php echo G5_BBS_URL; ?>/login.php?url=<?php echo urlencode(G5_SHOP_URL."/item.php?it_id=$it_id"); ?>";
            }
            else
            {
                url = "<?php echo G5_SHOP_URL; ?>/itemrecommend.php?it_id=" + it_id;
                opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
                popup_window(url, "itemrecommend", opt);
            }
        }
        </script>
    </section>
</div>

</form>

<aside id="sit_siblings">
    <h2>다른 상품 보기</h2>
    <?php
    if ($prev_href || $next_href) {
        echo $prev_href.$prev_title.$prev_href2;
        echo $next_href.$next_title.$next_href2;
    } else {
        echo '<span class="sound_only">이 분류에 등록된 다른 상품이 없습니다.</span>';
    }
    ?>
</aside>

<?php
// 관리자가 확인한 사용후기의 갯수를 얻음
$sql = " select count(*) as cnt from `{$g5['g5_shop_item_use_table']}` where it_id = '{$it_id}' and is_confirm = '1' ";
$row = sql_fetch($sql);
$item_use_count = $row['cnt'];

// 상품문의의 갯수를 얻음
$sql = " select count(*) as cnt from `{$g5['g5_shop_item_qa_table']}` where it_id = '{$it_id}' ";
$row = sql_fetch($sql);
$item_qa_count = $row['cnt'];

// 관련상품의 갯수를 얻음
$sql = " select count(*) as cnt
           from {$g5['g5_shop_item_relation_table']} a
           left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id and b.it_use='1')
          where a.it_id = '{$it['it_id']}' ";
$row = sql_fetch($sql);
$item_relation_count = $row['cnt'];

$href = G5_SHOP_URL.'/iteminfo.php?it_id='.$it_id;
?>
<div>
    <ul class="sanchor">
        <li><a href="<?php echo $href; ?>" target="_blank">상품정보</a></li>
        <li><a href="<?php echo $href; ?>&amp;info=use" target="_blank">사용후기 <span class="item_use_count"><?php echo $item_use_count; ?></span></a></li>
        <li><a href="<?php echo $href; ?>&amp;info=qa" target="_blank">상품문의 <span class="item_qa_count"><?php echo $item_qa_count; ?></span></a></li>
        <?php if ($default['de_baesong_content']) { ?><li><a href="<?php echo $href; ?>&amp;info=dvr" target="_blank">배송정보</a></li><?php } ?>
        <?php if ($default['de_change_content']) { ?><li><a href="<?php echo $href; ?>&amp;info=ex" target="_blank">교환정보</a></li><?php } ?>
        <li><a href="<?php echo $href; ?>&amp;info=rel" target="_blank">관련상품 <span class="item_relation_count"><?php echo $item_relation_count; ?></span></a></li>
    </ul>
</div>

<script>
$(function(){
    // 상품이미지 크게보기
    $(".popup_item_image").click(function() {
        var url = $(this).attr("href");
        var top = 10;
        var left = 10;
        var opt = 'scrollbars=yes,top='+top+',left='+left;
        popup_window(url, "largeimage", opt);

        return false;
    });
});

// 바로구매, 장바구니 폼 전송
function fitem_submit(f)
{
    if (document.pressed == "장바구니") {
        f.sw_direct.value = 0;
    } else { // 바로구매
        f.sw_direct.value = 1;
    }

    // 판매가격이 0 보다 작다면
    if (document.getElementById("it_price").value < 0) {
        alert("전화로 문의해 주시면 감사하겠습니다.");
        return false;
    }

    if($(".sit_opt_list").size() < 1) {
        alert("상품의 선택옵션을 선택해 주십시오.");
        return false;
    }

    var val, result = true;
    $("input[name^=ct_qty]").each(function() {
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
    });

    if(!result) {
        return false;
    }

    return true;
}
</script>