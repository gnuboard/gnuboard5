<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/head.php');
    return;
}

if(G5_COMMUNITY_USE === false) {
    define('G5_IS_COMMUNITY_PAGE', true);
    include_once(G5_THEME_SHOP_PATH.'/shop.head.php');
    return;
}
include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
?>

<!-- 상단 시작 { -->
<div id="hd" class="bg-mainbg">
    <h1 id="hd_h1" class="blind"><?php echo $g5['title'] ?></h1>
    <div id="skip_to_container"><a href="#container" class="absolute top-0 left-0 w-px h-px overflow-hidden opacity-0 z-1000 focus:w-full focus:h-20 focus:bg-gray-900 focus:text-white focus:text-sm focus:font-bold focus:text-center focus:no-underline focus:leading-loose active:w-full active:h-20 active:bg-gray-900 active:text-white active:text-sm active:font-bold active:text-center active:no-underline active:leading-loose">본문 바로가기</a></div>

    <?php
    if(defined('_INDEX_')) { // index에서만 실행
        include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
    }
    ?>
    <div id="tnb" class="border-b border-solid border-mainborder mx-auto px-3">
    	<div class="inner max-w-screen-xl w-full mx-auto flex justify-between">
            <?php if(G5_COMMUNITY_USE) { ?>
    		<ul id="hd_define" class="flex text-xs">
    			<li class="active border-r border-gray-600 my-3.5 pr-2.5"><a href="<?php echo G5_URL ?>/" class="text-gray-400">커뮤니티</a></li>
                <?php if (defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
    			<li class="my-3.5 pl-2.5"><a href="<?php echo G5_SHOP_URL ?>/" class="text-gray-400">쇼핑몰</a></li>
                <?php } ?>
    		</ul>
            <?php } ?>
			<ul id="hd_qnb" class="flex">
	            <li class="text-xs border-r border-gray-600 my-3.5 mr-2.5 pr-2.5"><a href="<?php echo G5_BBS_URL ?>/faq.php" class="text-gray-400">FAQ</a></li>
	            <li class="text-xs border-r border-gray-600 my-3.5 mr-2.5 pr-2.5"><a href="<?php echo G5_BBS_URL ?>/qalist.php" class="text-gray-400">Q&A</a></li>
	            <li class="text-xs border-r border-gray-600 my-3.5 mr-2.5 pr-2.5"><a href="<?php echo G5_BBS_URL ?>/new.php" class="text-gray-400">새글</a></li>
	            <li class="text-xs my-3.5"><a href="<?php echo G5_BBS_URL ?>/current_connect.php" class="visit text-gray-400">접속자<strong class="visit-num inline-block rounded-xl bg-fuchsia-600 text-white text-xs ml-2 px-1"><?php echo connect('theme/basic'); // 현재 접속자수, 테마의 스킨을 사용하려면 스킨을 theme/basic 과 같이 지정  ?></strong></a></li>
	        </ul>
		</div>
    </div>
    <div id="hd_wrapper" class="max-w-screen-xl w-full relative m-0 h-140 flex justify-between items-center mx-auto xl:px-0 px-3">
        <div id="logo" class="grow-0 shrink-0">
            <a href="<?php echo G5_URL ?>"><img src="<?php echo G5_IMG_URL ?>/logo.png" alt="<?php echo $config['cf_title']; ?>"></a>
        </div>
    
        <div class="xl:block hidden hd_sch_wr grow w-full ms-16">
            <fieldset id="hd_sch">
                <legend>사이트 내 전체검색</legend>
                <form name="fsearchbox" method="get" class="flex" action="<?php echo G5_BBS_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);">
                <input type="hidden" name="sfl" value="wr_subject||wr_content">
                <input type="hidden" name="sop" value="and">
                <label for="sch_stx" class="sound_only">검색어 필수</label>
                <input type="text" name="stx" id="sch_stx" class="max-w-sm w-full h-11 ps-2.5 bg-schbg border-0 text-white text-sm rounded-l-3xl overflow-hidden" maxlength="20" placeholder="검색어를 입력해주세요">
                <button type="submit" id="sch_submit" class="w-14 h-11 border-0 bg-schbg text-white rounded-e-3xl corsor-pointer text-base" value="검색"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
                </form>

                <script>
                function fsearchbox_submit(f)
                {
                    var stx = f.stx.value.trim();
                    if (stx.length < 2) {
                        alert("검색어는 두글자 이상 입력하십시오.");
                        f.stx.select();
                        f.stx.focus();
                        return false;
                    }

                    // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
                    var cnt = 0;
                    for (var i = 0; i < stx.length; i++) {
                        if (stx.charAt(i) == ' ')
                            cnt++;
                    }

                    if (cnt > 1) {
                        alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
                        f.stx.select();
                        f.stx.focus();
                        return false;
                    }
                    f.stx.value = stx;

                    return true;
                }
                </script>

            </fieldset>
                
            <?php echo popular('theme/basic'); // 인기검색어, 테마의 스킨을 사용하려면 스킨을 theme/basic 과 같이 지정  ?>
        </div>
        <ul class="hd_login flex grow-0 shrink-0 text-white">        
            <?php if ($is_member) {  ?>
            <li class="mx-1"><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a></li>
            <li class="border-l border-gray-600 pl-2.5 mx-1"><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
            <?php if ($is_admin) {  ?>
            <li class="tnb_admin border-l border-gray-600 pl-2.5 mx-1"><a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>">관리자</a></li>
            <?php }  ?>
            <?php } else {  ?>
            <li class="pl-2.5 mx-1"><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></li>
            <li class="border-l border-gray-600 pl-2.5 mx-1"><a href="<?php echo G5_BBS_URL ?>/login.php">로그인</a></li>
            <?php }  ?>
        </ul>
    </div>
    
    <nav id="gnb" class="bg-white dark:bg-zinc-900">
        <h2 class="blind">메인메뉴</h2>
        <div class="gnb_wrap relative max-w-screen-xl w-full mx-auto hover:z-10 active:z-10 focus:z-10">
            <ul id="gnb_1dul" class="flex flex-row-reverse text-xs border-b border-solid border-gray-200 p-0 dark:border-mainborder">
                <li class="gnb_1dli gnb_mnal ml-auto"><button type="button" class="gnb_menu_btn bg-gnbmenu text-white w-14 h-14 border-0 align-top text-lg" title="전체메뉴"><i class="fa fa-bars" aria-hidden="true"></i><span class="sound_only">전체메뉴열기</span></button></li>
                <?php
				$menu_datas = get_menu_db(0, true);
				$gnb_zindex = 999; // gnb_1dli z-index 값 설정용
                $i = 0;
                foreach( $menu_datas as $row ){
                    if( empty($row) ) continue;
                    $add_class = (isset($row['sub']) && $row['sub']) ? 'gnb_al_li_plus' : '';
                ?>
                <li class="gnb_1dli <?php echo $add_class; ?> relative leading-55 group" style="z-index:<?php echo $gnb_zindex--; ?>">
                    <a href="<?php echo $row['me_link']; ?>" target="_<?php echo $row['me_target']; ?>" class="gnb_1da block font-bold px-3.5 text-black no-underline group-hover:text-blue-400 transition ease-out duration-200 dark:text-white"><?php echo $row['me_name'] ?></a>
                    <?php
                    $k = 0;
                    foreach( (array) $row['sub'] as $row2 ){

                        if( empty($row2) ) continue; 

                        if($k == 0)
                            echo '<span class="bg absolute top-6 right-2 inline-block w-2.5 h-2.5 overflow-hidden">하위분류</span><div class="gnb_2dul hidden absolute top-13 min-w-36"><ul class="gnb_2dul_box border border-gray-200 shadow-md shadow-gray-300/5 dark:border-mainborder">'.PHP_EOL;
                    ?>
                        <li class="gnb_2dli"><a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>" class="gnb_2da block px-2.5 bg-white text-black text-left no-underline leading-10 hover:text-blue-400 hover:bg-gray-100 transition ease-out duration-200 dark:bg-zinc-800 dark:text-white"><?php echo $row2['me_name'] ?></a></li>
                    <?php
                    $k++;
                    }   //end foreach $row2

                    if($k > 0)
                        echo '</ul></div>'.PHP_EOL;
                    ?>
                </li>
                <?php
                $i++;
                }   //end foreach $row

                if ($i == 0) {  ?>
                    <li class="gnb_empty py-2.5 w-full text-center text-black">메뉴 준비 중입니다.<?php if ($is_admin) { ?> <a href="<?php echo G5_ADMIN_URL; ?>/menu_list.php" class="text-blue-400 no-underline">관리자모드 &gt; 환경설정 &gt; 메뉴설정</a>에서 설정하실 수 있습니다.<?php } ?></li>
                <?php } ?>
            </ul>
            <div id="gnb_all" class="hidden absolute border border-gray-200 w-full bg-white z-1000 shadow-md shadow-gray-600/50 dark:bg-zinc-800 dark:border-mainborder">
                <h2 class="flex items-center text-sm border-b border-solid border-gray-200 h-14 px-5 dark:border-mainborder dark:text-white">전체메뉴</h2>
                <ul class="gnb_al_ul flex flex-wrap">
                    <?php
                    
                    $i = 0;
                    foreach( $menu_datas as $row ){
                    ?>
                    <li class="gnb_al_li w-1/5 min-h-40 border-l border-solid border-gray-200 p-5 dark:border-mainborder">
                        <a href="<?php echo $row['me_link']; ?>" target="_<?php echo $row['me_target']; ?>" class="gnb_al_a text-sm block relative mb-2.5 font-bold text-blue-400"><?php echo $row['me_name'] ?></a>
                        <?php
                        $k = 0;
                        foreach( (array) $row['sub'] as $row2 ){
                            if($k == 0)
                                echo '<ul>'.PHP_EOL;
                        ?>
                            <li><a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>" class="text-gray-700 leading-relaxed dark:text-white"><?php echo $row2['me_name'] ?></a></li>
                        <?php
                        $k++;
                        }   //end foreach $row2

                        if($k > 0)
                            echo '</ul>'.PHP_EOL;
                        ?>
                    </li>
                    <?php
                    $i++;
                    }   //end foreach $row

                    if ($i == 0) {  ?>
                        <li class="gnb_empty text-gray-800">메뉴 준비 중입니다.<?php if ($is_admin) { ?> <br><a href="<?php echo G5_ADMIN_URL; ?>/menu_list.php">관리자모드 &gt; 환경설정 &gt; 메뉴설정</a>에서 설정하실 수 있습니다.<?php } ?></li>
                    <?php } ?>
                </ul>
                <button type="button" class="gnb_close_btn bg-transparent text-gray-400 w-14 h-14 border-0 align-top text-lg absolute top-0 right-0"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>
            <div id="gnb_all_bg" class="hidden bg-black bg-opacity-20 w-full h-full fixed left-0 top-0 z-999"></div>
        </div>
    </nav>
    <script>
    
    $(function(){
        $(".gnb_menu_btn").click(function(){
            $("#gnb_all, #gnb_all_bg").show();
        });
        $(".gnb_close_btn, #gnb_all_bg").click(function(){
            $("#gnb_all, #gnb_all_bg").hide();
        });
    });

    </script>
</div>
<!-- } 상단 끝 -->


<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper" class="xl:px-0 px-2 dark:bg-zinc-900">
    <div id="container_wr" class="flex max-w-screen-xl w-full mx-auto">
      <div id="container" class="xl:w-container w-full relative min-h-500 my-5 h-auto">
        <?php if (!defined("_INDEX_")) { ?><h2 id="container_title" class="text-base mx-auto font-bold"><span class="block leading-relaxed mx-auto mb-2.5 dark:text-white" title="<?php echo get_text($g5['title']); ?>"><?php echo get_head_title($g5['title']); ?></span></h2><?php }