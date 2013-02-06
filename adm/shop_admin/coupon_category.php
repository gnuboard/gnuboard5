<?php
include_once('./_common.php');

$stx = trim($stx);
if($stx) {
    $sql_common = " from {$g4['yc4_category_table']} ";
    $sql_search = " where ca_use = '1' and ca_nocoupon = '0' and ca_name like '%$stx%' ";
    $sql_order = " order by ca_id asc ";

    $sql = " select ca_id, ca_name
                $sql_common
                $sql_search
                $sql_order ";
    $result = sql_query($sql);
}

$g4['title'] = "카테고리찾기";
include_once(G4_PATH.'/head.sub.php');
?>

<style type="text/css">
<!--
#container { width: 370px; margin: 0 auto; }
form { display: inline; }
.searcharea { text-align: center; }
.resultarea { padding-top: 15px; width: 370px; height: 200px; }
.resultarea .list { width: 350px; height: 250px; overflow-y: auto; }
.resultarea .btn { text-align: center; height: 30px; }
-->
</style>

<div id="container">
    <div class="searcharea">
    <form id="fcategory" method="get" action="./coupon_category.php?w=<? echo $w; ?>">
        <input type="text" id="stx" name="stx" size="30" value="<? echo stripslashes($stx); ?>" />
        <input type="submit" value="검색" />
    </form>
    </div>
    <? if($stx) { ?>
    <div class="resultarea">
    <form id="fresult" method="get">
        <div class="list">
            <table width="330">
            <colgroup width="50" />
            <colgroup width="120" />
            <colgroup width="" />
            <tr>
                <th><input type="checkbox" id="check_all" name="check_all" /></th>
                <th>카테고리코드</th>
                <th>카테고리명</th>
            </tr>
            <?
            for($i=0; $row=sql_fetch_array($result); $i++) {
            ?>
            <tr>
                <td align="center"><input type="checkbox" id="s_ca_id[]" name="s_ca_id[]" value="<? echo $row['ca_id']; ?>" /></td>
                <td align="center"><? echo $row['ca_id']; ?></td>
                <td align="center"><? echo $row['ca_name']; ?></td>
            </tr>
            <?
            }

            if($i == 0) {
                echo "<tr><td colspan=\"3\" height=\"100\" align=\"center\">검색된 카테고리가 없습니다.</td></tr>";
            }
            ?>
            </table>
        </div>
        <div class="btn"><input type="submit" value="적용" /></div>
    </form>
    </div>
    <? } ?>
</div>

<script>
$(function() {
    $("#fcategory").submit(function() {
        var stx = $.trim($("input[name=stx]").val());
        if(stx == "") {
            alert("검색어를 입력해 주세요.");
            return false;
        }

        return true;
    });

    $("input[name=check_all]").click(function() {
        if($(this).is(":checked")) {
            $("input[name^=s_ca_id]").attr("checked", true);
        } else {
            $("input[name^=s_ca_id]").attr("checked", false);
        }
    });

    $("#fresult").submit(function() {
        var $checked = $("input[name^=s_ca_id]:checked");
        var chk_count = $checked.size();
        if(!chk_count) {
            alert("적용할 카테고리를 선택해 주세요.");
            return false;
        }

        <? if($w == "u") { ?>
        if(chk_count > 1) {
            alert("쿠폰 수정시는 하나의 카테고리만 선택할 수 있습니다.");
            return false;
        <? } ?>

        var $opener = window.opener;
        var caid = "";
        var comma = "";

        $checked.each(function() {
            var id = $(this).val();
            if(id != "") {
                caid += comma + id;
            }

            if(caid != "") {
                comma = ",";
            }
        });

        $opener.$("input[name=ca_id]").val(caid);
        self.close();

        return false;
    });
});
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>