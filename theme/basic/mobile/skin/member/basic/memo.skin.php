<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<div id="memo_list" class="new_win mbskin">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>

    <ul class="win_ul">
        <li><a href="./memo.php?kind=recv">받은쪽지</a></li>
        <li><a href="./memo.php?kind=send">보낸쪽지</a></li>
        <li><a href="./memo_form.php">쪽지쓰기</a></li>
    </ul>

    <div class="win_desc">
        전체 <?php echo $kind_title ?>쪽지 <?php echo $total_count ?>통<br>
    </div>

    <ul id="memo_list_ul">
        <?php for ($i=0; $i<count($list); $i++) { ?>
        <li>
            <a href="<?php echo $list[$i]['view_href'] ?>" class="memo_link"><?php echo $list[$i]['send_datetime'] ?> 에 받은 쪽지</a>
            <span class="memo_read"><?php echo $list[$i]['read_datetime'] ?></span>
            <span class="memo_send"><?php echo $list[$i]['name'] ?></span>
            <a href="<?php echo $list[$i]['del_href'] ?>" onclick="del(this.href); return false;" class="memo_del">삭제</a>
        </li>
        <?php } ?>
        <?php if ($i==0) { echo "<li class=\"empty_list\">자료가 없습니다.</li>"; } ?>
    </ul>

    <p class="win_desc">
        쪽지 보관일수는 최장 <strong><?php echo $config['cf_memo_del'] ?></strong>일 입니다.
    </p>

    <div class="win_btn">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
</div>