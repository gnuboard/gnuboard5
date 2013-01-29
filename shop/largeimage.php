<?
include_once("./_common.php");

$sql = " select it_name, it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
            from $g4[yc4_item_table]
            where it_id='$it_id' ";
$row = sql_fetch_array(sql_query($sql));

$imagefile = G4_DATA_PATH."/item/$img";
$imagefile_url = G4_DATA_URL."/item/$img";
$size = getimagesize($imagefile);

$g4['title'] = "{$row['it_name']} ($it_id)";
include_once(G4_PATH.'/head.sub.php');
?>
<br>
<div align=center>
    <a href='#' onclick='window.close();'><img id='largeimage' src='<?=$imagefile_url?>' width='<?=$size[0]?>' height='<?=$size[1]?>' alt='<?=$row['it_name']?>' border=0 style='border:1 solid #E4E4E4;'></a>
</div>
<p>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
    <td width=30% align=center><a href='#' onclick='window.close();'><img src='<? echo G4_SHOP_IMG_URL; ?>/btn_close.gif' border=0 alt="창닫기"></a></td>
    <td width=70% align=right>
        <?
        for ($i=1; $i<=10; $i++)
        {
            $filename = $row['it_img'.$i];
            if (file_exists(G4_DATA_PATH."/item/{$it_id}/{$filename}") && $filename != "")
                echo "<img id='large{$i}' src='".G4_DATA_URL."/item/{$it_id}/{$filename}' border=0 width=50 height=50 style='border:1 solid #E4E4E4;'
                    onmouseover=\"document.getElementById('largeimage').src=document.getElementById('large{$i}').src;\"> &nbsp;";
        }
        ?>
        &nbsp;</td>
</tr>
</table>
<?
include_once(G4_PATH.'/tail.sub.php');
?>