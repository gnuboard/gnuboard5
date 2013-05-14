<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
// $list_mod 가로 나열 수

for ($i=1; $row=sql_fetch_array($result); $i++)
{
    $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];
    if ($list_mod >= 2) { // 1줄 이미지 : 2개 이상
        if ($i%$list_mod == 0) $sct_last = 'sct_last'; // 줄 마지막
        else if ($i%$list_mod == 1) $sct_last = 'sct_clear'; // 줄 첫번째
        else $sct_last = '';
    } else { // 1줄 이미지 : 1개
        $sct_last = 'sct_clear';
    }

    $sns_title = get_text($row['it_name']).' | '.get_text($config['cf_title']);
    $sns_url  = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];

    if ($i == 1) echo '<ul class="sct sct_13">';
?>
    <li class="sct_li <?php echo $sct_last; ?>">
        <a href="<?php echo $href; ?>" class="sct_a">
            <span class="sct_img"><?php echo get_it_image($row['it_id'], $img_width, $img_height); ?></span>
            <b><?php echo stripslashes($row['it_name']); ?></b>
            <?php if ($row['it_cust_amount'] && !$row['it_gallery']) { ?>
            <s><?php echo display_amount($row['it_cust_amount']); ?></s>
            <?php } ?>
            <?php if (!$row['it_gallery']) { // 전시 상품이 아닐 때 ?>
            <span class="sct_cost"><?php echo display_amount(get_amount($row), $row['it_tel_inq']); ?></span>
            <?php } ?>
            <span class="sct_icon">
                <?php echo display_item_icon($row); // 이미지 아이콘?>
            </span>
        </a>
        <div class="sct_rel">
            <?php echo display_relation_item($row['it_id'], 70, 70, 5); // 관련 상품 ?>
        </div>
        <div class="sct_sns">
            <?php echo get_sns_share_link('facebook', $sns_url, $sns_title, G4_URL.'/img/shop/sns_fb.png'); ?>
            <?php echo get_sns_share_link('twitter', $sns_url, $sns_title, G4_URL.'/img/shop/sns_twt.png'); ?>
            <?php echo get_sns_share_link('googleplus', $sns_url, $sns_title, G4_URL.'/img/shop/sns_goo.png'); ?>
        </div>
    </li>
<?php
}
if ($i > 1) echo '</ul>';

if($i == 1) echo '<p class="sct_noitem">등록된 상품이 없습니다.</p>';
?>
