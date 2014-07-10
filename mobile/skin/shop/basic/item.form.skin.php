<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/jquery.nicescroll.min.js"></script>
<?php if($config['cf_kakao_js_apikey']) { ?>
<script src="https://developers.kakao.com/sdk/js/kakao.min.js"></script>
<script src="<?php echo G5_JS_URL; ?>/kakaolink.js"></script>
<script>
    // 사용할 앱의 Javascript 키를 설정해 주세요.
    Kakao.init("<?php echo $config['cf_kakao_js_apikey']; ?>");
</script>
<?php } ?>

<form name="fitem" action="<?php echo $action_url; ?>" method="post" onsubmit="return fitem_submit(this);">
<input type="hidden" name="it_id[]" value="<?php echo $it['it_id']; ?>">
<input type="hidden" name="sw_direct">
<input type="hidden" name="url">

<div id="sit_ov_wrap">
    <?php
    // 이미지(중) 썸네일
    $thumb_img = '';
    $thumb_img_w = 280; // 넓이
    $thumb_img_h = 280; // 높이
    for ($i=1; $i<=10; $i++)
    {
        if(!$it['it_img'.$i])
            continue;

        $thumb = get_it_thumbnail($it['it_img'.$i], $thumb_img_w, $thumb_img_h);

        if(!$thumb)
            continue;

        $thumb_img .= '<li>';
        $thumb_img .= '<a href="'.G5_SHOP_URL.'/largeimage.php?it_id='.$it['it_id'].'&amp;no='.$i.'" class="popup_item_image slide_img" target="_blank">'.$thumb.'</a>';
        $thumb_img .= '</li>'.PHP_EOL;
    }
    if ($thumb_img)
    {
        echo '<div id="sit_pvi">'.PHP_EOL;
        echo '<button type="button" id="sit_pvi_prev" class="sit_pvi_btn" style="height:'.$thumb_img_h.'px">이전</button>'.PHP_EOL;
        echo '<button type="button" id="sit_pvi_next" class="sit_pvi_btn" style="height:'.$thumb_img_h.'px">다음</button>'.PHP_EOL;
        echo '<ul id="sit_pvi_slide" style="width:'.$thumb_img_w.'px;height:'.$thumb_img_h.'px">'.PHP_EOL;
        echo $thumb_img;
        echo '</ul>'.PHP_EOL;
        echo '</div>';
    }
    ?>

    <section id="sit_ov">
        <h2>상품간략정보 및 구매기능</h2>
        <strong id="sit_title"><?php echo stripslashes($it['it_name']); ?></strong><br>
        <span id="sit_desc"><?php echo $it['it_basic']; ?></span>
        <?php if($is_orderable) { ?>
        <p id="sit_opt_info">
            상품 선택옵션 <?php echo $option_count; ?> 개, 추가옵션 <?php echo $supply_count; ?> 개
        </p>
        <?php } ?>
        <div id="sit_star_sns">
            <?php
            $sns_title = get_text($it['it_name']).' | '.get_text($config['cf_title']);
            $sns_url  = G5_SHOP_URL.'/item.php?it_id='.$it['it_id'];

            if ($score = get_star_image($it['it_id'])) { ?>
            고객선호도 <span>별<?php echo $score?>개</span>
            <img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $score?>.png" alt="" class="sit_star">
            <?php } ?>
            <?php echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_fb.png'); ?>
            <?php echo get_sns_share_link('twitter', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_twt.png'); ?>
            <?php echo get_sns_share_link('googleplus', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_goo.png'); ?>
            <?php echo get_sns_share_link('kakaotalk', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_kakao.png'); ?>
        </div>
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
        <?php if (!$it['it_use']) { // 판매가능이 아닐 경우 ?>
        <tr>
            <th scope="row">판매가격</th>
            <td>판매중지</td>
        </tr>
        <?php } else if ($it['it_tel_inq']) { // 전화문의일 경우 ?>
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
        <?php } ?>

        <tr>
            <th scope="row">판매가격</th>
            <td>
                <?php echo display_price(get_price($it)); ?>
                <input type="hidden" id="it_price" value="<?php echo get_price($it); ?>">
            </td>
        </tr>
        <?php } ?>

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
                if($it['it_point_type'] == 2) {
                    echo '구매금액(추가옵션 제외)의 '.$it['it_point'].'%';
                } else {
                    $it_point = get_item_point($it);
                    echo number_format($it_point).'점';
                }
                ?>
            </td>
        </tr>
        <?php } ?>
        <?php
        $ct_send_cost_label = '배송비결제';

        if($it['it_sc_type'] == 1)
            $sc_method = '무료배송';
        else {
            if($it['it_sc_method'] == 1)
                $sc_method = '수령후 지불';
            else if($it['it_sc_method'] == 2) {
                $ct_send_cost_label = '<label for="ct_send_cost">배송비결제</label>';
                $sc_method = '<select name="ct_send_cost" id="ct_send_cost">
                                  <option value="0">주문시 결제</option>
                                  <option value="1">수령후 지불</option>
                              </select>';
            }
            else
                $sc_method = '주문시 결제';
        }
        ?>
        <tr>
            <th><?php echo $ct_send_cost_label; ?></th>
            <td><?php echo $sc_method; ?></td>
        </tr>
        <?php if($it['it_buy_min_qty']) { ?>
        <tr>
            <th>최소구매수량</th>
            <td><?php echo number_format($it['it_buy_min_qty']); ?> 개<td>
        </tr>
        <?php } ?>
        <?php if($it['it_buy_max_qty']) { ?>
        <tr>
            <th>최대구매수량</th>
            <td><?php echo number_format($it['it_buy_max_qty']); ?> 개<td>
        </tr>
        <?php } ?>
        </tbody>
        </table>

        <?php
        if($option_item) {
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
            echo $option_item;
            ?>
            </tbody>
            </table>
        </section>
        <?php
        }
        ?>

        <?php
        if($supply_item) {
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
            echo $supply_item;
            ?>
            </tbody>
            </table>
        </section>
        <?php
        }
        ?>

        <?php if ($it['it_use'] && !$it['it_tel_inq'] && !$is_soldout) { ?>
        <div id="sit_sel_option">
        <?php
        if(!$option_item) {
            if(!$it['it_buy_min_qty'])
                $it['it_buy_min_qty'] = 1;
        ?>
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
                        <input type="text" name="ct_qty[<?php echo $it_id; ?>][]" value="<?php echo $it['it_buy_min_qty']; ?>" class="frm_input" size="5">
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
        <?php } ?>

        <?php if($is_soldout) { ?>
        <p id="sit_ov_soldout">상품의 재고가 부족하여 구매할 수 없습니다.</p>
        <?php } ?>

        <div id="sit_ov_btn">
            <?php if ($is_orderable) { ?>
            <input type="submit" onclick="document.pressed=this.value;" value="바로구매" id="sit_btn_buy">
            <input type="submit" onclick="document.pressed=this.value;" value="장바구니" id="sit_btn_cart">
            <?php } ?>
            <?php if(!$is_orderable && $it['it_soldout'] && $it['it_stock_sms']) { ?>
            <a href="javascript:popup_stocksms('<?php echo $it['it_id']; ?>');" id="sit_btn_buy">재입고알림</a>
            <?php } ?>
            <a href="javascript:item_wish(document.fitem, '<?php echo $it['it_id']; ?>');" id="sit_btn_wish">위시리스트</a>
            <a href="javascript:popup_item_recommend('<?php echo $it['it_id']; ?>');" id="sit_btn_rec">추천하기</a>
        </div>
    </section>
</div>

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
$href = G5_SHOP_URL.'/iteminfo.php?it_id='.$it_id;
?>
<div id="sit_more">
    <ul class="sanchor">
        <li><a href="<?php echo $href; ?>" target="_blank">상품정보</a></li>
        <li><a href="<?php echo $href; ?>&amp;info=use" target="_blank">사용후기 <span class="item_use_count"><?php echo $item_use_count; ?></span></a></li>
        <li><a href="<?php echo $href; ?>&amp;info=qa" target="_blank">상품문의 <span class="item_qa_count"><?php echo $item_qa_count; ?></span></a></li>
        <?php if ($default['de_baesong_content']) { ?><li><a href="<?php echo $href; ?>&amp;info=dvr" target="_blank">배송정보</a></li><?php } ?>
        <?php if ($default['de_change_content']) { ?><li><a href="<?php echo $href; ?>&amp;info=ex" target="_blank">교환정보</a></li><?php } ?>
        <?php if($default['de_mobile_rel_list_use']) {?>
        <li><a href="<?php echo $href; ?>&amp;info=rel" target="_blank">관련상품 <span class="item_relation_count"><?php echo $item_relation_count; ?></span></a></li>
        <?php } ?>
    </ul>
</div>

</form>

<script>
$(window).bind("pageshow", function(event) {
    if (event.originalEvent.persisted) {
        document.location.reload();
    }
});

$(function(){
    // 상품이미지 슬라이드
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

// 재입고SMS 알림
function popup_stocksms(it_id)
{
    url = "<?php echo G5_SHOP_URL; ?>/itemstocksms.php?it_id=" + it_id;
    opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
    popup_window(url, "itemstocksms", opt);
}

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