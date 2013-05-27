$(function() {
    // 선택옵션
    $("select[name='it_option[]']").change(function() {
        var sel_count = $("select[name='it_option[]']").size();
        var idx = $("select[name='it_option[]']").index($(this));
        var val = $(this).val();
        var it_id = $("input[name=it_id]").val();

        // 선택값이 없을 경우 하위 옵션은 disabled
        if(val == "") {
            $("select[name='it_option[]']:gt("+idx+")").val("").attr("disabled", true);
            return;
        }

        // 하위선택옵션로드
        if(sel_count > 1 && (idx + 1) < sel_count) {
            var opt_id = "";

            // 상위 옵션의 값을 읽어 옵션id 만듬
            if(idx > 0) {
                $("select[name='it_option[]']:lt("+idx+")").each(function() {
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
                "./itemoption.php",
                { it_id: it_id, opt_id: opt_id, idx: idx, sel_count: sel_count },
                function(data) {
                    $("select[name='it_option[]']").eq(idx+1).empty().html(data).attr("disabled", false);

                    // select의 옵션이 변경됐을 경우 하위 옵션 disabled
                    if(idx+1 < sel_count) {
                        var idx2 = idx + 1;
                        $("select[name='it_option[]']:gt("+idx2+")").val("").attr("disabled", true);
                    }
                }
            );
        } else if((idx + 1) == sel_count) { // 선택옵션처리
            var info = val.split(",");
            // 재고체크
            if(parseInt(info[2]) < 1) {
                alert("선택하신 선택옵션상품은 재고가 부족하여 구매할 수 없습니다.");
                return false;
            }

            // 선택옵션 자동추가 기능을 사용하려면 아래 false를 true로 설정
            sel_option_process(false);
        }
    });

    // 추가옵션
    $("select[name='it_supply[]']").change(function() {
        var $el = $(this);
        // 선택옵션 자동추가 기능을 사용하려면 아래 false를 true로 설정
        sel_supply_process($el, false);
    });

    // 선택옵션 추가
    $("#sit_selopt_submit").click(function() {
        sel_option_process(true);
    });

    // 추가옵션 추가
    $("button.sit_sel_submit").click(function() {
        var $el = $(this).closest("td").find("select[name='it_supply[]']");
        sel_supply_process($el, true);
    });

    // 수량변경 및 삭제
    $("#sit_sel_option li button").live("click", function() {
        var mode = $(this).text();
        var this_qty, max_qty = 9999, min_qty = 1;
        var $el_qty = $(this).closest("li").find("input[name='ct_qty[]']");
        var stock = parseInt($(this).closest("li").find("input[name='io_stock[]']").val());

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
                price_calculate();
                break;

            case "감소":
                this_qty = parseInt($el_qty.val().replace(/[^0-9]/, "")) - 1;
                if(this_qty < min_qty) {
                    this_qty = min_qty;
                    alert("최소 구매수량은 "+number_format(String(min_qty))+" 입니다.");
                }
                $el_qty.val(this_qty);
                price_calculate();
                break;

            case "삭제":
                if(confirm("선택하신 옵션항목을 삭제하시겠습니까?")) {
                    var $el = $(this).closest("li");
                    var del_exec = true;

                    if($("#sit_sel_option .sit_spl_list").size() > 0) {
                        // 선택옵션이 하나이상인지
                        if($el.hasClass("sit_opt_list")) {
                            if($(".sit_opt_list").size() <= 1)
                                del_exec = false;
                        } else {
                            if($(".sit_opt_list").size() < 1)
                                del_exec = false;
                        }
                    }

                    if(del_exec) {
                        $(this).closest("li").remove();
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
    $("input[name='ct_qty[]']").live("keyup", function() {
        var val= $(this).val();

        if(val != "") {
            if(val.replace(/[0-9]/g, "").length > 0) {
                alert("수량은 숫자만 입력해 주십시오.");
                $(this).val(1);
            } else {
                var d_val = parseInt(val);
                if(d_val < 1 || d_val > 9999) {
                    alert("수량은 1에서 9999 사이의 값으로 입력해 주십시오.");
                    $(this).val(1);
                } else {
                    var stock = parseInt($(this).closest("li").find("input[name='io_stock[]']").val());
                    if(d_val > stock) {
                        alert("재고수량 보다 많은 수량을 구매할 수 없습니다.");
                        $(this).val(stock);
                    }
                }
            }

            price_calculate();
        }
    });
});

// 선택옵션 추가처리
function sel_option_process(add_exec)
{
    var id = "";
    var value, info, sel_opt, item, price, stock, run_error = false;
    var option = sep = "";
    info = $("select[name='it_option[]']:last").val().split(",");

    $("select[name='it_option[]']").each(function(index) {
        value = $(this).val();
        item = $(this).closest("tr").find("th label").text();

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

    if(add_exec) {
        if(same_option_check(option))
            return;

        add_sel_option(0, id, option, price, stock);
    }
}

// 추가옵션 추가처리
function sel_supply_process($el, add_exec)
{
    var val = $el.val();
    var item = $el.closest("tr").find("th label").text();

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

    if(add_exec) {
        if(same_option_check(option))
            return;

        add_sel_option(1, id, option, price, stock);
    }
}

// 선택된 옵션 출력
function add_sel_option(type, id, option, price, stock)
{
    var opt = "";
    var li_class = "sit_opt_list";
    if(type)
        li_class = "sit_spl_list";

    var opt_prc;
    if(parseInt(price) >= 0)
        opt_prc = "(+"+number_format(String(price))+"원)";
    else
        opt_prc = "("+number_format(String(price))+"원)";

    opt += "<li class=\""+li_class+"\">\n";
    opt += "<input type=\"hidden\" name=\"io_type[]\" value=\""+type+"\">\n";
    opt += "<input type=\"hidden\" name=\"io_id[]\" value=\""+id+"\">\n";
    opt += "<input type=\"hidden\" name=\"io_value[]\" value=\""+option+"\">\n";
    opt += "<input type=\"hidden\" name=\"io_price[]\" value=\""+price+"\">\n";
    opt += "<input type=\"hidden\" name=\"io_stock[]\" value=\""+stock+"\">\n";
    opt += "<span class=\"sit_opt_subj\">"+option+"</span>\n";
    opt += "<span class=\"sit_opt_prc\">"+opt_prc+"</span>\n";
    opt += "<div><input type=\"text\" name=\"ct_qty[]\" value=\"1\" class=\"frm_input\" size=\"5\">\n";
    opt += "<button type=\"button\" class=\"sit_qty_plus btn_frmline\">증가</button>\n";
    opt += "<button type=\"button\" class=\"sit_qty_minus btn_frmline\">감소</button>\n";
    opt += "<button type=\"button\" class=\"sit_opt_del btn_frmline\">삭제</button></div>\n";
    opt += "</li>\n";

    if($("#sit_sel_option > ul").size() < 1) {
        $("#sit_sel_option").html("<ul id=\"sit_opt_added\"></ul>");
        $("#sit_sel_option > ul").html(opt);
    } else{
        if(type) {
            if($("#sit_sel_option .sit_spl_list").size() > 0) {
                $("#sit_sel_option .sit_spl_list:last").after(opt);
            } else {
                if($("#sit_sel_option .sit_opt_list").size() > 0) {
                    $("#sit_sel_option .sit_opt_list:last").after(opt);
                } else {
                    $("#sit_sel_option > ul").html(opt);
                }
            }
        } else {
            if($("#sit_sel_option .sit_opt_list").size() > 0) {
                $("#sit_sel_option .sit_opt_list:last").after(opt);
            } else {
                if($("#sit_sel_option .sit_spl_list").size() > 0) {
                    $("#sit_sel_option .sit_spl_list:first").before(opt);
                } else {
                    $("#sit_sel_option > ul").html(opt);
                }
            }
        }
    }

    price_calculate();
}

// 동일선택옵션있는지
function same_option_check(val)
{
    var result = false;
    $("input[name='io_value[]']").each(function() {
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
    var it_price = parseInt($("input[name=it_price]").val());
    var $el_prc = $("input[name='io_price[]']");
    var $el_qty = $("input[name='ct_qty[]']");
    var $el_type = $("input[name='io_type[]']");
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

    $("input[name=total_price]").val(total);
    $("#sit_tot_price").empty().html("총 금액 : "+number_format(String(total))+"원");
}

// php chr() 대응
function chr(code)
{
    return String.fromCharCode(code);
}