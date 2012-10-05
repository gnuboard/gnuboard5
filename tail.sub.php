<script type="text/javascript" src="<?=$g4['path']?>/js/wrest.js"></script>

<!-- 새창 대신 사용하는 iframe -->
<iframe width=0 height=0 name='hiddenframe' style='display:none;'></iframe>

<? if ($is_admin == "super") { ?><!-- <div style='float:left; width:<?=$table_width?>px; text-align:center;'>RUN TIME : <?=get_microtime()-$begin_time;?><br></div> --><? } ?>

</body>
</html>
<?
$tmp_sql = " select count(*) as cnt from $g4[login_table] where lo_ip = '$_SERVER[REMOTE_ADDR]' ";
$tmp_row = sql_fetch($tmp_sql);
//sql_query(" lock table $g4[login_table] write ", false);
if ($tmp_row['cnt'])
{
	$tmp_sql = " update $g4[login_table] set mb_id = '$member[mb_id]', lo_datetime = '$g4[time_ymdhis]', lo_location = '$lo_location', lo_url = '$lo_url' where lo_ip = '$_SERVER[REMOTE_ADDR]' ";
	sql_query($tmp_sql, FALSE);
}
else
{
	$tmp_sql = " insert into $g4[login_table] ( lo_ip, mb_id, lo_datetime, lo_location, lo_url ) values ( '$_SERVER[REMOTE_ADDR]', '$member[mb_id]', '$g4[time_ymdhis]', '$lo_location',  '$lo_url' ) ";
	sql_query($tmp_sql, FALSE);

	// 시간이 지난 접속은 삭제한다
	sql_query(" delete from $g4[login_table] where lo_datetime < '".date("Y-m-d H:i:s", $g4[server_time] - (60 * $config[cf_login_minutes]))."' ");

	// 부담(overhead)이 있다면 테이블 최적화
	//$row = sql_fetch(" SHOW TABLE STATUS FROM `$mysql_db` LIKE '$g4[login_table]' ");
	//if ($row['Data_free'] > 0) sql_query(" OPTIMIZE TABLE $g4[login_table] ");
}
//sql_query(" unlock tables ", false);
?>