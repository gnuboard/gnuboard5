function kakaolink_send(text, url, image)
{
    if( image === undefined ){
        image = '';
    }

    // 카카오톡 링크 버튼을 생성합니다. 처음 한번만 호출하면 됩니다.
    Kakao.Link.sendDefault({
        objectType: 'feed',
        content: {
            title: String(text),
            description: url,
            imageUrl: image,
            link: {
                mobileWebUrl: url,
                webUrl: url // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
            }
        }
    });
}