<?
$sub_menu = '500120';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '주문내역출력';
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<section class="cbox">
    <h2>주문내역출력</h2>
    <p>기간별 혹은 주문번호구간별 주문내역을 새창으로 출력할 수 있습니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">기간별 출력</th>
        <td>
            <form name="forderprint" action="./orderprintresult.php" onsubmit="return forderprintcheck(this);" autocomplete="off">
            <input type="hidden" name="case" value="1">

            <input type="radio" name="csv" value="xls" id="xls1"><label for="xls1">MS엑셀 XLS 데이터</label>
            <input type="radio" name="csv" value="csv" id="csv1"><label for="csv1">MS엑셀 CSV 데이터</label>
            <div>
                <label for="ct_status_p" class="sound_only">출력대상</label>
                <select name="ct_status" id="ct_status_p">
                    <option value="주문">주문</option>
                    <option value="준비">상품준비중</option>
                    <option value="배송">배송</option>
                    <option value="완료">완료</option>
                    <option value="취소">취소</option>
                    <option value="반품">반품</option>
                    <option value="품절">품절</option>
                    <option value="">전체</option>
                </select>
                <input type="text" name="fr_date" value="<?=date("Ymd");?>" id="fr_date" class="frm_input" size="10" maxlength="8"> 부터
                <input type="text" name="to_date" value="<?=date("Ymd");?>" id="to_date" class="frm_input" size="10" maxlength="8"> 까지
                <button type="submit" class="btn_frmline">출력 (새창)</button>
            </div>

            </form>
        </td>
    </tr>
    <tr>
        <th scope="row">주문번호구간별 출력</th>
        <td>
            <form name="forderprint" action="./orderprintresult.php" onsubmit="return forderprintcheck(this);" autocomplete="off" >
            <input type="hidden" name="case" value="2">

            <input type="radio" name="csv" value="xls" id="xls2"><label for="xls2">MS엑셀 XLS 데이터</label>
            <input type="radio" name="csv" value="csv" id="csv2"><label for="csv2">MS엑셀 CSV 데이터</label>
            <div>
                <label for="ct_status_n" class="sound_only">출력대상</label>
                <select name="ct_status" id="ct_status_n">
                    <option value="주문">주문</option>
                    <option value="준비">상품준비중</option>
                    <option value="배송">배송</option>
                    <option value="완료">완료</option>
                    <option value="취소">취소</option>
                    <option value="반품">반품</option>
                    <option value="품절">품절</option>
                    <option value="">전체</option>
                </select>
                <input type="text" name="fr_od_id" class="frm_input" size="10" maxlength="10"> 부터
                <input type="text" name="to_od_id" class="frm_input" size="10" maxlength="10"> 까지
                <button type="submit" class="btn_frmline">출력 (새창)</button>
            </div>

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
