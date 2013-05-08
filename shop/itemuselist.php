<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/thumb.lib.php');

$g4['title'] = '사용후기';
include_once('./_head.php');

$sql_common = " from {$g4['shop_item_ps_table']} where is_confirm = '1' ";
$sql_order = " order by is_time desc ";

$sql = " select count(*) as cnt
         $sql_common
         $sql_search
         $sql_order ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>

<div id="sps">

    <?php
    $sql = " select *
              $sql_common
              $sql_search
              $sql_order
              limit $from_record, $rows ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $num = $total_count - ($page - 1) * $rows - $i;
        $star = get_star($row['is_score']);

        $thumb = new g4_thumb(G4_DATA_PATH.'/itemuse', 500);
        $is_content = $thumb->run($row['is_content']);
        $is_time = substr($row['is_time'], 2, 14);
        $small_image = $row['it_id']."_s";

        $row2 = sql_fetch(" select it_name from {$g4['shop_item_table']} where it_id = '{$row['it_id']}' ");
        $it_href = G4_SHOP_URL."/item.php?it_id={$row['it_id']}";

        if ($i == 0) echo '<ol>';
    ?>
    <li>

        <div class="sps_img">
            <a href="<?php echo $it_href; ?>">
                <?php echo get_it_image($small_image, 70, 70); ?>
                <span><?php echo $row2['it_name']; ?></span>
            </a>
        </div>

        <section class="sps_section">
            <h2><?php echo $row['is_subject']; ?></h2>

            <dl class="sps_dl">
                <dt>작성자</dt>
                <dd><?php echo $row['is_name']; ?></dd>
                <dt>작성일</dt>
                <dd><?php echo substr($row['is_time'],0,10); ?></dd>
                <dt>평가점수</dt>
                <dd><img src="<?php echo G4_URL; ?>/img/shop/s_star<?php echo $star; ?>.png" alt="별<?php echo $star; ?>개"></dd>
            </dl>

            <p id="sps_con_<?php echo $i; ?>">
                <?php echo $is_content; // 상품 문의 내용 ?>
            </p>

            <div class="sps_con_btn"><button class="sps_con_<?php echo $i; ?>">더보기</button></div>
        </section>

    </li>
    <?php }
    if ($i > 0) echo '</ol>';
    if ($i == 0) echo '<p id="sps_empty">자료가 없습니다.</p>';
    ?>

</div>


<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
$(function(){
    // 사용후기 더보기
    $('.sps_con_btn button').click(function(){
        $this = $(this);
        sps_con_no = $this.attr('class');
        $('#'+sps_con_no).toggleClass('sps_con_full');
    });
    $('.sps_con_btn button').toggle(function(){
        $this.text('닫기');
    }, function(){
        $this.text('더보기');
    });
});
</script>

<?php
include_once('./_tail.php');
?>
