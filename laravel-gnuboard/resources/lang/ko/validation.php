<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute을(를) 동의하셔야 합니다.',
    'active_url' => ':attribute은(는) 유효한 URL이 아닙니다.',
    'after' => ':attribute은(는) :date 이후 날짜여야 합니다.',
    'after_or_equal' => ':attribute은(는) :date 이후 날짜이거나 같은 날짜여야 합니다.',
    'alpha' => ':attribute은(는) 문자만 포함할 수 있습니다.',
    'alpha_dash' => ':attribute은(는) 문자, 숫자, 대시(-), 밑줄(_)만 포함할 수 있습니다.',
    'alpha_num' => ':attribute은(는) 문자와 숫자만 포함할 수 있습니다.',
    'array' => ':attribute은(는) 배열이어야 합니다.',
    'before' => ':attribute은(는) :date 이전 날짜여야 합니다.',
    'before_or_equal' => ':attribute은(는) :date 이전 날짜이거나 같은 날짜여야 합니다.',
    'between' => [
        'numeric' => ':attribute은(는) :min에서 :max 사이여야 합니다.',
        'file' => ':attribute은(는) :min에서 :max 킬로바이트 사이여야 합니다.',
        'string' => ':attribute은(는) :min에서 :max 문자 사이여야 합니다.',
        'array' => ':attribute은(는) :min에서 :max 개의 항목이 있어야 합니다.',
    ],
    'boolean' => ':attribute은(는) true 또는 false 이어야 합니다.',
    'confirmed' => ':attribute 확인 항목이 일치하지 않습니다.',
    'date' => ':attribute은(는) 유효한 날짜가 아닙니다.',
    'date_equals' => ':attribute은(는) :date와 같은 날짜여야 합니다.',
    'date_format' => ':attribute은(는) :format 형식과 일치하지 않습니다.',
    'different' => ':attribute과(와) :other은(는) 달라야 합니다.',
    'digits' => ':attribute은(는) :digits 자리 숫자여야 합니다.',
    'digits_between' => ':attribute은(는) :min에서 :max 자리 사이여야 합니다.',
    'dimensions' => ':attribute은(는) 유효하지 않은 이미지 크기입니다.',
    'distinct' => ':attribute 필드에 중복된 값이 있습니다.',
    'email' => ':attribute은(는) 유효한 이메일 주소여야 합니다.',
    'ends_with' => ':attribute은(는) 다음 중 하나로 끝나야 합니다: :values.',
    'exists' => '선택된 :attribute은(는) 유효하지 않습니다.',
    'file' => ':attribute은(는) 파일이어야 합니다.',
    'filled' => ':attribute 필드는 값이 있어야 합니다.',
    'gt' => [
        'numeric' => ':attribute은(는) :value보다 커야 합니다.',
        'file' => ':attribute은(는) :value 킬로바이트보다 커야 합니다.',
        'string' => ':attribute은(는) :value 문자보다 많아야 합니다.',
        'array' => ':attribute은(는) :value 개보다 많은 항목이 있어야 합니다.',
    ],
    'gte' => [
        'numeric' => ':attribute은(는) :value보다 크거나 같아야 합니다.',
        'file' => ':attribute은(는) :value 킬로바이트보다 크거나 같아야 합니다.',
        'string' => ':attribute은(는) :value 문자보다 많거나 같아야 합니다.',
        'array' => ':attribute은(는) :value 개 이상의 항목이 있어야 합니다.',
    ],
    'image' => ':attribute은(는) 이미지여야 합니다.',
    'in' => '선택된 :attribute은(는) 유효하지 않습니다.',
    'in_array' => ':attribute 필드는 :other에 존재하지 않습니다.',
    'integer' => ':attribute은(는) 정수여야 합니다.',
    'ip' => ':attribute은(는) 유효한 IP 주소여야 합니다.',
    'ipv4' => ':attribute은(는) 유효한 IPv4 주소여야 합니다.',
    'ipv6' => ':attribute은(는) 유효한 IPv6 주소여야 합니다.',
    'json' => ':attribute은(는) JSON 문자열이어야 합니다.',
    'lt' => [
        'numeric' => ':attribute은(는) :value보다 작아야 합니다.',
        'file' => ':attribute은(는) :value 킬로바이트보다 작아야 합니다.',
        'string' => ':attribute은(는) :value 문자보다 적어야 합니다.',
        'array' => ':attribute은(는) :value 개보다 적은 항목이 있어야 합니다.',
    ],
    'lte' => [
        'numeric' => ':attribute은(는) :value보다 작거나 같아야 합니다.',
        'file' => ':attribute은(는) :value 킬로바이트보다 작거나 같아야 합니다.',
        'string' => ':attribute은(는) :value 문자보다 적거나 같아야 합니다.',
        'array' => ':attribute은(는) :value 개 이하의 항목이 있어야 합니다.',
    ],
    'max' => [
        'numeric' => ':attribute은(는) :max보다 클 수 없습니다.',
        'file' => ':attribute은(는) :max 킬로바이트보다 클 수 없습니다.',
        'string' => ':attribute은(는) :max 문자보다 많을 수 없습니다.',
        'array' => ':attribute은(는) :max 개보다 많을 수 없습니다.',
    ],
    'mimes' => ':attribute은(는) 다음 타입의 파일이어야 합니다: :values.',
    'mimetypes' => ':attribute은(는) 다음 타입의 파일이어야 합니다: :values.',
    'min' => [
        'numeric' => ':attribute은(는) 최소 :min이어야 합니다.',
        'file' => ':attribute은(는) 최소 :min 킬로바이트여야 합니다.',
        'string' => ':attribute은(는) 최소 :min 문자 이상이어야 합니다.',
        'array' => ':attribute은(는) 최소 :min 개의 항목이 있어야 합니다.',
    ],
    'not_in' => '선택된 :attribute은(는) 유효하지 않습니다.',
    'not_regex' => ':attribute 형식이 유효하지 않습니다.',
    'numeric' => ':attribute은(는) 숫자여야 합니다.',
    'password' => '비밀번호가 올바르지 않습니다.',
    'present' => ':attribute 필드가 있어야 합니다.',
    'regex' => ':attribute 형식이 유효하지 않습니다.',
    'required' => ':attribute 필드는 필수입니다.',
    'required_if' => ':other이(가) :value 일때 :attribute 필드는 필수입니다.',
    'required_unless' => ':other이(가) :values에 없으면 :attribute 필드는 필수입니다.',
    'required_with' => ':values이(가) 있으면 :attribute 필드는 필수입니다.',
    'required_with_all' => ':values이(가) 모두 있으면 :attribute 필드는 필수입니다.',
    'required_without' => ':values이(가) 없으면 :attribute 필드는 필수입니다.',
    'required_without_all' => ':values이(가) 모두 없으면 :attribute 필드는 필수입니다.',
    'same' => ':attribute과(와) :other은(는) 일치해야 합니다.',
    'size' => [
        'numeric' => ':attribute은(는) :size이어야 합니다.',
        'file' => ':attribute은(는) :size 킬로바이트여야 합니다.',
        'string' => ':attribute은(는) :size 문자여야 합니다.',
        'array' => ':attribute은(는) :size 개의 항목을 포함해야 합니다.',
    ],
    'starts_with' => ':attribute은(는) 다음 중 하나로 시작해야 합니다: :values.',
    'string' => ':attribute은(는) 문자열이어야 합니다.',
    'timezone' => ':attribute은(는) 유효한 시간대여야 합니다.',
    'unique' => ':attribute은(는) 이미 사용중입니다.',
    'uploaded' => ':attribute 업로드를 실패했습니다.',
    'url' => ':attribute 형식이 유효하지 않습니다.',
    'uuid' => ':attribute은(는) 유효한 UUID여야 합니다.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'mb_id' => [
            'unique' => '이미 사용중인 아이디입니다.',
            'regex' => '아이디는 영문자, 숫자, _ 만 사용 가능합니다.',
            'min' => '아이디는 최소 :min자 이상 입력하세요.',
        ],
        'mb_password' => [
            'min' => '비밀번호는 최소 :min자 이상 입력하세요.',
            'confirmed' => '비밀번호 확인이 일치하지 않습니다.',
        ],
        'mb_email' => [
            'unique' => '이미 사용중인 이메일입니다.',
            'email' => '올바른 이메일 주소를 입력해주세요.',
        ],
        'mb_nick' => [
            'unique' => '이미 사용중인 닉네임입니다.',
        ],
        'agree' => [
            'accepted' => '회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.',
        ],
        'agree2' => [
            'accepted' => '개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'mb_id' => '아이디',
        'mb_password' => '비밀번호',
        'mb_password_confirmation' => '비밀번호 확인',
        'mb_name' => '이름',
        'mb_nick' => '닉네임',
        'mb_email' => '이메일',
        'mb_hp' => '휴대폰번호',
        'mb_zip1' => '우편번호',
        'mb_zip2' => '우편번호',
        'mb_addr1' => '기본주소',
        'mb_addr2' => '상세주소',
        'mb_addr3' => '참고항목',
        'mb_signature' => '서명',
        'mb_profile' => '자기소개',
        'mb_mailling' => '메일링서비스',
        'mb_sms' => 'SMS',
        'mb_open' => '정보공개',
        'agree' => '회원가입약관',
        'agree2' => '개인정보처리방침',
    ],
];