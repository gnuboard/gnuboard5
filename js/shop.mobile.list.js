$(function() {
    $("#btn_more_item").on("click", function() {
        var $this = $(this);
        var url   = $this.data("url");
        var page  = $this.data("page");
        var $msg  = $("#item_load_msg");

        if($msg.is(":visible"))
            return false;

        if($this.hasClass("no_more_item")) {
            alert("등록된 상품이 더 이상없습니다.");
            return false;
        }

        $msg.css("display", "block");

        $.ajax({
            type: "POST",
            data: { page: page },
            url: url,
            cache: false,
            async: true,
            dataType: "json",
            success: function(data) {
                if(data.error != "") {
                    alert(data.error);
                    return false;
                }

                var $items = $(data.item).find("li");
                var cnt = $items.length;

                if(cnt < 1) {
                    alert("등록된 상품이 더 이상없습니다.");
                    $msg.css("display", "none");
                    $this.addClass("no_more_item");
                    return false;
                }

                $("#sct_wrap").append($items);
                $this.data("page", data.page);
                $msg.css("display", "none");
            }
        });
    });
});