<?
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

    <h1><?=$config['cf_title']?></h1>

    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?=G4_URL?>"><img src="<?=G4_IMG_URL?>/logo.jpg" alt="<?=$config['cf_title']?> 처음으로" width="53" height="37"></a>
        </div>

        <fieldset id="schall">
            <legend>사이트 내 전체검색</legend>
            <form name="fsearchbox" method="get" action="<?=G4_BBS_URL?>/search.php" onsubmit="return fsearchbox_submit(this);">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <input type="text" id="schall_stx" name="stx" title="검색어" maxlength="20"><input type="image" id="schall_submit" src="<?=G4_IMG_URL?>/btn_search.jpg" width="24" height="24" alt="검색">
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

        <ul id="snb">
            <li><a href="<?=G4_BBS_URL?>/current_connect.php" id="snb_cnt">접속자 <?=connect(); // 현재 접속자수 ?></a></li>
            <li><a href="<?=G4_BBS_URL?>/new.php" id="snb_new">새글</a></li>
            <? if ($is_member) { ?>
            <? if ($is_admin) { ?>
            <li><a href="<?=G4_ADMIN_URL?>" id="snb_adm">관리자</a></li>
            <? } ?>
            <li><a href="<?=G4_BBS_URL?>/member_confirm.php?url=<?=G4_BBS_URL?>/register_form.php" id="snb_modify">내 정보</a></li>
            <li><a href="<?=G4_BBS_URL?>/logout.php" id="snb_logout">로그아웃</a></li>
            <? } else { ?>
            <li><a href="<?=G4_BBS_URL?>/register.php" id="snb_join">회원가입</a></li>
            <li><a href="<?=G4_BBS_URL?>/login.php" id="snb_login">로그인</a></li>
            <? } ?>
        </ul>

    </div>
</header>

<hr>

<nav id="gnb">
    <script>$('#gnb').addClass('gnb_js');</script>
    <h2>홈페이지 메인메뉴</h2>
    <ul>
        <?
        $sql = " select * from {$g4['group_table']} where gr_show_menu order by gr_order ";
        $result = sql_query($sql);
        for ($gi=0; $row=sql_fetch_array($result); $gi++) { // gi 는 group index 
        ?>
        <li class="gnb_1depth">
            <a href="<?=G4_BBS_URL?>/group.php?gr_id=<?=$row['gr_id']?>"><?=$row['gr_subject']?></a>
            <ul>
                <?
                $sql2 = " select * from {$g4['board_table']} where gr_id = '{$row['gr_id']}' order by bo_order ";
                $result2 = sql_query($sql2);
                for ($bi=0; $row2=sql_fetch_array($result2); $bi++) { // bi 는 board index
                ?>
                <li class="gnb_2depth"><a href="<?=G4_BBS_URL?>/board.php?bo_table=<?=$row2['bo_table']?>"><?=$row2['bo_subject']?></a></li>
                <?}?>
            </ul>
        </li>
        <?}?>
    </ul>
</nav>

<hr>

<div id="wrapper">
    <div id="lnb">
        <?=outlogin('basic'); // 외부 로그인 ?>
        <?=poll('basic'); // 설문조사 ?>
    </div>
    <div id="container">
        <? if ((!$bo_table || $w == 's' ) && !defined("_INDEX_")) {?><h1 id="wrapper_title"><?=$g4['title']?></h1><?}?>
