<?php
$sub_menu = '300820';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '글,댓글 현황';
include_once ('./admin.head.php');

$graph = isset($_GET['graph']) ? clean_xss_tags($_GET['graph'], 1, 1) : '';
$period = isset($_GET['period']) ? clean_xss_tags($_GET['period'], 1, 1) : '';

// http://www.jqplot.com/
add_stylesheet('<link rel="stylesheet" href="'.G5_PLUGIN_URL.'/jqplot/jquery.jqplot.css">', 1);
add_javascript('<script src="'.G5_PLUGIN_URL.'/jqplot/jquery.jqplot.js"></script>', 1);
add_javascript('<script src="'.G5_PLUGIN_URL.'/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>', 1);
add_javascript('<script src="'.G5_PLUGIN_URL.'/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>', 1);
add_javascript('<script src="'.G5_PLUGIN_URL.'/jqplot/plugins/jqplot.pointLabels.min.js"></script>', 1);
add_javascript('<!--[if lt IE 9]><script src="'.G5_PLUGIN_URL.'/jqplot/excanvas.js"></script><![endif]-->', 1);

if (!($graph == 'line' || $graph == 'bar'))
    $graph = 'line';

if ($graph == 'bar') {
    // 바 타입으로 사용하는 코드입니다.
    add_javascript('<script src="'.G5_PLUGIN_URL.'/jqplot/jqplot.barRenderer.min.js"></script>', 1);
    add_javascript('<script src="'.G5_PLUGIN_URL.'/jqplot/jqplot.categoryAxisRenderer.min.js"></script>', 1);
    add_javascript('<script src="'.G5_PLUGIN_URL.'/jqplot/jqplot.pointLabels.min.js"></script>', 1);
}

$period_array = array(
    '오늘'=>array('시간', 0),
    '어제'=>array('시간', 0),
    '7일전'=>array('일', 7),
    '14일전'=>array('일', 14),
    '30일전'=>array('일', 30),
    '3개월전'=>array('주', 90),
    '6개월전'=>array('주', 180),
    '1년전'=>array('월', 365),
    '2년전'=>array('월', 365*2),
    '3년전'=>array('월', 365*3),
    '5년전'=>array('년', 365*5),
    '10년전'=>array('년', 365*10),
);

$is_period = false;
foreach($period_array as $key=>$value) {
    if ($key == $period) {
        $is_period = true;
        break;
    }
}
if (!$is_period)
    $period = '오늘';

$day = $period_array[$period][0];

$today = date('Y-m-d', G5_SERVER_TIME);
$yesterday = date('Y-m-d', G5_SERVER_TIME - 86400);

if ($period == '오늘') {
    $from = $today;
    $to = $from;
} else if ($period == '어제') {
    $from = $yesterday;
    $to = $from;
} else if ($period == '내일') {
    $from = date('Y-m-d', G5_SERVER_TIME + (86400 * 2));
    $to = $from;
} else {
    $from = date('Y-m-d', G5_SERVER_TIME - (86400 * $period_array[$period][1]));
    $to = $yesterday;
}

$sql_bo_table = '';
if ($bo_table)
    $sql_bo_table = "and bo_table = '$bo_table'";

$line1 = $line2 = array();

switch ($day) {
    case '시간' :
        $sql = " select substr(bn_datetime,6,8) as hours, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between '$from' and '$to' {$sql_bo_table} group by hours order by bn_datetime ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 월-일 시간
            $line1[] = "['".substr($row['hours'],0,8)."',".$row['wcount'].']';
            $line2[] = "['".substr($row['hours'],0,8)."',".$row['ccount'].']';
        }
        break;
    case '일' :
        $sql  = " select substr(bn_datetime,1,10) as days, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between '$from' and '$to' {$sql_bo_table} group by days order by bn_datetime ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 월-일
            $line1[] = "['".substr($row['days'],5,5)."',".$row['wcount'].']';
            $line2[] = "['".substr($row['days'],5,5)."',".$row['ccount'].']';
        }
        break;
    case '주' :
        $sql  = " select concat(substr(bn_datetime,1,4), '-', weekofyear(bn_datetime)) as weeks, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between '$from' and '$to' {$sql_bo_table} group by weeks order by bn_datetime ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 올해의 몇주로 보여주면 바로 확인이 안되므로 주를 날짜로 바꾼다.
            // 년-월-일
            list($lyear, $lweek) = explode('-', $row['weeks']);
            $date = date('y-m-d', strtotime($lyear.'W'.str_pad($lweek, 2, '0', STR_PAD_LEFT)));
            $line1[] = "['".$date."',".$row['wcount'].']';
            $line2[] = "['".$date."',".$row['ccount'].']';
        }
        break;
    case '월' :
        $sql  = " select substr(bn_datetime,1,7) as months, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between '$from' and '$to' {$sql_bo_table} group by months order by bn_datetime ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 년-월
            $line1[] = "['".substr($row['months'],2,5)."',".$row['wcount'].']';
            $line2[] = "['".substr($row['months'],2,5)."',".$row['ccount'].']';
        }
        break;
    case '년' :
        $sql  = " select substr(bn_datetime,1,4) as years, sum(if(wr_id=wr_parent,1,0)) as wcount, sum(if(wr_id=wr_parent,0,1)) as ccount from {$g5['board_new_table']} where substr(bn_datetime,1,10) between '$from' and '$to' {$sql_bo_table} group by years order by bn_datetime ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 년(4자리)
            $line1[] = "['".substr($row['years'],0,4)."',".$row['wcount'].']';
            $line2[] = "['".substr($row['years'],0,4)."',".$row['ccount'].']';
        }
        break;
}
?>
<div id="wr_cont">
    <form>
    <select name="bo_table">
    <option value="">전체게시판</option>
    <?php
    $sql = " select bo_table, bo_subject from {$g5['board_table']} order by bo_count_write desc ";
    $result = sql_query($sql);
    for($i=0; $row=sql_fetch_array($result); $i++) {
        echo "<option value=\"{$row['bo_table']}\"";
        if ($bo_table == $row['bo_table'])
            echo ' selected="selected"';
        echo ">{$row['bo_subject']}</option>\n";
    }
    ?>
    </select>

    <select name="period">
    <?php
    foreach($period_array as $key=>$value) {
        echo "<option value=\"{$key}\"";
        if ($key == $period)
            echo " selected=\"selected\"";
        echo ">{$key}</option>\n";
    }
    ?>
    </select>

    <select name="graph">
    <option value="line" <?php echo ($graph == 'line' ? 'selected="selected"' : ''); ?>>선그래프</option>
    <option value="bar" <?php echo ($graph == 'bar' ? 'selected="selected"' : ''); ?>>막대그래프</option>
    </select>

    <input type="submit" class="btn_submit" value="확인">
    </form>
    <ul id="grp_color">
        <li><span></span>글 수</li>
        <li class="color2"><span></span>댓글 수</li>
    </ul>
</div>
<br>
<div id="chart_wr">
<?php
if (empty($line1) || empty($line2)) {
    echo "<h5>그래프를 만들 데이터가 없습니다.</h5>\n";
} else {
?>
<div id="chart1" style="height:500px; width:100%;"></div>
<script>
$(document).ready(function(){
    var line1 = [<?php echo implode(',', $line1); ?>];
    var line2 = [<?php echo implode(',', $line2); ?>];
    var plot1 = $.jqplot ('chart1', [line1, line2], {
            seriesDefaults: {
                <?php if ($graph == 'bar') { ?>
                renderer:$.jqplot.BarRenderer,
                <?php } ?>
                pointLabels: { show: true }
            },
            axes:{
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    label: '<?php echo $day; ?>',
                    pad:0,
                    max:23
                },
                yaxis: {
                    label: '글수',
                    min: 0

                }
            }
        });
});
</script>
<?php
}
?>
</div>
<?php
include_once ('./admin.tail.php');