<?
$sub_menu = '400710';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = 'FAQ관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['yc4_faq_master_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by fm_id desc limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
?>

<style type="text/css">
#faq_box th{height:35px;line-height:35px;text-align:center}
#faq_box p{position:relative}
#faq_box span{position:absolute;top:-12;right:5px}
#faq_register h3{color:#18abff}
#faq_register ul{list-style:none;padding-left:0}
#faq_register ul li{height:20px;line-height:20px}
</style>

<section id="faq_box" class="cbox">
<h2>FAQ관리</h2>
<p><a href='<?=$_SERVER['PHP_SELF']?>'>처음</a><span>건수 : <? echo $total_count ?></span></p>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_11">
        <col class="grid_2">
        <col class="gird_2">
        <col class="grid_1">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">제목</th>
        <th scope="col">FAQ수</th>
        <th scope="col">상세보기</th>
        <th scope="col"><a href='./faqmasterform.php'><img src='<?=G4_ADMIN_URL?>/img/icon_insert.gif' border=0 title='등록'></a></th>
    </tr>
    </thead>
    <tbody>
        <?
        for ($i=0; $row=mysql_fetch_array($result); $i++)
        {
            $sql1 = " select COUNT(*) as cnt from {$g4['yc4_faq_table']} where fm_id = '{$row['fm_id']}' ";
            $row1 = sql_fetch($sql1);
            $cnt = $row1['cnt'];

            $s_detail_vie = icon("보기", "./faqlist.php?fm_id={$row['fm_id']}");

            $s_mod = icon("수정", "./faqmasterform.php?w=u&fm_id={$row['fm_id']}");
            $s_del = icon("삭제", "javascript:del('./faqmasterformupdate.php?w=d&fm_id={$row['fm_id']}');");
            $s_vie = icon("보기", G4_SHOP_URL."/faq.php?fm_id={$row['fm_id']}");

            $list = $i%2;
            echo "
            <tr class='list$list ht'>
                <td align=center>{$row['fm_id']}</td>
                <td>" . stripslashes($row['fm_subject']) . "</td>
                <td align=center>$cnt</td>
                <td align=center>$s_detail_vie</td>
                <td align=center>$s_mod $s_del $s_vie</td>
            </tr>";
        }

        if ($i == 0)
            echo "<tr><td colspan=5 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
        ?>
    </tbody>
    </table>
</section>

<p><?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?></p>

<section id="faq_register" class="cbox">
    <h2>FAQ 등록하기</h2>
    <ul>
        <li>: FAQ는 무제한으로 등록할 수 있습니다</li>
        <li>1. 먼저 <img src='<?=G4_ADMIN_URL?>/img/icon_insert.gif'>를 눌러 FAQ Master를 생성합니다. (하나의 FAQ 타이틀 생성 : 자주하시는 질문, 이용안내..등 )</li>
        <li> 2. 상세보기에 있는 <img src='<?=G4_ADMIN_URL?>/img/icon_viewer.gif'>을 눌러 세부 내용으로 들어갑니다.</li>
    </ul>
</section>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
