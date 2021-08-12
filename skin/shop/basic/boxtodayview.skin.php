<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$tv_datas = get_view_today_items(true);

$tv_div['top'] = 0;
$tv_div['img_width'] = 65;
$tv_div['img_height'] = 65;
$tv_div['img_length'] = 10; // 한번에 보여줄 이미지 수

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 오늘 본 상품 시작 { -->
<div id="stv">
    <h2 class="s_h2">오늘 본 상품 <span><?php echo get_view_today_items_count(); ?></span></h2>

    <?php if ($tv_datas) { // 오늘 본 상품이 1개라도 있을 때 ?>
    <?php
    $tv_tot_count = 0;
    $k = 0;
    $i = 1;
    foreach($tv_datas as $rowx)
    {
        if(!$rowx['it_id'])
            continue;
        
        $tv_it_id = $rowx['it_id'];

        if ($tv_tot_count % $tv_div['img_length'] == 0) $k++;

        $it_name = get_text($rowx['it_name']);
        $img = get_it_image($tv_it_id, $tv_div['img_width'], $tv_div['img_height'], $tv_it_id, '', $it_name);
        $it_price = get_price($rowx);
        $print_price = is_int($it_price) ? number_format($it_price) : $it_price;

        if ($tv_tot_count == 0) echo '<ul id="stv_ul">'.PHP_EOL;
        echo '<li class="stv_item c'.$k.'">'.PHP_EOL;
        echo '<div class="prd_img">';
        echo $img;
        echo '</div>'.PHP_EOL;
		echo '<div class="prd_cnt">';
        echo '<span class="prd_name">';
        echo cut_str($it_name, 10, '').PHP_EOL;
        echo '</span>';
        echo '<span class="prd_cost">';
        echo $print_price.PHP_EOL;
        echo '</span>'.PHP_EOL;
		echo '</div>'.PHP_EOL;
        echo '</li>'.PHP_EOL;

        $tv_tot_count++;
        $i++;
    }
    if ($tv_tot_count > 0) echo '</ul>'.PHP_EOL;
    ?>
    <div id="stv_btn"></div>
    <span id="stv_pg"></span>

    <script>
    $(function() {
        var itemQty = <?php echo $tv_tot_count; ?>; // 총 아이템 수량
        var itemShow = <?php echo $tv_div['img_length']; ?>; // 한번에 보여줄 아이템 수량
        if (itemQty > itemShow)
        {
            $('#stv_btn').append('<button type="button" id="up"><i class="fa fa-angle-left" aria-hidden="true"></i> 이전</button><button type="button" id="down">다음 <i class="fa fa-angle-right" aria-hidden="true"></i></button>');
        }
        var Flag = 1; // 페이지
        var EOFlag = parseInt(<?php echo $i-1; ?>/itemShow); // 전체 리스트를 3(한 번에 보여줄 값)으로 나눠 페이지 최댓값을 구하고
        var itemRest = parseInt(<?php echo $i-1; ?>%itemShow); // 나머지 값을 구한 후
        if (itemRest > 0) // 나머지 값이 있다면
        {
            EOFlag++; // 페이지 최댓값을 1 증가시킨다.
        }
        $('.c'+Flag).css('display','block');
        $('#stv_pg').text(Flag+'/'+EOFlag); // 페이지 초기 출력값
        $('#up').click(function() {
            if (Flag == 1)
            {
                alert('목록의 처음입니다.');
            } else {
                Flag--;
                $('.c'+Flag).css('display','block');
                $('.c'+(Flag+1)).css('display','none');
            }
            $('#stv_pg').text(Flag+'/'+EOFlag); // 페이지 값 재설정
        })
        $('#down').click(function() {
            if (Flag == EOFlag)
            {
                alert('더 이상 목록이 없습니다.');
            } else {
                Flag++;
                $('.c'+Flag).css('display','block');
                $('.c'+(Flag-1)).css('display','none');
            }
            $('#stv_pg').text(Flag+'/'+EOFlag); // 페이지 값 재설정
        });
    });
    </script>

    <?php } else { // 오늘 본 상품이 없을 때 ?>

    <p class="li_empty">없음</p>

    <?php } ?>
</div>

<script src="<?php echo G5_JS_URL ?>/scroll_oldie.js"></script>
<!-- } 오늘 본 상품 끝 -->