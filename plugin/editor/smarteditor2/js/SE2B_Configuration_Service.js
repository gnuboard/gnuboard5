/*
 * Smart Editor 2 Configuration : This setting must be changed by service
 */
window.nhn = window.nhn || {};
nhn.husky = nhn.husky || {};
nhn.husky.SE2M_Configuration = nhn.husky.SE2M_Configuration || {};

/**
 * 스마트에디터2에서 접근하는 JS, IMG 디렉토리
 */
nhn.husky.SE2M_Configuration.Editor = {
	sJsBaseURL : './js_src',
	sImageBaseURL : './img/'
};

/**
 * JS LazyLoad를 위한 경로
 */
nhn.husky.SE2M_Configuration.LazyLoad = {
	sJsBaseURI : "js_lazyload"
};

/**
 * CSS LazyLoad를 위한 경로
 */
nhn.husky.SE2M_Configuration.SE2B_CSSLoader = {
	sCSSBaseURI : "css"
};

/**
 * 편집영역 설정
 */
nhn.husky.SE2M_Configuration.SE_EditingAreaManager = {
	sCSSBaseURI : "css",
	sBlankPageURL : "smart_editor2_inputarea.html",
	sBlankPageURL_EmulateIE7 : "smart_editor2_inputarea_ie8.html",
	aAddtionalEmulateIE7 : [] // IE8 default 사용, IE9 ~ 선택적 사용
};

/**
 * 스마트에디터2에서 사용하는 도메인 정보
 * http://wiki.nhncorp.com/pages/viewpage.action?pageId=74253685
 */
nhn.husky.SE2M_Configuration.LinkageDomain = {
	sCommonAPI : 'http://api.se2.naver.com',
	sCommonStatic : 'http://static.se2.naver.com',
	sCommonImage : 'http://images.se2.naver.com'
};


/**
 * [웹접근성]
 * 단축키 ALT+,  ALT+. 을 이용하여 스마트에디터 영역의 이전/이후 요소로 이동할 수 있다.
 * 		sBeforeElementId : 스마트에디터 영역 이전 요소의 id
 * 		sNextElementId : 스마트에디터 영역 이후 요소의 id 
 * 
 * 스마트에디터 영역 이외의 제목 영역 (예:스마트에디터가 적용된 블로그 쓰기 페이지에서의 제목 영역) 에 해당하는 엘리먼트에서 Tab키를 누르면 에디팅 영역으로 포커스를 이동시킬 수 있다.
 * 		sTitleElementId : 제목에 해당하는 input 요소의 id. 
 */
nhn.husky.SE2M_Configuration.SE2M_Accessibility = {
    sBeforeElementId : '',
    sNextElementId : '',
    sTitleElementId : ''
};

/**
 * 링크 기능 옵션
 */
nhn.husky.SE2M_Configuration.SE2M_Hyperlink = {
	bAutolink : true	// 자동링크기능 사용여부(기본값:true)
};