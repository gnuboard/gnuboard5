if (typeof(SHOP_JS) == 'undefined') { // 한번만 실행
    var SHOP_JS = true;

    // 큰이미지 창
    function popup_large_image(it_id, img, width, height, cart_dir)
    {
        var top = 10;
        var left = 10;
        url = cart_dir+"/largeimage.php?it_id=" + it_id + "&img=" + img;
        width = width + 50;
        height = height + 100;
        opt = 'scrollbars=yes,width='+width+',height='+height+',top='+top+',left='+left;
        popup_window(url, "largeimage", opt);
    }
}