var mainCart = mainCart || {}; 

mainCart.chr = function(code){
    return String.fromCharCode(code);
}

jQuery(function ($) {
    
    var select_option_el = "select.it_option",
        overclass = "overlay",
        cartclass = "sct_cartop",
        cart_btn_class = "sct_btn";

    mainCart.add_wishitem = function(el) {

        var $el   = $(el),
            it_id = $el.data("it_id");

        if(!it_id) {
            alert("상품코드가 올바르지 않습니다.");
            return false;
        }

        $.post(
            g5_shop_url + "/ajax.action.php",
            { it_id: it_id, action : "wish_update" },
            function(error) {
                if(error != "OK") {
                    alert(error.replace(/\\n/g, "\n"));
                    return false;
                }
                
                mainCart.update_wish_side();
                alert("상품을 위시리스트에 담았습니다.");
                return;
            }
        );
    }

    mainCart.add_cart = function(frm) {

        var $frm = $(frm);
        var $sel = $frm.find(select_option_el);
        var it_name = $frm.find("input[name^=it_name]").val();
        var it_price = parseInt($frm.find("input[name^=it_price]").val());
        var id = "";
        var value, info, sel_opt, item, price, stock, run_error = false;
        var option = sep = "";
        var count = $sel.length;

        if(count > 0) {
            $sel.each(function(index) {
                value = $(this).val();
                item  = $(this).prev("label").text();

                if(!value) {
                    run_error = true;
                    return false;
                }

                // 옵션선택정보
                sel_opt = value.split(",")[0];

                if(id == "") {
                    id = sel_opt;
                } else {
                    id += mainCart.chr(30)+sel_opt;
                    sep = " / ";
                }

                option += sep + item + ":" + sel_opt;
            });

            if(run_error) {
                alert(it_name+"의 "+item+"을(를) 선택해 주십시오.");
                return false;
            }

            price = value[1];
            stock = value[2];
        } else {
            price = 0;
            stock = $frm.find("input[name^=it_stock]").val();
            option = it_name;
        }

        // 금액 음수 체크
        if(it_price + parseInt(price) < 0) {
            alert("구매금액이 음수인 상품은 구매할 수 없습니다.");
            mainCart.add_cart_after();
            return false;
        }

        // 옵션 선택정보 적용
        $frm.find("input[name^=io_id]").val(id);
        $frm.find("input[name^=io_value]").val(option);
        $frm.find("input[name^=io_price]").val(price);
        
        $.ajax({
            url: $(frm).attr("action"),
            type: "POST",
            data: $(frm).serialize(),
            dataType: "json",
            async: true,
            cache: false,
            success: function(data, textStatus) {

                mainCart.add_cart_after(frm);

                if(data.error != "") {
                    alert(data.error);
                    return false;
                }
                
                mainCart.update_cart_side();

                alert("상품을 장바구니에 담았습니다.");
            },
            error : function(request, status, error){
                mainCart.add_cart_after(frm);
                alert('false ajax :'+request.responseText);
            }
        });

        return false;
    }

    // 5.4 버전의 기본테마의 사이드바의 장바구니를 새로고침합니다.
    mainCart.update_cart_side = function(){
        var ajax_url = g5_shop_url || g5_shop_url;

        $.ajax({
            url: ajax_url + "/ajax.action.php",
            type: "GET",
            data: {"action":"refresh_cart"},
            dataType: "html",
            async: true,
            cache: false,
            success: function(data, textStatus) {
                var inner_html = $(data).filter(".sbsk").html(),
                    cart_count = $(data).find(".cart-count").text();
                
                $(".qk_con_wr .sbsk").html(inner_html);
                $(".hd_login .shop_cart .count").text(cart_count);
            },
            error : function(request, status, error){
                alert("false ajax :"+request.responseText);
            }
        });

        return true;
    }

    mainCart.update_wish_side = function(){
        var ajax_url = g5_shop_url || g5_shop_url;
        
        if (typeof g5_is_member == "undefined" || ! g5_is_member) {
            return false;
        }

        $.ajax({
            url: ajax_url + "/ajax.action.php",
            type: "GET",
            data: {"action":"refresh_wish"},
            dataType: "html",
            async: true,
            cache: false,
            success: function(data, textStatus) {
                var inner_html = $(data).filter(".side-wish").html();
                
                $(".qk_con_wr .side-wish").html(inner_html);
            },
            error : function(request, status, error){
                alert("false ajax :"+request.responseText);
            }
        });

        return true;
    }

    mainCart.add_cart_after = function(frm){
        var $over_rayers = $("."+overclass),
            $cart_rayers = $("."+cartclass);
        
        $over_rayers.each(function(i) {
            $(this).removeClass(overclass);
        });

        $cart_rayers.each(function(i) {
            if( !(frm && $(this).find("select").length) ){
                $(this).html("").removeClass(cartclass);
            }
        });
    }

    $(document).on("click", ".btn_cart", function(e) {
        e.preventDefault();

        var $this = $(this),
            it_id = $this.data("it_id"),
            $sct_li = $this.closest("li.sct_li"),
            $opt = $sct_li.find(".cart-layer"),
            $btn = $sct_li.find("."+cart_btn_class);
        
        $(".cart-layer").not($opt).removeClass(cartclass).html('');
        $("li.sct_li").not($sct_li).removeClass(overclass);

        $.ajax({
            url: g5_shop_url+"/ajax.action.php",
            type: "POST",
            data: {
                "it_id" : it_id,
                "action" : "get_item_option"
            },
            dataType: "json",
            async: true,
            cache: false,
            success: function(data, textStatus) {
                if(data.error != "") {
                    alert(data.error);
                    return false;
                }
                
                $sct_li.addClass(overclass);
                $opt.addClass(cartclass).html(data.html);

                if(!data.option) {
                    mainCart.add_cart($opt.find("form").get(0));
                    return;
                }

                //$btn.css("display","none");
                //$opt.css("display","block");
            },
            error : function(request, status, error){
                alert('false ajax :'+request.responseText);
            }
        });
    });

    $(document).on("change", "select.it_option", function() {
        var $frm = $(this).closest("form");
        var $sel = $frm.find("select.it_option");
        var sel_count = $sel.length;
        var idx = $sel.index($(this));
        var val = $(this).val();
        var it_id = $frm.find("input[name='it_id[]']").val();

        // 선택값이 없을 경우 하위 옵션은 disabled
        if(val == "") {
            $frm.find("select.it_option:gt("+idx+")").val("").attr("disabled", true);
            return;
        }

        // 하위선택옵션로드
        if(sel_count > 1 && (idx + 1) < sel_count) {
            var opt_id = "";

            // 상위 옵션의 값을 읽어 옵션id 만듬
            if(idx > 0) {
                $frm.find("select.it_option:lt("+idx+")").each(function() {
                    if(!opt_id)
                        opt_id = $(this).val();
                    else
                        opt_id += mainCart.chr(30)+$(this).val();
                });

                opt_id += mainCart.chr(30)+val;
            } else if(idx == 0) {
                opt_id = val;
            }

            $.post(
                g5_shop_url + "/itemoption.php",
                { it_id: it_id, opt_id: opt_id, idx: idx, sel_count: sel_count },
                function(data) {
                    $sel.eq(idx+1).empty().html(data).attr("disabled", false);

                    // select의 옵션이 변경됐을 경우 하위 옵션 disabled
                    if(idx+1 < sel_count) {
                        var idx2 = idx + 1;
                        $frm.find("select.it_option:gt("+idx2+")").val("").attr("disabled", true);
                    }
                }
            );
        } else if((idx + 1) == sel_count) { // 선택옵션처리
            if(val == "")
                return;

            var info = val.split(",");
            // 재고체크
            if(parseInt(info[2]) < 1) {
                alert("선택하신 선택옵션상품은 재고가 부족하여 구매할 수 없습니다.");
                return false;
            }
        }
    });

    $(document).on("click", ".cartopt_cart_btn", function(e) {
        e.preventDefault();

        mainCart.add_cart(this.form);
    });

    $(document).on("click", ".cartopt_close_btn", function(e) {
        e.preventDefault();
        
        mainCart.add_cart_after();

        //$(this).closest(".sct_cartop").css("display","none");
        //$(this).closest("li.sct_li").find(".sct_btn").css("display", "");
    });

    $(document).on("click", ".btn_wish", function(e) {
        e.preventDefault();

        mainCart.add_wishitem(this);
    });
});