<?php

class CreateIdModule {

    function makeTid($payMetod, $mid, $mobileType) {
        date_default_timezone_set('Asia/Seoul');
        $date = new DateTime();

        $prefix = "";

        if ($mobileType) {
            $prefix = "StdMX_";
        } else {
            $prefix = "Stdpay";
        }


        /////////////
        list($usec, $sec) = explode(" ", microtime());
        $time = date("YmdHis", $sec) . intval(round($usec * 1000));
        if (strlen($time) == 17) {
            
        } elseif (strlen($time) == 16) {
            $time = $time . "0";
        } else {
            $time = $time . "00";
        }
        /////////////

        $tid = $prefix . $this->getPGID($payMetod) . $mid . $time . $this->makeRandNum();


        return $tid;
    }

    function getPGID($payMethod) {
        $pgid = "";

        if ($payMethod == "Card") {
            $pgid = "CARD";
        } elseif ($payMethod == "Account") {
            $pgid = "ACCT";
        } elseif ($payMethod == "DirectBank") {
            $pgid = "DBNK";
        } elseif ($payMethod == "OCBPoint") {
            $pgid = "OCBP";
        } elseif ($payMethod == "VCard") {
            $pgid = "ISP_";
        } elseif ($payMethod == "HPP") {
            $pgid = "HPP_";
        } elseif ($payMethod == "Nemo") {
            $pgid = "NEMO";
        } elseif ($payMethod == "ArsBill") {
            $pgid = "ARSB";
        } elseif ($payMethod == "PhoneBill") {
            $pgid = "PHNB";
        } elseif ($payMethod == "Ars1588Bill") {
            $pgid = "1588";
        } elseif ($payMethod == "VBank") {
            $pgid = "VBNK";
        } elseif ($payMethod == "Culture") {
            $pgid = "CULT";
        } elseif ($payMethod == "CMS") {
            $pgid = "CMS_";
        } elseif ($payMethod == "AUTH") {
            $pgid = "AUTH";
        } elseif ($payMethod == "INIcard") {
            $pgid = "INIC";
        } elseif ($payMethod == "MDX") {
            $pgid = "MDX_";
        } elseif ($payMethod == "CASH") {
            $pgid = "CASH";
        } elseif (strlen($payMethod) > 4) {
            $pgid = strtoupper($payMethod);
            $pgid = substr($pgid, 0, 4);
        } else {
            $pgid = trim($pgid);
        }

        return $pgid;
    }

    //랜덤 숫자 생성
    function makeRandNum() {
        $strNum = "";
        $randNum = rand(0, 300);

        if ($randNum < 10) {
            $strNum = $strNum . "00" . $randNum;
        } elseif ($randNum < 100) {
            $strNum = $strNum . "0" . $randNum;
        } else {
            $strNum = $randNum;
        }

        return $strNum;
    }

}