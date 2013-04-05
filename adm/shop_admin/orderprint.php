<?
$sub_menu = '500120';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '주문내역출력';
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<section class="cbox">
    <h2>주문내역출력</h2>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="gird_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">출력기간</th>
        <td>
            <form name="forderprint" action="./orderprintresult.php" onsubmit="return forderprintcheck(this);" autocomplete="off">
            <input type="hidden" name="case" value="1">

            <input type="radio" name="csv" value="xls" id="xls1"><label for="xls1">MS엑셀 XLS 데이터</label>
            <input type="radio" name="csv" value="csv" id="csv1"><label for="csv1">MS엑셀 CSV 데이터</label>
                        <input type="text" name="fr_date" value="<?=date("Ymd");?>" id="fr_date" class="frm_input" size="10" maxlength="8">
            <a href="javascript:win_calendar('fr_date', document.getElementById('fr_date').value, '');">
            <img src="<?=G4_URL?>/img/calendar.gif" alt="달력 - 날짜를 선택하세요">
            </a>
            ∼
            <input type="text" name="to_date" value="<?=date("Ymd");?>" id="to_date" class="frm_input" size="10" maxlength="8">
            <a href="javascript:win_calendar('to_date', document.getElementById('to_date').value, '');">
            <img src="<?=G4_URL?>/img/calendar.gif" alt="달력 - 날짜를 선택하세요">
            </a>
            &nbsp;
            <select name="ct_status">
                <option value="주문">주문</option>
                <option value="준비">상품준비중</option>
                <option value="배송">배송</option>
                <option value="완료">완료</option>
                <option value="취소">취소</option>
                <option value="반품">반품</option>
                <option value="품절">품절</option>
                <option value="전체">전체</option>
            </select>
            <input type="submit" value="확  인">
            </form>
        </td>
    </tr>
    <tr>
        <th scope="row">주문번호구간</th>
        <td>
            <form name="forderprint" action="./orderprintresult.php" onsubmit="return forderprintcheck(this);" autocomplete="off" ><!--form시작-->
            <input type="hidden" name="case" value="2">
            <input type="radio" name="csv" value="xls" id="xls2"><label for="xls2">MS엑셀 XLS 데이터</label>
            <input type="radio" name="csv" value="csv" id="csv2"><label for="csv2">MS엑셀 CSV 데이터</label>
            <input type="text" name="fr_od_id" class="frm_input" size="10" maxlength="10"> 부터
            <input type="text" name="to_od_id" class="frm_input" size="10" maxlength="10"> 까지
            <select name="ct_status">
                <option value="주문">주문</option>
                <option value="준비">상품준비중</option>
                <option value="배송">배송</option>
                <option value="완료">완료</option>
                <option value="취소">취소</option>
                <option value="반품">반품</option>
                <option value="품절">품절</option>
                <option value="전체">전체</option>
            </select>
            <input type="submit" value="확  인">
            </form>
        </td>
    </tr>
    </tbody>
    </table>
</section>




<script>
function forderprintcheck(f)
{
    if (f.csv[0].checked || f.csv[1].checked)
    {
        f.target = "_top";
    }
    else
    {
        var win = window.open("", "winprint", "left=10,top=10,width=670,height=800,menubar=yes,toolbar=yes,scrollbars=yes");
        f.target = "winprint";
    }

    f.submit();
}
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
