$(function() {
    $(".theme_active").on("click", function() {
        var theme = $(this).data("theme");
        var name  = $(this).data("name");

        if(!confirm(name+" 테마를 적용하시겠습니까?"))
            return false;

        var set_default_skin = 0;
        if($(this).data("set_default_skin") == true) {
            if(confirm("기본환경설정의 스킨을 테마에서 설정된 스킨으로 변경하시겠습니까?"))
                set_default_skin = 1;
        }

        $.ajax({
            type: "POST",
            url: "./theme_update.php",
            data: {
                "theme": theme,
                "set_default_skin": set_default_skin
            },
            cache: false,
            async: false,
            success: function(data) {
                if(data) {
                    alert(data);
                    return false;
                }

                document.location.reload();
            }
        });
    });

    $(".theme_deactive").on("click", function() {
        var theme = $(this).data("theme");
        var name  = $(this).data("name");

        if(!confirm(name+" 테마 사용설정을 해제하시겠습니까?"))
            return false;

        $.ajax({
            type: "POST",
            url: "./theme_update.php",
            data: {
                "theme": theme,
                "type": "reset"
            },
            cache: false,
            async: false,
            success: function(data) {
                if(data) {
                    alert(data);
                    return false;
                }

                document.location.reload();
            }
        });
    });

    $(".theme_preview").on("click", function() {
        var theme = $(this).data("theme");

        $("#theme_detail").remove();

        $.ajax({
            type: "POST",
            url: "./theme_detail.php",
            data: {
                "theme": theme
            },
            cache: false,
            async: false,
            success: function(data) {
                $("#theme_list").after(data);
            }
        });
    });
});