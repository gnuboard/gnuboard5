<?php
include_once('./_common.php');

$stx = trim($stx);
if($stx) {
    $sql_common = " from {$g4['member_table']} ";
    $sql_search = " where mb_leave_date = '' and mb_intercept_date = '' and $sfl like '%$stx%' ";
    $sql_order = " order by mb_id asc ";

    $sql = " select mb_id, mb_name, mb_nick
                $sql_common
                $sql_search
                $sql_order ";
    $result = sql_query($sql);
}

$g4['title'] = "회원찾기";
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
    <form id="fmember" method="get" action="./coupon_member.php?w=<? echo $w; ?>">
        <select id="sfl" name="sfl">
            <option value="mb_name">이름</option>
            <option value="mb_nick">별명</option>
            <option value="mb_id">아이디</option>
        </select>
        <input type="text" id="stx" name="stx" class="ed" size="20" value="<? echo stripslashes($stx); ?>" />
        <input type="submit" class="btn1" value="검색" />
    </form>
    </div>
    <? if($stx) { ?>
    <div class="resultarea">
    <form id="fresult" method="get">
        <div class="list">
            <table width="330" cellpadding="0" cellspacing="0" border="0">
            <colgroup width="50" />
            <colgroup width="" />
            <colgroup width="90" />
            <colgroup width="90" />
            <tr>
                <th><input type="checkbox" id="check_all" name="check_all" /></th>
                <th>아이디</th>
                <th>이름</th>
                <th>별명</th>
            </tr>
            <?
            for($i=0; $row=sql_fetch_array($result); $i++) {
            ?>
            <tr>
                <td align="center"><input type="checkbox" id="s_mb_id[]" name="s_mb_id[]" value="<? echo $row['mb_id']; ?>" /></td>
                <td align="center"><? echo $row['mb_id']; ?></td>
                <td align="center"><? echo $row['mb_name']; ?></td>
                <td align="center"><? echo $row['mb_nick']; ?></td>
            </tr>
            <?
            }

            if($i == 0) {
                echo "<tr><td colspan=\"4\" height=\"100\" align=\"center\">검색된 회원이 없습니다.</td></tr>";
            }
            ?>
            </table>
        </div>
        <div class="btn"><input type="submit" class="btn1" value="적용" /></div>
    </form>
    </div>
    <? } ?>
</div>

<script>
$(function() {
    $("#fmember").submit(function() {
        var stx = $.trim($("input[name=stx]").val());
        if(stx == "") {
            alert("검색어를 입력해 주세요.");
            return false;
        }

        return true;
    });

    $("input[name=check_all]").click(function() {
        if($(this).is(":checked")) {
            $("input[name^=s_mb_id]").attr("checked", true);
        } else {
            $("input[name^=s_mb_id]").attr("checked", false);
        }
    });

    $("#fresult").submit(function() {
        var $checked = $("input[name^=s_mb_id]:checked");
        var chk_count = $checked.size();
        if(!chk_count) {
            alert("적용할 회원을 선택해 주세요.");
            return false;
        }

        <? if($w == "u") { ?>
        if(chk_count > 1) {
            alert("쿠폰 수정시는 한 명의 회원만 선택할 수 있습니다.");
            return false;
        <? } ?>

        var $opener = window.opener;
        var mbid = "";
        var comma = "";

        $checked.each(function() {
            var id = $(this).val();
            if(id != "") {
                mbid += comma + id;
            }

            if(mbid != "") {
                comma = ",";
            }
        });

        $opener.$("input[name=mb_id]").val(mbid);
        self.close();

        return false;
    });
    <? if($sfl) { ?>
    $("select[name=sfl]").val("<? echo $sfl; ?>");
    <? } ?>
});
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>