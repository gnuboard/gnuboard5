<?
$sub_menu = "400200";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if ($ca_id && ($move == 'up' || $move == 'down')) {
    $parent_ca_id = parent_ca_id($ca_id);
    $length = strlen($ca_id);
    $level = $length / 2;

    // 클릭한 분류의 분류아이디와 출력순서
    $sql = " select ca_id, ca_sort from {$g4['yc4_category_table']} where ca_id = '$ca_id' ";
    $org = sql_fetch($sql);

    // 옮겨갈 분류의 분류아이디와 출력순서
    if ($move == 'up') {
        $sql = " select ca_id, ca_sort from {$g4['yc4_category_table']} where ca_id like '{$parent_ca_id}%' and length(ca_id) = $length and ca_sort < '{$org['ca_sort']}' order by ca_sort desc limit 1 ";
        $dst = sql_fetch($sql);
    } else {
        $sql = " select ca_id, ca_sort from {$g4['yc4_category_table']} where ca_id like '{$parent_ca_id}%' and length(ca_id) = $length and ca_sort > '{$org['ca_sort']}' order by ca_sort asc limit 1 ";
        $dst = sql_fetch($sql);
    }

    // 옮겨갈 분류가 있다면
    if ($dst) {
        $sql = " update {$g4['yc4_category_table']} set ca_sort = concat('{$org['ca_sort']}', mid(ca_sort,$level*4+1, 20)) where ca_id like '{$dst['ca_id']}%' ";
        sql_query($sql);

        $sql = " update {$g4['yc4_category_table']} set ca_sort = concat('{$dst['ca_sort']}', mid(ca_sort,$level*4+1, 20)) where ca_id like '{$org['ca_id']}%' ";
        sql_query($sql);
    }
}

$g4['title'] = "분류관리";
include_once(G4_ADMIN_PATH."/admin.head.php");


$where = " where ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

$sql_common = " from {$g4['yc4_category_table']} ";
if ($is_admin != 'super')
    $sql_common .= " $where ca_mb_id = '$member[mb_id]' ";
$sql_common .= $sql_search;


// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql_order = "order by ca_sort, ca_id ";

// 출력할 레코드를 얻음
$sql  = " select *
             {$sql_common}
             {$sql_order}
             limit {$from_record}, {$rows} ";
$result = sql_query($sql);

//$qstr = "page=$page&sort1=$sort1&sort2=$sort2";
$qstr  = "$qstr&amp;sca=$sca&amp;page=$page&amp;save_stx=$stx";
?>

<form id="flist" name="flist">
<input type="hidden" id="ca_id" name="ca_id" value="<?=$ca_id ?>">
<input type="hidden" id="move" name="move"  value="<?=$move ?>">
<input type="hidden" id="page" name="page" value="<?=$page?>">
<input type="hidden" id="save_stx" name="save_stx" value="<?=$stx?>">
<table>
<tr>
    <td><a href='<?=$_SERVER['PHP_SELF']?>'>처음</a></td>
    <td>
        <select id="sfl" name="sfl">
            <option value="ca_name" <?if($sfl == '분류명') echo "selected";?>>분류명</option>
            <option value="ca_id" <?if($sfl == '분류코드') echo "selected";?>>분류코드</option>
            <option value="ca_mb_id" <?if($sfl == '회원아이디') echo "selected";?>>회원아이디</option>
        </select>

        <input type="text" id="stx" name="stx" value="<?=$stx?>">
        <input type="submit" value="검색">
    </td>
    <td>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>

<a href="./categoryform.php">추가</a>

<form name="fcategorylist" method="post" action="./categorylistupdate.php" autocomplete="off">
<input type="hidden" name="page"  value="<?=$page?>">
<table>
<thead>
<tr>
    <th scope="col">분류코드</th>
    <th scope="col">분류명</th>
    <th scope="col">메뉴표시</th>
    <th scope="col">판매가능</th>
    <th scope="col">출력순서</th>
    <th scope="col">상품수</th>
    <th scope="col">관리</th>
</tr>
<thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $s_level = "";
    $level = strlen($row[ca_id]) / 2 - 1;
    if ($level > 0) // 2단계 이상
    {
        $s_level = "<img src=\"./img/icon_catlevel.gif\" width=\"17\" height=\"15\" alt=\"".($level+1)."단계 분류\">";
        for ($k=1; $k<$level; $k++)
            $s_level = $s_level;
        $style = " ";
    }
    else // 1단계
    {
        $style = " style='border:1 solid; border-color:#0071BD;' ";
    }

    $s_add = "<a href=\"./categoryform.php?ca_id=".$row['ca_id']."&amp;$qstr\">추가</a>";
    $s_upd = "<a href=\"./categoryform.php?w=u&amp;ca_id=".$row['ca_id']."&amp;$qstr\">수정</a>";
    $s_vie = "<a href=\"".$g4['shop_path']."/list.php?ca_id=".$row['ca_id'].">보기</a>";

    if ($is_admin == 'super')
        $s_del = "<a href=\"javascript:del('./categoryformupdate.php?w=d&amp;ca_id={$row['ca_id']}&amp;$qstr');\">삭제</a>";


    // 해당 분류에 속한 상품의 갯수
    $sql1 = " select COUNT(*) as cnt from {$g4['yc4_item_table']} where ca_id = '{$row['ca_id']}' or ca_id2 = '{$row['ca_id']}' or ca_id3 = '{$row['ca_id']}' ";
    $row1 = sql_fetch($sql1);
?>

<tr id="tr<?=$i?>">
    <td><input type="hidden" name="ca_id[]" value="<?=$row['ca_id']?>"><?=$row['ca_id']?></td>
    <td><?=$s_level?> <input type="text" name="ca_name[<?=$i?>]" value="<?=get_text($row['ca_name'])?>" title="<?=$row['ca_id']?>" required title="분류명" size="35" <?=$style?>></td>
    <td><input type="checkbox" name="ca_menu[<?=$i?>]" <?=($row['ca_menu'] ? "checked" : "")?> value="1"></td>
    <td><input type="checkbox" name="ca_use[<?=$i?>]" <?=($row['ca_use'] ? "checked" : "")?> value="1"></td>
    <td><a href="javascript:;" onclick="category_move('<?=$row['ca_id']?>', 'up')">위로 이동</a> <a href="javascript:;" onclick="category_move('<?=$row['ca_id']?>', 'down')">아래로 이동</a></td>
    <td><a href="./itemlist.php?sca=<?=$row['ca_id']?>"><?=$row1['cnt']?><span class="sound_only">개의 상품리스트</span></a></td>
    <td>
        <?=$s_upd?>
        <?=$s_del?>
        <?=$s_vie?>
        <?=$s_add?>
    </td>
</tr>

<?
}

if ($i == 0) {
    echo "<tr><td colspan=\"20\" class=\"empty_table\">자료가 한건도 없습니다.</td></tr>\n";
}
?>
</tbody>
</table>

<div><input type="submit" value="일괄수정"></div>

</form>
</table>

<?=get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF']."?$qstr&amp;page=");?>

<script>
function category_move(ca_id, move)
{
    var f = document.flist;
    f.ca_id.value = ca_id;
    f.move.value  = move;
    f.submit();
}

$(function() {
    $("form").find("input, select, textarea").keydown(function(e) {
        if (!e.ctrlKey) return;

        // 배열변수에 $i 값이 들어가므로 앞의 변수명만 취한다.
        var el_name = this.name.split("[")[0];

        var $find = null;
        if (e.keyCode == 37) {
            // 왼쪽
            $(this).prevAll("input, select, textarea").each(function() {
                if ($(this).is(":visible") && $(this).is(":enabled")) {
                    $find = $(this);
                    return false;
                }
            });

            if ($find) {
                $find.focus().select();
                return false;
            }

            $(this).parent("td").prevAll("td").each(function() {
                // element 를 오른쪽(거꾸로)부터 가지고 와야 한다.
                $( $(this).children("input, select, textarea").get().reverse() ).each(function() {
                    if ($(this).is(":visible") && $(this).is(":enabled")) {
                        $find = $(this);
                        return false;
                    }
                });

                if ($find)
                    return false;
            });

            if ($find) {
                $find.focus().select();
                return false;
            }
        }
        else if (e.keyCode == 38) {
            // 위
            //$(this).parents("tr").prev("tr").find("[name='"+this.name+"']").focus().select();
            $(this).parents("tr").prev("tr").find("[name^='"+el_name+"']").focus().select();
        }
        else if (e.keyCode == 39) {
            // 오른쪽
            $(this).nextAll("input, select, textarea").each(function() {
                if ($(this).is(":visible") && $(this).is(":enabled")) {
                    $find = $(this);
                    return false;
                }
            });

            if ($find) {
                $find.focus().select();
                return false;
            }

            $(this).parent("td").nextAll("td").children("input, select, textarea").each(function() {
                if ($(this).is(":visible") && $(this).is(":enabled")) {
                    $find = $(this);
                    return false;
                }
            });

            if ($find) {
                $find.focus().select();
                return false;
            }
        }
        else if (e.keyCode == 40) {
            // 아래
            $(this).parents("tr").next("tr").find("[name^='"+el_name+"']").focus().select();
        }

        e.preventDefault();
    });
});
</script>


<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
