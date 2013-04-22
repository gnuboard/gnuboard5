<?
$sub_menu = '400640';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '이벤트일괄처리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$where = " where ";
$sql_search = "";
if ($search != "") {
    if ($sel_field != "") {
        $sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
    }
}

if ($sel_ca_id != "") {
    $sql_search .= " $where ca_id like '$sel_ca_id%' ";
}

if ($sel_field == "")  {
    $sel_field = "it_name";
}

$sql_common = " from {$g4['shop_item_table']} a
                left join {$g4['shop_event_item_table']} b on (a.it_id=b.it_id and b.ev_id='$ev_id') ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sort1) {
    $sort1 = "b.ev_id";
}

if (!$sort2) {
    $sort2 = "desc";
}

$sql  = " select a.*, b.ev_id
          $sql_common
          order by $sort1 $sort2
          limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search;
$qstr1 = 'ev_id='.$ev_id.'&amp;sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search;
$qstr  = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

$listall = '';
if ($stx) // 검색 결과일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

// 이벤트 아이디와 제목을 분리 - 지운아빠 2013-04-15
if (isset($ev_set)) {
    $ev_exp = explode('`',$ev_set);
    $ev_id = $ev_exp[0];
    $ev_title = $ev_exp[1];
}
?>

<fieldset>
    <legend>이벤트 선택</legend>

    <form name="flist" autocomplete="off">
    <input type="hidden" name="page" value="<?=$page?>">
    전체 이벤트 <?=$total_count ?>건
    <label for="ev_set" class="sound_only">이벤트</label>
    <select name="ev_set" id="ev_set" action="<?=$_SERVER['PHP_SELF']?>">
        <?
        // 이벤트 옵션처리
        $event_option = "<option value=''>이벤트를 선택하세요</option>";
        $sql1 = " select ev_id, ev_subject from {$g4['shop_event_table']} order by ev_id desc ";
        $result1 = sql_query($sql1);
        while ($row1=mysql_fetch_array($result1))
            $event_option .= '<option value="'.$row1['ev_id'].'`'.$row1['ev_subject'].'" '.get_selected($ev_set, $row1['ev_id'].'`'.$row1['ev_subject']).' >'.conv_subject($row1['ev_subject'], 20,"…").'</option>';
        echo $event_option;
        ?>
    </select>
    <input type="submit" value="이동" class="btn_submit">

    </form>
</fieldset>

<fieldset>
    <legend>이벤트 검색</legend>

    <form name="flist" autocomplete="off">
    <input type="hidden" name="page" value="<?=$page?>">
    <?=$listall?>

    <label for="sel_ca_id" class="sound_only">분류선택</label>
    <? // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-15 ?>
    <select name="sel_ca_id" id="sel_ca_id">
        <option value=''>전체분류</option>
        <?
        $sql1 = " select ca_id, ca_name from {$g4['shop_category_table']} order by ca_id ";
        $result1 = sql_query($sql1);
        for ($i=0; $row1=mysql_fetch_array($result1); $i++)
        {
            $len = strlen($row1['ca_id']) / 2 - 1;
            $nbsp = "";
            for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
            echo '<option value="'.$row1['ca_id'].'" '.get_selected($sel_ca_id, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
        }
        ?>
    </select>
    <? // ##### // 웹 접근성 취약 지점 끝 ?>

    <label for="sel_field" class="sound_only">검색대상</label>
    <select name="sel_field" id="sel_field">
        <option value="it_name" <?=get_selected($sel_field, 'it_name')?>>상품명</option>
        <option value="a.it_id" <?=get_selected($sel_field, 'a.it_id')?>>상품코드</option>
    </select>

    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?=$search?>" id="search" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">

    </form>

</fieldset>

<section class="cbox">
    <h2>상품 목록</h2>
    <p>상품을 이벤트별로 일괄 처리합니다. 현재 선택된 이벤트는 <?=$ev_title?>입니다.</p>

    <form name="fitemeventlistupdate" method="post" action="./itemeventlistupdate.php" onsubmit="return fitemeventlistupdatecheck(this)">
    <input type="hidden" name="ev_id" value="<?=$ev_id?>">
    <input type="hidden" name="ev_set" value="<?=$ev_set?>">
    <input type="hidden" name="sel_ca_id" value="<?=$sel_ca_id?>">
    <input type="hidden" name="sel_field" value="<?=$sel_field?>">
    <input type="hidden" name="search" value="<?=$search?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <input type="hidden" name="sort1" value="<?=$sort1?>">
    <input type="hidden" name="sort2" value="<?=$sort2?>">

    <table>
    <thead>
    <tr>
        <th scope="col">이벤트사용</th>
        <th scope="col"><a href="<?=title_sort("a.it_id") . '&amp;'.$qstr1.'&amp;ev_id='.$ev_id; ?>">상품코드</a></th>
        <th scope="col"><a href="<?=title_sort("it_name") . '&&amp;'.$qstr1.'&amp;ev_id='.$ev_id; ?>">상품명</a></th>
    </tr>
    </thead>
    <tbody>
    <? for ($i=0; $row=mysql_fetch_array($result); $i++) {
        $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];

        $sql = " select ev_id from {$g4['shop_event_item_table']}
                  where it_id = '{$row['it_id']}'
                    and ev_id = '$ev_id' ";
        $ev = sql_fetch($sql);

    ?>

    <tr>
        <td class="td_mng">
            <input type="hidden" name="it_id[<?=$i?>]" value="<?=$row['it_id']?>">
            <input type="checkbox" name="ev_chk[<?=$i?>]" value="1" <?=($row['ev_id'] ? "checked" : "")?>>
        </td>
        <td class="td_bignum"><a href="<?=$href?>"><?=$row['it_id']?></a></td>
        <td><a href="<?=$href?>"><?=get_it_image($row['it_id'].'_s', 50, 50)?> <?=cut_str(stripslashes($row['it_name']), 60, "&#133")?></a></td>
    </tr>

    <?
    }

    if ($i == 0)
        echo '<tr><td colspan="4" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <p class="btn_confirm_msg">
        <? if ($ev_title) { ?>
         현재 선택된 이벤트는 <strong><?=$ev_title?></strong>입니다.<br>
         선택된 이벤트의 상품 수정 내용을 반영하시려면 일괄수정 버튼을 누르십시오.
        <? } else { ?>
        이벤트를 선택하지 않으셨습니다. 수정 내용을 반영하기 전에 이벤트를 선택해주십시오.<br>
        <a href="#ev_set">이벤트 선택</a>
        <? } ?>
    </p>
    <div class="btn_confirm">
        <input type="submit" value="일괄수정" class="btn_submit" accesskey="s">
    </div>
    </form>

</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");?>

<script>
function fitemeventlistupdatecheck(f)
{
    if (!f.ev_id.value)
    {
        alert('이벤트를 선택하세요');
        document.flist.ev_id.focus();
        return false;
    }

    return true;
}
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>