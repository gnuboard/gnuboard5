<?
$sub_menu = "400210";
include_once("./_common.php");
$g4[title] = $html_title;
include_once(G4_ADMIN_PATH."/admin.head.php");
?>

<style>
ul.category { list-style-type: none; margin: 0; padding: 0; }
</style>

<?
for ($i=1; $i<=5; $i++) {
    echo '<fieldset style="float:left;" id="category-'.$i.'">'.PHP_EOL;
    echo '<legend>'.$i.'단계</legend>'.PHP_EOL;
    echo '<ul class="category">'.PHP_EOL;
    echo '</ul>'.PHP_EOL;
    echo '</fieldset>'.PHP_EOL;
}
?>

<script>
// ajax 영역은 프로그램을 모르시는 경우 절대 수정하지 마십시오.
$(function(){
    $("ul.category").sortable();
    $("ul.category").disableSelection();

    // live : 동적으로 만드는 엘리먼트에 적용되는 bind
    // 분류 항목을 클릭하면 하위분류를 노출해 줌
    $("ul.category li").live("click", function() {
        var ca_id = $(this).attr("id").split("-")[1];
        var low_index = parseInt(ca_id.length) / 2 + 1;
        var $category = $("#category-"+low_index+" ul");
        $category.html("");
        load_category(ca_id, low_index);
        /*
        $.ajax({
            url: "categoryorderlowcode.ajax.php",
            data: {
                "ca_id": ca_id
            },
            dataType: "json",
            async: false,
            success: function(data, status) {
                var $category = $("#category-"+low_index+" ul");
                $category.html("");

                if (data.error) {
                    alert(data.error);
                    return false;
                }

                if (!data.list) return;

                for (var i=0; i<data.list.length; i++) {
                    var id   = data.list[i].ca_id;
                    var name = data.list[i].ca_name;
                    $category.append('<li id="ca_id-'+id+'">'+name+'</li>');
                }
            }
        });
        */

    });

    // 각 단계의 드래그앤드롭이 끝나면 실행
    $(".category").live("sortstop", function(event, ui) { 
        //alert($(this).attr("class")); 
        var $li = $(this).children("li");
        var count = $li.length;
        for (var i=0; i<count; i++) {
            var ca_id = $li.eq(i).attr("id").split("-")[1];
            $.ajax({
                url: "categoryorderupdate.ajax.php",
                dataType: "text",
                data: {
                    "ca_id": ca_id,
                    "order": i
                },
                success: function(data, status) {
                    //alert(data);
                }
            });
        }
    });

    function load_category(ca_id, index) {
        $.ajax({
            url: "categoryorderload.ajax.php", 
            data: {
                "ca_id": ca_id
            },
            dataType: "json",
            async: false,
            success: function(data, status) {
                //alert(data);
                if (data.error) {
                    alert(data.error);
                    return false;
                }

                var $category = $("#category-"+index+" ul");
                for (var i=0; i<data.list.length; i++) {
                    var id   = data.list[i].ca_id;
                    var name = data.list[i].ca_name;
                    var cnt  = data.list[i].low_category_count;
                    $category.append('<li id="ca_id-'+id+'">'+name+' ('+cnt+')</li>');
                }
            }
        });
    }

    load_category("", 1);
});
</script>

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>