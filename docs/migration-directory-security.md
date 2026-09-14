# 마이그레이션 폴더 접근 차단

`migrations/`는 DB 업그레이드에 필요한 SQL 파일을 보관한다. 업그레이드 완료 후에도 적용 상태와 체크섬 확인, 후속 업데이트를 위해 폴더와 파일을 유지한다. HTTP 접근만 차단하고, 서버 내부에서 PHP가 파일을 읽을 수 있도록 한다.

## Apache 2.4 및 공유호스팅

배포본의 `migrations/.htaccess`에는 다음 설정이 포함된다.

```apache
Require all denied
```

FTP·파일 관리자로 업로드할 때 숨김 파일인 `.htaccess`도 포함해야 한다. 기존에 해당 파일을 수정했다면 설정을 병합한다. 이 규칙은 폴더와 개별 SQL 파일의 HTTP 접근을 차단하며 관리자 DB 업그레이드의 파일 읽기에는 영향을 주지 않는다.

호스팅에서 `.htaccess`와 인증·접근 제어 지시문을 허용해야 적용된다. `AllowOverride None`이면 무시되며, 지시문 사용이 제한된 환경에서는 HTTP 500이 발생할 수 있다. 차단 여부를 아래 방법으로 확인하고, 적용되지 않으면 호스팅 업체에 해당 폴더와 하위 파일의 HTTP 접근 차단을 요청한다. 웹서버 관리자는 `AllowOverride AuthConfig` 또는 필요한 지시문을 허용하는 설정을 검토할 수 있다. 자세한 조건은 [Apache 공식 안내](https://httpd.apache.org/docs/2.4/howto/htaccess.html)를 참고한다.

## Nginx

Nginx는 `.htaccess`를 적용하지 않는다. 사이트를 서비스하는 `server` 블록에 다음 설정을 추가한다. 도메인 루트에 그누보드를 설치한 경우의 예시다.

```nginx
location = /migrations {
    return 404;
}

location ^~ /migrations/ {
    return 404;
}
```

하위 경로에 설치했다면 실제 공개 URL에 맞춰 두 경로를 모두 바꾼다. 예를 들어 `/gnuboard5-dev/`에 설치한 경우 다음과 같다.

```nginx
location = /gnuboard5-dev/migrations {
    return 404;
}

location ^~ /gnuboard5-dev/migrations/ {
    return 404;
}
```

`^~`는 해당 접두어가 선택되었을 때 일반 정규식 location보다 우선하도록 한다. 기존의 더 구체적인 location, alias, 별도 호스트나 프록시 경로로 같은 파일을 제공한다면 해당 경로도 함께 차단한다. [Nginx location 공식 문서](https://nginx.org/en/docs/http/ngx_http_core_module.html#location)를 참고한다.

서버 관리자는 설정 파일을 저장한 뒤 문법 검사를 통과한 경우에만 반영한다.

```bash
sudo nginx -t
sudo systemctl reload nginx
```

공유호스팅에서 Nginx 설정 권한이 없다면 위 설치 경로와 설정 예시를 호스팅 업체에 전달한다. Apache 앞에서 Nginx가 정적 파일을 직접 제공하는 구성도 Nginx 측 차단이 필요하다. 이 작업을 위해 SQL 확장자나 파일명을 임의로 변경하지 않는다.

## 적용 확인

사이트 주소와 설치 경로를 바꿔 확인한다. 마지막 URL은 실제 존재하는 SQL 파일이어야 한다.

```bash
curl -i 'https://example.com/migrations'
curl -i 'https://example.com/migrations/'
curl -i 'https://example.com/migrations/20260910_001_kcp_notification.sql'
```

- 폴더와 개별 파일 요청 모두 HTTP 403 또는 404가 반환되고 SQL 본문이 노출되지 않아야 한다. 폴더 목록만 숨기는 `index.html`이나 디렉터리 목록 비활성화만으로는 개별 파일 다운로드를 막을 수 없다.
- 사이트 전체 인증으로 HTTP 401이 반환되는 것만으로 폴더 전용 차단을 확인할 수는 없다. 사이트 인증을 통과한 요청에서도 SQL 파일이 차단되는지 확인한다.
- HTTP 500은 정상 차단으로 간주하지 않는다. 호스팅의 허용 지시문과 오류 로그를 확인한다.
- 관리자 **환경설정 → DB업그레이드**에서 기존 마이그레이션 목록과 상태가 정상적으로 조회되는지 확인한다. 검증 목적으로 마이그레이션을 재실행할 필요는 없다.

PHP 실행 계정에는 가능한 한 읽기 권한만 부여하고 파일 변경은 배포 계정으로 관리한다. 두 계정이 같은 공유호스팅에서는 계정별 권한 분리가 어려울 수 있으므로 `chmod` 값만으로 변조 방지가 보장된다고 안내하지 않는다. 폴더를 쓰기 가능하게 만들거나 `777`로 설정하지 않는다. DB 백업·실제 비밀번호·개인정보가 포함된 파일은 이 폴더에 보관하지 않는다.
