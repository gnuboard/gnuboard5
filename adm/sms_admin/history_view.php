<?php
$sub_menu = "900400";
include_once("./_common.php");

$spage_size = 20;
$colspan = 10;

auth_check($auth[$sub_menu], "r");

$g5['title'] = "문자전송 상세내역";

if (!is_numeric($wr_no))
    alert('전송 고유 번호가 없습니다.');

if ($spage < 1) $spage = 1;

if ($sst && trim($ssv))
    $sql_search = " and $sst like '%$ssv%' ";
else
    $sql_search = "";

if ($wr_renum) {
    $sql_renum = " and wr_renum='$wr_renum' ";
    $re_text = " <span style='font-weight:normal; color:red;'>(재전송)</span>";
} else
    $sql_renum = " and wr_renum='0'";

$total_res = sql_fetch("select count(*) as cnt from {$g5['sms5_history_table']} where wr_no='$wr_no' $sql_search $sql_renum");
$total_count = $total_res['cnt'];

$total_spage = (int)($total_count/$spage_size) + ($total_count%$spage_size==0 ? 0 : 1);
$spage_start = $spage_size * ( $spage - 1 );

$vnum = $total_count - (($spage-1) * $spage_size);

$write = sql_fetch("select * from {$g5['sms5_write_table']} where wr_no='$wr_no' $sql_renum");
if ($write['wr_booking'] == '0000-00-00 00:00:00')
    $write['wr_booking'] = '즉시전송';

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<script>
function re_send()
{
    <?php if (!$write['wr_failure']) { ?>
    alert('실패한 전송이 없습니다.');
    <?php } else { ?>
    if (!confirm('전송에 실패한 SMS 를 재전송 하시겠습니까?'))
        return;

    act = window.open('sms_ing.php', 'act', 'width=300, height=200');
    act.focus();

    location.href = './history_send.php?w=f&page=<?php echo $page?>&st=<?php echo  $st?>&sv=<?php echo $sv?>&wr_no=<?php echo $wr_no?>&wr_renum=<?php echo $wr_renum?>';
    <?php } ?>
}
function all_send()
{
    if (!confirm('전체 SMS 를 재전송 하시겠습니까?\n\n예약전송일 경우 예약일시는 다시 설정하셔야 합니다.'))
        return;
    location.href = './sms_write.php?wr_no=<?php echo $wr_no?>';
}
</script>

<form name="search_form" method="get" action="<?php echo $_SERVER['SCRIPT_NAME']?>" class="local_sch01 local_sch">
<input type="hidden" name="wr_no" value="<?php echo $wr_no?>">
<input type="hidden" name="wr_renum" value="<?php echo $wr_renum?>">
<input type="hidden" name="page" value="<?php echo $page?>">
<input type="hidden" name="st" value="<?php echo $st?>">
<input type="hidden" name="sv" value="<?php echo $sv?>">
<label for="sst" class="sound_only">검색대상</label>
<select name="sst" id="sst">
    <option value="hs_name" <?php echo get_selected('hs_name', $sst); ?>>이름</option>
    <option value="hs_hp" <?php echo get_selected('hs_hp', $sst); ?>>휴대폰번호</option>
</select>
<label for="ssv" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="ssv" value="<?php echo $ssv?>" id="ssv" class="frm_input">
<input type="submit" value="검색" class="btn_submit">
</form>

<div id="sms5_sent">
    <div class="local_ov01 local_ov">
        <span class="ov_listall">전송건수 <?php echo number_format($write['wr_total'])?> 건</span>
        <span class="ov_listall">성공건수 <span class="txt_succeed"><?php echo number_format($write['wr_success'])?> 건</span></span>
        <span class="ov_listall">실패건수 <span class="txt_fail"><?php echo number_format($write['wr_failure'])?> 건</span></span>
        <span class="ov_listall">전송일시 <?php echo $write['wr_datetime']?></span>
        <span class="ov_listall">예약일시 <?php echo $write['wr_booking']?></span>
        <span class="ov_listall">회신번호 <?php echo $write['wr_reply']?></span>
    </div>

    <h2>전송내용</h2>

    <div id="con_sms" class="sms5_box">
        <span class="box_ico"></span>
        <textarea class="box_txt" readonly><?php echo $write['wr_message'];?></textarea>
    </div>

    <?php if ($write['wr_re_total'] && !$wr_renum) { ?>
    <h2>전송실패 문자 재전송 내역</h2>
    <div  class="sms_table">
    <table>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <!-- <th scope="col"><input type=checkbox></th> -->
        <!-- <th scope="col">메세지</th> -->
        <!-- <th scope="col">회신번호</th> -->
        <th scope="col">전송일시</th>
        <th scope="col">총건수</th>
        <th scope="col">성공</th>
        <th scope="col">실패</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $res = sql_fetch("select count(*) as cnt from {$g5['sms5_write_table']} where wr_no='$wr_no' and wr_renum>0");
    $re_vnum = $res['cnt'];

    $qry = sql_query("select * from {$g5['sms5_write_table']} where wr_no='$wr_no' and wr_renum>0 order by wr_renum desc");
    while($res = sql_fetch_array($qry)) {
    ?>
    <tr>
        <td><?php echo $re_vnum--?></td>
        <!-- <td><input type=checkbox></td> -->
        <!-- <td><?php echo $res[wr_message]; ?></span></td>-->
        <!-- <td><?php echo $res[wr_reply]; ?></td>-->
        <td><?php echo $res['wr_datetime']?></td>
        <td><?php echo number_format($res['wr_total'])?></td>
        <td><?php echo number_format($res['wr_success'])?></td>
        <td><?php echo number_format($res['wr_failure'])?></td>
        <td>
            <a href="./history_view.php?page=<?php echo $page?>&amp;st=<?php echo $st?>&amp;sv=<?php echo $sv?>&amp;wr_no=<?php echo $res['wr_no']?>&amp;wr_renum=<?php echo $res['wr_renum']?>">수정</a>
            <!-- <a href="./history_del.php?page=<?php echo $page?>&amp;st=<?php echo $st?>&amp;sv=<?php echo $sv?>&amp;wr_no=<?php echo $res[wr_no]?>&amp;wr_renum=<?php echo $res[wr_renum]?>">삭제</a> -->
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
    <?php } ?>
    </div>

    <?php
    if( $write['wr_memo'] ){
        $tmp_wr_memo = @unserialize($write['wr_memo']);
        if( count($tmp_wr_memo) && is_array($tmp_wr_memo) ){
            if(function_exists('array_fill_keys')){
                $tmp_wr_hp = array_replace($tmp_wr_memo['hp'],array_fill_keys(array_keys($tmp_wr_memo['hp'], null),''));
            } else {
                $tmp_wr_hp = $tmp_wr_memo['hp'];
            }
            $arr_wr_memo = @array_count_values( $tmp_wr_hp );
    ?>
    <h2>중복번호 <?php echo $tmp_wr_memo['total'];?>건</h2>
    <ul id="sent_overlap">
        <?php
        foreach( $arr_wr_memo as $key=>$v){
        if( empty($v) || $key == '' ) continue;
        ?>
        <li><b><?php echo $key;?></b> 중복 <?php echo $v;?>건</li>
        <?php } ?>
    </ul>
    <?php
        }
    }
    ?>

    <h2>문자전송 목록 <?php echo $re_text?></h2>

    <div class="btn_add01 btn_add">
        <a href="javascript:all_send()">전체 재전송</a>
        <a href="javascript:re_send()">실패내역 재전송</a>
        <?php if (!$wr_renum) {?>
        <a href="./history_list.php?page=<?php echo $page?>&amp;st=<?php echo $st?>&amp;sv=<?php echo $sv?>">목록</a>
        <?php } else { ?>
        <a href="./history_view.php?page=<?php echo $page?>&amp;st=<?php echo $st?>&amp;sv=<?php echo $sv?>&amp;wr_no=<?php echo $wr_no?>">뒤로가기</a>
        <?php } ?>
    </div>

    <div class="tbl_head01 tbl_wrap">
        <table>
        <thead>
        <tr>
            <th scope="col">번호</th>
            <th scope="col">그룹</th>
            <th scope="col">이름</th>
            <th scope="col">회원ID</th>
            <th scope="col">휴대폰번호</th>
            <th scope="col">전송일시</th>
            <th scope="col">결과</th>
            <th scope="col">비고</th>
            <th scope="col">내역</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$total_count) { ?>
        <tr>
            <td colspan="<?php echo $colspan?>" class="empty_table">
                데이터가 없습니다.
            </td>
        </tr>
        <?php
        }
        $qry = sql_query("select * from {$g5['sms5_history_table']} where wr_no='$wr_no' $sql_search $sql_renum order by hs_no desc limit $spage_start, $spage_size");
        while($res = sql_fetch_array($qry)) {
            $bg = 'bg'.($line++%2);

            $group = sql_fetch("select * from {$g5['sms5_book_group_table']} where bg_no='{$res['bg_no']}'");
            if ($group)
                $bg_name = $group['bg_name'];
            else
                $bg_name = '없음';

            if ($res['mb_id'])
                $mb_id = get_sideview($res['mb_id'], $res['mb_id']);
            else
                $mb_id = '비회원';

            $res['hs_log'] = str_replace($config['cf_icode_pw'], '**********', $res['hs_log']);
        ?>
        <tr class="<?php echo $bg; ?>">
            <td class="td_numsmall"><?php echo number_format($vnum--)?></td>
            <td class="td_name"><?php echo $bg_name?></td>
            <td class="td_mbname"><?php echo $res['hs_name']?></a></td>
            <td class="td_mbid"><?php echo $mb_id?></td>
            <td class="td_numbig"><?php echo $res['hs_hp']?></td>
            <td class="td_datetime"><?php echo $res['hs_datetime']?></td>
            <td class="td_boolean"><?php echo $res['hs_flag']?'성공':'실패'?></td>
            <td>
                <u>결과코드</u> : <?php echo $res['hs_code']?><br>
                <u>로그</u> : <?php echo $res['hs_log']?><br>
                <u>메모</u> : <?php echo $res['hs_memo']?>
            </td>
            <td class="td_mngsmall">
                <?php if ($res['bk_no']) { ?>
                <a href="./history_num.php?wr_id=<?php echo $res['wr_no']?>&amp;st=bk_no&amp;sv=<?php echo $res['bk_no']?>">내역</a>
                <?php } else { ?>
                <a href="./history_num.php?wr_id=<?php echo $res['wr_no']?>&amp;st=hs_hp&amp;sv=<?php echo $res['hs_hp']?>">내역</a>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</div>

<?php echo sms5_sub_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $spage, $total_spage, $_SERVER['SCRIPT_NAME']."?wr_no=$wr_no&amp;wr_renum=$wr_renum&amp;page=$page&amp;st=$st&amp;sv=$sv&amp;sst=$sst&amp;ssv=$ssv", "", "spage"); ?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>