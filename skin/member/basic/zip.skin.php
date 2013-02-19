<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
?>

<div id="post_code" class="new_win">
    <h1><?=$g4['title']?></h1>

    <form name="fzip" method="get" autocomplete="off">
    <input type="hidden" name="frm_name"  value="<?=$frm_name?>">
    <input type="hidden" name="frm_zip1"  value="<?=$frm_zip1?>">
    <input type="hidden" name="frm_zip2"  value="<?=$frm_zip2?>">
    <input type="hidden" name="frm_addr1" value="<?=$frm_addr1?>">
    <input type="hidden" name="frm_addr2" value="<?=$frm_addr2?>">

    <fieldset>
        <label for="addr1">동/읍/면/리 검색</label>
        <input type="text" id="addr1" name="addr1" class="fs_input" value="<?=$addr1?>" required minlength=2>
        <input type="submit" class="fs_submit" value="검색">
    </fieldset>

    <!-- 검색결과 여기서부터 -->

    <script>
    document.fzip.addr1.focus();
    </script>


    <? if ($search_count > 0) { ?>
    <dl>
        <dt>총 <?=$search_count?>건 가나다순 정렬</dt>
        <dd>
            <ul>
                <? for ($i=0; $i<count($list); $i++) { ?>
                <li><a href='javascript:;' onclick="find_zip('<?=$list[$i][zip1]?>', '<?=$list[$i][zip2]?>', '<?=$list[$i][addr]?>');"><span class="post_code"><?=$list[$i][zip1]?>-<?=$list[$i][zip2]?></span> <?=$list[$i][addr]?> <?=$list[$i][bunji]?></a></li>
                <? } ?>
            </ul>
        </dd>
    </dl>

    <p>검색결과가 끝났습니다.</p>

    <div class="btn_win">
        <a href="javascript:window.close();">창닫기</a>
    </div>

    <script>
    function find_zip(zip1, zip2, addr1)
    {
        var of = opener.document.<?=$frm_name?>;

        of.<?=$frm_zip1?>.value  = zip1;
        of.<?=$frm_zip2?>.value  = zip2;

        of.<?=$frm_addr1?>.value = addr1;

        of.<?=$frm_addr2?>.focus();
        window.close();
        return false;
    }
    </script>
    <? } ?>
</div>
