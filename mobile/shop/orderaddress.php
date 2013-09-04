<?php
include_once('./_common.php');

$g4['title'] = '배송지 목록';
include_once(G4_PATH.'/head.sub.php');
?>

<div>
    <table>
    <thead>
    <tr>
        <td>배송지명</th>
        <td>이름</td>
        <td>전화번호</td>
        <td>주소</td>
        <td>관리</td>
    </tr>
    </thead>
    <tbody>
    <?php
    $sep = chr(30);
    for($i=0; $row=sql_fetch_array($result); $i++) {
        $addr = $row['ad_name'].$sep.$row['ad_tel'].$sep.$row['ad_hp'].$sep.$row['ad_zip1'].$sep.$row['ad_zip2'].$sep.$row['ad_addr1'].$sep.$row['ad_addr2'].$sep.$row['ad_subject'];
    ?>
    <tr>
        <td><?php echo $row['ad_subject']; ?></td>
        <td><?php echo $row['ad_name']; ?></td>
        <td><?php echo $row['ad_tel']; ?><br><?php echo $row['ad_hp']; ?></td>
        <td><?php echo sprintf('%s %s', $row['ad_addr1'], $row['ad_addr2']); ?></td>
        <td>
            <input type="hidden" value="<?php echo $addr; ?>">
            <button type="button" class="sel_address">선택</button>
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