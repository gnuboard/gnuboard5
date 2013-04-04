<?
$sub_menu = '500110';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '매출현황';
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>
<style type="text/css">
    .sale1 {text-align:right}
</style>
<section class="cbox">
    <h2>매출현황</h2>
    <table class="frm_tbl">
    <tbody>
    <tr>
        <form name="frm_sale_today" action="./sale1today.php">
        <th scope="row"> 당일 매출현황</th>
        <td class="sale1">
            <input type="text" name="date" size="8" maxlength="8" value="<? echo date("Ymd", G4_SERVER_TIME) ?>" id="date" class="frm_input">
            <label for="date">일 하루</label>
            <input type="submit" value="확  인">
        </td>
        </form>
    </tr>
    <tr>
        <form name="frm_sale_date" action="./sale1date.php">
        <th scope="row">일별 매출현황</th>
        <td class="sale1">
            <input type="text" name="fr_date" value="<? echo date("Ym01", G4_SERVER_TIME) ?>" id="fr_date" class="frm_input" size="8" maxlength="8">
            <label for="fr_date">일 부터</label>
            <input type="text" name="to_date" value="<? echo date("Ymd", G4_SERVER_TIME) ?>" id="to_date" class="frm_input" size="8" maxlength="8">
            <label for="to_date">일 까지</label>
            <input type="submit" value="확  인">
        </td>
        </form>
    </tr>
    <tr>
        <form name="frm_sale_month" action="./sale1month.php">
        <th scope="row">월별 매출현황</th>
        <td class="sale1">
        <input type="text" name="fr_month" value="<? echo date("Y01", G4_SERVER_TIME) ?>" id="fr_month" class="frm_input" size="6" maxlength="6">
        <label for="fr_month">월 부터</label>
        <input type="text" name="to_month" value="<? echo date("Ym", G4_SERVER_TIME) ?>" id="to_month" class="frm_input" size="6" maxlength="6">
        <label for="to_month">월 까지</label>
        <input type="submit" value="확  인">
        </td>
        </form>
    </tr>
    <tr>
        <form name="frm_sale_year" action="./sale1year.php">
        <th scope="row">연별 매출현황</th>
        <td class="sale1">
            <input type="text" name="fr_year" size="4" maxlength="4" value="<? echo date("Y", G4_SERVER_TIME)-1 ?>"id="fr_year" class="frm_input">
            <label for="fr_year">년 부터</label>
            <input type="text" name="to_year" size="4" maxlength="4" value="<? echo date("Y", G4_SERVER_TIME) ?>" id="to_year" class="frm_input">
            <label for="to_year">년 까지</label>
            <input type="submit" value="확  인">
        </td>
        </form>
    </tr>
    </tbody>
    </table>
</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
