<?
$sub_menu = '500110';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '매출현황';
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<section id="ssale_stats" class="cbox">
    <h2>조건별 매출현황 확인</h2>

    <div>
        <h3>일일 매출현황</h3>
        <form name="frm_sale_today" action="./sale1today.php" method="get">
        <input type="text" name="date" value="<? echo date("Ymd", G4_SERVER_TIME) ?>" id="date" class="frm_input" size="8" maxlength="8">
        <label for="date">일 하루</label>
        <input type="submit" value="확인" class="btn_submit">
        </form>
    </div>

    <div>
        <h3>일간 매출현황</h3>
        <form name="frm_sale_date" action="./sale1date.php" method="get">
        <input type="text" name="fr_date" value="<? echo date("Ym01", G4_SERVER_TIME) ?>" id="fr_date" class="frm_input" size="8" maxlength="8">
        <label for="fr_date">일 부터</label>
        <input type="text" name="to_date" value="<? echo date("Ymd", G4_SERVER_TIME) ?>" id="to_date" class="frm_input" size="8" maxlength="8">
        <label for="to_date">일 까지</label>
        <input type="submit" value="확인" class="btn_submit">
        </form>
    </div>

    <div>
        <h3>월간 매출현황</h3>
        <form name="frm_sale_month" action="./sale1month.php" method="get">
        <input type="text" name="fr_month" value="<? echo date("Y01", G4_SERVER_TIME) ?>" id="fr_month" class="frm_input" size="6" maxlength="6">
        <label for="fr_month">월 부터</label>
        <input type="text" name="to_month" value="<? echo date("Ym", G4_SERVER_TIME) ?>" id="to_month" class="frm_input" size="6" maxlength="6">
        <label for="to_month">월 까지</label>
        <input type="submit" value="확인" class="btn_submit">
        </form>
    </div>

    <div>
        <h3>연간 매출현황</h3>
        <form name="frm_sale_year" action="./sale1year.php" method="get">
        <input type="text" name="fr_year" value="<? echo date("Y", G4_SERVER_TIME)-1 ?>" id="fr_year" class="frm_input" size="4" maxlength="4">
        <label for="fr_year">년 부터</label>
        <input type="text" name="to_year" value="<? echo date("Y", G4_SERVER_TIME) ?>" id="to_year" class="frm_input" size="4" maxlength="4">
        <label for="to_year">년 까지</label>
        <input type="submit" value="확인" class="btn_submit">
        </form>
    </div>

</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
