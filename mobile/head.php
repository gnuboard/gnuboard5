<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G4_PATH.'/head.sub.php');
include_once(G4_LIB_PATH.'/latest.lib.php');
include_once(G4_LIB_PATH.'/outlogin.lib.php');
include_once(G4_LIB_PATH.'/poll.lib.php');
include_once(G4_LIB_PATH.'/visit.lib.php');
include_once(G4_LIB_PATH.'/connect.lib.php');
include_once(G4_LIB_PATH.'/popular.lib.php');
?>

<header id="hd">
    <h1><?php echo $config['cf_title'] ?></h1>

    <div class="to_content"><a href="#container">본문 바로가기</a></div>
    <div class="to_content"><a href="#gnb">메인메뉴 바로가기</a></div>

    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?php echo G4_URL ?>"><img src="<?php echo G4_IMG_URL ?>/logo.jpg" alt="처음으로"></a>
        </div>

        <fieldset id="schall">
            <legend>사이트 내 전체검색</legend>
            <form name="fsearchbox" action="<?php echo G4_BBS_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);" method="get">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <input type="text" name="stx" id="schall_stx" placeholder="검색어(필수)" required class="required" maxlength="20">
            <input type="image" alt="검색" src="<?php echo G4_IMG_URL ?>/btn_search.jpg" id="schall_submit" width="24" height="24">
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
        </fieldset>

        <ul id="mb_nb">
            <li><a href="<?php echo G4_BBS_URL ?>/current_connect.php" id="snb_cnt">접속자 <?php echo connect(); // 현재 접속자수 ?></a></li>
            <li><a href="<?php echo G4_BBS_URL ?>/new.php" id="snb_new">새글</a></li>
            <?php if ($is_member) { ?>
            <?php if ($is_admin) { ?>
            <li><a href="<?php echo G4_ADMIN_URL ?>" id="snb_adm">관리자</a></li>
            <?php } ?>
            <li><a href="<?php echo G4_BBS_URL ?>/member_confirm.php?url=<?php echo G4_BBS_URL ?>/register_form.php" id="snb_modify">내 정보</a></li>
            <li><a href="<?php echo G4_BBS_URL ?>/logout.php" id="snb_logout">로그아웃</a></li>
            <?php } else { ?>
            <li><a href="<?php echo G4_BBS_URL ?>/register.php" id="snb_join">회원가입</a></li>
            <li><a href="<?php echo G4_BBS_URL ?>/login.php" id="snb_login">로그인</a></li>
            <?php } ?>
        </ul>

    </div>
</header>

<hr>

<nav id="lnb">
    <ul>
        <?php
        $sql2 = " select * from {$g4['board_table']} where bo_show_menu = 1 and bo_device <> 'pc' ";
        if ($gr_id) $sql2 .= " and gr_id = '$gr_id' ";
        $sql2 .= " order by bo_order ";
        $result2 = sql_query($sql2);
        for ($bi=0; $row2=sql_fetch_array($result2); $bi++) { // bi 는 board index
            $bo_subject = $row2['bo_subject'];
            if (G4_IS_MOBILE && $row2['bo_mobile_subject']) {
                $bo_subject = $row2['bo_mobile_subject'];
            }
        ?>
        <li><a href="<?php echo G4_BBS_URL ?>/board.php?bo_table=<?php echo $row2['bo_table'] ?>"><?php echo $bo_subject; ?></a></li>
        <?php } ?>
    </ul>
</nav>

<hr>

<div id="wrapper">
    <aside id="aside">
        <?php echo outlogin('basic'); // 외부 로그인 ?>
    </aside>
    <div id="container">
        <?php if ((!$bo_table || $w == 's' ) && !defined("_INDEX_")) { ?><h1 id="wrapper_title"><?php echo $g4['title'] ?></h1><?php } ?>
        <div id="text_size">
            <button class="no_text_resize" onclick="font_resize('container', 'decrease');">작게</button>
            <button class="no_text_resize" onclick="font_default('container');">기본</button>
            <button class="no_text_resize" onclick="font_resize('container', 'increase');">크게</button>
        </div>