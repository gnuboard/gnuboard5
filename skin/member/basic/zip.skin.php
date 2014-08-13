<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
echo $g5['daum_juso_js'].PHP_EOL;
?>
<div id="daum_juso_wrap" class="daum_juso_wrap"></div>
<script>
jQuery(function($){
    $("html, body").addClass("daum_juso_body");
    function put_data2(zip1, zip2, addr1, addr2, addr3, jibeon)
    {
        var of = window.opener.document.<?php echo $frm_name; ?>;
        
        if(jibeon == "N"){
            of.<?php echo $frm_addr3; ?>.parentNode.style.display="none";
        } else if (jibeon == "R"){
            of.<?php echo $frm_addr3; ?>.parentNode.style.display="";
        }
        of.<?php echo $frm_zip1; ?>.value = zip1;
        of.<?php echo $frm_zip2; ?>.value = zip2;
        of.<?php echo $frm_addr1; ?>.value = addr1;
        of.<?php echo $frm_addr2; ?>.value = addr2;
        of.<?php echo $frm_addr3; ?>.value = addr3;

        if( jibeon ){
            if(of.<?php echo $frm_jibeon; ?> !== undefined){
                of.<?php echo $frm_jibeon; ?>.value = jibeon;
            }
        }
        of.<?php echo $frm_addr2; ?>.focus();
        window.close();
    }

    var el_id = document.getElementById('daum_juso_wrap');
    new daum.Postcode({
        oncomplete: function(data) {
            var address1 = data.address1,
                address2 = "";
            if(data.addressType == "R"){        //도로명이면 
                address2 = data.address2;
            }
            put_data2(data.postcode1, data.postcode2, address1, '', address2, data.addressType);
        },
        width : '100%',
        height : '100%'
    }).embed(el_id);
});
</script>