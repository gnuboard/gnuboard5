<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<!-- 우편번호 찾기 시작 { -->
<link rel="stylesheet" href="<?php echo $member_skin_url ?>/style.css">
<script src="<?php echo G5_JS_URL ?>/zip.js"></script>

<div id="post_code" class="new_win mbskin">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>

    <p>
        시도 및 시군구 선택없이 도로명, 읍/면/동, 건물명 등으로 검색하실 수 있습니다.<br>
        만약 검색결과에 찾으시는 주소가 없을 때는 시도와 시군구를 선택하신 후 다시 검색해 주십시오.<br>
        (검색결과는 최대 1,000건만 표시됩니다.)
    </p>

    <form name="fzip" method="get" onsubmit="search_call(); return false;" autocomplete="off">
    <!-- 검색어 입력 시작 { -->
    <div id="code_sch">
        <label for="sido" class="sound_only">시도선택</label>
        <select name="sido" id="sido">
            <option value="">- 시도 선택 -</option>
        </select>
        <label for="gugun" class="sound_only">시군구</label>
        <select name="gugun" id="gugun">
            <option value="">- 시군구 선택 -</option>
        </select>
        <div>
            <label for="q" class="sound_only">검색어</label>
            <input type="text" name="q" value="" id="q" required  class="required frm_input">
            <input type="submit" value="검색" class="btn_submit">
        </div>
    </div>
    <!-- } 검색어 입력 끝 -->

    <div id="result"><span id="result_b4">검색어를 입력해주세요.</span></div>

    <div class="win_btn">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<script>
function put_data(zip1, zip2, addr1, addr2, jibeon)
{
    var of = window.opener.document.<?php echo $frm_name; ?>;

    of.<?php echo $frm_zip1; ?>.value = zip1;
    of.<?php echo $frm_zip2; ?>.value = zip2;
    of.<?php echo $frm_addr1; ?>.value = addr1;
    of.<?php echo $frm_addr2; ?>.value = addr2;

    window.opener.document.getElementById("<?php echo $frm_jibeon; ?>").innerText = "지번주소 : "+jibeon;

    if(of.<?php echo $frm_jibeon; ?> !== undefined)
        of.<?php echo $frm_jibeon; ?>.value = jibeon;

    window.close();
}
</script>
<!-- } 우편번호 찾기 끝 -->