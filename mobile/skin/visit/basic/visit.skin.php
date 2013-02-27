<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

global $is_admin;
?>

<section id="visit">
    <div>
        <h2>방문자집계</h2>
        <dl>
            <dt>오늘</dt>
            <dd><?=number_format($visit[1])?></dd>
            <dt>어제</dt>
            <dd><?=number_format($visit[2])?></dd>
            <dt>최대</dt>
            <dd><?=number_format($visit[3])?></dd>
            <dt>전체</dt>
            <dd><?=number_format($visit[4])?></dd>
        </dl>
        <? if ($is_admin == "super") { ?><a href="<?=G4_ADMIN_URL?>/visit_list.php">상세보기</a><?}?>
    </div>
</section>
