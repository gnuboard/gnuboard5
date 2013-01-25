<?
$sub_menu = "500125";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

//if (!$_FILES[csv_file][tmp_name]) alert('csv 파일을 선택해주세요.');

//print_r2($_FILES);

if (!preg_match("/(.csv)$/i", $_FILES['csv_file']['name'])) 
    alert('csv 파일을 선택해주세요.');

$i = 0;
$handle = fopen($_FILES[csv_file][tmp_name], "r");
while (($item = fgetcsv($handle, 1000, ",")) !== FALSE)
{
    // 앞, 뒤의 " 를 모두 삭제
    $od_id = preg_replace("/^([\"\'])(.*)([\"\'])$/", "$2", $item[8]);      // 주문번호
    $od_invoice = preg_replace("/^([\"\'])(.*)([\"\'])$/", "$2", $item[9]); // 운송장번호

    //print_r2($item);

    if ($od_id && $od_invoice) 
    {
        $sql = " select od_id, on_uid, dl_id, od_invoice from $g4[yc4_order_table] where od_id = '$od_id' ";
        $row = sql_fetch($sql);
        //echo $sql; echo "<br/>";
        //print_r2($row);
        if (!$row[od_id]) continue;
        
        // 이미 입력된 운송장번호를 모두 새로 수정하지 않는다면...
        if ($row[dl_id] && $row[od_invoice] && !$re)
            continue;

        $sql = " update $g4[yc4_order_table] 
                    set od_invoice = '$od_invoice', 
                        od_invoice_time = '$od_invoice_time', 
                        dl_id = '$_POST[dl_id]' 
                  where od_id = '$od_id' ";
        sql_query($sql);

        if ($ct_status) 
        {
            $sql = " update $g4[yc4_cart_table] 
                        set ct_status = '$ct_status' 
                      where on_uid = '$row[on_uid]' 
                        and ct_status in ('주문', '준비', '배송', '완료') ";
            sql_query($sql);
        }

        $i++;
        $msg .= sprintf("%05d", $i) . ". 주문번호 : <a href='orderform.php?od_id=$od_id' target='_blank'>$od_id</a> -> 송장번호 : $od_invoice<br/>";
    }
}

$g4[title] = "배송일괄등록 처리결과";
include_once ("$g4[admin_path]/admin.head.php");

echo subtitle($g4[title]);
echo "<p>";

if ($msg) 
{
    echo $msg;
    echo "업데이트 완료<br/>";
}
else
    echo "처리 내역이 없습니다.";
echo "<p>[끝]";

include_once ("$g4[admin_path]/admin.tail.php");
?>