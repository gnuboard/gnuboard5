<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<table id="current_connect_tbl" class="basic_tbl">
<caption>현재접속자 목록</caption>
<thead>
<tr>
    <th scope="col">번호</th>
    <th scope="col">이름</th>
    <th scope="col">위치</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $i<count($list); $i++) {
    $location = conv_content($list[$i]['lo_location'], 0);
    // 최고관리자에게만 허용
    // 이 조건문은 가능한 변경하지 마십시오.
    if ($list[$i]['lo_url'] && $is_admin == 'super') $display_location = "<a href=\"".$list[$i]['lo_url']."\">".$location."</a>";
    else $display_location = $location;
?>
    <tr>
        <td class="td_num"><?=$list[$i]['num']?></td>
        <td class="td_name"><?=$list[$i]['name']?></td>
        <td><?=$display_location?></td>
    </tr>
<?
}
if ($i == 0)
    echo "<tr><td colspan=\"3\" class=\"empty_table\">현재 접속자가 없습니다.</td></tr>";
?>
</tbody>
</table>
