<?php
include_once('./_common.php');

$g5['title'] = '배송지 목록';
include_once(G5_PATH.'/head.sub.php');

$order_action_url = G5_HTTPS_SHOP_URL.'/orderaddressupdate.php';
?>

<form name="forderaddress" method="post" action="<?php echo $order_action_url; ?>" autocomplete="off">
<div id="sod_addr" class="new_win">
    <h1 id="win_title">배송지 목록</h1>

    <div class="win_desc">
        <ul>
            <?php
            $sep = chr(30);
            for($i=0; $row=sql_fetch_array($result); $i++) {
                $addr = $row['ad_name'].$sep.$row['ad_tel'].$sep.$row['ad_hp'].$sep.$row['ad_zip1'].$sep.$row['ad_zip2'].$sep.$row['ad_addr1'].$sep.$row['ad_addr2'].$sep.$row['ad_addr3'].$sep.$row['ad_jibeon'].$sep.$row['ad_subject'];
            ?>
            <li>
                <div class="addr_chk" rowspan="3">
                    <input type="hidden" name="ad_id[<?php echo $i; ?>]" value="<?php echo $row['ad_id'];?>">
                    <label for="chk_<?php echo $i;?>" class="sound_only">배송지선택</label>
                    <input type="checkbox" name="chk[]" value="<?php echo $i;?>" id="chk_<?php echo $i;?>">
                </div>
                <div class="addr_title">
                    <label for="ad_subject<?php echo $i;?>" class="sound_only">배송지명</label>
                    <input type="text" name="ad_subject[<?php echo $i; ?>]" value="<?php echo $row['ad_subject']; ?>" id="ad_subject" class="frm_input" maxlength="20">
                </div>
                <div class="addr_default">
                    <input type="radio" name="ad_default" value="<?php echo $row['ad_id'];?>" id="ad_default<?php echo $i;?>" <?php if($row['ad_default']) echo 'checked="checked"';?>>
                    <label for="ad_default<?php echo $i;?>">기본배송지 설정</label>
                </div>
                <div class="addr_addr"><?php echo print_address($row['ad_addr1'], $row['ad_addr2'], $row['ad_addr3'], $row['ad_jibeon']); ?></div>
                <div class="addr_name"><?php echo $row['ad_name']; ?></div>
                <div class="addr_tel"><?php echo $row['ad_tel']; ?> / <?php echo $row['ad_hp']; ?></div>
                <div class="addr_btn">
                    <input type="hidden" value="<?php echo $addr; ?>">
                    <button type="button" id="btn_sel" class="sel_address">선택</button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?w=d&amp;ad_id=<?php echo $row['ad_id']; ?>" id="btn_del" class="del_address">삭제</a>
                </div>
            </li>
            <?php
            }
            ?>
        </ul>
    </div>

    <div class="win_btn">
        <input type="submit" name="act_button" value="선택수정" class="btn_submit">
        <button type="button" onclick="self.close();">닫기</button>
    </div>
</div>
</form>

<?php echo get_paging($config['cf_mobile_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $(".sel_address").on("click", function() {
        var addr = $(this).siblings("input").val().split(String.fromCharCode(30));

        var f = window.opener.forderform;
        f.od_b_name.value        = addr[0];
        f.od_b_tel.value         = addr[1];
        f.od_b_hp.value          = addr[2];
        f.od_b_zip1.value        = addr[3];
        f.od_b_zip2.value        = addr[4];
        f.od_b_addr1.value       = addr[5];
        f.od_b_addr2.value       = addr[6];
        f.od_b_addr3.value       = addr[7];
        f.od_b_addr_jibeon.value = addr[8];
        f.ad_subject.value       = addr[9];

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

    // 전체선택 부분
    $("#chk_all").on("click", function() {
        if($(this).is(":checked")) {
            $("input[name^='chk[']").attr("checked", true);
        } else {
            $("input[name^='chk[']").attr("checked", false);
        }
    });

    $(".btn_submit").on("click", function() {
        if($("input[name^='chk[']:checked").length==0 ){
            alert("수정하실 항목을 하나 이상 선택하세요.");
            return false;
        }
    });

});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>