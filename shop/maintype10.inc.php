<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
// $list_mod 가로 나열 수
?>

<?php
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];
    if (($i+1)%$list_mod == 0) $sidx_it_last = 'sidx_it_last';
    else $sidx_it_last = '';
    if ($i == 0) echo '<ul class="sidx_it sidx_it_10">';
?>
    <li class="sidx_it_li <?php echo $sidx_it_last; ?>">
        <a href="<?php echo $href; ?>" class="sidx_it_a">
            <span><?php echo get_it_image($row['it_id']."_s", $img_width, $img_height); ?></span>
            <b><?php echo stripslashes($row['it_name']); ?></b>
            <?php echo display_amount(get_amount($row), $row['it_tel_inq']); ?>
        </a>
        <div class="sidx_it_sns">
            <a href="#"><img src="<?php echo G4_URL; ?>/img/shop/sns_fb.png" alt="페이스북에 공유"></a>
            <a href="#"><img src="<?php echo G4_URL; ?>/img/shop/sns_twt.png" alt="트위터에 공유"></a>
            <a href="#"><img src="<?php echo G4_URL; ?>/img/shop/sns_goo.png" alt="구글플러스에 공유"></a>
        </div>
    </li>
<?php }
if ($i > 0) echo '</ul>';
?>
