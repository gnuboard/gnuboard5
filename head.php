<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once($g4['path'].'/head.sub.php');
include_once($g4['path'].'/lib/outlogin.lib.php');
include_once($g4['path'].'/lib/poll.lib.php');
include_once($g4['path'].'/lib/visit.lib.php');
include_once($g4['path'].'/lib/connect.lib.php');
include_once($g4['path'].'/lib/popular.lib.php');

//print_r2(get_defined_constants());
?>

<header id="hd">
    <div id="hd_wrapper">
        <div id="to_content"><a href="#wrapper">본문 바로가기</a></div>
        <div id="logo"><a href="<?=$g4['path']?>/"><img src="<?=$g4['path']?>/img/logo.jpg" alt="처음으로"></a></div>

        <h1><?=$config['cf_title']?></h1>

        <ul id="snb">
            <li><a href="<?=$g4['bbs_path']?>/current_connect.php">현재접속자</a></li>
            <li><a href="<?=$g4['bbs_path']?>/new.php">최근게시물</a></li>
            <? if ($is_member) { ?>
            <? if ($is_admin) { ?><li><a href="<?=$g4['path']?>/adm">관리자</a></li><? } ?>
            <li><a href="<?=$g4['bbs_path']?>/member_confirm.php?url=register_form.php">정보수정</a></li>
            <li><a href="<?=$g4['bbs_path']?>/logout.php">로그아웃</a></li>
            <? } else { ?>
            <li><a href="<?=$g4['bbs_path']?>/register.php">회원가입</a></li>
            <li><a href="<?=$g4['bbs_path']?>/login.php">로그인</a></li>
            <? } ?>
        </ul>

        <fieldset id="schall">
            <legend>사이트 내 전체검색</legend>
            <form name="fsearchbox" method="get" action="<?=$g4['https_bbs_url'].'/search.php'?>" onsubmit="return fsearchbox_submit(this);">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <input type="text" id="schall_stx" name="stx" title="검색어" maxlength="20">
            <input type="image" id="schall_submit" src="<?=$g4['path']?>/img/btn_search.jpg" alt="검색">
            </form>
        </fieldset>
    </div>
</header>

<hr>

<div id="wrapper">
    <div id="lnb">
        <?=outlogin('neo'); // 외부 로그인 ?>
        <?=poll('neo'); // 설문조사 ?>
        <?=visit("neo"); // 방문자수 ?>
        <?=connect(); // 현재 접속자수 ?>
    </div>
    <div id="container">
        <? if ((!$bo_table || $w == 's' ) && !defined("_INDEX_")) {?><h1 id="wrapper_title"><?=$g4['title']?></h1><?}?>
