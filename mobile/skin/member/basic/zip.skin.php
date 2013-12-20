<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<!-- 우편번호 찾기 시작 { -->
<link rel="stylesheet" href="<?php echo $member_skin_url ?>/style.css">

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
            <option value="서울특별시">서울</option>
            <option value="부산광역시">부산</option>
            <option value="대구광역시">대구</option>
            <option value="인천광역시">인천</option>
            <option value="광주광역시">광주</option>
            <option value="대전광역시">대전</option>
            <option value="울산광역시">울산</option>
            <option value="강원도">강원</option>
            <option value="경기도">경기</option>
            <option value="경상남도">경남</option>
            <option value="경상북도">경북</option>
            <option value="전라남도">전남</option>
            <option value="전라북도">전북</option>
            <option value="제주특별자치도">제주</option>
            <option value="충청남도">충남</option>
            <option value="충청북도">충북</option>
        </select>
        <label for="gugun" class="sound_only">시군구</label>
        <select name="gugun" id="gugun">
            <option value="">- 시군구 선택 -</option>
        </select>
        <div id="sch_q">
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
$(function() {
    $("#sido").on("change", function() {
        var sido = $(this).val();

        gugun_make(sido);
    });
});

function gugun_make(sido)
{
    var gugun  = document.getElementById("gugun");

    gugun.options.length = 1;
    gugun.options[0].value = "";
    gugun.options[0].text  = "- 시군구 선택 -";
    gugun.options[0].selected = true;
    if (!sido) {
        return;
    }

    sojae = sojaeji[sido].split(",");
    gugun.options.length = sojae.length+1;
    for (i=0; i<sojae.length; i++) {
        gugun.options[i+1].value = sojae[i];
        gugun.options[i+1].text = sojae[i];
    }
}

function search_call(page)
{
    var sido = $("#sido").val();
    var gugun = $("#gugun").val();
    var q = $.trim($("#q").val());
    if(!page)
        page = 1;

    $.ajax({
        type: "POST",
        url: "http://juso.sir.co.kr/search.php",
        async: false,
        dataType: "jsonp",
        jsonp: "callback",
        data: {
            "sido": sido,
            "gugun": gugun,
            "page": page,
            "q": q
        },
        success:function(data) {
            $("#result").empty();

            if(data.error) {
                alert(data.error);
                return false;
            }

            $("#result").html(data.juso);
        }
    });
}

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


// 시군구 정보
var sojaeji = new Array();

sojaeji['서울특별시'] = '강남구,강동구,강북구,강서구,관악구,광진구,구로구,금천구,노원구,도봉구,동대문구,동작구,마포구,서대문구,서초구,성동구,성북구,송파구,양천구,영등포구,용산구,은평구,종로구,중구,중랑구';
sojaeji['부산광역시'] = '강서구,금정구,기장군,남구,동구,동래구,부산진구,북구,사상구,사하구,서구,수영구,연제구,영도구,중구,해운대구';
sojaeji['대구광역시'] = '남구,달서구,달성군,동구,북구,서구,수성구,중구';
sojaeji['인천광역시'] = '강화군,계양구,남구,남동구,동구,부평구,서구,연수구,옹진군,중구';
sojaeji['광주광역시'] = '광산구,남구,동구,북구,서구';
sojaeji['대전광역시'] = '대덕구,동구,서구,유성구,중구';
sojaeji['울산광역시'] = '남구,동구,북구,울주군,중구';
sojaeji['강원도']     = '강릉시,고성군,동해시,삼척시,속초시,양구군,양양군,영월군,원주시,인제군,정선군,철원군,춘천시,태백시,평창군,홍천군,화천군,횡성군';
sojaeji['경기도']     = '가평군,고양시 덕양구,고양시 일산동구,고양시 일산서구,과천시,광명시,광주시,구리시,군포시,김포시,남양주시,동두천시,부천시 소사구,부천시 오정구,부천시 원미구,성남시 분당구,성남시 수정구,성남시 중원구,수원시 권선구,수원시 장안구,수원시 팔달구,시흥시,안산시 단원구,안산시 상록구,안성시,안양시 동안구,안양시 만안구,양주군,양평군,여주군,연천군,오산시,용인시,의왕시,의정부시,이천시,파주시,평택시,포천군,하남시,화성시';
sojaeji['경상남도']   = '거제시,거창군,고성군,김해시,남해군,마산시,밀양시,사천시,산청군,양산시,의령군,진주시,진해시,창녕군,창원시 의창구, 창원시 성산구,창원시 마산합포구,창원시 마산회원구,창원시 진해구,통영시,하동군,함안군,함양군,합천군';
sojaeji['경상북도']   = '경산시,경주시,고령군,구미시,군위군,김천시,문경시,봉화군,상주시,성주군,안동시,영덕군,영양군,영주시,영천시,예천군,울릉군,울진군,의성군,청도군,청송군,칠곡군,포항시 남구,포항시 북구';
sojaeji['전라남도']   = '강진군,고흥군,곡성군,광양시,구례군,나주시,담양군,목포시,무안군,보성군,순천시,신안군,여수시,영광군,영암군,완도군,장성군,장흥군,진도군,함평군,해남군,화순군';
sojaeji['전라북도']   = '고창군,군산시,김제시,남원시,무주군,부안군,순창군,완주군,익산시,임실군,장수군,전주시 덕진구,전주시 완산구,정읍시,진안군';
sojaeji['제주특별자치도'] = '남제주군,북제주군,서귀포시,제주시';
sojaeji['충청남도']   = '계룡시,공주시,금산군,논산시,당진군,보령시,부여군,서산시,서천군,아산시,연기군,예산군,천안시,청양군,태안군,홍성군';
sojaeji['충청북도']   = '괴산군,단양군,보은군,영동군,옥천군,음성군,제천시,증평군,진천군,청원군,청주시 상당구,청주시 흥덕구,충주시';
</script>
<!-- } 우편번호 찾기 끝 -->