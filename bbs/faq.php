<?php
include_once('./_common.php');

if (!$fm_id) $fm_id = 1;

//dbconfig파일에 $g5['faq_table'] , $g5['faq_master_table'] 배열변수가 있는지 체크
if( !isset($g5['faq_table']) || !isset($g5['faq_master_table']) ){
    die('<meta charset="utf-8">관리자 모드에서 게시판관리->FAQ관리를 먼저 확인해 주세요.');
}

// FAQ MASTER
$sql = " select * from {$g5['faq_master_table']} where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);
if (!$fm['fm_id'])
    alert('등록된 내용이 없습니다.');

$g5['title'] = $fm['fm_subject'];
include_once('./_head.php');
?>

<?php
if ($is_admin)
    echo '<div class="faq_admin"><a href="'.G5_ADMIN_URL.'/faqmasterform.php?w=u&amp;fm_id='.$fm_id.'" class="btn_admin">FAQ 수정</a></div>';
?>

<!-- FAQ 시작 { -->
<?php
$himg = G5_DATA_PATH.'/faq/'.$fm_id.'_h';
if (file_exists($himg))
    echo '<div id="faq_himg" class="faq_img"><img src="'.G5_DATA_URL.'/faq/'.$fm_id.'_h" alt=""></div>';

// 상단 HTML
echo '<div id="faq_hhtml">'.stripslashes($fm['fm_head_html']).'</div>';
?>

<div id="faq_wrap" class="faq_<?php echo $fm_id; ?>">
    <?php // FAQ 목차
    $sql = " select * from {$g5['faq_table']}
              where fm_id = '$fm_id'
              order by fa_order , fa_id ";
    $result = sql_query($sql);
    for ($i=1; $row=sql_fetch_array($result); $i++)
    {
        if ($i == 1)
        {
    ?>
    <section id="faq_list">
        <h2><?php echo $g5['title']; ?> 목차</h2>
        <ol>
    <?php } ?>
            <li><a href="#faq_<?php echo $fm_id.'_'.$i; ?>"><?php echo stripslashes($row['fa_subject']); ?></a></li>
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
    <section id="faq_con">
        <h2><?php echo $g5['title']; ?> 내용</h2>
        <ol>
    <?php } ?>
        <li id="faq_<?php echo $fm_id.'_'.$i; ?>">
            <h3><?php echo stripslashes($row['fa_subject']); ?></h3>
            <div id="con_inner">
                <?php echo stripslashes($row['fa_content']); ?>
            </div>
            <div class="faq_tolist"><a href="#faq_list" class="btn01">FAQ 목차</a></div>
        </li>
    <?php }
    if ($i > 1) echo '</ol></section>';

    if ($i == 1) echo '<p>등록된 FAQ가 없습니다.<br><a href="'.G5_ADMIN_URL.'/faqmasterlist.php">FAQ를 새로 등록하시려면 FAQ관리</a> 메뉴를 이용하십시오.</p>';
    ?>
</div>

<?php
// 하단 HTML
echo '<div id="faq_thtml">'.stripslashes($fm['fm_tail_html']).'</div>';

$timg = G5_DATA_PATH.'/faq/'.$fm_id.'_t';
if (file_exists($timg))
    echo '<div id="faq_timg" class="faq_img"><img src="'.G5_DATA_URL.'/faq/'.$fm_id.'_t" alt=""></div>';
?>
<!-- } FAQ 끝 -->

<?php
if ($is_admin)
    echo '<div class="faq_admin"><a href="'.G5_ADMIN_URL.'/faqmasterform.php?w=u&amp;fm_id='.$fm_id.'" class="btn_admin">FAQ 수정</a></div>';

include_once('./_tail.php');
?>
