<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G4_LIB_PATH.'/thumb.lib.php');
?>

<table id="sit_ps_tbl">
<thead>
<tr>
    <th>번호</th>
    <th>제목</th>
    <th>작성자</th>
    <th>작성일</th>
    <th>평점</th>
</tr>
</thead>
<?php
$sql_common = " from {$g4['shop_item_ps_table']} where it_id = '{$it['it_id']}' and is_confirm = '1' ";

// 테이블의 전체 레코드수만 얻음
$sql = " select COUNT(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$use_total_count = $row['cnt'];

$use_total_page  = ceil($use_total_count / $use_page_rows); // 전체 페이지 계산
if ($use_page == "") $use_page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$use_from_record = ($use_page - 1) * $use_page_rows; // 시작 레코드 구함

$sql = "select * $sql_common order by is_id desc limit $use_from_record, $use_page_rows ";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $num = $use_total_count - ($use_page - 1) * $use_page_rows - $i;

    $star = get_star($row['is_score']);

    $is_name = get_text($row['is_name']);
    $is_subject = conv_subject($row['is_subject'],50,"…");
    //$is_content = conv_content($row[is_content],0);
    $is_content = $row['is_content'];
    //$is_content = preg_replace_callback("#<img[^>]+>#iS", "g4_thumb", $is_content);

    $thumb = new g4_thumb(G4_DATA_PATH.'/itemuse', 500);
    $is_content = $thumb->run($is_content);

    $is_time = substr($row['is_time'], 2, 14);
?>

    <li class="sit_ps_li">
        <button type="button" class="sit_ps_li_title" onclick="javascript:qa_menu('sit_ps_con_<?php echo $i; ?>')"><?php echo $num; ?>. <?php echo $iq_subject; ?></button>
        <dl class="sit_ps_dl">
            <dt>작성자</dt>
            <dd><?php echo $iq_name; ?></dd>
            <dt>작성일</dt>
            <dd><?php echo $iq_time; ?></dd>
            <dt>상태</dt>
            <dd><?php echo $iq_stats; ?></dd>
        </dl>

        <div id="sit_ps_con_<?php echo $i; ?>" class="sit_ps_con">
            <p class="sit_ps_qaq">
                <strong>문의내용</strong><br>
                <?php echo $iq_question; // 상품 문의 내용 ?>
            </p>
            <p class="sit_ps_qaa">
                <strong>답변</strong><br>
                <?php echo $iq_answer; ?>
            </p>

            <textarea id="tmp_iq_id<?php echo $i; ?>"><?php echo $row['iq_id']; ?></textarea>
            <textarea id="tmp_iq_name<?php echo $i; ?>"><?php echo $row['iq_name']; ?></textarea>
            <textarea id="tmp_iq_subject<?php echo $i; ?>"><?php echo $row['iq_subject']; ?></textarea>
            <textarea id="tmp_iq_question<?php echo $i; ?>"><?php echo $row['iq_question']; ?></textarea>

            <?php if ($row['mb_id'] == $member['mb_id'] && $iq_answer == 0) { ?>
            <div class="sit_ps_cmd">
                <button onclick="javascript:itemqa_update(<?php echo $i; ?>);" class="btn01">수정</button>
                <button onclick="javascript:itemqa_delete(fitemqa_password<?php echo $i; ?>, <?php echo $i; ?>);" class="btn01">삭제</button>
            </div>
            <?php } ?>
        </div>
    </li>



<tr>
    <td><?php echo $num; ?><span class="sound_only">번</span></td>
    <td>
        <a href="javascript:;" onclick="use_menu('is<?php echo $i; ?>')"><?php echo $is_subject; ?></a>
        <div>
            <div>
                <?php echo $is_content; ?>
            </div>
            <textarea id="tmp_is_id<?php echo $i; ?>"><?php echo $row['is_id']; ?></textarea>
            <textarea id="tmp_is_name<?php echo $i; ?>"><?php echo $row['is_name']; ?></textarea>
            <textarea id="tmp_is_subject<?php echo $i; ?>"><?php echo $row['is_subject']; ?></textarea>
            <textarea id="tmp_is_content<?php echo $i; ?>"><?php echo $row['is_content']; ?></textarea>

            <?php if ($row[mb_id] == $member[mb_id]) { ?>
            <a href="javascript:itemusewin('is_id=<?php echo $row['is_id']; ?>&amp;w=u');">수정</a>
            <a href="javascript:itemuse_delete(fitemuse_password<?php echo $i; ?>, <?php echo $i; ?>);">삭제</a>
            <?php } ?>
            <div id="is<?php echo $i; ?>">
            <!-- 사용후기 삭제 패스워드 입력 폼 -->
                    <form name="fitemuse_password<?php echo $i; ?>" method="post" action="./itemuseupdate.php" autocomplete="off">
                    <input type="hidden" name="w" value="">
                    <input type="hidden" name="is_id" value="">
                    <input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
                    <label for="is_password_<?php echo $i; ?>">패스워드</label>
                    <input type="password" name="is_password" id="is_password_<?php echo $i; ?>" required>
                    <input type="submit" value="확인">
                    </form>
            </div>
        </div>
    </td>
    <td><?php echo $is_name; ?></td>
    <td><?php echo $is_time; ?></td>
    <td><img src="<?php echo G4_URL; ?>/img/shop/s_star<?php echo $star; ?>.png" alt="별<?php echo $star; ?>개"></td>
</tr>

<?
}

if (!$i)
{
    echo '<tr><td class="empty_class">등록된 사용후기가 없습니다.</td></tr>';
}
?>
</table>

<?php
if ($use_pages) {
    $use_pages = get_paging(10, $use_page, $use_total_page, "./item.php?it_id=$it_id&amp;$qstr&amp;use_page=", "#use");
}
?>

<a href="javascript:itemusewin('it_id=<?php echo $it_id; ?>');">사용후기 쓰기<span class="sound_only"> 새 창</span></a>

<script>
function itemusewin(query_string)
{
    window.open("./itemusewin.php?"+query_string, "itemusewin", "width=800,height=700");
}

function fitemuse_submit(f) 
{
    if (!check_kcaptcha(f.is_key)) { 
        return false; 
    } 

    f.action = "itemuseupdate.php"
    return true;
}

function itemuse_insert()
{
    /*
    if (!g4_is_member) {
        alert("로그인 하시기 바랍니다.");
        return;
    }
    */

    var f = document.fitemuse;
    var id = document.getElementById('itemuse');

    id.style.display = 'block';

    f.w.value = '';
    f.is_id.value = '';
    if (!g4_is_member)
    {
        f.is_name.value = '';
        f.is_name.readOnly = false;
        f.is_password.value = '';
    }
    f.is_subject.value = '';
    f.is_content.value = '';
}

function itemuse_update(idx)
{
    var f = document.fitemuse;
    var id = document.getElementById('itemuse');

    id.style.display = 'block';

    f.w.value = 'u';
    f.is_id.value = document.getElementById('tmp_is_id'+idx).value;
    if (!g4_is_member)
    {
        f.is_name.value = document.getElementById('tmp_is_name'+idx).value;
        f.is_name.readOnly = true;
    }
    f.is_subject.value = document.getElementById('tmp_is_subject'+idx).value;
    f.is_content.value = document.getElementById('tmp_is_content'+idx).value;
}

function itemuse_delete(f, idx)
{
    var id = document.getElementById('itemuse');

    f.w.value = 'd';
    f.is_id.value = document.getElementById('tmp_is_id'+idx).value;

    if (g4_is_member)
    {
        if (confirm("삭제하시겠습니까?"))
            f.submit();
    }
    else 
    {
        id.style.display = 'none';
        document.getElementById('itemuse_password'+idx).style.display = 'block';
    }
}
</script>