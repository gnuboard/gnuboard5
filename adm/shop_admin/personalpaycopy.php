<?php
$sub_menu = '400440';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$g5['title'] = '개인결제 복사';
include_once(G5_PATH.'/head.sub.php');

$sql = " select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$pp_id' ";
$row = sql_fetch($sql);

if(!$row['pp_id'])
    alert_close('복사하시려는 개인결제 정보가 존재하지 않습니다.');
?>

<div class="new_win">
    <h1>개인결제 복사</h1>

    <form name="fpersonalpaycopy" method="post" action="./personalpaycopyupdate.php" onsubmit="return form_check(this);">
    <input type="hidden" name="pp_id" value="<?php echo $pp_id; ?>">

     <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption><?php echo $g5['title']; ?></caption>
        <tbody>
        <tr>
            <th scope="row"><label for="pp_name">이름</label></th>
            <td><input type="text" name="pp_name" value="<?php echo $row['pp_name']; ?>" id="pp_name" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_id">주문번호</label></th>
            <td><input type="text" name="od_id" value="<?php echo $row['od_id']; ?>" id="od_id" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="pp_price">주문금액</label></th>
            <td><input type="text" name="pp_price" value="" id="pp_price" required class="required frm_input" size="20"> 원</td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="복사하기" class="btn_submit">
        <button type="button" onclick="self.close();">창닫기</button>
    </div>

    </form>

</div>

<script>
// <![CDATA[
function form_check(f)
{
    if(f.pp_price.value.replace(/[0-9]/g, "").length > 0) {
        alert("주문금액은 숫자만 입력해 주십시오");
        return false;
    }

    return true;
}
// ]]>
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>