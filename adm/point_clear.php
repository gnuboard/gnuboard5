<?php
$sub_menu = "200200";
include_once('./_common.php');

check_demo();

if (!$ok)
    alert();

if ($is_admin != 'super')
    alert('포인트 정리는 최고관리자만 가능합니다.');

$g5['title'] = '포인트 정리';
include_once('./admin.head.php');
echo '<span id="ct"></span>';
include_once('./admin.tail.php');
flush();

echo '<script>document.getElementById(\'ct\').innerHTML += \'<p>포인트 정리중...</p>\';</script>'."\n";
flush();

$max_count = 50;

// 테이블 락을 걸고
$sql = " LOCK TABLES {$g5['member_table']} WRITE, {$g5['point_table']} WRITE ";
sql_query($sql);

$sql = " select mb_id, count(po_point) as cnt
            from {$g5['point_table']}
            group by mb_id
            having cnt > {$max_count}+1
            order by cnt ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $count = 0;
    $total = 0;
    $sql2 = " select po_id, po_point
                  from {$g5['point_table']}
                  where mb_id = '{$row['mb_id']}'
                  order by po_id desc
                  limit {$max_count}, {$row['cnt']} ";
    $result2 = sql_query($sql2);
    for ($k=0; $row2=sql_fetch_array($result2); $k++)
    {
        $count++;
        $total += $row2['po_point'];

        sql_query(" delete from {$g5['point_table']} where po_id = '{$row2['po_id']}' ");
    }

    insert_point($row['mb_id'], $total, '포인트 {$count}건 정리', '@clear', $row['mb_id'], G5_TIME_YMD."-".uniqid(""));

    $str = $row['mb_id']."님 포인트 내역 ".number_format($count)."건 ".number_format($total)."점 정리<br>";
    echo '<script>document.getElementById(\'ct\').innerHTML += \''.$str.'\';</script>'."\n";
    flush();
}

// 테이블 락을 풀고
$sql = " UNLOCK TABLES ";
sql_query($sql);

echo '<script>document.getElementById(\'ct\').innerHTML += \'<p>총 '.$i.'건의 회원포인트 내역이 정리 되었습니다.</p>\';</script>'."\n";
?>
