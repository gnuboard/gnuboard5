// 임시 저장하는 시간을 초단위로 설정한다.
var AUTOSAVE_INTERVAL = 60; // 초

// 글의 제목과 내용을 바뀐 부분이 있는지 비교하기 위하여 저장해 놓는 변수
var save_wr_subject = null;
var save_wr_content = null;

function autosave() {
    $("form#fwrite").each(function() {
        if (g5_editor.indexOf("ckeditor4") != -1 && typeof(CKEDITOR.instances.wr_content)!="undefined") {
            this.wr_content.value = CKEDITOR.instances.wr_content.getData();
        } else if (g5_editor.indexOf("cheditor5") != -1 && typeof(ed_wr_content)!="undefined") {
            this.wr_content.value = ed_wr_content.outputBodyHTML();
        }
        // 변수에 저장해 놓은 값과 다를 경우에만 임시 저장함
        if (save_wr_subject != this.wr_subject.value || save_wr_content != this.wr_content.value) {
            $.ajax({
                url: g5_bbs_url+"/ajax.autosave.php",
                data: {
                    "uid" : this.uid.value,
                    "subject": this.wr_subject.value,
                    "content": this.wr_content.value
                },
                type: "POST",
                success: function(data){
                    if (data) {
                        $("#autosave_count").html(data);    
                    }
                }
            });
            save_wr_subject = this.wr_subject.value;
            save_wr_content = this.wr_content.value;
        }
    });
}

$(function(){

    if (g5_is_member) {
        setInterval(autosave, AUTOSAVE_INTERVAL * 1000);
    }

    // 임시저장된 글목록을 가져옴
    $("#btn_autosave").click(function(){
        if ($("#autosave_pop").is(":hidden")) {
            $.get(g5_bbs_url+"/ajax.autosavelist.php", function(data){
                //alert(data);
                //console.log( "Data: " + data);
                $("#autosave_pop ul").empty();
                if ($(data).find("list").find("item").length > 0) {
                    $(data).find("list").find("item").each(function(i) { 
                        var id = $(this).find("id").text();
                        var uid = $(this).find("uid").text();
                        var subject = $(this).find("subject").text();
                        var datetime = $(this).find("datetime").text();
                        $("#autosave_pop ul").append('<li><a href="#none" class="autosave_load">'+subject+'</a><span>'+datetime+' <button type="button" class="autosave_del">삭제</button></span></li>');
                        $.data(document.body, "autosave_id"+i, id);
                        $.data(document.body, "autosave_uid"+i, uid);
                    });
                }
            }, "xml");
            $("#autosave_pop").show();
        } else {
            $("#autosave_pop").hide();
        }
    });

    // 임시저장된 글 제목과 내용을 가져와서 제목과 내용 입력박스에 노출해 줌
    $(".autosave_load").live("click", function(){
        var i = $(this).parents("li").index();
        var as_id = $.data(document.body, "autosave_id"+i);
        var as_uid = $.data(document.body, "autosave_uid"+i);
        $("#fwrite input[name='uid']").val(as_uid);
        $.get(g5_bbs_url+"/ajax.autosaveload.php", {"as_id":as_id}, function(data){
            var subject = $(data).find("item").find("subject").text();
            var content = $(data).find("item").find("content").text();
            $("#wr_subject").val(subject);
            if (g5_editor.indexOf("ckeditor4") != -1 && typeof(CKEDITOR.instances.wr_content)!="undefined") {
                CKEDITOR.instances.wr_content.setData(content);
            } else if (g5_editor.indexOf("cheditor5") != -1 && typeof(ed_wr_content)!="undefined") {
                ed_wr_content.putContents(content);
            } else {
                $("#fwrite #wr_content").val(content);
            }
        }, "xml");
        $("#autosave_pop").hide();
    });

    $(".autosave_del").live("click", function(){
        var i = $(this).parents("li").index();
        var as_id = $.data(document.body, "autosave_id"+i);
        $.get(g5_bbs_url+"/ajax.autosavedel.php", {"as_id":as_id}, function(data){ 
            if (data == -1) {
                alert("임시 저장된글을 삭제중에 오류가 발생하였습니다.");
            } else {
                $("#autosave_count").html(data);    
                $("#autosave_pop ul > li").eq(i).remove();
            }
        });
    });

    $(".autosave_close").click(function(){ $("#autosave_pop").hide(); });
});
