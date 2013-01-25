<!doctype html>
 
<html lang="en">
<head>
<meta charset="utf-8" />
<title>jQuery UI Sortable - Default functionality</title>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
<style>
#category-1 { list-style-type: none; margin: 0; padding: 0; }
#category-1 li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; border:1px dotted;}
#category-1 li span { position: absolute; margin-left: -1.3em; }

#category-2 { list-style-type: none; margin: 0; padding: 0; }
#category-2 li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; border:1px dotted;}
#category-2 li span { position: absolute; margin-left: -1.3em; }

#category-3 { list-style-type: none; margin: 0; padding: 0; }
#category-3 li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; border:1px dotted;}
#category-3 li span { position: absolute; margin-left: -1.3em; }

#sortable4 { list-style-type: none; margin: 0; padding: 0; }
#sortable4 li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; border:1px dotted;}
#sortable4 li span { position: absolute; margin-left: -1.3em; }
</style>
</head>
<body>

<fieldset style="float:left;" id="fieldset-1">
<legend>1단계</legend>
<ul class="category" id="category-1"></ul>
</fieldset>

<fieldset style="float:left;" id="fieldset-2">
<legend>2단계</legend>
<ul class="category" id="category-2"></ul>
</fieldset>

<fieldset style="float:left;" id="fieldset-3">
<legend>3단계</legend>
<ul class="category" id="category-3"></ul>
</fieldset>

<div style="clear:both;">
<fieldset style="float:left;">
<legend>등록/수정</legend>
<input type="text" id="category_number" size="1" />단계 분류 선택중<br />
분류명 : <input type="text" id="ca_name" />
<button id="btn_add">추가</button>
<button id="btn_upd">수정</button>
</fieldset>
</div>

 
<script>
$(function(){
    var ca_number = -1; // 카테고리 셀렉트박스 번호
    function get_ca_number($elem) {
        return $elem.attr("id").split("-")[1];
    }

    $("fieldset[id^=fieldset-]").click(function(){
        ca_number = get_ca_number($(this));
        $("#category_number").val(ca_number);
    });

    $(".category").sortable();
    $(".category").disableSelection();
    //$( "#category-1" ).bind( "sortstop", function(event, ui) {
    $("#category-1").bind( "sortstop", function(event, ui) {

        /*
        var childList = $(this).children();
        for(i=0;i<childList.length;i++){
            var child = childList[i];
            var orderBy = 4 - i;
            var id = $(child).attr( "id" );
            $.ajax({
                type : "POST",
                url : "서버프로그램주소",
                dataType : 'json',
                // 서버로 전송할 데이터
                data : {
                    "id":id,
                    "orderBy":orderBy
                },
                success : function(msg){
                }
            });
        }
        */
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
</body>
</html>