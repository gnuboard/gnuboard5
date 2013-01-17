<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once($g4['path'].'/head.sub.php');

function get_group_name($gr_id)
{
    global $g4;
    $row = sql_fetch(" select gr_subject from $g4[group_table] where gr_id = '$gr_id' ");
    return $row['gr_subject'];
}

if (!$gr_id) {
    $sql = " select gr_id from {$g4['group_table']} where gr_use in ('both', 'mobile') order by gr_order limit 1 ";
    $row = sql_fetch($sql);
    $gr_id = $row[gr_id];
}
?>

<div class="content-primary">
    <ul data-role="listview" data-inset="true" data-dividertheme="f">
        <li data-role="list-divider" class="ui-bar ui-bar-a" style="font-size:1.0em;"><?=get_group_name($gr_id);?> Category</li>
        <?
        $sql = " select bo_subject, bo_table, bo_count_write from {$g4['board_table']} where gr_id = '$gr_id' and bo_use in ('both', 'mobile') order by bo_order_search ";
        $result = sql_query($sql);
        for ($k=0; $row=sql_fetch_array($result); $k++) {
            $active = "";
            if ($row[bo_table] == get_cookie("ck_bo_table")) 
                $active = ' class="active"';

            $li_count = "";
            if ($row[bo_count_write]) 
                $li_count = "<span class=\"ui-li-count\">$row[bo_count_write]</span>";

            echo "\n<li{$active}><a href='$g4[path]/bbs/board.php?bo_table=$row[bo_table]'><span>$row[bo_subject]</span>$li_count</a></li>";
        }
        ?>
    </ul>
</div>

<div class="content-secondary">
    <ul data-role="listview" data-inset="true">
        <li data-role="list-divider" class="ui-bar ui-bar-d" style="font-size:1.0em;">Category List</li>
        <?
        $sql = " select gr_id, gr_subject from {$g4['group_table']} where gr_use in ('both', 'mobile') order by gr_order ";
        $result = sql_query($sql);
        while ($row=sql_fetch_array($result)) {
            $row2 = sql_fetch(" select count(*) as cnt from $g4[board_table] where gr_id = '$row[gr_id]' and bo_use in ('both', 'mobile') ");
            $li_count = "";
            if ($row2[cnt]) 
                $li_count = "<span class=\"ui-li-count\">$row2[cnt]</span>";
            echo "\n<li><a href=\"$g4[path]/?gr_id=$row[gr_id]\"><span>$row[gr_subject]</span>$li_count</a></li>";
        }
        ?>
    </ul>
</div>


<?
include_once($g4['path'].'/tail.sub.php');
?>