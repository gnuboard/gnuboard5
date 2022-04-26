<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<div id="memo_list" class="new_win">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>

    <ul class="win_ul">
        <li><a href="./memo.php?kind=recv" class="<?php if ($kind == 'recv') {  ?>selected<?php }  ?>">받은쪽지</a></li>
        <li><a href="./memo.php?kind=send" class="<?php if ($kind == 'send') {  ?>selected<?php }  ?>">보낸쪽지</a></li>
        <li><a href="./memo_form.php">쪽지쓰기</a></li>
    </ul>
    <div class="new_win_con">
        <div class="win_desc">
            전체 <?php echo $kind_title ?>쪽지 <?php echo $total_count ?>통<br>
        </div>

        <ul id="memo_list_ul">
            <?php for ($i=0; $i<count($list); $i++) { ?>
            <li>
                <a href="<?php echo $list[$i]['view_href'] ?>" class="memo_link"><?php echo $list[$i]['mb_nick'] ?></a>
                <span class="memo_date">
                    <?php echo $list[$i]['send_datetime'] ?><span class="sound_only"> 에 <?php echo  ($kind == "recv") ? "받은" : "보낸";  ?> 쪽지</span> - 
                    <?php echo $list[$i]['read_datetime'] ?>
                </span>
                <a href="<?php echo $list[$i]['del_href'] ?>" onclick="del(this.href); return false;" class="memo_del">삭제</a>
            </li>
            <?php } ?>
            <?php if ($i==0) { echo "<li class=\"empty_list\">자료가 없습니다.</li>"; } ?>
        </ul>

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