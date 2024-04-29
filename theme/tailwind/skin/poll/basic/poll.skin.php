<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$poll_skin_url.'/style.css">', 0);
?>

<!-- 설문조사 시작 { -->
<form name="fpoll" action="<?php echo G5_BBS_URL ?>/poll_update.php" onsubmit="return fpoll_submit(this);" method="post">
<input type="hidden" name="po_id" value="<?php echo $po_id ?>">
<input type="hidden" name="skin_dir" value="<?php echo urlencode($skin_dir); ?>">
<section id="poll" class="relative border border-gray-200 bg-white rounded mb-4 dark:bg-zinc-900 dark:border-mainborder">
    <header class="relative border-b border-gray-200 px-4 py-3 after:block after:invisible after:clear-both after:content-[''] dark:border-mainborder">
        <h2 class="inline-block float-left text-sm text-black leading-7 after:block after:invisible after:clear-both after:content-[''] dark:text-white">설문조사</h2>
		<?php if ($is_admin == "super") {  ?><a href="<?php echo G5_ADMIN_URL ?>/poll_form.php?w=u&amp;po_id=<?php echo $po_id ?>" class="btn_admin float-right leading-7 text-sm ml-1" title="설문관리"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">설문관리</span></a><?php }  ?>
    	<a href="<?php echo G5_BBS_URL."/poll_result.php?po_id=$po_id&amp;skin_dir=".urlencode($skin_dir); ?>" target="_blank" onclick="poll_result(this.href); return false;" class="btn_result float-right inline-block leading-7 h-7 border border-gray-200 text-blue-500 rounded px-1 py-px bg-white dark:bg-zinc-900 dark:border-mainborder">결과보기</a>
    </header>
    <div class="poll_con">
        <p class="font-bold text-black text-xs py-4 px-5 dark:text-white"><?php echo $po['po_subject'] ?></p>
        <ul class="list-none px-5">
            <?php for ($i=1; $i<=9 && $po["po_poll{$i}"]; $i++) {  ?>
            <li class="chk_box py-1">
	        	<input type="radio" name="gb_poll" value="<?php echo $i ?>" id="gb_poll_<?php echo $i ?>">
	        	<label for="gb_poll_<?php echo $i ?>">
	        		<span></span>
	        		<?php echo $po['po_poll'.$i] ?>
	        	</label>
	        </li>
            <?php }  ?>
        </ul>
        <div id="poll_btn" class="text-center pt-3 px-5 pb-5 after:block after:invisible after:clear-both after:content-['']">
            <button type="submit" class="btn_poll block w-full h-10 leading-10 border-0 font-bold text-white bg-blue-500 rounded px-3">투표하기</button>
        </div>
    </div>
</section>
</form>

<script>
function fpoll_submit(f)
{
    <?php
    if ($member['mb_level'] < $po['po_level'])
        echo " alert('권한 {$po['po_level']} 이상의 회원만 투표에 참여하실 수 있습니다.'); return false; ";
     ?>

    var chk = false;
    for (i=0; i<f.gb_poll.length;i ++) {
        if (f.gb_poll[i].checked == true) {
            chk = f.gb_poll[i].value;
            break;
        }
    }

    if (!chk) {
        alert("투표하실 설문항목을 선택하세요");
        return false;
    }

    var new_win = window.open("about:blank", "win_poll", "width=616,height=500,scrollbars=yes,resizable=yes");
    f.target = "win_poll";

    return true;
}

function poll_result(url)
{
    <?php
    if ($member['mb_level'] < $po['po_level'])
        echo " alert('권한 {$po['po_level']} 이상의 회원만 결과를 보실 수 있습니다.'); return false; ";
     ?>

    win_poll(url);
}
</script>
<!-- } 설문조사 끝 -->