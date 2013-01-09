<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<div id="scrap" class="new_win">
    <h1><?=$g4['title']?></h1>

    <table>
    <caption>스크랩 목록</caption>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">게시판</th>
        <th scope="col">제목</th>
        <th scope="col">보관일시</th>
        <th scope="col">삭제</th>
    </tr>
    </thead>
    <tbody>
    <? for ($i=0; $i<count($list); $i++) { ?>
    <tr>
        <td class="td_num"><?=$list[$i][num]?></td>
        <td class="td_board"><a href="javascript:;" onclick="opener.document.location.href='<?=$list[$i][opener_href]?>';"><?=$list[$i][bo_subject]?></a></td>
        <td><a href="javascript:;" onclick="opener.document.location.href='<?=$list[$i][opener_href_wr_id]?>';"><?=$list[$i][subject]?></a></td>
        <td class="td_datetime"><?=$list[$i][ms_datetime]?></td>
        <td class="td_mng"><a href="javascript:del('<?=$list[$i][del_href]?>');">삭제</a></td>
    </tr>
    <? } ?>

    <? if ($i == 0) echo "<tr><td colspan=\"5\" class=\"empty_table\">자료가 없습니다.</td></tr>"; ?>
    </tbody>
    </table>

    <div class="pg">
        <?=get_paging($config[cf_write_pages], $page, $total_page, "?$qstr&amp;page=");?>
    </div>

    <div class="btn_window">
        <a href="javascript:window.close();">창닫기</a>
    </div>
</div>