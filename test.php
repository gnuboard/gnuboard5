<?
$qstr1 = "&amp;stx=ddd&amp;page=123";
$qstr2 = "&amp;stx=ddd&amp;page=123&amp;";
$qstr3 = "&amp;stx=ddd&amp;page=&amp;";
$qstr3 = "&amp;stx=ddd&amp;page=x&amp;";

echo preg_replace('#(&amp;|&)?page\=[^&]*#', '', $qstr3);
?>