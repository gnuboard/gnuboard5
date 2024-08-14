<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$str = '';
$exists = false;

$sc_id_len = strlen($sc_id);
$len2 = $sc_id_len + 2;
$len4 = $sc_id_len + 4;

$sql = " select sc_id, sc_name from {$g5['g5_shop_category_table']} where sc_id like '$sc_id%' and length(sc_id) = $len2 and sc_use = '1' order by sc_order, sc_id ";
$result = sql_query($sql);
while ($row=sql_fetch_array($result)) {

    $row2 = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_item_table']} where (sc_id like '{$row['sc_id']}%' or sc_id2 like '{$row['sc_id']}%' or sc_id3 like '{$row['sc_id']}%') and it_use = '1'  ");

    $str .= '<li><a href="'.shop_category_url($row['sc_id']).'">'.$row['sc_name'].' ('.$row2['cnt'].')</a></li>';
    $exists = true;
}

if ($exists) {

    // add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
    add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 상품분류 1 시작 { -->
<aside id="sct_ct_1" class="sct_ct">
    <h2>현재 상품 분류와 관련된 분류</h2>
    <ul>
        <?php echo $str; ?>
    </ul>
</aside>
<!-- } 상품분류 1 끝 -->

<?php }