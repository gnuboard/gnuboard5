<?php

namespace API\v1\Model\Response\Alram;

/**
 * FCM 서버로 전송하는 모델
 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages?hl=ko#Notification.FIELDS.title
 */
class FcmMessage
{

    public const ANDROID_PRIORITY_HIGH = 'high';
    public const ANDROID_PRIORITY_NORMAL = 'normal';

    /**
     * 화면 켤때 패턴, 비번등 잠겨있으면 알림 숨김
     */
    public const ANDROID_VISIBILITY_PRIVATE = 0;

    /**
     * 잠금화면의 알림 공개
     */
    public const ANDROID_VISIBILITY_PUBLIC = 1;
    /**
     * 모든 잠금화면에서 알림표시 금지
     */
    public const ANDROID_VISIBILITY_SECRET = -1;
    public const IOS_PRIORITY_HIGH = '10';

    /**
     * 사용자 정의 키 => 값 데이터입니다. 이 데이터는 앱에서 메시지를 처리하는 데 사용됩니다.
     * 공통 데이터
     * @var array
     */
//    public array $data = [
//    ];

    /**
     * @var array|string[] $notification {
     *     title: string,
     *     body: string,
     *     image: string,
     * }
     * 모든 플랫폼에서 사용할 기본 알림 템플릿입니다.
     * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#notification
     */
    public array $notification = [
        'title' => '',
        'body' => '',
        'image' => '',
    ];

    /**
     * 안드로이드 푸시 특정옵션
     * @var array $android {
     * 'collapse_key' => '',
     * 'priority' => '',
     * 'ttl' => '',
     * 'restricted_package_name' => '',
     * 'data' => [],
     * 'notification' => [
     * 'title' => '',
     * 'body' => '',
     * 'icon' => '',
     * 'color' => '',
     * 'sound' => '',
     * 'tag' => '',
     * 'click_action' => '',
     * 'body_loc_key' => '',
     * 'body_loc_args' => '',
     * 'title_loc_key' => '',
     * 'title_loc_args' => '',
     * 'channel_id' => '',
     * 'ticker' => '',
     * 'sticky' => false,
     * 'visibility' => self::ANDROID_VISIBILITY_PUBLIC,
     * 'notification_priority' => 'PRIORITY_DEFAULT',
     * 'image' => '', // 공통이 아닌 안드로이드에서만 따로 지정할 경우
     * ],
     * 'fcm_options' => [],
     * 'direct_boot_ok' => false,
     * }
     */
//    public array $android = [
//        'collapse_key' => '',
//        'priority' => self::ANDROID_PRIORITY_NORMAL,
//        'ttl' => '10s',
//        'restricted_package_name' => '',
//        'data' => [],
//        'notification' => [
//            'title' => '',
//            'body' => '',
//            'icon' => '',
//            'color' => '',
//            'sound' => '',
//            'tag' => '',
//            'click_action' => '',
//            'body_loc_key' => '',
//            'body_loc_args' => '',
//            'title_loc_key' => '',
//            'title_loc_args' => '',
//            'channel_id' => '',
//            'ticker' => '',
//            'sticky' => false,
//            'visibility' => self::ANDROID_VISIBILITY_PUBLIC,
//            'notification_priority' => 'PRIORITY_DEFAULT',
//            'image' => '', // 공통이 아닌 안드로이드에서만 따로 지정할 경우
//            // 'proxy' 
//        ],
//        'fcm_options' => [],
//        'direct_boot_ok' => false,
//    ];

    /**
     * 애플 푸시 특정옵션
     * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages?hl=ko#apns
     * @see https://developer.apple.com/documentation/usernotifications/generating-a-remote-notification
     * @var array|array[]
     */
//    public array $apns = [
//        'headers' => [], // map
//        // payload https://developer.apple.com/documentation/usernotifications/generating-a-remote-notification
//        // 애플에서 제공하는것 외에 추가적인 키값을 넣을 수 있음
//        'payload' => [], 
//        'fcm_options' => [
//            'analytics_label' => '',
//            'image' => '', // 공통 안쓰고 애플기기용 따로 지정할 경우
//        ],
//    ];

    /**
     * 웹푸시 특정옵션
     * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages?hl=ko#webpushfcmoptions
     * link 는 사용자가 알림을 클릭했을 때 열리는 링크입니다. URL 은 "HTTPS" 로 시작해야 합니다.
     * @var array $webpush
     */
//    public array $webpush = [
//        'headers' => [],
//        'data' => [],
//        'notification' => [
//            // @see 지원 속성 https://developer.mozilla.org/en-US/docs/Web/API/Notification
//        ],
//        'fcm_options' => [
//            'link' => '',
//        ],
//    ];
    public array $fcm_options = [
        'analytics_label' => ''
    ];
    /**
     * @var array ['key' => 'value']
     * 주의! key 는 token , topic, condition  셋 중 하나만 사용 가능
     */
}