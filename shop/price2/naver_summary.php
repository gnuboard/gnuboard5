<?php
include_once('./_common.php');

ob_start();

/*
요약 상품페이지

Field   Status  Notes
<<<begin>>> 필수    상품의 시작을 알리는 필드
<<<mapid>>>         판매하는 상품의 유니크한 상품ID
<<<pname>>>         실제 서비스에 반영될 상품명(Title)
<<<price>>>         해당 상품의 판매가격
<<<class>>>         I(신규상품) / U (업데이트상품) / D (품절상품)
<<<utime>>>         상품정보 생성 시각
<<<ftend>>> 필수    상품의 마지막을 알리는 필드
*/

$lt = "<<<";
$gt = ">>>";

$sql =" select it_id, it_name, it_price, it_time from {$g5['g5_shop_item_table']} where it_use = '1' order by ca_id";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 상품별옵션
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' and io_use = '1' order by io_no asc ";
    $result2 = sql_query($sql);
    $opt_count = @mysql_num_rows($result2);

    if(!$opt_count) {
        $it_name = $row['it_name'];
        $it_price = $row['it_price'];
        $stock_qty = get_it_stock_qty($row['it_id']);

        echo "{$lt}begin{$gt}\n";
        echo "{$lt}mapid{$gt}{$row['it_id']}\n";
        if ($stock_qty <= 0)
        {
            // 품절 상품 양식
            echo "{$lt}class{$gt}D\n";
        }
        else
        {
            // 업데이트 상품 양식 & 품절 복구 상품 양식
            echo "{$lt}pname{$gt}{$it_name}\n";
            echo "{$lt}price{$gt}{$it_price}\n";
            echo "{$lt}class{$gt}U\n";
        }
        echo "{$lt}utime{$gt}{$row['it_time']}\n";
        echo "{$lt}ftend{$gt}\n";

    } else {
        $subj = explode(',', $row['it_option_subject']);
        for($k=0; $row2=sql_fetch_array($result2); $k++) {
            $it_name = $row['it_name'].' ';
            $opt = explode(chr(30), $row2['io_id']);
            $sep = '';
            for($j=0; $j<count($subj); $j++) {
                $it_name .= $sep.$subj[$j].':'.$opt[$j];
                $sep = ' ';
            }
            $it_price = $row['it_price'] + $row2['io_price'];
            $stock_qty = get_option_stock_qty($row['it_id'], $row2['io_id'], 0);

            echo "{$lt}begin{$gt}\n";
            echo "{$lt}mapid{$gt}{$row['it_id']}\n";
            if ($stock_qty <= 0)
            {
                // 품절 상품 양식
                echo "{$lt}class{$gt}D\n";
            }
            else
            {
                // 업데이트 상품 양식 & 품절 복구 상품 양식
                echo "{$lt}pname{$gt}{$it_name}\n";
                echo "{$lt}price{$gt}{$it_price}\n";
                echo "{$lt}class{$gt}U\n";
            }
            echo "{$lt}utime{$gt}{$row['it_time']}\n";
            echo "{$lt}ftend{$gt}\n";

        }
    }
}

$content = ob_get_contents();
ob_end_clean();

// 091223 : 네이버에서는 아직 utf-8 을 지원하지 않고 있음
$content = iconv('utf-8', 'euc-kr', $content);

echo $content;
?>