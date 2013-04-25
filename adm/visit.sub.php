<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G4_PLUGIN_PATH.'/jquery-ui/datepicker.php');
include_once(G4_LIB_PATH.'/visit.lib.php');
include_once('./admin.head.php');

if (empty($fr_date)) $fr_date = G4_TIME_YMD;
if (empty($to_date)) $to_date = G4_TIME_YMD;

$qstr = "fr_date=".$fr_date."&amp;to_date=".$to_date;
$query_string = $qstr ? '?'.$qstr : '';
?>

<ul class="anchor">
    <li><a href="./visit_list.php<?php echo $query_string ?>">접속자</a></li>
    <li><a href="./visit_domain.php<?php echo $query_string ?>">도메인</a></li>
    <li><a href="./visit_browser.php<?php echo $query_string ?>">브라우저</a></li>
    <li><a href="./visit_os.php<?php echo $query_string ?>">운영체제</a></li>
    <li><a href="./visit_hour.php<?php echo $query_string ?>">시간</a></li>
    <li><a href="./visit_week.php<?php echo $query_string ?>">요일</a></li>
    <li><a href="./visit_date.php<?php echo $query_string ?>">일</a></li>
    <li><a href="./visit_month.php<?php echo $query_string ?>">월</a></li>
    <li><a href="./visit_year.php<?php echo $query_string ?>">년</a></li>
</ul>

<form name="fvisit" id="fvisit" method="get">
<fieldset>
    <legend>기간별 접속자집계 검색</legend>
    <input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="11" maxlength="10"> 부터
    <input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="11" maxlength="10"> 까지
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); 
});

function fvisit_submit(act)
{
    var f = document.fvisit;
    f.action = act;
    f.submit();
}
</script>
