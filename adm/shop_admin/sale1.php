<?php
$sub_menu = '500110';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '매출현황';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<div class="local_sch03 local_sch">

    <div>
        <form name="frm_sale_today" action="./sale1today.php" method="get">
        <strong>일일 매출</strong>
        <input type="text" name="date" value="<?php echo date("Ymd", G5_SERVER_TIME); ?>" id="date" required class="required frm_input" size="8" maxlength="8">
        <label for="date">일 하루</label>
        <input type="submit" value="확인" class="btn_submit">
        </form>
    </div>

    <div>
        <form name="frm_sale_date" action="./sale1date.php" method="get">
        <strong>일간 매출</strong>
        <input type="text" name="fr_date" value="<?php echo date("Ym01", G5_SERVER_TIME); ?>" id="fr_date" required class="required frm_input" size="8" maxlength="8">
        <label for="fr_date">일 ~</label>
        <input type="text" name="to_date" value="<?php echo date("Ymd", G5_SERVER_TIME); ?>" id="to_date" required class="required frm_input" size="8" maxlength="8">
        <label for="to_date">일</label>
        <input type="submit" value="확인" class="btn_submit">
        </form>
    </div>

    <div>
        <form name="frm_sale_month" action="./sale1month.php" method="get">
        <strong>월간 매출</strong>
        <input type="text" name="fr_month" value="<?php echo date("Y01", G5_SERVER_TIME); ?>" id="fr_month" required class="required frm_input" size="6" maxlength="6">
        <label for="fr_month">월 ~</label>
        <input type="text" name="to_month" value="<?php echo date("Ym", G5_SERVER_TIME); ?>" id="to_month" required class="required frm_input" size="6" maxlength="6">
        <label for="to_month">월</label>
        <input type="submit" value="확인" class="btn_submit">
        </form>
    </div>

    <div class="sch_last">
        <form name="frm_sale_year" action="./sale1year.php" method="get">
        <strong>연간 매출</strong>
        <input type="text" name="fr_year" value="<?php echo date("Y", G5_SERVER_TIME)-1; ?>" id="fr_year" required class="required frm_input" size="4" maxlength="4">
        <label for="fr_year">년 ~</label>
        <input type="text" name="to_year" value="<?php echo date("Y", G5_SERVER_TIME); ?>" id="to_year" required class="required frm_input" size="4" maxlength="4">
        <label for="to_year">년</label>
        <input type="submit" value="확인" class="btn_submit">
        </form>
    </div>

</div>

<script>
$(function() {
    $("#date, #fr_date, #to_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yymmdd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+0d"
    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');