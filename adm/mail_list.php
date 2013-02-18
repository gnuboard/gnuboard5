<?
$sub_menu = '200300';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$sql_common = " from {$g4['mail_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select COUNT(*) as cnt {$sql_common} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$page = 1;

$sql = " select * {$sql_common} order by ma_id desc ";
$result = sql_query($sql);

$g4['title'] = '회원메일발송';
include_once('./admin.head.php');

$colspan = 6;
?>

<section class="cbox">
    <h2>등록된 메일내용 목록</h2>
    <p>
        <strong>테스트</strong>는 등록된 최고관리자의 이메일로 테스트 메일을 발송합니다.<br>
        현재 등록된 메일은 총 <?=$total_count ?>건입니다.
    </p>

    <div id="btn_add">
        <a href="./mail_form.php" id="mail_add">메일내용추가</a>
    </div>

    <form id="fmaillist" name="fmaillist" method="post" action="./mail_delete.php">
    <table>
    <thead>
    <tr>
        <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 목록 전체선택" onclick="check_all(this.form)"></th>
        <th scope="col">번호</th>
        <th scope="col">제목</th>
        <th scope="col">작성일시</th>
        <th scope="col">테스트</th>
        <th scope="col">보내기</th>
        <th scope="col">미리보기</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=mysql_fetch_array($result); $i++) {
        //$s_del = '<a href="javascript:post_delete(\'mail_update.php\', '.$row['ma_id'].');">삭제</a>';
        $s_vie = '<a href="./mail_preview.php?ma_id='.$row['ma_id'].'" target="_blank">미리보기</a>';

        $num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
    ?>

    <tr>
        <td class="td_chk">
            <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$row['ma_id']?>" title="메일선택">
        </td>
        <td class="td_num"><?=$num?></td>
        <td><a href="./mail_form.php?w=u&amp;ma_id=<?=$row['ma_id']?>"><?=$row['ma_subject']?></a></td>
        <td class="td_time"><?=$row['ma_time']?></td>
        <td class="td_test"><a href="./mail_test.php?ma_id=<?=$row['ma_id']?>">테스트</a></td>
        <td class="td_send"><a href="./mail_select_form.php?ma_id=<?=$row['ma_id']?>">보내기</a></td>
        <td class="td_mng"><?=$s_vie?></td>
    </tr>

    <?
    }
    if (!$i)
        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <button>선택삭제</button>
    </div>
    </form>
</section>

<script>
$(function() {
    $('#fmaillist').submit(function() {
        if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
            if (!is_checked("chk[]")) {
                alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
                return false;
            }

            return true;
        } else {
            return false;
        }
    });
});
</script>

<?
include_once ('./admin.tail.php');
?>