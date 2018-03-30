<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$connect_skin_url.'/style.css">', 0);
?>

<!-- 현재접속자 목록 시작 { -->
<div id="current_connect">
    <ul>
    <?php
    for ($i=0; $i<count($list); $i++) {
        //$location = conv_content($list[$i]['lo_location'], 0);
        $location = $list[$i]['lo_location'];
        // 최고관리자에게만 허용
        // 이 조건문은 가능한 변경하지 마십시오.
        if ($list[$i]['lo_url'] && $is_admin == 'super') $display_location = "<a href=\"".$list[$i]['lo_url']."\">".$location."</a>";
        else $display_location = $location;
        
        $classes = array();
        if( $i && ($i % 4 == 0) ){
            $classes[] = 'box_clear';
        }
    ?>
        <li class="<?php echo implode(' ', $classes); ?>">
            <div class="inner">
                <span class="crt_num"><?php echo $list[$i]['num'] ?></span>
                <span class="crt_name"><?php echo get_member_profile_img($list[$i]['mb_id']); ?><br><?php echo $list[$i]['name'] ?></span>
                <span class="crt_lct"><?php echo $display_location ?></span>
            </div>
        </li>
    <?php
    }
    if ($i == 0)
        echo "<li class=\"empty_li\">현재 접속자가 없습니다.</li>";
    ?>
    </ul>
</div>
<!-- } 현재접속자 목록 끝 -->