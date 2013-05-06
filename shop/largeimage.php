<?php
define('_SHOP_', true);
include_once('./_common.php');

$it_id = $_GET['it_id'];
$img = $_GET['img'];

$sql = " select it_id, it_name from {$g4['shop_item_table']} where it_id='$it_id' ";
$row = sql_fetch_array(sql_query($sql));

if(!$row['it_id'])
    alert_close('상품정보가 존재하지 않습니다.');

$imagefile = G4_DATA_PATH."/item/$img";
$imagefileurl = G4_DATA_URL."/item/$img";
$size = getimagesize($imagefile);

$g4['title'] = "{$row['it_name']} ($it_id)";
include_once(G4_PATH.'/head.sub.php');
?>

<div id="sit_pvi_nw">
    <h1>상품 이미지 새 창 보기</h1>

    <div id="sit_pvi_nwbig">
        <a href="javascript:window.close();">
            <img src="<?php echo $imagefileurl; ?>" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>" alt="<?php echo $row['it_name']; ?>" id="largeimage">
        </a>
    </div>

    <?php
    for ($i=1; $i<=5; $i++)
    {
        if ($i == 1) echo '<ul>';
        if (file_exists(G4_DATA_PATH."/item/{$it_id}_l{$i}")) {
            $id = $it_id.'_l'.$i;
    ?>
        <li><a href="<?php echo G4_SHOP_URL; ?>/largeimage.php?it_id=<?php echo $it_id; ?>&amp;img=<?php echo $id; ?>" id="<?php echo $id; ?>" class="img_thumb"><img id="large<?php echo $i; ?>" src="<?php echo G4_DATA_URL; ?>/item/<?php echo $id; ?>" alt=""></a></li>
    <?php
        }
    }
    if ($i > 1) echo '</ul>';
    ?>

    <div class="btn_win">
        <button type="button" onclick="javascript:window.close();">창닫기</button>
    </div>
</div>

<script>
$(function(){
    // 창 사이즈 조절
    var w = <?php echo $size[0]; ?> + 50;
    var h = <?php echo $size[1]; ?> + 210;
    window.resizeTo(w, h);

    // 이미지 미리보기
    $(".img_thumb").bind("hover focus", function(){
        var img_src = $(this).attr("id");
        $("#sit_pvi_nwbig img").attr("src","<?php echo G4_DATA_URL; ?>/item/"+img_src); // 이미지 소스 교체
    });
});
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>