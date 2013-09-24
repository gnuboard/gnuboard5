<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 이전 상품보기
$sql = " select it_id, it_name from {$g5['g5_shop_item_table']} where it_id > '$it_id' and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."' and it_use = '1' order by it_id asc limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $prev_title = '이전상품<span class="sound_only"> '.$row['it_name'].'</span>';
    $prev_href = '<a href="./item.php?it_id='.$row['it_id'].'" class="btn01">';
    $prev_href2 = '</a>'.PHP_EOL;
} else {
    $prev_title = '';
    $prev_href = '';
    $prev_href2 = '';
}

// 다음 상품보기
$sql = " select it_id, it_name from {$g5['g5_shop_item_table']} where it_id < '$it_id' and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."' and it_use = '1' order by it_id desc limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $next_title = '다음 상품<span class="sound_only"> '.$row['it_name'].'</span>';
    $next_href = '<a href="./item.php?it_id='.$row['it_id'].'" class="btn01">';
    $next_href2 = '</a>'.PHP_EOL;
} else {
    $next_title = '';
    $next_href = '';
    $next_href2 = '';
}

// 상품 선택옵션 갯수
$sql = " select count(*) as cnt from {$g5['g5_shop_item_option_table']} where it_id = '{$it['it_id']}' and io_type = '0' and io_use = '1' ";
$row = sql_fetch($sql);
$opt_count = $row['cnt'];

// 상품 추가옵션 갯수
$sql = " select count(*) as cnt from {$g5['g5_shop_item_option_table']} where it_id = '{$it['it_id']}' and io_type = '1' and io_use = '1' ";
$row = sql_fetch($sql);
$spl_count = $row['cnt'];

// 고객선호도 별점수
$star_score = get_star_image($it['it_id']);

// 선택 옵션
$option_1 = get_item_options($it['it_id'], $it['it_option_subject']);

// 추가 옵션
$option_2 = get_item_supply($it['it_id'], $it['it_supply_subject']);

// 소셜 관련
$sns_title = get_text($it['it_name']).' | '.get_text($config['cf_title']);
$sns_url  = G5_SHOP_URL.'/item.php?it_id='.$it['it_id'];
$sns_share_links .= get_sns_share_link('facebook', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/sns_fb2.png').' ';
$sns_share_links .= get_sns_share_link('twitter', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/sns_twt2.png').' ';
$sns_share_links .= get_sns_share_link('googleplus', $sns_url, $sns_title, G5_SHOP_SKIN_URL.'/img/sns_goo2.png');
?>

<link rel="stylesheet" href="<?php echo G5_SHOP_SKIN_URL; ?>/style.css">

<form name="fitem" method="post" action="<?php echo $action_url; ?>" onsubmit="return fitem_submit(this);">
<input type="hidden" name="it_id[]" value="<?php echo $it_id; ?>">
<input type="hidden" name="sw_direct">
<input type="hidden" name="url">

<div id="sit_ov_wrap">
    <!-- 상품이미지 미리보기 시작 { -->
    <div id="sit_pvi">
        <div id="sit_pvi_big">
        <?php
        $big_img_count = 0;
        $thumbnails = array();
        for($i=1; $i<=10; $i++) {
            if(!$it['it_img'.$i])
                continue;

            $img = get_it_thumbnail($it['it_img'.$i], $default['de_mimg_width'], $default['de_mimg_height']);

            if($img) {
                // 썸네일
                $thumb = get_it_thumbnail($it['it_img'.$i], 60, 60);
                $thumbnails[] = $thumb;
                $big_img_count++;

                echo '<a href="'.G5_SHOP_URL.'/largeimage.php?it_id='.$it['it_id'].'&amp;no='.$i.'" target="_blank" class="popup_item_image">'.$img.'</a>';
            }
        }

        if($big_img_count == 0) {
            echo '<img src="'.G5_SHOP_URL.'/img/no_image.gif" alt="">';
        }
        ?>
        </div>
        <?php
        // 썸네일
        $thumb1 = true;
        $thumb_count = 0;
        $total_count = count($thumbnails);
        if($total_count > 0) {
            echo '<ul id="sit_pvi_thumb">';
            foreach($thumbnails as $val) {
                $thumb_count++;
                $sit_pvi_last ='';
                if ($thumb_count % 5 == 0) $sit_pvi_last = 'class="li_last"';
                    echo '<li '.$sit_pvi_last.'>';
                    echo '<a href="'.G5_SHOP_URL.'/largeimage.php?it_id='.$it['it_id'].'&amp;no='.$thumb_count.'" target="_blank" class="popup_item_image img_thumb">'.$val.'<span class="sound_only"> '.$thumb_count.'번째 이미지 새창</span></a>';
                    echo '</li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
    <!-- } 상품이미지 미리보기 끝 -->

    <!-- 상품 요약정보 및 구매 시작 { -->
    <section id="sit_ov">
        <h2 id="sit_title"><?php echo stripslashes($it['it_name']); ?> 요약정보 및 구매</h2>
        <p id="sit_desc"><?php echo $it['it_basic']; ?></p>
        <p id="sit_opt_info">
            상품 선택옵션 <?php echo $opt_count; ?> 개, 추가옵션 <?php echo $spl_count; ?> 개
        </p>
        <?php if ($star_score) { ?>
        <div id="sit_star_sns">
            고객선호도 <span>별<?php echo $star_score?>개</span>
            <img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $star_score?>.png" alt="" class="sit_star">
            <?php echo $sns_share_links; ?>
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

        <?php if (!$it['it_use']) { // 판매가능이 아닐 경우 ?>
        <tr>
            <th scope="row">판매가격</th>
            <td>판매중지</td>
        </tr>
        </tbody>
        </table>
        <?php } else if ($it['it_tel_inq']) { // 전화문의일 경우 ?>
        <tr>
            <th scope="row">판매가격</th>
            <td>전화문의</td>
        </tr>
        </tbody>
        </table>
        <?php } else { // 전화문의가 아닐 경우?>

            <?php if ($it['it_cust_price']) { ?>
            <tr>
                <th scope="row">시중가격</th>
                <td><?php echo display_price($it['it_cust_price']); ?></td>
            </tr>
            <?php } // 시중가격 끝 ?>

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
                <th scope="row">포인트</th>
                <td>
                    <?php
                    $it_point = get_item_point($it);
                    echo number_format($it_point);
                    ?> 점
                </td>
            </tr>
            <?php } ?>
            <?php
            if($default['de_send_cost_case'] == '무료')
                $sc_method = '무료배송';
            else
                $sc_method = '주문시 결제';

            if($it['it_sc_type'] == 1)
                $sc_method = '무료배송';
            else if($it['it_sc_type'] > 1) {
                if($it['it_sc_method'] == 1)
                    $sc_method = '수령후 지불';
                else if($it['it_sc_method'] == 2) {
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
                <th><label for="ct_send_cost">배송비결제</label></th>
                <td><?php echo $sc_method; ?></td>
            </tr>
            </tbody>
            </table>

            <?php
            if($option_1) {
            ?>
            <!-- 선택옵션 시작 { -->
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
            <!-- } 선택옵션 끝 -->
            <?php
            }
            ?>

            <?php
            if($option_2) {
            ?>
            <!-- 추가옵션 시작 { -->
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
            <!-- } 추가옵션 끝 -->
            <?php
            }
            ?>

        <?php } // 전화문의가 아닐 경우 끝 ?>

        <!-- 선택된 옵션 시작 { -->
        <section id="sit_sel_option">
            <h3>선택된 옵션</h3>
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
        </section>
        <!-- } 선택된 옵션 끝 -->

        <!-- 총 구매액 -->
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
                url = "./itemrecommend.php?it_id=" + it_id;
                opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
                popup_window(url, "itemrecommend", opt);
            }
        }
        </script>
    </section>
    <!-- } 상품 요약정보 및 구매 끝 -->

        <!-- 다른 상품 보기 시작 { -->
        <div id="sit_siblings">
            <?php
            if ($prev_href || $next_href) {
                echo $prev_href.$prev_title.$prev_href2;
                echo $next_href.$next_title.$next_href2;
            } else {
                echo '<span class="sound_only">이 분류에 등록된 다른 상품이 없습니다.</span>';
            }
            ?>
        </div>
        <!-- } 다른 상품 보기 끝 -->
</div>

</form>


<script>
$(function(){
    // 상품이미지 첫번째 링크
    $("#sit_pvi_big a:first").addClass("visible");

    // 상품이미지 미리보기 (썸네일에 마우스 오버시)
    $("#sit_pvi .img_thumb").bind("mouseover focus", function(){
        var idx = $("#sit_pvi .img_thumb").index($(this));
        $("#sit_pvi_big a.visible").removeClass("visible");
        $("#sit_pvi_big a:eq("+idx+")").addClass("visible");
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