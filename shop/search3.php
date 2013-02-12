<?
include_once("./_common.php");

// 상품이미지 사이즈(폭, 높이)를 몇배 축소 할것인지를 설정
// 0 으로 설정하면 오류남 : 기본 2
$image_rate = 2;

$g4[title] = "상품 검색";
include_once("./_head.php");
?>

<img src="<?=$g4[shop_img_path]?>/top_search.gif" border="0"><p>

<table width=100% cellpadding=0 cellspacing=0 align=center border=0>
<tr>
    <td>
        &nbsp;&nbsp; 찾으시는 검색어는 &quot;<b><?=stripslashes(get_text($search_str))?></b>&quot; 입니다.
        <br><br>
        <?
        // QUERY 문에 공통적으로 들어가는 내용
        // 상품명에 검색어가 포한된것과 상품판매가능인것만
        $sql_common = " from $g4[shop_item_table] a, 
                             $g4[shop_category_table] b
                       where a.ca_id=b.ca_id
                         and a.it_use = 1 
                         and b.ca_use = 1
                       /* 중복검색에 대한 오류로 인해 막음 : where (a.ca_id=b.ca_id or a.ca_id2=b.ca_id or a.ca_id3=b.ca_id) */ ";
        if ($search_str) {
            $sql_common .= " and ( a.it_id like '$search_str%' or 
                                   a.it_name like   '%$search_str%' or
                                   a.it_basic like  '%$search_str%' or
                                   a.it_explan like '%$search_str%' ) ";
        }

        // 분류선택이 있다면 특정 분류만
        if ($search_ca_id != "")
            $sql_common .= " and a.ca_id like '$search_ca_id%' ";

        // 검색된 내용이 몇행인지를 얻는다
        $sql = " select COUNT(*) as cnt $sql_common ";
        $row = sql_fetch($sql);
        $total_count = $row[cnt];
        echo "&nbsp;&nbsp; 입력하신 검색어로 총 <b>{$total_count}건</b>의 상품이 검색 되었습니다.<br><br>";

        // 임시배열에 저장해 놓고 분류별로 출력한다.
        // write_serarch_save() 함수가 임시배열에 있는 내용을 출력함
        if ($total_count > 0) {
            // 인기검색어
            $sql = " insert into $g4[popular_table]
                        set pp_word = '$search_str',
                            pp_date = '".G4_TIME_YMD"',
                            pp_ip = '$_SERVER[REMOTE_ADDR]' ";
            sql_query($sql, FALSE);

            unset($save); // 임시 저장 배열
            $sql = " select a.ca_id, 
                            a.it_id
                     $sql_common
                     order by a.ca_id, a.it_id desc ";
            $result = sql_query($sql);
            for ($i=0; $row=mysql_fetch_array($result); $i++) {
                if ($save[ca_id] != $row[ca_id]) {
                    if ($save[ca_id]) {
                        write_search_save($save);
                        unset($save);
                    }
                    $save[ca_id] = $row[ca_id];
                    $save[cnt] = 0;
                }
                $save[it_id][$save[cnt]] = $row[it_id];
                $save[cnt]++;
            }
            mysql_free_result($result);
            write_search_save($save);
        }
        ?>
    </td>
</tr>
</table>

<?
function write_search_save($save) 
{
	global $g4, $search_str , $default , $image_rate , $cart_dir;

    $sql = " select ca_name from $g4[shop_category_table] where ca_id = '$save[ca_id]' ";
    $row = sql_fetch($sql);

    /*
    echo "
    <table width=98% cellpadding=0 cellspacing=0 border=0 align=center>
    <colgroup width=80>
    <colgroup width=>
    <colgroup width=150>
    <colgroup width=100>
    <tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
    <tr>
        <td colspan=2 height='28'>&nbsp;<b><a href='./list.php?ca_id={$save[ca_id]}'>$row[ca_name]</a></b> ($save[cnt])</td>
        <td align=center>판매가격</td>
        <td align=center>포인트</td>
    </tr>
    <tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
    ";
    */

    // 김선용 2006.12 : 중복 하위분류명이 많으므로 대분류 포함하여 출력
    $ca_temp = "";
    if(strlen($save['ca_id']) > 2) // 중분류 이하일 경우
    {
        $sql2 = " select ca_name from $g4[shop_category_table] where ca_id='".substr($save[ca_id],0,2)."' ";
        $row2 = sql_fetch($sql2);
        $ca_temp = "<b><a href='./list.php?ca_id=".substr($save[ca_id],0,2)."'>$row2[ca_name]</a></b> &gt; ";
    }

    $list_mod = 4;
    $td_width = (int)(100 / $list_mod);
    $img_width  = $default[de_simg_width]; 
    $img_height = $default[de_simg_height]; 

    echo "
    <table width=98% cellpadding=0 cellspacing=0 border=0 align=center>
    <tr>
        <td colspan='$list_mod' height='28'>&nbsp;{$ca_temp}<b><a href='./list.php?ca_id={$save[ca_id]}'>$row[ca_name]</a></b> ($save[cnt])</td>
    </tr>
    <tr><td colspan='$list_mod' height=1 bgcolor=#CCCCCC></td></tr>";

    for ($i=0; $i<$save[cnt]; $i++) {
        $sql = " select it_id,
                        it_name, 
                        it_amount,
                        it_amount2,
                        it_amount3,
                        it_tel_inq,
                        it_cust_amount,
                        it_gallery,
                        it_point,
                        it_type1,
                        it_type2,
                        it_type3,
                        it_type4,
                        it_type5
                   from $g4[shop_item_table] where it_id = '{$save[it_id][$i]}' ";
        $row = sql_fetch($sql);

        //$image = get_it_image("$row[it_id]_s", (int)($default[de_simg_width] / $image_rate), (int)($default[de_simg_height] / $image_rate), $row[it_id]);

        if ( ($i>0) && (($i%$list_mod)==0) ) 
        {
            echo "</tr>\n\n";
            echo "<tr><td colspan='$list_mod' background='$g4[shop_img_path]/line_h.gif' height=1></td></tr>\n\n";
            echo "<tr>\n";
        }

        echo "
        <td width='{$td_width}%' align=center valign=top>
            <br>
            <table width=98% cellpadding=2 cellspacing=0>
            <tr><td align=center>".get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id])."</td></tr>
            <tr><td align=center>".it_name_icon($row)."</td></tr>";
        
        if ($row[it_cust_amount] && !$row[it_gallery])
            echo "<tr><td align=center><strike>".display_amount($row[it_cust_amount])."</strike></td></tr>";

        echo "<tr><td align=center>";
        
        if (!$row[it_gallery])
            echo "<span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span>";

        echo "</td></tr></table></td>";

    } 

    // 나머지 td 를 채운다.
    if (($cnt = $i%$list_mod) != 0)
        for ($k=$cnt; $k<$list_mod; $k++)
            echo "<td>&nbsp;</td>\n";

    echo "<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>";
    echo "</table><br><br>\n";
}

include_once("./_tail.php");
?>
