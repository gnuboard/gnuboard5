<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 쪽지 목록 시작 { -->
<div id="memo_list" class="new_win">
    <h1 id="win_title"><i class="fa fa-envelope-o" aria-hidden="true"></i> <?php echo $g5['title'] ?></h1>
    <div class="new_win_con">
        <ul class="win_ul">
            <li class="<?php if ($kind == 'recv') {  ?>selected<?php }  ?>"><a href="./memo.php?kind=recv">받은쪽지</a></li>
            <li class="<?php if ($kind == 'send') {  ?>selected<?php }  ?>"><a href="./memo.php?kind=send">보낸쪽지</a></li>
            <li><a href="./memo_form.php">쪽지쓰기</a></li>
        </ul>
        <div class="win_total">
            <span>
                전체 <?php echo $kind_title ?>쪽지 <?php echo $total_count ?>통<br>
            </span>
        </div>
        <div class="list_01">

            <ul>
            <?php for ($i=0; $i<count($list); $i++) {  ?>
            <li>
                <span class="memo_name"><a href="<?php echo $list[$i]['view_href'] ?>"><?php echo $list[$i]['mb_nick'] ?></a></span>
                <span class="memo_datetime"><?php echo $list[$i]['send_datetime'] ?> - <?php echo $list[$i]['read_datetime'] ?> <a href="<?php echo $list[$i]['del_href'] ?>" onclick="del(this.href); return false;" class="memo_del"><i class="fa fa-times-circle" aria-hidden="true"></i> <span class="sound_only">삭제</span></a></span>
            </li>
            <?php }  ?>
            <?php if ($i==0) { echo '<li class="empty_table">자료가 없습니다.</li>'; }  ?>
            </ul>
        </div>

        <!-- 페이지 -->
        <?php echo $write_pages; ?>

        <p class="win_desc">
            쪽지 보관일수는 최장 <strong><?php echo $config['cf_memo_del'] ?></strong>일 입니다.
        </p>

        <div class="win_btn">
            <button type="button" onclick="window.close();" class="btn_close">창닫기</button>
        </div>
    </div>
</div>
<!-- } 쪽지 목록 끝 -->