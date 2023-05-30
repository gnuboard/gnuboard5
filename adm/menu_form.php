<?php
$sub_menu = "100290";
require_once './_common.php';

if ($is_admin != 'super') {
    alert_close('최고관리자만 접근 가능합니다.');
}

$g5['title'] = '메뉴 추가';
require_once G5_PATH . '/head.sub.php';

$new    = isset($_GET['new']) ? clean_xss_tags($_GET['new'], 1, 1) : '';
$code   = isset($_GET['code']) ? (string)preg_replace('/[^0-9a-zA-Z]/', '', $_GET['code']) : '';


if ($new == 'new' || !$code) {
    $code = (int)base_convert(substr($code, 0, 2), 36, 10);
    $code += 36;
    $code = base_convert((string)$code, 10, 36);
}

?>

<div id="menu_frm" class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="fmenuform" id="fmenuform" class="new_win_con">
        <div class="new_win_desc">
            <label for="me_type">대상선택</label>
            <select name="me_type" id="me_type">
                <option value="">직접입력</option>
                <option value="group">게시판그룹</option>
                <option value="board">게시판</option>
                <option value="content">내용관리</option>
            </select>
        </div>
        <div id="menu_result"></div>

    </form>

</div>

<script>
    $(function() {
        $("#menu_result").load(
            "./menu_form_search.php"
        );

        function link_checks_all_chage() {

            var $links = $(opener.document).find("#menulist input[name='me_link[]']"),
                $o_link = $(".td_mngsmall input[name='link[]']"),
                hrefs = [],
                menu_exist = false;

            if ($links.length) {
                $links.each(function(index) {
                    hrefs.push($(this).val());
                });

                $o_link.each(function(index) {
                    if ($.inArray($(this).val(), hrefs) != -1) {
                        $(this).closest("tr").find("td:eq( 0 )").addClass("exist_menu_link");
                        menu_exist = true;
                    }
                });
            }

            // 메뉴가 존재하면 menu_exists_tip 띄움
            if (menu_exist) {
                $(".menu_exists_tip").show();
            } else {
                $(".menu_exists_tip").hide();
            }
        }

        function menu_result_change(type) {

            var dfd = new $.Deferred();

            $("#menu_result").empty().load(
                "./menu_form_search.php", {
                    type: type
                },
                function() {
                    dfd.resolve('Finished');
                }
            );

            return dfd.promise();
        }

        $("#me_type").on("change", function() {
            var type = $(this).val();

            var promise = menu_result_change(type);

            promise.done(function(message) {
                link_checks_all_chage(type);
            });

        });

        $(document).on("click", "#add_manual", function() {
            var me_name = $.trim($("#me_name").val());
            var me_link = $.trim($("#me_link").val());

            add_menu_list(me_name, me_link, "<?php echo $code; ?>");
        });

        $(document).on("click", ".add_select", function() {
            var me_name = $.trim($(this).siblings("input[name='subject[]']").val());
            var me_link = $.trim($(this).siblings("input[name='link[]']").val());

            add_menu_list(me_name, me_link, "<?php echo $code; ?>");
        });
    });

    // 선택 누르면 이 함수가 실행
    function add_menu_list(name, link, code) {
        var $menulist = $("#menulist", opener.document);
        var ms = new Date().getTime();
        var sub_menu_class;

        <?php if ($new == 'new') { ?>
            sub_menu_class = " class=\"td_category\"";
        <?php } else { ?>
            sub_menu_class = " class=\"td_category sub_menu_class\""; 
        <?php } ?>

        var list = "<tr class=\"menu_list menu_group_<?php echo $code; ?>\">";
        list += "<td" + sub_menu_class + ">";
        list += "<label for=\"me_name_" + ms + "\"  class=\"sound_only\">메뉴<strong class=\"sound_only\"> 필수</strong></label>";
        list += "<input type=\"hidden\" name=\"code[]\" value=\"<?php echo $code; ?>\">";
    
        list += "<input type=\"text\" name=\"me_name[]\" value=\"" + name + "\" id=\"me_name_" + ms + "\" required class=\"required frm_input full_input\">";
        list += "</td>";
        list += "<td>";
        list += "<label for=\"me_link_" + ms + "\"  class=\"sound_only\">링크<strong class=\"sound_only\"> 필수</strong></label>";
        list += "<input type=\"text\" name=\"me_link[]\" value=\"" + link + "\" id=\"me_link_" + ms + "\" required class=\"required frm_input full_input\">";
        list += "</td>";
        list += "<td class=\"td_mng\">";
        list += "<label for=\"me_target_" + ms + "\"  class=\"sound_only\">새창</label>";
        list += "<select name=\"me_target[]\" id=\"me_target_" + ms + "\">";
        list += "<option value=\"self\">사용안함</option>";
        list += "<option value=\"blank\">사용함</option>";
        list += "</select>";
        list += "</td>";
        list += "<td class=\"td_numsmall\">";
        list += "<label for=\"me_order_" + ms + "\"  class=\"sound_only\">순서<strong class=\"sound_only\"> 필수</strong></label>";
        list += "<input type=\"text\" name=\"me_order[]\" value=\"0\" id=\"me_order_" + ms + "\" required class=\"required frm_input\" size=\"5\">";
        list += "</td>";
        list += "<td class=\"td_mngsmall\">";
        list += "<label for=\"me_use_" + ms + "\"  class=\"sound_only\">PC사용</label>";
        list += "<select name=\"me_use[]\" id=\"me_use_" + ms + "\">";
        list += "<option value=\"1\">사용함</option>";
        list += "<option value=\"0\">사용안함</option>";
        list += "</select>";
        list += "</td>";
        list += "<td class=\"td_mngsmall\">";
        list += "<label for=\"me_mobile_use_" + ms + "\"  class=\"sound_only\">모바일사용</label>";
        list += "<select name=\"me_mobile_use[]\" id=\"me_mobile_use_" + ms + "\">";
        list += "<option value=\"1\">사용함</option>";
        list += "<option value=\"0\">사용안함</option>";
        list += "</select>";
        list += "</td>";
        list += "<td class=\"td_mng\">";
        <?php if ($new == 'new') { ?>
            list += "<button type=\"button\" class=\"btn_add_submenu btn_03\">추가</button>\n";
        <?php } ?>
        list += "<button type=\"button\" class=\"btn_del_menu btn_02\">삭제</button>";
        list += "</td>";
        list += "</tr>";

        var $menu_last = null;

        if (code)
            $menu_last = $menulist.find("tr.menu_group_" + code + ":last");
        else
            $menu_last = $menulist.find("tr.menu_list:last");

        if ($menu_last.length > 0) {
            $menu_last.after(list);
        } else { // 리스트가 없을 때(추가한 게 없을 때)
            if ($menulist.find("#empty_menu_list").length > 0) // 이미 추가된 게 있으면
                $menulist.find("#empty_menu_list").remove(); // 메뉴리스트가 비지 않았다 

            $menulist.find("table tbody").append(list); // 리스트를 붙혀 
        }

        $menulist.find("tr.menu_list").each(function(index) {
            $(this).removeClass("bg0 bg1")
                .addClass("bg" + (index % 2));
        });
        
        loacation.replace(location.bref);
    }
</script>

<?php
require_once G5_PATH . '/tail.sub.php';
