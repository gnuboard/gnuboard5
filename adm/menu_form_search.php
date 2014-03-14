<?php
include_once('./_common.php');

if ($is_admin != 'super')
    die('최고관리자만 접근 가능합니다.');

switch($type) {
    case 'group':
        $sql = " select gr_id as id, gr_subject as subject
                    from {$g5['group_table']}
                    order by gr_order, gr_id ";
        break;
    case 'board':
        $sql = " select bo_table as id, bo_subject as subject
                    from {$g5['board_table']}
                    order by bo_order, bo_table ";
        break;
    case 'content':
        $sql = " select co_id as id, co_subject as subject
                    from {$g5['content_table']}
                    order by co_id ";
        break;
    default:
        $sql = '';
        break;
}

if($sql) {
    $result = sql_query($sql);

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0) echo '<ul>'.PHP_EOL;

        switch($type) {
            case 'group':
                $link = G5_BBS_URL.'/group.php?gr_id='.$row['id'];
                break;
            case 'board':
                $link = G5_BBS_URL.'/board.php?bo_table='.$row['id'];
                break;
            case 'content':
                $link = G5_BBS_URL.'/content.php?co_id='.$row['id'];
                break;
            default:
                $link = '';
                break;
        }

        echo '<li>'.PHP_EOL;
        echo '<input type="hidden" name="subject[]" value="'.preg_replace('/[\'\"]/', '', $row['subject']).'">'.PHP_EOL;
        echo '<input type="hidden" name="link[]" value="'.$link.'">'.PHP_EOL;
        echo '<span>'.$row['subject'].'</span>';
        echo '<button type="button" class="add_select">선택</button>'.PHP_EOL;
        echo '</li>'.PHP_EOL;
    }
} else { ?>
<table>
<tbody>
<tr>
    <th><label for="me_name">메뉴</label></th>
    <td><input type="text" name="me_name" id="me_name" class="frm_input"></td>
</tr>
<tr>
    <th><label for="me_link">링크</label></th>
    <td>
        <input type="text" name="me_link" id="me_link" class="frm_input"><br>
        링크는 http://를 포함해서 입력해 주세요.
    </td>
</tr>
<tr>
    <td colspan="2"><button type="button" id="add_manual">추가</button>
</tr>
</tbody>
</table>
<?php } ?>