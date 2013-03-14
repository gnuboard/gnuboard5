<?
if (!defined('_GNUBOARD_')) exit;

// 설문조사
function poll($skin_dir="basic", $po_id=false)
{
    global $config, $member, $g4;

    // 투표번호가 넘어오지 않았다면 가장 큰(최근에 등록한) 투표번호를 얻는다
    if (empty($po_id)) 
    {
        $po_id = $config['cf_max_po_id'];
        if (empty($po_id))
            return "<!-- po_id를 찾을 수 없습니다. -->";
    }

    ob_start();
    $poll_skin_path = "$g4[path]/skin/poll/$skin_dir";
    include_once ("$poll_skin_path/poll.skin.php");
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
?>