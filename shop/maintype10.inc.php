<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
// $list_mod 가로 나열 수
?>

<?php
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];
    if (($i+1)%$list_mod == 0) $sct_last = 'sct_last';
    else $sct_last = '';
    if ($i == 0) echo '<ul class="sct sct_10">';
?>
    <li class="sct_li <?php echo $sct_last; ?>">
        <a href="<?php echo $href; ?>" class="sct_a">
            <span class="sct_img"><?php echo get_it_image($row['it_id']."_s", $img_width, $img_height, '', $type); ?></span>
            <b><?php echo stripslashes($row['it_name']); ?></b>
            <?php echo display_amount(get_amount($row), $row['it_tel_inq']); ?>
            <span class="sct_icon">
            <?php // 이미지 아이콘
            echo display_item_icon($row);
            ?>
            </span>
        </a>
        <div class="sct_sns">
            <a href="#"><img src="<?php echo G4_URL; ?>/img/shop/sns_fb.png" alt="페이스북에 공유"></a>
            <a href="#"><img src="<?php echo G4_URL; ?>/img/shop/sns_twt.png" alt="트위터에 공유"></a>
            <a href="#"><img src="<?php echo G4_URL; ?>/img/shop/sns_goo.png" alt="구글플러스에 공유"></a>
        </div>
    </li>
<?php }
if ($i > 0) echo '</ul>';
?>
