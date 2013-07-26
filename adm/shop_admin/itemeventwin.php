<?php
$sub_menu = '500300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$sql = " select ev_subject from {$g4['shop_event_table']} where ev_id = '$ev_id' ";
$ev = sql_fetch($sql);

$g4['title'] = $ev['ev_subject'].' 이벤트상품';
include_once(G4_PATH.'/head.sub.php');
?>

<div class="cbox">
    <h1><?php echo $g4['title']; ?></h1>
    <table>
    <thead>
    <tr>
        <th scope="col">상품명</th>
        <th scope="col">사용구분</th>
        <th scope="col">삭제</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = " select b.it_id, b.it_name, b.it_use from {$g4['shop_event_item_table']} a
               left join {$g4['shop_item_table']} b on (a.it_id=b.it_id)
              where a.ev_id = '$ev_id'
              order by b.it_id desc ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];
    ?>
    <tr>
        <td>
            <a href="<?php echo $href; ?>" target="_blank">
                <?php echo get_it_image($row['it_id'], 40, 40); ?>
                <?php echo cut_str(stripslashes($row['it_name']), 60, "&#133"); ?>
            </a>
        </td>
        <td class="td_smallmng"><?php echo ($row['it_use']?"사용":"미사용"); ?></td>
        <td class="td_smallmng"><a href="javascript:del('./itemeventwindel.php?ev_id=<?php echo $ev_id; ?>&amp;it_id=<?php echo $row['it_id']; ?>');">삭제</a></td>
    <tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="3" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

</div>

<div class="btn_win">
    <button type="button" onclick="javascript:window.close()">창 닫기</button>
</div>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>
