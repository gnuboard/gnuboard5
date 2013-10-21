function fancyalert(msg)
{
    var w = $(window).width();
    var h = $(window).height();
    var scroll_top = $(window).scrollTop();
    var box;

    if (/iP(hone|od|ad)/.test(navigator.platform)) {
        if(window.innerHeight - $(window).outerHeight(true) > 0)
            h += (window.innerHeight - $(window).outerHeight(true));
    }

    box = "<div id=\"fancyalert\" style=\"top:"+scroll_top+"px;width:"+w+"px;height:"+h+"px;\">";
    box += "<table>";
    box + "<tr>";
    box += "<td width=\""+w+"\" height=\""+h+"\">"+msg;
    box += "<br>";
    box += "<button type=\"button\" id=\"fancyalert_close\">확인</button>";
    box += "</td>";
    box += "</tr>";
    box += "</table>";
    box += "</div>";

    $("body").append(box);
}

$(function() {
    $("#fancyalert_close").live("click", function() {
        $("#fancyalert").fadeOut().remove();
    });
});