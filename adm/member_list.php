<?
$sub_menu = "200100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$sql_common = " from {$g4['member_table']} ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point' :
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level' :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel' :
        case 'mb_hp' :
            $sql_search .= " ({$sfl} like '%{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} like '{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($is_admin != 'super')
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";

if (!$sst) {
    $sst = "mb_datetime";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

// 차단회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

$listall = "";
if (isset($sfl) || isset($stx)) // 검색일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g4['title'] = '회원관리';
include_once('./admin.head.php');

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 15;
?>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>회원검색</legend>
    <span>
        <?=$listall?>
        총회원수 <?=number_format($total_count)?>명 중,
        <a href="?sst=mb_intercept_date&amp;sod=desc&amp;sfl=<?=$sfl?>&amp;stx=<?=$stx?>">차단 <?=number_format($intercept_count)?></a>명,
        <a href="?sst=mb_leave_date&amp;sod=desc&amp;sfl=<?=$sfl?>&amp;stx=<?=$stx?>">탈퇴 <?=number_format($leave_count)?></a>명
    </span>
    <select name="sfl" title="검색대상">
        <option value="mb_id"<?=get_selected($_GET['sfl'], "mb_id");?>>회원아이디</option>
        <option value="mb_nick"<?=get_selected($_GET['sfl'], "mb_nick");?>>별명</option>
        <option value="mb_name"<?=get_selected($_GET['sfl'], "mb_name");?>>이름</option>
        <option value="mb_level"<?=get_selected($_GET['sfl'], "mb_level");?>>권한</option>
        <option value="mb_email"<?=get_selected($_GET['sfl'], "mb_email");?>>E-MAIL</option>
        <option value="mb_tel"<?=get_selected($_GET['sfl'], "mb_tel");?>>전화번호</option>
        <option value="mb_hp"<?=get_selected($_GET['sfl'], "mb_hp");?>>핸드폰번호</option>
        <option value="mb_point"<?=get_selected($_GET['sfl'], "mb_point");?>>포인트</option>
        <option value="mb_datetime"<?=get_selected($_GET['sfl'], "mb_datetime");?>>가입일시</option>
        <option value="mb_ip"<?=get_selected($_GET['sfl'], "mb_ip");?>>IP</option>
        <option value="mb_recommend"<?=get_selected($_GET['sfl'], "mb_recommend");?>>추천인</option>
    </select>
    <input type="text" name="stx" class="required frm_input" required value="<?=$stx?>" title="검색어(필수)">
    <input type="submit" class="btn_submit" value="검색">
</fieldset>
</form>

<section class="cbox">
    <h2>회원 목록</h2>
    <p>회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 별명은 삭제하지 않고 영구 보관합니다.</p>

    <? if ($is_admin == 'super') {?>
    <div id="btn_add">
        <a href="./member_form.php" id="member_add">회원추가</a>
    </div>
    <?}?>

    <form id="fmemberlist" name="fmemberlist" method="post" action="./member_list_update.php" onsubmit="return fmemberlist_submit(this);">
    <input type="hidden" name="sst" value="<?=$sst?>">
    <input type="hidden" name="sod" value="<?=$sod?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="page" value="<?=$page?>">

    <table class="tbl_mb_list">
    <thead>
    <tr>
        <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 회원 전체선택" onclick="check_all(this.form)"></th>
        <th scope="col"><?=subject_sort_link('mb_id')?>회원아이디</a></th>
        <th scope="col"><?=subject_sort_link('mb_nick')?>별명</a></th>
        <th scope="col"><?=subject_sort_link('mb_name')?>이름</a></th>
        <th scope="col"><?=subject_sort_link('mb_level', '', 'desc')?>권한</a></th>
        <th scope="col"><?=subject_sort_link('mb_point', '', 'desc')?> 포인트</a></th>
        <th scope="col"><?=subject_sort_link('mb_today_login', '', 'desc')?>최종접속</a></th>
        <th scope="col"><?=subject_sort_link('mb_mailling', '', 'desc')?>수신</a></th>
        <th scope="col"><?=subject_sort_link('mb_open', '', 'desc')?>공개</a></th>
        <th scope="col"><?=subject_sort_link('mb_email_certify', '', 'desc')?>인증</a></th>
        <th scope="col"><?=subject_sort_link('mb_intercept_date', '', 'desc')?>차단</a></th>
        <th scope="col">그룹</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 접근가능한 그룹수
        $sql2 = " select count(*) as cnt from {$g4['group_member_table']} where mb_id = '{$row['mb_id']}' ";
        $row2 = sql_fetch($sql2);
        $group = '';
        if ($row2['cnt'])
            $group = '<a href="./boardgroupmember_form.php?mb_id='.$row['mb_id'].'">'.$row2['cnt'].'</a>';

        if ($is_admin == 'group')
        {
            $s_mod = '';
            $s_del = '';
        }
        else
        {
            $s_mod = '<a href="./member_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$row['mb_id'].'">수정</a>';
            //$s_del = '<a href="javascript:post_delete(\'member_delete.php\', \''.$row['mb_id'].'\');">삭제</a>';
        }
        $s_grp = '<a href="./boardgroupmember_form.php?mb_id='.$row['mb_id'].'">그룹</a>';

        $leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date('Ymd', G4_SERVER_TIME);
        $intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date('Ymd', G4_SERVER_TIME);

        $mb_nick = get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);

        $mb_id = $row['mb_id'];
        $leave_msg = '';
        $intercept_msg = '';
        $intercept_title = '';
        if ($row['mb_leave_date']) {
            $mb_id = $mb_id;
            $leave_msg = '<br>탈퇴함';
        }
        else if ($row['mb_intercept_date']) {
            $mb_id = $mb_id;
            $intercept_msg = '<br>차단됨';
            $intercept_title = '차단해제';
        }
        if ($intercept_title == '')
            $intercept_title = '차단하기';
    ?>

    <tr>
        <td class="td_chk">
            <input type="hidden" id="mb_id_<?=$i?>" name="mb_id[<?=$i?>]" value="<?=$row['mb_id']?>">
            <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>" title="회원선택">
        </td>
        <td class="td_mbid">
            <?=$mb_id?>
            <span><?=$leave_msg?><?=$intercept_msg?></span>
        </td>
        <td class="td_name"><div><?=$mb_nick?></div></td>
        <td class="td_mbname"><?=$row['mb_name']?></td>
        <td><?=get_member_level_select("mb_level[$i]", 1, $member['mb_level'], $row['mb_level'])?></td>
        <td class="td_bignum"><a href="point_list.php?sfl=mb_id&amp;stx=<?=$row['mb_id']?>"><?=number_format($row['mb_point'])?></a></td>
        <td><?=substr($row['mb_today_login'],2,8)?></td>
        <td><?=$row['mb_mailling']?'<span class="txt_true">Yes</span>':'<span class="txt_false">No</span>';?></td>
        <td><?=$row['mb_open']?'<span class="txt_true">Yes</span>':'<span class="txt_false">No</span>';?></td>
        <td><?=preg_match('/[1-9]/', $row['mb_email_certify'])?'<span class="txt_true">Yes</span>':'<span class="txt_false">No</span>';?></td>
        <td class="td_chk">
            <? if(empty($row['mb_leave_date'])){?>
            <input type="checkbox" id="mb_intercept_date_<?=$i?>" name="mb_intercept_date[<?=$i?>]" <?=$row['mb_intercept_date']?'checked':'';?> value="<?=$intercept_date?>" title="<?=$intercept_title?>">
            <?}?>
        </td>
        <td class="td_chk"><?=$group?></td>
        <td><?=$s_mod?> <?=$s_grp?></td>
    </tr>

    <?
    }
    if ($i == 0)
        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </table>

    <div class="btn_list">
        <input type="submit" name="act_button" onclick="document.pressed=this.value" value="선택수정">
        <input type="submit" name="act_button" onclick="document.pressed=this.value" value="선택삭제">
        <? if ($is_admin == 'super') {?><a href="./member_form.php">회원추가</a><?}?>
    </div>

    </form>
</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page=');?>

<script>
function fmemberlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?
include_once ('./admin.tail.php');
?>
