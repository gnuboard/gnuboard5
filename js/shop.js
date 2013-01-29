if (typeof(SHOP_JS) == 'undefined') { // 한번만 실행
    var SHOP_JS = true;

    // 큰이미지 창
    function popup_large_image(url, width, height)
    {
        var top = 10;
        var left = 10;
        width = width + 50;
        height = height + 100;
        opt = 'scrollbars=yes,width='+width+',height='+height+',top='+top+',left='+left;
        popup_window(url, "largeimage", opt);
    }
}