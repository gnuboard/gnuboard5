<?
$sub_menu = '400730';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '배너관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['yc4_banner_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>
<style type="text/css">

</style>

<section class="cbox">
    <h2>배너관리</h2>
    <p>건수 <? echo $total_count ?></p>
    <table class="frm_flb">
    <colgroup>
        <col class="grid_1">
        <col class="grid_7">
        <col class="grid_1">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_1">
        <col class="grid_2">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">이미지</th>
        <th scope="col">위치</th>
        <th scope="col">시작일시</th>
        <th scope="col">종료일시</th>
        <th scope="col">출력순서</th>
        <th scope="col">조회</th>
        <th scope="col"><?=icon("입력", "./bannerform.php");?></th>
    </tr>
    </thead>
    <tbody>
    <?
        $sql = " select * from {$g4['yc4_banner_table']}
              order by bn_order, bn_id desc
              limit $from_record, $rows  ";
        $result = sql_query($sql);
        for ($i=0; $row=mysql_fetch_array($result); $i++)
        {
        // 테두리 있는지
        $bn_border  = $row['bn_border'];
        // 새창 띄우기인지
        $bn_new_win = ($row['bn_new_win']) ? "target='_new'" : "";

        $bn_img = "";
        if ($row['bn_url'] && $row['bn_url'] != "http://")
            $bn_img .= "<a href='{$row['bn_url']}' $bn_new_win>";
        $bn_img .= "<img src='".G4_DATA_URL."/banner/{$row['bn_id']}' border='$bn_border' alt='{$row['bn_alt']}'></a>";

        $bn_begin_time = substr($row['bn_begin_time'], 2, 14);
        $bn_end_time   = substr($row['bn_end_time'], 2, 14);

        $s_mod = icon("수정", "./bannerform.php?w=u&bn_id={$row['bn_id']}");
        $s_del = icon("삭제", "javascript:del('./bannerformupdate.php?w=d&bn_id={$row['bn_id']}');");

        $list = $i%2;
        ?>
        <tr class="list<?=$list?> center">
            <td><?=$row['bn_id']?></td>
            <td><?=$bn_img?></td>
            <td><?=$row['bn_position']?></td>
            <td><?=$bn_begin_time?></td>
            <td><?=$bn_end_time?></td>
            <td><?=$row['bn_order']?></td>
            <td><?=$row['bn_hit']?></td>
            <td><?=$s_mod?> <?=$s_del?></td>
        </tr>
        <?
        }

        if ($i == 0) {
        echo "<tr><td colspan=\"8\"><span class=\"point\">자료가 한건도 없습니다.</span></td></tr>\n";
    }
    ?>
    </tbody>
    </table>
    <p><?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?></p>
</section>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
