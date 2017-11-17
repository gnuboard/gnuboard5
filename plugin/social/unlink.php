<?php
include_once('_common.php');

$mp_no = isset($_REQUEST['mp_no']) ? (int) $_REQUEST['mp_no'] : 0;
$nonce = isset($_POST['nonce']) ? preg_replace('/[^\da-z]/i', '',$_POST['nonce']) : 0;
$provider = social_get_request_provider();

if( !($mp_no || $provider) || !$member['mb_id'] )
    die("{\"error\":\"회원이 아니거나 해당값이 넘어오지 않았습니다.\"}");

$mb_id = $member['mb_id'];

if($is_admin == 'super'){   //최고관리자인 경우
    if( isset($_REQUEST['mb_id']) && !empty($_REQUEST['mb_id']) ){      //mb_id 요청이 있다면
        $mb_id = addslashes(strip_tags($_REQUEST['mb_id']));
    }
} else {
    // 비회원인 경우 nonce를 체크한다.

    if( ! social_nonce_is_valid($nonce, strtolower($provider), session_id()) ){
        die("{\"error\":\"권한이 없거나 잘못된 요청입니다.\"}");
    }
}

if($mp_no){
    $sql = "SELECT * from {$g5['social_profile_table']} where mb_id= '".$mb_id."' and mp_no= $mp_no";
    $row = sql_fetch($sql);
} else if($provider){
    $sql = "SELECT * from {$g5['social_profile_table']} where mb_id= '".$mb_id."' and provider= '".$provider."'";
    $row = sql_fetch($sql);
}

if( $row['mp_no'] ){

    social_member_link_delete($mb_id, $row['mp_no']);
    
    if( $provider === get_session('ss_social_provider') ){
        set_session('ss_social_provider', '');
    }

    die("{\"error\":\"\", \"mp_no\":".$row['mp_no']."}");

} else {

    die("{\"error\":\"잘못된 요청입니다.\"}");

}
?>