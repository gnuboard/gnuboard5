<?php
$sub_menu = '400010';
include_once('./_common.php');

$max_limit = 7; // 몇행 출력할 것인지?

$g5['title'] = ' 쇼핑몰현황';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$pg_anchor = '<ul class="anchor sidx_anchor">
<li><a href="#anc_sidx_ord">주문현황</a></li>
<li><a href="#anc_sidx_rdy">입금완료미배송내역</a></li>
<li><a href="#anc_sidx_wait">미입금주문내역</a></li>
<li><a href="#anc_sidx_ps">사용후기</a></li>
<li><a href="#anc_sidx_qna">상품문의</a></li>
</ul>';

// 주문상태에 따른 합계 금액
function get_order_status_sum($status)
{
    global $g5;

    $sql = " select count(*) as cnt,
                    sum(od_cart_price + od_send_cost + od_send_cost2 - od_cancel_price) as price
                from {$g5['g5_shop_order_table']}
                where od_status = '$status' ";
    $row = sql_fetch($sql);

    $info = array();
    $info['count'] = (int)$row['cnt'];
    $info['price'] = (int)$row['price'];
    $info['href'] = './orderlist.php?od_status='.urlencode($status);

    return $info;
}

// 일자별 주문 합계 금액
function get_order_date_sum($date)
{
    global $g5;

    $sql = " select sum(od_cart_price + od_send_cost + od_send_cost2) as orderprice,
                    sum(od_cancel_price) as cancelprice
                from {$g5['g5_shop_order_table']}
                where SUBSTRING(od_time, 1, 10) = '$date' ";
    $row = sql_fetch($sql);

    $info = array();
    $info['order'] = (int)$row['orderprice'];
    $info['cancel'] = (int)$row['cancelprice'];

    return $info;
}

// 일자별 결제수단 주문 합계 금액
function get_order_settle_sum($date)
{
    global $g5, $default;

    $case = array('신용카드', '계좌이체', '가상계좌', '무통장', '휴대폰');
    $info = array();

    // 결제수단별 합계
    foreach($case as $val)
    {
        $sql = " select sum(od_cart_price + od_send_cost + od_send_cost2 - od_receipt_point - od_cart_coupon - od_coupon - od_send_coupon) as price,
                        count(*) as cnt
                    from {$g5['g5_shop_order_table']}
                    where SUBSTRING(od_time, 1, 10) = '$date'
                      and od_settle_case = '$val' ";
        $row = sql_fetch($sql);

        $info[$val]['price'] = (int)$row['price'];
        $info[$val]['count'] = (int)$row['cnt'];
    }

    // 포인트 합계
    $sql = " select sum(od_receipt_point) as price,
                    count(*) as cnt
                from {$g5['g5_shop_order_table']}
                where SUBSTRING(od_time, 1, 10) = '$date'
                  and od_receipt_point > 0 ";
    $row = sql_fetch($sql);
    $info['포인트']['price'] = (int)$row['price'];
    $info['포인트']['count'] = (int)$row['cnt'];

    // 쿠폰 합계
    $sql = " select sum(od_cart_coupon + od_coupon + od_send_coupon) as price,
                    count(*) as cnt
                from {$g5['g5_shop_order_table']}
                where SUBSTRING(od_time, 1, 10) = '$date'
                  and ( od_cart_coupon > 0 or od_coupon > 0 or od_send_coupon > 0 ) ";
    $row = sql_fetch($sql);
    $info['쿠폰']['price'] = (int)$row['price'];
    $info['쿠폰']['count'] = (int)$row['cnt'];

    return $info;
}

function get_max_value($arr)
{
    foreach($arr as $key => $val)
    {
        if(is_array($val))
        {
            $arr[$key] = get_max_value($val);
        }
    }

    sort($arr);

    return array_pop($arr);
}
?>
<?php if (! auth_check_menu($auth, '400400', 'r', true)) { ?>
<div class="sidx">
    <section id="anc_sidx_ord">
        <h2>주문현황</h2>
        <?php echo $pg_anchor; ?>

        <?php
        $arr_order = array();
        $x_val = array();
        for($i=6; $i>=0; $i--) {
            $date = date('Y-m-d', strtotime('-'.$i.' days', G5_SERVER_TIME));

            $x_val[] = $date;
            $arr_order[] = get_order_date_sum($date);
        }

        $max_y = get_max_value($arr_order);
        $max_y = ceil(($max_y) / 1000) * 1000;
        $y_val = array();
        $y_val[] = $max_y;

        for($i=4; $i>=1; $i--) {
            $y_val[] = $max_y * (($i * 2) / 10);
        }

        $max_height = 230;
        $h_val = array();
        $js_val = array();
        $offset = 10; // 금액이 상대적으로 작아 높이가 0일 때 기본 높이로 사용
        foreach($arr_order as $val) {
            if($val['order'] > 0)
                $h1 = intval(($max_height * $val['order']) / $max_y) + $offset;
            else
                $h1 = 0;

            if($val['cancel'] > 0)
                $h2 = intval(($max_height * $val['cancel']) / $max_y) + $offset;
            else
                $h2 = 0 ;

            $h_val['order'][] = $h1;
            $h_val['cancel'][] = $h2;
        }
        ?>

        <div id="sidx_graph">
            <ul id="sidx_graph_price">
                <?php
                foreach($y_val as $val) {
                ?>
                <li><span></span><?php echo number_format($val); ?></li>
                <?php
                }
                ?>
            </ul>
            <ul id="sidx_graph_area">
                <?php
                for($i=0; $i<count($x_val); $i++) {
                    $order_title = date("n월 j일", strtotime($x_val[$i])).' 주문: '.display_price($arr_order[$i]['order']);
                    $cancel_title = date("n월 j일", strtotime($x_val[$i])).' 취소: '.display_price($arr_order[$i]['cancel']);
                    $k = 10 - $i;
                    $li_bg = 'bg'.($i%2);
                ?>
                <li class="<?php echo $li_bg; ?>" style="z-index:<?php echo $k; ?>">
                    <div class="graph order" title="<?php echo $order_title; ?>">

                    </div>
                    <div class="graph cancel" title="<?php echo $cancel_title; ?>">

                    </div>
                </li>
                <?php
                }
                ?>
            </ul>
            <ul id="sidx_graph_date">
                <?php
                foreach($x_val as $val) {
                ?>
                <li><span></span><?php echo substr($val, 5, 5).' ('.get_yoil($val).')'; ?></li>
                <?php
                }
                ?>
            </ul>
            <div id="sidx_graph_legend">
                <span id="legend_order"></span> 주문
                <span id="legend_cancel"></span> 취소
            </div>
        </div>
    </section>

    <div id="sidx_stat">
        <section id="anc_sidx_act">
            <h2>처리할 주문</h2>
            <?php echo $pg_anchor; ?>

            <div id="sidx_take_act" class="tbl_head01 tbl_wrap">
                <table>
                <thead>
                <tr>
                    <th scope="col" class="td_mng">상태변경</th>
                    <th scope="col">건수</th>
                    <th scope="col">금액</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <?php
                    $info = get_order_status_sum('주문');
                    ?>
                    <th scope="row">주문 -&gt; 입금</th>
                    <td class="td_num"><a href="<?php echo $info['href']; ?>"><?php echo number_format($info['count']); ?></a></td>
                    <td class="td_price"><a href="<?php echo $info['href']; ?>"><?php echo number_format($info['price']); ?></a></td>
                </tr>
                <tr>
                    <?php
                    $info = get_order_status_sum('입금');
                    ?>
                    <th scope="row">입금 -&gt; 준비</th>
                    <td class="td_num"><a href="<?php echo $info['href']; ?>"><?php echo number_format($info['count']); ?></a></td>
                    <td class="td_price"><a href="<?php echo $info['href']; ?>"><?php echo number_format($info['price']); ?></a></td>
                </tr>
                <tr>
                    <?php
                    $info = get_order_status_sum('준비');
                    ?>
                    <th scope="row">준비 -&gt; 배송</th>
                    <td class="td_num"><a href="<?php echo $info['href']; ?>"><?php echo number_format($info['count']); ?></a></td>
                    <td class="td_price"><a href="<?php echo $info['href']; ?>"><?php echo number_format($info['price']); ?></a></td>
                </tr>
                <tr>
                    <?php
                    $info = get_order_status_sum('배송');
                    ?>
                    <th scope="row">배송 -&gt; 완료</th>
                    <td class="td_num"><a href="<?php echo $info['href']; ?>"><?php echo number_format($info['count']); ?></a></td>
                    <td class="td_price"><a href="<?php echo $info['href']; ?>"><?php echo number_format($info['price']); ?></a></td>
                </tr>
                </tbody>
                </table>
            </div>
        </section>

        <section id="anc_sidx_stock">
            <h2>재고현황</h2>
            <?php echo $pg_anchor; ?>

            <?php
            // 재고부족 상품
            $item_noti = 0;
            $sql = " select count(*) as cnt
                        from {$g5['g5_shop_item_table']}
                        where it_use = '1'
                          and it_option_subject = ''
                          and it_stock_qty <= it_noti_qty ";
            $row = sql_fetch($sql);
            $item_noti = (int)$row['cnt'];

            // 재고부족 옵션
            $option_noti = 0;
            $sql = " select count(*) as cnt
                        from {$g5['g5_shop_item_option_table']}
                        where io_use = '1'
                          and io_stock_qty <= io_noti_qty ";
            $row = sql_fetch($sql);
            $option_noti = (int)$row['cnt'];

            // SMS 정보
            $userinfo = array('coin'=>0);
            if ($config['cf_sms_use'] && $config['cf_icode_id'] && $config['cf_icode_pw']) {
                $userinfo = get_icode_userinfo($config['cf_icode_id'], $config['cf_icode_pw']);
            }
            ?>
            <div id="sidx_stock" class="tbl_head01 tbl_wrap">
                <table>
                <thead>
                <tr>
                    <th scope="col">재고부족 상품</th>
                    <th scope="col">재고부족 옵션</th>
                    <th scope="col">SMS 잔여금액</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="td_num2"><a href="./itemstocklist.php"><?php echo number_format($item_noti); ?></a></td>
                    <td class="td_num2"><a href="./optionstocklist.php"><?php echo number_format($option_noti); ?></a></td>
                    <td class="td_price"><?php echo display_price(intval($userinfo['coin'])); ?></td>
                </tr>
                </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<section id="anc_sidx_settle">
    <h2>결제수단별 주문현황</h2>
    <?php echo $pg_anchor; ?>

    <div id="sidx_settle" class="tbl_head01 tbl_wrap">
        <table>
        <thead>
        <tr>
            <th scope="col" rowspan="2">구분</th>
            <?php
            $term = 3;
            $info = array();
            $info_key = array();
            for($i=($term - 1); $i>=0; $i--) {
                $date = date("Y-m-d", strtotime('-'.$i.' days', G5_SERVER_TIME));
                $info[$date] = get_order_settle_sum($date);

                $day = substr($date, 5, 5).' ('.get_yoil($date).')';
                $info_key[] = $date;
            ?>
            <th scope="col" colspan="2"><?php echo $day; ?></th>
            <?php } ?>
        </tr>
        <tr>
            <?php
            for($i=0; $i<$term; $i++) {
            ?>
            <th scope="col">건수</th>
            <th scope="col">금액</th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php
        $case = array('신용카드', '계좌이체', '가상계좌', '무통장', '휴대폰', '포인트', '쿠폰');
        
        $val_cnt = 0;
        foreach($case as $val)
        {
            $val_cnt++;
        ?>
        <tr>
            <th scope="row" id="th_val_<?php echo $val_cnt; ?>" class="td_category"><?php echo $val; ?></th>
            <?php
            foreach($info_key as $date)
            {
            ?>
            <td><?php echo number_format($info[$date][$val]['count']); ?></td>
            <td><?php echo number_format($info[$date][$val]['price']); ?></td>
            <?php
            }
            ?>
        </tr>
        <?php
        }
        ?>
        </tbody>
        </table>
    </div>
</section>

<script>
jQuery(function($) {
    graph_draw();

    $("#sidx_graph_area div").hover(
        function() {
            if($(this).is(":animated"))
                return false;

            var title = $(this).attr("title");
            if(title && $(this).data("title") == undefined)
                $(this).data("title", title);
            var left = parseInt($(this).css("left")) + 10;
            var bottom = $(this).height() + 5;

            $(this)
                .attr("title", "")
                .append("<div id=\"price_tooltip\"><div></div></div>");
            $("#price_tooltip")
                .find("div")
                .html(title)
                .end()
//                .css({ left: left+"px", bottom: bottom+"px" })
                .show(200);
        },
        function() {
            if($(this).is(":animated"))
                return false;

            $(this).attr("title", $(this).data("title"));
            $("#price_tooltip").remove();
        }
    );
});

function graph_draw()
{
    var g_h1 = new Array("<?php echo implode('", "', $h_val['order']); ?>");
    var g_h2 = new Array("<?php echo implode('", "', $h_val['cancel']); ?>");
    var duration = 600;

    var $el = $("#sidx_graph_area li");
    var h1, h2;
    var $g1, $g2;

    $el.each(function(index) {
        h1 = g_h1[index];
        h2 = g_h2[index];

        $g1 = $(this).find(".order");
        $g2 = $(this).find(".cancel");

        $g1.animate({ height: h1+"px" }, duration);
        $g2.animate({ height: h2+"px" }, duration);
    });
}
</script>

<?php } //endif ?>
<?php if ($is_admin === 'super') { ?>
<div class="sidx sidx_cs">
    <section id="anc_sidx_oneq">
        <h2>1:1문의</h2>
        <?php echo $pg_anchor; ?>

        <div class="ul_01 ul_wrap">
            <ul>
                <?php
                $sql = " select * from {$g5['qa_content_table']}
                          where qa_status = '0'
                            and qa_type = '0'
                          order by qa_num
                          limit $max_limit ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++)
                {
                    $sql1 = " select * from {$g5['member_table']} where mb_id = '{$row['mb_id']}' ";
                    $row1 = sql_fetch($sql1);

                    $name = get_sideview($row['mb_id'], get_text($row['qa_name']), $row1['mb_email'], $row1['mb_homepage']);
                ?>
                <li>
                    <span class="oneq_cate oneq_span"><?php echo get_text($row['qa_category']); ?></span>
                    <a href="<?php echo G5_BBS_URL; ?>/qaview.php?qa_id=<?php echo $row['qa_id']; ?>" target="_blank" class="oneq_link"><?php echo conv_subject($row['qa_subject'],40); ?></a>
                    <?php echo $name; ?>
                </li>
                <?php
                }

                if ($i == 0)
                    echo '<li class="empty_list">자료가 없습니다.</li>';
                ?>
            </ul>
        </div>

        <div class="btn_list03 btn_list">
            <a href="<?php echo G5_BBS_URL; ?>/qalist.php" target="_blank">1:1문의 더보기</a>
        </div>
    </section>

    <section id="anc_sidx_qna">
        <h2>상품문의</h2>
        <?php echo $pg_anchor; ?>

        <div class="ul_01 ul_wrap">
            <ul>
                <?php
                $sql = " select * from {$g5['g5_shop_item_qa_table']}
                          where iq_answer = ''
                          order by iq_id desc
                          limit $max_limit ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++)
                {
                    $sql1 = " select * from {$g5['member_table']} where mb_id = '{$row['mb_id']}' ";
                    $row1 = sql_fetch($sql1);

                    $name = get_sideview($row['mb_id'], get_text($row['iq_name']), $row1['mb_email'], $row1['mb_homepage']);
                ?>
                <li>
                    <a href="./itemqaform.php?w=u&amp;iq_id=<?php echo $row['iq_id']; ?>" class="qna_link"><?php echo conv_subject($row['iq_subject'],40); ?></a>
                    <?php echo $name; ?>
                </li>
                <?php
                }

                if ($i == 0)
                    echo '<li class="empty_list">자료가 없습니다.</li>';
                ?>
            </ul>
        </div>

        <div class="btn_list03 btn_list">
            <a href="./itemqalist.php?sort1=iq_answer&amp;sort2=asc">상품문의 더보기</a>
        </div>
    </section>

    <section id="anc_sidx_ps">
        <h2>사용후기</h2>
        <?php echo $pg_anchor; ?>

        <div class="ul_01 ul_wrap">
            <ul>
            <?php
            $sql = " select * from {$g5['g5_shop_item_use_table']}
                      where is_confirm = 0
                      order by is_id desc
                      limit $max_limit ";
            $result = sql_query($sql);
            for ($i=0; $row=sql_fetch_array($result); $i++)
            {
                $sql1 = " select * from {$g5['member_table']} where mb_id = '{$row['mb_id']}' ";
                $row1 = sql_fetch($sql1);

                $name = get_sideview($row['mb_id'], get_text($row['is_name']), $row1['mb_email'], $row1['mb_homepage']);
            ?>
                <li>
                    <a href="./itemuseform.php?w=u&amp;is_id=<?php echo $row['is_id']; ?>" class="ps_link"><?php echo conv_subject($row['is_subject'],40); ?></a>
                    <?php echo $name; ?>
                </li>
            <?php
            }
            if ($i == 0) echo '<li class="empty_list">자료가 없습니다.</li>';
            ?>
            </ul>
        </div>

        <div class="btn_list03 btn_list">
            <a href="./itemuselist.php?sort1=is_confirm&amp;sort2=asc">사용후기 더보기</a>
        </div>
    </section>
</div>
<?php
}   //end if
include_once (G5_ADMIN_PATH.'/admin.tail.php');