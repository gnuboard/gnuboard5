<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

global $is_admin;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$visit_skin_url.'/style.css">', 0);
?>

<!-- 접속자집계 시작 { -->
<section id="visit" class="ft_cnt relative w-1/4 px-5 xl:block hidden">
    <h2 class="relative text-sm text-left text-white mb-5">접속자집계</h2>
    <dl class="py-3 after:block after:invisible after:clear-both after:content-['']">
        <dt class="float-left w-1/2 text-left leading-6 h-6 text-gray-200"><span class="inline-block w-1 h-1 rounded-full align-middle bg-blue-600 mr-3"></span> 오늘</dt>
        <dd class="float-left w-1/2 text-right font-bold leading-6 h-6 px-1"><strong class="inline-block rounded-2xl leading-4 text-white px-1"><?php echo number_format($visit[1]) ?></strong></dd>
        <dt class="float-left w-1/2 text-left leading-6 h-6 text-gray-200"><span class="inline-block w-1 h-1 rounded-full align-middle bg-blue-600 mr-3"></span> 어제</dt>
        <dd class="float-left w-1/2 text-right font-bold leading-6 h-6 px-1"><strong class="inline-block rounded-2xl leading-4 text-white px-1"><?php echo number_format($visit[2]) ?></strong></dd>
        <dt class="float-left w-1/2 text-left leading-6 h-6 text-gray-200"><span class="inline-block w-1 h-1 rounded-full align-middle bg-blue-600 mr-3"></span> 최대</dt>
        <dd class="float-left w-1/2 text-right font-bold leading-6 h-6 px-1"><strong class="inline-block rounded-2xl leading-4 text-white px-1"><?php echo number_format($visit[3]) ?></strong></dd>
        <dt class="float-left w-1/2 text-left leading-6 h-6 text-gray-200"><span class="inline-block w-1 h-1 rounded-full align-middle bg-blue-600 mr-3"></span> 전체</dt>
        <dd class="float-left w-1/2 text-right font-bold leading-6 h-6 px-1"><strong class="inline-block rounded-2xl leading-4 text-white px-1"><?php echo number_format($visit[4]) ?></strong></dd>
    </dl>
    <?php if ($is_admin == "super") {  ?><a href="<?php echo G5_ADMIN_URL ?>/visit_list.php" class="btn_admin btn absolute top-0 right-5 h-6 leading-6 rounded px-1"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a><?php } ?>
</section>
<!-- } 접속자집계 끝 -->                                                                                                                                                                  