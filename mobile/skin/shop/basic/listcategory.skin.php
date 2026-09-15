<?php
if (!defined('_GNUBOARD_')) exit;

$category_groups = get_shop_category_menu_groups($ca_id);
if ($category_groups) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
?>
<aside id="sct_ct_1" class="sct_ct">
    <h2>현재 상품 분류와 관련된 분류</h2>
    <?php foreach ($category_groups as $category_group) { ?>
    <ul aria-label="<?php echo $category_group['label']; ?>">
        <?php foreach ($category_group['categories'] as $row) {
            $row2 = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_item_table']} where (ca_id like '{$row['ca_id']}%' or ca_id2 like '{$row['ca_id']}%' or ca_id3 like '{$row['ca_id']}%') and it_use = '1' ");
            $current_attr = '';
            if ($row['ca_id'] === $ca_id) {
                $current_attr = ' class="sct_ct_here" aria-current="page"';
            } elseif (strpos($ca_id, $row['ca_id']) === 0) {
                $current_attr = ' class="sct_ct_here sct_ct_parent"';
            }
            echo '<li><a'.$current_attr.' href="'.shop_category_url($row['ca_id']).'">'.get_text($row['ca_name']).' <span class="prd_cnt">'.$row2['cnt'].'</span></a></li>';
        } ?>
    </ul>
    <?php } ?>
</aside>
<?php }
