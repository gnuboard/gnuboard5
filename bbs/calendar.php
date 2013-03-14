<?
include_once("./_common.php");

$g4[title] = "달력";
include_once("$g4[path]/head.sub.php");

// 글자 색상
$weekday_color = "#000000"; // 평일
$saturday_color = "#000000"; // 토요일
$sunday_color = "#FF3300"; // 일요일 (공휴일)
// 배경 색상
$today_bgcolor = "yellow"; // 오늘 
$select_bgcolor = "#BAFFF6"; // 선택일

// 요일
$yoil = array ("일", "월", "화", "수", "목", "금", "토");

// mktime() 함수는 1970 ~ 2038년까지만 계산되므로 사용하지 않음
// 참고 : http://phpschool.com/bbs2/inc_view.html?id=3924&code=tnt2&start=0&mode=search&s_que=mktime&field=title&operator=and&period=all
function spacer($year, $month)
{
    $day = 1;
    $spacer = array(0, 3, 2, 5, 0, 3, 5, 1, 4, 6, 2, 4);
    $year = $year - ($month < 3);
    $result = ($year + (int) ($year/4) - (int) ($year/100) + (int) ($year/400) + $spacer[$month-1] + $day) % 7;
    return $result;
}

// 오늘
$today = getdate($g4[server_time]);
$mon  = substr("0".$today[mon],-2);
$mday = substr("0".$today[mday],-2);

// delimiter 를 없앤다
$cur_date = preg_replace("/([^0-9]*)/", "", $cur_date);

if ($cur_date && !$yyyy)
{
    $yyyy = substr($cur_date,0,4);
    $mm = substr($cur_date,4,2);
}
else
{
    if (!$yyyy) $yyyy = $today['year'];
    if (!$mm) $mm = $today['mon'];
}
$yyyy = (int)$yyyy;
$mm = (int)$mm;

$f = @file("./calendar/$yyyy.txt");
if ($f) {
    while ($line = each($f)) {
        $tmp = explode("|", $line[value]);
        $nal[$tmp[0]] = $tmp;
        //print_r2($nal);
    }
}

$spacer = spacer($yyyy, $mm);

$endday = array(1=>31, 28, 31, 30 , 31, 30, 31, 31, 30 ,31 ,30, 31);
// 윤년 계산 부분이다. 4년에 한번꼴로 2월이 28일이 아닌 29일이 있다.
if( $yyyy%4 == 0 && $yyyy%100 != 0 || $yyyy%400 == 0 )
    $endday[2] = 29; // 조건에 적합할 경우 28을 29로 변경

// 해당월의 1일
$mktime = mktime(0,0,0,$mm,1,$yyyy);
$dt = getdate(strtotime(date("Y-m-1", $mktime)));

$dt[wday] = $spacer;

// 해당월의 마지막 날짜,
//$last_day = date("t", $mktime);
$last_day = $endday[$mm];

$yyyy_before = $yyyy;
$mm_before = $mm - 1;
if ($mm_before < 1)
{
    $yyyy_before--;
    $mm_before = 12;
}

$yyyy_after = $yyyy;
$mm_after = $mm + 1;
if ($mm_after > 12)
{
    $yyyy_after++;
    $mm_after = 1;
}

$fr_yyyy = $yyyy - 80;
$to_yyyy = $yyyy + 80;

$yyyy_before_href = "$_SERVER[PHP_SELF]?yyyy=".($yyyy-1)."&mm={$mm}&cur_date={$cur_date}&fld={$fld}&delimiter={$delimiter}";
$yyyy_after_href = "$_SERVER[PHP_SELF]?yyyy=".($yyyy+1)."&mm={$mm}&cur_date={$cur_date}&fld={$fld}&delimiter={$delimiter}";

$mm_after_href = "$_SERVER[PHP_SELF]?yyyy={$yyyy_after}&mm={$mm_after}&cur_date={$cur_date}&fld={$fld}&delimiter={$delimiter}";
$mm_before_href = "$_SERVER[PHP_SELF]?yyyy={$yyyy_before}&mm={$mm_before}&cur_date={$cur_date}&fld={$fld}&delimiter={$delimiter}";

$yyyy_select = "<select name=yyyy onchange='document.fcalendar.submit();'>";
for ($i=$fr_yyyy; $i<=$to_yyyy; $i++)
{
    if ($i == $yyyy) $selected = " selected";
    else $selected = "";
    $yyyy_select .= "<option value='{$i}'{$selected}>$i 년</option>";
}
$yyyy_select .= "</select>";

$mm_select = "<select name=mm onchange='document.fcalendar.submit();'>";
for ($i=1; $i<=12; $i++)
{
    if ($i == $mm) $selected = " selected";
    else $selected = "";
    $mm_select .= "<option value='{$i}'{$selected}>$i 월</option>";
}
$mm_select .= "</select>";

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
include_once("$member_skin_path/calendar.skin.php");
?>

<script type="text/javascript">
//
// year : 4자리
// month : 1~2자리
// day : 1~2자리
// wday : 요일 숫자 (0:일 ~ 6:토)
// handay : 요일 한글
//
function date_send(year, month, day, wday, handay)
{
    var delimiter = document.getElementById('delimiter').value;
    opener.document.getElementById('<?=$fld?>').value = year + delimiter + month + delimiter + day;
    window.close();
}
</script>

<?
include_once("$g4[path]/tail.sub.php");
?>
