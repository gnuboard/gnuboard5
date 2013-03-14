<?
$sub_menu = "200800";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g4['title'] = '접속자집계';
include_once('./admin.head.php');
include_once('./visit.sub.php');

$colspan = 5;

$sql_common = " from {$g4['visit_table']} ";
$sql_search = " where vi_date between '{$fr_date}' and '{$to_date}' ";
if (isset($domain))
    $sql_search .= " and vi_referer like '%{$domain}%' ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == '') $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            order by vi_id desc
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);
?>

<section class="cbox">
    <h2>접속자 개요</h2>
    <p>IP, 경로, 브라우저, 운영체제, 일시</p>

    <table>
    <thead>
    <tr>
        <th scope="col">IP</th>
        <th scope="col">접속 경로</th>
        <th scope="col">브라우저</th>
        <th scope="col">운영체제</th>
        <th scope="col">일시</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $brow = get_brow($row['vi_agent']);
        $os   = get_os($row['vi_agent']);

        $link = '';
        $link2 = '';
        $referer = '';
        $title = '';
        if ($row['vi_referer']) {

            $referer = get_text(cut_str($row['vi_referer'], 255, ''));
            $referer = urldecode($referer);

            if (strtolower($g4['charset']) == 'utf-8') {
                if (!is_utf8($referer)) {
                    $referer = iconv('euc-kr', 'utf-8', $referer);
                }
            }
            else {
                if (is_utf8($referer)) {
                    $referer = iconv('utf-8', 'euc-kr', $referer);
                }
            }

            $title = str_replace(array('<', '>', '&'), array("&lt;", "&gt;", "&amp;"), $referer);
            $link = '<a href="'.$row['vi_referer'].'" target="_blank">';
            $link = str_replace('&', "&amp;", $link);
            $link2 = '</a>';
        }

        if ($is_admin == 'super')
            $ip = $row['vi_ip'];
        else
            $ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", "\\1.♡.\\3.\\4", $row['vi_ip']);

        if ($brow == '기타') { $brow = '<span title="'.$row['vi_agent'].'">'.$brow.'</span>'; }
        if ($os == '기타') { $os = '<span title="'.$row['vi_agent'].'">'.$os.'</span>'; }

    ?>
    <tr>
        <td class="td_category"><?=$ip?></td>
        <td><?=$link?><?=$title?><?=$link2?></td>
        <td class="td_category"><?=$brow?></td>
        <td class="td_category"><?=$os?></td>
        <td class="td_time"><?=$row['vi_date']?> <?=$row['vi_time']?></td>
    </tr>

    <?
    }
    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</section>

<?
if (isset($domain)) 
    $qstr .= "&amp;domain=$domain";
$qstr .= "&amp;page=";

$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr");
echo $pagelist;

include_once('./admin.tail.php');
?>
