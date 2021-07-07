<?php
include_once('./_common.php');

if(!$is_member)
    alert_close('회원이시라면 회원로그인 후 이용해 주십시오.');

$ad_id = isset($_REQUEST['ad_id']) ? (int) $_REQUEST['ad_id'] : 0;

if($w == 'd') {
    $sql = " delete from {$g5['g5_shop_order_address_table']} where mb_id = '{$member['mb_id']}' and ad_id = '$ad_id' ";
    sql_query($sql);
    goto_url($_SERVER['SCRIPT_NAME']);
}

$sql_common = " from {$g5['g5_shop_order_address_table']} where mb_id = '{$member['mb_id']}' ";

$sql = " select count(ad_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            $sql_common
            order by ad_default desc, ad_id desc
            limit $from_record, $rows";

$result = sql_query($sql);

if(!sql_num_rows($result))
    alert_close('배송지 목록 자료가 없습니다.');

$order_action_url = G5_HTTPS_SHOP_URL.'/orderaddressupdate.php';

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/orderaddress.php');
    return;
}

// 테마에 orderaddress.php 있으면 include
if(defined('G5_THEME_SHOP_PATH')) {
    $theme_orderaddress_file = G5_THEME_SHOP_PATH.'/orderaddress.php';
    if(is_file($theme_orderaddress_file)) {
        include_once($theme_orderaddress_file);
        return;
        unset($theme_orderaddress_file);
    }
}

$g5['title'] = '배송지 목록';
include_once(G5_PATH.'/head.sub.php');
?>
<form name="forderaddress" method="post" action="<?php echo $order_action_url; ?>" autocomplete="off">
<div id="sod_addr" class="new_win">

    <h1 id="win_title">배송지 목록</h1>
    <div class="tbl_head03 tbl_wrap">
        <table>
        <thead>
        <tr>
            <th scope="col">배송지명</th>
            <th scope="col">이름</th>
            <th scope="col">배송지정보</th>
            <th scope="col">관리</th>
        </tr>
 
        </thead>
        <tbody>
        <?php
        $sep = chr(30);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $addr = $row['ad_name'].$sep.$row['ad_tel'].$sep.$row['ad_hp'].$sep.$row['ad_zip1'].$sep.$row['ad_zip2'].$sep.$row['ad_addr1'].$sep.$row['ad_addr2'].$sep.$row['ad_addr3'].$sep.$row['ad_jibeon'].$sep.$row['ad_subject'];
            $addr = get_text($addr);
        ?>
        <tr>
            <td class="td_sbj">
            	<div class="chk_box">
                	<input type="hidden" name="ad_id[<?php echo $i; ?>]" value="<?php echo $row['ad_id'];?>">
                	<input type="checkbox" name="chk[]" value="<?php echo $i;?>" id="chk_<?php echo $i;?>" class="selec_chk">
                	<label for="chk_<?php echo $i;?>"><span></span><b class="sound_only">배송지선택</b></label>
                </div>
                
                <label for="ad_subject<?php echo $i;?>" class="sound_only">배송지명</label>
                <input type="text" name="ad_subject[<?php echo $i; ?>]" id="ad_subject<?php echo $i;?>" class="frm_input" size="12" maxlength="20" value="<?php echo get_text($row['ad_subject']); ?>">
            </td>

            <td class="td_name"><?php echo get_text($row['ad_name']); ?></td>
            <td class="td_address">
                <?php echo print_address($row['ad_addr1'], $row['ad_addr2'], $row['ad_addr3'], $row['ad_jibeon']); ?><br>
                <span class="ad_tel"><?php echo $row['ad_tel']; ?> / <?php echo $row['ad_hp']; ?></span>

            </td>
            <td class="td_mng">
                <input type="hidden" value="<?php echo $addr; ?>" >
                <button type="button" class="sel_address mng_btn">선택</button>
                <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?w=d&amp;ad_id=<?php echo $row['ad_id']; ?>" class="del_address mng_btn">삭제</a>
                <input type="radio" name="ad_default" value="<?php echo $row['ad_id'];?>" id="ad_default<?php echo $i;?>" <?php if($row['ad_default']) echo 'checked="checked"';?>>
                <label for="ad_default<?php echo $i;?>" class="default_lb mng_btn">기본배송지</label>

            </td>
        </tr>

        <?php
        }
        ?>
        </tbody>
        </table>
    </div>

    <div class="win_btn">
        <input type="submit" name="act_button" value="선택수정" class="btn_submit">
        <button type="button" onclick="self.close();" class="btn_close">닫기</button>
    </div>
</div>
</form>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $(".sel_address").on("click", function() {
        var addr = $(this).siblings("input").val().split(String.fromCharCode(30));

        var f = window.opener.forderform;
        f.od_b_name.value        = addr[0];
        f.od_b_tel.value         = addr[1];
        f.od_b_hp.value          = addr[2];
        f.od_b_zip.value         = addr[3] + addr[4];
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