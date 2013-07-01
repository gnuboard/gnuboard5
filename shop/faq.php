<?php
include_once('./_common.php');

if (!$fm_id) $fm_id = 1;

// FAQ MASTER
$sql = " select * from {$g4['shop_faq_master_table']} where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);
if (!$fm['fm_id'])
    alert('등록된 내용이 없습니다.');

$g4['title'] = $fm['fm_subject'];
include_once('./_head.php');
?>

<?php
if ($is_admin)
    echo '<div class="sfaq_admin"><a href="'.G4_ADMIN_URL.'/shop_admin/faqmasterform.php?w=u&amp;fm_id='.$fm_id.'" class="btn_admin">FAQ 수정</a></div>';
?>

<!-- FAQ 시작 { -->
<?php
$himg = G4_DATA_PATH.'/faq/'.$fm_id.'_h';
if (file_exists($himg))
    echo '<div id="sfaq_himg" class="sfaq_img"><img src="'.G4_DATA_URL.'/faq/'.$fm_id.'_h" alt=""></div>';

// 상단 HTML
echo '<div id="sfaq_hhtml">'.stripslashes($fm['fm_head_html']).'</div>';
?>

<div id="sfaq_wrap" class="sfaq_<?php echo $fm_id; ?>">
    <?php // FAQ 목차
    $sql = " select * from {$g4['shop_faq_table']}
              where fm_id = '$fm_id'
              order by fa_order , fa_id ";
    $result = sql_query($sql);
    for ($i=1; $row=sql_fetch_array($result); $i++)
    {
        if ($i == 1)
        {
    ?>
    <section id="sfaq_list">
        <h2><?php echo $g4['title']; ?> 목차</h2>
        <ol>
    <?php } ?>
            <li><a href="#sfaq_<?php echo $fm_id.'_'.$i; ?>"><?php echo stripslashes($row['fa_subject']); ?></a></li>
    <?php }
    if ($i > 1) echo '</ol></section>';
    ?>

    <?php // FAQ 내용
    $resultb = sql_query($sql);
    for ($i=1; $row=sql_fetch_array($resultb); $i++)
    {
        if ($i == 1)
        {
    ?>
    <section id="sfaq_con">
        <h2><?php echo $g4['title']; ?> 내용</h2>
        <ol>
    <?php } ?>
        <li id="sfaq_<?php echo $fm_id.'_'.$i; ?>">
            <h3><?php echo stripslashes($row['fa_subject']); ?></h3>
            <p>
                <?php echo stripslashes($row['fa_content']); ?>
            </p>
            <div class="sfaq_tolist"><a href="#sfaq_list" class="btn01">FAQ 목차</a></div>
        </li>
    <?php }
    if ($i > 1) echo '</ol></section>';

    if ($i == 1) echo '<p>등록된 FAQ가 없습니다.<br><a href="'.G4_ADMIN_URL.'/shop_admin/faqmasterlist.php">FAQ를 새로 등록하시려면 FAQ관리</a> 메뉴를 이용하십시오.</p>';
    ?>
</div>

<?php
// 하단 HTML
echo '<div id="sfaq_thtml">'.stripslashes($fm['fm_tail_html']).'</div>';

$timg = G4_DATA_PATH.'/faq/'.$fm_id.'_t';
if (file_exists($timg))
    echo '<div id="sfaq_timg" class="sfaq_img"><img src="'.G4_DATA_URL.'/faq/'.$fm_id.'_t" alt=""></div>';
?>
<!-- } FAQ 끝 -->

<?php
if ($is_admin)
    echo '<div class="sfaq_admin"><a href="'.G4_ADMIN_URL.'/shop_admin/faqmasterform.php?w=u&amp;fm_id='.$fm_id.'" class="btn_admin">FAQ 수정</a></div>';

include_once('./_tail.php');
?>
