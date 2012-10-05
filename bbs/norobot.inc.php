<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

/*
**  $norobot_str 와 $_SESSION[ss_norobot_key] 을 반환함
*/

// 자동등록기를 막아볼까요?
$is_norobot = false;
if ($config[cf_use_norobot]) {
    // 임의의 md5 문자열을 생성
    $tmp_str = substr(md5($g4[server_time]),0,12);
    // 난수 발생기
    list($usec, $sec) = explode(' ', microtime()); 
    $seed =  (float)$sec + ((float)$usec * 100000); 
    srand($seed);
    $keylen = strlen($tmp_str);
    $div = (int)($keylen / 2);
    while (count($arr) < 4) 
    {
        unset($arr);
        for ($i=0; $i<$keylen; $i++) 
        {
            $rnd = rand(1, $keylen);
            $arr[$rnd] = $rnd;
            if ($rnd > $div) break;
        }
    }

    // 배열에 저장된 숫자를 차례대로 정렬
    sort($arr);

    $norobot_key = "";
    $norobot_str = "";
    $m = 0;
    for ($i=0; $i<count($arr); $i++) 
    {
        for ($k=$m; $k<$arr[$i]-1; $k++) 
            $norobot_str .= $tmp_str[$k];
        $norobot_str .= "<font size=3 color=#FF0000><b>{$tmp_str[$k]}</b></font>";
        $norobot_key .= $tmp_str[$k];
        $m = $k + 1;
    }

    if ($m < $keylen) {
        for ($k=$m; $k<$keylen; $k++)
            $norobot_str .= $tmp_str[$k];
    }

    $norobot_str = "<font color=#999999>$norobot_str</font>";

    // 입력, 답변이면서 회원이 아닐 경우만 자동등록방지 사용
    if (($w == "" || $w == "r") && !$member[mb_id]) {
        set_session("ss_norobot_key", $norobot_key);
        $is_norobot = true;
    } 
    else
        set_session("ss_norobot_key", "");
}
?>
<script type='text/javascript'> var md5_norobot_key = '<?=md5($norobot_key)?>'; </script>