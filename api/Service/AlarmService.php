<?php

namespace API\Service;

use API\Database\Db;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Exception\GuzzleException;

class AlarmService
{

    private $google_service_credentials;
    private string $project_id;
    private $fcm_token_table;

    private array $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    public function __construct()
    {
        $this->project_id = $_ENV['FIREBASE_PROJECT_ID'] ?? '';

        $this->fcm_token_table = $GLOBALS['g5']['fcm_token_table'] ?? G5_TABLE_PREFIX . 'fcm_token';
        if (($GLOBALS['g5']['fcm_token_table'] ?? '') === '') {
            $this->createFcmTokenTable();
        }
    }

    public function setGoogleServiceCredentials()
    {
        $this->google_service_credentials = new ServiceAccountCredentials($this->scopes, $_ENV['FIREBASE_KEY_PATH'] ?? G5_DATA_PATH . '/fcm.json');
    }

    /**
     * @return string|false
     */
    public function getAuthToken()
    {
        if (!$this->google_service_credentials) {
            $this->setGoogleServiceCredentials();
            // 실패 확인
            if (!$this->google_service_credentials) {
                return false;
            }
        }

        $fetch_result = $this->google_service_credentials->fetchAuthToken();
        return $fetch_result['access_token'] ?? false;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function sendMessage($data)
    {
        $auth_token = $this->getAuthToken();
        if (!$auth_token) {
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";
        $headers = [
            'Authorization' => 'Bearer ' . $auth_token,
            'Content-Type' => 'application/json;',
        ];
        $client = new \GuzzleHttp\Client();
        try {
            $client->request('POST', $url, [
                'headers' => $headers,
                'json' => $data,
            ]);
        } catch (GuzzleException $e) {
            error_log('fcm send fail: ' . $e->getMessage() . ' data' . json_encode($data));
            return false;
        }

        return true;
    }


    /**
     *
     * @param array $target_data [topic => value], [token => value], [condition => value]
     * @param string $title
     * @param string $body
     * @param ?string $image
     * @return array
     */
    public function createMessage($target_data, $title, $body, $image = null)
    {
        $notification = [
            'title' => $title,
            'body' => $body
        ];

        if ($image) {
            $notification['image'] = $image;
        }

        $data = [
            'message' => [
                'notification' => $notification
            ]
        ];

        list($target, $target_value) = ($target_data);
        switch ($target) {
            case 'token':
                $data['message']['token'] = $target_value;
                break;
            case 'topic':
                $data['message']['topic'] = $target_value;
                break;
            case 'condition':
                $data['message']['condition'] = $target_value;
                break;
            default:
                throw new \InvalidArgumentException('Invalid type: ' . print_r($target_data, true));
        }

        return $data;
    }

    /**
     * @param $target
     * @param $data
     * @return array
     */
    public function addData($target, $data)
    {
        $target['message']['data'] = $data;
        return $target;
    }


    /**
     * @param string $mb_id
     * @param string $token
     * @param string $platform
     * @param string $ip
     * @return void
     */
    public function registerFcmToken($mb_id, $token, $platform, $ip)
    {
        $fcm_token_table = $this->fcm_token_table;
        $query = "INSERT INTO $fcm_token_table SET
            mb_id = ?,
            ft_token = ?, 
            ft_platform = ?, 
            ft_created_at = NOW(),
            ft_expired_at = DATE_ADD(NOW(), INTERVAL 270 DAY), 
            ft_last_access_at = NOW(), 
            ft_ip = ? ON DUPLICATE KEY UPDATE ft_last_access_at = NOW(), ft_ip = ?";
        Db::getInstance()->run($query, [$mb_id, $token, $platform, $ip, $ip]);
    }

    /**
     * 접속 안한지 270 일 후 만료되는 토큰 삭제 하므로 270일을 업데이트
     * @see https://firebase.google.com/docs/cloud-messaging/manage-tokens
     * @return void
     */
    public function updateExpiresFcmToken($token)
    {
        $fcm_token_table = $this->fcm_token_table;
        $query = "UPDATE $fcm_token_table SET ft_expired_at = DATE_ADD(NOW(), INTERVAL 270 DAY) WHERE ft_token = ?";
        Db::getInstance()->run($query, [$token]);
    }

    /**
     * 접속 안한지 270 일 후 만료되는 토큰 삭제
     * @see https://firebase.google.com/docs/cloud-messaging/manage-tokens
     * @return void
     */
    public function deleteExpriesFcmToken()
    {
        $fcm_token_table = $this->fcm_token_table;
        $query = "DELETE FROM $fcm_token_table WHERE ft_expired_at < NOW()";
        Db::getInstance()->run($query);
    }

    public function deleteFcmToken($token)
    {
        $fcm_token_table = $this->fcm_token_table;
        Db::getInstance()->deleteById($fcm_token_table, 'ft_token', $token);
    }

    public function createFcmTokenTable()
    {
        $fcm_token_table = $this->fcm_token_table;
        if (!table_exist_check($fcm_token_table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$fcm_token_table` (
                    `ft_no` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `mb_id` varchar(20) NULL,
                    `ft_token` varchar(328) NOT NULL,
                    `ft_platform` ENUM('web', 'ios', 'android') NOT NULL,
                    `ft_meta` varchar(255) NULL,
                    `ft_created_at` datetime NOT NULL,
                    `ft_expired_at` datetime NULL,
                    `ft_last_access_at` datetime NULL,
                    `ft_ip` varchar(45) NULL,
                    PRIMARY KEY (`ft_no`),
                    UNIQUE INDEX `ft_token` (`ft_token`) USING BTREE,
                    KEY `ix_fcm_token_mb_id` (`mb_id`),
                    KEY `ix_fcm_token_primary_key` (`ft_no`)
                    ) AUTO_INCREMENT=1";
            Db::getInstance()->run($query);
        }
    }

    public function fetchFcmToken($mb_id)
    {
        $fcm_token_table = $this->fcm_token_table;
        $query = "SELECT ft_token FROM $fcm_token_table WHERE mb_id = ?";
        $stmt = Db::getInstance()->run($query, [$mb_id]);
        return $stmt->fetchAll();
    }

}