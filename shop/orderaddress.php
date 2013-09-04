<?php
include_once('./_common.php');

if(!$is_member)
    alert_close('회원이시라면 회원로그인 후 이용해 주십시오.');

if($w == 'd') {
    $sql = " delete from {$g4['shop_order_address_table']} where mb_id = '{$member['mb_id']}' and ad_id = '$ad_id' ";
    sql_query($sql);
    goto_url($_SERVER['PHP_SELF']);
}

$sql = " select *
            from {$g4['shop_order_address_table']}
            where mb_id = '{$member['mb_id']}'
            order by ad_default, ad_id desc ";
$result = sql_query($sql);

if(!mysql_num_rows($result))
    alert_close('배송지 목록 자료가 없습니다.');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/orderaddress.php');
    return;
}

$g4['title'] = '배송지 목록';
include_once(G4_PATH.'/head.sub.php');
?>

<div id="sod_addr_list" class="new_win">

    <h1 id="new_win_title">배송지 목록</h1>

    <table class="basic_tbl">
    <thead>
    <tr>
        <th scope="col">배송지명</th>
        <th scope="col">이름</th>
        <th scope="col">전화번호</th>
        <th scope="col">주소</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sep = chr(30);
    for($i=0; $row=sql_fetch_array($result); $i++) {
        $addr = $row['ad_name'].$sep.$row['ad_tel'].$sep.$row['ad_hp'].$sep.$row['ad_zip1'].$sep.$row['ad_zip2'].$sep.$row['ad_addr1'].$sep.$row['ad_addr2'].$sep.$row['ad_subject'];
    ?>
    <tr>
        <td class="td_name"><?php echo $row['ad_subject']; ?></td>
        <td class="td_smallname"><?php echo $row['ad_name']; ?></td>
        <td class="td_num"><?php echo $row['ad_tel']; ?><br><?php echo $row['ad_hp']; ?></td>
        <td><?php echo sprintf('%s %s', $row['ad_addr1'], $row['ad_addr2']); ?></td>
        <td class="td_mng">
            <input type="hidden" value="<?php echo $addr; ?>">
            <button type="button" class="sel_address btn_frmline">선택</button>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?w=d&amp;ad_id=<?php echo $row['ad_id']; ?>" class="del_address">삭제</a>
        </td>
    </tr>
    <?php
    }
    ?>
    </tbody>
    </table>
</div>

<script>
$(function() {
    $(".sel_address").on("click", function() {
        var addr = $(this).siblings("input").val().split(String.fromCharCode(30));

        var f = window.opener.forderform;
        f.od_b_name.value   = addr[0];
        f.od_b_tel.value    = addr[1];
        f.od_b_hp.value     = addr[2];
        f.od_b_zip1.value   = addr[3];
        f.od_b_zip2.value   = addr[4];
        f.od_b_addr1.value  = addr[5];
        f.od_b_addr2.value  = addr[6];
        f.ad_subject.value  = addr[7];

        var zip1 = addr[3].replace(/[^0-9]/g, "");
        var zip2 = addr[4].replace(/[^0-9]/g, "");

        if(zip1 != "" && zip2 != "") {
            var code = String(zip1) + String(zip2);

            if(window.opener.zipcode != code) {
                window.opener.zipcode = code;
                window.opener.calculate_sendcost(code);
            }
        }

        window.close();
    });

    $(".del_address").on("click", function() {
        return confirm("배송지 목록을 삭제하시겠습니까?");
    });
});
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>