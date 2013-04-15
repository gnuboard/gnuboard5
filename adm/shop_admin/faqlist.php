<?
$sub_menu = '400710';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = 'FAQ 상세관리';
if ($fm_subject) $g4['title'] .= ' : '.$fm_subject;
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql = " select * from {$g4['shop_faq_master_table']} where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);

$sql_common = " from {$g4['shop_faq_table']} where fm_id = '$fm_id' ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$sql = "select * $sql_common order by fa_order , fa_id ";
$result = sql_query($sql);
?>

<section class="cbox">
    <h2><?=$fm_subject?> 목록</h2>

    <p>등록된 FAQ 상세내용 <?=$total_count ?>건</p>

    <ol>
        <li>FAQ는 무제한으로 등록할 수 있습니다</li>
        <li><strong>FAQ 상세내용 추가</strong>를 눌러 자주하는 질문과 답변을 입력합니다.</li>
    </ol>

    <div id="btn_add">
        <a href="./faqform.php?fm_id=<?=$fm['fm_id']?>">FAQ 상세내용 추가</a>
    </div>

    <table>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">제목</th>
        <th scope="col">순서</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $row1 = sql_fetch(" select COUNT(*) as cnt from {$g4['shop_faq_table']} where fm_id = '{$row['fm_id']}' ");
        $cnt = $row1[cnt];

        $s_mod = icon("수정", "");
        $s_del = icon("삭제", "");

        $num = $i + 1;
    ?>

        <tr>
            <td class="td_num"><?=$num?></td>
            <td><?=stripslashes($row['fa_subject'])?></td>
            <td class="td_num"><?=$row['fa_order']?></td>
            <td class="td_smallmng">
                <a href="./faqform.php?w=u&amp;fm_id=<?=$row['fm_id']?>&amp;fa_id=<?=$row['fa_id']?>">수정</a>
                <a href="javascript:del('./faqformupdate.php?w=d&amp;fm_id=<?=$row['fm_id']?>&amp;fa_id=<?=$row['fa_id']?>');">삭제</a>
            </td>
        </tr>

    <?
    }

    if ($i == 0) {
        echo '<tr><td colspan="4" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>

</section>

<div class="btn_confirm">
    <a href="./faqmasterlist.php">FAQ 관리</a>
</div>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
