<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$str = '';
$exists = false;

$depth2_ca_id = substr($ca_id, 0, 2);

$sql = " select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id like '${depth2_ca_id}%' and length(ca_id) = 4 and ca_use = '1' order by ca_order, ca_id ";
$result = sql_query($sql);
while ($row=sql_fetch_array($result)) {
    if (preg_match("/^{$row['ca_id']}/", $ca_id))
        $sct_ct_here = 'sct_ct_here';
    else
        $sct_ct_here = '';
    $str .= '<li><a href="./list.php?ca_id='.$row['ca_id'].'" class="'.$sct_ct_here.'">'.$row['ca_name'].'</a></li>';
    $exists = true;
}

if ($exists) {

    // add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
    add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_CSS_URL.'/style.css">', 0);
?>

<!-- 상품분류 3 시작 { -->
<aside id="sct_ct_3" class="sct_ct">
    <h2>현재 상품 분류와 관련된 분류</h2>
    <ul>
        <?php echo $str; ?>
    </ul>
</aside>
<!-- } 상품분류 3 끝 -->

<?php } ?>