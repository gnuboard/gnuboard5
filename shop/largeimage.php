<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/largeimage.php');
    return;
}

$it_id = $_GET['it_id'];
$no = $_GET['no'];

$sql = " select it_id, it_name, it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
            from {$g5['g5_shop_item_table']} where it_id='$it_id' ";
$row = sql_fetch_array(sql_query($sql));

if(!$row['it_id'])
    alert_close('상품정보가 존재하지 않습니다.');

$imagefile = G5_DATA_PATH.'/item/'.$row['it_img'.$no];
$imagefileurl = G5_DATA_URL.'/item/'.$row['it_img'.$no];
$size = getimagesize($imagefile);

$g5['title'] = "{$row['it_name']} ($it_id)";
include_once(G5_PATH.'/head.sub.php');
?>

<div id="sit_pvi_nw" class="new_win">
    <h1 title="win_title">상품 이미지 새창 보기</h1>

    <div id="sit_pvi_nwbig">
        <?php
        $thumbnails = array();
        for($i=1; $i<=10; $i++) {
            if(!$row['it_img'.$i])
                continue;

            $file = G5_DATA_PATH.'/item/'.$row['it_img'.$i];
            if(is_file($file)) {
                // 썸네일
                $thumb = get_it_thumbnail($row['it_img'.$i], 60, 60);
                $thumbnails[$i] = $thumb;
                $imageurl = G5_DATA_URL.'/item/'.$row['it_img'.$i];
        ?>
        <span>
            <a href="javascript:window.close();">
                <img src="<?php echo $imageurl; ?>" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>" alt="<?php echo $row['it_name']; ?>" id="largeimage">
            </a>
        </span>
        <?php
            }
        }
        ?>
    </div>

    <?php
    $total_count = count($thumbnails);
    $thumb_count = 0;
    if($total_count > 0) {
        echo '<ul>';
        foreach($thumbnails as $key=>$val) {
            echo '<li><a href="'.G5_SHOP_URL.'/largeimage.php?it_id='.$it_id.'&amp;no='.$key.'" class="img_thumb">'.$val.'</a></li>';
        }
        echo '</ul>';
    }
    ?>

    <div class="win_btn">
        <button type="button" onclick="javascript:window.close();">창닫기</button>
    </div>
</div>

<script>
$(function(){
    // 창 사이즈 조절
    var w = <?php echo $size[0]; ?> + 50;
    var h = <?php echo $size[1]; ?> + 210;
    window.resizeTo(w, h);

    $("#sit_pvi_nwbig span:eq("+<?php echo ($no - 1); ?>+")").addClass("visible");

    // 이미지 미리보기
    $(".img_thumb").bind("mouseover focus", function(){
        var idx = $(".img_thumb").index($(this));
        $("#sit_pvi_nwbig span.visible").removeClass("visible");
        $("#sit_pvi_nwbig span:eq("+idx+")").addClass("visible");
    });
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>