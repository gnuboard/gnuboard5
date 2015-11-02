<?php
$sub_menu = "100510";
include_once('./_common.php');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

$g5['title'] = 'Browscap 업데이트';
include_once('./admin.head.php');
?>

<div id="processing">
    <p>Browscap 정보를 업데이트하시려면 아래 업데이트 버튼을 클릭해 주세요.</p>
    <div><button type="button" id="run_update">업데이트</button>
</div>

<script>
$(function() {
    $("#run_update").on("click", function() {
        $("#processing").html('<div class="update_processing"></div><p>Browscap 정보를 업데이트 중입니다.</p>');

        $.ajax({
            url: "./browscap_update.php",
            async: true,
            cache: false,
            dataType: "html",
            success: function(data) {
                if(data != "") {
                    alert(data);
                    return false;
                }

                $("#processing").html("<div class='check_processing'></div><p>Browscap 정보를 업데이트 했습니다.</p>");
            }
        });
    });
});
</script>

<?php
include_once('./admin.tail.php');
?>