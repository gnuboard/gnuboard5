<?php
$sub_menu = '400200';
include_once('./_common.php');

if ($file = $_POST['ca_include_head']) {
    if (!preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("상단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if ($file = $_POST['ca_include_tail']) {
    if (!preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("하단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if ($w == "u" || $w == "d")
    check_demo();

auth_check($auth[$sub_menu], "d");

check_admin_token();

if ($w == 'd' && $is_admin != 'super')
    alert("최고관리자만 분류를 삭제할 수 있습니다.");

if ($w == "" || $w == "u")
{
    if ($ca_mb_id)
    {
        $sql = " select mb_id from {$g5['member_table']} where mb_id = '$ca_mb_id' ";
        $row = sql_fetch($sql);
        if (!$row['mb_id'])
            alert("\'$ca_mb_id\' 은(는) 존재하는 회원아이디가 아닙니다.");
    }
}

$sql_common = " ca_order                = '$ca_order',
                ca_skin_dir             = '$ca_skin_dir',
                ca_mobile_skin_dir      = '$ca_mobile_skin_dir',
                ca_skin                 = '$ca_skin',
                ca_mobile_skin          = '$ca_mobile_skin',
                ca_img_width            = '$ca_img_width',
                ca_img_height           = '$ca_img_height',
				ca_list_mod             = '$ca_list_mod',
				ca_list_row             = '$ca_list_row',
                ca_mobile_img_width     = '$ca_mobile_img_width',
                ca_mobile_img_height    = '$ca_mobile_img_height',
				ca_mobile_list_mod      = '$ca_mobile_list_mod',
                ca_mobile_list_row      = '$ca_mobile_list_row',
                ca_sell_email           = '$ca_sell_email',
                ca_use                  = '$ca_use',
                ca_stock_qty            = '$ca_stock_qty',
                ca_explan_html          = '$ca_explan_html',
                ca_head_html            = '$ca_head_html',
                ca_tail_html            = '$ca_tail_html',
                ca_mobile_head_html     = '$ca_mobile_head_html',
                ca_mobile_tail_html     = '$ca_mobile_tail_html',
                ca_include_head         = '$ca_include_head',
                ca_include_tail         = '$ca_include_tail',
                ca_mb_id                = '$ca_mb_id',
                ca_cert_use             = '$ca_cert_use',
                ca_adult_use            = '$ca_adult_use',
                ca_nocoupon             = '$ca_nocoupon',
                ca_1_subj               = '$ca_1_subj',
                ca_2_subj               = '$ca_2_subj',
                ca_3_subj               = '$ca_3_subj',
                ca_4_subj               = '$ca_4_subj',
                ca_5_subj               = '$ca_5_subj',
                ca_6_subj               = '$ca_6_subj',
                ca_7_subj               = '$ca_7_subj',
                ca_8_subj               = '$ca_8_subj',
                ca_9_subj               = '$ca_9_subj',
                ca_10_subj              = '$ca_10_subj',
                ca_1                    = '$ca_1',
                ca_2                    = '$ca_2',
                ca_3                    = '$ca_3',
                ca_4                    = '$ca_4',
                ca_5                    = '$ca_5',
                ca_6                    = '$ca_6',
                ca_7                    = '$ca_7',
                ca_8                    = '$ca_8',
                ca_9                    = '$ca_9',
                ca_10                   = '$ca_10' ";


if ($w == "")
{
    if (!trim($ca_id))
        alert("분류 코드가 없으므로 분류를 추가하실 수 없습니다.");

    // 소문자로 변환
    $ca_id = strtolower($ca_id);

    $sql = " insert {$g5['g5_shop_category_table']}
                set ca_id   = '$ca_id',
                    ca_name = '$ca_name',
                    $sql_common ";
    sql_query($sql);
}
else if ($w == "u")
{
    $sql = " update {$g5['g5_shop_category_table']}
                set ca_name = '$ca_name',
                    $sql_common
              where ca_id = '$ca_id' ";
    sql_query($sql);

    // 하위분류를 똑같은 설정으로 반영
    if ($sub_category) {
        $len = strlen($ca_id);
        $sql = " update {$g5['g5_shop_category_table']}
                    set $sql_common
                  where SUBSTRING(ca_id,1,$len) = '$ca_id' ";
        if ($is_admin != 'super')
            $sql .= " and ca_mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }
}
else if ($w == "d")
{
    // 분류의 길이
    $len = strlen($ca_id);

    $sql = " select COUNT(*) as cnt from {$g5['g5_shop_category_table']}
              where SUBSTRING(ca_id,1,$len) = '$ca_id'
                and ca_id <> '$ca_id' ";
    $row = sql_fetch($sql);
    if ($row['cnt'] > 0)
        alert("이 분류에 속한 하위 분류가 있으므로 삭제 할 수 없습니다.\\n\\n하위분류를 우선 삭제하여 주십시오.");

    $str = $comma = "";
    $sql = " select it_id from {$g5['g5_shop_item_table']} where ca_id = '$ca_id' ";
    $result = sql_query($sql);
    $i=0;
    while ($row = sql_fetch_array($result))
    {
        $i++;
        if ($i % 10 == 0) $str .= "\\n";
        $str .= "$comma{$row['it_id']}";
        $comma = " , ";
    }

    if ($str)
        alert("이 분류와 관련된 상품이 총 {$i} 건 존재하므로 상품을 삭제한 후 분류를 삭제하여 주십시오.\\n\\n$str");

    // 분류 삭제
    $sql = " delete from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
    sql_query($sql);
}

if ($w == "" || $w == "u")
{
    goto_url("./categoryform.php?w=u&amp;ca_id=$ca_id&amp;$qstr");
} else {
    goto_url("./categorylist.php?$qstr");
}
?>
