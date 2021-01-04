<?php
$sub_menu = "900300";
include_once("./_common.php");
@include_once(G5_PLUGIN_PATH."/sms5/JSON.php");

if( !function_exists('json_encode') ) {
    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}

ajax_auth_check_menu($auth, $sub_menu, "r");

$lev = array();

for ($i=1; $i<=10; $i++)
{
    $lev[$i] = 0;
}

$qry = sql_query("select mb_level, count(*) as cnt from {$g5['member_table']} where mb_sms=1 and not (mb_hp='') group by mb_level");

while ($row = sql_fetch_array($qry))
{
    $lev[$row['mb_level']] = $row['cnt'];
}
$str_json = array();
$line = 0;
$tmp_str = '';
$tmp_str .= '
<div class="tbl_head01 tbl_wrap">
    <table>
    <thead>
    <tr>
        <th scope="col">권한</th>
        <th scope="col">수신가능</th>
        <th scope="col">추가</th>
    </tr>
    </thead>
    <tbody>';
    for ($i=1; $i<=10; $i++) {
        $bg = 'bg'.($line++%2);
        $tmp_str .= '
        <tr class="'.$bg.'">
            <td>'.$i.' 레벨</td>
            <td class="td_num">'.number_format($lev[$i]).'</td>
            <td class="td_mng"><button type="button" class="btn_frmline" onclick="sms_obj.level_add('.$i.', \''.number_format($lev[$i]).'\')">추가</button></td>
        </tr>';
    }
$tmp_str .= '
    </tbody>
    </table>
</div>';

$str_json['html'] = $tmp_str;
echo json_encode($str_json);