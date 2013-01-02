<?
include_once('./_common.php');

header("Content-Type: text/html; charset={$g4['charset']}");

$subject = strtolower($_POST['subject']);
$content = strtolower(strip_tags($_POST['content']));

//euc-kr 일 경우 $config['cf_filter'] 를 utf-8로 변환한다.
if (strtolower($g4['charset']) == 'euc-kr') {
    //$subject = convert_charset('utf-8', 'cp949', $subject);
    //$content = convert_charset('utf-8', 'cp949', $content);
    $config['cf_filter'] = convert_charset('cp949', 'utf-8', $config['cf_filter']);
}

//$filter = explode(",", strtolower(trim($config['cf_filter'])));
// strtolower 에 의한 한글 변형으로 아래 코드로 대체 (곱슬최씨님이 알려 주셨습니다.)
$filter = explode(",", trim($config['cf_filter']));
for ($i=0; $i<count($filter); $i++) {
    $str = $filter[$i];

    // 제목 필터링 (찾으면 중지)
    $subj = "";
    $pos = strpos($subject, $str);
    if ($pos !== false) {
        if (strtolower($g4['charset']) == 'euc-kr')
            $subj = convert_charset('utf-8', 'cp949', $str);//cp949 로 변환해서 반환
        else
            $subj = $str;
        break;
    }

    // 내용 필터링 (찾으면 중지)
    $cont = "";
    $pos = strpos($content, $str);
    if ($pos !== false) {
        if (strtolower($g4['charset']) == 'euc-kr')
            $cont = convert_charset('utf-8', 'cp949', $str);//cp949 로 변환해서 반환
        else
            $cont = $str;
        break;
    }
}

die("{\"subject\":\"$subj\",\"content\":\"$cont\"}");
?>