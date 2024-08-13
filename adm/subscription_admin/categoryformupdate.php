<?php
$sub_menu = '600200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$sc_include_head = isset($_POST['sc_include_head']) ? trim($_POST['sc_include_head']) : '';
$sc_include_tail = isset($_POST['sc_include_tail']) ? trim($_POST['sc_include_tail']) : '';
$sc_id = isset($_REQUEST['sc_id']) ? preg_replace('/[^0-9a-z]/i', '', $_REQUEST['sc_id']) : '';

if( ! $sc_id ){
    alert('', G5_SUBSCRIPTION_URL);
}

if ($file = $sc_include_head) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if (! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || !preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("상단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if ($file = $sc_include_tail) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if (! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || !preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("하단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if( $sc_id ){
    $sql = " select * from {$g5['g5_subscription_category_table']} where sc_id = '$sc_id' ";
    $ca = sql_fetch($sql);

    if ($ca && ($ca['sc_include_head'] !== $sc_include_head || $ca['sc_include_tail'] !== $sc_include_tail) && function_exists('get_admin_captcha_by') && get_admin_captcha_by()){
        include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

        if (!chk_captcha()) {
            alert('자동등록방지 숫자가 틀렸습니다.');
        }
    }
}

$check_str_keys = array(
'sc_order'=>'int',
'sc_img_width'=>'int',
'sc_img_height'=>'int',
'sc_name'=>'str',
'sc_mb_id'=>'str',
'sc_nocoupon'=>'str',
'sc_mobile_skin_dir'=>'str',
'sc_skin'=>'str',
'sc_mobile_skin'=>'str',
'sc_list_mod'=>'int',
'sc_list_row'=>'int',
'sc_mobile_img_width'=>'int',
'sc_mobile_img_height'=>'int',
'sc_mobile_list_mod'=>'int',
'sc_mobile_list_row'=>'int',
'sc_sell_email'=>'str',
'sc_use'=>'int',
'sc_stock_qty'=>'int',
'sc_explan_html'=>'int',
'sc_cert_use'=>'int',
'sc_adult_use'=>'int',
'sc_skin_dir'=>'str'
);

for($i=0;$i<=10;$i++){
    $check_str_keys['sc_'.$i.'_subj'] = 'str';
    $check_str_keys['sc_'.$i] = 'str';
}

foreach( $check_str_keys as $key=>$val ){
    if( $val === 'int' ){
        $value = isset($_POST[$key]) ? (int) $_POST[$key] : 0;
    } else {
        $value = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
    }
    $$key = $_POST[$key] = $value;
}

$sc_head_html = isset($_POST['sc_head_html']) ? $_POST['sc_head_html'] : '';
$sc_tail_html = isset($_POST['sc_tail_html']) ? $_POST['sc_tail_html'] : '';
$sc_mobile_head_html = isset($_POST['sc_mobile_head_html']) ? $_POST['sc_mobile_head_html'] : '';
$sc_mobile_tail_html = isset($_POST['sc_mobile_tail_html']) ? $_POST['sc_mobile_tail_html'] : '';

if(!is_include_path_check($sc_include_head, 1)) {
    alert('상단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

if(!is_include_path_check($sc_include_tail, 1)) {
    alert('하단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

$check_keys = array('sc_skin_dir', 'sc_mobile_skin_dir', 'sc_skin', 'sc_mobile_skin'); 

foreach( $check_keys as $key ){
    if( isset($$key) && preg_match('#\.+(\/|\\\)#', $$key) ){
        alert('스킨명 또는 경로에 포함시킬수 없는 문자열이 있습니다.');
    }
}

if( function_exists('filter_input_include_path') ){
    $sc_include_head = filter_input_include_path($sc_include_head);
    $sc_include_tail = filter_input_include_path($sc_include_tail);
}

if ($w == "u" || $w == "d")
    check_demo();

auth_check_menu($auth, $sub_menu, "d");

check_admin_token();

if ($w == 'd' && $is_admin != 'super')
    alert("최고관리자만 분류를 삭제할 수 있습니다.");

if ($w == "" || $w == "u")
{
    if ($sc_mb_id)
    {
        $row = get_member($sc_mb_id, 'mb_id');
        if (!$row['mb_id'])
            alert("\'$sc_mb_id\' 은(는) 존재하는 회원아이디가 아닙니다.");
    }
}

if( $sc_skin && ! is_include_path_check($sc_skin) ){
    alert('오류 : 데이터폴더가 포함된 path 를 포함할수 없습니다.');
}

$sql_common = " sc_order                = '$sc_order',
                sc_skin_dir             = '$sc_skin_dir',
                sc_mobile_skin_dir      = '$sc_mobile_skin_dir',
                sc_skin                 = '$sc_skin',
                sc_mobile_skin          = '$sc_mobile_skin',
                sc_img_width            = '$sc_img_width',
                sc_img_height           = '$sc_img_height',
				sc_list_mod             = '$sc_list_mod',
				sc_list_row             = '$sc_list_row',
                sc_mobile_img_width     = '$sc_mobile_img_width',
                sc_mobile_img_height    = '$sc_mobile_img_height',
				sc_mobile_list_mod      = '$sc_mobile_list_mod',
                sc_mobile_list_row      = '$sc_mobile_list_row',
                sc_sell_email           = '$sc_sell_email',
                sc_use                  = '$sc_use',
                sc_stock_qty            = '$sc_stock_qty',
                sc_explan_html          = '$sc_explan_html',
                sc_head_html            = '$sc_head_html',
                sc_tail_html            = '$sc_tail_html',
                sc_mobile_head_html     = '$sc_mobile_head_html',
                sc_mobile_tail_html     = '$sc_mobile_tail_html',
                sc_include_head         = '$sc_include_head',
                sc_include_tail         = '$sc_include_tail',
                sc_mb_id                = '$sc_mb_id',
                sc_cert_use             = '$sc_cert_use',
                sc_adult_use            = '$sc_adult_use',
                sc_nocoupon             = '$sc_nocoupon',
                sc_1_subj               = '$sc_1_subj',
                sc_2_subj               = '$sc_2_subj',
                sc_3_subj               = '$sc_3_subj',
                sc_4_subj               = '$sc_4_subj',
                sc_5_subj               = '$sc_5_subj',
                sc_6_subj               = '$sc_6_subj',
                sc_7_subj               = '$sc_7_subj',
                sc_8_subj               = '$sc_8_subj',
                sc_9_subj               = '$sc_9_subj',
                sc_10_subj              = '$sc_10_subj',
                sc_1                    = '$sc_1',
                sc_2                    = '$sc_2',
                sc_3                    = '$sc_3',
                sc_4                    = '$sc_4',
                sc_5                    = '$sc_5',
                sc_6                    = '$sc_6',
                sc_7                    = '$sc_7',
                sc_8                    = '$sc_8',
                sc_9                    = '$sc_9',
                sc_10                   = '$sc_10' ";


if ($w == "")
{
    if (!trim($sc_id))
        alert("분류 코드가 없으므로 분류를 추가하실 수 없습니다.");

    // 소문자로 변환
    $sc_id = strtolower($sc_id);

    $sql = " insert {$g5['g5_subscription_category_table']}
                set sc_id   = '$sc_id',
                    sc_name = '$sc_name',
                    $sql_common ";

    sql_query($sql);
    run_event('subscription_admin_category_created', $sc_id);
}
else if ($w == "u")
{
    $sql = " update {$g5['g5_subscription_category_table']}
                set sc_name = '$sc_name',
                    $sql_common
              where sc_id = '$sc_id' ";
    sql_query($sql);

    // 하위분류를 똑같은 설정으로 반영
    if (isset($_POST['sub_category']) && $_POST['sub_category']) {
        $len = strlen($sc_id);
        $sql = " update {$g5['g5_subscription_category_table']}
                    set $sql_common
                  where SUBSTRING(sc_id,1,$len) = '$sc_id' ";
        if ($is_admin != 'super')
            $sql .= " and sc_mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }
    run_event('subscription_admin_category_updated', $sc_id);
}
else if ($w == "d")
{
    // 분류의 길이
    $len = strlen($sc_id);

    $sql = " select COUNT(*) as cnt from {$g5['g5_subscription_category_table']}
              where SUBSTRING(sc_id,1,$len) = '$sc_id'
                and sc_id <> '$sc_id' ";
    $row = sql_fetch($sql);
    if ($row['cnt'] > 0)
        alert("이 분류에 속한 하위 분류가 있으므로 삭제 할 수 없습니다.\\n\\n하위분류를 우선 삭제하여 주십시오.");

    $str = $comma = "";
    $sql = " select it_id from {$g5['g5_subscription_item_table']} where sc_id = '$sc_id' ";
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
    $sql = " delete from {$g5['g5_subscription_category_table']} where sc_id = '$sc_id' ";
    sql_query($sql);
    run_event('subscription_admin_category_deleted', $sc_id);
}

if(function_exists('get_admin_captcha_by'))
    get_admin_captcha_by('remove');

if ($w == "" || $w == "u")
{
    goto_url("./categoryform.php?w=u&amp;sc_id=$sc_id&amp;$qstr");
} else {
    goto_url("./categorylist.php?$qstr");
}