<?php
include_once('./_common.php');

$g4['title'] = '카테고리';
include_once(G4_PATH.'/head.sub.php');

$ca = $_GET['ca'];

if($ca) {
    $ca_len = strlen($ca) + 2;
    $sql_where = " where ca_id like '$ca%' and length(ca_id) = $ca_len ";
} else {
    $sql_where = " where length(ca_id) = '2' ";
}

$sql = " select ca_id, ca_name from {$g4['shop_category_table']}
          $sql_where
            and ca_use = '1'
          order by ca_id ";
$result = sql_query($sql);
?>

<?php
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($i == 0)
        echo '<ul>';

    $ca_href = G4_SHOP_URL.'/category.php?ca='.$row['ca_id'];
    $list_href = G4_SHOP_URL.'/list.php?ca_id='.$row['ca_id'];
?>
    <li>
        <span><a href="<?php echo $ca_href; ?>"><?php echo $row['ca_name']; ?></a></span>
        <span><a href="<?php echo $list_href; ?>" class="ca_list_view">상품보기</a></span>
    </li>
<?php
}

if($i > 0)
    echo '</ul>';

if($i ==0) {
    echo '<p>분류가 존재하지 않습니다.</p>';
}
?>

<script>
$(function() {
    $(".ca_list_view").click(function() {
        window.opener.location = $(this).attr("href");
        window.close();
        return false;
    });
});
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>