<?
include_once("./_common.php");

define("_ORDERINQUIRY_", true);

$od_pwd = sql_password($od_pwd);

// 회원인 경우
if ($is_member)
{
    $sql_common = " from $g4[shop_order_table] where mb_id = '$member[mb_id]' ";
}
else if ($od_id && $od_pwd) // 비회원인 경우 주문서번호와 비밀번호가 넘어왔다면
{
    $sql_common = " from $g4[shop_order_table] where od_id = '$od_id' and od_pwd = '$od_pwd' ";
}
else // 그렇지 않다면 로그인으로 가기
{
    goto_url("$g4[bbs_path]/login.php?url=".urlencode("$g4[shop_path]/orderinquiry.php"));
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

// 비회원 주문확인시 비회원의 모든 주문이 다 출력되는 오류 수정
// 조건에 맞는 주문서가 없다면
if ($total_count == 0)
{
    if ($is_member) // 회원일 경우는 메인으로 이동
        alert("주문이 존재하지 않습니다.", $g4[path]);
    else // 비회원일 경우는 이전 페이지로 이동
        alert("주문이 존재하지 않습니다.");
}

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


// 비회원 주문확인의 경우 바로 주문서 상세조회로 이동
if (!$is_member)
{
    $sql = " select od_id, on_uid from $g4[shop_order_table] where od_id = '$od_id' and od_pwd = '$od_pwd' ";
    $row = sql_fetch($sql);
    if ($row[od_id]) {
        set_session("ss_on_uid_inquiry", $row[on_uid]);
        goto_url("$g4[shop_path]/orderinquiryview.php?od_id=$row[od_id]&on_uid=$row[on_uid]");
    }
}

$g4[title] = "주문내역";
include_once("./_head.php");
?>

<img src="<?=$g4[shop_img_path]?>/top_orderinquiry.gif" border=0><p>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
    <td>

        <div style='height:30px;'>
        &nbsp;&nbsp;※ <font color="#FF6600">주문서번호를 클릭</font>하시면 주문, 입금, 배송정보등 세부 내역을 확인하실 수 있습니다.
        </div>

        <?
        $limit = " limit $from_record, $rows ";
        include "./orderinquiry.sub.php";
        ?>

    </td>
</tr>
</table>

<table width=98%>
<tr>
    <td width=50%>&nbsp;</td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>

<?
include_once("./_tail.php");
?>