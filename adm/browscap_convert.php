<?php
$sub_menu = "100520";
require_once './_common.php';

if (!(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE)) {
    alert('사용할 수 없는 기능입니다.', correct_goto_url(G5_ADMIN_URL));
}

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$rows = isset($_GET['rows']) ? preg_replace('#[^0-9]#', '', $_GET['rows']) : 0;
if (!$rows) {
    $rows = 100;
}

$g5['title'] = '접속로그 변환';
require_once './admin.head.php';
?>

<div id="processing">
    <p>접속로그 정보를 Browscap 정보로 변환하시려면 아래 업데이트 버튼을 클릭해 주세요.</p>
    <button type="button" id="run_update">업데이트</button>
</div>

<script>
    $(function() {
        $(document).on("click", "#run_update", function() {
            $("#processing").html('<div class="update_processing"></div><p>Browscap 정보로 변환 중입니다.</p>');

            $.ajax({
                method: "GET",
                url: "./browscap_converter.php",
                data: {
                    rows: "<?php echo strval($rows); ?>"
                },
                async: true,
                cache: false,
                dataType: "html",
                success: function(data) {
                    $("#processing").html(data);
                }
            });
        });
    });
</script>

<?php
require_once './admin.tail.php';
