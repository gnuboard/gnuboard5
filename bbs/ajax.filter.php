<?php
include_once('./_common.php');

$subject = strip_tags($_POST['subject']);
$content = strip_tags($_POST['content']);

//$filter = explode(",", strtolower(trim($config['cf_filter'])));
// strtolower 에 의한 한글 변형으로 아래 코드로 대체 (곱슬최씨님이 알려 주셨습니다.)
$filter = explode(",", trim($config['cf_filter']));
for ($i=0; $i<count($filter); $i++) {
    $str = $filter[$i];

    // 제목 필터링 (찾으면 중지)
    $subj = "";
    $pos = stripos($subject, $str);
    if ($pos !== false) {
        $subj = $str;
        break;
    }

    // 내용 필터링 (찾으면 중지)
    $cont = "";
    $pos = stripos($content, $str);
    if ($pos !== false) {
        $cont = $str;
        break;
    }
}

die("{\"subject\":\"$subj\",\"content\":\"$cont\"}");
?>