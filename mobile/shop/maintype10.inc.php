<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
// $list_row 출력할 이미지 수

for ($i=1; $row=sql_fetch_array($result); $i++) {
    $href = G4_MSHOP_URL.'/item.php?it_id='.$row['it_id'];

    $sns_title = get_text($row['it_name']).' | '.get_text($config['cf_title']);
    $sns_send  = G4_MSHOP_URL.'/sns_send.php?url='.urlencode(G4_MSHOP_URL.'/item.php?it_id='.$row['it_id']);
    $sns_send .= '&amp;title='.urlencode(cut_str($sns_title, 100));

    if ($i == 1) echo '<ul class="sct sct_10">';
?>
    <li class="sct_li">
        <a href="<?php echo $href; ?>" class="sct_a">
            <span class="sct_img"><?php echo get_it_image($row['it_id'].'_m', $img_width, $img_height); ?></span>
            <b><?php echo stripslashes($row['it_name']); ?></b>
            <span class="sct_cost"><?php echo display_amount(get_amount($row), $row['it_tel_inq']); ?></span>
            <span class="sct_icon">
                <?php echo display_item_icon($row); // 이미지 아이콘?>
            </span>
        </a>
        <div class="sct_sns">
            <a href="<?php echo $sns_send; ?>&amp;sns=facebook" target="_blank"><img src="<?php echo G4_URL; ?>/img/shop/sns_fb.png" alt="페이스북에 공유"></a>
            <a href="<?php echo $sns_send; ?>&amp;sns=twitter" target="_blank"><img src="<?php echo G4_URL; ?>/img/shop/sns_twt.png" alt="트위터에 공유"></a>
            <a href="<?php echo $sns_send; ?>&amp;sns=google" target="_blank"><img src="<?php echo G4_URL; ?>/img/shop/sns_goo.png" alt="구글플러스에 공유"></a>
        </div>
    </li>
<?php
}
if ($i > 1) echo '</ul>';

if($i == 1) echo '<p class="sct_noitem">등록된 상품이 없습니다.</p>';
?>
