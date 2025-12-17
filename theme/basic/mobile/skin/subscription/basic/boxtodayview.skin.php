<?php
$tv_idx = get_session("ss_tv_idx");

$tv_div['img_width'] = 100;
$tv_div['img_height'] = 100;
$tv_div['img_length'] = 5; // 한번에 보여줄 이미지 수

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 오늘 본 상품 시작 { -->
<div id="stv" class="op_area">
    <h2>오늘 본 상품<span id="stv_pg"></span></h2>
    <?php if ($tv_idx) { // 오늘 본 상품이 1개라도 있을 때 ?>
    
    <?php
        $tv_tot_count = 0;
        $k = 0;
        for ($i=1;$i<=$tv_idx;$i++)
        {
            $tv_it_idx = $tv_idx - ($i - 1);
            $tv_it_id = get_session("ss_tv[$tv_it_idx]");

            $rowx = get_shop_item($tv_it_id, true);
            if(!$rowx['it_id'])
                continue;

            if ($tv_tot_count % $tv_div['img_length'] == 0) $k++;

            $it_name = get_text($rowx['it_name']);
            $img = get_it_image($tv_it_id, $tv_div['img_width'], $tv_div['img_height'], $tv_it_id, '', $it_name);

        if ($tv_tot_count == 0) echo '<ul id="stv_ul">'.PHP_EOL;
        echo '<li class="stv_item c'.$k.'">'.PHP_EOL;
        echo '<div class="prd_img">';
        echo $img;
        echo '</div>'.PHP_EOL;
		echo '</li>'.PHP_EOL;

        $tv_tot_count++;
    	}
    	if ($tv_tot_count > 0) echo '</ul>'.PHP_EOL;
    ?>
    <div id="stv_btn"></div>

    <script>
    $(function() {
        var itemQty = <?php echo $tv_tot_count; ?>; // 총 아이템 수량
        var itemShow = <?php echo $tv_div['img_length']; ?>; // 한번에 보여줄 아이템 수량
        if (itemQty > itemShow)
        {
            $('#stv_btn').append('<button type="button" id="up"><i class="fa fa-chevron-left" aria-hidden="true"></i><span class="sound_only">이전</span></button><button type="button" id="down"><span class="sound_only">다음</span><i class="fa fa-chevron-right" aria-hidden="true"></i></button>');
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