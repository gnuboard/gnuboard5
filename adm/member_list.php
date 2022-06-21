<?php
$sub_menu = "200100";
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$sql_common = " from {$g5['member_table']} ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel':
        case 'mb_hp':
            $sql_search .= " ({$sfl} like '%{$stx}') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

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
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

// 차단회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = '회원관리';
require_once './admin.head.php';

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 16;
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총회원수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>명 </span></span>
    <a href="?sst=mb_intercept_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="차단된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">차단 </span><span class="ov_num"><?php echo number_format($intercept_count) ?>명</span></a>
    <a href="?sst=mb_leave_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="탈퇴된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">탈퇴 </span><span class="ov_num"><?php echo number_format($leave_count) ?>명</span></a>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="mb_id" <?php echo get_selected($sfl, "mb_id"); ?>>회원아이디</option>
        <option value="mb_nick" <?php echo get_selected($sfl, "mb_nick"); ?>>닉네임</option>
        <option value="mb_name" <?php echo get_selected($sfl, "mb_name"); ?>>이름</option>
        <option value="mb_level" <?php echo get_selected($sfl, "mb_level"); ?>>권한</option>
        <option value="mb_email" <?php echo get_selected($sfl, "mb_email"); ?>>E-MAIL</option>
        <option value="mb_tel" <?php echo get_selected($sfl, "mb_tel"); ?>>전화번호</option>
        <option value="mb_hp" <?php echo get_selected($sfl, "mb_hp"); ?>>휴대폰번호</option>
        <option value="mb_point" <?php echo get_selected($sfl, "mb_point"); ?>>포인트</option>
        <option value="mb_datetime" <?php echo get_selected($sfl, "mb_datetime"); ?>>가입일시</option>
        <option value="mb_ip" <?php echo get_selected($sfl, "mb_ip"); ?>>IP</option>
        <option value="mb_recommend" <?php echo get_selected($sfl, "mb_recommend"); ?>>추천인</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
    <input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc">
    <p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p>
</div>


<form name="fmemberlist" id="fmemberlist" action="./member_list_update.php" onsubmit="return fmemberlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col" id="mb_list_chk" rowspan="2">
                        <label for="chkall" class="sound_only">회원 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col" id="mb_list_id" colspan="2"><?php echo subject_sort_link('mb_id') ?>아이디</a></th>
                    <th scope="col" rowspan="2" id="mb_list_cert"><?php echo subject_sort_link('mb_certify', '', 'desc') ?>본인확인</a></th>
                    <th scope="col" id="mb_list_mailc"><?php echo subject_sort_link('mb_email_certify', '', 'desc') ?>메일인증</a></th>
                    <th scope="col" id="mb_list_open"><?php echo subject_sort_link('mb_open', '', 'desc') ?>정보공개</a></th>
                    <th scope="col" id="mb_list_mailr"><?php echo subject_sort_link('mb_mailling', '', 'desc') ?>메일수신</a></th>
                    <th scope="col" id="mb_list_auth">상태</th>
                    <th scope="col" id="mb_list_mobile">휴대폰</th>
                    <th scope="col" id="mb_list_lastcall"><?php echo subject_sort_link('mb_today_login', '', 'desc') ?>최종접속</a></th>
                    <th scope="col" id="mb_list_grp">접근그룹</th>
                    <th scope="col" rowspan="2" id="mb_list_mng">관리</th>
                </tr>
                <tr>
                    <th scope="col" id="mb_list_name"><?php echo subject_sort_link('mb_name') ?>이름</a></th>
                    <th scope="col" id="mb_list_nick"><?php echo subject_sort_link('mb_nick') ?>닉네임</a></th>
                    <th scope="col" id="mb_list_sms"><?php echo subject_sort_link('mb_sms', '', 'desc') ?>SMS수신</a></th>
                    <th scope="col" id="mb_list_adultc"><?php echo subject_sort_link('mb_adult', '', 'desc') ?>성인인증</a></th>
                    <th scope="col" id="mb_list_auth"><?php echo subject_sort_link('mb_intercept_date', '', 'desc') ?>접근차단</a></th>
                    <th scope="col" id="mb_list_deny"><?php echo subject_sort_link('mb_level', '', 'desc') ?>권한</a></th>
                    <th scope="col" id="mb_list_tel">전화번호</th>
                    <th scope="col" id="mb_list_join"><?php echo subject_sort_link('mb_datetime', '', 'desc') ?>가입일</a></th>
                    <th scope="col" id="mb_list_point"><?php echo subject_sort_link('mb_point', '', 'desc') ?> 포인트</a></th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    // 접근가능한 그룹수
                    $sql2 = " select count(*) as cnt from {$g5['group_member_table']} where mb_id = '{$row['mb_id']}' ";
                    $row2 = sql_fetch($sql2);
                    $group = '';
                    if ($row2['cnt']) {
                        $group = '<a href="./boardgroupmember_form.php?mb_id=' . $row['mb_id'] . '">' . $row2['cnt'] . '</a>';
                    }

                    if ($is_admin == 'group') {
                        $s_mod = '';
                    } else {
                        $s_mod = '<a href="./member_form.php?' . $qstr . '&amp;w=u&amp;mb_id=' . $row['mb_id'] . '" class="btn btn_03">수정</a>';
                    }
                    $s_grp = '<a href="./boardgroupmember_form.php?mb_id=' . $row['mb_id'] . '" class="btn btn_02">그룹</a>';

                    $leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date('Ymd', G5_SERVER_TIME);
                    $intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date('Ymd', G5_SERVER_TIME);

                    $mb_nick = get_sideview($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

                    $mb_id = $row['mb_id'];
                    $leave_msg = '';
                    $intercept_msg = '';
                    $intercept_title = '';
                    if ($row['mb_leave_date']) {
                        $mb_id = $mb_id;
                        $leave_msg = '<span class="mb_leave_msg">탈퇴함</span>';
                    } elseif ($row['mb_intercept_date']) {
                        $mb_id = $mb_id;
                        $intercept_msg = '<span class="mb_intercept_msg">차단됨</span>';
                        $intercept_title = '차단해제';
                    }
                    if ($intercept_title == '') {
                        $intercept_title = '차단하기';
                    }

                    $address = $row['mb_zip1'] ? print_address($row['mb_addr1'], $row['mb_addr2'], $row['mb_addr3'], $row['mb_addr_jibeon']) : '';

                    $bg = 'bg' . ($i % 2);

                    switch ($row['mb_certify']) {
                        case 'hp':
                            $mb_certify_case = '휴대폰';
                            $mb_certify_val = 'hp';
                            break;
                        case 'ipin':
                            $mb_certify_case = '아이핀';
                            $mb_certify_val = '';
                            break;
                        case 'simple':
                            $mb_certify_case = '간편인증';
                            $mb_certify_val = '';
                            break;
                        case 'admin':
                            $mb_certify_case = '관리자';
                            $mb_certify_val = 'admin';
                            break;
                        default:
                            $mb_certify_case = '&nbsp;';
                            $mb_certify_val = 'admin';
                            break;
                    }
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <td headers="mb_list_chk" class="td_chk" rowspan="2">
                            <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_<?php echo $i ?>">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
                            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                        </td>
                        <td headers="mb_list_id" colspan="2" class="td_name sv_use">
                            <?php echo $mb_id ?>
                            <?php
                            //소셜계정이 있다면
                            if (function_exists('social_login_link_account')) {
                                if ($my_social_accounts = social_login_link_account($row['mb_id'], false, 'get_data')) {
                                    echo '<div class="member_social_provider sns-wrap-over sns-wrap-32">';
                                    foreach ((array) $my_social_accounts as $account) {     //반복문
                                        if (empty($account) || empty($account['provider'])) {
                                            continue;
                                        }

                                        $provider = strtolower($account['provider']);
                                        $provider_name = social_get_provider_service_name($provider);

                                        echo '<span class="sns-icon sns-' . $provider . '" title="' . $provider_name . '">';
                                        echo '<span class="ico"></span>';
                                        echo '<span class="txt">' . $provider_name . '</span>';
                                        echo '</span>';
                                    }
                                    echo '</div>';
                                }
                            }
                            ?>
                        </td>
                        <td headers="mb_list_cert" rowspan="2" class="td_mbcert">
                            <input type="radio" name="mb_certify[<?php echo $i; ?>]" value="simple" id="mb_certify_sa_<?php echo $i; ?>" <?php echo $row['mb_certify'] == 'simple' ? 'checked' : ''; ?>>
                            <label for="mb_certify_sa_<?php echo $i; ?>">간편인증</label><br>
                            <input type="radio" name="mb_certify[<?php echo $i; ?>]" value="hp" id="mb_certify_hp_<?php echo $i; ?>" <?php echo $row['mb_certify'] == 'hp' ? 'checked' : ''; ?>>
                            <label for="mb_certify_hp_<?php echo $i; ?>">휴대폰</label><br>
                            <input type="radio" name="mb_certify[<?php echo $i; ?>]" value="ipin" id="mb_certify_ipin_<?php echo $i; ?>" <?php echo $row['mb_certify'] == 'ipin' ? 'checked' : ''; ?>>
                            <label for="mb_certify_ipin_<?php echo $i; ?>">아이핀</label>
                        </td>
                        <td headers="mb_list_mailc"><?php echo preg_match('/[1-9]/', $row['mb_email_certify']) ? '<span class="txt_true">Yes</span>' : '<span class="txt_false">No</span>'; ?></td>
                        <td headers="mb_list_open">
                            <label for="mb_open_<?php echo $i; ?>" class="sound_only">정보공개</label>
                            <input type="checkbox" name="mb_open[<?php echo $i; ?>]" <?php echo $row['mb_open'] ? 'checked' : ''; ?> value="1" id="mb_open_<?php echo $i; ?>">
                        </td>
                        <td headers="mb_list_mailr">
                            <label for="mb_mailling_<?php echo $i; ?>" class="sound_only">메일수신</label>
                            <input type="checkbox" name="mb_mailling[<?php echo $i; ?>]" <?php echo $row['mb_mailling'] ? 'checked' : ''; ?> value="1" id="mb_mailling_<?php echo $i; ?>">
                        </td>
                        <td headers="mb_list_auth" class="td_mbstat">
                            <?php
                            if ($leave_msg || $intercept_msg) {
                                echo $leave_msg . ' ' . $intercept_msg;
                            } else {
                                echo "정상";
                            }
                            ?>
                        </td>
                        <td headers="mb_list_mobile" class="td_tel"><?php echo get_text($row['mb_hp']); ?></td>
                        <td headers="mb_list_lastcall" class="td_date"><?php echo substr($row['mb_today_login'], 2, 8); ?></td>
                        <td headers="mb_list_grp" class="td_numsmall"><?php echo $group ?></td>
                        <td headers="mb_list_mng" rowspan="2" class="td_mng td_mng_s"><?php echo $s_mod ?><?php echo $s_grp ?></td>
                    </tr>
                    <tr class="<?php echo $bg; ?>">
                        <td headers="mb_list_name" class="td_mbname"><?php echo get_text($row['mb_name']); ?></td>
                        <td headers="mb_list_nick" class="td_name sv_use">
                            <div><?php echo $mb_nick ?></div>
                        </td>

                        <td headers="mb_list_sms">
                            <label for="mb_sms_<?php echo $i; ?>" class="sound_only">SMS수신</label>
                            <input type="checkbox" name="mb_sms[<?php echo $i; ?>]" <?php echo $row['mb_sms'] ? 'checked' : ''; ?> value="1" id="mb_sms_<?php echo $i; ?>">
                        </td>
                        <td headers="mb_list_adultc">
                            <label for="mb_adult_<?php echo $i; ?>" class="sound_only">성인인증</label>
                            <input type="checkbox" name="mb_adult[<?php echo $i; ?>]" <?php echo $row['mb_adult'] ? 'checked' : ''; ?> value="1" id="mb_adult_<?php echo $i; ?>">
                        </td>
                        <td headers="mb_list_deny">
                            <?php if (empty($row['mb_leave_date'])) { ?>
                                <input type="checkbox" name="mb_intercept_date[<?php echo $i; ?>]" <?php echo $row['mb_intercept_date'] ? 'checked' : ''; ?> value="<?php echo $intercept_date ?>" id="mb_intercept_date_<?php echo $i ?>" title="<?php echo $intercept_title ?>">
                                <label for="mb_intercept_date_<?php echo $i; ?>" class="sound_only">접근차단</label>
                            <?php } ?>
                        </td>
                        <td headers="mb_list_auth" class="td_mbstat">
                            <?php echo get_member_level_select("mb_level[$i]", 1, $member['mb_level'], $row['mb_level']) ?>
                        </td>
                        <td headers="mb_list_tel" class="td_tel"><?php echo get_text($row['mb_tel']); ?></td>
                        <td headers="mb_list_join" class="td_date"><?php echo substr($row['mb_datetime'], 2, 8); ?></td>
                        <td headers="mb_list_point" class="td_num"><a href="point_list.php?sfl=mb_id&amp;stx=<?php echo $row['mb_id'] ?>"><?php echo number_format($row['mb_point']) ?></a></td>

                    </tr>

                <?php
                }
                if ($i == 0) {
                    echo "<tr><td colspan=\"" . $colspan . "\" class=\"empty_table\">자료가 없습니다.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
        <?php if ($is_admin == 'super') { ?>
            <a href="./member_form.php" id="member_add" class="btn btn_01">회원추가</a>
        <?php } ?>

    </div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<script>
    function fmemberlist_submit(f) {
        if (!is_checked("chk[]")) {
            alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
            return false;
        }

        if (document.pressed == "선택삭제") {
            if (!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
                return false;
            }
        }

        return true;
    }
</script>

<?php
require_once './admin.tail.php';
