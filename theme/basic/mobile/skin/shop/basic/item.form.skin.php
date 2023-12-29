<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.bxslider.js"></script>', 10);
?>

<?php if($config['cf_kakao_js_apikey']) { ?>
<script src="https://developers.kakao.com/sdk/js/kakao.min.js" async></script>
<script>
var kakao_javascript_apikey = "<?php echo $config['cf_kakao_js_apikey']; ?>";
</script>
<script src="<?php echo G5_JS_URL; ?>/kakaolink.js?ver=<?php echo G5_JS_VER; ?>"></script>
<?php } ?>

<form name="fitem" action="<?php echo $action_url; ?>" method="post" onsubmit="return fitem_submit(this);">
<input type="hidden" name="it_id[]" value="<?php echo $it['it_id']; ?>">
<input type="hidden" name="sw_direct">
<input type="hidden" name="url">

<div id="sit_ov_wrap">
    <?php
    // 이미지(중) 썸네일
    $thumb_img = '';
    $thumb_img_w = 600; // 넓이
    $thumb_img_h = 600; // 높이
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
        echo '<ul id="sit_pvi_slide" >'.PHP_EOL;
        echo $thumb_img;
        echo '</ul>'.PHP_EOL;
        echo '<div class="btn_wr"><a href="#" class="pager-prev"><i class="fa fa-angle-left"></i></a><div id="slide-counter"></div><a href="#" class="pager-next"><i class="fa fa-angle-right"></i></a> </div>'.PHP_EOL;
        echo '</div>';
    }
    ?>

    <section id="sit_ov" class="2017_renewal_itemform">
        <h2>상품간략정보 및 구매기능</h2>
        <div class="sit_ov_wr">
            <strong id="sit_title"><?php echo stripslashes($it['it_name']); ?></strong>
            <?php if($it['it_basic']) { ?><p id="sit_desc"><?php echo $it['it_basic']; ?></p><?php } ?>
            <?php if($is_orderable) { ?>
            <p id="sit_opt_info">상품 선택옵션 <?php echo $option_count; ?> 개, 추가옵션 <?php echo $supply_count; ?> 개</p>
            <?php } ?>

            <div id="sit_star_sns">
                <?php
                $sns_title = get_text($it['it_name']).' | '.get_text($config['cf_title']);
                $sns_url  = shop_item_url($it['it_id']);

                if ($score = get_star_image($it['it_id'])) { ?>
                <span class="sound_only">고객평점 <?php echo $score?>개</span>
                <img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $score?>.png" alt="" class="sit_star" width="100">
                <span class="review_num">사용후기 <?php echo $it['it_use_cnt']; ?> 개</span>
                <?php } ?>

                <div class="sit_btn_opt">
                    <span id="btn_wish"><i class="fa fa-heart-o" aria-hidden="true"></i><span class="sound_only">위시리스트</span><span class="btn_wish_num"><?php echo get_wishlist_count_by_item($it['it_id']); ?></span></span>
                    <button type="button" class="btn_sns_share"><i class="fa fa-share-alt" aria-hidden="true"></i><span class="sound_only">sns 공유</span></button>
                    <div class="sns_area">
                        <?php echo get_sns_share_link('facebook', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/facebook.png'); ?>
                        <?php echo get_sns_share_link('twitter', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/twitter.png'); ?>
                        <?php echo get_sns_share_link('kakaotalk', $sns_url, $sns_title, G5_MSHOP_SKIN_URL.'/img/sns_kakao.png'); ?>
                        <?php
                        $href = G5_SHOP_URL.'/iteminfo.php?it_id='.$it_id;
                        ?> 
                        <a href="javascript:popup_item_recommend('<?php echo $it['it_id']; ?>');" id="sit_btn_rec"><i class="fa fa-envelope-o" aria-hidden="true"></i><span class="sound_only">추천하기</span></a>
                    </div>
                </div>
            </div>
            <script>
            $(".btn_sns_share").click(function(){
                $(".sns_area").show();
            });
            $(document).mouseup(function (e){
                var container = $(".sns_area");
                if( container.has(e.target).length === 0)
                container.hide();
            });
            </script>

            <div class="sit_ov_tbl">
                <table >
                <colgroup>
                    <col class="grid_2">
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
                    <th scope="row">포인트</th>
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
                    <td><?php echo number_format($it['it_buy_min_qty']); ?> 개</td>
                </tr>
                <?php } ?>
                <?php if($it['it_buy_max_qty']) { ?>
                <tr>
                    <th>최대구매수량</th>
                    <td><?php echo number_format($it['it_buy_max_qty']); ?> 개</td>
                </tr>
                <?php } ?>
                </tbody>
                </table>
            </div>
        </div>

        <!-- 다른 상품 보기 시작 { -->
        <div id="sit_siblings">
            <?php
            if ($prev_href || $next_href) {
                $prev_title = '<i class="fa fa-caret-left" aria-hidden="true"></i> '.$prev_title;
                $next_title = $next_title.' <i class="fa fa-caret-right" aria-hidden="true"></i>';

                echo $prev_href.$prev_title.$prev_href2;
                echo $next_href.$next_title.$next_href2;
            } else {
                echo '<span class="sound_only">이 분류에 등록된 다른 상품이 없습니다.</span>';
            }
            ?>
            <a href="<?php echo G5_SHOP_URL; ?>/largeimage.php?it_id=<?php echo $it['it_id']; ?>&amp;no=1" target="_blank" class="popup_item_image "><i class="fa fa-search-plus" aria-hidden="true"></i><span class="sound_only">확대보기</span></a>
        </div>
        <!-- } 다른 상품 보기 끝 -->
    </section>
</div>

<div class="btn_option_wr">
    <button type="button" class="btn_cart_op btn_submit btn">구매하기</button>
</div>
 
<div id="btn_option">
    <div class="sl_option">
        <?php
        if($option_item) {
        ?>
        <section class="sit_option">
            <h3>선택옵션</h3>
            <div class="sit_op_sl">
            <?php // 선택옵션
            echo $option_item;
            ?>
            </div>
        </section>
        <?php
        }
        ?>

        <?php
        if($supply_item) {
        ?>
        <section class="sit_option">
            <h3>추가옵션</h3>
            <div class="sit_op_sl">
  
            <?php // 추가옵션
            echo $supply_item;
            ?>
            </div>
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
                    <div class="opt_name">
                        <span class="sit_opt_subj"><?php echo $it['it_name']; ?></span>
                    </div>
                    <div class="opt_count">
                        <label for="ct_qty_<?php echo $i; ?>" class="sound_only">수량</label>
                       <button type="button" class="sit_qty_minus"><i class="fa fa-minus" aria-hidden="true"></i><span class="sound_only">감소</span></button>
                        <input type="text" name="ct_qty[<?php echo $it_id; ?>][]" value="<?php echo $it['it_buy_min_qty']; ?>" id="ct_qty_<?php echo $i; ?>" class="num_input" size="5">
                        <button type="button" class="sit_qty_plus"><i class="fa fa-plus" aria-hidden="true"></i><span class="sound_only">증가</span></button>
                        <span class="sit_opt_prc">+0원</span>
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
            <input type="submit" onclick="document.pressed=this.value;" value="장바구니" id="sit_btn_cart">
            <input type="submit" onclick="document.pressed=this.value;" value="바로구매" id="sit_btn_buy" class="btn_submit">
            <?php } ?>
            <?php if(!$is_orderable && $it['it_soldout'] && $it['it_stock_sms']) { ?>
            <a href="javascript:popup_stocksms('<?php echo $it['it_id']; ?>');" id="sit_btn_phone">재입고알림</a>
            <?php } ?>
            <a href="javascript:item_wish(document.fitem, '<?php echo $it['it_id']; ?>');" id="sit_btn_wish"><span class="sound_only">위시리스트</span><i class="fa fa-heart-o" aria-hidden="true"></i></a>
            <?php if ($naverpay_button_js) { ?>
            <div class="naverpay-item"><?php echo $naverpay_request_js.$naverpay_button_js; ?></div>
            <?php } ?>
        </div>
    </div>
    <button type="button" class="btn_close"><span class="sound_only">닫기</span><i class="fa fa-chevron-down" aria-hidden="true"></i></button>
</div>

<script>
$(function (){
    $(".btn_cart_op").click(function(){
        $("#btn_option").show();
    });
    $("#btn_option .btn_close").click(function(){
        $("#btn_option").hide();
    });
});
</script>

<div id="sit_tab">
    <ul class="tab_tit">
        <li><button type="button" id="btn_sit_inf" rel="#sit_inf" class="selected">상품정보</button></li>
        <li><button type="button" id="btn_sit_use" rel="#sit_use">사용후기</button></li>
        <li><button type="button" id="btn_sit_qa" rel="#sit_qa">상품문의</button></li>
        <li><button type="button" id="btn_sit_dvex" rel="#sit_dvex">배송/교환</button></li>
    </ul>
    <ul class="tab_con">

        <!-- 상품 정보 시작 { -->
        <li id="sit_inf">
            <h2 class="contents_tit"><span>상품 정보</span></h2>

            <?php if ($it['it_explan'] || $it['it_mobile_explan']) { // 상품 상세설명 ?>
            <h3>상품 상세설명</h3>
            <div id="sit_inf_explan">
                <?php echo ($it['it_mobile_explan'] ? conv_content($it['it_mobile_explan'], 1) : conv_content($it['it_explan'], 1)); ?>
            </div>
            <?php } ?>

            <?php
            if ($it['it_info_value']) { // 상품 정보 고시
                $info_data = unserialize(stripslashes($it['it_info_value']));
                if(is_array($info_data)) {
                    $gubun = $it['it_info_gubun'];
                    $info_array = $item_info[$gubun]['article'];
            ?>
            <h3>상품 정보 고시</h3>
            <table id="sit_inf_open">
            <tbody>
            <?php
            foreach($info_data as $key=>$val) {
                $ii_title = $info_array[$key][0];
                $ii_value = $val;
            ?>
            <tr>
                <th scope="row"><?php echo $ii_title; ?></th>
                <td><?php echo $ii_value; ?></td>
            </tr>
            <?php } //foreach?>
            </tbody>
            </table>
            <!-- 상품정보고시 end -->
            <?php
                } else {
                    if($is_admin) {
                        echo '<p>상품 정보 고시 정보가 올바르게 저장되지 않았습니다.<br>config.php 파일의 G5_ESCAPE_FUNCTION 설정을 addslashes 로<br>변경하신 후 관리자 &gt; 상품정보 수정에서 상품 정보를 다시 저장해주세요. </p>';
                    }
                }
            } //if
            ?>
        </li>
        
        <!-- 사용후기 시작 { -->
        <li id="sit_use">
            <h2>사용후기</h2>
            <div id="itemuse"><?php include_once(G5_SHOP_PATH.'/itemuse.php'); ?></div>
        </li>
        <!-- } 사용후기 끝 -->

        <!-- 상품문의 시작 { -->
        <li id="sit_qa">
            <h2>상품문의</h2>
            <div id="itemqa"><?php include_once(G5_SHOP_PATH.'/itemqa.php'); ?></div>
        </li>
        <!-- } 상품문의 끝 -->

        <?php if ($default['de_baesong_content']) { // 배송정보 내용이 있다면 ?>
        <!-- 배송정보 시작 { -->
        <li id="sit_dvex">
            <h2>배송/교환정보</h2>
            
            <div id="sit_dvr">
                <h3>배송정보</h3>
                <?php echo conv_content($default['de_baesong_content'], 1); ?>
            </div>
            <!-- } 배송정보 끝 -->
            <?php } ?>

            <?php if ($default['de_change_content']) { // 교환/반품 내용이 있다면 ?>
            <!-- 교환/반품 시작 { -->
            <div id="sit_ex" >
                <h3>교환/반품</h3>

                <?php echo conv_content($default['de_change_content'], 1); ?>
            </div>
            <!-- } 교환/반품 끝 -->
            <?php } ?>
        </li>
    </ul>
</div>
<script>
$(function (){
    $(".tab_con>li").hide();
    $(".tab_con>li:first").show();   
    $(".tab_tit li button").click(function(){
        $(".tab_tit li button").removeClass("selected");
        $(this).addClass("selected");
        $(".tab_con>li").hide();
        $($(this).attr("rel")).show();
    });
});
</script>
</form>

<?php if($default['de_mobile_rel_list_use']) { ?>
<!-- 관련상품 시작 { -->
<section id="sit_rel">
    <h2>관련상품</h2>
    <div class="sct_wrap">
        <?php
        $rel_skin_file = $skin_dir.'/'.$default['de_mobile_rel_list_skin'];
        if(!is_file($rel_skin_file))
            $rel_skin_file = G5_MSHOP_SKIN_PATH.'/'.$default['de_mobile_rel_list_skin'];

        $sql = " select b.* from {$g5['g5_shop_item_relation_table']} a left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id) where a.it_id = '{$it['it_id']}' and b.it_use='1' ";
        $list = new item_list($rel_skin_file, $default['de_mobile_rel_list_mod'], 0, $default['de_mobile_rel_img_width'], $default['de_mobile_rel_img_height']);
        $list->set_query($sql);
        echo $list->run();
        ?>
    </div>
</section>
<!-- } 관련상품 끝 -->
<?php } ?>

<script>
$(window).bind("pageshow", function(event) {
    if (event.originalEvent.persisted) {
        document.location.reload();
    }
});

$(function(){
    //이미지
    $('#slide-counter').prepend('<strong class="slide-index current-index"></strong> / ');
     
    var slider = $('#sit_pvi_slide').bxSlider({
        auto: true,
        pager:false,
        controls: false,
        onSliderLoad: function (currentIndex){
            $('#slide-counter .current-index').text(currentIndex + 1);
        },
        onSlideBefore: function ($slideElement, oldIndex, newIndex){
            $('#slide-counter .current-index').text(newIndex + 1);
        }
    });
   
    try {
        $('#slide-counter').append('<span class="total-slides">'+slider.getSlideCount()+'</span>');
    } catch (error) {
    }

    $('a.pager-prev').click(function () {
        var current = slider.getCurrentSlide();
        slider.goToPrevSlide(current) - 1;
    });
    $('a.pager-next').click(function () {
        var current = slider.getCurrentSlide();
        slider.goToNextSlide(current) + 1;
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

    if (window.location.href.split("#").length > 1) {
        let id = window.location.href.split("#")[1];
        $("#btn_" + id).trigger("click");
    };
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
            document.location.href = "<?php echo G5_BBS_URL; ?>/login.php?url=<?php echo urlencode(shop_item_url($it_id)); ?>";
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

function fsubmit_check(f)
{
    // 판매가격이 0 보다 작다면
    if (document.getElementById("it_price").value < 0) {
        alert("전화로 문의해 주시면 감사하겠습니다.");
        return false;
    }

    if($(".sit_opt_list").length < 1) {
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

// 바로구매, 장바구니 폼 전송
function fitem_submit(f)
{
    f.action = "<?php echo $action_url; ?>";
    f.target = "";

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

    if($(".sit_opt_list").length < 1) {
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
<?php /* 2017 리뉴얼한 테마 적용 스크립트입니다. 기존 스크립트를 오버라이드 합니다. */ ?>
<script src="<?php echo G5_JS_URL; ?>/shop.override.js"></script>