<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once($g4['path'].'/head.sub.php');
include_once($g4['path'].'/lib/outlogin.lib.php');
include_once($g4['path'].'/lib/poll.lib.php');
include_once($g4['path'].'/lib/visit.lib.php');
include_once($g4['path'].'/lib/connect.lib.php');
include_once($g4['path'].'/lib/popular.lib.php');

//print_r2(get_defined_constants());

if ($config['cf_title'] == $g4['title']) $g4['title'] = '';
?>

<header>
<h1><?=$g4['title']?> <?=$config['cf_title']?></h1>
<div id="to_content"><a href="#wrapper">본문 바로가기</a></div>
<div id="logo"><a href="<?=$g4['path']?>/">처음으로</a></div>

<aside>
    <ul>
        <li><a href="<?=$g4['path']?>/bbs/login.php">로그인</a></li>
        <li><a href="<?=$g4['path']?>/bbs/register.php">회원가입</a></li>
        <li><a href="<?=$g4['path']?>/bbs/new.php">최근게시물</a></li>
    </ul>
</aside>

<form name="fsearchbox" method="get" action="" onsubmit="return fsearchbox_submit(this);">
<input type="hidden" id="sfl" name="sfl" value="wr_subject||wr_content">
<input type="hidden" id="sop" name="sop" value="and">
<fieldset>
    <legend>사이트 내 전체검색</legend>
    <input type="text" id="stx" name="stx" maxlength="20">
    <input type="submit" value="검색">
</fieldset>
</form>
</header>

<?//=outlogin('basic'); // 외부 로그인 ?>
<?//=poll('basic'); // 설문조사 ?>
<?//=visit('basic'); // 방문자수 ?>
<?//=connect(); // 현재 접속자수 ?>

<div id="wrapper">