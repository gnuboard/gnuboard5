<? 
include_once("./_common.php");

if ($w == '' || $w == 'u')
{
    /*
    if (!(trim($is_key) && trim($is_key) == get_session('ss_norobot_key'))) {
        alert('자동등록방지 코드가 틀립니다.');
    }
    */

    $key = get_session("captcha_keystring");
    if (!($key && $key == $_POST[is_key])) {
        session_unregister("captcha_keystring");
        alert("정상적인 접근이 아닌것 같습니다.");
    }

    /*
    // 세션에 저장된 토큰과 폼으로 넘어온 토큰을 비교하여 틀리면 에러
    if ($token && get_session("ss_token") == $token) {
        // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
        set_session("ss_token", "");
    } else {
        alert_close("토큰 에러");
    }
    */

    if (!$is_member) 
    {
        if (!trim($_POST[is_name])) alert("이름을 입력하여 주십시오.");
        if (!trim($_POST[is_password])) alert("패스워드를 입력하여 주십시오.");
    }
    else
    {
        $is_name = $member[mb_name];
        $is_password = $member[mb_password];
    }

    if (!trim($_POST[is_subject])) alert("제목을 입력하여 주십시오.");
    if (!trim($_POST[is_content])) alert("내용을 입력하여 주십시오.");

    $is_password = sql_password($is_password);
}

$url = "./item.php?it_id=$it_id";

if ($w == '')
{
    $sql = " select max(is_id) as max_is_id from $g4[yc4_item_ps_table] ";
    $row = sql_fetch($sql);
    $max_is_id = $row[max_is_id];

    $sql = " select max(is_id) as max_is_id from $g4[yc4_item_ps_table]
              where it_id = '$it_id'
                and mb_id = '$member[mb_id]' ";
    $row = sql_fetch($sql);
    if ($row[max_is_id] && $row[max_is_id] == $max_is_id) 
        alert("같은 상품에 대하여 계속해서 평가하실 수 없습니다.");

    $sql = "insert $g4[yc4_item_ps_table]
               set it_id = '$it_id',
                   mb_id = '$member[mb_id]',
                   is_score = '$is_score',
                   is_name = '$is_name',
                   is_password = '$is_password',
                   is_subject = '$is_subject',
                   is_content = '$is_content',
                   is_time = '$g4[time_ymdhis]',
                   is_ip = '$_SERVER[REMOTE_ADDR]' ";
    if (!$default[de_item_ps_use])
        $sql .= ", is_confirm = '1' ";
    sql_query($sql);

    if ($default[de_item_ps_use])
        alert("평가하신 글은 관리자가 확인한 후에 표시됩니다.", $url);
    else
        goto_url($url);
} 
else if ($w == 'u')
{
    $sql = " select is_password from $g4[yc4_item_ps_table] where is_id = '$is_id' ";
    $row = sql_fetch($sql);
    if ($row[is_password] != $is_password)
        alert("패스워드가 틀리므로 수정하실 수 없습니다.");

    $sql = " update $g4[yc4_item_ps_table] 
                set is_subject = '$is_subject',
                    is_content = '$is_content',
                    is_score = '$is_score'
              where is_id = '$is_id' ";
    sql_query($sql);

    goto_url($url);
}
else if ($w == 'd')
{
    if ($is_member)
    {
        $sql = " select count(*) as cnt from $g4[yc4_item_ps_table] where mb_id = '$member[mb_id]' and is_id = '$is_id' ";
        $row = sql_fetch($sql);
        if (!$row[cnt])
            alert("자신의 사용후기만 삭제하실 수 있습니다.");
    }
    else
    {
        $is_password = sql_password($is_password);

        $sql = " select is_password from $g4[yc4_item_ps_table] where is_id = '$is_id' ";
        $row = sql_fetch($sql);
        if ($row[is_password] != $is_password)
            alert("패스워드가 틀리므로 삭제하실 수 없습니다.");
    }

    $sql = " delete from $g4[yc4_item_ps_table] where mb_id = '$member[mb_id]' and is_id = '$is_id' ";
    sql_query($sql);

    goto_url($url);
}
?>
