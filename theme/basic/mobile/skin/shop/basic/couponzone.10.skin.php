<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
?>

<section class="couponzone_list">
    <h2>다운로드 쿠폰</h2>
    <p><?php echo $default['de_admin_company_name']; ?> 회원이시라면 쿠폰 다운로드 후 바로 사용하실 수 있습니다.</p>

    <?php
    $sql = " select * $sql_common and cz_type = '0' $sql_order ";
    $result = sql_query($sql);

    $coupon = '';

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if(!$row['cz_file'])
            continue;

        $img_file = G5_DATA_PATH.'/coupon/'.$row['cz_file'];
        if(!is_file($img_file))
            continue;

        $subj = get_text($row['cz_subject']);

        switch($row['cp_method']) {
            case '0':
                $sql3 = " select it_id, it_name from {$g5['g5_shop_item_table']} where it_id = '{$row['cp_target']}' ";
                $row3 = sql_fetch($sql3);
                $cp_target = '<a href="./item.php?it_id='.$row3['it_id'].'">'.get_text($row3['it_name']).'</a>';
                break;
            case '1':
                $sql3 = " select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$row['cp_target']}' ";
                $row3 = sql_fetch($sql3);
                $cp_target = '<a href="./list.php?ca_id='.$row3['ca_id'].'">'.get_text($row3['ca_name']).'</a>';
                break;
            case '2':
                $cp_target = '주문금액할인';
                break;
            case '3':
                $cp_target = '배송비할인';
                break;
        }

        // 다운로드 쿠폰인지
        $disabled = '';
        if(is_coupon_downloaded($member['mb_id'], $row['cz_id']))
            $disabled = ' disabled';

        $coupon .= '<li><div class="coupon_wr">'.PHP_EOL;
        $coupon .= '<div class="coupon_img"><img src="'.str_replace(G5_PATH, G5_URL, $img_file).'" alt="'.$subj.'"></div>'.PHP_EOL;
        $coupon .= '<div class="coupon_tit">'.$subj.'</div>'.PHP_EOL;
        $coupon .= '<div class="coupon_date">기한 : 다운로드 후 '.number_format($row['cz_period']).'일</div>'.PHP_EOL;
        $coupon .= '<div class="coupon_target">적용 : '.$cp_target.'</div>'.PHP_EOL;
        $coupon .= '<div class="coupon_btn"><button type="button" class="coupon_download btn02'.$disabled.'" data-cid="'.$row['cz_id'].'">쿠폰다운로드</button></div>'.PHP_EOL;
        $coupon .= '</div></li>'.PHP_EOL;
    }

    if($coupon)
        echo '<ul>'.PHP_EOL.$coupon.'</ul>'.PHP_EOL;
    else
        echo '<p class="no_coupon">사용할 수 있는 쿠폰이 없습니다.</p>';
    ?>
</section>

<section class="couponzone_list" id="coupon_point">
    <h2>포인트 쿠폰</h2>
    <p>보유하신 <?php echo $default['de_admin_company_name']; ?> 회원 포인트를 쿠폰으로 교환하실 수 있습니다.</p>

    <?php
    $sql = " select * $sql_common and cz_type = '1' $sql_order ";
    $result = sql_query($sql);

    $coupon = '';

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if(!$row['cz_file'])
            continue;

        $img_file = G5_DATA_PATH.'/coupon/'.$row['cz_file'];
        if(!is_file($img_file))
            continue;

        $subj = get_text($row['cz_subject']);

        switch($row['cp_method']) {
            case '0':
                $sql3 = " select it_id, it_name from {$g5['g5_shop_item_table']} where it_id = '{$row['cp_target']}' ";
                $row3 = sql_fetch($sql3);
                $cp_target = '<a href="./item.php?it_id='.$row3['it_id'].'">'.get_text($row3['it_name']).'</a>';
                break;
            case '1':
                $sql3 = " select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$row['cp_target']}' ";
                $row3 = sql_fetch($sql3);
                $cp_target = '<a href="./list.php?ca_id='.$row3['ca_id'].'">'.get_text($row3['ca_name']).'</a>';
                break;
            case '2':
                $cp_target = '주문금액할인';
                break;
            case '3':
                $cp_target = '배송비할인';
                break;
        }

        // 다운로드 쿠폰인지
        $disabled = '';
        if(is_coupon_downloaded($member['mb_id'], $row['cz_id']))
            $disabled = ' disabled';

        $coupon .= '<li><div class="coupon_wr">'.PHP_EOL;
        $coupon .= '<div class="coupon_img"><img src="'.str_replace(G5_PATH, G5_URL, $img_file).'" alt="'.$subj.'"></div>'.PHP_EOL;
        $coupon .= '<div class="coupon_tit">'.$subj.'</div>'.PHP_EOL;
        $coupon .= '<div class="coupon_date">기한 : 다운로드 후 '.number_format($row['cz_period']).'일</div>'.PHP_EOL;
        $coupon .= '<div class="coupon_target">적용 : '.$cp_target.'</div>'.PHP_EOL;
        $coupon .= '<div class="coupon_point">포인트 '.number_format($row['cz_point']).'점 차감</div>'.PHP_EOL;
        $coupon .= '<div class="coupon_btn"><button type="button" class="coupon_download btn02'.$disabled.'" data-cid="'.$row['cz_id'].'">쿠폰다운로드</button></div>'.PHP_EOL;
        $coupon .= '</div></li>'.PHP_EOL;
    }

    if($coupon)
        echo '<ul>'.PHP_EOL.$coupon.'</ul>'.PHP_EOL;
    else
        echo '<p class="no_coupon">사용할 수 있는 쿠폰이 없습니다.</p>';
    ?>
</section>