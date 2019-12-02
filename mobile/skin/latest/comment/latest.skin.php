<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
?>

<div class="cm_lt">
    <h2 class="cm_lt_title"><a href="<?php echo get_pretty_url($bo_table); ?>">최신댓글</a></h2>
    <ul>
    <?php
    $count = count($list);
    for ($i=0; $i<$count; $i++) {
    ?>
        <li>
            <span class="cm_lt_nick"><?php echo get_member_profile_img($member['mb_id']); ?></span>
            <div class="cm_lt_info">
            	<a href="<?php echo $list[$i]['href']; ?>" class="over"><?php echo $list[$i]['subject']; ?></a>
				<br>
				<span class="lt_nick"><?php echo $list[$i]['name'] ?></span>
            	<span class="lt_date"><?php echo $list[$i]['datetime2'] ?></span>              
            </div>
        </li>
    <?php
    }

    if($i ==0)
        echo '<li class="empty_li">게시물이 없습니다.</li>'.PHP_EOL;
    ?>
    </ul>
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_more"><span class="sound_only"><?php echo $bo_subject ?></span>더보기</a>

</div>
