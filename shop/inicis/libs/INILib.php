<?php

/**
 * Copyright (C) 2007 INICIS Inc.
 *
 * 해당 라이브러리는 절대 수정되어서는 안됩니다.
 * 임의로 수정된 코드에 대한 책임은 전적으로 수정자에게 있음을 알려드립니다.
 *
 */
require_once('INICls.php');
require_once('INISoc.php');

class INIpay50 {

    var $m_type;     // 거래 유형
    var $m_resulterrcode;       // 결과메세지 에러코드
    var $m_connIP;
    var $m_cancelRC = 0;
    var $m_Data;
    var $m_Log;
    var $m_Socket;
    var $m_Crypto;
    var $m_REQUEST = array();
    var $m_REQUEST2 = array();
    var $m_RESULT = array();

    function INIpay() {
        $this->UnsetField();
    }

    function UnsetField() {
        unset($this->m_REQUEST);
        unset($this->m_RESULT);
    }

    /* -------------------------------------------------- */
    /* 																									 */
    /* 결제/취소 요청값 Set or Add                      */
    /* 																									 */
    /* -------------------------------------------------- */

    function SetField($key, $val) { //Default Entity
        $this->m_REQUEST[$key] = $val;
    }

    function SetXPath($xpath, $val) { //User Defined Entity
        $this->m_REQUEST2[$xpath] = $val;
    }

    /* -------------------------------------------------- */
    /* 																									 */
    /* 결제/취소 결과값 fetch                           */
    /* 																									 */
    /* -------------------------------------------------- */

    function GetResult($name) { //Default Entity
        $result = $this->m_RESULT[$name];
        if ($result == "")
            $result = $this->m_Data->GetXMLData($name);
        if ($result == "")
            $result = $this->m_Data->m_RESULT[$name];
        return $result;
    }

    /* -------------------------------------------------- */
    /* 																									 */
    /* 결제/취소 처리 메인                              */
    /* 																									 */
    /* -------------------------------------------------- */

    function startAction() {

        /* -------------------------------------------------- */
        /* Overhead Operation                               */
        /* -------------------------------------------------- */
        $this->m_Data = new INIData($this->m_REQUEST, $this->m_REQUEST2);

        /* -------------------------------------------------- */
        /* Log Start																				 */
        /* -------------------------------------------------- */
        $this->m_Log = new INILog($this->m_REQUEST);
        if (!$this->m_Log->StartLog()) {
            $this->MakeTXErrMsg(LOG_OPEN_ERR, "로그파일을 열수가 없습니다.[" . $this->m_REQUEST["inipayhome"] . "]");
            return;
        }

        /* -------------------------------------------------- */
        /* Logging Request Parameter												 */
        /* -------------------------------------------------- */
        $this->m_Log->WriteLog(DEBUG, $this->m_REQUEST);

        /* -------------------------------------------------- */
        /* Set Type																					 */
        /* -------------------------------------------------- */
        $this->m_type = $this->m_REQUEST["type"];

        /* -------------------------------------------------- */
        /* Check Field																			 */
        /* -------------------------------------------------- */
        if (!$this->m_Data->CheckField()) {
            $err_msg = "필수항목(" . $this->m_Data->m_ErrMsg . ")이 누락되었습니다.";
            $this->MakeTXErrMsg($this->m_Data->m_ErrCode, $err_msg);
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            return;
        }
        $this->m_Log->WriteLog(INFO, "Check Field OK");

        /* -------------------------------------------------- */
        //웹페이지위변조용 키생성. 여기서 끝!!
        /* -------------------------------------------------- */
        if ($this->m_type == TYPE_CHKFAKE) {
            return $this->MakeChkFake();
        }

        /* -------------------------------------------------- */
        //Generate TID
        /* -------------------------------------------------- */
        if ($this->m_type == TYPE_SECUREPAY || $this->m_type == TYPE_FORMPAY || $this->m_type == TYPE_OCBSAVE ||
                $this->m_type == TYPE_AUTHBILL || $this->m_type == TYPE_FORMAUTH || $this->m_type == TYPE_REQREALBILL ||
                $this->m_type == TYPE_REPAY || $this->m_type == TYPE_VACCTREPAY || $this->m_type == TYPE_RECEIPT || $this->m_type == TYPE_AUTH
        ) {
            if (!$this->m_Data->MakeTID()) {
                $err_msg = "TID생성에 실패했습니다.::" . $this->m_Data->m_sTID;
                $this->m_Log->WriteLog(ERROR, $err_msg);
                $this->MakeTXErrMsg(MAKE_TID_ERR, $err_msg);
                $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
                return;
            }
            $this->m_Log->WriteLog(INFO, 'Make TID OK ' . $this->m_Data->m_sTID);
        }

        $this->m_Crypto = new INICrypto($this->m_REQUEST);

        /* -------------------------------------------------- */
        //PI공개키 로드
        /* -------------------------------------------------- */
        $this->m_Data->ParsePIEncrypted();
        $this->m_Log->WriteLog(INFO, "PI PUB KEY LOAD OK [" . $this->m_Data->m_PIPGPubSN . "]");

        /* -------------------------------------------------- */
        //PG공개키 로드
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Crypto->LoadPGPubKey($pg_cert_SN)) != OK) {
            $err_msg = "PG공개키 로드오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            return;
        }
        $this->m_Data->m_TXPGPubSN = $pg_cert_SN;
        $this->m_Log->WriteLog(INFO, "PG PUB KEY LOAD OK [" . $this->m_Data->m_TXPGPubSN . "]");

        /* -------------------------------------------------- */
        //상점개인키 로드
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Crypto->LoadMPrivKey()) != OK) {
            $err_msg = "상점개인키 로드오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Crypto->FreePubKey();
            return;
        }
        $this->m_Log->WriteLog(INFO, "MERCHANT PRIV KEY LOAD OK");

        /* -------------------------------------------------- */
        //상점 공개키 로드(SN 를 알기위해!!)
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Crypto->LoadMPubKey($m_cert_SN)) != OK) {
            $err_msg = "상점공개키 로드오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            return;
        }
        $this->m_Data->m_MPubSN = $m_cert_SN;
        $this->m_Log->WriteLog(INFO, "MERCHANT PUB KEY LOAD OK [" . $this->m_Data->m_MPubSN . "]");

        /* -------------------------------------------------- */
        //폼페이 암호화( formpay, cancel, repay, recept, inquiry, opensub)
        /* -------------------------------------------------- */
        if ($this->m_type == TYPE_CANCEL || $this->m_type == TYPE_REPAY || $this->m_type == TYPE_VACCTREPAY ||
                $this->m_type == TYPE_FORMPAY || $this->m_type == TYPE_RECEIPT ||
                $this->m_type == TYPE_CAPTURE || $this->m_type == TYPE_INQUIRY || $this->m_type == TYPE_OPENSUB ||
                ($this->m_type == TYPE_ESCROW && $this->m_Data->m_EscrowType == TYPE_ESCROW_DLV ) ||
                ($this->m_type == TYPE_ESCROW && $this->m_Data->m_EscrowType == TYPE_ESCROW_DNY_CNF ) ||
                $this->m_type == TYPE_REFUND
        ) {
            if (($rtv = $this->m_Data->MakeEncrypt($this->m_Crypto)) != OK) {
                $err_msg = "암호화 오류";
                $this->m_Log->WriteLog(ERROR, $err_msg);
                $this->MakeTXErrMsg($rtv, $err_msg);
                $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
                return;
            }
            //$this->m_Log->WriteLog( DEBUG, "MAKE ENCRYPT OK" );
            $this->m_Log->WriteLog(DEBUG, "MAKE ENCRYPT OK[" . $this->m_Data->m_EncBody . "]");
        }

        /* -------------------------------------------------- */
        //전문생성(Body)
        /* -------------------------------------------------- */
        $this->m_Data->MakeBody();
        $this->m_Log->WriteLog(INFO, "MAKE BODY OK");
        //$this->m_Log->WriteLog( INFO, "MAKE BODY OK[".$this->m_Data->m_sBody."]" );

        /* -------------------------------------------------- */
        //서명(sign)
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Crypto->Sign($this->m_Data->m_sBody, $sign)) != OK) {
            $err_msg = "싸인실패";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Crypto->FreeAllKey();
            return;
        }
        $this->m_Data->m_sTail = $sign;
        $this->m_Log->WriteLog(INFO, "SIGN OK");
        //$this->m_Log->WriteLog( INFO, "SIGN OK[".$sign."]" );

        /* -------------------------------------------------- */
        //전문생성(Head)
        /* -------------------------------------------------- */
        $this->m_Data->MakeHead();
        $this->m_Log->WriteLog(INFO, "MAKE HEAD OK");
        //$this->m_Log->WriteLog( INFO, "MAKE HEAD OK[".$head."]" );

        $this->m_Log->WriteLog(INFO, "MSG_TO_PG:[" . $this->m_Data->m_sMsg . "]");

        /* -------------------------------------------------- */
        //소켓생성
        /* -------------------------------------------------- */
        //DRPG 셋팅, added 07.11.15
        //취소시-PG설정 변경(도메인->IP), edited 10.09.09
        if ($this->m_type == TYPE_SECUREPAY) {
            if ($this->m_REQUEST["pgn"] == "")
                $host = $this->m_Data->m_PG1;
            else
                $host = $this->m_REQUEST["pgn"];
        }
        else {
            if ($this->m_REQUEST["pgn"] == "") {
                if ($this->m_cancelRC == 1)
                    $host = DRPG_IP;
                else
                    $host = PG_IP;
            } else
                $host = $this->m_REQUEST["pgn"];
        }

        $this->m_Socket = new INISocket($host);
        if (($rtv = $this->m_Socket->DNSLookup()) != OK) {
            $err_msg = "[" . $host . "]DNS LOOKUP 실패(MAIN)" . $this->m_Socket->getErr();
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            if ($this->m_type == TYPE_SECUREPAY) { //PI일경우, PI가 내려주는 pg1ip로!
                $this->m_Socket->ip = $this->m_Data->m_PG1IP;
            } else {
                if ($this->m_cancelRC == 1)
                    $this->m_Socket->ip = DRPG_IP;
                else
                    $this->m_Socket->ip = PG_IP;
            }
        }
        $this->m_Log->WriteLog(INFO, "DNS LOOKUP OK(" . $this->m_Socket->host . ":" . $this->m_Socket->ip . ":" . $this->m_Socket->port . ") laptime:" . $this->m_Socket->dns_laptime);
        if (($rtv = $this->m_Socket->open()) != OK) {
            $this->m_Socket->close();

            //PG2로 전환
            $err_msg = "[" . $host . "소켓연결오류(MAIN)::PG2로 전환";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            if ($this->m_type == TYPE_SECUREPAY) {
                $host = $this->m_Data->m_PG2;
            } else {
                $host = DRPG_HOST;
            }
            $this->m_Socket = new INISocket($host);
            if (($rtv = $this->m_Socket->DNSLookup()) != OK) {
                $err_msg = "[" . $host . "]DNS LOOKUP 실패(MAIN)" . $this->m_Socket->getErr();
                $this->m_Log->WriteLog(ERROR, $err_msg);
                $this->MakeTXErrMsg($rtv, $err_msg);
                if ($this->m_type == TYPE_SECUREPAY) { //PI일경우, PI가 내려주는 pg2ip로!
                    $this->m_Socket->ip = $this->m_Data->m_PG2IP;
                } else {
                    $this->m_Socket->ip = DRPG_IP;
                }
            }
            $this->m_Log->WriteLog(INFO, "DNS LOOKUP OK(" . $this->m_Socket->host . ":" . $this->m_Socket->ip . ":" . $this->m_Socket->port . ") laptime:" . $this->m_Socket->dns_laptime);
            if (($rtv = $this->m_Socket->open()) != OK) {
                $err_msg = "[" . $host . "소켓연결오류(MAIN)::" . $this->m_Socket->getErr();
                $this->m_Log->WriteLog(ERROR, $err_msg);
                $this->MakeTXErrMsg($rtv, $err_msg);
                $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
                $this->m_Socket->close();
                $this->m_Crypto->FreeAllKey();
                return;
            }
        }
        $this->m_connIP = $this->m_Socket->ip;
        $this->m_Log->WriteLog(INFO, "SOCKET CONNECT OK");

        /* -------------------------------------------------- */
        //전문송신
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Socket->send($this->m_Data->m_sMsg)) != OK) {
            $err_msg = "소켓송신오류(MAIN)::" . $this->m_Socket->getErr();
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Crypto->FreeAllKey();
            $this->m_Socket->close();
            return;
        }
        $this->m_Log->WriteLog(INFO, "SEND OK");

        /* -------------------------------------------------- */
        //전문수신
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Socket->recv($head, $body, $tail)) != OK) {
            $err_msg = "소켓수신오류(MAIN)::" . $this->m_Socket->getErr();
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Socket->close();
            $this->NetCancel();
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Crypto->FreeAllKey();
            return;
        }
        $this->m_Log->WriteLog(INFO, "RECV OK");
        $this->m_Log->WriteLog(INFO, "MSG_FROM_PG:[" . $head . $body . $tail . "]");
        $this->m_Data->m_Body = $body;

        /* -------------------------------------------------- */
        //서명확인
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Crypto->Verify($body, $tail)) != OK) {
            $err_msg = "VERIFY FAIL";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Socket->close();
            $this->NetCancel();
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Crypto->FreeAllKey();
            return;
        }
        $this->m_Log->WriteLog(INFO, "VERIFY OK");

        /* -------------------------------------------------- */
        //Head 파싱
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Data->ParseHead($head)) != OK) {
            $err_msg = "수신전문(HEAD) 파싱 오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Socket->close();
            $this->NetCancel();
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Crypto->FreeAllKey();
            return;
        }
        $this->m_Log->WriteLog(INFO, "PARSE HEAD OK");

        /* -------------------------------------------------- */
        //Body 파싱
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Data->ParseBody($body, $encrypted, $sessionkey)) != OK) {
            $err_msg = "수신전문(Body) 파싱 오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Socket->close();
            $this->NetCancel();
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Crypto->FreeAllKey();
            return;
        }
        $this->m_Log->WriteLog(INFO, "PARSE BODY OK");

        /* -------------------------------------------------- */
        //복호화
        /* -------------------------------------------------- */
        if ($this->m_type == TYPE_SECUREPAY || $this->m_type == TYPE_FORMPAY || $this->m_type == TYPE_OCBSAVE ||
                $this->m_type == TYPE_CANCEL || $this->m_type == TYPE_AUTHBILL || $this->m_type == TYPE_FORMAUTH ||
                $this->m_type == TYPE_REQREALBILL || $this->m_type == TYPE_REPAY || $this->m_type == TYPE_VACCTREPAY || $this->m_type == TYPE_RECEIPT ||
                $this->m_type == TYPE_AUTH || $this->m_type == TYPE_CAPTURE || $this->m_type == TYPE_ESCROW ||
                $this->m_type == TYPE_REFUND || $this->m_type == TYPE_INQUIRY || $this->m_type == TYPE_OPENSUB
        ) {
            if (($rtv = $this->m_Crypto->Decrypt($sessionkey, $encrypted, $decrypted)) != OK) {
                $err_msg = "복호화 실패[" . $this->GetResult(NM_RESULTMSG) . "]";
                $this->m_Log->WriteLog(ERROR, $err_msg);
                $this->MakeTXErrMsg($rtv, $err_msg);
                $this->m_Socket->close();
                $this->NetCancel();
                $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
                $this->m_Crypto->FreeAllKey();
                return;
            }
            $this->m_Log->WriteLog(INFO, "DECRYPT OK");
            $this->m_Log->WriteLog(DEBUG, "DECRYPT MSG:[" . $decrypted . "]");

            //Parse Decrypt
            $this->m_Data->ParseDecrypt($decrypted);
            $this->m_Log->WriteLog(INFO, "DECRYPT PARSE OK");
        }

        /* -------------------------------------------------- */
        //Assign Interface Variables
        /* -------------------------------------------------- */
        $this->m_RESULT = $this->m_Data->m_RESULT;

        /* -------------------------------------------------- */
        //ACK
        /* -------------------------------------------------- */
        //if( $this->GetResult(NM_RESULTCODE) == "00" && 
        if ((strcmp($this->GetResult(NM_RESULTCODE), "00") == 0) &&
                ( $this->m_type == TYPE_SECUREPAY || $this->m_type == TYPE_OCBSAVE ||
                $this->m_type == TYPE_FORMPAY || $this->m_type == TYPE_RECEIPT
                )
        ) {
            $this->m_Log->WriteLog(INFO, "WAIT ACK INVOKING");
            if (($rtv = $this->Ack()) != OK) {
                //ERROR
                $err_msg = "ACK 실패";
                $this->m_Log->WriteLog(ERROR, $err_msg);
                $this->MakeTXErrMsg($rtv, $err_msg);
                $this->m_Socket->close();
                $this->NetCancel();
                $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
                $this->m_Crypto->FreeAllKey();
                return;
            }
            $this->m_Log->WriteLog(INFO, "SUCCESS ACK INVOKING");
        }
        /* -------------------------------------------------- */
        //PG 공개키가 바뀌었으면 공개키 UPDATE
        /* -------------------------------------------------- */
        $pgpubkey = $this->m_Data->GetXMLData(NM_PGPUBKEY);
        if ($pgpubkey != "") {
            if (($rtv = $this->m_Crypto->UpdatePGPubKey($pgpubkey)) != OK) {
                $err_msg = "PG공개키 업데이트 실패";
                $this->m_Log->WriteLog(ERROR, $err_msg);
                $this->m_Data->GTHR($rtv, $err_msg);
            } else
                $this->m_Log->WriteLog(INFO, "PGPubKey UPDATED!!");
        }

        $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
        $this->m_Crypto->FreeAllKey();
        $this->m_Socket->close();

        /* -------------------------------------------------- */
        //취소실패-원거래없음시에 DRPG로 재시도
        //2008.04.01
        /* -------------------------------------------------- */
        if ($this->GetResult(NM_RESULTCODE) == "01" && ($this->m_type == TYPE_CANCEL || $this->m_type == TYPE_INQUIRY) && $this->m_cancelRC == 0) {
            if (intval($this->GetResult(NM_ERRORCODE)) > 400000 && substr($this->GetResult(NM_ERRORCODE), 3, 3) == "623") {
                $this->m_cancelRC = 1;
                $this->startAction();
            }
        }

        return;
    }

// End of StartAction

    /* -------------------------------------------------- */
    /* 																									 */
    /* 웹페이지 위변조 방지용 데이타 생성								 */
    /* 																									 */
    /* -------------------------------------------------- */

    function MakeChkFake() {
        $this->m_Crypto = new INICrypto($this->m_REQUEST);

        /* -------------------------------------------------- */
        //상점개인키 로드
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Crypto->LoadMPrivKey()) != OK) {
            $err_msg = "상점개인키 로드오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Crypto->FreePubKey();
            return;
        }
        $this->m_Log->WriteLog(INFO, "MERCHANT PRIV KEY LOAD OK");

        /* -------------------------------------------------- */
        //상점 공개키 로드(SN 를 알기위해!!)
        /* -------------------------------------------------- */
        if (($rtv = $this->m_Crypto->LoadMPubKey($m_cert_SN)) != OK) {
            $err_msg = "상점공개키 로드오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            $this->MakeTXErrMsg($rtv, $err_msg);
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            return;
        }
        $this->m_Log->WriteLog(INFO, "MERCHANT PUB KEY LOAD OK [" . $this->m_Data->m_MPubSN . "]");

        foreach ($this->m_REQUEST as $key => $val) {
            if ($key == "inipayhome" || $key == "type" || $key == "debug" ||
                    $key == "admin" || $key == "checkopt" || $key == "enctype")
                continue;
            if ($key == "mid")
                $temp1 .= $key . "=" . $val . "&"; //msg
            else
                $temp2 .= $key . "=" . $val . "&"; //hashmsg
        }
        //Make RN
        $this->m_RESULT["rn"] = $this->m_Data->MakeRN();
        $temp1 .= "rn=" . $this->m_RESULT["rn"] . "&";

        $checkMsg = $temp1;
        $checkHashMsg = $temp2;

        $retHashStr = Base64Encode(sha1($checkHashMsg, TRUE));
        $checkMsg .= "data=" . $retHashStr;

        $HashMid = Base64Encode(sha1($this->m_REQUEST["mid"], TRUE));

        $this->m_Crypto->RSAMPrivEncrypt($checkMsg, $RSATemp);
        $this->m_RESULT["encfield"] = "enc=" . $RSATemp . "&src=" . Base64Encode($checkHashMsg);
        $this->m_RESULT["certid"] = $HashMid . $m_cert_SN;

        $this->m_Log->WriteLog(INFO, "CHKFAKE KEY MAKE OK:" . $this->m_RESULT["rn"]);

        $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
        $this->m_Crypto->FreeAllKey();
        $this->m_RESULT[NM_RESULTCODE] = "00";
        return;
    }

    /* -------------------------------------------------- */
    /* 																									 */
    /* 결제처리 확인 메세지 전송												 */
    /* 																									 */
    /* -------------------------------------------------- */

    function Ack() {
        //ACK용 Data	
        $this->m_Data->m_sBody = "";
        $this->m_Data->m_sTail = "";
        $this->m_Data->m_sCmd = CMD_REQ_ACK;

        //전문생성(Head)
        $this->m_Data->MakeHead();
        $this->m_Log->WriteLog(DEBUG, "MAKE HEAD OK");
        //$this->m_Log->WriteLog( DEBUG, "MSG_TO_PG:[".$this->m_Data->m_sMsg."]" );
        //Send
        if (($rtv = $this->m_Socket->send($this->m_Data->m_sMsg)) != OK) {
            $err_msg = "ACK 전송오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            return ACK_CHECKSUM_ERR;
        }
        //$this->m_Log->WriteLog( DEBUG, "SEND OK" );

        if (($rtv = $this->m_Socket->recv($head, $body, $tail)) != OK) {
            $err_msg = "ACK 수신오류(ACK)";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            return ACK_CHECKSUM_ERR;
        }
        //$this->m_Log->WriteLog( DEBUG, "RECV OK" );
        //$this->m_Log->WriteLog( INFO, "MSG_FROM_PG:[".$recv."]" );
        return OK;
    }

    /* -------------------------------------------------- */
    /* 																									 */
    /* 망취소 메세지 전송																 */
    /* 																									 */
    /* -------------------------------------------------- */

    function NetCancel() {
        $this->m_Log->WriteLog(INFO, "WAIT NETCANCEL INVOKING");

        if ($this->m_type == TYPE_CANCEL || $this->m_type == TYPE_REPAY || $this->m_type == TYPE_VACCTREPAY || $this->m_type == TYPE_RECEIPT ||
                $this->m_type == TYPE_CONFIRM || $this->m_type == TYPE_OCBQUERY || $this->m_type == TYPE_ESCROW ||
                $this->m_type == TYPE_CAPTURE || $this->m_type == TYPE_AUTH || $this->m_type == TYPE_AUTHBILL ||
                ($this->m_type == TYPE_ESCROW && $this->m_Data->m_EscrowType == TYPE_ESCROW_DNY_CNF ) ||
                $this->m_type == TYPE_NETCANCEL
        ) {
            $this->m_Log->WriteLog(INFO, "DON'T NEED NETCANCEL");
            return true;
        }

        //NetCancel용 Data	
        $this->m_Data->m_REQUEST["cancelmsg"] = "망취소";
        $body = "";
        $sign = "";

        $this->m_Data->m_Type = TYPE_CANCEL; //망취소 전문은 취소전문과 같음.헤더만틀리고..쩝~
        //added escrow netcancel, 08.03.11
        if ($this->m_type == TYPE_ESCROW && $this->m_Data->m_EscrowType == TYPE_ESCROW_DLV)
            $this->m_Data->m_sCmd = CMD_REQ_DLV_NETC;
        else if ($this->m_type == TYPE_ESCROW && $this->m_Data->m_EscrowType == TYPE_ESCROW_CNF)
            $this->m_Data->m_sCmd = CMD_REQ_CNF_NETC;
        else if ($this->m_type == TYPE_ESCROW && $this->m_Data->m_EscrowType == TYPE_ESCROW_DNY)
            $this->m_Data->m_sCmd = CMD_REQ_DNY_NETC;
        else
            $this->m_Data->m_sCmd = CMD_REQ_NETC;

        $this->m_Data->m_sCrypto = FLAG_CRYPTO_3DES;

        //암호화
        if (($rtv = $this->m_Data->MakeEncrypt($this->m_Crypto)) != OK) {
            $err_msg = "암호화 오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            //$this->MakeTXErrMsg( $rtv, $err_msg ); 
            return;
        }
        $this->m_Log->WriteLog(DEBUG, "MAKE ENCRYPT OK[" . $this->m_Data->m_EncBody . "]");

        //전문생성(Body)
        $this->m_Data->MakeBody();
        $this->m_Log->WriteLog(INFO, "MAKE BODY OK");

        //서명(sign)
        if (($rtv = $this->m_Crypto->Sign($this->m_Data->m_sBody, $sign)) != OK) {
            $err_msg = "싸인실패";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            //$this->MakeTXErrMsg( $rtv, $err_msg ); 
            return false;
        }
        $this->m_Data->m_sTail = $sign;
        $this->m_Log->WriteLog(INFO, "SIGN OK");

        //전문생성(Head)
        $this->m_Data->MakeHead();
        $this->m_Log->WriteLog(INFO, "MAKE HEAD OK");

        $this->m_Log->WriteLog(DEBUG, "MSG_TO_PG:[" . $this->m_Data->m_sMsg . "]");

        //소켓생성
        $this->m_Socket = new INISocket("");
        $this->m_Socket->ip = $this->m_connIP; //기존연결된 IP 사용, 08.03.12
        if (($rtv = $this->m_Socket->open()) != OK) {
            $err_msg = "[" . $this->m_Socket->ip . "]소켓연결오류(NETC)::" . $this->m_Socket->getErr();
            $this->m_Log->WriteLog(ERROR, $err_msg);
            //$this->MakeTXErrMsg( $rtv, $err_msg ); 
            $this->m_Log->CloseLog($this->GetResult(NM_RESULTMSG));
            $this->m_Socket->close();
            $this->m_Crypto->FreeAllKey();
            return;
        }
        $this->m_Log->WriteLog(INFO, "SOCKET CONNECT OK::" . $this->m_Socket->ip);

        //전문송신
        if (($rtv = $this->m_Socket->send($this->m_Data->m_sMsg)) != OK) {
            $err_msg = "소켓송신오류(NETC)" . $this->m_Socket->getErr();
            $this->m_Log->WriteLog(ERROR, $err_msg);
            //$this->MakeTXErrMsg( $rtv, $err_msg ); 
            $this->m_Socket->close();
            return false;
        }
        $this->m_Log->WriteLog(INFO, "SEND OK");

        //전문수신
        if (($rtv = $this->m_Socket->recv($head, $body, $tail)) != OK) {
            $err_msg = "소켓수신오류(NETC)";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            //$this->MakeTXErrMsg( $rtv, $err_msg ); 
            $this->m_Socket->close();
            return false;
        }
        $this->m_Log->WriteLog(INFO, "RECV OK");
        $this->m_Log->WriteLog(DEBUG, "MSG_FROM_PG:[" . $head . $body . $tail . "]");

        //서명확인
        if (($rtv = $this->m_Crypto->Verify($body, $tail)) != OK) {
            $err_msg = "VERIFY FAIL";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            //$this->MakeTXErrMsg( $rtv, $err_msg ); 
            $this->m_Socket->close();
            return false;
        }
        $this->m_Log->WriteLog(INFO, "VERIFY OK");

        //이하 헤더나 본문은 파싱하지 않는다!!!!
        //그냥 여기서 끝내자 피곤하다.-_-;;
        //Head 파싱
        if (($rtv = $this->m_Data->ParseHead($head)) != OK) {
            $err_msg = "수신전문(HEAD) 파싱 오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            //$this->MakeTXErrMsg( $rtv, $err_msg ); 
            $this->m_Socket->close();
            return;
        }
        //Body 파싱
        if (($rtv = $this->m_Data->ParseBody($body, $encrypted, $sessionkey)) != OK) {
            $err_msg = "수신전문(Body) 파싱 오류";
            $this->m_Log->WriteLog(ERROR, $err_msg);
            //$this->MakeTXErrMsg( $rtv, $err_msg ); 
            $this->m_Socket->close();
            return;
        }

        //if( $this->GetResult(NM_RESULTCODE) == "00" )
        if (strcmp($this->GetResult(NM_RESULTCODE), "00") == 0)
            $this->m_Log->WriteLog(INFO, "SUCCESS NETCANCEL");
        else
            $this->m_Log->WriteLog(ERROR, "ERROR NETCANCEL[" . $this->GetResult(NM_RESULTMSG) . "]");
        return true;
    }

    function MakeIMStr($s, $t) {
        $this->m_Crypto = new INICrypto($this->m_REQUEST);
        if ($t == "H")
            return $this->m_Crypto->MakeIMStr($s, base64_decode(IMHK));
        else if ($t == "J")
            return $this->m_Crypto->MakeIMStr($s, base64_decode(IMJK));
    }

    /* -------------------------------------------------- */
    /* 																									 */
    /* 에러메세지 Make				                          */
    /* 																									 */
    /* -------------------------------------------------- */

    function MakeTXErrMsg($err_code, $err_msg) {
        $this->m_RESULT[NM_RESULTCODE] = "01";
        $this->m_RESULT[NM_RESULTERRORCODE] = $err_code;
        $this->m_RESULT[NM_RESULTMSG] = "[" . $err_code . "|" . $err_msg . "]";
        $this->m_Data->GTHR($err_code, $err_msg);
        return;
    }

}

?>
