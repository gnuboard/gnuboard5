<?
if (!defined('_GNUBOARD_')) exit;

// 인기검색어 출력
// $skin_dir : 스킨 디렉토리
// $pop_cnt : 검색어 몇개
// $date_cnt : 몇일 동안
function popular($skin_dir='basic', $pop_cnt=7, $date_cnt=3)
{
    global $config, $g4;

    if (!$skin_dir) $skin_dir = 'basic';

    $date_gap = date("Y-m-d", $g4[server_time] - ($date_cnt * 86400));
    $sql = " select pp_word, count(*) as cnt from $g4[popular_table]
              where pp_date between '$date_gap' and '$g4[time_ymd]'
              group by pp_word
              order by cnt desc, pp_word
              limit 0, $pop_cnt ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) 
    {
        $list[$i] = $row;
        // 스크립트등의 실행금지
        $list[$i][pp_word] = get_text($list[$i][pp_word]);
    }

    ob_start();
    $popular_skin_path = "$g4[path]/skin/popular/$skin_dir";
    include_once ("$popular_skin_path/popular.skin.php");
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
?>