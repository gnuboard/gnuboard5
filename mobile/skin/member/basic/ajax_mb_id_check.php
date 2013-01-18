<?
include_once("_common.php");

// echo "한글"로 출력하지 않는 이유는 Ajax 는 euc_kr 에서 한글을 제대로 인식하지 못하기 때문
// 여기에서 영문으로 echo 하여 Request 된 값을 Javascript 에서 한글로 메세지를 출력함

if (preg_match("/[^0-9a-z_]+/i", $reg_mb_id)) {
    echo "110"; // 유효하지 않은 회원아이디
} else if (strlen($reg_mb_id) < 3) {
    echo "120"; // 3보다 작은 회원아이디
} else {
    $row = sql_fetch(" select count(*) as cnt from $g4[member_table] where mb_id = '$reg_mb_id' ");
    if ($row[cnt]) {
        echo "130"; // 이미 존재하는 회원아이디
    } else {
        if (preg_match("/[\,]?{$reg_mb_id}/i", $config[cf_prohibit_id]))
            echo "140"; // 예약어로 금지된 회원아이디
        else
            echo "000"; // 정상
    }
}
?>