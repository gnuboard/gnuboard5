<?php

require_once('../libs/INIStdPayUtil.php');

$SignatureUtil = new INIStdPayUtil();

$input = "oid=" . $_REQUEST["oid"] . "&price=" . $_REQUEST["price"] . "&timestamp=" . $_REQUEST["timestamp"];

$output['signature'] = array(
    ///'signature' => $SignatureUtil->makeHash($input, "sha256")
    'signature' => hash("sha256", $input)
);

echo json_encode($output);