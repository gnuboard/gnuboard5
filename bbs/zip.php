<?
include_once("./_common.php");

// 메모리를 많이 잡아먹어서 아래의 코드로 대체
//ini_set('memory_limit', '20M');
//$zipfile = file("./zip.db");

$zipfile = array();
$fp = fopen("./zip.db", "r");
while(!feof($fp)) {
    $zipfile[] = fgets($fp, 4096);
}
fclose($fp);

$search_count = 0;

if ($addr1) 
{
    while ($zipcode = each($zipfile)) 
    {
        if(strstr(substr($zipcode[1],9,512), $addr1))
        {
            $list[$search_count][zip1] = substr($zipcode[1],0,3);
            $list[$search_count][zip2] = substr($zipcode[1],4,3);    
            $addr = explode(" ", substr($zipcode[1],8));

            if ($addr[sizeof($addr)-1]) 
            {
                $list[$search_count][addr] = str_replace($addr[sizeof($addr)-1], "", substr($zipcode[1],8));
                $list[$search_count][bunji] = trim($addr[sizeof($addr)-1]);
            }
            else
                $list[$search_count][addr] = substr($zipcode[1],8);

            $list[$search_count][encode_addr] = urlencode($list[$search_count][addr]);
            $search_count++;
        }    
    }

    if (!$search_count) alert("찾으시는 주소가 없습니다.");
}

/* 기존의 DB에서 불러오는 방식
if ($addr1) 
{
    //$sql = " select * from $g4[zip_table] where zp_dong like '%$addr1%' order by zp_id ";
    $sql = " select * from $g4[zip_table] where zp_dong like '%$addr1%' order by zp_sido, zp_gugun, zp_dong ";
    $result = sql_query($sql);
    $search_count = 0;
    for ($i=0; $row=sql_fetch_array($result); $i++) 
    {
        $list[$i][zip1] = substr($row[zp_code], 0, 3);
        $list[$i][zip2] = substr($row[zp_code], 3, 3);
        $list[$i][addr] = "$row[zp_sido] $row[zp_gugun] $row[zp_dong]";
        $list[$i][bunji] = $row[zp_bunji];
        $list[$i][encode_addr] = urlencode($list[$i][addr]);
        $search_count++;
    }

    if (!$search_count) 
        alert("찾으시는 주소가 없습니다.");
}
*/

$g4[title] = "우편번호 검색";
include_once("$g4[path]/head.sub.php");

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
include_once("$member_skin_path/zip.skin.php");

include_once("$g4[path]/tail.sub.php");
?>
