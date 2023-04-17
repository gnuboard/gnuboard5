var option_add = false;
var supply_add = false;
var isAndroid = (navigator.userAgent.toLowerCase().indexOf("android") > -1);
var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

$(function() {
    // 선택옵션
    /* 가상커서 ctrl keyup 이베트 대응 */
    /*
    $(document).on("keyup", "select.it_option", function(e) {
        var sel_count = $("select.it_option").length;
        var idx = $("select.it_option").index($(this));
        var code = e.keyCode;
        var val = $(this).val();

        option_add = false;
        if(code == 17 && sel_count == idx + 1) {
            if(val == "")
                return;

            sel_option_process(true);
        }
    });
    */

    /* 키보드 접근 후 옵션 선택 Enter keydown 이벤트 대응 */
    $(document).on("keydown", "select.it_option", function(e) {
        var sel_count = $("select.it_option").length;
        var idx = $("select.it_option").index($(this));
        var code = e.keyCode;
        var val = $(this).val();

        option_add = false;
        if(code == 13 && sel_count == idx + 1) {
            if(val == "")
                return;

            sel_option_process(true);
        }
    });

    if(isAndroid) {
        $(document).on("touchend mouseup", "select.it_option", function() {
            option_add = true;
        });
    } else {
        var it_option_events = isSafari ? "mousedown" : "mouseup";

        $(document).on(it_option_events, "select.it_option", function(e) {
            option_add = true;
        });
    }

    $(document).on("change", "select.it_option", function() {
        var sel_count = $("select.it_option").length,
            idx = $("select.it_option").index($(this)),
            val = $(this).val(),
            it_id = $("input[name='it_id[]']").val(),
            post_url = (typeof g5_shop_url !== "undefined") ? g5_shop_url+"/itemoption.php" : "./itemoption.php",
            $this = $(this),
            op_0_title = $this.find("option:eq(0)").text();

        // 선택값이 없을 경우 하위 옵션은 disabled
        if(val == "") {
            $("select.it_option:gt("+idx+")").val("").attr("disabled", true);
            return;
        }

        $this.trigger("select_it_option_change", [$this]);

        // 하위선택옵션로드
        if(sel_count > 1 && (idx + 1) < sel_count) {
            var opt_id = "";

            // 상위 옵션의 값을 읽어 옵션id 만듬
            if(idx > 0) {
                $("select.it_option:lt("+idx+")").each(function() {
                    if(!opt_id)
                        opt_id = $(this).val();
                    else
                        opt_id += chr(30)+$(this).val();
                });

                opt_id += chr(30)+val;
            } else if(idx == 0) {
                opt_id = val;
            }

            $.post(
                post_url,
                { it_id: it_id, opt_id: opt_id, idx: idx, sel_count: sel_count, op_title : op_0_title },
                function(data) {
                    $("select.it_option").eq(idx+1).empty().html(data).attr("disabled", false);

                    // select의 옵션이 변경됐을 경우 하위 옵션 disabled
                    if(idx+1 < sel_count) {
                        var idx2 = idx + 1;
                        $("select.it_option:gt("+idx2+")").val("").attr("disabled", true);
                    }

                    $this.trigger("select_it_option_post", [$this, idx, sel_count, data]);
                }
            );
        } else if((idx + 1) == sel_count) { // 선택옵션처리
            if(option_add && val == "")
                return;

            var info = val.split(",");
            // 재고체크
            if(parseInt(info[2]) < 1) {
                alert("선택하신 선택옵션상품은 재고가 부족하여 구매할 수 없습니다.");
                return false;
            }

            if(option_add)
                sel_option_process(true);
        }
    });

    // 추가옵션
    /* 가상커서 ctrl keyup 이베트 대응 */
    /*
    $(document).on("keyup", "select.it_supply", function(e) {
        var $el = $(this);
        var code = e.keyCode;
        var val = $(this).val();

        supply_add = false;
        if(code == 17) {
            if(val == "")
                return;

            sel_supply_process($el, true);
        }
    });
    */

    /* 키보드 접근 후 옵션 선택 Enter keydown 이벤트 대응 */
    $(document).on("keydown", "select.it_supply", function(e) {
        var $el = $(this);
        var code = e.keyCode;
        var val = $(this).val();

        supply_add = false;
        if(code == 13) {
            if(val == "")
                return;

            sel_supply_process($el, true);
        }
    });

    if(isAndroid) {
        $(document).on("touchend mouseup", "select.it_supply", function() {
            supply_add = true;
        });
    } else {
        var it_supply_events = isSafari ? "mousedown" : "mouseup";
        
        $(document).on(it_supply_events, "select.it_supply", function(e) {
            supply_add = true;
        });
    }

    $(document).on("change", "select.it_supply", function() {
        var $el = $(this);
        var val = $(this).val();

        if(val == "")
            return;

        if(supply_add)
            sel_supply_process($el, true);
    });

    // 수량변경 및 삭제
    $(document).on("click", "#sit_sel_option li button", function() {
        var $this = $(this),
            mode = $this.text(),
            this_qty, max_qty = 9999, min_qty = 1,
            $el_qty = $(this).closest("li").find("input[name^=ct_qty]"),
            stock = parseInt($(this).closest("li").find("input.io_stock").val());

        switch(mode) {
            case "증가":
                this_qty = parseInt($el_qty.val().replace(/[^0-9]/, "")) + 1;
                if(this_qty > stock) {
                    alert("재고수량 보다 많은 수량을 구매할 수 없습니다.");
                    this_qty = stock;
                }

                if(this_qty > max_qty) {
                    this_qty = max_qty;
                    alert("최대 구매수량은 "+number_format(String(max_qty))+" 입니다.");
                }

                $el_qty.val(this_qty);
                $this.trigger("sit_sel_option_success", [$this, mode, this_qty]);
                price_calculate();
                break;

            case "감소":
                this_qty = parseInt($el_qty.val().replace(/[^0-9]/, "")) - 1;
                if(this_qty < min_qty) {
                    this_qty = min_qty;
                    alert("최소 구매수량은 "+number_format(String(min_qty))+" 입니다.");
                }
                $el_qty.val(this_qty);
                $this.trigger("sit_sel_option_success", [$this, mode, this_qty]);
                price_calculate();
                break;

            case "삭제":
                if(confirm("선택하신 옵션항목을 삭제하시겠습니까?")) {
                    var $el = $(this).closest("li");
                    var del_exec = true;

                    if($("#sit_sel_option .sit_spl_list").length > 0) {
                        // 선택옵션이 하나이상인지
                        if($el.hasClass("sit_opt_list")) {
                            if($(".sit_opt_list").length <= 1)
                                del_exec = false;
                        }
                    }

                    if(del_exec) {
                        // 지우기전에 호출해야 trigger 를 호출해야 합니다.
                        $this.trigger("sit_sel_option_success", [$this, mode, ""]);
                        $el.closest("li").remove();
                        price_calculate();
                    } else {
                        alert("선택옵션은 하나이상이어야 합니다.");
                        return false;
                    }
                }
                break;

            default:
                alert("올바른 방법으로 이용해 주십시오.");
                break;
        }
    });

    // 수량직접입력
    $(document).on("keyup", "input[name^=ct_qty]", function() {
        var $this = $(this),
            val= $this.val(),
            force_val = 0;

        if(val != "") {
            if(val.replace(/[0-9]/g, "").length > 0) {
                alert("수량은 숫자만 입력해 주십시오.");
                force_val = 1;
                $(this).val(force_val);
            } else {
                var d_val = parseInt(val);
                if(d_val < 1 || d_val > 9999) {
                    alert("수량은 1에서 9999 사이의 값으로 입력해 주십시오.");
                    force_val = 1;
                    $(this).val(force_val);
                } else {
                    var stock = parseInt($(this).closest("li").find("input.io_stock").val());
                    if(d_val > stock) {
                        alert("재고수량 보다 많은 수량을 구매할 수 없습니다.");
                        force_val = stock;
                        $(this).val(force_val);
                    }
                }
            }
            
            $this.trigger("change_option_qty", [$this, val, force_val]);

            price_calculate();
        }
    });
});

// 선택옵션 추가처리
function sel_option_process(add_exec)
{
    var it_price = parseInt($("input#it_price").val());
    var id = "";
    var value, info, sel_opt, item, price, stock, run_error = false;
    var option = sep = "";
    info = $("select.it_option:last").val().split(",");

    $("select.it_option").each(function(index) {

        value = $(this).val();
        item = $(this).closest(".get_item_options").length ? $(this).closest(".get_item_options").find("label[for^=it_option]").text() : "";
        
        if( !item ){
            item = $(this).closest("tr").length ? $(this).closest("tr").find("th label").text() : "";
        }

        if(!value) {
            run_error = true;
            return false;
        }

        // 옵션선택정보
        sel_opt = value.split(",")[0];

        if(id == "") {
            id = sel_opt;
        } else {
            id += chr(30)+sel_opt;
            sep = " / ";
        }

        option += sep + item + ":" + sel_opt;
    });

    if(run_error) {
        alert(item+"을(를) 선택해 주십시오.");
        return false;
    }

    price = info[1];
    stock = info[2];

    // 금액 음수 체크
    if(it_price + parseInt(price) < 0) {
        alert("구매금액이 음수인 상품은 구매할 수 없습니다.");
        return false;
    }

    if(add_exec) {
        if(same_option_check(option))
            return;

        add_sel_option(0, id, option, price, stock);
    }
}

// 추가옵션 추가처리
function sel_supply_process($el, add_exec)
{
    if( $el.triggerHandler( 'shop_sel_supply_process',{add_exec:add_exec} ) !== false ){
        var val = $el.val();
        var item = $el.closest(".get_item_supply").length ? $el.closest(".get_item_supply").find("label[for^=it_supply]").text() : "";
        
        if( !item ){
            item = $el.closest("tr").length ? $el.closest("tr").find("th label").text() : "";
        }

        if(!val) {
            alert(item+"을(를) 선택해 주십시오.");
            return;
        }

        var info = val.split(",");

        // 재고체크
        if(parseInt(info[2]) < 1) {
            alert(info[0]+"은(는) 재고가 부족하여 구매할 수 없습니다.");
            return false;
        }

        var id = item+chr(30)+info[0];
        var option = item+":"+info[0];
        var price = info[1];
        var stock = info[2];

        // 금액 음수 체크
        if(parseInt(price) < 0) {
            alert("구매금액이 음수인 상품은 구매할 수 없습니다.");
            return false;
        }

        if(add_exec) {
            if(same_option_check(option))
                return;

            add_sel_option(1, id, option, price, stock);
        }
    }
}

// 선택된 옵션 출력
function add_sel_option(type, id, option, price, stock)
{
    var item_code = $("input[name='it_id[]']").val();
    var opt = "";
    var li_class = "sit_opt_list";
    if(type)
        li_class = "sit_spl_list";

    var opt_prc;
    if(parseInt(price) >= 0)
        opt_prc = "(+"+number_format(String(price))+"원)";
    else
        opt_prc = "("+number_format(String(price))+"원)";

    opt += "<li class=\""+li_class+"\">";
    opt += "<input type=\"hidden\" name=\"io_type["+item_code+"][]\" value=\""+type+"\">";
    opt += "<input type=\"hidden\" name=\"io_id["+item_code+"][]\" value=\""+id+"\">";
    opt += "<input type=\"hidden\" name=\"io_value["+item_code+"][]\" value=\""+option+"\">";
    opt += "<input type=\"hidden\" class=\"io_price\" value=\""+price+"\">";
    opt += "<input type=\"hidden\" class=\"io_stock\" value=\""+stock+"\">";
    opt += "<span class=\"sit_opt_subj\">"+option+"</span>";
    opt += "<span class=\"sit_opt_prc\">"+opt_prc+"</span>";
    opt += "<div><input type=\"text\" name=\"ct_qty["+item_code+"][]\" value=\"1\" class=\"frm_input\" size=\"5\">";
    opt += "<button type=\"button\" class=\"sit_qty_plus btn_frmline\">증가</button>";
    opt += "<button type=\"button\" class=\"sit_qty_minus btn_frmline\">감소</button>";
    opt += "<button type=\"button\" class=\"sit_opt_del btn_frmline\">삭제</button></div>";
    opt += "</li>";

    if($("#sit_sel_option > ul").length < 1) {
        $("#sit_sel_option").html("<ul id=\"sit_opt_added\"></ul>");
        $("#sit_sel_option > ul").html(opt);
    } else{
        if(type) {
            if($("#sit_sel_option .sit_spl_list").length > 0) {
                $("#sit_sel_option .sit_spl_list:last").after(opt);
            } else {
                if($("#sit_sel_option .sit_opt_list").length > 0) {
                    $("#sit_sel_option .sit_opt_list:last").after(opt);
                } else {
                    $("#sit_sel_option > ul").html(opt);
                }
            }
        } else {
            if($("#sit_sel_option .sit_opt_list").length > 0) {
                $("#sit_sel_option .sit_opt_list:last").after(opt);
            } else {
                if($("#sit_sel_option .sit_spl_list").length > 0) {
                    $("#sit_sel_option .sit_spl_list:first").before(opt);
                } else {
                    $("#sit_sel_option > ul").html(opt);
                }
            }
        }
    }

    price_calculate();

    $("#sit_sel_option").trigger("add_sit_sel_option", [opt]);
}

// 동일선택옵션있는지
function same_option_check(val)
{
    var result = false;
    $("input[name^=io_value]").each(function() {
        if(val == $(this).val()) {
            result = true;
            return false;
        }
    });

    if(result)
        alert(val+" 은(는) 이미 추가하신 옵션상품입니다.");

    return result;
}

// 가격계산
function price_calculate()
{
    var it_price = parseInt($("input#it_price").val());

    if(isNaN(it_price))
        return;

    var $el_prc = $("input.io_price");
    var $el_qty = $("input[name^=ct_qty]");
    var $el_type = $("input[name^=io_type]");
    var price, type, qty, total = 0;

    $el_prc.each(function(index) {
        price = parseInt($(this).val());
        qty = parseInt($el_qty.eq(index).val());
        type = $el_type.eq(index).val();

        if(type == "0") { // 선택옵션
            total += (it_price + price) * qty;
        } else { // 추가옵션
            total += price * qty;
        }
    });

    $("#sit_tot_price").empty().html("<span>총 금액 :</span><strong>"+number_format(String(total))+"</strong> 원");

    $("#sit_tot_price").trigger("price_calculate", [total]);
}

// php chr() 대응
function chr(code)
{
    return String.fromCharCode(code);
}