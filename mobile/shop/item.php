<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/iteminfo.lib.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');

$captcha_html = captcha_html();

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

$rand = rand(4, 6);
$norobot_key = substr($token, 0, $rand);
set_session('ss_norobot_key', $norobot_key);

// 분류사용, 상품사용하는 상품의 정보를 얻음
$sql = " select a.*,
                b.ca_name,
                b.ca_use
           from {$g4['shop_item_table']} a,
                {$g4['shop_category_table']} b
          where a.it_id = '$it_id'
            and a.ca_id = b.ca_id ";
$it = sql_fetch($sql);
if (!$it['it_id'])
    alert('자료가 없습니다.');
if (!($it['ca_use'] && $it['it_use'])) {
    if (!$is_admin)
        alert('판매가능한 상품이 아닙니다.');
}

// 분류 테이블에서 분류 상단, 하단 코드를 얻음
$sql = " select ca_include_head, ca_include_tail, ca_hp_cert_use, ca_adult_cert_use
           from {$g4['shop_category_table']}
          where ca_id = '{$it['ca_id']}' ";
$ca = sql_fetch($sql);

if(!$is_admin) {
    // 본인확인체크
    if($ca['ca_hp_cert_use'] && !$member['mb_hp_certify']) {
        if($is_member)
            alert('회원정보 수정에서 휴대폰 본인확인 후 이용해 주십시오.');
        else
            alert('휴대폰 본인확인된 로그인 회원만 이용할 수 있습니다.');
    }

    // 성인인증체크
    if($ca['ca_adult_cert_use'] && !$member['mb_adult']) {
        if($is_member)
            alert('휴대폰 본인확인으로 성인인증된 회원만 이용할 수 있습니다.\\n회원정보 수정에서 휴대폰 본인확인을 해주십시오.');
        else
            alert('휴대폰 본인확인으로 성인인증된 회원만 이용할 수 있습니다.');
    }
}

// 오늘 본 상품 저장 시작
// tv 는 today view 약자
$saved = false;
$tv_idx = (int)get_session("ss_tv_idx");
if ($tv_idx > 0) {
    for ($i=1; $i<=$tv_idx; $i++) {
        if (get_session("ss_tv[$i]") == $it_id) {
            $saved = true;
            break;
        }
    }
}

if (!$saved) {
    $tv_idx++;
    set_session("ss_tv_idx", $tv_idx);
    set_session("ss_tv[$tv_idx]", $it_id);
}
// 오늘 본 상품 저장 끝

// 조회수 증가
if ($_COOKIE['ck_it_id'] != $it_id) {
    sql_query(" update {$g4['shop_item_table']} set it_hit = it_hit + 1 where it_id = '$it_id' "); // 1증가
    set_cookie("ck_it_id", $it_id, time() + 3600); // 1시간동안 저장
}

$g4['title'] = $it['it_name'].' &gt; '.$it['ca_name'];

include_once(G4_MSHOP_PATH.'/_head.php');

// 분류 위치
// HOME > 1단계 > 2단계 ... > 6단계 분류
$ca_id = $it['ca_id'];
include G4_MSHOP_PATH.'/navigation1.inc.php';

if ($is_admin)
    echo '<div class="sit_admin"><a href="'.G4_ADMIN_URL.'/shop_admin/itemform.php?w=u&amp;it_id='.$it_id.'" class="btn_admin">상품 관리</a></div>';

// 상단 HTML
echo '<div id="sit_hhtml">'.stripslashes($it['it_mobile_head_html']).'</div>';

// 이전 상품보기
$sql = " select it_id, it_name from {$g4['shop_item_table']}
          where it_id > '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id asc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $prev_title = '이전상품보기 '.$row['it_name'];
    $prev_href = '<a href="'.G4_SHOP_URL.'/item.php?it_id='.$row['it_id'].'">';
    $prev_href = '</a>';
} else {
    $prev_title = '';
    $prev_href = '';
    $prev_href2 = '';
}

// 다음 상품보기
$sql = " select it_id, it_name from {$g4['shop_item_table']}
          where it_id < '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id desc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $next_title = '다음 상품 '.$row['it_name'];
    $next_href = '<a href="'.G4_SHOP_URL.'/item.php?it_id='.$row['it_id'].'">';
    $next_href2 = '</a>';
} else {
    $next_title = '';
    $next_href = '';
    $next_href2 = '';
}

// 관련상품의 갯수를 얻음
$sql = " select count(*) as cnt
           from {$g4['shop_item_relation_table']} a
           left join {$g4['shop_item_table']} b on (a.it_id2=b.it_id and b.it_use='1')
          where a.it_id = '{$it['it_id']}' ";
$row = sql_fetch($sql);
$item_relation_count = $row['cnt'];
?>

<?php
function pg_anchor_m($anc_id) {
    global $default;
?>
            <ul class="sanchor">
                <li><a href="#sit_inf" <?php if ($anc_id == 'inf') echo 'class="sanchor_on"'; ?>>상품정보</a></li>
                <li><a href="#sit_use" <?php if ($anc_id == 'use') echo 'class="sanchor_on"'; ?>>사용후기 <span class="item_use_count"></span></a></li>
                <li><a href="#sit_qa" <?php if ($anc_id == 'qa') echo 'class="sanchor_on"'; ?>>상품문의 <span class="item_qa_count"></span></a></li>
                <?php if ($default['de_baesong_content']) { ?><li><a href="#sit_dvr" <?php if ($anc_id == 'dvr') echo 'class="sanchor_on"'; ?>>배송정보</a></li><?php } ?>
                <?php if ($default['de_change_content']) { ?><li><a href="#sit_ex" <?php if ($anc_id == 'ex') echo 'class="sanchor_on"'; ?>>교환정보</a></li><?php } ?>
                <li><a href="#sit_rel" <?php if ($anc_id == 'rel') echo 'class="sanchor_on"'; ?>>관련상품 <span class="item_relation_count"></span></a></li>
            </ul>
<?php } ?>

<script src="<?php echo G4_JS_URL; ?>/md5.js"></script>
<script src="<?php echo G4_JS_URL; ?>/shop.js"></script>

<?php
if (G4_HTTPS_DOMAIN)
    $action_url = G4_HTTPS_DOMAIN.'/'.G4_SHOP_DIR.'/cartupdate.php';
else
    $action_url = G4_SHOP_URL.'/cartupdate.php';
?>

<div id="sit">

    <form name="fitem" action="<?php echo $action_url; ?>" method="post">
    <input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
    <input type="hidden" name="it_name" value="<?php echo $it['it_name']; ?>">
    <input type="hidden" name="total_price" value="">
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
                echo '<a href="'.G4_SHOP_URL.'/largeimage.php?it_id='.$it['it_id'].'&amp;no='.$i.'" class="popup_item_image slide_img" target="_blank">'.$thumb.'</a>';
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

        <?php //echo it_name_icon($it, false, 0); ?>

        <section id="sit_ov">
            <h2>상품간략정보 및 구매기능</h2>
            <strong id="sit_title"><?php echo stripslashes($it['it_name']); ?></strong><br>
            <span id="sit_desc"><?php echo $it['it_basic']; ?></span>
            <?php if ($score = get_star_image($it['it_id'])) { ?>
            <div id="sit_star_sns">
                <?php
                $sns_title = get_text($it['it_name']).' | '.get_text($config['cf_title']);
                $sns_url  = G4_SHOP_URL.'/item.php?it_id='.$it['it_id'];
                ?>
                고객선호도 <span>별<?php echo $score?>개</span>
                <img src="<?php echo G4_URL; ?>/img/shop/s_star<?php echo $score?>.png" alt="" class="sit_star">
                <?php echo get_sns_share_link('facebook', $sns_url, $sns_title, G4_URL.'/img/shop/sns_fb2.png'); ?>
                <?php echo get_sns_share_link('twitter', $sns_url, $sns_title, G4_URL.'/img/shop/sns_twt2.png'); ?>
                <?php echo get_sns_share_link('googleplus', $sns_url, $sns_title, G4_URL.'/img/shop/sns_goo2.png'); ?>
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

            <?php if (!$it['it_gallery']) { // 갤러리 형식이라면 가격, 구매하기 출력하지 않음 ?>
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
                    <?php echo number_format($it['it_price']); ?> 원
                    <input type="hidden" name="it_price" value="<?php echo get_price($it); ?>">
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
                    <input type="hidden" name="it_point" value="<?php echo $it_point; ?>">
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
                <div class="sit_sel_btn">
                    <button type="button" id="sit_selopt_submit" class="btn_frmline">추가</button>
                </div>
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

            <?php } ?>

            <?php } ?>

            <div id="sit_sel_option">
            <?php if(!$option_1 && !$option_2) { ?>
                <ul id="sit_opt_added">
                    <li class="sit_opt_list">
                        <input type="hidden" name="io_type[]" value="0">
                        <input type="hidden" name="io_id[]" value="">
                        <input type="hidden" name="io_value[]" value="<?php echo $it['it_name']; ?>">
                        <input type="hidden" name="io_price[]" value="0">
                        <input type="hidden" name="io_stock[]" value="<?php echo $it['it_stock_qty']; ?>">
                        <span class="sit_opt_subj"><?php echo $it['it_name']; ?></span>
                        <span class="sit_opt_prc">(+0원)</span>
                        <div>
                            <input type="text" name="ct_qty[]" value="1" class="frm_input" size="5">
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

            <ul id="sit_ov_btn">
                <?php if (!$it['it_tel_inq'] && !$it['it_gallery']) { ?>
                <li><a href="javascript:fitemcheck(document.fitem, 'direct_buy');" id="sit_btn_buy">바로구매</a></li>
                <li><a href="javascript:fitemcheck(document.fitem, 'cart_update');" id="sit_btn_cart">장바구니</a></li>
                <?php } ?>
                <?php if (!$it['it_gallery']) { ?>
                <li><a href="javascript:item_wish(document.fitem, '<?php echo $it['it_id']; ?>');" id="sit_btn_wish">위시리스트</a></li>
                <li><a href="javascript:popup_item_recommend('<?php echo $it['it_id']; ?>');" id="sit_btn_rec">추천하기</a></li>
                <?php } ?>
            </ul>

            <script>
            // 상품보관
            function item_wish(f, it_id)
            {
                f.url.value = "<?php echo G4_SHOP_URL; ?>/wishupdate.php?it_id="+it_id;
                f.action = "<?php echo G4_SHOP_URL; ?>/wishupdate.php";
                f.submit();
            }

            // 추천메일
            function popup_item_recommend(it_id)
            {
                if (!g4_is_member)
                {
                    if (confirm("회원만 추천하실 수 있습니다."))
                        document.location.href = "<?php echo G4_BBS_URL; ?>/login.php?url=<?php echo urlencode(G4_SHOP_URL."/item.php?it_id=$it_id"); ?>";
                }
                else
                {
                    url = "<?php echo G4_SHOP_URL; ?>/itemrecommend.php?it_id=" + it_id;
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

    <script>
    function click_item(id)
    {
        <?php
        echo "var str = 'item_explan,item_use,item_qa";
        if ($default['de_baesong_content']) echo ",item_baesong";
        if ($default['de_change_content']) echo ",item_change";
        echo ",item_relation';";
        ?>

        var s = str.split(',');

        for (i=0; i<s.length; i++)
        {
            if (id=='*')
                document.getElementById(s[i]).style.display = 'block';
            else
                document.getElementById(s[i]).style.display = 'none';
        }

        if (id!='*')
            document.getElementById(id).style.display = 'block';
    }
    </script>

    <section id="sit_inf">
        <h2>상품 정보</h2>
        <?php echo pg_anchor_m('inf'); ?>

        <?php if ($it['it_basic']) { // 상품 기본설명 ?>
        <div id="sit_inf_basic">
             <?php echo $it['it_basic']; ?>
        </div>
        <?php } ?>

        <?php if ($it['it_explan'] || $it['it_mobile_explan']) { // 상품 상세설명 ?>
        <div id="sit_inf_explan">
            <?php echo ($it['it_mobile_explan'] ? conv_content($it['it_mobile_explan'], 1) : conv_content($it['it_explan'], 1)); ?>
        </div>
        <?php } ?>

        <h3>상품 정보 고시</h3>
        <?php
        if ($it['it_info_value']) {
            $info_data = unserialize($it['it_info_value']);
            $gubun = $it['it_info_gubun'];
            $info_array = $item_info[$gubun]['article'];
        ?>
        <!-- 상품정보고시 -->
        <table id="sit_inf_open">
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php
        foreach($info_data as $key=>$val) {
            $ii_title = $info_array[$key][0];
            $ii_value = $val;
        ?>
        <tr valign="top">
            <th scope="row"><?php echo $ii_title; ?></th>
            <td><?php echo $ii_value; ?></th>
        </tr>
        <?php } //foreach?>
        </tbody>
        </table>
        <!-- 상품정보고시 end -->
        <?php } //if?>

    </section>
    <!-- 상품설명 end -->

    <section id="sit_use">
        <h2>사용후기</h2>
        <?php echo pg_anchor_m('use'); ?>

        <?php
        $use_page_rows = 10; // 페이지당 목록수
        include_once(G4_MSHOP_PATH.'/itemuse.inc.php');
        ?>
    </section>

    <section id="sit_qa">
        <h2>상품문의</h2>
        <?php echo pg_anchor_m('qa'); ?>

        <?php
        $qa_page_rows = 10; // 페이지당 목록수
        include_once(G4_MSHOP_PATH.'/itemqa.inc.php');
        ?>
    </section>


    <?php if ($default['de_baesong_content']) { // 배송정보 내용이 있다면 ?>
    <section id="sit_dvr">
        <h2>배송정보</h2>
        <?php echo pg_anchor_m('dvr'); ?>

        <?php echo conv_content($default['de_baesong_content'], 1); ?>
    </section>
    <?php } ?>


    <?php if ($default['de_change_content']) { // 교환/반품 내용이 있다면 ?>
    <section id="sit_ex">
        <h2>교환/반품</h2>
        <?php echo pg_anchor_m('ex'); ?>

        <?php echo conv_content($default['de_change_content'], 1); ?>
    </section>
    <?php } ?>

    <section id="sit_rel">
        <h2>관련상품</h2>
        <?php echo pg_anchor_m('rel'); ?>

        <div class="sct_wrap">
            <?php
            $img_width  = 230;
            $img_height = 230;

            $sql = " select b.*
                       from {$g4['shop_item_relation_table']} a
                       left join {$g4['shop_item_table']} b on (a.it_id2=b.it_id)
                      where a.it_id = '{$it['it_id']}'
                        and b.it_use='1' ";
            $result = sql_query($sql);
            $num = @mysql_num_rows($result);
            if ($num)
                include G4_MSHOP_PATH.'/maintype10.inc.php';
            else
                echo '<p class="sit_empty">이 상품과 관련된 상품이 없습니다.</p>';
            ?>
        </div>
    </section>

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

        $(".item_use_count").text("<?php echo $use_total_count; ?>");
        $(".item_qa_count").text("<?php echo $qa_total_count; ?>");
        $(".item_relation_count").text("<?php echo $item_relation_count; ?>");
    });

    // 바로구매 또는 장바구니 담기
    function fitemcheck(f, act)
    {
        // 판매가격이 0 보다 작다면
        if (f.it_price.value < 0)
        {
            alert("전화로 문의해 주시면 감사하겠습니다.");
            return;
        }

        if($(".sit_opt_list").size() < 1)
        {
            alert("상품의 선택옵션을 선택해 주십시오.");
            return;
        }

        if (act == "direct_buy") {
            f.sw_direct.value = 1;
        } else {
            f.sw_direct.value = 0;
        }

        var val, result = true;
        $("input[name='ct_qty[]']").each(function() {
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

        if(!result)
            return;

        f.submit();
    }

    function addition_write(element_id)
    {
        if (element_id.style.display == 'none') { // 안보이면 보이게 하고
            element_id.style.display = 'block';
        } else { // 보이면 안보이게 하고
            element_id.style.display = 'none';
        }
    }

    var save_use_id = null;
    function use_menu(id)
    {
        if (save_use_id != null)
            document.getElementById(save_use_id).style.display = "none";
        menu(id);
        save_use_id = id;
    }

    var save_qa_id = null;
    function qa_menu(id)
    {
        if (save_qa_id != null)
            document.getElementById(save_qa_id).style.display = "none";
        menu(id);
        save_qa_id = id;
    }
    </script>

    <!--[if lte IE 6]>
    <script>
    // 이미지 등비율 리사이징
    $(window).load(function() {
        view_image_resize();
    });

    function view_image_resize()
    {
        var $img = $("#sit_inf_explan img");
        var img_wrap = $("#sit_inf_explan").width();
        var win_width = $(window).width() - 35;
        var res_width = 0;

        if(img_wrap < win_width)
            res_width = img_wrap;
        else
            res_width = win_width;

        $img.each(function() {
            var img_width = $(this).width();
            var img_height = $(this).height();
            var this_width = $(this).data("width");
            var this_height = $(this).data("height");

            if(this_width == undefined) {
                $(this).data("width", img_width); // 원래 이미지 사이즈
                $(this).data("height", img_height);
                this_width = img_width;
                this_height = img_height;
            }

            if(this_width > res_width) {
                $(this).width(res_width);
                var res_height = Math.round(res_width * $(this).data("height") / $(this).data("width"));
                $(this).height(res_height);
            } else {
                $(this).width(this_width);
                $(this).height(this_height);
            }
        });
    }
    </script>
    <![endif]-->

</div><!-- #sit 끝 -->

<?php
// 하단 HTML
echo stripslashes($it['it_mobile_tail_html']);

include_once(G4_MSHOP_PATH.'/_tail.php');
?>
