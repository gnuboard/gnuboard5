<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once($g4['path'].'/head.sub.php');
include_once($g4['path'].'/lib/outlogin.lib.php');
include_once($g4['path'].'/lib/poll.lib.php');
include_once($g4['path'].'/lib/visit.lib.php');
include_once($g4['path'].'/lib/connect.lib.php');
include_once($g4['path'].'/lib/popular.lib.php');

//print_r2(get_defined_constants());
?>

<?
// 쪽지를 받았나?
if (isset($member['mb_memo_call']) && $member['mb_memo_call']) {
    $mb = get_member($member['mb_memo_call'], "mb_nick");
    sql_query(" update {$g4['member_table']} set mb_memo_call = '' where mb_id = '{$member['mb_id']}' ");

    //alert($mb['mb_nick'].'님으로부터 쪽지가 전달되었습니다.', $_SERVER['REQUEST_URI'], false);
    $memo_msg = $mb['mb_nick'].'님으로부터 쪽지가 전달되었습니다.\\n\\n바로 확인하시겠습니까?';
    include_once($g4['bbs_path'].'/memocall.php');
}
?>

<header id="header">
    <div id="to_content"><a href="#wrapper">본문 바로가기</a></div>
    <div id="logo"><a href="<?=$g4['path']?>/"><img src="<?=$g4['path']?>/img/logo.jpg" alt="처음으로"></a></div>

    <h1><?=$config['cf_title']?></h1>

    <ul id="tnb">
        <? if ($is_member) { ?>
        <? if ($is_admin) { ?><li><a href="<?=$g4['path']?>/adm">관리자</a></li><? } ?>
        <li><a href="<?=$g4['bbs_path']?>/member_confirm.php?url=register_form.php">정보수정</a></li>
        <li><a href="<?=$g4['bbs_path']?>/logout.php">로그아웃</a></li>
        <? } else { ?>
        <li><a href="<?=$g4['bbs_path']?>/register.php">회원가입</a></li>
        <li><a href="<?=$g4['bbs_path']?>/login.php">로그인</a></li>
        <? } ?>
        <li><a href="<?=$g4['bbs_path']?>/current_connect.php">현재접속자</a></li>
        <li><a href="<?=$g4['bbs_path']?>/new.php">최근게시물</a></li>
    </ul>

    <fieldset id="hdsch">
        <legend>사이트 내 전체검색</legend>
        <form name="fsearchbox" method="get" action="<?=$g4['https_bbs_url'].'/search.php'?>" onsubmit="return fsearchbox_submit(this);">
        <input type="hidden" name="sfl" value="wr_subject||wr_content">
        <input type="hidden" name="sop" value="and">
        <input type="text" id="hdsch_stx" name="stx" title="검색어" maxlength="20">
        <input type="image" id="hdsch_submit" src="<?=$g4['path']?>/img/btn_search.jpg" alt="검색">
        </form>
    </fieldset>

</header>

<div id="snb">
    <?=outlogin('neo'); // 외부 로그인 ?>
    <?=poll('neo'); // 설문조사 ?>
</div>

<? /* if ($index || 게시판이 하나도 없을때) {?>
<!-- 설치 완료 메세지 -->
<article id="install_done">
    <h1>Welcome to Gnuboard 4s</h1>
    <div><span><!--  --></span></div>
    <section>
        <h2>한글 안내</h2>
        <p>
        그누보드4표준 버전을 설치해주셔서 감사합니다.<br>
        그누보드4표준 버전은 웹 접근성과 웹 표준을 준수합니다.<br>
        새로운 게시판을 생성하시면 이 메세지는 사라집니다.<br>
        감사합니다.
        </p>
    </section>
    <section>
        <h2>영문 안내</h2>
        <p>
        Thank you for installing Gnuboard4 Standard version.<br>
        This version is for Web Accessibility and Web Standard version.<br>
        This message will disappear after Create a new board.<br>
        Thank you.
        </p>
    </section>
</article>
<!-- 설치 완료 메세지 끝 -->
<? }*/ ?>

<hr>

<div id="wrapper">
    <? if (!$bo_table || $w == 's') {?><h1 id="wrapper_title"><?=$g4['title']?></h1><?}?>