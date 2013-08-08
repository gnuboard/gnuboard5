<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<script>
// 리스트 타입 쿠키가 있을 경우 바로 적용
if(itemlist_type = get_cookie("ck_itemlist_type")) {
    if(itemlist_type == "gallery") {
        $("ul.sct").removeClass("sct_40");
        set_cookie("ck_itemlist_type", "gallery", 1, g4_cookie_domain);
    } else {
        $("ul.sct").addClass("sct_40");
        set_cookie("ck_itemlist_type", "list", 1, g4_cookie_domain);
    }
}

$(function() {
    $("button.sct_lst_view").on("click", function() {
        if($(this).hasClass("sct_lst_gallery")) {
            $("ul.sct").removeClass("sct_40");
            set_cookie("ck_itemlist_type", "gallery", 1, g4_cookie_domain);
        } else {
            $("ul.sct").addClass("sct_40");
            set_cookie("ck_itemlist_type", "list", 1, g4_cookie_domain);
        }
    });
});
</script>