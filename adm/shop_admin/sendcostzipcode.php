<?php
$sub_menu = '400750';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = "우편번호 찾기";
include_once(G5_PATH.'/head.sub.php');
?>

<script src="<?php echo G5_JS_URL; ?>/zip.js"></script>

<div id="post_code" class="new_win">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>

    <p>
        주소지의 시도를 선택해주세요.<br>
        검색결과가 많은 경우 시/군/구를 지정하시됩니다.<br>
        (검색결과는 최대 1,000건만 표시됩니다.)
    </p>

    <form name="fzip" method="get" onsubmit="zipcode_call(); return false;" autocomplete="off">
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
        <input type="submit" value="검색" class="btn_submit">
    </div>
    <!-- } 검색어 입력 끝 -->
    </form>

    <div id="result"><span id="result_b4">시도 선택 후 검색해주세요.</span></div>

    <div class="btn_confirm01 btn_confirm">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<script>
function zipcode_call(page)
{
    var sido = $("#sido").val();
    var gugun = $("#gugun").val();

    if(sido.length < 1) {
        alert("시도를 선택해 주세요.");
        return false;
    }

    if(!page)
        page = 1;

    $.getJSON("//juso.sir.co.kr/zipcode.php?sido="+sido+"&gugun="+gugun+"&sort=<?php echo $sort; ?>&page="+page+"&callback=?",
        function(data) {
            $("#result").empty();

            if(data.error) {
                alert(data.error);
                return false;
            }

            $("#result").html(data.juso);
        }
    );
}

function put_data(zipcode)
{
    var of = window.opener.document.fsendcost2;

    of.sc_zip<?php echo $no; ?>.value = zipcode;
    window.close();
}
</script>

<?php
include_once(G5_PATH."/tail.sub.php");
?>