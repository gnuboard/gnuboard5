<?php

namespace API\v1;

// swagger-php의 tokenUrl, refreshUrl을 위한 상수 정의
define('G5_ACCESS_TOKEN_URL', G5_URL . '/api/v1/token');
define('G5_REFRESH_TOKEN_URL', G5_URL . '/api/v1/token/refresh');

/**
 * @OA\OpenApi(
 *      @OA\Info(
 *           version="1.0.0",
 *           title="GnuBoard5 API Test Documentation",
 *           description="Swagger OpenApi description Test",
 *           @OA\Contact(
 *               email="admin@admin.com"
 *           ),
 *           @OA\License(
 *               name="Apache 2.0",
 *               url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *           )
 *      ),
 *      @OA\Server(url=G5_URL),
 *      @OA\Tag(name="환경설정", description="기능별 환경설정정보 API"),
 *      @OA\Tag(name="인증", description="JWT를 활용한 인증 API"),
 *      @OA\Tag(name="회원", description="회원 API"),
 *      @OA\Tag(name="포인트", description="포인트 API"),
 *      @OA\Tag(name="쪽지", description="쪽지 API"),
 *      @OA\Tag(name="스크랩", description="스크랩 API"),
 *      @OA\Tag(name="게시판그룹", description="게시판 그룹 API"),
 *      @OA\Tag(name="게시판", description="게시판 API"),
 *      @OA\Tag(name="자동 임시저장", description="게시글 작성 시 자동 임시저장 API"),
 *      @OA\Tag(name="최신글", description="최신 게시글 API"),
 *      @OA\Tag(name="콘텐츠", description="콘텐츠 API"),
 *      @OA\Tag(name="메뉴", description="메뉴 API"),
 *      @OA\Tag(name="설문조사", description="설문조사 API"),
 *      @OA\Tag(name="인기 검색어", description="인기 검색어 API"),
 *      @OA\Tag(name="팝업 레이어", description="팝업 레이어 API"),
 *      @OA\Tag(name="방문자", description="방문자 API"),
 * )
 * 
 * @OA\SecurityScheme(
 *      type="oauth2",
 *      description="
로그인을 통해 Access Token 발급 후, API 요청 시 Authorization 헤더를 추가합니다.
- Authorization: Bearer {Access Token}
",
 *      securityScheme="Oauth2Password",
 *      @OA\Flow(
 *          flow="password",
 *          tokenUrl=G5_ACCESS_TOKEN_URL,
 *          refreshUrl=G5_REFRESH_TOKEN_URL,
 *          scopes={}
 *      )
 * )
 */
class OpenApi
{
}
