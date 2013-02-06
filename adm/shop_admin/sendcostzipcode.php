<?php
$sub_menu = "400750";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$option = '<option value="">검색된 자료가 없습니다.</option>';
$addr = trim($addr);

if($addr) {
    $option_list = '';
    $zipfile = array();
    $fp = fopen($g4['bbs_path']."/zip.db", "r");
    while(!feof($fp)) {
        $zipfile[] = fgets($fp, 4096);
    }
    fclose($fp);

    $search_count = 0;

    while ($zipcode = each($zipfile))
    {
        if(strstr(substr($zipcode[1],8,512), $addr))
        {
            $address = trim($zipcode[1]);

            $list[$search_count] = $address;

            $search_count++;
        }
    }

    if($search_count) {
        natsort($list);

        foreach($list as $value) {
            $code = substr($value, 0, 7);
            $option_list .= '<option value="'.$code.'">'.$value.'</option>'."\n";
        }

        $option = $option_list;
    }
}

$g4['title'] = "우편번호 찾기";
include_once($g4['path']."/head.sub.php");
?>

<style type="text/css">
<!--
#container { width: 300px; margin: 0 auto; }
form { display: inline; }
-->
</style>

<div id="container">
    <form id="fzipcode" method="post" action="./sendcostzipcode.php">
    <table cellpadding="0" cellspacing="0">
    <tr>
        <td width="300" height="30">우편번호 찾기</td>
    </tr>
    <tr>
        <td height="20">주소지의 시/군을 입력하세요.</td>
    </tr>
    <tr>
        <td>
            <input type="text" id="addr" name="addr" value="<? echo stripslashes($addr); ?>" size="20" />
            <input type="submit" value=" 검색 " />
        </td>
    </tr>
    <tr>
        <td height="30" valign="bottom">검색결과</td>
    </tr>
    <tr>
        <td>
            <select id="zipcode" name="zipcode">
                <? echo $option; ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            <button type="button" class="addbutton">우편번호1입력</button>
            <button type="button" class="addbutton">우편번호2입력</button>
        </td>
    </tr>
    <tr>
        <td>
            <input type="text" id="zip1" name="zip1" size="10" /> 부터
            <input type="text" id="zip2" name="zip2" size="10" /> 까지
        </td>
    </tr>
    <tr>
        <td height="35"><button type="button" id="addzipcode">우편번호추가</button></td>
    </tr>
    </table>
    </form>
</div>

<script>
$(function() {
    $("#fzipcode").submit(function() {
        var addr = $.trim($("input[id="addr" name="addr"]").val());
        if(addr == "") {
            alert("주소를 입력해 주세요.");
            return false;
        }

        return true;
    });

    $(".addbutton").click(function() {
        var code = $("select[id="zipcode" name="zipcode"]").val();
        var idx = $(".addbutton").index($(this));

        if(code == "") {
            alert("우편번호를 검색결과에서 선택해 주세요.");
            return false;
        }

        $("input[name^=zip]:eq("+idx+")").val(code);
    });

    $("#addzipcode").click(function() {
        var zip1 = $.trim($("input[id="zip1" name="zip1"]").val());
        var zip2 = $.trim($("input[id="zip2" name="zip2"]").val());

        if(zip1 == "" || zip2 == "") {
            alert("우편번호 범위를 입력해 주세요.");
            return false;
        }

        var $opener = window.opener;

        $opener.$("input[id="sc_zip1" name="sc_zip1"]").val(zip1);
        $opener.$("input[id="sc_zip2" name="sc_zip2"]").val(zip2);

        self.close();
    });
});
</script>

<?php
include_once($g4['path']."/tail.sub.php");
?>