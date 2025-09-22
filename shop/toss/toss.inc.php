<?php
class TossPayments {

    // 클라이언트, 시크릿 키, 상점아이디
    public string $clientKey;
    public string $secretKey;
    public string $mId;
    public string $headerSecretKey = "";

    public string $paymentUrl = "https://api.tosspayments.com/v1/payments/orders/{orderId}";
    public string $acceptUrl = "https://api.tosspayments.com/v1/payments/confirm";
    public string $cancelUrl = "https://api.tosspayments.com/v1/payments/{paymentKey}/cancel";
    public string $cashReceiptsUrl = "https://api.tosspayments.com/v1/cash-receipts";

    // 결제데이터
    public array $headers = array();
    public array $paymentData = array();
    public array $cancelData = array();
    public array $cashReceiptsData = array();
    public array $responseData = array();

    public array $bankCode = array(
        // 은행
        '02' => '한국산업은행',
        '03' => 'IBK기업은행',
        '06' => 'KB국민은행',
        '07' => 'Sh수협은행',
        '11' => 'NH농협은행',
        '12' => '단위농협(지역농축협)',
        '20' => '우리은행',
        '23' => 'SC제일은행',
        '27' => '씨티은행',
        '31' => 'iM뱅크(대구)',
        '32' => '부산은행',
        '34' => '광주은행',
        '35' => '제주은행',
        '37' => '전북은행',
        '39' => '경남은행',
        '45' => '새마을금고',
        '48' => '신협',
        '50' => '저축은행중앙회',
        '54' => '홍콩상하이은행',
        '64' => '산림조합',
        '71' => '우체국예금보험',
        '81' => '하나은행',
        '88' => '신한은행',
        '89' => '케이뱅크',
        '90' => '카카오뱅크',
        '92' => '토스뱅크',
    
        // 증권
        'S0' => '유안타증권',
        'S2' => '신한금융투자',
        'S3' => '삼성증권',
        'S4' => 'KB증권',
        'S5' => '미래에셋증권',
        'S6' => '한국투자증권',
        'S8' => '교보증권',
        'S9' => '아이엠증권',
        'SA' => '현대차증권',
        'SB' => '키움증권',
        'SD' => 'SK증권',
        'SE' => '대신증권',
        'SG' => '한화투자증권',
        'SH' => '하나금융투자',
        'SI' => 'DB금융투자',
        'SJ' => '유진투자증권',
        'SK' => '메리츠증권',
        'SM' => '부국증권',
        'SN' => '신영증권',
        'SO' => 'LIG투자증권',
        'SP' => 'KTB투자증권(다올투자증권)',
        'SQ' => '카카오페이증권',
        'SR' => '펀드온라인코리아(한국포스증권)',
        'ST' => '토스증권'
    );

    public array $cardCode = array(
        '3K' => '기업 BC',
        '46' => '광주은행',
        '71' => '롯데카드',
        '30' => '한국산업은행',
        '31' => 'BC카드',
        '51' => '삼성카드',
        '38' => '새마을금고',
        '41' => '신한카드',
        '62' => '신협',
        '36' => '씨티카드',
        '33' => '우리BC카드(BC 매입)',
        'W1' => '우리카드(우리 매입)',
        '37' => '우체국예금보험',
        '39' => '저축은행중앙회',
        '35' => '전북은행',
        '42' => '제주은행',
        '15' => '카카오뱅크',
        '3A' => '케이뱅크',
        '24' => '토스뱅크',
        '21' => '하나카드',
        '61' => '현대카드',
        '11' => 'KB국민카드',
        '91' => 'NH농협카드',
        '34' => 'Sh수협은행',
        'PCP' => '페이코',
        'KBS' => 'KB증권'
    );

    // 간편결제 제공업체 코드
    public array $easyPayCode = array(
        'TOSSPAY' => '토스페이',
        'NAVERPAY' => '네이버페이',
        'SAMSUNGPAY' => '삼성페이',
        'APPLEPAY' => '애플페이',
        'LPAY' => '엘페이',
        'KAKAOPAY' => '카카오페이',
        'PINPAY' => '핀페이',
        'PAYCO' => '페이코',
        'SSG' => 'SSG페이'
    );

    public function __construct(string $clientKey, string $secretKey, string $mId) {
        $this->clientKey = $clientKey;
        $this->secretKey = $secretKey;
        $this->mId = $mId;
    }

    /**
     * 헤더 시크릿 키 설정
     * @return void
     */
    private function setHeaderSecretKey(): void
    {
        $this->headerSecretKey = base64_encode($this->secretKey . ':');
    }
 
    /**
     * 헤더 설정
     * @return void
     */
    public function setPaymentHeader(): void
    {
        $this->setHeaderSecretKey();

        $this->headers = array(
            'Authorization: Basic ' . $this->headerSecretKey,
            'Content-Type' => 'Content-Type: application/json'
        );
    }

    /**
     * 결제 데이터 설정
     *
     * @param array $request
     * @return void
     */
    public function setPaymentData(array $request): void
    {
        $this->paymentData = array(
            'amount' => $request['amount'],
            'orderId' => $request['orderId'],
            'paymentKey' => $request['paymentKey'],
        );
    }

    /**
     * 주문번호로 결제정보 조회
     *
     * @param string $orderId
     * @return bool
     */
    public function getPaymentByOrderId(string $orderId): bool
    {
        if (empty($orderId)) {
            return false;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, str_replace('{orderId}', $orderId, $this->paymentUrl));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($curl);

        $return_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseData = json_decode($response, true);

        curl_close($curl);

        // 결제 실패 상황인 경우
        if ($return_status != 200) {
            return false;
        }

        return true;
    }

    /**
     * 결제 승인
     *
     * @return bool
     */
    public function approvePayment(): bool {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->acceptUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->paymentData));
        curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($curl);

        $return_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseData = json_decode($response, true);

        curl_close($curl);

        // 결제 실패 상황인 경우
        if ($return_status != 200 || ($this->responseData['status'] != 'DONE' && $this->responseData['status'] != 'WAITING_FOR_DEPOSIT')) {
            return false;
        }

        return true;
    }

    /**
     * 결제 취소 데이터 설정
     *
     * @param array $request
     * @return void
     */
    public function setCancelData(array $request): void
    {
        $this->cancelData = array(
            'paymentKey' => $request['paymentKey'],
            'cancelReason' => $request['cancelReason'],
        );

        // 부분취소 금액이 있는 경우
        if (isset($request['cancelAmount']) && $request['cancelAmount'] > 0) {
            $this->cancelData['cancelAmount'] = $request['cancelAmount'];
        }

        // 면세금액이 있는 경우
        if (isset($request['taxFreeAmount']) && $request['taxFreeAmount'] > 0) {
            $this->cancelData['taxFreeAmount'] = $request['taxFreeAmount'];
        }

        // 환불 계좌정보가 있는 경우 (가상계좌)
        if (isset($request['refundReceiveAccount']) && is_array($request['refundReceiveAccount'])) {
            $this->cancelData['refundReceiveAccount'] = array(
                'bank' => $request['refundReceiveAccount']['bank'],
                'accountNumber' => $request['refundReceiveAccount']['accountNumber'],
                'holderName' => $request['refundReceiveAccount']['holderName'],
            );
        }
    }

    /**
     * 결제 취소
     *
     * @return bool
     */
    public function cancelPayment(): bool
    {
        // 취소에 필요한 결제 키가 있는지 여부
        if (empty($this->cancelData['paymentKey'])) {
            return false;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, str_replace('{paymentKey}', $this->cancelData['paymentKey'], $this->cancelUrl));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->cancelData));
        curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($curl);

        $return_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseData = json_decode($response, true);

        curl_close($curl);

        // 결제 실패 상황인 경우
        if ($return_status != 200) {
            return false;
        }

        return true;
    }

    /**
     * 현금영수증 발급 데이터 설정
     */
    public function setCashReceiptsData(array $request): void
    {
        $this->cashReceiptsData = array(
            'amount' => $request['amount'],
            'orderId' => $request['orderId'],
            'type' => $request['type'],
            'customerIdentityNumber' => $request['customerIdentityNumber'],
            'orderName' => $request['orderName'],
        );
    }

    /**
     * 현금영수증 발급
     */
    public function issueCashReceipt(): bool
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->cashReceiptsUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->cashReceiptsData));
        curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

        $response = curl_exec($curl);

        $return_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseData = json_decode($response, true);

        curl_close($curl);

        // 결제 실패 상황인 경우
        if ($return_status != 200) {
            return false;
        }

        return true;
    }

    /**
     * 현금영수증 발급 취소
     */
    public function cancelCashReceipt($receiptKey): bool
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->cashReceiptsUrl."/".$receiptKey."/cancel");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

        $response = curl_exec($curl);

        $return_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseData = json_decode($response, true);

        curl_close($curl);

        // 결제 실패 상황인 경우
        if ($return_status != 200) {
            return false;
        }

        return true;
    }
}