<?
if (!defined("_GNUBOARD_")) exit;

include_once($g4['path'].'/lib/visit.lib.php');

if (empty($fr_date)) $fr_date = $g4['time_ymd'];
if (empty($to_date)) $to_date = $g4['time_ymd'];

$qstr = 'fr_date='.$fr_date.'&amp;to_date='.$to_date;
?>

<form id="fvisit" name="fvisit" method="get">
<fieldset>
    <legend>접속자집계 열람조건 지정</legend>
    <span>기간지정 <input type="text" id="fr_date" name="fr_date" size=11 maxlength=10 value='<?=$fr_date?>'> 부터 <input type="text" id="to_date" name="to_date" size=11 maxlength=10 value='<?=$to_date?>'> 까지</span>
    <button onclick="fvisit_submit('visit_list.php');">접속자</button>
    <button onclick="fvisit_submit('visit_domain.php');">도메인</button>
    <button onclick="fvisit_submit('visit_browser.php');">브라우저</button>
    <button onclick="fvisit_submit('visit_os.php');">운영체제</button>
    <button onclick="fvisit_submit('visit_hour.php');">시간</button>
    <button onclick="fvisit_submit('visit_week.php');">요일</button>
    <button onclick="fvisit_submit('visit_date.php');">일</button>
    <button onclick="fvisit_submit('visit_month.php');">월</button>
    <button onclick="fvisit_submit('visit_year.php');">년</button>
</fieldset>
</form>

<script>
function fvisit_submit(act)
{
    var f = document.fvisit;
    f.action = act;
    f.submit();
}
</script>
