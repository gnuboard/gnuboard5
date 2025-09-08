// 카카오톡 - URL 생성 후 팝업 오픈
async function openKakao5PopupFromAjax(kakaoUrl, getUrlValue) {
    const currentUrl = kakaoUrl + '/ajax.get_url.php';
    let response, data;
    try {
        response = await fetch(currentUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ get_url: getUrlValue })
        });
        data = await response.json();
    } catch (error) {
        alert('서버와 통신에 실패했습니다.');
        return;
    }

    if (data && data.url) {
        window.open(
            data.url,
            'win_template',
            `width=${data.width},height=${data.height},scrollbars=yes`
        );
    } else {
        alert('URL 생성에 실패했습니다.');
    }
}