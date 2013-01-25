<?
include_once('./_common.php');

// 상품정보
$sql = " select it_id, it_option_use, it_opt1_subject, it_opt2_subject, it_opt3_subject, it_opt1, it_opt2, it_opt3, it_supplement_use
            from {$g4['yc4_item_table']}
            where it_id = '$it_id' ";
$it = sql_fetch($sql);

if(!$it['it_id']) {
    alert_close('상품 정보가 존재하지 않아 선택사항을 변경할 수 없습니다.');
}

if($sw_direct != 1)
    $sw_direct = 0;

$s_uq_id = get_session('ss_uniqid');

$sql = " select ct_id, ct_send_cost_pay
            from {$g4['yc4_cart_table']}
            where uq_id = '$s_uq_id' and it_id = '$it_id' and ct_direct = '$sw_direct' and ct_status = '쇼핑'
            order by ct_id asc
            limit 0, 1 ";
$temp = sql_fetch($sql);
$ct_send_cost_pay = $temp['ct_send_cost_pay'];
$ct_parent = $temp['ct_id'];

// 장바구니정보
$sql_where = " where uq_id = '$s_uq_id' and it_id = '$it_id' and ct_direct = '$sw_direct' ";

$sql = " select ct_id, ct_parent, is_option, it_id, it_name, opt_id, ct_option, it_amount, ct_amount, ct_qty
            from {$g4['yc4_cart_table']}
            $sql_where
            order by ct_id asc ";
$result = sql_query($sql);

$ct_count = mysql_num_rows($result);

if(!$ct_count) {
    alert_close('장바구니 정보가 존재하지 않습니다.');
}

$g4['title'] = '선택사항/수량 변경';
include_once ($g4['path'].'/head.sub.php');
?>

<style type="text/css">
<!--
#container { width: 650px; margin: 0 auto; }
ul { margin: 0; padding: 0; list-style: none; }
form { display: inline; }
#optionform { width: 600px; margin: 0 auto; padding: 10px 0 30px 0; }
#optionlist { width: 580px; height: 180px; margin: 0 auto; padding: 10px 0 10px 20px; border: 1px solid #ccc; }
#fbutton { width: 600px; padding-top: 15px; margin: 0 auto; text-align: center; }
.option-delete { cursor: pointer; }
.option-stock { display: none; }
.item-count input { width: 45px; text-align: right; padding-right: 5px; }
.add-item { cursor: pointer; }
.subtract-item { cursor: pointer; }
#total-price { }
-->
</style>

<div id="container">
    <div id="optionform">
        <table width="600" cellpadding="0" cellspacing="0" border="0">
        <? // 선택옵션
        if($it['it_option_use']) {
            $disabled = '';
            for($i = 1; $i <= 3; $i++) {
                if($i > 1) {
                    $disabled = 'disabled';
                }

                $str = conv_item_options(trim($it["it_opt{$i}_subject"]), trim($it["it_opt{$i}"]), $i, $disabled);
                if($str) {
                    echo '<tr height="25">';
                    echo '<td width="100">&nbsp;&nbsp;&nbsp; · <span class="opt_subject">'.$it["it_opt{$i}_subject"].'</span></td>';
                    echo '<td width="20" align="center">:</td>';
                    echo '<td style="word-break:break-all;">'.$str.'</td></tr>';
                }
            }
        }
        ?>
        <? // 추가옵션
        if($it['it_supplement_use']) {
            $subject = get_supplement_subject($it_id);
            if($subject) {
                $index = 1;

                foreach($subject as $value) {
                    $sp_id = $value;
                    $opt = get_supplement_option($it_id, $sp_id, $index);

                    if($opt) {
                        echo '<tr height="25">';
                        echo '<td width="100">&nbsp;&nbsp;&nbsp; · <span class="spl_subject">'.$value.'</span></td>';
                        echo '<td width="20" align="center">:</td>';
                        echo '<td style="word-break:break-all;">'.$opt.'</td></tr>';

                        $index++;
                    }
                }
            }
        }
        ?>
        </table>
    </div>
    <form id="fcartoption" method="post" action="./cartoptionupdate.php">
    <input type="hidden" name="it_id" value="<? echo $it_id; ?>" />
    <input type="hidden" name="ct_parent" value="<? echo $ct_parent; ?>" />
    <input type="hidden" name="sw_direct" value="<? echo $sw_direct; ?>" />
    <input type="hidden" name="ct_send_cost_pay" value="<? echo $ct_send_cost_pay; ?>" />
    <div id="optionlist">
        <ul>
        <? // 옵션
        $total_price = 0;
        for($i=0; $row=sql_fetch_array($result); $i++) {
            // 상품가격
            if($i == 0) {
                $item_amount = $row['it_amount'];
            }
            // 재고수량
            if($row['is_option']) {
                $stock_qty = get_option_stock_qty($row['it_id'], $row['opt_id'], $row['is_option']);
            } else {
                $stock_qty = get_it_stock_qty($row['it_id']);
            }
            $total_price += ($row['it_amount'] + $row['ct_amount']) * $row['ct_qty'];
            if($row['ct_option']) {
                $ct_option = $row['ct_option'];
            } else {
                $ct_option = $row['it_name'];
            }
            $option_price = ' (+'.number_format($row['ct_amount']).'원)';

            echo '<li>';
            echo '<input type="hidden" name="ct_id[]" value="'.$row['ct_id'] .'" />';
            echo '<input type="hidden" name="is_delete[]" value="0" />';
            echo '<input type="hidden" name="is_option[]" value="'.$row['is_option'].'" />';
            echo '<input type="hidden" name="opt_id[]" value="'.$row['opt_id'].'" />';
            echo '<input type="hidden" name="ct_option[]" value="' . $row['ct_option'].'" />';
            echo '<input type="hidden" name="it_amount[]" value="'.$row['it_amount'].'" />';
            echo '<input type="hidden" name="ct_amount[]" value="'.$row['ct_amount'].'" />';
            echo '<span class="option-stock">'. $stock_qty . '</span>';
            if($row['is_option'] == 1) {
                echo '<span class="selected-option">' . $ct_option . '</span>';
            } else if($row['is_option'] == 2) {
                echo '<span class="selected-supplement">' . $ct_option . '</span>';
            } else {
                echo '<span class="basic-option">' . $ct_option . '</span>';
            }
            echo '<span class="option-price">' . $option_price . '</span>';
            echo '<span class="item-count"> <input type="text" name="ct_qty[]" value="'.$row['ct_qty'].'" maxlength="4" /></span>';
            echo '<span class="add-item"> + </span><span class="subtract-item"> - </span>';
            echo '<span class="option-delete"> 삭제</span>';
            echo '</li>';
        }
        ?>
        </ul>
        <div id="total-price">총 금액 : <span><? echo number_format($total_price); ?>원</span></div>
    </div>
    <div id="fbutton"><input type="submit" value="변경" />&nbsp;<button type="button" id="winclose">닫기</button></div>
    </form>
</div>

<script>
$(function() {
    // 선택옵션
    var $option_select = $("select[name^=item-option-]");
    var option_count = $option_select.size();

    // 추가옵션
    var $supplement_select = $("select[name^=item-supplement-]");
    var supplement_count = $supplement_select.size();

    // 선택옵션이 1개일 때 옵션항목 갱신
    if(option_count == 1) {
        var opt_id = "";
        $.post(
            "./itemoptiondata.php",
            { it_id: "<?php echo $it_id; ?>", opt_id: opt_id, idx: -1, showinfo: "showinfo" },
            function(data) {
                $option_select.html(data);
            }
        );
    }

    // 선택옵션선택
    $option_select.change(function() {
        var idx = $option_select.index($(this));
        var val = $(this).val();

        if((idx + 1) < option_count) {
            if(val == '') {
                var $gt_select = $("select[name^=item-option-]:gt(" + idx + ")");
                $gt_select.val("");
                $gt_select.attr("disabled", true);
            } else {
                var $next_select = $option_select.eq(idx + 1);

                // 옵션정보갱신
                var opt_id = "";
                var deli = "";
                $option_select.each(function(index) {
                    if(idx < index) {
                        return false;
                    }

                    var s_val = $(this).val();
                    if(s_val != "") {
                        opt_id += deli + s_val
                    }

                    deli = chr(30);
                });

                // 마지막 직전 select 변경시 마지막 select 옵션에 가격정보 표시하도록
                var showinfo = "";
                if(idx == (option_count - 2)) {
                    showinfo = "showinfo";
                }

                $.post(
                    "./itemoptiondata.php",
                    { it_id: "<? echo $it_id; ?>", opt_id: opt_id, idx: idx, showinfo: showinfo },
                    function(data) {
                        $option_select.eq(idx + 1).html(data);
                    }
                );

                $next_select.val("");
                if($next_select.is(":disabled")) {
                    $next_select.attr("disabled", false);
                }
            }
        }

        if((idx + 1) == option_count) {
            if(val != "") {
                optionDisplay();
            }
        }
    });

    // 추가옵션선택
    $supplement_select.change(function() {
        var val = $(this).val();
        var idx = $supplement_select.index($(this));

        if(val != "") {
            var subj = $("span.spl_subject:eq("+idx+")").text();
            var sp_id = subj+chr(30)+val;
            var splcontent = "";
            var spladd = true;
            var ct_option = subj+" : "+val;

            // 선택된 옵션체크
            $("#optionlist ul li:visible span.selected-supplement").each(function() {
                var oldval = $(this).text();
                if(oldval == ct_option) {
                    alert("이미 선택된 옵션입니다.");
                    spladd = false;
                    return false;
                }
            });

            // 추가옵션정보
            $.post(
                "./itemsupplementinfo.php",
                { it_id: "<? echo $it_id; ?>", sp_id: sp_id },
                function(data) {
                    // 재고체크
                    if(parseInt(data.qty) < 1) {
                        alert("해당 상품은 재고가 부족하여 구매할 수 없습니다.");
                        spladd = false;
                        return false;
                    }

                    if(spladd) {
                        splcontent += "<li>";
                        splcontent += "<input type=\"hidden\" name=\"ct_id[]\" value=\"\" />";
                        splcontent += "<input type=\"hidden\" name=\"is_delete[]\" value=\"0\" />";
                        splcontent += "<input type=\"hidden\" name=\"is_option[]\" value=\"2\" />";
                        splcontent += "<input type=\"hidden\" name=\"opt_id[]\" value=\""+ sp_id + "\" />";
                        splcontent += "<input type=\"hidden\" name=\"ct_option[]\" value=\""+ct_option+"\" />";
                        splcontent += "<input type=\"hidden\" name=\"it_amount[]\" value=\"0\" />";
                        splcontent += "<input type=\"hidden\" name=\"ct_amount[]\" value=\"" + data.amount + "\" />";
                        splcontent += "<span class=\"option-stock\">" + data.qty + "</span>";
                        splcontent += "<span class=\"selected-supplement\">" + ct_option + "</span>";
                        splcontent += "<span class=\"supplement-price\"> (+" + number_format(String(data.amount)) + "원)</span>";
                        splcontent += "<span class=\"item-count\"> <input type=\"text\" name=\"ct_qty[]\" value=\"1\" maxlength=\"4\" /></span>";
                        splcontent += "<span class=\"add-item\"> + </span><span class=\"subtract-item\"> - </span>";
                        splcontent += "<span class=\"option-delete\"> 삭제</span>";
                        splcontent += "</li>";

                        var resultcount = $("#optionlist ul li").size();
                        if(resultcount > 0) {
                            $("#optionlist ul li:last").after(splcontent);
                        } else {
                            $("#optionlist ul").html(splcontent);
                        }

                        calculatePrice();
                    }
                }, "json"
            );
        }
    });

    // 상품개수증가
    $("span.add-item").live("click", function() {
        var $cntinput = $(this).closest("li").find("input[name^=ct_qty]");
        var count = parseInt($cntinput.val());
        count++;

        // 재고체크
        var option_stock = $(this).closest("li").find("span.option-stock").text().replace(/[^0-9]/g, "");
        if(option_stock == "") {
            option_stock = 0;
        } else {
            option_stock = parseInt(option_stock);
        }

        if(option_stock < count) {
            alert("해당 상품은 " + count + "개 이상 주문할 수 없습니다.");
            $(this).val(option_stock);
            return false;
        }

        $cntinput.val(count);

        calculatePrice();
    });

    // 상품개수감소
    $("span.subtract-item").live("click", function() {
        var $cntinput = $(this).closest("li").find("input[name^=ct_qty]");
        var count = parseInt($cntinput.val());
        count--;

        if(count < 1) {
            alert("상품개수는 1이상 입력해 주십시오.");
            count = 1;
        }

        $cntinput.val(count);

        calculatePrice();
    });

    // 옵션삭제
    $("span.option-delete").live("click", function() {
        if(confirm("해당 옵션을 삭제하시겠습니까?")) {
            var $li = $(this).closest("li");
            var is_option = parseInt($li.find("input[name^=is_option]").val());

            if(is_option != 2) { // 선택옵션 삭제일 때 체크
                // 추가옵션 개수 체크
                var sp_count = $("#optionlist li:visible input[name^=is_option]").filter("input[value=2]").size();

                // 추가옵션있을 경우 선택옵션이 반드시 1개는 있어야 함
                if(sp_count > 0) {
                    var opt_count = $("#optionlist li:visible input[name^=is_option]").not("input[value=2]").size();
                    if(opt_count < 2) {
                        alert("추가옵션이 있을 경우 선택옵션을 모두 삭제할 수 없습니다.");
                        return false;
                    }
                }
            }

            $li.css("display", "none");
            $li.find("input[name^=is_delete]").val(1);

            calculatePrice();
        }
    });

    // 수량변경
    $("input[name^=ct_qty]").live("keyup", function() {
        var val = $(this).val().replace(/[^0-9]/g, "");
        if(val == "") {
            //alert('구매수량을 입력해 주세요.');
            return false;
        }

        qty = parseInt(val);

        if(qty < 1) {
            alert("수량은 1이상만 가능합니다.");
            return false;
        }

        if(qty > 9999) {
            alert("수량은 9999이하만 가능합니다.");
            return false;
        }

        // 옵션재고체크
        var option_stock = $(this).closest("li").find("span.option-stock").text().replace(/[^0-9]/g, "");
        if(option_stock == "") {
            option_stock = 0;
        } else {
            option_stock = parseInt(option_stock);
        }

        if(option_stock < qty) {
            alert("해당 상품은 " + qty + "개 이상 주문할 수 없습니다.");
            $(this).val(option_stock);
        }

        calculatePrice();
    });

    // 창닫기
    $("#winclose").click(function() {
        self.close();
    });
});

function optionDisplay()
{
    var option = "";
    var opt_id = "";
    var sep = "";
    var deli = "";
    var optionadd = true;

    $("select[name^=item-option-]").each(function(index) {
        var opt = $(this).val();
        var subj = $("span.opt_subject:eq("+index+")").text();

        option += sep + subj + " : " + opt;
        opt_id += deli + opt;

        sep = " / ";
        deli = chr(30);
    });

    // 선택된 옵션체크
    $("#optionlist ul li:visible span.selected-option").each(function() {
        var oldoption = $(this).html();

        if(oldoption == option) {
            alert("이미 선택된 옵션입니다.");
            optionadd = false;
            return false;
        }
    });

    if(optionadd) {
        // 옵션정보
        $.post(
            "./itemoptioninfo.php",
            { it_id: "<? echo $it_id; ?>", opt_id: opt_id },
            function(data) {
                if(parseInt(data.qty) < 1) {
                    alert("해당 상품은 재고가 부족하여 구매할 수 없습니다.");
                    return false;
                }

                var resultcount = $("#optionlist ul li").size();
                var optioncontent = "<li>";
                optioncontent += "<input type=\"hidden\" name=\"ct_id[]\" value=\"\" />";
                optioncontent += "<input type=\"hidden\" name=\"is_delete[]\" value=\"0\" />";
                optioncontent += "<input type=\"hidden\" name=\"is_option[]\" value=\"1\" />";
                optioncontent += "<input type=\"hidden\" name=\"opt_id[]\" value=\""+ opt_id + "\" />";
                optioncontent += "<input type=\"hidden\" name=\"ct_option[]\" value=\""+ option + "\" />";
                optioncontent += "<input type=\"hidden\" name=\"it_amount[]\" value=\"<? echo $item_amount; ?>\" />";
                optioncontent += "<input type=\"hidden\" name=\"ct_amount[]\" value=\"" + data.amount + "\" />";
                optioncontent += "<span class=\"option-stock\">" + data.qty + "</span>";
                optioncontent += "<span class=\"selected-option\">" + option + "</span>";
                optioncontent += "<span class=\"option-price\"> (+" + number_format(String(data.amount)) + "원)</span>";
                optioncontent += "<span class=\"item-count\"> <input type=\"text\" name=\"ct_qty[]\" value=\"1\" maxlength=\"4\" /></span>";
                optioncontent += "<span class=\"add-item\"> + </span><span class=\"subtract-item\"> - </span>";
                optioncontent += "<span class=\"option-delete\"> 삭제</span>";
                optioncontent += "</li>";

                if(resultcount > 0) {
                    $("#optionlist ul li:last").after(optioncontent);
                } else {
                    $("#optionlist ul").html(optioncontent);
                }

                calculatePrice();
            }, "json"
        );
    }
}

function calculatePrice()
{
    var itemprice = parseInt(<?php echo $item_amount; ?>);
    var optiontotalprice = 0;

    $("#optionlist ul li:visible").each(function() {
        var optprc = parseInt($(this).find("input[name^=ct_amount]").val());
        var itcnt = parseInt($(this).find("input[name^=ct_qty]").val());
        var is_option = parseInt($(this).find("input[name^=is_option]").val());

        if(is_option != 2) {
            optiontotalprice += (itemprice + optprc) * itcnt;
        } else {
            optiontotalprice += optprc * itcnt;
        }
    });

    $("#total-price span").text(number_format(String(optiontotalprice)) + "원");
}
</script>

<?php
include_once($g4['path'] . '/tail.sub.php');
?>