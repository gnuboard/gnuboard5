<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<div data-role="page">

<div data-role='header' data-position='inline' data-theme='a'>
    <? if (!defined('_INDEX_')) { ?>
    <a href='<?=$g4[path]?>/' data-icon='home' data-iconpos='notext' data-direction='reverse' class='jqm-home' data-ajax="false">Home</a>
    <? } else { ?>
    <a href='<?=$g4[path]?>/bbs/memo.php' data-icon='info' data-direction='reverse' class='jqm-home' data-ajax="false">쪽지</a>
    <? } ?>
    <h1><?=$g4[title]?></h1>
    <? if ($is_member) { ?>
    <a href="<?="$g4[bbs_path]/logout.php"?>" data-role="button" data-icon="check" data-iconpos="left" class="ui-btn-right" data-ajax="false">로그아웃</a>
    <? } else { ?>
    <a href="<?="$g4[bbs_path]/login.php?url=$urlencode"?>" data-role="button" data-icon="check" data-iconpos="left" class="ui-btn-right">로그인</a>
    <? } ?>
</div><!-- /header -->
