<?
include_once("./_common.php");

$g4['title'] = "카테고리";
include_once("{$g4['path']}/head.sub.php");

for ($i=1; $i<=5; $i++) {
    echo '<select id="category-'.$i.'" class="category" size="20" style="width:180px;">';
    if ($i == 1) {
        $sql = " select ca_id, ca_name from $g4[shop_category_table] where length(ca_id) = 2 order by ca_order, ca_id ";
        $result = sql_query($sql);
        while ($row=sql_fetch_array($result)) {
            echo '<option value="'.$row['ca_id'].'" title="'.$row['ca_id'].'">'.$row['ca_name'].'</option>'.PHP_EOL;
        }
    }
    echo '</select>'.PHP_EOL;
}
?>

<div>
<input type="text" id="category_number" size="1" />단계 분류 선택중<br />
분류명 : <input type="text" id="ca_name" />
<button id="btn_add">추가</button>
<button id="btn_upd">수정</button>
</div>

<script type="text/javascript">
$(function(){
    var ca_number = -1; // 카테고리 셀렉트박스 번호
    function get_ca_number($elem) {
        return $elem.attr("id").split("-")[1];
    }

    //$("select.category").focus(function(e){
    $("select.category").click(function(){
        ca_number = get_ca_number($(this));
        $("#category_number").val(ca_number);
    });

    $("select.category option").click(function(e){
        ca_number = get_ca_number($(this).parent("select"));
        if (ca_number < 5) {
            alert(ca_number);
        }
    });

    $("#btn_add").click(function(){
        if (ca_number == -1) {
            alert("추가하실 분류 단계를 선택하세요.");
            return false;
        }

        var $ca_name = $("#ca_name");
        var ca_name = $.trim($ca_name.val());
        if (ca_name == "") {
            alert("분류명을 입력하세요.");
            $ca_name.focus();
            return false;
        }

        var $current = $("#category-"+ca_number); // 현재 카테고리 셀렉트박스
        var ca_id = $current.children("option:selected").val();

        $.ajax({
            url: "category.ajax.php", 
            type: "POST",
            data: {
                "ca_id": ca_id,
                "ca_name": ca_name,
                "ca_number": ca_number
            },
            dataType: "json",
            async: false,
            success: function(data, textStatus) {
                if (data.error) {
                    alert(data.error);
                    return false;
                }
                $current.append('<option value="'+data.ca_id+'" title="'+data.ca_id+'">'+data.ca_name+'</option>');
            }
        });
    });
});
</script>

<?
include_once("{$g4['path']}/tail.sub.php");
?>