<?php
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
require dirname(__FILE__).'/../shop/inicis/libs/response.lib.php';
set_error_handler(function ($code, $message) { throw new Exception($message); });
$checks = 0;
foreach (array(
    '{"resultCode":"0000","tid":"test","extra":{"new":true}}',
    '{"extra":null,"tid":"test","resultCode":"0000"}'
) as $json) {
    $fields = inicis_response_fields(json_decode($json, true), array('resultCode', 'tid'));
    if ($fields['resultCode'] !== '0000' || $fields['tid'] !== 'test') throw new Exception('JSON mismatch');
    $checks++;
}
parse_str('extra[item]=new&P_AMT=0&P_STATUS=00&P_TID=test', $data);
$fields = inicis_response_fields($data, array('P_STATUS', 'P_TID', 'P_AMT'));
$fields = array_map('trim', $fields);
if (isset($fields['extra']) || $fields['P_AMT'] !== '0') throw new Exception('NVP mismatch');
$checks++;
foreach (array(null, false, array(), array('tid'=>array('test')), array('tid'=>null), array('tid'=>true), array('tid'=>' ')) as $bad) {
    $rejected = false;
    try { inicis_response_fields($bad, array('tid')); } catch (Exception $e) { $rejected = true; }
    if (!$rejected) throw new Exception('Invalid required field accepted');
    $checks++;
}
echo 'PASS '.$checks." response checks\n";
