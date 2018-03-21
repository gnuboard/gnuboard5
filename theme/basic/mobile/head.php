<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
?>

<header id="hd">
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>

    <div class="to_content"><a href="#container">본문 바로가기</a></div>

    <?php
    if(defined('_INDEX_')) { // index에서만 실행
        include G5_MOBILE_PATH.'/newwin.inc.php'; // 팝업레이어
    } ?>

    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?php echo G5_URL ?>"><img src="<?php echo G5_IMG_URL ?>/m_logo.png" alt="<?php echo $config['cf_title']; ?>"></a>
        </div>

        <button type="button" id="gnb_open" class="hd_opener"><i class="fa fa-bars" aria-hidden="true"></i><span class="sound_only"> 메뉴열기</span></button>

        <div id="gnb" class="hd_div">
            <button type="button" id="gnb_close" class="hd_closer"><span class="sound_only">메뉴 </span>닫기</button>

            <ul id="gnb_1dul">
            <?php
            $sql = " select *
                        from {$g5['menu_table']}
                        where me_mobile_use = '1'
                          and length(me_code) = '2'
                        order by me_order, me_id ";
            $result = sql_query($sql, false);

            for($i=0; $row=sql_fetch_array($result); $i++) {
            ?>
                <li class="gnb_1dli">
                    <a href="<?php echo $row['me_link']; ?>" target="_<?php echo $row['me_target']; ?>" class="gnb_1da"><?php echo $row['me_name'] ?></a>
                    <?php
                    $sql2 = " select *
                                from {$g5['menu_table']}
                                where me_mobile_use = '1'
                                  and length(me_code) = '4'
                                  and substring(me_code, 1, 2) = '{$row['me_code']}'
                                order by me_order, me_id ";
                    $result2 = sql_query($sql2);

                    for ($k=0; $row2=sql_fetch_array($result2); $k++) {
                        if($k == 0)
                            echo '<button type="button" class="btn_gnb_op">하위분류</button><ul class="gnb_2dul">'.PHP_EOL;
                    ?>
                        <li class="gnb_2dli"><a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>" class="gnb_2da"><span></span><?php echo $row2['me_name'] ?></a></li>
                    <?php
                    }

                    if($k > 0)
                        echo '</ul>'.PHP_EOL;
                    ?>
                </li>
            <?php
            }

            if ($i == 0) {  ?>
                <li id="gnb_empty">메뉴 준비 중입니다.<?php if ($is_admin) { ?> <br><a href="<?php echo G5_ADMIN_URL; ?>/menu_list.php">관리자모드 &gt; 환경설정 &gt; 메뉴설정</a>에서 설정하세요.<?php } ?></li>
            <?php } ?>
            </ul>

            <div id="hd_sch">
                <h2>사이트 내 전체검색</h2>
                <form name="fsearchbox" action="<?php echo G5_BBS_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);" method="get">
                <input type="hidden" name="sfl" value="wr_subject||wr_content">
                <input type="hidden" name="sop" value="and">
                <input type="text" name="stx" id="sch_stx" placeholder="검색어(필수)" required maxlength="20">
                <button type="submit" value="검색" id="sch_submit"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
                </form>

                <script>
                function fsearchbox_submit(f)
                {
                    if (f.stx.value.length < 2) {
                        alert("검색어는 두글자 이상 입력하십시오.");
                        f.stx.select();
                        f.stx.focus();
                        return false;
                    }

                    // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
                    var cnt = 0;
                    for (var i=0; i<f.stx.value.length; i++) {
                        if (f.stx.value.charAt(i) == ' ')
                            cnt++;
                    }

                    if (cnt > 1) {
                        alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
                        f.stx.select();
                        f.stx.focus();
                        return false;
                    }

                    return true;
                }
                </script>
            </div>
        </div>

        <button type="button" id="user_btn" class="hd_opener"><i class="fa fa-user" aria-hidden="true"></i><span class="sound_only">사용자메뉴</span></button>
        <div class="hd_div" id="user_menu">
            <button type="button" id="user_close" class="hd_closer"><span class="sound_only">메뉴 </span>닫기</button>

            <?php echo outlogin('theme/basic'); // 외부 로그인 ?>

            <ul id="hd_nb">
                <li class="hd_nb1"><a href="<?php echo G5_BBS_URL ?>/qalist.php" id="snb_qa"><i class="fa fa-comments" aria-hidden="true"></i><br>1:1문의</a></li>
                <li class="hd_nb2"><a href="<?php echo G5_BBS_URL ?>/faq.php" id="snb_faq"><i class="fa fa-question-circle" aria-hidden="true"></i><br>FAQ</a></li>
                <li class="hd_nb3"><a href="<?php echo G5_BBS_URL ?>/current_connect.php" id="snb_cnt"><i class="fa fa-users" aria-hidden="true"></i><br>접속자 <span><?php echo connect('theme/basic'); // 현재 접속자수 ?></span></a></li>
                <li class="hd_nb4"><a href="<?php echo G5_BBS_URL ?>/new.php" id="snb_new"><i class="fa fa-history" aria-hidden="true"></i><br>새글</a></li>
                
            </ul>

            <div id="text_size">
            <!-- font_resize('엘리먼트id', '제거할 class', '추가할 class'); -->
                <button id="size_down" onclick="font_resize('container', 'ts_up ts_up2', '', this);" class="select"><img src="<?php echo G5_URL; ?>/img/ts01.png" width="20" alt="기본"></button>
                <button id="size_def" onclick="font_resize('container', 'ts_up ts_up2', 'ts_up', this);"><img src="<?php echo G5_URL; ?>/img/ts02.png" width="20" alt="크게"></button>
                <button id="size_up" onclick="font_resize('container', 'ts_up ts_up2', 'ts_up2', this);"><img src="<?php echo G5_URL; ?>/img/ts03.png" width="20" alt="더크게"></button>
            </div>
        </div>

        <script>
        $(function () {
            //폰트 크기 조정 위치 지정
            var font_resize_class = get_cookie("ck_font_resize_add_class");
            if( font_resize_class == 'ts_up' ){
                $("#text_size button").removeClass("select");
                $("#size_def").addClass("select");
            } else if (font_resize_class == 'ts_up2') {
                $("#text_size button").removeClass("select");
                $("#size_up").addClass("select");
            }

            $(".hd_opener").on("click", function() {
                var $this = $(this);
                var $hd_layer = $this.next(".hd_div");

                if($hd_layer.is(":visible")) {
                    $hd_layer.hide();
                    $this.find("span").text("열기");
                } else {
                    var $hd_layer2 = $(".hd_div:visible");
                    $hd_layer2.prev(".hd_opener").find("span").text("열기");
                    $hd_layer2.hide();

                    $hd_layer.show();
                    $this.find("span").text("닫기");
                }
            });

            $("#container").on("click", function() {
                $(".hd_div").hide();

            });

            $(".btn_gnb_op").click(function(){
                $(this).toggleClass("btn_gnb_cl").next(".gnb_2dul").slideToggle(300);
                
            });

            $(".hd_closer").on("click", function() {
                var idx = $(".hd_closer").index($(this));
                $(".hd_div:visible").hide();
                $(".hd_opener:eq("+idx+")").find("span").text("열기");
            });
        });
        </script>
        
    </div>
</header>



<div id="wrapper">

    <div id="container">
    <?php if (!defined("_INDEX_")) { ?><h2 id="container_title" class="top" title="<?php echo get_text($g5['title']); ?>"><?php echo get_head_title($g5['title']); ?></h2><?php } ?>
