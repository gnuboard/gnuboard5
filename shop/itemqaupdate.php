<? 
include_once("./_common.php");

if ($w == '' || $w == 'u')
{
    /*
    if (!(trim($iq_key) && trim($iq_key) == get_session('ss_norobot_key'))) {
        alert('자동등록방지 코드가 틀립니다.');
    }
    */

    $key = get_session("captcha_keystring");
    if (!($key && $key == $_POST[iq_key])) {
        session_unregister("captcha_keystring");
        alert("정상적인 접근이 아닌것 같습니다.");
    }

    /*
    // 두개씩 비교할 필요가 없음
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
        if (!trim($_POST[iq_name])) alert("이름을 입력하여 주십시오.");
        if (!trim($_POST[iq_password])) alert("패스워드를 입력하여 주십시오.");
    }
    else
    {
        $iq_name = $member[mb_name];
        $iq_password = $member[mb_password];
    }

    $iq_password = sql_password($iq_password);

    if (!trim($_POST[iq_subject])) alert("제목을 입력하여 주십시오.");
    if (!trim($_POST[iq_question])) alert("내용을 입력하여 주십시오.");
}

$url = "./item.php?it_id=$it_id";

if ($w == '')
{
    $sql = " select max(iq_id) as max_iq_id from $g4[yc4_item_qa_table] ";
    $row = sql_fetch($sql);
    $max_iq_id = $row[max_iq_id];

    $sql = " select max(iq_id) as max_iq_id from $g4[yc4_item_qa_table]
              where it_id = '$it_id'
                and mb_id = '$member[mb_id]' ";
    $row = sql_fetch($sql);
    if ($row[max_iq_id] && $row[max_iq_id] == $max_iq_id) 
        alert("같은 상품에 대하여 계속해서 질문 하실 수 없습니다.");

    $sql = "insert $g4[yc4_item_qa_table]
               set it_id = '$it_id',
                   mb_id = '$member[mb_id]',
                   iq_name  = '$iq_name',
                   iq_password  = '$iq_password',
                   iq_subject  = '$iq_subject',
                   iq_question = '$iq_question',
                   iq_time = '$g4[time_ymdhis]',
                   iq_ip = '$REMOTE_ADDR' ";
    sql_query($sql);

    goto_url($url);
}
else if ($w == 'u')
{
    $sql = " select iq_password from $g4[yc4_item_qa_table] where iq_id = '$iq_id' ";
    $row = sql_fetch($sql);
    if ($row[iq_password] != $iq_password)
        alert("패스워드가 틀리므로 수정하실 수 없습니다.");

    $sql = " update $g4[yc4_item_qa_table] 
                set iq_subject = '$iq_subject',
                    iq_question = '$iq_question'
              where iq_id = '$iq_id' ";
    sql_query($sql);

    goto_url($url);
}
else if ($w == 'd')
{
    if ($is_member)
    {
        $sql = " select count(*) as cnt from $g4[yc4_item_qa_table] where mb_id = '$member[mb_id]' and iq_id = '$iq_id' ";
        $row = sql_fetch($sql);
        if (!$row[cnt])
            alert("자신의 상품문의만 삭제하실 수 있습니다.");
    }
    else
    {
        $iq_password = sql_password($iq_password);

        $sql = " select iq_password from $g4[yc4_item_qa_table] where iq_id = '$iq_id' ";
        $row = sql_fetch($sql);
        if ($row[iq_password] != $iq_password)
            alert("패스워드가 틀리므로 삭제하실 수 없습니다.");
    }

    $sql = " delete from $g4[yc4_item_qa_table] where mb_id = '$member[mb_id]' and iq_id = '$iq_id' ";
    sql_query($sql);

    goto_url($url);
}
?>
