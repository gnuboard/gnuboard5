<?php

	$configPath = "C:/lgdacom"; //LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf") 위치 지정. 

    /*
     * [LG유플러스 연결 확인 페이지]
     *
     * 이페이지는 LG유플러스과의 연결을 테스트 하는 페이지 입니다.(수정하지 마세요.)
     */
    $CST_PLATFORM   = $HTTP_POST_VARS["CST_PLATFORM"];
    $CST_MID        = $HTTP_POST_VARS["CST_MID"]; // 't'가 추가되지 않은 가입요청시 아이디를 입력바랍니다.
    $LGD_MID        = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;
    
    if( $CST_PLATFORM == null || $CST_PLATFORM == "" ) {
        echo "[TX_PING error] 파라미터 누락<br>";
        return;
    }
    if( $LGD_MID == null || $LGD_MID == "" ) {
        echo "[TX_PING error] 파라미터 누락<br>";
        return;
    }

    require_once("./XPayClient.php");
    $xpay = &new XPayClient($configPath, $CST_PLATFORM);
    $xpay->Init_TX($LGD_MID);

    $xpay->Set("LGD_TXNAME", "Ping");
    $xpay->Set("LGD_RESULTCNT", "3");

    if ($xpay->TX()) {
        echo "response code = " . $xpay->Response_Code() . "<br>";
        echo "response msg = " . $xpay->Response_Msg() . "<br>";
        echo "response count = " . $xpay->Response_Count() . "<p>";

        $keys = $xpay->Response_Names();
        for ($i = 0; $i < $xpay->Response_Count(); $i++) {
            echo "count = " . $i . "<br>";
            foreach($keys as $name) {
                echo $name . " = " . $xpay->Response($name, $i) . "<br>";
            }
        }
    }
    else {
        echo "[TX_PING error] <br>";
        echo "response code = " . $xpay->Response_Code() . "<br>";
        echo "response msg = " . $xpay->Response_Msg() . "<p>";
    }