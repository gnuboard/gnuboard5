# 그누보드5

그누보드5는 PHP와 MySQL 또는 MariaDB 기반의 오픈소스 CMS입니다. 게시판과 회원 관리 기능을 제공하며 쇼핑몰 솔루션 영카트를 포함합니다.

현재 소스 코드의 버전은 [`version.php`](version.php)에서 확인할 수 있습니다.

## 요구사항

- PHP 7.0 이상 (PHP 8.3 이상 권장)
- MySQL 5.0 이상 또는 이와 호환되는 MariaDB
- Linux 호환 운영체제

### PHP 확장

| 구분 | 확장 | 용도 |
|---|---|---|
| 필수 | mysqli | DB 연결 |
| 필수 | JSON | 설치, 관리자 화면, 외부 연동 응답 처리 |
| 필수 | iconv | 회원가입, 문자 발송 등의 문자열 인코딩 변환 |
| 필수 | GD | 자동등록방지 문자(기본 캡차), 썸네일 |
| 권장 | mbstring | 다국어 문자열 처리, 메일, 문자 발송 |
| 권장 | OpenSSL | 보안 통신, 소셜 로그인, KCP REST API, 현금영수증 |
| 권장 | cURL | 결제(PG) 연동, 소셜 로그인, reCAPTCHA 등 외부 API |
| 선택 | SOAP | 영카트 KCP 모바일 결제 |
| 선택 | zip | 회원 목록 엑셀 내보내기, 엑셀 파일 업로드 |
| 선택 | exif | 사진 썸네일의 회전 방향 보정 |
| 선택 | SimpleXML | 영카트 구글 쇼핑 상품 피드 |

> SIR에서 제작한 그누보드5와 호환 프로그램은 Linux에서 개발하고 테스트했습니다. 다른 운영체제에서의 동작은 보장하지 않습니다.

5.6.44 이하 버전은 PHP 5.2.17 이상에서 동작합니다. 5.6.45부터는 PHP 7.0 이상이 필요합니다.

## 설치

### 1. 내려받기

다음 중 한 가지 방법으로 소스를 준비합니다.

- **GitHub Releases:** [Releases](https://github.com/gnuboard/gnuboard5/releases)에서 원하는 버전의 `gnuboard{버전}.zip` 또는 `gnuboard{버전}.tar.gz`를 내려받습니다. 압축을 풀면 상위 폴더 없이 바로 파일이 나옵니다.
  - 자동으로 생성되는 `Source code (zip)`, `Source code (tar.gz)`는 상위 폴더가 포함되어 있습니다.
  - `*.patch.zip`은 기존 설치본 업데이트용이므로 신규 설치에는 사용하지 않습니다.
- **SIR:** [그누보드5 다운로드](https://sir.kr/boards/g5_pds)에서 원하는 버전의 배포본을 내려받습니다.
- **Git:** Git을 사용할 수 있는 서버에서는 저장소를 복제합니다.

  ```bash
  git clone https://github.com/gnuboard/gnuboard5.git
  ```

내려받은 파일을 웹 서버의 서비스 경로에 배치합니다. 압축파일은 풀어서 FTP, SFTP 등으로 업로드합니다.

### 2. 설치 진행

1. 브라우저에서 소스를 배치한 주소로 접속해 설치 화면을 엽니다.
2. `data` 디렉터리를 만들라는 안내가 나오면, `config.php`가 있는 디렉터리에 `data`를 만들고 웹 서버가 쓸 수 있도록 권한을 설정합니다. 공식 매뉴얼은 `707` 권한을 예시로 사용합니다.
3. 서버 환경 점검 결과와 라이선스를 확인하고 설치를 계속합니다.
4. MySQL 또는 MariaDB 접속 정보와 최고관리자 정보를 입력합니다.
5. 설치가 끝나면 최고관리자 계정으로 로그인합니다.
6. `config.php`의 `G5_DOMAIN`에 사이트의 실제 주소를 입력합니다(예: `https://www.example.com`, 하위 경로에 설치했다면 경로까지 포함). 비어 있으면 비밀번호 찾기와 메일 인증 같은 보안 메일이 발송되지 않습니다. 자세한 내용은 [보안 업데이트 안내](docs/security-release-draft.md)를 확인하십시오.

> 기존 사이트에 다시 설치하면 데이터가 삭제될 수 있습니다. 진행하기 전에 파일과 데이터베이스를 백업하십시오.

## 업데이트

1. 파일과 데이터베이스를 백업합니다.
2. 새 버전의 파일을 덮어씁니다. 바로 이전 버전을 사용 중이면 해당 릴리스의 `*.patch.zip`을 적용할 수 있습니다. `data` 디렉터리와 DB 설정 파일은 보존합니다.
3. 건너뛴 버전을 포함해 각 버전의 안내 문서(`docs/release-<버전>.md` 또는 [GitHub Releases](https://github.com/gnuboard/gnuboard5/releases))에서 삭제 대상 파일과 필요한 조치를 확인합니다.
4. DB 변경이 포함된 버전이면 관리자 메뉴 **환경설정 → DB업그레이드**에서 변경 내용을 확인하고 실행합니다.

## 문서와 지원

- [그누보드5 공식 페이지](https://sir.kr/main/g5/)
- [그누보드5 공식 매뉴얼](https://sir.kr/manuals/g5/1)
- [그누보드5 Q&A](https://sir.kr/questions?s_tag=%EA%B7%B8%EB%88%84%EB%B3%B4%EB%93%9C5)
- 버전별 변경·업데이트 안내: [`docs/`](docs/)의 `release-<버전>.md`, [GitHub Releases](https://github.com/gnuboard/gnuboard5/releases)
- 개발자용 DB 마이그레이션 규칙: [데이터베이스 마이그레이션 문서](docs/database-migrations.md)
- 보안 취약점 신고: [`SECURITY.md`](SECURITY.md)

## 라이선스

라이선스 전문과 적용 조건은 [`LICENSE.txt`](LICENSE.txt)를 확인하십시오.
