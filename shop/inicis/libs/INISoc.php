<?php

/**
 * Copyright (C) 2007 INICIS Inc.
 *
 * 해당 라이브러리는 절대 수정되어서는 안됩니다.
 * 임의로 수정된 코드에 대한 책임은 전적으로 수정자에게 있음을 알려드립니다.
 *
 */
class INISocket {

    var $hnd;
    var $host;
    var $ip;
    var $port;
    var $type;
    var $family;
    var $protocol;
    var $bConnected;
    var $sBuffer;
    var $sSocErr;
    var $dns_laptime;

    function error($msg = null) {
        $errCode = socket_last_error($this->hnd);
        if ($errCode != 0) {
            //Connection reset by peer
            if ($errCode == 104)
                $this->bConnected = false;
            $errMsg = socket_strerror($errCode);
            $this->sSocErr = "(" . $errCode . ")(" . $errMsg . ")";
            socket_clear_error($this->hnd);
        }
        elseif (strlen($msg)) {
            $this->sSocErr = $errMsg;
        }
        return false;
    }

    function __construct($host) {
        $this->family = AF_INET;
        $this->type = SOCK_STREAM;
        $this->protocol = SOL_TCP;
        $this->hnd = @socket_create($this->family, $this->type, $this->protocol);
        $this->error();
        $this->sBuffer = false;
        $this->ip = null;
        $this->host = $host;
        $this->port = PG_PORT;
    }

    function DNSLookUP() {
        $starttime = GetMicroTime();
        $ip = @gethostbyname($this->host);
        if ($ip == PG_IP || $ip == KSPG_IP) {
            $this->ip = $ip;
        } else {
            $this->error("Hostname " . $this->host . " could not be resolved");
            return DNS_LOOKUP_ERR;
        }

        $this->dns_laptime = round(GetMicroTime() - $starttime, 3);
        if ($this->dns_laptime > DNS_LOOKUP_TIMEOUT)
            return DNS_LOOKUP_TIMEOUT_ERR;

        return OK;
    }

    function open() {
        //Connect timeout Trickkkkkkkkk ##2. NONBLOCKING NEED , less CPU clocking!!^^ 
        //modified by ddaemiri, 2007.08.30

        socket_set_nonblock($this->hnd);
        if (!@socket_connect($this->hnd, $this->ip, $this->port)) {
            $err = socket_last_error($this->hnd);
            $err_str = socket_strerror($err);
            if ($err == 106) { //EISCONN
                $this->bConnected = true;
                socket_set_block($this->hnd);
                return OK;
            }
            //EINPROGRESS( Linux:115, Window Socket:10035, FreeBSD4.10:36, 모든 OS 체크 불가능해서 str으로도 검색 )
            if ($err != ERRCODE_INPROGRESS_LINUX && $err != ERRCODE_INPROGRESS_WIN &&
                    $err != ERRCODE_INPROGRESS_FREEBSD && $err_str != ERRSTR_INPROGRESS) {
                $this->error();
                socket_close($this->hnd);
                return SOCK_CONN_ERR;
            }
        }

        $read = array($this->hnd);
        $write = array($this->hnd);
        $except = NULL;
        $rtv = @socket_select($read, $write, $except, TIMEOUT_CONNECT);
        if ($rtv == 0) { //TIMEOUT
            $this->error();
            socket_close($this->hnd);
            return SOCK_TIMEO_ERR;
        } else if ($rtv === FALSE) {
            $this->error();
            socket_close($this->hnd);
            return SOCK_ETC1_ERR;
        }
        if (in_array($this->hnd, $read) || in_array($this->hnd, $write)) {
            if (@socket_get_option($this->hnd, SOL_SOCKET, SO_ERROR) === FALSE) {
                $this->error();
                socket_close($this->hnd);
                return SOCK_ETC2_ERR;
            }
        }
        $this->bConnected = true;
        socket_set_block($this->hnd);
        return OK;
    }

    function close() {
        //if(!$this->bConnected) return;
        @socket_shutdown($this->hnd, 2);
        @socket_close($this->hnd);
    }

    function send($sBuf) {
        if (!$this->bConnected) {
            $this->error("Socket error. Cannot send data on a closed socket.");
            return SOCK_SEND1_ERR;
        }

        $vWrite = array($this->hnd);
        $vRead = null;
        $vExcept = null;

        while (($rtv = @socket_select($vRead, $vWrite, $vExcept, TIMEOUT_WRITE)) === FALSE);

        if ($rtv == 0) {
            $this->error();
            //return SOCK_TIMEO_ERR;
            return SOCK_CONN_ERR; //modify 2008.04.01
        }
        if ($rtv === FALSE) {
            $this->error();
            return SOCK_SEND2_ERR;
        }

        $tmpBuf = strlen($sBuf) ? $sBuf : $this->sBuffer;
        $iBufLen = strlen($tmpBuf);
        $res = @socket_send($this->hnd, $tmpBuf, $iBufLen, 0);

        if ($res === FALSE) {
            $this->error();
            return SOCK_SEND2_ERR;
        } elseif ($res < $iBufLen) {
            $tmpBuf = substr($tmpBuf, $res);
            $this->send($tmpBuf);
        }
        return OK;
    }

    function WaitRecv(&$recv_buf, $nleft) {
        $recv_buf = null;
        $read = array($this->hnd);
        $buf = null;
        while ($nleft > 0) {
            $write = null;
            $except = null;

            $rtv = @socket_select($read, $write, $except, TIMEOUT_READ);
            if ($rtv == 0) {
                $this->error();
                return SOCK_TIMEO_ERR;
            } else if ($rtv === FALSE) {
                $this->error();
                return SOCK_ETC1_ERR;
            }

            if (!in_array($this->hnd, $read)) {
                $this->error();
                return SOCK_RECV1_ERR;
            }
            if ($buf = @socket_read($this->hnd, $nleft)) {
                $recv_buf .= $buf;
            } else {
                $this->error();
                return SOCK_RECV1_ERR;
            }
            $nleft -= strlen($buf);
        }
        return OK;
    }

    function recv(&$head, &$body, &$tail) {
        if (!$this->bConnected) {
            $this->error("Socket error. Cannot read any data on a closed socket.");
            return SOCK_RECV1_ERR;
        }

        //------------------------------------------------------
        //head
        //------------------------------------------------------
        if (($rtv = $this->WaitRecv($head, MSGHEADER_LEN)) != OK)
            return $rtv;

        if ($head == "")
            return SOCK_RECV2_ERR;
        $body_len = intval(substr($head, 0, BODY_LEN));
        $tail_len = intval(substr($head, BODY_LEN, TAIL_LEN));

        //------------------------------------------------------
        //body
        //------------------------------------------------------
        if (($rtv = $this->WaitRecv($body, $body_len)) != OK)
            return $rtv;

        //------------------------------------------------------
        //tail
        //------------------------------------------------------
        if (($rtv = $this->WaitRecv($tail, $tail_len)) != OK)
            return $rtv;

        return OK;
    }

    function getErr() {
        return $this->sSocErr;
    }

}