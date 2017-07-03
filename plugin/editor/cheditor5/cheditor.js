// ================================================================
//                       CHEditor 5.1.9.3
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// EMail: support@chcode.com
// Copyright (c) 1997-2016 CHSOFT
// ================================================================
var GB = {
    colors: ['#000000','#313131','#434343','#535353','#666666','#999999','#a0a0a0','#b5b5b5','#c0c0c0','#dcdcdc','#eeeeee','#ffffff',
             '#ff0000','#ff8000','#ffff00','#80ff00','#00ff00','#00ff99','#00ffff','#0080ff','#0000ff','#7f00ff','#ff00ff','#ff007f',
             '#ffcccc','#ffe5cc','#ffffcc','#e5ffcc','#ccffcc','#ccffe5','#ccffff','#cce5ff','#ccccff','#e5ccff','#ffccff','#ffcce5',
             '#ff9999','#ffcc99','#ffff99','#ccff99','#99ff99','#99ffcc','#99ffff','#99ccff','#9999ff','#cc99ff','#ff99ff','#ff99cc',
             '#ff6666','#ffb266','#ffff66','#b2ff66','#66ff66','#66ffb2','#66ffff','#66b2ff','#6666ff','#b266ff','#ff66ff','#ff66b2',
             '#ff3333','#ff9933','#ffff33','#99ff33','#33ff33','#33ff99','#33ffff','#3399ff','#3333ff','#9933ff','#ff33ff','#ff3399',
             '#cc0000','#cc6600','#cccc00','#66cc00','#00cc00','#00cc66','#00cccc','#0066cc','#0000cc','#6600cc','#cc00cc','#cc0066',
             '#990000','#994c00','#999900','#4c9900','#009900','#00994c','#009999','#004c99','#000099','#4c0099','#990099','#99004c',
             '#660000','#663300','#666600','#336600','#006600','#006633','#006666','#003366','#000066','#330066','#660066','#660033'],
    offElementTags: {
        button: 1, embed: 1, fieldset: 1, form: 1, hr: 1, img: 1, input: 1, object: 1, select: 1, table: 1, textarea: 1
    },
    selfClosingTags: {
        area: 1, base: 1, br: 1, col: 1, command: 1, embed: 1, hr: 1, img: 1, input: 1, keygen: 1, link: 1, meta: 1,
        param: 1, source: 1, track: 1, wbr: 1
    },
    textFormatTags: {
        a: 1, addr: 1, acronym: 1, b: 1, bdo: 1, big: 1, cite: 1, code: 1, del: 1, dfn: 1, em: 1, font: 1, i: 1, ins: 1, kbd: 1,
        q: 1, samp: 1, small: 1, strike: 1, strong: 1, sub: 1, sup: 1, time: 1, tt: 1, u: 1, 'var': 1, span: 1
    },
    textFormatBlockTags: {
        address: 1, div: 1, h1: 1, h2: 1, h3: 1, h4: 1, h5: 1, h6: 1, p: 1,  pre: 1, code: 1, section: 1, aside: 1, article: 1, figcaption: 1
    },
    newLineBeforeTags: {
        address: 1, article: 1, aside: 1, audio: 1, blockquote: 1, body: 1, canvas: 1, code: 1, comment: 1, dd: 1, div: 1,
        dl: 1, fieldset: 1, figcaption: 1, figure: 1, footer: 1, form: 1, h1: 1, h2: 1, h3: 1, h4: 1, h5: 1, h6: 1, head: 1,
        header: 1, hggroup: 1, hr: 1, li: 1, noscript: 1, ol: 1, output: 1, p: 1, pre: 1, script: 1, section: 1, table: 1,
        tbody: 1, td: 1, tfoot: 1, th: 1, thead: 1, title: 1, tr: 1, ul: 1, video: 1
    },
    lineHeightBlockTags: {
        address: 1, article: 1, aside: 1, blockquote: 1, code: 1, dd: 1, div: 1, dt: 1, figcaption: 1, figure: 1,
        h1: 1, h2: 1, h3: 1, h4: 1, h5: 1, h6: 1, li: 1, p: 1, pre: 1, section: 1, td: 1, th: 1
    },
    listTags: { dd: 1, dt: 1, li: 1 },
    lineBreakTags: { address: 1, article: 1, aside: 1, dd: 1, div: 1, dt: 1, figcaption: 1, li: 1, p: 1, section: 1 },
    doctype: '<!DOCTYPE html>',
    popupWindow: {
        ColorPicker :   {tmpl : 'color_picker.html',    width : 420, title : '색상 선택'},
        Embed :         {tmpl : 'media.html',           width : 430, title : '미디어'},
        EmotionIcon :   {tmpl : 'icon.html',            width : 300, title : '표정 아이콘'},
        FlashMovie :    {tmpl : 'flash.html',           width : 584, title : '플래쉬 동영상'},
        GoogleMap :     {tmpl : 'google_map.html',      width : 538, title : '구글 지도'},
        ImageUpload :   {tmpl : 'image.html',           width : 700, title : '내 PC 사진 넣기'},
        ImageUrl :      {tmpl : 'image_url.html',       width : 350, title : '웹 사진 넣기'},
        Layout :        {tmpl : 'layout.html',          width : 430, title : '레이아웃'},
        Link :          {tmpl : 'link.html',            width : 350, title : '하이퍼링크'},
        ModifyTable :   {tmpl : 'table_modify.html',    width : 430, title : '표 고치기'},
        Symbol :        {tmpl : 'symbol.html',          width : 450, title : '특수 문자'},
        Table :         {tmpl : 'table.html',           width : 430, title : '표 만들기'}
    },
    fontName: {
        kr : ['맑은 고딕', '돋움', '굴림', '바탕', '궁서'],
        en : ['Arial', 'Comic Sans MS', 'Courier New', 'Georgia', 'HeadLineA', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana']
    },
    fontStyle: {
        FontSize: 'font-size', FontName: 'font-family', ForeColor: 'color', BackColor: 'background-color'
    },
    textAlign: {
        JustifyLeft: '', JustifyCenter: 'center', JustifyRight: 'right', JustifyFull: 'justify'
    },
    listStyle: {
        ordered: {
            decimal: '숫자', 'lower-alpha': '영문 소문자', 'upper-alpha': '영문 대문자', 'lower-roman': '로마 소문자', 'upper-roman': '로마 대문자'
        },
        unOrdered: {desc: '동그라미', circle: '빈 원', square: '사각형'}
    },
    fontSize: {
        pt: [7, 8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36],
        px: [9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72]
    },
    formatBlock: {
        P: '일반 텍스트',
        H1: '제목 1',
        H2: '제목 2',
        H3: '제목 3',
        H4: '제목 4',
        H5: '제목 5',
        H6: '제목 6',
        ADDRESS: 'Address',
        DIV: 'DIV',
        PRE: 'Preformatted (PRE)'
    },
    lineHeight: {
        '한 줄 간격': 1, '1.15': 1.15, '1.5': 1.5, '1.7': 1.7, '1.8': 1.8, '두 줄 간격': 2
    },
    textBlock: [
        ['1px #dedfdf solid','#f7f7f7'],
        ['1px #aee8e8 solid','#bfffff'],
        ['1px #d3bceb solid','#e6ccff'],
        ['1px #e8e88b solid','#ffff99'],
        ['1px #c3e89e solid','#d6ffad'],
        ['1px #e8c8b7 solid','#ffdcc9'],
        ['1px #666666 dashed','#ffffff'],
        ['1px #d4d4d4 solid','#ffffff'],
        ['1px #cccccc inset','#f7f7f7']
    ],
    node: {
        element: 1, attribute: 2, text: 3, cdata_section: 4, entity_reference: 5, entity: 6,
        processing_instruction: 7, comment: 8, document: 9, document_type: 10, document_fragment: 11,
        notation: 12
    },

    selection: { none: 1, text: 2, element: 3 },
    readyState: { 0: 'uninitialized', 1: 'loading', 2: 'loaded', 3: 'interactive', 4: 'complete' },
    dragWindow: null,
    colorDropper: null,
    readyEditor: 0,
    browser: {}
};

function isUndefined(obj) {
    return obj === void(0); // obj === undefined;
}

function detechBrowser() {
    function detect(ua) {
        var iosdevice = getFirstMatch(/(ipod|iphone|ipad)/i).toLowerCase(),
            likeAndroid = /like android/i.test(ua),
            android = !likeAndroid && /android/i.test(ua),
            versionIdentifier = getFirstMatch(/version\/(\d+(\.\d+)?)/i),
            tablet = /tablet/i.test(ua),
            mobile = !tablet && /[^\-]mobi/i.test(ua),
            result,
            osVersion = '',
            osMajorVersion,
            osname,
            app;

        function getFirstMatch(regex) {
            var match = ua.match(regex);
            return (match && match.length > 1 && match[1]) || '';
        }

        if (/opera|opr/i.test(ua)) {
            result = {
                name: 'Opera', opera: true,
                version: versionIdentifier || getFirstMatch(/(?:opera|opr)[\s\/](\d+(\.\d+)?)/i)
            };
        } else if (/windows phone/i.test(ua)) {
            result = {
                name: 'Windows Phone', windowsphone: true, msie: true,
                version: getFirstMatch(/iemobile\/(\d+(\.\d+)?)/i)
            };
        } else if (/msie|trident/i.test(ua)) {
            result = {
                name: 'Internet Explorer', msie: true, version: getFirstMatch(/(?:msie |rv:)(\d+(\.\d+)?)/i)
            };
        } else if (/edge/i.test(ua)) {
            result = {
                name: 'edge', edge: true, version: getFirstMatch(/(?:edge)\/(\d+(\.\d+)?)/i)
            };
        } else if (/chrome|crios|crmo/i.test(ua)) {
            result = {
                name: 'Chrome', chrome: true, version: getFirstMatch(/(?:chrome|crios|crmo)\/(\d+(\.\d+)?)/i)
            };
        } else if (iosdevice) {
            result = {
                name: iosdevice === 'iphone' ? 'iPhone' : iosdevice === 'ipad' ? 'iPad' : 'iPod'
            };
            if (versionIdentifier) {
                result.version = versionIdentifier;
            }
        } else if (/firefox|iceweasel/i.test(ua)) {
            result = {
                name: 'Firefox', firefox: true,
                version: getFirstMatch(/(?:firefox|iceweasel)[ \/](\d+(\.\d+)?)/i)
            };
            if (/\((mobile|tablet);[^\)]*rv:[\d\.]+\)/i.test(ua)) {
                result.firefoxos = true;
            }
        } else if (/silk/i.test(ua)) {
            result =  {
                name: 'Amazon Silk', silk: true, version : getFirstMatch(/silk\/(\d+(\.\d+)?)/i)
            };
        } else if (android) {
            result = { name: 'Android', version: versionIdentifier };
        } else if (/phantom/i.test(ua)) {
            result = {
                name: 'PhantomJS', phantom: true, version: getFirstMatch(/phantomjs\/(\d+(\.\d+)?)/i)
            };
        } else if (/blackberry|\bbb\d+/i.test(ua) || /rim\stablet/i.test(ua)) {
            result = {
                name: 'BlackBerry', blackberry: true,
                version: versionIdentifier || getFirstMatch(/blackberry[\d]+\/(\d+(\.\d+)?)/i)
            };
        } else if (/(web|hpw)os/i.test(ua)) {
            result = {
                name: 'WebOS', webos: true,
                version: versionIdentifier || getFirstMatch(/w(?:eb)?osbrowser\/(\d+(\.\d+)?)/i)
            };
            if (/touchpad\//i.test(ua)) {
                result.touchpad = true;
            }
        } else if (/safari/i.test(ua)) {
            result = {
                name: 'Safari', safari: true, version: versionIdentifier
            };
        } else {
            result = {};
        }

        if (/(apple)?webkit/i.test(ua)) {
            result.name = result.name || 'Webkit';
            result.webkit = true;
            if (!result.version && versionIdentifier) {
                result.version = versionIdentifier;
            }
        } else if (!result.opera && /gecko\//i.test(ua)) {
            result.gecko = true;
            result.version = result.version || getFirstMatch(/gecko\/(\d+(\.\d+)?)/i);
            result.name = result.name || 'Gecko';
        }
        if (android || result.silk) {
            result.android = true;
        } else if (iosdevice) {
            result[iosdevice] = true;
            result.ios = true;
        }

        if (iosdevice) {
            osVersion = getFirstMatch(/os (\d+([_\s]\d+)*) like mac os x/i);
            osVersion = osVersion.replace(/[_\s]/g, '.');
        } else if (android) {
            osVersion = getFirstMatch(/android[ \/\-](\d+(\.\d+)*)/i);
        } else if (result.windowsphone) {
            osVersion = getFirstMatch(/windows phone (?:os)?\s?(\d+(\.\d+)*)/i);
        } else if (result.webos) {
            osVersion = getFirstMatch(/(?:web|hpw)os\/(\d+(\.\d+)*)/i);
        } else if (result.blackberry) {
            osVersion = getFirstMatch(/rim\stablet\sos\s(\d+(\.\d+)*)/i);
        }

        if (osVersion) {
            result.osversion = osVersion;
        }

        osMajorVersion = osVersion.split('.')[0];
        if (tablet || iosdevice === 'ipad' ||
            (android && (osMajorVersion === 3 || (osMajorVersion === 4 && !mobile))) ||
            result.silk) {
            result.tablet = true;
        } else if (mobile || iosdevice === 'iphone' || iosdevice === 'ipod' || android ||
                result.blackberry || result.webos) {
            result.mobile = true;
        }

        if (result.edge ||
            (result.msie && result.version >= 10) ||
            (result.chrome && result.version >= 20) ||
            (result.firefox && result.version >= 20.0) ||
            (result.safari && result.version >= 6) ||
            (result.opera && result.version >= 10.0) ||
            (result.ios && result.osversion && result.osversion.split('.')[0] >= 6) ||
            (result.blackberry && result.version >= 10.1)) {
            result.a = true;
        } else if ((result.msie && result.version < 10) ||
            (result.chrome && result.version < 20) ||
            (result.firefox && result.version < 20.0) ||
            (result.safari && result.version < 6) ||
            (result.opera && result.version < 10.0) ||
            (result.ios && result.osversion && result.osversion.split('.')[0] < 6)) {
            result.c = true;
        } else {
            result.x = true;
        }

        osname = '';
        if (/windows/i.test(ua)) {
            osname = 'Windows';
        } else if (/mac/i.test(ua)) {
            osname = 'MacOS';
        } else if (/x11/i.test(ua)) {
            osname = 'UNIX';
        } else if (/linux/i.test(ua)) {
            osname = 'Linux';
        } else if (/sunos/i.test(ua)) {
            osname = 'Solaris';
        } else {
            osname = 'Unknown OS';
        }
        result.osname = osname;

        if (osname === 'Windows') {
            app = getFirstMatch(/(Windows NT\s(\d+)\.(\d+))/i);
            switch (app) {
                case 'Windows NT 5.1' : result.os = 'Windows XP'; break;
                case 'Windows NT 5.2' : result.os = 'Windows 2003'; break;
                case 'Windows NT 6.0' : result.os = 'Windows Vista'; break;
                case 'Windows NT 6.1' : result.os = 'Windows 7'; break;
                case 'Windows NT 6.2' : result.os = 'Windows 8'; break;
                case 'Windows NT 6.3' : result.os = 'Windows 8.1'; break;
                case 'Windows NT 10.0' : result.os = 'Windows 10'; break;
                default : result.os = app;
            }
        }

        if (result.msie) {
            if (result.version > 10) {
                result.msie_a = true;
                result.msie_bogus = true;
            } else if (result.version > 8) {
                result.msie_b = true;
                result.msie_bogus = false;
            } else {
                result.msie_c = true;
                result.msie_bogus = (result.os === 'Windows XP');
            }
        }
        return result;
    }
    return detect(!isUndefined(navigator) ? navigator.userAgent : null);
}

function URI(uri) {
    this.scheme = null;
    this.authority = null;
    this.path = '';
    this.query = null;
    this.fragment = null;

    this.parseUri = function (uri) {
        var m = uri.match(/^(([A-Za-z][0-9A-Za-z+.\-]*)(:))?((\/\/)([^\/?#]*))?([^?#]*)((\?)([^#]*))?((#)(.*))?/);
        this.scheme = m[3] ? m[2] : null;
        this.authority = m[5] ? m[6] : null;
        this.path = m[7];
        this.query = m[9] ? m[10] : null;
        this.fragment = m[12] ? m[13] : null;
        return this;
    };

    this.azToString = function () {
        var result = '';
        if (this.scheme !== null) {
            result = result + this.scheme + ':';
        }
        if (this.authority !== null) {
            result = result + '//' + this.authority;
        }
        if (this.path !== null) {
            result = result + this.path;
        }
        if (this.query !== null) {
            result = result + '?' + this.query;
        }
        if (this.fragment !== null) {
            result = result + '#' + this.fragment;
        }
        return result;
    };

    this.toAbsolute = function (location) {
        var baseUri = new URI(location),
            URIAbs = this,
            target = new URI(),
            removeDotSegments = function (path) {
                var result = '', rm;
                while (path) {
                    if (path.substr(0, 3) === '../' || path.substr(0, 2) === './') {
                        path = path.replace(/^\.+/, '').substr(1);
                    } else if (path.substr(0, 3) === '/./' || path === '/.') {
                        path = '/' + path.substr(3);
                    } else if (path.substr(0, 4) === '/../' || path === '/..') {
                        path = '/' + path.substr(4);
                        result = result.replace(/\/?[^\/]*$/, '');
                    } else if (path === '.' || path === '..') {
                        path = '';
                    } else {
                        rm = path.match(/^\/?[^\/]*/)[0];
                        path = path.substr(rm.length);
                        result = result + rm;
                    }
                }
                return result;
            };

        if (baseUri.scheme === null) {
            return false;
        }
        if (URIAbs.scheme !== null && URIAbs.scheme.toLowerCase() === baseUri.scheme.toLowerCase()) {
            URIAbs.scheme = null;
        }

        if (URIAbs.scheme !== null) {
            target.scheme = URIAbs.scheme;
            target.authority = URIAbs.authority;
            target.path = removeDotSegments(URIAbs.path);
            target.query = URIAbs.query;
        } else {
            if (URIAbs.authority !== null) {
                target.authority = URIAbs.authority;
                target.path = removeDotSegments(URIAbs.path);
                target.query = URIAbs.query;
            } else {
                if (URIAbs.path === '') {
                    target.path = baseUri.path;
                    target.query = URIAbs.query || baseUri.query;
                } else {
                    if (URIAbs.path.substr(0, 1) === '/') {
                        target.path = removeDotSegments(URIAbs.path);
                    } else {
                        if (baseUri.authority !== null && baseUri.path === '') {
                            target.path = '/' + URIAbs.path;
                        } else {
                            target.path = baseUri.path.replace(/[^\/]+$/, '') + URIAbs.path;
                        }
                        target.path = removeDotSegments(target.path);
                    }
                    target.query = URIAbs.query;
                }
                target.authority = baseUri.authority;
            }
            target.scheme = baseUri.scheme;
        }
        target.fragment = URIAbs.fragment;
        return target;
    };
    if (uri) {
        this.parseUri(uri);
    }
}

function setConfig() {
    var config = {
        allowedOnEvent      : true,
        colorToHex          : true,
        docTitle            : '내 문서',
        editAreaMargin      : '5px 10px',
        editorBgColor       : '#fff',
        editorFontColor     : '#000',
        editorFontName      : '"맑은 고딕", "Malgun Gothic", gulim',
        editorFontSize      : '12px',
        editorHeight        : '300px',
        editorPath          : null,
        editorWidth         : '100%',
        exceptedElements    : { script: true, style: true, iframe: false },
        fontSizeValue       : 'px',     // [pt, px]
        fullHTMLSource      : false,
        imgBlockMargin      : '5px 0px',
        imgCaptionFigure    : 'border: 1px #ccc solid; background-color: #f0f0f0; margin: 0',
        imgCaptionText      : 'margin: 5px 5px; text-align: left; line-height: 17px',
        imgCaptionWrapper   : '',
        imgDefaultAlign     : 'left',   // [left, center, right]
        imgJpegQuality      : 0.92,     // JPEG 사진의 퀄리티 값, 최대값 1
        imgMaxWidth         : 800,      // 사진 최대 가로 크기, 이 크기 보다 크면 리사이징 처리
        imgResizeMinLimit   : 32,       // 사진 리사이징의 사용자 직접 입력 값이 이 값 보다 작으면, 이 값으로 설정
        imgResizeSelected   : 800,      // 사진 리사이징의 선택 입력 폼의 기본 선택 값
        imgResizeValue      : [120, 240, 320, 640, 800, -1], // -1 = 사용자 직접 입력
        imgSetAttrAlt       : true,
        imgSetAttrWidth     : 1,        // -1 = (width="100%"; height="auto"), 0 = 설정 안함, 1 = 기본값
        imgUploadNumber     : 12,
        imgUploadSortName   : false,
        imgWaterMarkAlpha   : 1,        // 워터마크 불투명도 (최대값 1)
        imgWaterMarkUrl     : '',       // 워터마크 이미지 URL (예: 'http://udomain.com/cheditor/icons/watermark.png')
        includeHostname     : true,
        lineHeight          : 1.7,
        linkTarget          : '_blank',
        makeThumbnail       : false,    // 사진의 썸네일 이미지 생성, 가로 크기는 thumbnailWidth 값, 세로는 자동 계산
        paragraphCss        : false,    // true = <p style='margin:0'></p>, false = <p></p>
        removeIndent        : false,
        showTagPath         : false,
        tabIndent           : 3,
        tabIndex            : 0,
        thumbnailWidth      : 120,

        // 버튼 사용 유무
        useSource           : true,
        usePreview          : true,
        usePrint            : true,
        useNewDocument      : true,
        useUndo             : true,
        useRedo             : true,
        useCopy             : true,
        useCut              : true,
        usePaste            : true,
        usePasteFromWord    : true,
        useSelectAll        : true,
        useStrikethrough    : true,
        useUnderline        : true,
        useItalic           : true,
        useSuperscript      : false,
        useSubscript        : false,
        useJustifyLeft      : true,
        useJustifyCenter    : true,
        useJustifyRight     : true,
        useJustifyFull      : true,
        useBold             : true,
        useOrderedList      : true,
        useUnOrderedList    : true,
        useOutdent          : true,
        useIndent           : true,
        useFontName         : true,
        useFormatBlock      : true,
        useFontSize         : true,
        useLineHeight       : true,
        useBackColor        : true,
        useForeColor        : true,
        useRemoveFormat     : true,
        useClearTag         : true,
        useSymbol           : true,
        useLink             : true,
        useUnLink           : true,
        useFlash            : true,
        useMedia            : false,
        useImage            : true,
        useImageUrl         : false,
        useSmileyIcon       : true,
        useHR               : true,
        useTable            : true,
        useModifyTable      : true,
        useMap              : true,
        useTextBlock        : true,
        useFullScreen       : true,
        usePageBreak        : false
    },
    base, elem, i, editorUri, locationAbs;

    if (config.editorPath === null) {
        base = location.href;
        elem = document.getElementsByTagName('base');
        for (i = 0; i < elem.length; i++) {
            if (elem[i].href) {
                base = elem[i].href;
            }
        }
        elem = document.getElementsByTagName('script');
        for (i = 0; i < elem.length; i++) {
            if (elem[i].src) {
                editorUri = new URI(elem[i].src);
                if (/\/cheditor\.js$/.test(editorUri.path)) {
                    locationAbs = editorUri.toAbsolute(base).azToString();
                    delete locationAbs.query;
                    delete locationAbs.fragment;
                    config.editorPath = locationAbs.replace(/[^\/]+$/, '');
                }
            }
        }
        if (config.editorPath === null) {
            throw 'CHEditor 경로가 바르지 않습니다.\nmyeditor.config.editorPath를 설정하여 주십시오.';
        }
    }

    this.storedSelections = [];
    this.keyPressStoredSelections = [];
    this.images = [];
    this.editImages = {};
    this.cheditor = {};
    this.toolbar = {};
    this.pulldown = {};
    this.currentRS = {};
    this.resizeEditor = {};
    this.setFullScreenMode = false;
    this.modalElementZIndex = 1001;
    this.config = config;
    this.templateFile = 'template.xml';
    this.templatePath = config.editorPath + this.templateFile;
    this.W3CRange = !(this.undefined(window.getSelection));
    this.inputForm = 'textAreaId';
    this.range = null;
    this.tempTimer = null;
    this.cheditor.tabSpaces = '';
    this.cheditor.modifyState = false;
    this.cheditor.tabSpaces = new Array(this.config.tabIndent + 1).join(' ');
}

function cheditor() {
    this.toType = (function (global) {
        var toString = cheditor.prototype.toString,
            re = /^.*\s(\w+).*$/;

        return function (obj) {
            if (obj === global) {
                return 'global';
            }
            return toString.call(obj).replace(re, '$1').toLowerCase();
        };
    }(this));

    this.undefined = isUndefined;
    GB.browser = this.browser = detechBrowser();

    if (this.undefined(document.execCommand)) {
        alert('현재 브라우저에서 CHEditor를 실행할 수 없습니다.');
        return null;
    }
    if (this.browser.msie && this.browser.version < 7) {
        alert('CHEditor는 Internet Explorer 7 이하 버전은 지원하지 않습니다.');
        return null;
    }

    try {
        setConfig.call(this);
        this.cheditor.id = (this.undefined(GB.readyEditor)) ? 1 : GB.readyEditor++;
    } catch (e) {
        alert(e.toString());
        return null;
    }

    return this;
}

cheditor.prototype = {
    //----------------------------------------------------------------
    resetData : function () {
        if (GB.browser.msie) {
            if (this.undefined(this.cheditor.editArea.onreadystatechange)) {
                GB.browser.version = 11;
            }
            GB.browser.msie_bogus = (GB.browser.version < 8 || GB.browser.version > 10);
            document.execCommand('BackgroundImageCache', false, true);
        }
        this.resetEditArea();
    },

    appendContents : function (contents) {
        var div = this.doc.createElement('div');
        this.editAreaFocus();
        div.innerHTML = String(this.trimSpace(contents));

        while (div.hasChildNodes()) {
            this.doc.body.appendChild(div.firstChild);
        }
        this.editAreaFocus();
    },

    insertContents : function (contents) {
        this.editAreaFocus();
        this.doCmdPaste(String(this.trimSpace(contents)));
    },

    replaceContents : function (contents) {
        this.editAreaFocus();
        this.doc.body.innerHTML = '';
        this.loadContents(contents);
        this.editAreaFocus();
    },

    loadContents : function (contents) {
        if (typeof contents === 'string') {
            contents = this.trimSpace(contents);
            if (contents) {
                this.cheditor.editArea.style.visibility = 'hidden';
                this.doc.body.innerHTML = contents;
                this.cheditor.editArea.style.visibility = 'visible';
            }
        }
    },

    setFolderPath : function () {
        if (this.config.editorPath.charAt(this.config.editorPath.length - 1) !== '/') {
            this.config.editorPath += '/';
        }
        this.config.iconPath = this.config.editorPath + 'icons/';
        this.config.cssPath = this.config.editorPath + 'css/';
        this.config.popupPath = this.config.editorPath + 'popup/';
    },

    checkInputForm : function () {
        var textarea = document.getElementById(this.inputForm);
        if (!textarea) {
            throw 'ID가 "' + this.inputForm + '"인 textarea 개체를 찾을 수 없습니다.';
        }
        textarea.style.display = 'none';
        this.cheditor.textarea = textarea;
    },

    setDesignMode : function (designMode, doc) {
        var mode = designMode ? 'on' : 'off';

        doc = doc || this.doc;
        if (GB.browser.msie) {
            if (doc.body.contentEditable !== designMode) {
                doc.body.contentEditable = designMode;
            }
            return;
        }
        if (doc.designMode !== mode) {
            doc.designMode = mode;
        }
    },

    openDoc : function (doc, contents) {
        var html = '<html>' +
            '<head><title>' + this.config.docTitle + '</title>' +
            '<style></style></head><body>';

        doc.open();

        if (typeof contents === 'string') {
            html += this.trimSpace(contents);
        }

        html += '</body></html>';
        doc.write(html);
        doc.close();
    },

    getWindowHandle : function (iframeObj) {
        var iframeWin;
        if (iframeObj.contentWindow) {
            iframeWin = iframeObj.contentWindow;
        } else {
            throw '현재 브라우저에서 에디터를 실행할 수 없습니다.';
        }
        return iframeWin;
    },

    resetDoc : function () {
        if (this.undefined(this.cheditor.editArea)) {
            return false;
        }
        try {
            this.editArea = this.getWindowHandle(this.cheditor.editArea);
            this.doc = GB.browser.msie ? this.editArea.document : this.cheditor.editArea.contentDocument;
            this.resetData();
            return true;
        } catch (e) {
            alert(e.toString());
            return false;
        }
    },

    resetEditArea : function () {
        this.openDoc(this.doc, this.cheditor.textarea.value);
        this.setDocumentProp();
    },

    resetDocumentBody : function () {
        this.doc.body.parentNode.replaceChild(this.doc.createElement('body'), this.doc.body);
        this.setDocumentBodyProp();
    },

    setDocumentBodyProp : function () {
        this.doc.body.setAttribute('spellcheck', 'false');
        this.doc.body.setAttribute('hidefocus', '');
    },

    setDocumentProp : function () {
        var oSheet,
            bodyCss = 'font-size:' + this.config.editorFontSize +
                '; font-family:' + this.config.editorFontName +
                '; color:' + this.config.editorFontColor +
                '; margin:' + this.config.editAreaMargin +
                '; line-height:' + this.config.lineHeight,
            tableCss = 'font-size:' + this.config.editorFontSize + '; line-height:' + this.config.lineHeight,
            self = this;

        this.setDefaultCss({css: 'editarea.css', doc: this.doc});

        oSheet = this.doc.styleSheets[0];
        if (!this.W3CRange) {
            oSheet.addRule('body', bodyCss);
            oSheet.addRule('table', tableCss);
        } else {
            oSheet.insertRule('body {' + bodyCss + '}', 0);
            oSheet.insertRule('table {' + tableCss + '}', 1);
        }

        this.setDocumentBodyProp();
        this.cheditor.bogusSpacerName = 'ch_bogus_spacer';

        this.addEvent(this.doc, 'paste', function (event) {
            self.handlePaste(event);
        });

        if (!GB.browser.msie) {
            this.doc.execCommand('defaultParagraphSeparator', false, 'p');
        }

        this.setDesignMode(true);
        this.initDefaultParagraphSeparator();
    },

    initDefaultParagraphSeparator : function () {
        var p = this.doc.createElement('p'), br;

        if (this.doc.body.firstChild && this.doc.body.firstChild.nodeName.toLowerCase() === 'br') {
            this.doc.body.removeChild(this.doc.body.firstChild);
        }

        if (this.W3CRange) {
            if (!this.doc.body.hasChildNodes()) {
                this.doc.body.appendChild(p);
                if (!GB.browser.msie && !GB.browser.edge) {
                    br = this.doc.createElement('br');
                    br.className = this.cheditor.bogusSpacerName;
                    p.appendChild(br);
                    this.placeCaretAt(p, false);
                } else {
                    this.placeCaretAt(p, false);
                }
            }
        } else {
            this.doc.body.appendChild(p);
            this.placeCaretAt(p, false);
        }
    },

    handleBeforePaste : function () {
        var range = this.getRange(), commonAncestorContainer, startOffset, wrapper;
        this.backupRange();

        if (!range.collapsed) {
            range.deleteContents();
            range = this.getRange();
        }

        commonAncestorContainer = range.commonAncestorContainer;
        startOffset = range.startOffset;
        wrapper = this.doc.createElement('div');

        if (startOffset < 1 && commonAncestorContainer.nodeType === GB.node.text) {
            commonAncestorContainer.parentNode.insertBefore(wrapper, commonAncestorContainer);
        } else {
            range.insertNode(wrapper);
        }

        this.placeCaretAt(wrapper, false);
        return wrapper;
    },

    handlePaste : function (ev) {
        var text, clip, elem, wrapper, pNode, space = [], div, self = this;
        if (this.cheditor.mode === 'preview') {
            return;
        }
        if (this.cheditor.paste !== 'text' && this.cheditor.mode === 'rich' && this.W3CRange) {
            wrapper = this.handleBeforePaste();
            setTimeout(function () {
                if (wrapper) {
                    if (wrapper.hasChildNodes()) {
                        text = wrapper.innerHTML;
                        text = text.replace(/[\r\n]/g, '\u00a0');
                        text = text.replace(/<font\s?([^>]+)>(\s+|&nbsp;+)<\/font>/gi, '\u00a0');
                        text = text.replace(/<span\s?([^>]+)>(\s+|&nbsp;+)<\/span>/gi, '\u00a0');
                        text = text.replace(/<\/?(font)\s?([^>]+)?>/gi, '');
                        text = text.replace(/<strong>([\s&nbsp;]+)<\/strong>/gi, '\u00a0');
                        text = text.replace(/<b>([\s&nbsp;]+)<\/b>/gi, '\u00a0');
                        text = text.replace(/<em>([\s&nbsp;]+)<\/em>/gi, '\u00a0');
                        text = text.replace(/<i>([\s&nbsp;]+)<\/i>/gi, '\u00a0');
                        text = text.replace(/<\/?(colgroup|col\s?([^>]+))>/gi, '');
                        text = text.replace(/<(\/)?strong>/gi, '<$1b>');
                        text = text.replace(/<(\/)?em>/gi, '<$1i>');

                        wrapper.innerHTML = text;
                        if (wrapper.firstChild.nodeType === GB.node.text) {
                            text = wrapper.firstChild.data;
                            text = text.replace(/^(&nbsp;+|\s+)/g, '');
                            wrapper.firstChild.data = text;
                        }

                        elem = wrapper.firstChild;
                        while (elem) {
                            wrapper.parentNode.insertBefore(elem, wrapper);
                            elem = wrapper.firstChild;
                        }
                    }

                    pNode = wrapper.parentNode;
                    if (pNode) {
                        if (pNode.firstChild === wrapper && pNode.lastChild === wrapper) {
                            pNode.parentNode.removeChild(pNode);
                        } else {
                            pNode.removeChild(wrapper);
                        }
                    }
                    self.setImageEvent(true);
                }
            }, 50);
            return;
        }

        if (ev !== null) {
            clip = ev.clipboardData;
            this.stopEvent(ev);
        }

        text = this.trimSpace((this.undefined(clip) || clip === null) ?
            window.clipboardData.getData('Text') :
                clip.getData('text/plain'));

        if (text !== '') {
            text = text.replace(/\r/g, '');
            if (this.cheditor.mode === 'code') {
                div = this.doc.createElement('div');
                text = this.htmlEncode(text);
                text = text.replace(/\s{2,}/gm, '\n');
                text = text.replace(/[\u200b\ufeff\xa0\u3000]+/g, '');

                if (GB.browser.msie && GB.browser.version < 9) {
                    text = text.replace(/\n/g, '<br />');
                    text = text.replace(/\t/g, '__CHEDITOR_TAB_SPACE__');
                    text = text.replace(/\s/gm, '&nbsp;');
                }
                div.innerHTML = text;
                div.id = 'clipboardData';
                this.insertHTML(div);
                return;
            }

            text = this.htmlEncode(text);
            text = text.replace(/[\r\n]+/g, '\n');
            text = text.split('\n').join('<br>');

            text = text.replace(/(\s{2,})/g, function (a, b) {
                space = b.split(/\s/);
                space.shift();
                return ' ' + space.join('&nbsp;');
            });
            this.insertHTML(text);
            self.setImageEvent(true);
        }
    },

    editAreaFocus : function () {
        this.editArea.focus();
    },

    resizeGetY : function (evt) {
        return GB.browser.msie ?
                window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop :
                    evt.clientY + window.pageYOffset;
    },

    resizeStart : function (evt) {
        var self = this;
        self.currentRS.elNode = self.cheditor.mode === 'code' ? self.cheditor.textContent : self.cheditor.editArea;
        self.currentRS.cursorStartY = self.resizeGetY(evt);
        self.currentRS.elStartTop = parseInt(self.currentRS.elNode.style.height, 10);

        if (isNaN(self.currentRS.elStartTop)) {
            self.currentRS.elStartTop = 0;
        }

        evt = evt || window.event;

        self.resizeEditor.stopFunc = function (event) {
            self.resizeStop(event);
        };
        self.resizeEditor.moveFunc = function (event) {
            self.resizeMove(event);
        };

        if (GB.browser.msie) {
            self.setDesignMode(false);
        }

        self.currentRS.elNode.style.visibility = 'hidden';
        self.addEvent(document, 'mousemove', self.resizeEditor.moveFunc);
        self.addEvent(document, 'mouseup', self.resizeEditor.stopFunc);
        self.stopEvent(evt);
    },

    resizeMove : function (evt) {
        var offset = this.resizeGetY(evt),
            height = this.currentRS.elStartTop + offset - this.currentRS.cursorStartY;
        if (height < 1) {
            this.resizeStop(evt);
            height = 1;
        }
        this.config.editorHeight = this.currentRS.elNode.style.height = height + 'px';
        this.stopEvent(evt);
    },

    resizeStop : function (evt) {
        this.stopEvent(evt);
        this.removeEvent(document, 'mouseup', this.resizeEditor.stopFunc);
        this.removeEvent(document, 'mousemove', this.resizeEditor.moveFunc);
        this.currentRS.elNode.style.visibility = 'visible';
        if (GB.browser.msie) {
            this.setDesignMode(true);
        }
        if (this.cheditor.mode === 'code') {
            this.config.editorHeight = (parseInt(this.config.editorHeight, 10)
                + parseInt(this.cheditor.textContent.getAttribute('xbar-height'), 10)) + 'px';
            this.cheditor.textContent.focus();
        } else if (this.cheditor.mode === 'rich') {
            this.editAreaFocus();
        }
    },

    switchEditorMode : function (changeMode) {
        var self = this, i, className, interval;

        this.editAreaFocus();
        if (this.cheditor.mode === changeMode) {
            return;
        }

        for (i in this.cheditor.modetab) {
            if (this.cheditor.modetab.hasOwnProperty(i)) {
                className = this.cheditor.modetab[i].className;
                className = className.replace(/\-off$/, '');
                if (i !== changeMode) {
                    this.cheditor.modetab[i].className = className + '-off';
                } else {
                    this.cheditor.modetab[i].className = className;
                }
            }
        }

        switch (changeMode) {
            case 'rich' :
                this.richMode();
                this.showTagSelector(true);
                break;
            case 'code' :
                if (this.cheditor.modifyState) {
                    interval = setInterval(function () {
                        if (!self.cheditor.modifyState) {
                            clearInterval(interval);
                            self.editMode();
                        }
                    }, 10);
                } else {
                    this.editMode();
                }
                this.showTagSelector(false);
                break;
            case 'preview' :
                this.previewMode();
                this.showTagSelector(false);
        }
        this.cheditor.mode = changeMode;
    },

    initTemplate : function () {
        var self = this,
            httpRequest = null,
            showError = function (msg) {
                alert(self.templateFile + ' 파일 로딩 중 오류가 발생하였습니다.\n원인: ' + msg);
            },
            templateReady = function () {
                var event;
                if (httpRequest.readyState === 4) {
                    if (httpRequest.status === 200) {
                        try {
                            self.xmlDoc =  httpRequest.responseXML || httpRequest;
                            self.loadTemplate(self.xmlDoc);
                            if (self.W3CRange) {
                                event = document.createEvent('Event');
                                event.initEvent(self.cheditor.id, true, true);
                                document.dispatchEvent(event);
                            } else {
                                document.documentElement.loadEvent = self.cheditor.id;
                            }
                        } catch (e) {
                            showError(e.toString());
                        }
                    } else {
                        showError('XMLHttpRequest. Status ' + httpRequest.status);
                    }
                }
            };

        if (window.XMLHttpRequest) {
            httpRequest = new XMLHttpRequest();
            if (httpRequest.overrideMimeType) {
                httpRequest.overrideMimeType('text/xml');
            }
            httpRequest.onreadystatechange = templateReady;
            try {
                httpRequest.open('GET', self.templatePath, true);
            }
            catch (e) {
                showError(e + '참고: 에디터를 웹 서버에서 실행하여 주십시오.');
            }
            httpRequest.send();
        } else if (window.ActiveXObject) {
            httpRequest = new window.ActiveXObject('Microsoft.XMLDOM');
            httpRequest.async = true;
            httpRequest.onreadystatechange = templateReady;
            httpRequest.load(self.templatePath);
        } else {
            showError('현재 브라우저에서 ' + self.templateFile + ' 파일을 사용할 수 없습니다.');
        }
    },
/*
    getCDATASection : function (node) {
        var elem, data;
        if (node.hasChildNodes()) {
            elem = node.firstChild;
            while (elem && elem.nodeType !== GB.node.cdata_section) {
                elem = elem.nextSibling;
            }
            if (elem && elem.nodeType === GB.node.cdata_section) {
                data = elem.data;
                data = data.replace(/\n/g, '');
                data = data.replace(/(\s+?)<([^>]*)>/g, '<$2>');
                data = this.trimSpace(data);
                return data;
            }
        }
        return null;
    },
*/
    getCDATASection : function (node) {
        var text = node.textContent || node.text;
        text = text.replace(/\n/g, '');
        text = text.replace(/(\s+?)<([^>]*)>/g, '<$2>');
        text = this.trimSpace(text);
        return text;
    },

    setToolbarBgPosition : function (elem, attr) {
        elem.style.backgroundPosition = attr;
    },

    getToolbarBgPosition : function (elem) {
        var pos;
        switch (elem.className) {
            case 'cheditor-tb-bg'           : pos = 3; break;
            case 'cheditor-tb-bg-last'      : pos = 6; break;
            case 'cheditor-tb-bg-single'    : pos = 9; break;
            case 'cheditor-tb-bg30-first'   : pos = 12; break;
            case 'cheditor-tb-bg30'         : pos = 15; break;
            case 'cheditor-tb-bg30-last'    : pos = 18; break;
            case 'cheditor-tb-bg55'         : pos = 21; break;
            case 'cheditor-tb-bg40'         : pos = 24; break;
            case 'cheditor-tb-bg44'         : pos = 27; break;
            case 'cheditor-tb-bgcombo'      : pos = 30; break;
            case 'cheditor-tb-bgcombo-last' : pos = 33; break;
            default : pos = 0;
        }
        return pos;
    },

    toolbarMouseOverUp : function (elem) {
        var pos, obj;
        if (elem.checked) {
            return;
        }
        this.setToolbarBgPosition(elem.button, '0 ' + (~(((elem.pos + 1) * elem.height)) + 1) + 'px');

        if ((elem.name === 'combobox' && elem.prev && elem.prev.checked) ||
                (elem.name === 'combo' && elem.next && elem.next.checked)) {
            return;
        }
        if (elem.type === 'combobox') {
            if (elem.prev.checked) {
                return;
            }
            obj = elem.prev;
            pos = '0px ' + (~(((obj.pos + 1) * obj.height)) + 1) + 'px';
            this.setToolbarBgPosition(obj.button, pos);
        } else if (elem.type === 'combo') {
            if (elem.prev && !elem.prev.checked && !elem.prev.active) {
                obj = elem.prev;
                pos = (~(obj.width) + 1) + 'px ' + (~(obj.pos * obj.height) + 1) + 'px';
                this.setToolbarBgPosition(obj.button, pos);
            }
            if (elem.next) {
                if (elem.next.checked) {
                    return;
                }
                obj = elem.next;
                pos = (~(obj.width) + 1) + 'px ' + (~(((obj.pos + 1) * obj.height)) + 1) + 'px';
                this.setToolbarBgPosition(obj.button, pos);
            }
        } else {
            if (!elem.prev || (elem.prev && elem.prev.checked)) {
                return;
            }
            obj = elem.prev;
            if (obj.className === 'cheditor-tb-bg-first') {
                pos = (~(obj.width) + 1) + 'px 0';
            } else {
                pos = (~(obj.width) + 1) + 'px ' + (~(obj.pos * obj.height) + 1) + 'px';
            }
            this.setToolbarBgPosition(obj.button, pos);
        }
    },

    toolbarMouseDownOut : function (elem, mousedown) {
        if (elem.next && elem.next.checked && !mousedown) {
            this.setToolbarBgPosition(elem.button, (~(elem.width * 2) + 1) + 'px ' +
                (~(elem.pos * elem.height) + 1) + 'px');
        }
        if (elem.prev) {
            if (elem.prev.active || (elem.prev.type === 'combo' && elem.checked)) {
                return;
            }
            if (elem.prev.checked) {
                this.setToolbarBgPosition(elem.prev.button, '0 ' +
                    (~((elem.prev.pos + 2) * elem.prev.height) + 1) + 'px');
                return;
            }
            if (mousedown) {
                this.setToolbarBgPosition(elem.prev.button, (~(elem.prev.width * 2) + 1) + 'px ' +
                    (~(elem.prev.pos * elem.prev.height) + 1) + 'px');
            } else {
                this.setToolbarBgPosition(elem.prev.button,
                    '0 ' + (~(elem.prev.pos * elem.prev.height) + 1) + 'px');
            }
        }
    },

    toolbarButtonChecked : function (elem) {
        this.setToolbarBgPosition(elem.button, '0 ' + (~((elem.pos + 2) * elem.height) + 1) + 'px');
        if (elem.prev && elem.prev.type === 'combo') {
            if (elem.prev.checked || elem.checked) {
                return;
            }
            this.setToolbarBgPosition(elem.prev.button, (~(elem.prev.width * 2) + 1) + 'px ' +
                (~(elem.prev.pos * elem.prev.height) + 1) + 'px');
        }
        if (elem.prev && !elem.prev.checked) {
            if (elem.checked) {
                this.setToolbarBgPosition(elem.prev.button, (~(elem.prev.width * 2) + 1) + 'px ' +
                    (~(elem.prev.pos * elem.prev.height) + 1) + 'px');
            } else {
                this.setToolbarBgPosition(elem.prev.button, '0 ' + (~(elem.prev.pos * elem.prev.height) + 1) + 'px');
            }
        }
    },

    toolbarButtonUnchecked : function (elem) {
        if (elem.type === 'combobox' && !elem.checked) {
            if (elem.prev.checked) {
                this.setToolbarBgPosition(elem.button,
                    (~(elem.width) + 1) + 'px ' + (~(((elem.pos + 1) * elem.height)) + 1) + 'px');
                return;
            }
            this.setToolbarBgPosition(elem.prev.button, '0 ' + (~(elem.prev.pos * elem.prev.height) + 1) + 'px');
        }
        this.setToolbarBgPosition(elem.button, '0 ' + (~(elem.pos * elem.height) + 1) + 'px');
        if (elem.prev && elem.prev.name === 'BackColor') {
            this.setToolbarBgPosition(elem.prev.button, '0 ' + (~(elem.prev.pos * elem.prev.height) + 1) + 'px');
        }
    },

    makeToolbarGrayscale : function (image) {
        var context, imageData, filter, imgWidth = image.width, imgHeight = image.height,
            canvas = this.doc.createElement('canvas');

        filter = function (pixels) {
            var d = pixels.data, i, r, g, b;
            for (i = 0; i < d.length; i += 4) {
                r = d[i];
                g = d[i + 1];
                b = d[i + 2];
                d[i] = d[i + 1] = d[i + 2] = (r + g + b) / 3;
            }
            return pixels;
        };

        context = canvas.getContext('2d');
        canvas.width = imgWidth;
        canvas.height = imgHeight;
        context.drawImage(image, 0, 0);

        imageData = context.getImageData(0, 0, imgWidth, imgHeight);
        filter(imageData);
        context.putImageData(imageData, 0, 0);
        return canvas.toDataURL();
    },

    toolbarSetBackgroundImage : function (elem, disable) {
        var css = elem.firstChild.className,
            tbEnable = (this.cheditor.toolbarGrayscale && elem.firstChild.style.backgroundImage);

        css = css.replace(/-disable$/i, '');
        if (disable) {
            if (tbEnable) {
                elem.firstChild.style.backgroundImage = 'url(' + this.cheditor.toolbarGrayscale + ')';
            }
            css = css + '-disable';
            elem.style.cursor = 'default';
        } else {
            if (tbEnable) {
                elem.firstChild.style.backgroundImage = 'url(' + this.toolbar.icon + ')';
            }
            elem.style.cursor = 'pointer';
        }
        elem.firstChild.className = css;
    },

    toolbarDisable : function (elem, disable) {
        if (disable) {
            this.toolbarSetBackgroundImage(elem.button, true);
            this.toolbarButtonUnchecked(elem);
            this.toolbarMouseDownOut(elem);
            this.toolbar[elem.name].disabled = true;
            return true;
        }
        this.toolbarSetBackgroundImage(elem.button, false);
        this.toolbar[elem.name].disabled = false;
        return false;
    },

    colorConvert : function (color, which, opacity) {
        var colorDefs = [
            {
                re: /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,
                process : function (bits) {
                    return [
                        parseInt(bits[1], 10),
                        parseInt(bits[2], 10),
                        parseInt(bits[3], 10),
                        1
                    ];
                }
            },
            {
                re : /^rgba\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3}),\s*(\d+(?:\.\d+)?|\.\d+)\s*\)/,
                process : function (bits) {
                    return [
                        parseInt(bits[1], 10),
                        parseInt(bits[2], 10),
                        parseInt(bits[3], 10),
                        parseFloat(bits[4])
                    ];
                }
            },
            {
                re: /^([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/,
                process : function (bits) {
                    return [
                        parseInt(bits[1], 16),
                        parseInt(bits[2], 16),
                        parseInt(bits[3], 16),
                        1
                    ];
                }
            },
            {
                re: /^([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])$/,
                process : function (bits) {
                    return [
                        parseInt(bits[1] * 2, 16),
                        parseInt(bits[2] * 2, 16),
                        parseInt(bits[3] * 2, 16),
                        1
                    ];
                }
            }
        ], r = null, g = null, b = null, a = null, i, re, processor, bits, channels, min, rData = null;

        if (!which) {
            which = 'rgba';
        }

        color = color.replace(/^\s*#|\s*$/g, '');
        if (color.length === 3) {
            color = color.replace(/(.)/g, '$1$1');
        }

        color = color.toLowerCase();
        which = which.toLowerCase();

        for (i = 0; i < colorDefs.length; i++) {
            re = colorDefs[i].re;
            processor = colorDefs[i].process;
            bits = re.exec(color);
            if (bits) {
                channels = processor(bits);
                r = channels[0];
                g = channels[1];
                b = channels[2];
                a = channels[3];
            }
        }

        r = (r < 0 || isNaN(r)) ? 0 : ((r > 255) ? 255 : r);
        g = (g < 0 || isNaN(g)) ? 0 : ((g > 255) ? 255 : g);
        b = (b < 0 || isNaN(b)) ? 0 : ((b > 255) ? 255 : b);
        a = (a < 0 || isNaN(a)) ? 0 : ((a > 1) ? 1 : a);

        function hex(x) {
            return ('0' + parseInt(x, 10).toString(16)).slice(-2);
        }

        switch (which) {
            case 'rgba':
                if (opacity) {
                    a = (255 - (min = Math.min(r, g, b))) / 255;
                    r = ((r - min) / a).toFixed(0);
                    g = ((g - min) / a).toFixed(0);
                    b = ((b - min) / a).toFixed(0);
                    a = a.toFixed(4);
                }
                rData = 'rgba(' + r + ',' + g + ',' + b + ',' + a + ')';
                break;
            case 'rgb':
                rData = 'rgb(' + r + ',' + g + ',' + b + ')';
                break;
            case 'hex':
                if (isNaN(parseInt(r, 10)) || isNaN(parseInt(g, 10)) || isNaN(parseInt(b, 10))) {
                    return color;
                }
                rData = '#' + hex(r) + hex(g) + hex(b);
                break;
        }
        return rData;
    },

    toolbarUpdate : function (srcElement) {
        var toolbar = this.toolbar,
            range = this.getRange(),
            isCollapsed = this.rangeCollapsed(range),
            sType = GB.selection.text,
            bControl = false, bTable = false, ancestorsLen = 0, bNoOff = { 'Link': 1 }, ancestors = [],
            i, j, btn, cmd, autoOff, bDisable, el, wrapper, fontAttr, oldName, span, newAttr, defaultAttr,
            state, css, node, alignment, pNode;

        pNode = srcElement || this.getRangeElement(range);
        switch (pNode.nodeType) {
            case GB.node.element:
                sType = GB.selection.element;
                break;
            case GB.node.text:
                sType = GB.selection.text;
                pNode = pNode.parentNode;
                break;
            default:
                return;
        }

        if (sType === GB.selection.element && !isCollapsed) {
            bControl = GB.offElementTags[pNode.nodeName.toLowerCase()];
        } else {
            node = pNode;
            while (node && node.nodeType === GB.node.element && node.nodeName.toLowerCase() !== 'body') {
                ancestors.push(node);
                if (node.nodeName.toLowerCase() === 'td' || node.nodeName.toLowerCase() === 'th') {
                    bTable = true;
                }
                node = node.parentNode;
            }
            ancestorsLen = ancestors.length;
        }

        if (!bTable && sType === GB.selection.element &&
            (pNode.nodeName.toLowerCase() === 'table' || pNode.nodeName.toLowerCase() === 'td' ||
             pNode.nodeName.toLowerCase() === 'th')) {
            bTable = true;
        }

        alignment = { JustifyCenter : 'center', JustifyRight : 'right', JustifyFull : 'justify' };

        for (i in toolbar) {
            if (!toolbar.hasOwnProperty(i)) {
                continue;
            }

            btn = toolbar[i];
            if (!btn.cmd) {
                continue;
            }

            cmd = btn.cmd;
            autoOff = false;

            if (bControl && sType === GB.selection.element) {
                if (btn.group !== 'Alignment') {
                    autoOff = !(pNode.nodeName.toLowerCase() === 'img' && bNoOff[cmd]);
                }
            }

            if (btn.name === 'ModifyTable') {
                autoOff = !bTable;
            }

            bDisable = this.toolbarDisable(btn, autoOff);

            if (btn.name === 'ForeColor' || btn.name === 'BackColor') {
                btn.button.lastChild.style.display = bDisable ? 'none' : 'block';
            }
            if (btn.autocheck === null) {
                continue;
            }

            switch (cmd) {
                case 'Copy' :
                case 'Cut'  :
                    this.toolbarDisable(btn, isCollapsed);
                    break;
                case 'UnLink' :
                    if (GB.browser.firefox) {
                        this.toolbarDisable(btn, (pNode.nodeName.toLowerCase() !== 'a' && !pNode.getAttribute('href')));
                    } else {
                        this.toolbarDisable(btn, this.doc.queryCommandEnabled(cmd) === false);
                    }
                    break;
                case 'FormatBlock' :
                    wrapper = btn.button.firstChild;
                    oldName = wrapper.firstChild;
                    el = false;
                    span = document.createElement('span');
                    for (j = 0; j < ancestorsLen; j++) {
                        if (GB.formatBlock[ancestors[j].nodeName]) {
                            span.appendChild(document.createTextNode(ancestors[j].nodeName));
                            wrapper.replaceChild(span, oldName);
                            el = true;
                            break;
                        }
                    }
                    if (!el) {
                        span.appendChild(document.createTextNode('스타일'));
                        wrapper.replaceChild(span, oldName);
                    }
                    this.unselectionElement(span);
                    break;
                case 'ForeColor' :
                case 'BackColor' :
                    if (cmd === 'BackColor' && !GB.browser.msie) {
                        cmd = 'HiliteColor';
                    }
                    fontAttr = this.doc.queryCommandValue(cmd);
                    if (fontAttr && !/^[rgb|#]/.test(fontAttr)) {
                        fontAttr = (((fontAttr & 0x0000ff) << 16) | (fontAttr & 0x00ff00) | ((fontAttr & 0xff0000) >>> 16)).toString(16);
                        fontAttr = '#000000'.slice(0, 7 - fontAttr.length) + fontAttr;
                    } else {
                        fontAttr = (cmd === 'ForeColor') ? this.config.editorFontColor : this.config.editorBgColor;
                    }
                    btn.button.lastChild.style.backgroundColor = fontAttr;
                    break;
                case 'FontName' :
                case 'FontSize' :
                    fontAttr = this.doc.queryCommandValue(cmd);
                    wrapper = btn.button.firstChild;
                    span = this.doc.createElement('span');

                    if (cmd === 'FontSize') {
                        fontAttr = pNode.style.fontSize;
                        if (!fontAttr) {
                            for (i = 0; i < ancestors.length; i++) {
                                fontAttr = ancestors[i].style.fontSize;
                                if (fontAttr) {
                                    break;
                                }
                            }
                        }
                    }
                    if (fontAttr) {
                        newAttr = fontAttr;
                        newAttr = newAttr.replace(/'/g, '');
                        span.appendChild(this.doc.createTextNode(newAttr));
                        wrapper.replaceChild(span, wrapper.firstChild);
                    }
                    if (!span.hasChildNodes()) {
                        defaultAttr = (cmd === 'FontSize') ? this.config.editorFontSize : this.config.editorFontName;
                        if (wrapper.hasChildNodes()) {
                            wrapper.removeChild(wrapper.firstChild);
                        }
                        defaultAttr = defaultAttr.replace(/'/g, '');
                        span.appendChild(this.doc.createTextNode(defaultAttr));
                        wrapper.appendChild(span);
                    }
                    this.unselectionElement(span);
                    break;
                case 'LineHeight':
                    wrapper = btn.button.firstChild;
                    this.unselectionElement(wrapper.firstChild);
                    break;
                default :
                    if (!this.doc.queryCommandSupported(cmd)) {
                        continue;
                    }
                    state = this.doc.queryCommandState(cmd);
                    if (state === null) {
                        continue;
                    }

                    if (GB.browser.msie && state === false && alignment[cmd]) {
                        el = pNode;
                        while (el && el.nodeName.toLowerCase() !== 'body') {
                            if (GB.lineHeightBlockTags[el.nodeName.toLowerCase()]) {
                                css = this.getCssValue(el);
                                if (css) {
                                    for (j = 0; j < css.length; j++) {
                                        if (css[j].name.toLowerCase() === 'text-align' && css[j].value === alignment[cmd]) {
                                            state = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            el = el.parentNode;
                        }
                    }

                    if (state) {
                        btn.checked = true;
                        this.toolbarButtonChecked(btn);
                        if (btn.type === 'combo' && btn.name === btn.next.node) {
                            btn.next.active = true;
                            this.setToolbarBgPosition(btn.next.button,
                                (~(btn.next.width) + 1) + 'px ' + (~(((btn.next.pos + 1) * btn.next.height)) + 1) + 'px');
                        }
                    } else {
                        this.toolbarButtonUnchecked(btn);
                        btn.checked = false;
                        if (btn.next) {
                            btn.next.active = false;
                            if (btn.type === 'combo' && btn.name === btn.next.node) {
                                this.toolbarButtonUnchecked(btn.next);
                            }
                        }
                    }

            }
        }
    },

    createButton : function (name, attr, prev) {
        var self = this,
            elem, icon, btnIcon, iconPos, method, cmd, check, type, node, btnHeight, btnWidth, text,
            span, obj, btnClass, comboOut;

        method = attr.getElementsByTagName('Execution')[0].getAttribute('method');
        cmd = attr.getElementsByTagName('Execution')[0].getAttribute('value');
        check = attr.getAttribute('check');
        type = attr.getAttribute('type');
        node = attr.getAttribute('node');

        btnClass = attr.getAttribute('class');
        btnWidth = attr.getAttribute('width');
        btnHeight = attr.getAttribute('height');

        elem = document.createElement('div');
        elem.style.width = btnWidth + 'px';
        elem.setAttribute('name', name);
        elem.style.height = btnHeight + 'px';
        elem.style.border = '0px solid transparent';

        icon = attr.getElementsByTagName('Icon')[0];
        btnIcon = document.createElement('div');
        btnIcon.className = icon.getAttribute('class');
        btnIcon.style.marginLeft = icon.getAttribute('margin') || '3px';

        iconPos = icon.getAttribute('position');
        if (iconPos) {
            btnIcon.style.backgroundImage = 'url(' + self.toolbar.icon + ')';
            btnIcon.style.backgroundRepeat = 'no-repeat';
            self.setToolbarBgPosition(btnIcon, (~iconPos + 1) + 'px center');
        } else {
            text = icon.getAttribute('alt');
            if (text) {
                span = document.createElement('span');
                span.appendChild(document.createTextNode(text));
                btnIcon.appendChild(span);
            }
        }

        elem.appendChild(btnIcon);
        obj = { 'autocheck': check,
                'button': elem,
                'className': btnClass,
                'checked': false,
                'cmd': cmd,
                'colorNode': {},
                'disabled': false,
                'group': '',
                'height': btnHeight,
                'method': method,
                'name': name,
                'next': null,
                'node': node,
                'num': 0,
                'pos': 0,
                'prev': prev,
                'type': type,
                'width': btnWidth };

        if (prev) {
            prev.next = obj;
        }

        elem.attr = obj;
        self.toolbar[name] = obj;

        self.addEvent(elem, 'mouseover', function (ev) {
            if (!obj.disabled) {
                self.toolbarMouseOverUp(obj);
            }
            self.stopEvent(ev || window.event);
        });

        self.addEvent(elem, 'mousedown', function (ev) {
            if (!obj.checked && !obj.disabled) {
                self.toolbarButtonChecked(obj);
                self.toolbarMouseDownOut(obj, true);
                if (obj.prev && obj.prev.type === 'combo' && !obj.prev.checked) {
                    self.setToolbarBgPosition(obj.prev.button,
                        '0 ' + (~((self.getToolbarBgPosition(obj.prev.button) + 1) * obj.prev.height) + 1) + 'px');
                }
            }
            if (obj.next) {
                obj.next.button.style.visibility = 'hidden';
                obj.next.button.style.visibility = 'visible';
            }
            self.stopEvent(ev || window.event);
        });

        self.addEvent(elem, 'click', function (ev) {
            if (obj.disabled) {
                return;
            }
            switch (obj.method) {
                case 'doCmd' :
                    self.backupRange();
                    self.doCmd(obj.cmd, null);
                    break;
                case 'windowOpen' :
                    self.windowOpen(obj.cmd);
                    break;
                case 'showPulldown' :
                    if (obj.checked) {
                        obj.checked = false;
                        self.boxHideAll();
                        self.toolbarButtonUnchecked(obj);
                        return;
                    }
                    obj.checked = true;
                    self.showPulldown(obj.cmd, obj.button);
                    self.toolbarButtonChecked(obj);
                    self.toolbarMouseDownOut(obj, true);
                    break;
                default :
                    alert('지원하지 않는 명령입니다.');
            }
            self.stopEvent(ev || window.event);
        });

        comboOut = function (combo, startPos) {
            self.setToolbarBgPosition(combo.button,
                startPos + 'px ' + (~(((self.getToolbarBgPosition(combo.button) + (combo.checked ? 2 : 1)) * combo.height)) + 1) + 'px');
        };

        self.addEvent(elem, 'mouseout', function () {
            if (!obj.checked) {
                if (obj.type === 'combo') {
                    if (obj.next) {
                        if (!obj.next.checked) {
                            self.toolbarButtonUnchecked(obj.next);
                            self.toolbarMouseDownOut(obj.next, false);
                        } else {
                            return;
                        }
                    }
                }
                if (obj.type === 'combobox' && obj.prev.checked) {
                    self.setToolbarBgPosition(obj.button,
                        (~(obj.width) + 1) + 'px ' + (~(((obj.pos + 1) * obj.height)) + 1) + 'px');
                    return;
                }
                self.toolbarButtonUnchecked(obj);
                self.toolbarMouseDownOut(obj, false);
            } else {
                if (obj.node && obj.node === obj.prev.name) {
                    if (!obj.prev.checked) {
                        self.setToolbarBgPosition(obj.prev.button,
                            '0 ' + (~((self.getToolbarBgPosition(obj.prev.button) + 1) * obj.prev.height) + 1) + 'px');
                    }
                    comboOut(obj, 0);
                }
            }
        });

        return obj;
    },

    showToolbar : function (toolbar, toolbarWrapper) {
        var self = this,
            i, j, grpName, btn, btnLen, prevObj, attr, btnName, btnObj = null, btnNum, spacer,
            currentColor, fullscreen, child, len,
            toolbarIcon = toolbar.getElementsByTagName('Image').item(0).getAttribute('file'),
            tmpArr = toolbarIcon.split(/\./),
            group = toolbar.getElementsByTagName('Group'),
            grpNum = group.length,
            appendSpaceBlock = function (pNode) {
                var split = document.createElement('div');
                split.className = 'cheditor-tb-split';
                pNode.appendChild(split);
            },
            onClickEventHandler = function () {
                if (self.setFullScreenMode) {
                    this.className = 'cheditor-tb-fullscreen';
                    this.setAttribute('title', '전체 화면');
                } else {
                    this.className = 'cheditor-tb-fullscreen-actual';
                    this.setAttribute('title', '이전 크기로 복원');
                }
                self.fullScreenMode();
            };

        self.toolbar.icon = self.config.iconPath + toolbarIcon;
        self.toolbar.iconDisable = self.config.iconPath + tmpArr[0] + '-disable' + '.' + tmpArr[1];
        toolbarWrapper.className = 'cheditor-tb-wrapper';

        fullscreen = document.createElement('span');
        if (self.config.useFullScreen === true) {
            fullscreen.appendChild(document.createTextNode('\u00a0'));
            fullscreen.className = 'cheditor-tb-fullscreen';
            fullscreen.setAttribute('title', '전체 화면');
            (function () {
                fullscreen.onclick = onClickEventHandler;
            })();
        } else {
            fullscreen.clsaaName = 'cheditor-tb-fullscreen-disable';
        }
        toolbarWrapper.appendChild(fullscreen);

        for (i = 0; i < grpNum; i++) {
            grpName = group[i].getAttribute('name');
            if (grpName === 'Split') {
                appendSpaceBlock(toolbarWrapper);
                continue;
            }

            btn = group[i].getElementsByTagName('Button');
            btnLen = btn.length;
            btnNum = 0; btnObj = null;

            for (j = 0; j < btnLen; j++) {
                attr = btn[j].getElementsByTagName('Attribute')[0];
                btnName = btn[j].getAttribute('name');
                if (!attr.getAttribute('node') && self.config['use' + btnName] !== true) {
                    continue;
                }
                if (attr.getAttribute('type') === 'combobox' && self.config['use' + attr.getAttribute('node')] !== true) {
                    continue;
                }

                btnObj = self.createButton(btnName, attr, btnObj);
                self.toolbar[btnObj.name].num = btnNum++;
                self.toolbar[btnObj.name].group = grpName;

                if (btn[j].getAttribute('tooltip') !== null) {
                    btnObj.button.setAttribute('title', btn[j].getAttribute('tooltip'));
                }

                if (btnObj.name === 'ForeColor' || btnObj.name === 'BackColor') {
                    currentColor = document.createElement('div');
                    currentColor.className = 'cheditor-tb-color-btn';
                    currentColor.style.backgroundColor = attr.getAttribute('default');
                    btnObj.button.appendChild(currentColor);
                }
                toolbarWrapper.appendChild(btnObj.button);
            }

            if (btnObj === null) {
                continue;
            }

            prevObj = btnObj.prev;

            if (!prevObj) {
                btnObj.button.className = btnObj.className;
                if (btnObj.className === 'cheditor-tb-bg') {
                    btnObj.className = btnObj.className + '-single';
                    btnObj.button.className = btnObj.className;
                }
                btnObj.pos = self.getToolbarBgPosition(btnObj.button);
            } else {
                btnObj.className = btnObj.className + '-last';
                btnObj.button.className = btnObj.className;
                btnObj.pos = self.getToolbarBgPosition(btnObj.button);
                while (prevObj) {
                    prevObj.button.className = prevObj.className;
                    prevObj.pos = self.getToolbarBgPosition(prevObj.button);
                    btnObj = prevObj;
                    prevObj = prevObj.prev;
                }
                btnObj.className = btnObj.className + '-first';
                btnObj.button.className = btnObj.className;
                btnObj.pos = self.getToolbarBgPosition(btnObj.button);
            }
            spacer = document.createElement('div');
            spacer.className = 'cheditor-tb-button-spacer';
            toolbarWrapper.appendChild(spacer);
        }

        appendSpaceBlock(toolbarWrapper);

        if (GB.browser.msie) {
            child = toolbarWrapper.getElementsByTagName('div');
            len = child.length;
            for (i = 0; i < len; i++) {
                self.unselectionElement(child[i]);
            }
            self.unselectionElement(toolbarWrapper);
        } else {
            self.unselectionElement(toolbarWrapper);
        }
    },

    unselectionElement : function (elem) {
        if (!elem || elem.nodeType !== GB.node.element) {
            return;
        }
        if (GB.browser.msie) {
            elem.setAttribute('unselectable', 'on');
            elem.setAttribute('contentEditable', 'false');
        } else {
            elem.onselectstart = new Function('return false');
        }
    },

    createEditorElement : function (container, toolbar) {
        var child = container.firstChild,
            self = this,
            i, id, tab, tabId, editArea, done = false, frameEl = false, tryScroll, textContent, node,

            onClickEventHandler = function () {
                self.switchEditorMode(this.getAttribute('mode'));
            },
            onMouseDownEventHandler = function (evt) {
                self.resizeStart(evt);
            },
            modeOnMouseDownEventHandler = function (evt) {
                self.backupRange();
                self.stopEvent(evt);
            },

            pNode = self.cheditor.textarea.parentNode,
            nNode = self.cheditor.textarea.nextSibling;

        if (!child) {
            return;
        }

        do {
            id = child.getAttribute('id');
            switch (id) {
                case 'toolbar' :
                    self.showToolbar(toolbar, child);
                    self.cheditor.toolbarWrapper = child;
                    break;
                case 'viewMode' :
                    self.cheditor[id] = child;
                    self.cheditor.mode = 'rich';

                    if (child.hasChildNodes()) {
                        tab = child.childNodes;
                        self.cheditor.modetab = {};
                        for (i = 0; i < tab.length; i++) {
                            tabId = tab[i].getAttribute('id');
                            if (!tabId) {
                                continue;
                            }
                            if ((tabId === 'code' && self.config.useSource === false) ||
                                (tabId === 'preview' && self.config.usePreview === false)) {
                                tab[i].style.display = 'none';
                                tab[i].removeAttribute('id');
                                continue;
                            }

                            tab[i].setAttribute('mode', tabId);
                            tab[i].onclick = onClickEventHandler;
                            tab[i].onmousedown = modeOnMouseDownEventHandler;
                            tab[i].removeAttribute('id');
                            self.cheditor.modetab[tabId] = tab[i];
                            self.unselectionElement(tab[i]);
                        }
                    }
                    break;
                case 'editWrapper' :
                    node = child.firstChild;
                    while (node) {
                        if (node.nodeName.toLowerCase() === 'iframe') {
                            editArea = node;
                        } else if (node.nodeName.toLowerCase() === 'textarea') {
                            textContent = node;
                        }
                        node = node.nextSibling;
                    }

                    editArea.style.height = self.config.editorHeight;
                    editArea.style.backgroundColor = this.config.editorBgColor;

                    self.cheditor.editArea = editArea;
                    self.cheditor.editWrapper = child;
                    self.cheditor.textContent = textContent;
                    break;
                case 'modifyBlock' :
                    self.cheditor.editBlock = child;
                    break;
                case 'tagPath' :
                    if (self.config.showTagPath) {
                        self.cheditor.tagPath = child.firstChild;
                        child.style.display = 'block';
                    }
                    break;
                case 'resizeBar' :
                    self.cheditor.resizeBar = child;
                    child.onmousedown = onMouseDownEventHandler;
                    self.unselectionElement(child);
                    break;
                default : break;
            }
            child.removeAttribute('id');
            child = child.nextSibling;
        } while (child);

        if (!nNode) {
            pNode.appendChild(container);
        } else {
            pNode.insertBefore(container, nNode);
        }

        function ready() {
            if (done) {
                return;
            }
            done = true;
        }

        if (GB.browser.msie) {
            frameEl = window.frameElement !== null;
            if (document.documentElement.doScroll && !frameEl) {
                tryScroll = function () {
                    if (done) {
                        return;
                    }
                    try {
                        document.documentElement.doScroll('left');
                        ready();
                    } catch (e) {
                        setTimeout(tryScroll, 10);
                    }
                };
                tryScroll();
            }
            self.addEvent(document, 'readystatechange', function () {
                if (document.readyState === 'complete') {
                    ready();
                }
            });
        } else {
            self.addEvent(document, 'DOMContentLoaded', function () {
                ready();
            });
        }

        container.style.width = self.config.editorWidth;
        self.cheditor.container = container;
    },
/*
    loadTemplate : function (xmlDoc) {
        var self = this,
            tmpl = xmlDoc.getElementsByTagName('Template').item(0),
            toolbar = tmpl.getElementsByTagName('Toolbar').item(0),
            cdata = tmpl.getElementsByTagName('Container').item(0).getElementsByTagName('Html').item(0),
            html = self.getCDATASection(cdata),
            tmpDiv = document.createElement('div'),
            container, popupWindow, modalFrame, dragHandle;

        if (!(tmpl.getElementsByTagName('Image').item(0).getAttribute('file'))) {
            throw '툴바 아이콘 이미지 파일 이름이 정의되지 않았습니다.';
        }

        tmpDiv.innerHTML = html;

        container = tmpDiv.firstChild;
        self.createEditorElement(container, toolbar);

        cdata = tmpl.getElementsByTagName('PopupWindow').item(0).getElementsByTagName('Html').item(0);
        html = self.getCDATASection(cdata);
        tmpDiv.innerHTML = html;

        popupWindow = tmpDiv.firstChild;
        self.cheditor.popupElem = popupWindow;

        dragHandle = popupWindow.firstChild;
        self.cheditor.dragHandle = dragHandle;
        self.cheditor.popupTitle = dragHandle.getElementsByTagName('label')[0];
        self.cheditor.popupFrameWrapper = dragHandle.nextSibling;

        container.appendChild(popupWindow);

        modalFrame = document.createElement('div');
        modalFrame.className = 'cheditor-modalPopupTransparent';
        self.cheditor.modalBackground = modalFrame;
        self.cheditor.modalBackground.id = 'popupModalBackground';
        self.cheditor.modalBackground.className = 'cheditor-popupModalBackground';
        container.parentNode.insertBefore(modalFrame, container);

        self.cheditor.htmlEditable = document.createElement('iframe');
        self.cheditor.htmlEditable.style.display = 'none';
        self.cheditor.htmlEditable.style.width = '1px';
        self.cheditor.htmlEditable.style.height = '1px';
        self.cheditor.htmlEditable.style.visibility = 'hidden';
        container.appendChild(self.cheditor.htmlEditable);
    },
*/

    loadTemplate : function (xmlDoc) {
        var cdata, container, dragHandle, html, modalFrame, popupWindow, tmpDiv, tmpl, toolbar;
        tmpl = xmlDoc.getElementsByTagName('Template').item(0);
        if (!tmpl) {
            throw 'Template 노드를 설정할 수 없습니다.';
        }
        cdata = tmpl.getElementsByTagName('Container').item(0).getElementsByTagName('Html').item(0);
        if (!cdata) {
            throw 'XML CDATA 오류';
        }
        html = this.getCDATASection(cdata);
        tmpDiv = document.createElement('div');
        tmpDiv.innerHTML = html;
        container = tmpDiv.firstChild;

        toolbar = tmpl.getElementsByTagName('Toolbar').item(0);
        this.createEditorElement(container, toolbar);

        cdata = tmpl.getElementsByTagName('PopupWindow').item(0).getElementsByTagName('Html').item(0);
        if (!cdata) {
            throw 'XML CDATA 오류';
        }
        html = this.getCDATASection(cdata);
        tmpDiv.innerHTML = html;
        popupWindow = tmpDiv.firstChild;
        this.cheditor.popupElem = popupWindow;

        dragHandle = popupWindow.firstChild;
        this.cheditor.dragHandle = dragHandle;
        this.cheditor.popupTitle = dragHandle.getElementsByTagName('label')[0];
        this.cheditor.popupFrameWrapper = dragHandle.nextSibling;
        container.appendChild(popupWindow);

        modalFrame = document.createElement('div');
        modalFrame.className = 'cheditor-modalPopupTransparent';
        this.cheditor.modalBackground = modalFrame;
        this.cheditor.modalBackground.id = 'popupModalBackground';
        this.cheditor.modalBackground.className = 'cheditor-popupModalBackground';
        container.parentNode.insertBefore(modalFrame, container);

        this.cheditor.htmlEditable = document.createElement('iframe');
        this.cheditor.htmlEditable.style.display = 'none';
        this.cheditor.htmlEditable.style.width = '1px';
        this.cheditor.htmlEditable.style.height = '1px';
        this.cheditor.htmlEditable.style.visibility = 'hidden';
        container.appendChild(this.cheditor.htmlEditable);
    },

    imageEvent : function (img, action) {
        var self = this,
            onMouseUpEventHandler = function () {
                self.cheditor.editBlock.style.display = 'block';
                self.modifyImage(this);
            },
            onClickEventHandler = function () {
                self.cheditor.editBlock.style.display = 'block';
                self.modifyImage(this);
            };

        if (GB.browser.msie) {
            if (!action) {
                img.onmouseup = null;
                return;
            }
            (function () {
                img.onmouseup = onMouseUpEventHandler;
            })();
        } else {
            if (!action) {
                self.removeEvent(img, 'click', onClickEventHandler);
                return;
            }
            this.addEvent(img, 'click', onClickEventHandler);
        }
    },

    setImageEvent : function (action) {
        var images = this.doc.images, i,
            len = images.length;

        for (i = 0; i < len; i++) {
            if (/icons\/em\//.test(images[i].src)) {
                continue;
            }
            this.imageEvent(images[i], action);
        }
    },

    run : function () {
        var self = this,
            showEditor = function () {
                var grayImage = null;

                if (!self.resetDoc()) {
                    return;
                }
                self.editAreaFocus();
                self.setEditorEvent();

                if (GB.browser.msie && GB.browser.version > 8 || GB.browser.a) {
                    grayImage = new Image();
                    grayImage.onload = function () {
                        self.cheditor.toolbarGrayscale = self.makeToolbarGrayscale(this);
                        self.toolbarUpdate();
                    };
                    grayImage.src = self.toolbar.icon;
                    grayImage.style.width = '750px'; grayImage.style.height = '16px';
                } else {
                    self.cheditor.toolbarGrayscale = null;
                    self.toolbarUpdate();
                }
                self.setImageEvent(true);
                self.removeEvent(document, self.cheditor.id, showEditor);
            };

        try {
            this.setFolderPath();
            this.checkInputForm();
        }
        catch (e) {
            alert(e.toString());
            return;
        }

        self.setDefaultCss({css: 'ui.css', doc: window.document});

        if (this.W3CRange) {
            this.addEvent(document, this.cheditor.id, showEditor);
        } else {
            document.documentElement.loadEvent = 0;
            document.documentElement.attachEvent('onpropertychange', function (evt) {
                if (evt.propertyName === 'loadEvent') {
                    showEditor();
                }
            });
        }

        this.initTemplate();
    },

    fullScreenMode : function () {
        var self = this,
            container = self.cheditor.container,
            windowSize, height,
            child = container.firstChild,
            except = 0,
            editorHeight = parseInt(self.config.editorHeight, 10),

            containerReSize = function () {
                windowSize = self.getWindowSize();
                container.style.width = windowSize.width + 'px';
                if (self.cheditor.mode === 'code') {
                    self.resizeTextContent();
                    height = (windowSize.height - except - 6 - parseInt(self.cheditor.textContent.getAttribute('xbar-height'), 10));
                    self.cheditor.textContent.style.height = height + 'px';
                }
                self.cheditor.editArea.style.height = (windowSize.height - except - 6) + 'px';
            },
            onMouseDownEventHandler = function (evt) {
                self.resizeStart(evt);
            };

        self.editAreaFocus();
        self.boxHideAll();
        self.cheditor.editArea.style.visibility = 'hidden';

        if (!self.setFullScreenMode) {
            container.className = 'cheditor-container-fullscreen';

            if (GB.browser.msie && GB.browser.version < 7) {
                self.cheditor.fullScreenFlag = document.createElement('span');
                self.cheditor.fullScreenFlag.style.display = 'none';
                container.parentNode.insertBefore(self.cheditor.fullScreenFlag, container);
                document.body.insertBefore(container, document.body.firstChild);
            }

            while (child) {
                if (child.className !== 'cheditor-editarea-wrapper' &&
                    child.className !== 'cheditor-popup-window' &&
                    child.className !== '') {
                    except += child.offsetHeight;
                }
                child = child.nextSibling;
            }

            (function () {
                window.onresize = containerReSize;
            })();

            containerReSize();
            self.cheditor.resizeBar.onmousedown = null;
            self.cheditor.resizeBar.className = 'cheditor-resizebar-off';
        } else {
            window.onresize = null;
            container.removeAttribute('style');
            container.className = 'cheditor-container';
            container.style.width = self.config.editorWidth;

            if (self.cheditor.mode === 'code') {
                height = editorHeight - parseInt(self.cheditor.textContent.getAttribute('xbar-height'), 10);
                self.cheditor.textContent.style.height = height + 'px';
            } else {
                self.cheditor.editArea.style.height = editorHeight + 'px';
            }

            (function () {
                self.cheditor.resizeBar.onmousedown = onMouseDownEventHandler;
            })();
            self.cheditor.resizeBar.className = 'cheditor-resizebar';

            if (GB.browser.msie && GB.browser.version < 7) {
                self.cheditor.fullScreenFlag.parentNode.replaceChild(container, self.cheditor.fullScreenFlag);
            }
        }

        self.cheditor.editArea.style.visibility = 'visible';
        self.setFullScreenMode = !(self.setFullScreenMode);
        self.editAreaFocus();
    },

    showPulldown : function (cmd, btn) {
        switch (cmd) {
            case 'FontName' :
                this.showFontTypeMenu(btn);
                break;
            case 'FontSize' :
                this.showFontSizeMenu(btn);
                break;
            case 'FormatBlock' :
                this.showFormatBlockMenu(btn);
                break;
            case 'ForeColor' :
            case 'BackColor' :
                this.showColorMenu(btn);
                break;
            case 'TextBlock' :
                this.showTextBlockMenu(btn);
                break;
            case 'LineHeight' :
                this.showLineHeightMenu(btn);
                break;
            case 'OrderedList' :
            case 'UnOrderedList' :
                this.showOrderedListMenu(btn);
        }
    },

    setPulldownClassName : function (labels, pNode) {
        var i = 0, label;
        for (; i < labels.length; i++) {
            label = labels[i];
            if (label.getAttribute('name') === pNode.firstChild.firstChild.firstChild.nodeValue) {
                label.parentNode.style.backgroundImage = 'url(' + this.config.editorPath + 'icons/checked.png)';
                label.parentNode.style.backgroundPosition = '0 center';
                label.parentNode.style.backgroundRepeat = 'no-repeat';
            } else {
                label.parentNode.style.backgroundImage = '';
            }
            label.parentNode.className = 'cheditor-pulldown-mouseout';
        }
    },

    showOrderedListMenu : function (pNode) {
        var self = this,
            menu = pNode.getAttribute('name'),
            elem = self.pulldown[menu];

        (function () {
            if (!elem) {
                var cmd = (menu === 'UnOrderedListCombo') ? 'InsertUnOrderedList' : 'InsertOrderedList',
                    outputHtml = document.createElement('div'),
                    onClickEventHandler = function () {
                        self.doCmdPopup(cmd, this.id, self.toolbar[menu].prev.checked);
                    },
                    onMouseOverEventHandler = function () {
                        self.pulldownMouseOver(this);
                    },
                    onMouseOutEventHandler = function () {
                        self.pulldownMouseOut(this);
                    },
                    list = (cmd === 'InsertUnOrderedList') ? GB.listStyle.unOrdered : GB.listStyle.ordered,
                    i, div, label, li, ol;

                for (i in list) {
                    if (list.hasOwnProperty(i)) {
                        div = document.createElement('div');
                        label = document.createElement('label');
                        div.id = i;

                        (function () {
                            div.onclick = onClickEventHandler;
                            div.onmouseover = onMouseOverEventHandler;
                            div.onmouseout = onMouseOutEventHandler;
                        })();

                        self.pulldownMouseOut(div);

                        label.style.fontFamily = 'verdana';
                        label.style.textAlign = 'center';
                        label.style.width = '15px';
                        label.setAttribute('name', i);

                        li = document.createElement('li');
                        li.appendChild(document.createTextNode(list[i]));

                        ol = document.createElement('ul');
                        ol.style.width = '90px';
                        ol.style.padding = '0 15px';
                        ol.style.margin = '0px';
                        ol.style.listStyleType = i;
                        ol.style.cursor = 'default';
                        ol.style.textAlign = 'left';
                        ol.appendChild(li);
                        label.appendChild(ol);
                        div.appendChild(label);
                        outputHtml.appendChild(div);
                    }
                }
                self.createWindow(110, outputHtml);
                self.createPulldownFrame(outputHtml, menu);
            }
        })();

        self.windowPos(pNode, menu);
        self.displayWindow(pNode, menu);
    },

    showColorMenu : function (pNode) {
        var menu = pNode.getAttribute('name'),
            elem = this.pulldown[menu],
            selectedColor = this.colorConvert(pNode.lastChild.style.backgroundColor, 'hex'),
            i, len, nodes, node, outputHtml;

        if (!elem) {
            outputHtml = this.setColorTable(menu);
            this.createWindow(220, outputHtml);
            this.createPulldownFrame(outputHtml, menu);
            elem = this.pulldown[menu];
            elem.firstChild.className = 'cheditor-pulldown-color-container';
        }

        this.toolbar[menu].colorNode.selectedValue.style.backgroundColor = selectedColor;
        this.toolbar[menu].colorNode.colorPicker.hidePicker();
        this.toolbar[menu].colorNode.colorPicker.fromString(selectedColor);
        this.toolbar[menu].colorNode.showPicker = false;

        nodes = elem.getElementsByTagName('span');
        len = nodes.length;

        for (i = 0; i < len; i++) {
            node = nodes[i];
            node.style.backgroundImage = '';
            if (node.id && node.id.toLowerCase() === selectedColor.toLowerCase()) {
                node.style.backgroundImage = 'url("' + this.config.iconPath + '/color_picker_tick.png")';
                node.style.backgroundRepeat = 'no-repeat';
                node.style.backgroundPosition = 'center center';

            }
        }
        this.toolbar[menu].colorNode.selectedValue.style.backgroundImage = 'url("' +
            this.config.iconPath + '/color_picker_tick.png")';
        this.toolbar[menu].colorNode.selectedValue.style.backgroundRepeat = 'no-repeat';
        this.toolbar[menu].colorNode.selectedValue.style.backgroundPosition = 'center center';
        this.windowPos(pNode, menu);
        this.displayWindow(pNode, menu);
    },

    showFontTypeMenu : function (pNode) {
        var self = this,
            menu = pNode.getAttribute('name'),
            elem = self.pulldown[menu];

        (function () {
            if (!elem) {
                var fonts = null, type, i, div, label,
                    outputHtml = self.doc.createElement('div'),
                    onClickEventHandler = function () {
                        self.doCmdPopup(menu, this.id);
                    },
                    onMouseOverEventHandler = function () {
                        self.pulldownMouseOver(this);
                    },
                    onMouseOutEventHandler = function () {
                        self.pulldownMouseOut(this);
                    };

                for (type in GB.fontName) {
                    if (GB.fontName.hasOwnProperty(type)) {
                        fonts = GB.fontName[type];
                        for (i = 0; i < fonts.length; i++) {
                            div = self.doc.createElement('div');
                            label = self.doc.createElement('label');
                            div.id = fonts[i];
                            (function () {
                                div.onclick = onClickEventHandler;
                                div.onmouseover = onMouseOverEventHandler;
                                div.onmouseout = onMouseOutEventHandler;
                            })();
                            label.style.fontFamily = fonts[i];//(type !== 'kr') ? fonts[i] : this.config.editorFontName;
                            label.appendChild(self.doc.createTextNode(fonts[i]));
                            label.setAttribute('name', fonts[i]);
                            div.appendChild(label);
                            outputHtml.appendChild(div);
                        }
                    }
                }
                self.createWindow(155, outputHtml);
                self.createPulldownFrame(outputHtml, menu);
                elem = self.pulldown[menu];
            }
        })();

        self.setPulldownClassName(elem.firstChild.getElementsByTagName('LABEL'), pNode);
        self.windowPos(pNode, menu);
        self.displayWindow(pNode, menu);
    },

    showFormatBlockMenu : function (pNode) {
        var self = this,
            menu = pNode.getAttribute('name'),
            elem = self.pulldown[menu];

        (function () {
            if (!elem) {
                var para, label, fontSize, div,
                    outputHtml = document.createElement('div'),
                    onClickEventHandler = function () {
                        self.doCmdPopup('FormatBlock', '<' + this.id + '>');
                    },
                    onMouseOverEventHandler = function () {
                        self.pulldownMouseOver(this);
                    },
                    onMouseOutEventHandler = function () {
                        self.pulldownMouseOut(this);
                    };

                for (para in GB.formatBlock) {
                    if (GB.formatBlock.hasOwnProperty(para)) {
                        div = document.createElement('div');
                        div.id = para;
                        (function () {
                            div.onclick = onClickEventHandler;
                            div.onmouseover = onMouseOverEventHandler;
                            div.onmouseout = onMouseOutEventHandler;
                        })();
                        label = document.createElement('label');
                        if (para.match(/H[123456]/)) {
                            fontSize = {'H1': '2em','H2': '1.5em','H3': '1.17em','H4': '1em','H5': '0.83em','H6': '0.75em'};
                            label.style.fontWeight = 'bold';
                            label.style.fontSize = fontSize[para];
                            label.style.lineHeight = 1.4;
                        } else if (para === 'ADDRESS') {
                            label.style.fontStyle = 'italic';
                        }

                        label.appendChild(document.createTextNode(GB.formatBlock[para]));
                        div.appendChild(label);
                        label.setAttribute('name', GB.formatBlock[para]);
                        outputHtml.appendChild(div);

                    }
                }
                self.createWindow(150, outputHtml);
                self.createPulldownFrame(outputHtml, menu);
                elem = self.pulldown[menu];
            }
        })();

        self.setPulldownClassName(elem.firstChild.getElementsByTagName('label'), pNode);
        self.windowPos(pNode, menu);
        self.displayWindow(pNode, menu);
    },

    showFontSizeMenu : function (pNode) {
        var self = this,
            menu = pNode.getAttribute('name'),
            elem = self.pulldown[menu];

        (function () {
            if (!elem) {
                var size, div, label, text, i,
                    value = GB.fontSize[self.config.fontSizeValue],
                    len = value.length,
                    outputHtml = document.createElement('div'),
                    onClickEventHandler = function (e) {
                        self.stopEvent(e);
                        self.doCmdPopup(menu, this.id);
                    },
                    onMouseOverEventHandler = function () {
                        self.pulldownMouseOver(this);
                    },
                    onMouseOutEventHandler = function () {
                        self.pulldownMouseOut(this);
                    };

                for (i = 0; i < len; i++) {
                    size = value[i];
                    div = document.createElement('div');
                    label = document.createElement('label');
                    text = size > 48 ? '가' : (size > 28 ? '가나다' : '가나다라');
                    size = size + self.config.fontSizeValue;
                    div.id = size;
                    (function () {
                        div.onclick = onClickEventHandler;
                        div.onmouseover = onMouseOverEventHandler;
                        div.onmouseout = onMouseOutEventHandler;
                    })();
                    div.style.fontSize = size;
                    label.style.fontFamily = self.config.editorFontName;
                    label.setAttribute('name', size);
                    label.appendChild(document.createTextNode(text + '(' + size + ')'));
                    div.appendChild(label);
                    outputHtml.appendChild(div);
                }
                self.createWindow(350, outputHtml);
                outputHtml.style.height = '300px';
                outputHtml.style.overflow = 'auto';
                self.createPulldownFrame(outputHtml, menu);
                elem = self.pulldown[menu];
            }
        })();

        self.setPulldownClassName(elem.firstChild.getElementsByTagName('LABEL'), pNode);
        self.windowPos(pNode, menu);
        self.displayWindow(pNode, menu);
    },

    showLineHeightMenu : function (pNode) {
        var self = this,
            menu = pNode.getAttribute('name'),
            elem = self.pulldown[menu];

        (function () {
            if (!elem) {
                var i, div, label, text,
                    outputHtml = document.createElement('div'),
                    onClickEventHandler = function () {
                        self.doCmdPopup('LineHeight', this.id);
                    },
                    onMouseOverEventHandler = function () {
                        self.pulldownMouseOver(this);
                    },
                    onMouseOutEventHandler = function () {
                        self.pulldownMouseOut(this);
                    };

                for (i in GB.lineHeight) {
                    if (!(GB.lineHeight.hasOwnProperty(i))) {
                        continue;
                    }
                    if (!GB.lineHeight[i]) {
                        break;
                    }
                    div = document.createElement('div');
                    label = document.createElement('label');
                    text = i;

                    div.id = GB.lineHeight[i];
                    (function () {
                        div.onclick = onClickEventHandler;
                        div.onmouseover = onMouseOverEventHandler;
                        div.onmouseout = onMouseOutEventHandler;
                    })();

                    label.style.fontFamily = self.config.editorFontName;
                    label.setAttribute('name', GB.lineHeight[i]);
                    label.appendChild(document.createTextNode(text));
                    div.appendChild(label);
                    outputHtml.appendChild(div);
                }
                self.createWindow(100, outputHtml);
                self.createPulldownFrame(outputHtml, menu);
                elem = self.pulldown[menu];
            }
        })();

        self.setPulldownClassName(elem.firstChild.getElementsByTagName('LABEL'), pNode);
        self.windowPos(pNode, menu);
        self.displayWindow(pNode, menu);
    },

    showTextBlockMenu : function (pNode) {
        var self = this,
            menu = pNode.getAttribute('name'),
            elem = self.pulldown[menu];

        (function () {
            if (!elem) {
                var i, wrapper, div, label,
                    outputHtml = document.createElement('div'),
                    onClickEventHandler = function () {
                        self.boxStyle(this);
                    },
                    onMouseOverEventHandler = function () {
                        this.className = 'cheditor-pulldown-textblock-over';
                    },
                    onMouseOutEventHandler = function () {
                        this.className = 'cheditor-pulldown-textblock-out';
                    },
                    quote = GB.textBlock;

                for (i = 0; i < quote.length; i++) {
                    wrapper = document.createElement('div');
                    div = document.createElement('div');
                    (function () {
                        div.onclick = onClickEventHandler;
                        wrapper.onmouseover = onMouseOverEventHandler;
                        wrapper.onmouseout = onMouseOutEventHandler;
                    })();
                    wrapper.className = 'cheditor-pulldown-textblock-out';
                    div.id = i;
                    div.style.border = quote[i][0];
                    div.style.backgroundColor = quote[i][1];
                    div.style.fontFamily = self.config.editorFontName;

                    label = document.createElement('label');
                    label.appendChild(document.createTextNode('가나다라 ABC'));
                    div.appendChild(label);
                    wrapper.appendChild(div);
                    outputHtml.appendChild(wrapper);
                }
                self.createWindow(160, outputHtml);
                self.createPulldownFrame(outputHtml, menu);
                elem = self.pulldown[menu];
                elem.firstChild.className = 'cheditor-pulldown-textblock-container';
            }
        })();

        self.windowPos(pNode, menu);
        self.displayWindow(pNode, menu);
    },

    createPulldownFrame : function (contents, id) {
        var div = document.createElement('div');
        div.className = 'cheditor-pulldown-frame';
        div.appendChild(contents);
        this.pulldown[id] = div;
        this.cheditor.container.firstChild.appendChild(div);
    },

    setDefaultCss : function (ar) {
        var cssFile, head, found = false, children, i, href, css;

        ar = ar || {css: 'editarea.css', doc: this.doc};
        cssFile = this.config.cssPath + ar.css;
        head = ar.doc.getElementsByTagName('head')[0];

        if (this.undefined(head)) {
            return;
        }

        if (head.hasChildNodes()) {
            children = head.childNodes;
            for (i = 0; i < children.length; i++) {
                if (children[i].nodeName.toLowerCase() === 'link') {
                    href = children[i].getAttribute('href');
                    if (href && href === cssFile) {
                        found = true;
                        break;
                    }
                }
            }
        }

        if (!found) {
            css = head.appendChild(ar.doc.createElement('link'));
            css.setAttribute('type', 'text/css');
            css.setAttribute('rel', 'stylesheet');
            css.setAttribute('media', 'all');
            css.setAttribute('href', this.config.cssPath + ar.css);
        }
    },

    setEditorEvent : function () {
        var self = this,
            onKeyDownEventHandler = function (evt) {
                if (self.cheditor.mode === 'preview') {
                    self.stopEvent(evt);
                    return;
                }
                self.doOnKeyDown(evt);
            },
            onKeyPressEventHandler = function (evt) {
                if (self.cheditor.mode === 'preview') {
                    self.stopEvent(evt);
                    return;
                }
                self.doOnKeyPress(evt);
            },
            onKeyUpEventHandler = function (evt) {
                if (self.cheditor.mode === 'preview') {
                    self.stopEvent(evt);
                    return;
                }
                self.doOnKeyUp(evt);
            },
            onMouseUpEventHandler = function (evt) {
                if (self.cheditor.mode === 'rich') {
                    if (evt.clientX <= self.doc.body.offsetWidth) {
                        self.doEditorEvent(evt);
                    } else {
                        self.restoreRange();
                    }
                    return;
                }
                if (self.cheditor.mode === 'preview') {
                    self.stopEvent(evt);
                }
            },
            onMouseDownEventHandler = function (evt) {
                if (self.cheditor.mode === 'rich') {
                    if (evt.clientX <= self.doc.body.offsetWidth) {
                        // self.clearSelection();
                    } else {
                        self.backupRange();
                    }
                    self.boxHideAll();
                    return;
                }
                if (self.cheditor.mode === 'preview') {
                    self.stopEvent(evt);
                }
            };

        (function () {
            self.addEvent(self.doc, 'keydown', onKeyDownEventHandler);
            self.addEvent(self.doc, 'keypress', onKeyPressEventHandler);
            self.addEvent(self.doc, 'keyup', onKeyUpEventHandler);
            self.addEvent(self.doc, 'mouseup', onMouseUpEventHandler);
            self.addEvent(self.doc, 'mousedown', onMouseDownEventHandler);
        })();
    },

    addEvent : function (evTarget, evType, evHandler) {
        if (evTarget.addEventListener) {
            evTarget.addEventListener(evType, evHandler, false);
        } else {
            evTarget.attachEvent('on' + evType, evHandler);
        }
    },

    removeEvent : function (elem, ev, func) {
        if (elem.removeEventListener) {
            elem.removeEventListener(ev, func, false);
        } else {
            elem.detachEvent('on' + ev, func);
        }
    },

    stopEvent : function (ev) {
        if (ev && ev.preventDefault) {
            ev.preventDefault();
            ev.stopPropagation();
        } else {
            ev = ev || window.event;
            ev.cancelBubble = true;
            ev.returnValue = false;
        }
    },

    toolbarButtonOut : function (elemButton, nTop) {
        elemButton.style.top = -nTop + 'px';
    },

    toolbarButtonOver : function (elemButton) {
        var nTop = elemButton.style.top.substring(0, elemButton.style.top.length - 2);
        elemButton.style.top = nTop - 22 + 'px';
    },

    getElement : function (elem, tag) {
        if (!elem || !tag) {
            return null;
        }
        while (elem && elem.nodeName.toLowerCase() !== tag.toLowerCase()) {
            if (elem.nodeName.toLowerCase() === 'body') {
                break;
            }
            elem = elem.parentNode;
        }
        return elem;
    },

    hyperLink : function (href, target, title) {
        var self = this,
            links = null, i,
            createLinks = function () {
                var range = self.restoreRange(),
                    selectedLinks = [],
                    linkRange = self.createRange(), selection = null, container = null, k;

                self.backupRange(range);

                if (self.W3CRange) {
                    self.doc.execCommand("CreateLink", false, href);
                    selection = self.getSelection();

                    for (i = 0; i < selection.rangeCount; ++i) {
                        range = selection.getRangeAt(i);
                        container = range.commonAncestorContainer;

                        if (self.getSelectionType(range) === GB.selection.text) {
                            container = container.parentNode;
                        }

                        if (container.nodeName.toLowerCase() === 'a') {
                            selectedLinks.push(container);
                        } else {
                            links = container.getElementsByTagName('a');
                            for (k = 0; k < links.length; ++k) {
                                linkRange.selectNodeContents(links[k]);
                                if (linkRange.compareBoundaryPoints(range.END_TO_START, range) < 1 &&
                                    linkRange.compareBoundaryPoints(range.START_TO_END, range) > -1)
                                {
                                    selectedLinks.push(links[k]);
                                }
                            }
                        }
                    }
                    linkRange.detach();
                } else {
                    range = self.doc.selection.createRange();
                    range.execCommand("UnLink", false);
                    range.execCommand("CreateLink", false, href);

                    switch (self.getSelectionType(range)) {
                        case GB.selection.text :
                            container = range.parentElement();
                            break;
                        case GB.selection.element :
                            container = range.item(0).parentNode;
                            break;
                        default : return null;
                    }

                    if (container.nodeName.toLowerCase() === 'a') {
                        selectedLinks.push(container);
                    } else {
                        links = container.getElementsByTagName('a');
                        for (i = 0; i < links.length; ++i) {
                            linkRange.moveToElementText(links[i]);
                            if (linkRange.compareEndPoints("StartToEnd", range) > -1 &&
                                linkRange.compareEndPoints("EndToStart", range) < 1)
                            {
                                selectedLinks.push(links[i]);
                            }
                        }
                    }
                }
                return selectedLinks;
            };

        this.editArea.focus();
        links = createLinks();
        if (links) {
            for (i = 0; i < links.length; ++i) {
                if (target) {
                    links[i].setAttribute("target", target);
                }
                if (title) {
                    links[i].setAttribute("title", title);
                }
            }
        }
    },

    getOffsetBox : function (el) {
        var box = el.getBoundingClientRect(),
            doc = this.doc.documentElement,
            body = this.doc.body,
            scrollTop = doc.scrollTop || body.scrollTop,
            scrollLeft = doc.scrollLeft || body.scrollLeft,
            clientTop = doc.clientTop || body.clientTop || 0,
            clientLeft = doc.clientLeft || body.clientLeft || 0,
            top = box.top + scrollTop - clientTop,
            left = box.left + scrollLeft - clientLeft;

        return { top: Math.round(top), left: Math.round(left) };
    },

    makeSpacerElement : function () {
        var elem,
            para = this.doc.createElement('p');

        if (GB.browser.msie && GB.browser.version < 11 && GB.browser.version > 8) {
            elem = this.doc.createComment(this.cheditor.bogusSpacerName);
        } else if (GB.browser.msie_c) {
            elem = this.createNbspTextNode();
        } else {
            elem = this.doc.createElement('br');
            elem.className = this.cheditor.bogusSpacerName;
        }

        para.appendChild(elem);
        return para;
    },

    boxStyle : function (el) {
        var range, elem, ctx, textRange, frag, pNode,
            blockQuote = this.doc.createElement('blockquote'),
            para = null;

        this.editAreaFocus();
        range = this.range || this.getRange()

        blockQuote.style.border = GB.textBlock[el.id][0];
        blockQuote.style.backgroundColor = GB.textBlock[el.id][1];
        blockQuote.style.padding = '5px 10px';

        if (!this.W3CRange) {
            ctx = range.htmlText;
            blockQuote.innerHTML = ctx || '&nbsp;';
            range.select();
            this.insertHTML(blockQuote);
            textRange = this.getRange();
            elem = range.parentElement();
            textRange.moveToElementText(elem);
            textRange.collapse(false);
            textRange.select();
        } else {
            try {
                frag = range.extractContents();
                if (!frag.firstChild) {
                    para = this.makeSpacerElement();
                    blockQuote.appendChild(para);
                } else {
                    blockQuote.appendChild(frag);
                }

                range.insertNode(blockQuote);
                pNode = blockQuote.parentNode;

                while (pNode && pNode.nodeName.toLowerCase() !== 'body') {
                    if (pNode.nodeName.toLowerCase() === 'p' || pNode.nodeName.toLowerCase() === 'div') {
                        pNode.parentNode.insertBefore(blockQuote, pNode.nextSibling);
                        break;
                    }
                    pNode = pNode.parentNode;
                }
                this.placeCaretAt(para || blockQuote, false);
            } catch (ignore) {
                // --
            }
        }
        this.boxHideAll();
    },

    insertFlash : function (elem) {
        var embed = null, pos, str, obj, child, movieHeight, params = [], movieWidth, i,
            div = this.doc.createElement('div');

        this.editAreaFocus();
        this.restoreRange();

        if (typeof elem === 'string') {
            elem = this.trimSpace(elem);
            pos = elem.toLowerCase().indexOf('embed');

            if (pos !== -1) {
                str = elem.substr(pos);
                pos = str.indexOf('>');
                div.innerHTML = '<' + str.substr(0, pos) + '>';
                embed = div.firstChild;
            } else {
                div.innerHTML = elem;
                obj = div.getElementsByTagName('object')[0];

                if (obj && obj.hasChildNodes()) {
                    child = obj.firstChild;
                    movieWidth  = (isNaN(obj.width) !== true) ? obj.width : 320;
                    movieHeight = (isNaN(obj.height) !== true) ? obj.height : 240;
                    do {
                        if ((child.nodeName.toLowerCase() === 'param') &&
                            (!this.undefined(child.name) && !this.undefined(child.value))) {
                            params.push({key: (child.name === 'movie') ? 'src' : child.name, val: child.value});
                        }
                        child = child.nextSibling;
                    } while (child);

                    if (params.length > 0) {
                        embed = this.doc.createElement('embed');
                        embed.setAttribute('width', movieWidth);
                        embed.setAttribute('height', movieHeight);
                        for (i = 0; i < params.length; i++) {
                            embed.setAttribute(params[i].key, params[i].val);
                        }
                        embed.setAttribute('type', 'application/x-shockwave-flash');
                    }
                }
            }

            if (embed) {
                if (this.W3CRange) {
                    this.insertNodeAtSelection(embed);
                } else {
                    this.doCmdPaste(embed.outerHTML);
                }
            }
        }
    },

    insertHtmlPopup : function (elem) {
        this.editAreaFocus();
        this.restoreRange();

        if (!this.W3CRange) {
            this.doCmdPaste((this.toType(elem) === 'string') ? elem : elem.outerHTML);
        } else {
            this.insertNodeAtSelection(elem);
        }
        this.clearStoredSelections();
    },

    insertHTML : function (html) {
        if (!this.W3CRange) {
            this.getRange().pasteHTML((this.toType(html) === 'string') ? html : html.outerHTML);
        } else {
            this.insertNodeAtSelection(html);
        }
    },

    placeCaretAt : function (elem, az) {
        var range = this.createRange(),
            selection = this.getSelection();

        if (this.undefined(az)) {
            az = false;
        }

        if (this.W3CRange) {
            selection.removeAllRanges();
            try {
                if (elem.lastChild && elem.lastChild.nodeName.toLowerCase() === 'br') {
                    az = true;
                }
                range.selectNodeContents(elem);
            } catch (e) {
                range.selectNode(elem);
            }

            range.collapse(az);

            try {
                selection.addRange(range);
            } catch (e) {
                this.placeCaretAt(this.doc.body, az);
            }
        } else if (elem.nodeType === GB.node.element) {
            range.moveToElementText(elem);
            range.collapse(az);
            range.select();
        }
    },

    selectNodeContents : function (node, pos) {
        var collapsed = !this.undefined(pos),
            selection = this.getSelection(),
            range = this.getRange();

        if (node.nodeType === GB.node.element) {
            range.selectNode(node);
            if (collapsed) {
                range.collapse(pos);
            }
        }
        selection.removeAllRanges();
        selection.addRange(range);
        return range;
    },

    insertNodeAtSelection : function (insertNode) {
        var range = this.getRange(),
            selection = this.getSelection(),
            frag = this.doc.createDocumentFragment(),
            lastNode = null,
            elem, commonAncestorContainer, startOffset, pNode, tmpWrapper;

        if (!range.collapsed) {
            range.deleteContents();
            range = this.getRange();
        }

        commonAncestorContainer = range.commonAncestorContainer;
        startOffset = range.startOffset;
        pNode = commonAncestorContainer;

        if (pNode.nodeType === GB.node.text) {
            pNode = pNode.parentNode;
        }

        this.removeBogusSpacer(pNode);

        if (typeof insertNode === 'string') {
            tmpWrapper = this.doc.createElement('div');
            tmpWrapper.innerHTML = insertNode;

            elem = tmpWrapper.firstChild;
            while (elem) {
                lastNode = frag.appendChild(elem);
                elem = tmpWrapper.firstChild;
            }
        } else {
            lastNode = frag.appendChild(insertNode);
        }

        if (startOffset < 1 && commonAncestorContainer.nodeType === GB.node.text) {
            commonAncestorContainer.parentNode.insertBefore(frag, commonAncestorContainer);
        } else {
            range.insertNode(frag);
        }

        if (lastNode) {
            range = range.cloneRange();
            range.setStartAfter(lastNode);
            selection.removeAllRanges();
            selection.addRange(range);
        }
        this.toolbarUpdate();
        return lastNode;
    },

    findBogusSpacer : function (elem, all) {
        var self = this, result = [];
        (function findBogusSpacer(elem) {
            var i = 0, node;
            for (; i < elem.childNodes.length; i++) {
                node = elem.childNodes[i];
                if ((node.nodeType === GB.node.element && node.className === self.cheditor.bogusSpacerName) ||
                    (node.nodeType === GB.node.comment && node.nodeValue === self.cheditor.bogusSpacerName)) {
                    result.push(node);
                }
                if (node.nodeType === GB.node.text && node.nodeValue === '\u00a0' && !node.nextSibling) {
                    result.push(node);
                }
                if (all) {
                    findBogusSpacer(node);
                }
            }
        })(elem);
        return result;
    },

    removeBogusSpacer : function (elem, removeEmpty, all) {
        var remove = this.findBogusSpacer(elem, all), i = 0;
        for (; i < remove.length; i++) {
            remove[i].parentNode.removeChild(remove[i]);
        }
        if (removeEmpty && !(elem.hasChildNodes())) {
            elem.parentNode.removeChild(elem);
        }
    },

    ieGetRangeAt : function (range) {
        var self = this, start = {}, end = {};

        function convert(result, bStart) {
            var point = range.duplicate(),
                span = self.doc.createElement('span'),
                parent = point.parentElement(),
                cursor = self.createRange(),
                compareStr = bStart ? 'StartToStart' : 'StartToEnd';

            point.collapse(bStart);
            parent.appendChild(span);
            cursor.moveToElementText(span);

            while (cursor.compareEndPoints(compareStr, point) > 0 && span.previousSibling) {
                parent.insertBefore(span, span.previousSibling);
                cursor.moveToElementText(span);
            }

            result.container = span.nextSibling || span.previousSibling;
            if (result.container === null) {
                result.container = span.parentNode;
            }
            parent.removeChild(span);
        }

        convert(start, true); convert(end, false);
        return { startContainer: start.container, endContainer: end.container };
    },

    applyLineHeight : function (opt) {
        var range = this.getRange(),
            isBlockElement = function (elem) {
                return GB.lineHeightBlockTags[elem.toLowerCase()];
            },
            getNextLeaf = function (elem, endLeaf, value) {
                while (!elem.nextSibling) {
                    elem = elem.parentNode;
                    if (!elem) {
                        return elem;
                    }
                }

                if (elem === endLeaf) {
                    return elem;
                }

                var leaf = elem.nextSibling;
                if (isBlockElement(leaf.nodeName)) {
                    leaf.style.lineHeight = value;
                }

                while (leaf.firstChild) {
                    leaf = leaf.firstChild;
                    if (leaf.nodeType !== GB.node.text && isBlockElement(leaf.nodeName)) {
                        leaf.style.lineHeight = value;
                    }
                }
                return leaf;
            },
            applyBlockElement = function (elem) {
                while (elem) {
                    if (elem.nodeName.toLowerCase() === "body") {
                        para = self.doc.createElement("p");
                        para.style.lineHeight = opt;

                        if (elem.firstChild) {
                            elem.insertBefore(para, elem.firstChild);
                        } else {
                            elem.appendChild(para);
                            break;
                        }

                        nextNode = para.nextSibling;
                        while (nextNode) {
                            if (isBlockElement(nextNode.nodeName)) {
                                break;
                            }
                            para.appendChild(nextNode);
                            nextNode = para.nextSibling;
                        }
                        break;
                    }

                    if (isBlockElement(elem.nodeName)) {
                        elem.style.lineHeight = opt;
                        break;
                    }
                    elem = elem.parentNode;
                }
            },
            ieRange, startContainer, endContainer, para, nextNode, startLeaf, endLeaf, nextLeaf;

        if (!this.W3CRange) {
            ieRange = this.ieGetRangeAt(range);
            startContainer = ieRange.startContainer;
            endContainer = ieRange.endContainer;
        } else {
            startContainer = range.startContainer;
            endContainer = range.endContainer;
        }

        if (!this.doc.body.hasChildNodes() || !startContainer || !endContainer) {
            throw "Object Error";
        }

        if (startContainer && startContainer.nodeName.toLowerCase() === "body") {
            startContainer = startContainer.firstChild;
        }

        try {
            if (startContainer === endContainer) {
                applyBlockElement(startContainer);
            } else {
                startLeaf = startContainer;
                while (startLeaf) {
                    if (startLeaf.nodeName.toLowerCase() === "body" || isBlockElement(startLeaf.nodeName)) {
                        break;
                    }
                    startLeaf = startLeaf.parentNode;
                }

                endLeaf = endContainer;
                while (endLeaf) {
                    if (endLeaf.nodeName.toLowerCase() === "body" || isBlockElement(endLeaf.nodeName)) {
                        break;
                    }
                    endLeaf = endLeaf.parentNode;
                }

                if (startLeaf === endLeaf) {
                    if (isBlockElement(startLeaf.nodeName)) {
                        startLeaf.style.lineHeight = opt;
                    } else {
                        para = this.doc.createElement("p");
                        para.style.lineHeight = opt;
                        startLeaf.insertBefore(para, startLeaf.firstChild);

                        nextNode = para.nextSibling;
                        while (nextNode) {
                            if (isBlockElement(nextNode.nodeName)) {
                                break;
                            }
                            para.appendChild(nextNode);
                            nextNode = para.nextSibling;
                        }
                    }
                } else {
                    applyBlockElement(startLeaf);
                    while (startLeaf) {
                        nextLeaf = getNextLeaf(startLeaf, endLeaf, opt);
                        if (startLeaf === endLeaf) {
                            break;
                        }
                        startLeaf = nextLeaf;
                    }
                }
            }
        } catch (ignore) {
            // --
        }
    },

    doInsertImage : function (imgs, para, insertSpace) {
        var range, i, count = 0, imgAttr, img, space, lastNode = null, pNode, div, selection,
            len = imgs.length, self = this,
            fragment = this.doc.createDocumentFragment();

        function checkPara(pNode) {
            var result = true, text;
            if (!pNode.hasChildNodes()) {
                return false;
            }
            self.getNodeTree(pNode, function (node) {
                if (!node.node || node === pNode) {
                    return;
                }
                if (node.type === GB.node.text) {
                    text = self.trimSpace(node.node.nodeValue);
                    if (!text || text === '\u00a0') {
                        result = false;
                    }
                } else if (node.type === GB.node.element
                    && (node.name === 'br' && node.node.className === self.cheditor.bogusSpacerName)) {
                    result = false;
                }
            });
            return result;
        }

        this.editAreaFocus();
        range = this.restoreRange();
        pNode = this.getRangeElement(range);
        if (!pNode) {
            return;
        }

        if (pNode.nodeName.toLowerCase() === 'body') {
            pNode = this.doc.createElement('p');
            if (this.W3CRange) {
                range.insertNode(pNode);
            } else {
                range.pasteHTML(pNode.outerHTML);
            }
        }

        if (para) {
            do {
                if (GB.lineHeightBlockTags[pNode.nodeName.toLowerCase()]) {
                    break;
                }
                pNode = pNode.parentNode;
            } while (pNode && pNode.nodeName.toLowerCase() !== 'body');
        }

        for (i in imgs) {
            if (!imgs.hasOwnProperty(i) || this.undefined(imgs[i])) {
                continue;
            }
            imgAttr = imgs[i];
            img = this.doc.createElement('img');
            img.setAttribute('src', imgAttr.fileUrl);

            if (this.config.imgSetAttrWidth === 1) {
                img.style.width = imgAttr.width;
                img.style.height = imgAttr.height;
            } else if (this.config.imgSetAttrWidth === -1) {
                img.style.width = '100%';
                img.style.height = 'auto';
            }

            if (this.config.imgSetAttrAlt) {
                img.setAttribute('alt', imgAttr.alt || imgAttr.origName);
            } else {
                img.removeAttribute('alt');
            }

            count++;
            if (para) {
                lastNode = fragment.appendChild(this.doc.createElement('p'));
                if (imgAttr.align !== 'left') {
                    lastNode.style.textAlign = imgAttr.align;
                }
                lastNode.appendChild(img);
                if (insertSpace && count < len) {
                    space = this.makeSpacerElement();
                    fragment.appendChild(space);
                }
            } else {
                lastNode = fragment.appendChild(img);
                if (insertSpace && count < len) {
                    fragment.appendChild(this.doc.createTextNode('\u00a0'));
                }
            }
            this.images.push(imgAttr);
        }

        if (lastNode) {
            if (para) {
                if (pNode.nodeName.toLowerCase() === 'p') {
                    if (!checkPara(pNode)) {
                        pNode.parentNode.replaceChild(fragment, pNode);
                    } else {
                        pNode.parentNode.insertBefore(fragment, pNode.nextSibling);
                    }
                } else {
                    if (!this.W3CRange) {
                        div = this.doc.createElement('div');
                        div.appendChild(fragment);
                        range.pasteHTML(div.innerHTML);
                    } else {
                        range.insertNode(fragment);
                    }
                }
                this.placeCaretAt(lastNode.nextSibling ? lastNode.nextSibling : lastNode, false);
            } else {
                if (!this.W3CRange) {
                    div = this.doc.createElement('div');
                    div.appendChild(fragment);
                    range.pasteHTML(div.innerHTML);
                } else {
                    range.deleteContents();
                    range.insertNode(fragment);
                    range.setStartAfter(lastNode);
                    range.setEndAfter(lastNode);
                    selection = this.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            }
            this.setImageEvent(true);
        }
    },

    showTagSelector : function (on) {
        if (this.config.showTagPath !== true) {
            return;
        }
        this.cheditor.tagPath.style.display = on ? 'block' : 'none';
    },

    getTextContentSelectionPos : function (newline) {
        var textContent = this.cheditor.textContent,
            start, end, docRange, startRange, endRange, textContentLength, normalizedText;

        if (typeof textContent.selectionStart === 'number') {
            start = textContent.selectionStart;
            end = textContent.selectionEnd;
        } else {
            textContentLength = textContent.value.length;
            normalizedText = textContent.value.replace(/\r\n/g, '\n');
            docRange = document.selection.createRange();
            startRange = textContent.createTextRange();
            endRange = startRange.duplicate();
            endRange.collapse(false);
            startRange.moveToBookmark(docRange.getBookmark());

            if (startRange.compareEndPoints('StartToEnd', endRange) > -1) {
                start = end = textContentLength;
            } else {
                start = -startRange.moveStart('character', -textContentLength);
                if (newline) {
                    start += normalizedText.slice(0, start).split('\n').length - 1;
                }
                if (startRange.compareEndPoints('EndToEnd', endRange) > -1) {
                    end = textContentLength;
                } else {
                    end = -startRange.moveEnd('character', -textContentLength);
                    if (newline) {
                        end += normalizedText.slice(0, end).split('\n').length - 1;
                    }
                }
            }
        }
        return { startPos: start, endPos: end };
    },

    removeEmptyBogusTag : function (content) {
        if (/^<(p|div)([^>]+?)?>(&nbsp;|\s+?)<\/(p|div)>$/g.test(this.trimSpace(content))) {
            return '';
        }
        if (/^<br([^>]+?)?>$/g.test(content)) {
            return '';
        }
        return content;
    },

    setTextContentSelection : function (startPos, endPos, content) {
        var textContent = this.cheditor.textContent, range, top;

        content = this.removeEmptyBogusTag(content);

        if (!content) {
            textContent.focus();
            return;
        }
        if (startPos === 0 && endPos === 0) {
            textContent.value = content;
            textContent.focus();
            return;
        }
        textContent.select();
        if (typeof textContent.setSelectionRange === 'function') {
            textContent.value = content;
            textContent.setSelectionRange(startPos, startPos);
            textContent.focus();
            if (this.browser.msie || this.browser.edge) {
                setTimeout(function () {
                    top = textContent.scrollTop;
                    if (top > 0) {
                        textContent.scrollTop = top + textContent.clientHeight / 2;
                    }
                    textContent.selectionEnd = endPos;
                }, 0);
            } else {
                textContent.selectionEnd = endPos;
            }
        } else {
            range = document.selection.createRange();
            textContent.value = content;
            range.moveEnd('character', endPos - content.length);
            range.moveStart('character', startPos);
            range.select();
            top = textContent.scrollTop;
            if (top > 0) {
                textContent.scrollTop = top + textContent.clientHeight / 2;
            }
        }
    },

    richMode : function () {
        var char, collapsed, cursor, endNode, endPos, endRange, outputHTML = null, pNode, pos, range,
            scrollTop, selection, startNode, startPos, startRange, textContent = null, textContentLength;

        if (this.cheditor.mode === 'code' && typeof this.resizeTextContent !== 'undefined') {
            this.cheditor.textContent.focus();
            textContent = this.makeHtmlContent();
            textContentLength = textContent.length;

            pos = this.getTextContentSelectionPos(true);
            startPos = pos.startPos;
            endPos = pos.endPos;

            collapsed = startPos === endPos;
            cursor = startPos;

            if (textContent.charAt(startPos) === '>') {
                startPos++;
                collapsed = true;
            } else {
                while (cursor > -1) {
                    char = textContent.charAt(cursor);
                    if (char === '&'
                        && textContent.charAt(cursor + 1) === 'n' && textContent.charAt(cursor + 2) === 'b'
                        && textContent.charAt(cursor + 3) === 's' && textContent.charAt(cursor + 4) === 'p'
                        && textContent.charAt(cursor + 5) === ';')
                    {
                        startPos = cursor;
                        collapsed = endPos < (startPos + 6);
                        break;
                    }
                    if (char === '>') {
                        break;
                    }
                    if (char === '<') {
                        startPos = cursor;
                        collapsed = true;
                        break;
                    }
                    cursor--;
                }
            }

            if (!collapsed) {
                cursor = endPos;
                if (textContent.charAt(endPos - 1) === '<' ||
                    (textContent.charAt(endPos) === '\n' && cursor === textContentLength - 1)) {
                    endPos = startPos;
                } else {
                    while (cursor < textContentLength) {
                        char = textContent.charAt(cursor);
                        if (char === '<') {
                            break;
                        }
                        if (char === '>') {
                            endPos = startPos;
                            break;
                        }
                        cursor++;
                    }
                }
            } else {
                endPos = startPos;
            }

            if (startPos > 0 && endPos < textContentLength - 1) {
                outputHTML = textContent.substring(0, startPos);
                outputHTML += '<span id="startBogusNode"></span>';
                outputHTML += textContent.substring(startPos, endPos);
                outputHTML += '<span id="endBogusNode"></span>';
                outputHTML += textContent.substring(endPos, textContent.length);
                textContent = outputHTML;
            }

            this.cheditor.textContent.value = '';
            this.removeEvent(window, 'resize', this.resizeTextContent);
            this.putContents(this.convertContentsSpacer(textContent));
        }

        this.range = null;
        this.cheditor.textContent.blur();
        this.cheditor.textContent.removeAttribute('start-pos');
        this.cheditor.textContent.removeAttribute('end-pos');
        this.cheditor.textContent.style.display = 'none';
        this.cheditor.toolbarWrapper.style.display = '';
        this.cheditor.toolbarWrapper.className = 'cheditor-tb-wrapper';
        this.cheditor.editArea.style.visibility = 'hidden';

        if (!this.setFullScreenMode) {
            this.cheditor.editArea.style.height = this.config.editorHeight;
        }

        this.cheditor.editArea.style.display = 'block';
        this.cheditor.editArea.style.visibility = 'visible';
        this.setDesignMode(true);
        this.editAreaFocus();

        if (outputHTML) {
            startNode = this.doc.getElementById('startBogusNode');
            endNode = this.doc.getElementById('endBogusNode');
            if (startNode && endNode) {
                scrollTop = 0;
                pNode = startNode;
                if (pNode) {
                    while (pNode.offsetParent) {
                        scrollTop += pNode.offsetTop;
                        pNode = pNode.offsetParent;
                    }
                    scrollTop -= this.getWindowSize(this.doc).height / 2;
                }

                if (this.doc.compatMode === 'CSS1Compat') {
                    this.doc.documentElement.scrollTop = scrollTop;
                } else {
                    this.doc.body.scrollTop = scrollTop;
                }

                if (this.W3CRange) {
                    selection = this.clearSelection();
                    range = this.createRange();
                    range.setStartAfter(startNode);
                    range.setEndBefore(endNode);
                    try {
                        selection.addRange(range);
                    } catch (ignore) {
                        // display: none?
                    }
                } else {
                    startRange = this.createRange();
                    endRange = startRange.duplicate();
                    startRange.moveToElementText(startNode);
                    endRange.moveToElementText(endNode);
                    startRange.setEndPoint('StartToEnd', startRange);
                    startRange.setEndPoint('EndToEnd', endRange);
                    startRange.select();
                }

                pNode = startNode.parentNode;
                pNode.removeChild(startNode);
                pNode = endNode.parentNode;
                pNode.removeChild(endNode);
            }
        }

        this.setImageEvent(true);
        this.toolbarUpdate();
    },

    editMode : function () {
        var self = this,
            editorWidth, scrollBarWidth, scrollBarHeight, resize, content = null, borderWidth = 1, borderHeight = 1,
            startPos, endPos, startNode, endNode, startEndNode,
            editorHeight = this.cheditor.editWrapper.offsetHeight,
            textContent = this.cheditor.textContent,
            startNodeValue = 'startBogusNode', endNodeValue = 'endBogusNode',
            startNodeCommentTag = '<!--' + startNodeValue + '-->', endNodeCommentTag = '<!--' + endNodeValue + '-->',
            range = this.restoreRange();

        if (this.cheditor.mode === 'preview') {
            if (textContent.getAttribute('start-pos')) {
                startPos = parseInt(textContent.getAttribute('start-pos'), 10);
                textContent.removeAttribute('start-pos');

                if (textContent.getAttribute('end-pos')) {
                    endPos = parseInt(textContent.getAttribute('end-pos'), 10);
                    textContent.removeAttribute('end-pos');
                } else {
                    endPos = startPos;
                }
                content = textContent.value.replace(/\r\n/g, '\n');
            } else {
                this.placeCaretAt(this.doc.body.firstChild || this.doc.body, true);
                range = this.getRange();
            }
        }

        if (!content) {
            startEndNode = this.insertStartEndNode(range);
            startNode = this.doc.createComment(startNodeValue);
            endNode = this.doc.createComment(endNodeValue);
            startEndNode.startNode.parentNode.replaceChild(startNode, startEndNode.startNode);
            startEndNode.endNode.parentNode.replaceChild(endNode, startEndNode.endNode);
            startEndNode.startNode = startNode;
            startEndNode.endNode = endNode;

            content = this.getContents(startEndNode);

            startPos = content.search(startNodeCommentTag);
            endPos = content.search(endNodeCommentTag);
            endPos -= endNodeCommentTag.length + 2;
            content = content.replace(startNodeCommentTag, '').replace(endNodeCommentTag, '');

        }

        this.resetDocumentBody();
        this.cheditor.editArea.style.display = 'none';
        this.cheditor.toolbarWrapper.className = 'cheditor-tb-wrapper-code';
        this.cheditor.editBlock.style.display = 'none';

        textContent.value = '';
        textContent.style.lineHeight = '17px';
        textContent.style.width = '100px';
        textContent.style.height = '100px';
        textContent.style.display = 'block';

        scrollBarWidth = textContent.offsetWidth - 100;
        scrollBarHeight = textContent.offsetHeight - 100;
        if (this.browser.msie && this.browser.version < 8) {
            scrollBarHeight += 2;
        }

        textContent.setAttribute('xbar-height', scrollBarHeight.toString());
        textContent.setAttribute('ybar-width', scrollBarWidth.toString());

        resize = textContent.offsetHeight + (editorHeight - textContent.offsetHeight) - scrollBarHeight - borderHeight;
        textContent.style.height = resize + 'px';

        this.resizeTextContent = function () {
            editorWidth = self.cheditor.editWrapper.offsetWidth;
            resize = textContent.offsetWidth + (editorWidth - textContent.offsetWidth) - scrollBarWidth - borderWidth;
            self.cheditor.textContent.style.width = resize + 'px';
        };

        (function () {
            self.addEvent(window, 'resize', self.resizeTextContent);
            self.resizeTextContent();
        })();

        this.setTextContentSelection(startPos, endPos, content);
        this.setDesignMode(false);
    },

    makeHtmlContent : function () {
        var content = this.trimSpace(this.cheditor.textContent.value);
        content = this.trimZeroSpace(content);
        return content || '<p>&nbsp;</p>';
    },

    resetStatusBar : function () {
        if (this.config.showTagPath) {
            this.cheditor.tagPath.innerHTML = '&lt;html&gt; &lt;body&gt; ';
        }
    },

    previewMode : function () {
        var content, oSheet, pos = this.getTextContentSelectionPos(false);

        if (this.cheditor.mode === 'code' && typeof this.resizeTextContent !== 'undefined') {
            this.cheditor.textContent.setAttribute('start-pos', pos.startPos.toString());
            this.cheditor.textContent.setAttribute('end-pos', pos.endPos.toString());
            content = this.makeHtmlContent();
            this.cheditor.textContent.blur();
            this.cheditor.textContent.style.display = 'none';
            this.removeEvent(window, 'resize', this.resizeTextContent);
            this.putContents(content);
        }

        this.clearSelection();
        this.cheditor.editBlock.style.display = 'none';
        this.cheditor.toolbarWrapper.className = 'cheditor-tb-wrapper-preview';
        if (!this.setFullScreenMode) {
            this.cheditor.editArea.style.height = this.config.editorHeight;
        }
        this.cheditor.editArea.style.display = 'block';

        if (GB.browser.msie && parseInt(GB.browser.version, 10) === 8) {
            try {
                oSheet = this.doc.styleSheets[0];
                oSheet.addRule('p:before', 'content:"\u200b"');
            } catch (ignore) {
                // ignore
            }
        }
        this.setImageEvent(false);
        this.setDesignMode(false);
    },

    convertContentsSpacer : function (content) {
        var self = this, bogusBr = true,
            excepted = '<span id="startBogusNode"><\/span><span id="endBogusNode"><\/span>',
            reSpacer = new RegExp('<([^>]+)>(' + excepted + ')?(?:&nbsp;|\s+?|\u00a0)(' + excepted + ')?<\/([^>]+)>', 'g');

        if (GB.browser.msie && GB.browser.msie_bogus === false) {
            bogusBr = false;
        }
        content = content.replace(/\s{2,}|[\r\n\t]+?/gm, '');
        content = content.replace(reSpacer,
            function (all, open, excepted_a, excepted_b, close) {
                var tagName = self.trimSpace(open.split(' ')[0]).toLowerCase(), rdata = null;
                if (GB.lineHeightBlockTags[tagName] || GB.textFormatTags[tagName]) {
                    rdata = '<' + open + '>' + (excepted_a || '');
                    rdata += bogusBr ? '<br class="' + self.cheditor.bogusSpacerName + '" />' :
                        '<!--' + self.cheditor.bogusSpacerName + '-->';
                    rdata += (excepted_b || '') + '</' + close + '>';
                }
                return rdata || all;
            }
        );
        return content;
    },

    putContents : function (content) {
        if (this.config.fullHTMLSource) {
            content = content.substr(content.search(/<html/ig) + 1);
            content = content.substr(content.indexOf('>') + 1);
            content = '<html>' + content;
            this.doc.open();
            this.doc.write('<body>' + content + '</body>');
            this.doc.close();
        } else {
            content = '<span>remove_this</span>' + content;
            this.doc.body.innerHTML = content;
            this.doc.body.removeChild(this.doc.body.firstChild);
        }
    },

    getImages : function () {
        var img = this.doc.body.getElementsByTagName('img'),
            imgLength = this.images.length,
            imgs = [], i, imgId, j;

        for (i = 0; i < img.length; i++) {
            if (img[i].src) {
                imgId = img[i].src;
                imgId = imgId.slice(imgId.lastIndexOf('/') + 1);
                for (j = 0; j < imgLength; j++) {
                    if (this.images[j].fileName === imgId) {
                        imgs.push(this.images[j]);
                        break;
                    }
                }
            }
        }
        return imgs.length > 0 ? imgs : null;
    },

    getElementStyle : function (elem) {
        return (window.getComputedStyle) ? this.doc.defaultView.getComputedStyle(elem, null) : elem.currentStyle;
    },

    getElementDefaultDisplay : function (elem) {
        return (window.getComputedStyle ? this.doc.defaultView.getComputedStyle(elem, null) : elem.currentStyle).display;
    },

    tabRepeat : function (count) {
        var i = 0, tab = '';
        if (count < 1) {
            return tab;
        }
        for (; i < count; i++) {
            tab += this.cheditor.tabSpaces;
        }
        return tab;
    },

    htmlEncode : function (text) {
        //text = text.replace(/\n{2,}$/g, '\n');
        //text = text.replace(/&/g, '&amp;');
        text = text.replace(/</g, '&lt;');
        text = text.replace(/>/g, '&gt;');
        text = text.replace(/\u00a0/g, '&nbsp;');
        //text = text.replace(/<font ([^>]+)>&nbsp;<\/font>/mgi, '');
        //text = text.replace(/\x22/g, '&quot;');
        return text;
    },

    checkDocLinks : function () {
        var links = this.doc.links,
            len = links.length,
            host = location.host,
            i, href;

        this.cheditor.links = [];

        for (i = 0; i < len; i++) {
            if (!this.config.includeHostname) {
                href = links[i].href;
                if (href.indexOf(host) !== -1) {
                    links[i].setAttribute('href', href.substring(href.indexOf(host) + host.length));
                }
            }
            if (this.config.linkTarget !== '' && this.config.linkTarget !== null) {
                if (!(links[i].getAttribute('target'))) {
                    links[i].setAttribute('target', this.config.linkTarget);
                }
            }
            if (GB.browser.msie) {
                this.cheditor.links.push(links[i]);
            }
        }
    },

    checkDocImages : function () {
        var img = this.doc.images,
            len = img.length,
            host = location.host,
            i = 0, imgUrl;

        for (; i < len; i++) {
            if (!this.config.includeHostname) {
                imgUrl = img[i].src;
                if (imgUrl) {
                    if (imgUrl.indexOf(host) !== -1) {
                        img[i].src = imgUrl.substring(imgUrl.indexOf(host) + host.length);
                    }
                }
            }
            if (img[i].style.width) {
                img[i].removeAttribute('width');
            }
            if (img[i].style.height) {
                img[i].removeAttribute('height');
            }
        }
    },

    createNbspTextNode : function () {
        return this.doc.createTextNode('\u00a0');
    },

    getNodeTree : function (pNode, callback) {
        function Node(node) {
            this.node = node;
            this.name = node.nodeName.toLowerCase();
            this.type = node.nodeType;
            this.parent = node.parentNode;
            this.indent = 0;
        }
        (function recurse(cNode, indent) {
            var i, child,
                node = new Node(cNode),
                children = cNode.childNodes,
                len = children.length;

            node.indent = indent;

            for (i = 0; i < len; i++) {
                child = children[i];
                if (child) {
                    recurse(child, indent + 1);
                }
            }

            if (node.name !== 'body') {
                callback(node);
            }
        })(pNode, -1);
    },

    getContents : function (startEndNode) {
        var self = this,
            mydoc, indentNodes = [], i, node, msie_c = typeof this.browser.msie_c !== 'undefined',
            allowedIndent = this.getContents.caller !== this.getBodyContents,
            insertTabSpace = function (indent) {
                return msie_c ? self.doc.createComment('Tab Size:' + indent) :
                    self.doc.createTextNode('\n' + self.tabRepeat(indent));
            };

        function checkChildNodes(child) {
            if (!child) {
                return null;
            }
            if (!startEndNode) {
                return child;
            }
            do {
                if (child !== startEndNode.startNode && child !== startEndNode.endNode) {
                    break;
                }
                child = child.nextSibling;
            } while (child);

            return child;
        }

        this.checkDocLinks();
        this.checkDocImages();
        this.getNodeTree(this.doc.body, function (node) {
            if (!node.node) {
                return;
            }
            if (self.config.exceptedElements[node.name]) {
                node.parent.replaceChild(self.doc.createTextNode(''), node.node);
                return;
            }

            switch (node.type) {
                case GB.node.text :
                    if (!self.isTextVisible(node.node.nodeValue)) {
                        node.parent.replaceChild(self.doc.createTextNode(''), node.node);
                    }
                    break;
                case GB.node.element :
                    if (node.node.className === self.cheditor.bogusSpacerName) {
                        if (node.node.firstChild === null) {
                            if (node.name === 'br') {
                                node.parent.removeChild(node.node);
                                if (!checkChildNodes(node.parent.firstChild)) {
                                    node.parent.appendChild(self.createNbspTextNode());
                                }
                            } else {
                                node.node.appendChild(self.createNbspTextNode());
                            }
                        } else if (!checkChildNodes(node.node.firstChild)) {
                            node.node.appendChild(self.createNbspTextNode());
                        }
                        node.node.className = '';
                    }
                    if ((node.name === 'p' || node.name === 'div') && !checkChildNodes(node.node.firstChild)) {
                        node.node.appendChild(self.createNbspTextNode());
                    }
                    if (GB.newLineBeforeTags[node.name] && allowedIndent) {
                        if (node.node.firstChild && (node.node.firstChild.nodeType === GB.node.element)) {
                            node.node.insertBefore(insertTabSpace(node.indent + 1), node.node.firstChild);
                            node.node.appendChild(insertTabSpace(node.indent));
                        }
                        indentNodes.push(node);
                    }
                    if (GB.selfClosingTags[node.name]) {
                        node.node.setAttribute('self-close-tag', '1');
                    }
                    break;
                case GB.node.comment :
                    if (node.node.nodeValue === self.cheditor.bogusSpacerName) {
                        node.parent.removeChild(node.node);
                        if (!checkChildNodes(node.parent.firstChild)) {
                            node.parent.appendChild(self.createNbspTextNode());
                        } else if (node.parent.firstChild.nodeName.toLowerCase() === 'br') {
                            node.parent.replaceChild(self.createNbspTextNode(), node.node.firstChild);
                        }
                    }
            }
        });

        for (i in indentNodes) {
            if (indentNodes.hasOwnProperty(i)) {
                node = indentNodes[i];
                node.parent.insertBefore(insertTabSpace(node.indent), node.node);

                if (node.node.nextSibling) {
                    node.node.nextSibling.parentNode.insertBefore(insertTabSpace(node.indent), node.node.nextSibling);
                } else {
                    node.parent.appendChild(insertTabSpace(node.indent));
                }
            }
        }

        indentNodes = [];
        mydoc = this.doc.body.innerHTML;
        mydoc = mydoc.replace(/^\s*[\r\n]/gm, '').replace(/\u200b/g, '');

        if (msie_c) {
            mydoc = mydoc.replace(/<!--Tab Size:(\d+)-->(?:[\r\n]*)/g,
                function (a, b) {
                    return '\n' + self.tabRepeat(b);
                }).replace(/<(\/?)([A-Za-z]+)([^>]*)>/g,
                function (a, close, tag, attr) {
                    attr = attr.replace(/\s(\w+)=([^'"\s>]+)/g,
                        function (a, k, v) {
                            return ' ' + k.toLowerCase() + '="' + v + '"';
                        }).replace(/([A-Za-z\-]+)(?:\s*):\s+?/g,
                        function (a, k) {
                            return k.toLowerCase() + ': ';
                        });
                    return '<' + close + tag.toLowerCase() + attr + '>';
                }
            );
        }

        mydoc = mydoc.replace(/<[^>]+>/gm, function (match) {
            match = match.replace(/\sself-close-tag="1"([^>]+)?/g, '$1 /');

            if (self.config.allowedOnEvent !== true) {
                match = match.replace(/\s+on([A-Za-z]+)=("[^"]*"|'[^']*'|[^\s>]*)/g, '');
            }

            if (self.config.colorToHex) {
                match = match.replace(/(background-color|color)\s*([:=])\s*(rgba?)\(\s*(\d+)\s*,\s*(\d+),\s*(\d+)\)/ig,
                    function (all, p, s, rgb, r, g, b) {
                        return p + s + ' ' + self.colorConvert(rgb + '(' + r + ',' + g + ',' + b + ')', 'hex');
                    });
            } else {
                match = match.replace(/(background-color|color)\s*([:=])\s*(#[A-Fa-f0-9]{3,6})/ig,
                    function (all, p, s, hex) {
                        return p + s + ' ' + self.colorConvert(hex, 'rgb');
                    });
            }

            return match;
        });

        return mydoc;
    },

    returnContents : function (mydoc) {
        mydoc = this.removeEmptyBogusTag(mydoc);
        this.setDesignMode(true);
        this.cheditor.textarea.value = mydoc;
        return mydoc;
    },

    makeAmpTag : function (str) {
        return str.replace(/&lt;/g, '&amp;lt;').replace(/&gt;/g, '&amp;gt;');
    },

    removeAmpTag : function (str) {
        if (this.config.removeIndent) {
            str = str.replace(/^[\t]+/gm, '');
        }
        return str.replace (/&amp;lt;/g, '&lt;').replace(/&amp;gt;/g, '&gt;');
    },

    getOutputContents : function () {
        this.resetViewHTML();
        return this.removeAmpTag(this.getContents(null));
    },

    outputHTML : function () {
        return '<!DOCTYPE html>\n' +
            '<html>\n' +
            '  <head>\n' +
            '    <title>' + this.config.docTitle + '</title>\n' +
            '  </head>\n' +
            '  <body>\n' +
            this.returnContents(this.getOutputContents()) +
            '  </body>\n' +
            '</html>';
    },

    getBodyContents : function () {
        return (this.cheditor.mode === 'code') ? this.makeHtmlContent() : this.getContents(null);
    },

    outputBodyHTML : function () {
        return this.returnContents(this.getOutputContents());
    },

    outputBodyText : function () {
        return this.returnContents(this.getBodyText());
    },

    getBodyText : function () {
        this.resetViewHTML();
        return this.trimSpace(String(GB.browser.msie ? this.doc.body.innerText : this.doc.body.textContent));
    },

    returnFalse : function () {
        var img = this.doc.images, i;
        this.editAreaFocus();

        for (i = 0; i < img.length; i++) {
            if (img[i].src) {
                if (img[i].getAttribute('onload')) {
                    img[i].onload = 'true';
                }
            } else {
                img[i].removeAttribute('onload');
                img[i].removeAttribute('className');
            }
        }
        return false;
    },

    trimZeroSpace : function (str) {
        return str ? str.replace(/[\ufeff\u200b\xa0\u3000]+/gm, '') : '';
    },

    trimSpace : function (str) {
        return str ? str.replace(/^[\s\ufeff\u200b\xa0\u3000]+|[\s\ufeff\u200b\xa0\u3000]+$/g, '') : '';
    },

    makeRandomString : function () {
        var chars = '_-$@!#0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz',
            len = 32,
            clen = chars.length,
            rData = '', i, rnum;

        for (i = 0; i < len; i++) {
            rnum = Math.floor(Math.random() * clen);
            rData += chars.substring(rnum, rnum + 1);
        }
        return rData;
    },

    strLength : function (str) {
        var len = str.length, mbytes = 0, i, c;
        for (i = 0; i < len; i++) {
            c = str.charCodeAt(i);
            if (c > 128) {
                mbytes++;
            }
        }
        return (len - mbytes) + (mbytes * 2);
    },

    resetViewHTML : function () {
        if (this.cheditor.mode === 'code') {
            this.switchEditorMode('rich');
        }
    },

    contentsLengthAll : function () {
        return this.strLength(this.outputHTML());
    },

    contentsLength : function () {
        var content = String(this.trimSpace(this.outputBodyHTML()));
        if (!content) {
            return 0;
        }
        return this.strLength(content);
    },

    inputLength : function () {
        var content = this.getBodyText();
        if (content === '') {
            return 0;
        }
        return this.strLength(content);
    },

    displayWindow : function (pNode, id) {
        var pullDown = this.pulldown[id];
        this.editAreaFocus();
        this.backupRange();
        this.boxHideAll(id);
        pullDown.style.visibility = 'visible';
        pullDown.style.zIndex = 10002;
        pullDown.focus();
    },

    pulldownMouseOver : function (el) {
        if (el.className === 'cheditor-pulldown-selected') {
            return;
        }
        el.className = 'cheditor-pulldown-mouseover';
    },
    pulldownMouseOut  : function (el) {
        if (el.className === 'cheditor-pulldown-selected') {
            return;
        }
        el.className = 'cheditor-pulldown-mouseout';
    },

    windowPos : function (pNode, id) {
        var left = pNode.offsetLeft, box = this.pulldown[id];

        if (this.toolbar[id].type === 'combobox') {
            left -= parseInt(this.toolbar[this.toolbar[id].node].width, 10);
        }
        if (this.toolbar[id].prev && !this.toolbar[id].next) {
            left -= 1;
        }
        box.style.left = left + 'px';
        box.style.top  = pNode.offsetTop + parseInt(pNode.style.height, 10) + 'px';
    },

    boxHideAll : function (showId) {
        var menu, box, ishide;
        for (menu in this.pulldown) {
            if (this.pulldown.hasOwnProperty(menu)) {
                box = this.pulldown[menu];
                if (box) {
                    box.style.visibility = 'hidden';
                    ishide = this.undefined(showId) ? true : (menu !== showId);
                    if (ishide && this.toolbar[menu].checked) {
                        this.toolbar[menu].checked = false;
                        this.toolbarButtonUnchecked(this.toolbar[menu]);
                    }
                }
            }
        }
        this.editAreaFocus();
    },

    createWindow : function (width, elem) {
        elem.className = 'cheditor-pulldown-container';
        elem.style.width = width + 'px';
    },

    setColorTable : function (menu) {
        var self = this,
            pulldown = document.createElement('div'),
            len = GB.colors.length,
            container = document.createElement('div'),
            selected = document.createElement('input'),
            selectedValue = document.createElement('input'),
            cellWrapper = document.createElement('div'),
            br = document.createElement('div'),
            reset = document.createElement('span'),
            pickerSwitch = document.createElement('span'),
            button = document.createElement('img'),
            showTooltip = '더 많은 색 보기',
            hideTooltip = '감추기',
            i, cell, color = 0, colorPicker, cellBorder,
            onMouseOverEventHandler = function () {
                colorPicker.fromString(this.id);
                this.parentNode.className = 'cheditor-pulldown-color-cell-over';
            },
            onMouseOutEventHandler = function () {
                this.parentNode.className = 'cheditor-pulldown-color-cell';
            },
            onClickEventHandler = function () {
                self.doCmdPopup(menu, this.id);
            },
            onResetEventHandler = function () {
                colorPicker.fromString(self.colorConvert(selectedValue.style.backgroundColor, 'hex'));
            },
            onPickerEventHandler = function () {
                if (self.toolbar[menu].colorNode.showPicker) {
                    colorPicker.hidePicker();
                    self.toolbar[menu].colorNode.showPicker = false;
                    pickerSwitch.setAttribute('title', showTooltip);
                } else {
                    colorPicker.showPicker();
                    self.toolbar[menu].colorNode.showPicker = true;
                    pickerSwitch.setAttribute('title', hideTooltip);
                }
            },
            onSubmitEventHandler = function () {
                self.doCmdPopup(menu, selected.value);
            };

        selected.setAttribute('type', 'text');
        selected.setAttribute('maxlength', '7');
        selected.className = 'cheditor-pulldown-color-selected';

        selectedValue.setAttribute('type', 'text');
        selectedValue.onfocus = function () {
            selected.focus();
        };

        selectedValue.style.cursor = 'default';
        selectedValue.className = 'cheditor-pulldown-color-selected';
        selected.style.marginLeft = '-1px';
        selected.style.borderLeft = 'none';
        selected.spellcheck = false;

        cellWrapper.style.margin = '2px';
        cellWrapper.style.position = 'relative';
        container.style.position = 'relative';

        br.style.clear = 'both';
        br.style.height = '0px';
        colorPicker = new GB.colorDropper(selected, {'iconDir': this.config.iconPath});

        for (i = 0; i < len; i++) {
            if (i % 13 === 0) {
                cellWrapper.appendChild(br.cloneNode(true));
                if (i === 26) {
                    cellWrapper.lastChild.style.height = '4px';
                }
                len++;
                continue;
            }
            cellBorder = document.createElement('span');
            cellBorder.className = 'cheditor-pulldown-color-cell';
            cell = document.createElement('span');
            cell.id = GB.colors[color];
            cell.style.backgroundColor = GB.colors[color++];
            cell.appendChild(document.createTextNode('\u00a0'));
            cellBorder.appendChild(cell);
            cellWrapper.appendChild(cellBorder);
            (function () {
                cell.onclick = onClickEventHandler;
                cell.onmouseover = onMouseOverEventHandler;
                cell.onmouseout = onMouseOutEventHandler;
            })();
        }

        cellWrapper.appendChild(br);
        cellWrapper.appendChild(selectedValue);
        cellWrapper.appendChild(selected);

        reset.appendChild(document.createTextNode('\u00a0'));
        reset.className = 'cheditor-pulldown-color-reset';
        reset.onclick = onResetEventHandler;

        cellWrapper.appendChild(reset);

        pickerSwitch.appendChild(document.createTextNode('\u00a0'));
        pickerSwitch.className = 'cheditor-pulldown-color-show-picker';
        pickerSwitch.setAttribute('title', showTooltip);
        pickerSwitch.onclick = onPickerEventHandler;
        cellWrapper.appendChild(pickerSwitch);

        button.className = 'cheditor-pulldown-color-submit';
        button.src = this.config.iconPath + 'button/input_color.gif';
        button.onclick = onSubmitEventHandler;
        cellWrapper.appendChild(button);
        container.appendChild(cellWrapper);

        self.toolbar[menu].colorNode.selectedValue = selectedValue;
        self.toolbar[menu].colorNode.colorPicker = colorPicker;

        pulldown.appendChild(container);
        return pulldown;
    },

    onKeyPressToolbarUpdate : function () {
        var self = this;
        if (this.tempTimer) {
            clearTimeout(this.tempTimer);
        }
        this.tempTimer = setTimeout(function () {
            if (self.config.showTagPath) {
                self.doEditorEvent();
            } else {
                self.toolbarUpdate();
            }
            self.tempTimer = null;
        }, 50);
    },

    doOnKeyDown : function (evt) {
        switch (evt.keyCode) {
            case 37: case 38: case 39: case 40: case 46: case 8:
                this.onKeyPressToolbarUpdate(evt);
        }
    },

    doOnKeyUp : function (evt) {
        var caretRange, css, enterNode, i, keyCode = evt.keyCode, node, self = this, storedNode, storedRange,
            clearBackgroundColor = function (cNode) {
                if (cNode.nodeType !== GB.node.element || cNode.hasChildNodes() || !cNode.getAttribute('style')) {
                    return cNode;
                }
                if (GB.textFormatTags[cNode.nodeName.toLowerCase()]) {
                    css = self.checkCssValue(cNode, 'background-color');
                    if (css) {
                        css = self.clearCss(cNode, 'background-color');
                        if (cNode.nodeName.toLowerCase() === 'span' && !css) {
                            node = cNode.parentNode;
                            node.removeChild(cNode);
                            return node;
                        }
                    }
                }
                return cNode;
            };

        if (keyCode !== 13 || this.cheditor.mode !== 'rich') {
            return;
        }

        if (typeof GB.browser.msie_c !== 'undefined') {
            node = this.storedSelections[0];
            if (!node) {
                return;
            }
            if (node.className === this.cheditor.bogusSpacerName) {
                node.className = '';
            }
            while (node.firstChild) {
                node = node.firstChild;
            }
            if (node.nodeType === GB.node.element && node.canHaveChildren) {
                node.className = this.cheditor.bogusSpacerName;
            }
            return;
        }

        caretRange = this.getRange();
        enterNode = caretRange.commonAncestorContainer;

        if (GB.browser.msie || GB.browser.edge) {
            this.backupRange(caretRange);
            if (enterNode.nodeType === GB.node.element) {
                enterNode = clearBackgroundColor(enterNode);
                enterNode.className = this.cheditor.bogusSpacerName;
            }
            for (i = 0; i < this.keyPressStoredSelections.length; i++) {
                storedRange = this.keyPressStoredSelections[i];
                storedNode = storedRange.commonAncestorContainer;
                if (storedNode && storedNode.nodeType === GB.node.element) {
                    node = storedNode.childNodes[storedRange.startOffset];
                    if (!node) {
                        break;
                    }
                    while (node.firstChild) {
                        node = node.firstChild;
                    }
                    node = clearBackgroundColor(node);
                    node.className = this.cheditor.bogusSpacerName;
                }
            }
            this.restoreRange();
            this.keyPressStoredSelections = [];
        } else {
            this.applyBogusClassName(caretRange);
        }
    },

    doOnKeyPress : function (evt) {
        var keyCode = evt.keyCode, caretRange;
        if (keyCode && keyCode === 13 && this.cheditor.mode === 'rich') {
            caretRange = this.getRange();
            if (typeof this.browser.msie_c !== 'undefined') {
                try {
                    this.storedSelections[0] = caretRange.parentElement();
                } catch (e) {
                    this.keyPressBackupRange();
                }
            } else if (GB.browser.msie || GB.browser.edge) {
                this.keyPressBackupRange();
            } else {
                this.applyBogusClassName(caretRange);
            }
        }
    },

    applyBogusClassName : function (range) {
        var node = range.commonAncestorContainer;
        if (range.startOffset < 1 && (!node.lastChild || node.lastChild.nodeName.toLowerCase() !== 'br')) {
            do {
                if (node.parentNode.nodeName.toLowerCase() === 'body') {
                    break;
                }
                node = node.parentNode;
            } while (GB.textFormatTags[node.nodeName.toLowerCase()]);

            node = node.previousSibling;
            if (node) {
                while (node.firstChild) {
                    node = node.firstChild;
                }
                if (node.nodeType === GB.node.element && node.nodeName.toLowerCase() === 'br') {
                    node.className = this.cheditor.bogusSpacerName;
                }
            }
        } else {
            if (!node || node.nodeType !== GB.node.element) {
                return;
            }
            if (node.lastChild
                && node.lastChild.nodeName.toLowerCase() === 'br'
                && node.lastChild.className !== this.cheditor.bogusSpacerName)
            {
                node.lastChild.className = this.cheditor.bogusSpacerName;
            }
        }
    },

    setWinPosition : function (oWin, popupAttr, windowSize) {
        oWin.style.width = popupAttr.width + 'px';
        oWin.style.left = Math.round(((this.cheditor.editArea.clientWidth - popupAttr.width) / 2) +
                windowSize.offsetLeft) + 'px';
        oWin.style.top = Math.round(windowSize.offsetTop) + 'px';
    },

    getWindowSize : function (doc) {
        var mydoc = doc || document,
            docMode = mydoc.compatMode === 'CSS1Compat',
            docBody = mydoc.body,
            docElem = mydoc.documentElement,
            factor, rect, physicalWidth, logicalWidth,
            editAreaRect,
            rData = {
                width: docMode ? docElem.clientWidth : docBody.clientWidth,
                height: docMode ? docElem.clientHeight : docBody.clientHeight,
                scrollHeight: docMode ? docElem.scrollHeight : docBody.scrollHeight,
                scrollWidth: docMode ? docElem.scrollWidth : docBody.scrollWidth
            };

        if (this.undefined(window.pageXOffset)) {
            factor = 1;
            if (docBody.getBoundingClientRect) {
                rect = docBody.getBoundingClientRect();
                physicalWidth = rect.right - rect.left;
                logicalWidth = mydoc.body.offsetWidth;
                factor = Math.round ((physicalWidth / logicalWidth) * 100) / 100;
            }
            rData.scrollY = Math.round(docElem.scrollTop / factor);
            rData.scrollX = Math.round(docElem.scrollLeft / factor);
        } else {
            rData.scrollY = window.pageYOffset;
            rData.scrollX = window.pageXOffset;
        }

        editAreaRect = this.cheditor.editArea.getBoundingClientRect();
        rData.clientTop = docElem.clientTop || docBody.clientTop || 0;
        rData.clientLeft = docElem.clientLeft || docBody.clientLeft || 0;
        rData.offsetTop = rData.scrollY + (rData.height / 2);
        rData.offsetLeft = editAreaRect.left + rData.scrollX - rData.clientLeft;
        return rData;
    },

    popupWinLoad : function (popupAttr) {
        var self = this,
            windowSize = self.getWindowSize(),
            iframe = document.createElement('iframe'),
            body = document.getElementsByTagName('body')[0],
            done = false,

            popWinResizeHeight = function (evt) {
                iframe.contentWindow.focus();
                iframe.contentWindow.init.call(self, iframe, popupAttr.argv || null);

                if (self.cheditor.popupElem.style.visibility !== 'visible') {
                    self.cheditor.popupElem.style.top = Math.ceil(parseInt(self.cheditor.popupElem.style.top, 10) -
                            Math.ceil(self.cheditor.popupElem.clientHeight / 2)) + 'px';
                    self.cheditor.popupElem.style.visibility = 'visible';
                }

                self.stopEvent(evt);
            },
            modalResize = function () {
                self.cheditor.modalBackground.style.height = (windowSize.scrollHeight > windowSize.height) ?
                    windowSize.scrollHeight : windowSize.height + 'px';

                if (window.scrollWidth > window.width) {
                    self.cheditor.modalBackground.style.width = windowSize.width +
                        (windowSize.scrollWidth - windowSize.width) + 'px';
                } else {
                    self.cheditor.modalBackground.style.width = windowSize.width + 'px';
                }
                self.cheditor.modalBackground.style.left = windowSize.scrollX + 'px';
            },
            onReadyStateChangeEventHandler = function (evt) {
                if (!done && (!this.readyState || this.readyState === 'complete' || this.readyState === 'loaded')) {
                    popWinResizeHeight(evt);
                    done = true;
                }
            };

        if (self.cheditor.popupTitle.hasChildNodes()) {
            self.cheditor.popupTitle.removeChild(self.cheditor.popupTitle.firstChild);
        }

        if (self.cheditor.popupFrameWrapper.hasChildNodes()) {
            self.cheditor.popupFrameWrapper.removeChild(self.cheditor.popupFrameWrapper.firstChild);
        }

        self.cheditor.popupTitle.appendChild(document.createTextNode(popupAttr.title));
        self.cheditor.popupElem.style.zIndex = self.modalElementZIndex + 1;
        self.setWinPosition(self.cheditor.popupElem, popupAttr, windowSize);

        iframe.setAttribute('frameBorder', '0');
        iframe.setAttribute('height', '0');
        iframe.setAttribute('width', String(popupAttr.width - 22));
        iframe.setAttribute('name', popupAttr.tmpl);
        iframe.setAttribute('src', self.config.popupPath + popupAttr.tmpl);
        iframe.id = popupAttr.tmpl;

        self.cheditor.modalBackground.style.zIndex = self.modalElementZIndex;
        body.insertBefore(self.cheditor.modalBackground, body.firstChild);
        body.insertBefore(self.cheditor.popupElem, body.firstChild);

        self.cheditor.popupFrameWrapper.appendChild(iframe);
        self.cheditor.popupElem.style.visibility = 'hidden';
        self.cheditor.popupElem.style.display = 'block';
        self.cheditor.modalBackground.style.display = 'block';
        GB.dragWindow.init(self.cheditor.dragHandle, self.cheditor.popupElem);

        (function () {
            if (GB.browser.msie && !(self.undefined(iframe.onreadystatechange))) {
                iframe.onreadystatechange = onReadyStateChangeEventHandler;
            } else {
                iframe.onload = popWinResizeHeight;
            }

            if (GB.browser.msie && GB.browser.version < 9) {
                window.onresize = function () {
                    windowSize = self.getWindowSize();
                    modalResize();
                };
                modalResize();
                self.cheditor.modalBackground.style.filter = 'alpha(opacity=50)';
                self.cheditor.modalBackground.style.opacity = 0.5;
            } else {
                self.cheditor.modalBackground.style.opacity = 0.5;
            }
            self.cheditor.modalBackground.focus();
        })();
    },

    popupWinCancel : function () {
        this.restoreRange();
        this.popupWinClose();
    },

    popupWinClose : function () {
        if (!this.cheditor.popupElem) {
            return;
        }
        this.cheditor.popupElem.style.display = 'none';
        this.cheditor.popupElem.style.zIndex = -1;
        this.cheditor.popupFrameWrapper.src = '';

        if (this.cheditor.popupFrameWrapper.hasChildNodes()) {
            this.cheditor.popupFrameWrapper.removeChild(this.cheditor.popupFrameWrapper.firstChild);
        }

        this.cheditor.modalBackground.style.display = 'none';
        this.cheditor.modalBackground.style.zIndex = -1;

        if (this.modalReSize !== null) {
            if (GB.browser.opera) {
                window.removeEventListener('resize', this.modaReSize, false);
            }
            this.modalReSize = null;
        }
        this.editAreaFocus();
    },

    clearStoredSelections : function () {
        this.storedSelections.splice(0, this.storedSelections.length);
    },

    restoreRange : function () {
        var range = null, selection = null;
        if (this.storedSelections[0]) {
            if (this.W3CRange) {
                selection = this.getSelection();
                if (selection.rangeCount > 0) {
                    selection.removeAllRanges();
                }
                selection.addRange(this.storedSelections[0]);
                range = selection.getRangeAt(0);
            } else {
                range = this.createRange();
                if (this.storedSelections[0]) {
                    if (typeof this.storedSelections[0] === 'string') {
                        range.moveToBookmark(this.storedSelections[0]);
                    } else {
                        range = this.storedSelections[0];
                    }
                }
                range.select();
            }
        }
        return range;
    },

    keyPressBackupRange : function (range) {
        var selection = null, i;
        if (this.W3CRange) {
            selection = this.getSelection();
            if (selection) {
                for (i = 0; i < selection.rangeCount; i++) {
                    this.keyPressStoredSelections.push(selection.getRangeAt(i));
                }
            }
        } else {
            range = range || this.getRange();
            switch (this.getSelectionType()) {
                case GB.selection.none:
                case GB.selection.text:
                    this.storedSelections[0] = range.getBookmark();
                    break;
                case GB.selection.element:
                    this.storedSelections[0] = range;
                    break;
                default:
                    this.storedSelections[0] = null;
            }
        }
    },

    backupRange : function (range) {
        var selection = null;
        if (this.W3CRange) {
            selection = this.getSelection();
            if (selection && selection.rangeCount > 0) {
                this.storedSelections[0] = selection.getRangeAt(0);
            }
        } else {
            range = range || this.getRange();
            switch (this.getSelectionType()) {
                case GB.selection.none:
                case GB.selection.text:
                    this.storedSelections[0] = range.getBookmark();
                    break;
                case GB.selection.element:
                    this.storedSelections[0] = range;
                    break;
                default:
                    this.storedSelections[0] = null;
            }
        }
    },

    getSelection : function () {
        return this.W3CRange ? this.editArea.getSelection() : this.doc.selection;
    },

    clearSelection : function () {
        var sel = this.getSelection();
        if (!sel) {
            return;
        }
        if (this.W3CRange) {
            sel.removeAllRanges();
        } else {
            sel.empty();
        }
        return sel;
    },

    getRange : function () {
        var selection = this.getSelection(), range = null;
        if (this.W3CRange) {
            if (selection.getRangeAt) {
                range = selection.rangeCount ? selection.getRangeAt(0) : this.doc.createRange();
            } else {
                range = this.doc.createRange();
                range.setStart(selection.anchorNode, selection.anchorOffset);
                range.setEnd(selection.focusNode, selection.focusOffset);
                if (range.collapsed !== selection.isCollapsed) {
                    range.setStart(selection.focusNode, selection.focusOffset);
                    range.setEnd(selection.anchorNode, selection.anchorOffset);
                }
            }
        } else {
            range = selection.createRange ? selection.createRange() : this.doc.createRange();
            if (!range) {
                range = this.doc.body.createTextRange();
            }
        }
        this.range = range;
        return range;
    },

    createRange : function () {
        return this.W3CRange ? this.doc.createRange() : this.doc.body.createTextRange();
    },

    rangeCollapsed : function (range) {
        return this.W3CRange ?
            range.collapsed :
                (!this.undefined(range.text) && range.text.length === 0 && range.boundingWidth === 0);
    },

    getRangeElement : function (range) {
        return this.W3CRange ? this.getW3CRangeElement(range) : this.getIeRangeElement(range);
    },

    getIeRangeElement : function (range) {
        var sType = this.getSelectionType(),
            node;

        if (!range) {
            range = this.createRange();
        }

        switch (sType) {
            case GB.selection.text :
                node = range.parentElement();
                break;
            case GB.selection.element :
                node = range.item(0);
                break;
            case GB.selection.none :
                if (!this.undefined(range.parentElement)) {
                    node = range.parentElement();
                } else {
                    node = range.item(0);
                }
        }
        return node;
    },

    getW3CRangeElement : function (range) {
        var ancestorContainer = range.commonAncestorContainer,
            startContainer = range.startContainer,
            startOffset = range.startOffset,
            endContainer = range.endContainer,
            endOffset = range.endOffset,
            docFragment = null, node = startContainer;

        if (GB.browser.msie || GB.browser.edge) {
            if (!range.collapsed && ancestorContainer.nodeType === GB.node.element) {
                if (ancestorContainer === endContainer) {
                    node = ancestorContainer.childNodes[endOffset - 1];
                } else if (ancestorContainer === startContainer) {
                    node = ancestorContainer.childNodes[startOffset];
                } else {
                    docFragment = range.cloneContents();
                    node = (docFragment.childNodes.length === 1) ? startContainer.nextSibling : ancestorContainer;
                }
            }
        } else {
            if (!range.collapsed
                && startContainer.nodeType === GB.node.element
                && startContainer === endContainer
                && endOffset - startOffset === 1
                && startContainer.hasChildNodes())
            {
                node = startContainer.childNodes[startOffset];
            }
        }

        if (node.nodeType === GB.node.text) {
            node = node.parentNode;
        }
        return node;
    },

    getSelectionType : function () {
        var selection = this.getSelection(), type;

        if (this.W3CRange) {
            if (!selection) {
                type = GB.selection.none;
            } else if (selection.rangeCount && !selection.isCollapsed && !selection.toString()) {
                type = GB.selection.element;
            } else {
                type = GB.selection.text;
            }
        } else {
            switch (selection.type) {
                case 'Text' : type = GB.selection.text; break;
                case 'Control' : type = GB.selection.element; break;
                default : type = GB.selection.none;
            }
            if (selection.createRange().parentElement) {
                type = GB.selection.text;
            }
        }
        return type;
    },

    windowOpen : function (popupName) {
        this.editAreaFocus();
        this.boxHideAll();
        this.backupRange();
        if (!(this.undefined(GB.popupWindow[popupName]))) {
            var popup = GB.popupWindow[popupName];
            if (popupName === 'ImageUpload' && window.File && window.FileReader && window.FileList && window.Blob) {
                popup.tmpl = 'image.html5.html';
            }
            this.popupWinLoad(popup);
        } else {
            alert('사용할 수 없는 명령입니다.');
        }
    },

    doCmd : function (cmd, opt) {
        var self = this, range = this.range,
            i, keyboard = '', command = '', pNode, node, tmpframe, tmpdoc, html, content,
            hr, newHr, para, next = null, nNode, tagName, style, id, hRule, nodeType, css, found = false,
            isEmpty = false, selectionType, selection, nodeRange,
            cleanPaste = function () {
                self.editAreaFocus();
                var tmpDoc = self.cheditor.tmpdoc;
                tmpDoc.execCommand('SelectAll');
                tmpDoc.execCommand('Paste');
                return self.cleanFromWord(tmpDoc);
            },
            isTextVisible = function (elem) {
                return (!(elem.firstChild.nodeType === GB.node.text && elem.firstChild === elem.lastChild &&
                    elem.firstChild.nodeValue === ''));
            };

        this.editAreaFocus();
        this.boxHideAll();

        if (!range) {
            return;
        }

        if (cmd === 'NewDocument') {
            if (confirm('글 내용이 모두 사라집니다. 계속하시겠습니까?')) {
                this.doc.body.innerHTML = '';
            }
            this.images = [];
            this.editImages = {};
            this.editAreaFocus();
            this.toolbarUpdate();
            this.initDefaultParagraphSeparator();
            return;
        }

        if (cmd === 'ClearTag') {
            if (confirm('모든 HTML 태그를 삭제합니다. 계속하시겠습니까?\n(P, DIV, BR 태그와 텍스트는 삭제하지 않습니다.)')) {
                content = this.doc.body.innerHTML;
                this.doc.body.innerHTML = content.replace(/<(\/?)([^>]*)>/g,
                        function (a, b, c) {
                            var el = c.toLowerCase().split(/ /)[0];
                            if (el !== 'p' && el !== 'div' && el !== 'br') {
                                return '';
                            }
                            return '<' + b + el + '>';
                        });
            }
            this.editAreaFocus();
            this.toolbarUpdate();
            return;
        }

        if (cmd === 'Print') {
            this.editArea.print();
            return;
        }

        if (cmd === 'PageBreak') {
            this.printPageBreak();
            this.editAreaFocus();
            return;
        }

        selectionType = this.getSelectionType();
        if (this.W3CRange || selectionType === GB.selection.none) {
            range = this.doc;
        }

        if (!GB.browser.msie && (cmd === 'Cut' || cmd === 'Copy' || cmd === 'Paste')) {
            if ((range.execCommand(cmd, false, opt)) !== true) {
                switch (cmd) {
                    case 'Cut'  : keyboard = 'x'; command = '자르기'; break;
                    case 'Copy' : keyboard = 'c'; command = '복사'; break;
                    case 'Paste': keyboard = 'v'; command = '붙이기'; break;
                }
                alert('사용하고 계신 브라우저는 보안 상의 이유로 \'' + command + '\' 명령을 사용하실 수 없습니다. \n\n' +
                '키보드 단축키를 이용하여 주십시오.\n단축키: Windows: Ctrl+' + keyboard + ', Mac OS X: Command+' + keyboard);
                this.editAreaFocus();

            }
            return;
        }

        try {
            if (cmd === 'PasteFromWord') {
                if (this.undefined(this.cheditor.tmpdoc)) {
                    tmpframe = this.doc.createElement('iframe');
                    tmpframe.setAttribute('contentEditable', 'true');
                    tmpframe.style.visibility = 'hidden';
                    tmpframe.style.height = tmpframe.style.width = '0px';
                    tmpframe.setAttribute('frameBorder', '0');
                    this.cheditor.editWrapper.appendChild(tmpframe);

                    tmpdoc = tmpframe.contentWindow.document;
                    tmpdoc.designMode = 'On';
                    tmpdoc.open();
                    tmpdoc.close();
                    this.cheditor.tmpdoc = tmpdoc;
                }

                if (this.W3CRange) {
                    html = cleanPaste();
                    // range = this.restoreRange();
                    this.insertNodeAtSelection(html);
                } else {
                    range = this.getRange();
                    range.pasteHTML(cleanPaste());
                    range.select();
                }
            } else if (cmd === 'Paste') {
                this.cheditor.paste = 'text';
                this.handlePaste(null);
                this.cheditor.paste = 'html';
            } else if (cmd === 'InsertHorizontalRule') {
                hr = this.doc.createElement('hr');
                hr.style.height = '1px';
                hr.style.backgroundColor = '#999';
                hr.style.border = 'none';

                this.unselectionElement(hr);
                range = this.getRange();

                if (this.W3CRange) {
                    range.insertNode(hr);
                } else {
                    nodeType = this.getSelectionType();
                    id = this.makeRandomString();
                    range.execCommand('InsertHorizontalRule', false, id);
                    switch (nodeType) {
                    case GB.selection.none :
                    case GB.selection.text :
                        node = range.parentElement();
                        break;
                    case GB.selection.element :
                        node = range.item(0);
                        break;
                    default :
                        return;
                }
                    newHr = this.$(id);
                    newHr.parentNode.replaceChild(hr, newHr);
                }

                hRule = hr;
                pNode = hRule.parentNode;
                para = this.makeSpacerElement();

                while (pNode && GB.textFormatTags[pNode.nodeName.toLowerCase()]) {
                    pNode = pNode.parentNode;
                }

                tagName = pNode.tagName.toLowerCase();
                if (GB.textFormatBlockTags[tagName]) {
                    if (hr.nextSibling) {
                        next = this.doc.createElement(tagName);
                        pNode.parentNode.insertBefore(next, pNode.nextSibling);
                        while (hr.nextSibling) {
                            if (hr.nextSibling.nodeType === GB.node.text && hr.nextSibling.nodeValue === '') {
                                hr = hr.nextSibling;
                                continue;
                            }
                            if (hr.nextSibling.parentNode !== pNode) {
                                node =  hr.nextSibling.parentNode;
                                while (node !== pNode && GB.textFormatTags[node.nodeName.toLowerCase()]) {
                                    nNode = this.doc.createElement(node.nodeName);
                                    style = this.getCssValue(node);
                                    if (style) {
                                        for (i = 0; i < style.length; i++) {
                                            nNode.style[style[i].name] = style[i].value;
                                        }
                                    }
                                    if (next.hasChildNodes() === false) {
                                        next.appendChild(nNode);
                                        while (hr.nextSibling) {
                                            nNode.appendChild(hr.nextSibling);
                                        }
                                    } else {
                                        next.appendChild(nNode);
                                        nNode.appendChild(next.firstChild);
                                    }
                                    node = node.parentNode;
                                }
                            } else {
                                next.appendChild(hr.nextSibling);
                            }
                        }
                    }
                    node = hr.parentNode;
                    pNode.parentNode.insertBefore(hRule, pNode.nextSibling);

                    while (node && node !== pNode) {
                        if (node.hasChildNodes() === false || (isTextVisible(node)) === false) {
                            nNode = node.parentNode;
                            node.parentNode.removeChild(node);
                            node = nNode;
                            continue;
                        }
                        node = node.parentNode;
                    }

                    if (pNode.hasChildNodes() === false || (isTextVisible(pNode)) === false) {
                        pNode.parentNode.replaceChild(para.cloneNode(true), pNode);
                    }
                    if (next === null || next.hasChildNodes() === false) {
                        hRule.parentNode.insertBefore(para.cloneNode(true), hRule.nextSibling);
                    }

                    node = hRule.nextSibling;
                    while (node.firstChild) {
                        node = node.firstChild;
                    }
                    if (node && node.nodeType !== GB.node.text) {
                        node = node.parentNode;
                    }
                    this.placeCaretAt(this.W3CRange ? node : hRule.nextSibling, true);
                } else {
                    if (!hRule.previousSibling) {
                        hRule.parentNode.insertBefore(para.cloneNode(true), hRule);
                    }
                    if (!hRule.nextSibling) {
                        hRule.parentNode.insertBefore(para.cloneNode(true), hRule.nextSibling);
                    }
                    this.placeCaretAt(hRule.nextSibling, hRule.nextSibling.nodeType === GB.node.text);
                }
            } else {
                switch (cmd) {
                    case 'JustifyLeft' :
                    case 'JustifyCenter' :
                    case 'JustifyRight' :
                    case 'JustifyFull' :
                        pNode = this.getRangeElement(this.range);
                        node = null;

                        if (GB.offElementTags[pNode.nodeName.toLowerCase()]) {
                            nodeRange = this.createRange();
                            selection = this.clearSelection();
                            if (this.W3CRange) {
                                nodeRange.selectNode(pNode);
                                selection.addRange(nodeRange);
                            } else {
                                nodeRange.moveToElementText(pNode);
                                nodeRange.select();
                                range = nodeRange;
                            }
                        }
                        // Caption
                        if (pNode.nodeName.toLowerCase() === 'img') {
                            if (pNode.parentNode.nodeName.toLowerCase() === 'figure') {
                                node = pNode.parentNode;
                                if (node.parentNode.nodeName.toLowerCase() === 'div') {
                                    node = node.parentNode;
                                    node.style.textAlign = GB.textAlign[cmd];
                                    break;
                                }
                            }
                        } else if (pNode.nodeName.toLowerCase() === 'figure') {
                            node = pNode.parentNode;
                            if (node.nodeName.toLowerCase() === 'div') {
                                node.style.textAlign = GB.textAlign[cmd];
                                break;
                            }
                        }

                        do {
                            if (pNode.nodeName.toLowerCase() === 'li') {
                                node = pNode;
                                break;
                            }
                            pNode = pNode.parentNode;
                        } while (pNode && pNode.nodeName.toLowerCase() !== 'body');


                        if (node) {
                            node.style.textAlign = GB.textAlign[cmd];
                            break;
                        }

                        range.execCommand(cmd, false, opt);
                        pNode = this.getRangeElement(this.W3CRange ? this.getRange() : range);

                        while (pNode && pNode.nodeName.toLowerCase() !== 'body') {
                            if (typeof pNode.getAttribute !== 'undefined' && pNode.getAttribute('align')) {
                                node = pNode;
                                break;
                            } else {
                                css = this.getCssValue(pNode);
                                if (css) {
                                    for (i = 0; i < css.length; i++) {
                                        if (css[i].name === 'text-align') {
                                            node = pNode;
                                            break;
                                        }
                                    }
                                }
                            }
                            pNode = pNode.parentNode;
                        }

                        if (node) {
                            pNode.style.textAlign = GB.textAlign[cmd];
                            pNode.removeAttribute('align');
                            break;
                        }
                        break;
                    case 'InsertOrderedList' :
                    case 'InsertUnOrderedList' :
                        range.execCommand(cmd, false, opt);
                        if (this.W3CRange) {
                            range = this.getRange();
                            node = range.commonAncestorContainer;
                            if (node.nodeType === GB.node.element && node.lastChild &&
                                    node.lastChild.nodeName.toLowerCase() === 'br') {
                                node.lastChild.className = this.cheditor.bogusSpacerName;
                                isEmpty = true;
                            }
                            found = false;
                            while (node) {
                                if (node.nodeName.toLowerCase() === 'ul' || node.nodeName.toLowerCase() === 'ol') {
                                    found = true;
                                    break;
                                }
                                node = node.parentNode;
                            }
                            if (found) {
                                node.style.listStyleType = '';
                                if (!GB.browser.msie) {
                                    if (node.parentNode.nodeName.toLowerCase() === 'p' ||
                                            node.parentNode.nodeName.toLowerCase() === 'div') {
                                        pNode = node.parentNode;
                                        if (pNode.lastChild && pNode.lastChild.nodeName.toLowerCase() === 'br') {
                                            pNode.removeChild(pNode.lastChild);
                                        }
                                        if (pNode.firstChild === node && pNode.lastChild === node) {
                                            pNode.parentNode.insertBefore(node, pNode);
                                            pNode.parentNode.removeChild(pNode);
                                            this.placeCaretAt(node.lastChild, isEmpty);
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    default :
                        if (range.queryCommandSupported(cmd)) {
                            range.execCommand(cmd, false, opt);
                        }
                }
            }
        } catch (e) {
            alert(cmd + ': 지원되지 않는 명령입니다. ' + e.toString());
        }

        this.toolbarUpdate();
    },

    cleanFromWord : function (tmpDoc) {
        var doc = tmpDoc.body.innerHTML;
        doc = doc.replace(/MsoNormal/g, '');        doc = doc.replace(/<\\?\?xml[^>]*>/g, '');  doc = doc.replace(/<\/?o:p[^>]*>/g, '');
        doc = doc.replace(/<\/?v:[^>]*>/g, '');     doc = doc.replace(/<\/?o:[^>]*>/g, '');     doc = doc.replace(/<\/?st1:[^>]*>/g, '');
        doc = doc.replace(/<!--(.*)-->/g, '');      doc = doc.replace(/<!--(.*)>/g, '');        doc = doc.replace(/<!(.*)-->/g, '');
        doc = doc.replace(/<\\?\?xml[^>]*>/g, '');  doc = doc.replace(/<\/?o:p[^>]*>/g, '');    doc = doc.replace(/<\/?v:[^>]*>/g, '');
        doc = doc.replace(/<\/?o:[^>]*>/g, '');     doc = doc.replace(/<\/?st1:[^>]*>/g, '');   //doc = doc.replace(/lang=.?[^' >]*/ig, '');
        doc = doc.replace(/type=.?[^' >]*/g, '');   doc = doc.replace(/href='#[^']*'/g, '');    doc = doc.replace(/href='#[^']*'/g, '');
        doc = doc.replace(/name=.?[^' >]*/g, '');   doc = doc.replace(/ clear='all'/g, '');     doc = doc.replace(/id='[^']*'/g, '');
        doc = doc.replace(/title='[^']*'/g, '');    doc = doc.replace(/\n/g, '');               doc = doc.replace(/\r/g, '');
        doc = doc.replace(/mso\-[^'>;]*/g, '');     doc = doc.replace(/<p[^>]*/ig, '<p');       doc = doc.replace(/windowtext/ig, '#000000');
        doc = doc.replace(/class=table/ig, '');     doc = doc.replace(/<span[^>]*<\/span>/ig, '');
        return doc;
    },

    printPageBreak : function () {
        var hr = document.createElement('hr'),
            div = this.doc.createElement('div');
        hr.style.pageBreakAfter = 'always';
        hr.style.border = '1px #999 dotted';
        this.insertHTML(hr);
        div.appendChild(this.doc.createTextNode('\u00a0'));
        this.insertHTML(div);
    },

    doCmdPaste : function (html) {
        var range = null;
        this.editAreaFocus();
        if (!this.W3CRange) {
            if (this.range.item) {
                range = this.doc.body.createTextRange();
                if (range) {
                    range.moveToElementText(this.range.item(0));
                    range.select();
                    this.range.item(0).outerHTML = html;
                }
                this.toolbarUpdate();
            } else {
                this.range.pasteHTML(html);
                this.range.select();
            }
        } else {
            this.insertNodeAtSelection(html);
        }
    },

    getPreviousLeaf : function (node) {
        var leaf;
        while (!node.previousSibling) {
            node = node.parentNode;
            if (!node) {
                return node;
            }
        }
        leaf = node.previousSibling;
        while (leaf.lastChild) {
            leaf = leaf.lastChild;
        }
        return leaf;
    },

    getNextLeaf : function (node, breakNode) {
        var leaf;
        while (!node.nextSibling) {
            node = node.parentNode;
            if ((breakNode && breakNode === node) || !node) {
                return node;
            }
        }
        leaf = node.nextSibling;
        if (breakNode && leaf === breakNode) {
            return node;
        }
        while (leaf.firstChild) {
            leaf = leaf.firstChild;
        }
        return leaf;
    },

    isTextVisible : function (text) {
        var i, found = false, len = text.length;
        for (i = 0; i < len; i++) {
            if (text.charAt(i) !== ' ' && text.charAt(i) !== '\t' && text.charAt(i) !== '\r' && text.charAt(i) !== '\n') {
                found = true;
                break;
            }
        }
        return found;
    },

    checkCssValue : function (elem, prop) {
        var css = this.getCssValue(elem), i;
        if (!css) {
            return null;
        }
        for (i = 0; i < css.length; i++) {
            if (css[i].name === prop) {
                return css[i];
            }
        }
        return null;
    },

    getCssValue : function (elem) {
        var i, q, style = [], len, css;

        css = elem.getAttribute('style');
        if (!css) {
            return null;
        }
        if (typeof css === 'object') {
            css = css.cssText;
        }
        if (this.trimSpace(css) === '') {
            return null;
        }

        css = css.replace(/;$/, '').split(';');
        len = css.length;

        for (i = 0; i < len; i++) {
            q = css[i].split(':');
            style.push({'name': this.trimSpace(q[0]).toLowerCase(), 'value': this.trimSpace(q[1]).toLowerCase()});
        }
        return style;
    },

    makeFontCss : function (cmd, opt, elem) {
        switch (cmd) {
            case 'font-size' : elem.style.fontSize = opt; break;
            case 'font-family' : elem.style.fontFamily = opt; break;
            case 'color': elem.style.color = opt; break;
            case 'background-color': elem.style.backgroundColor = opt; break;
        }
    },

    insertStartEndNode : function (range) {
        var startNode = this.doc.createElement('span'),
            startRange, endRange, endNode, collapsed, node = null;

        startNode.id = 'startNode';

        if (!this.W3CRange) {
            startNode.appendChild(this.doc.createTextNode('\u200b'));
            try {
                endRange = range.duplicate();
                startRange = range.duplicate();
                endRange.collapse(false);
                startRange.collapse(true);
            } catch (e) {
                node = this.getRangeElement(range);
                if (node.nodeType === GB.node.element) {

                } else {
                    return null;
                }
            }

            endNode = startNode.cloneNode(true);
            endNode.id = 'endNode';

            if (node) {
                node.parentNode.insertBefore(startNode, node);
                node.parentNode.insertBefore(endNode, node.nextSibling);
                collapsed = false;
            } else {
                collapsed = startRange.isEqual(endRange);
                endRange.pasteHTML(endNode.outerHTML);
                endRange.moveStart('character', -1);
                endNode = endRange.parentElement();

                if (collapsed || range.text.length === 0) {
                    endNode.parentNode.insertBefore(startNode, endNode);
                } else {
                    startNode = endNode.cloneNode(true);
                    startNode.id = 'startNode';
                    startRange.pasteHTML(startNode.outerHTML);
                    startRange.moveStart('character', -1);
                    startNode = startRange.parentElement();
                }
            }
            endRange = null;
            startRange = null;
        } else {
            endRange = range.cloneRange();
            startRange = range.cloneRange();
            startRange.collapse(true);

            endRange.collapse(false);
            endNode = startNode.cloneNode(false);
            endNode.id = 'endNode';

            collapsed = range.collapsed;
            if (collapsed) {
                endRange.insertNode(endNode);
                endNode.parentNode.insertBefore(startNode, endNode);
            } else {
                endRange.insertNode(endNode);
                startRange.insertNode(startNode);
            }

            if (startNode.previousSibling && startNode.previousSibling.nodeType === GB.node.text &&
                startNode.previousSibling.nodeValue === '') {
                startNode.previousSibling.parentNode.removeChild(startNode.previousSibling);
            }
            if (endNode.nextSibling && endNode.nextSibling.nodeType === GB.node.text &&
                endNode.nextSibling.nodeValue === '') {
                endNode.nextSibling.parentNode.removeChild(endNode.nextSibling);
            }
            startRange.detach(); endRange.detach();
            endRange = null; startRange = null;
        }
        return {startNode: startNode, endNode: endNode, collapsed: collapsed};
    },

    removeStartEndNode : function (nodes) {
        if (nodes.startNode) {
            nodes.startNode.parentNode.removeChild(nodes.startNode);
        }
        if (nodes.endNode) {
            nodes.endNode.parentNode.removeChild(nodes.endNode);
        }
    },

    clearCss : function (node, name) {
        var i, css, styles = [];

        if (!node || node.nodeType !== GB.node.element) {
            return null;
        }

        css = this.getCssValue(node);
        if (!css) {
            return null;
        }

        node.removeAttribute('style');
        for (i = 0; i < css.length; i++) {
            if (css[i].name !== name) {
                node.style[css[i].name] = css[i].value;
                styles.push(css[i]);
            }
        }

        return styles.length ? styles : null;
    },

    doCmdPopup : function (cmd, opt, checked) {
        var self = this,
            range, cursor, selectionType, pNode, node, found, isEmpty, span, endNode, startNode, i, endNodeAncestorRange,
            startNodeRange, endNodeRange, compare, tNode, len, selection, child, css, tempNodes, endNodeAncestor,
            backupRange, removeNodes = [], spanNodes = [], applyTextNodes = [], tailNodes = [], headNodes = [], rootNode = null,
            zeroWidth = this.doc.createTextNode('\u200b'), inRange,

            makeSpanText = function (elem, ancestor) {
                if (self.undefined(ancestor)) {
                    ancestor = elem.parentNode;
                }
                span = self.doc.createElement('span');
                self.makeFontCss(cmd, opt, span);
                ancestor.insertBefore(span, elem);
                span.appendChild(elem);
                spanNodes.push(span);
                return span;
            },
            searchTextNode = function (node, match) {
                var i = 0, len = node.childNodes.length;
                for (; i < len; i++) {
                    child = node.childNodes[i];
                    match = searchTextNode(child, match);
                    if (child === startNode) {
                        break;
                    }
                    if (child.nodeType === GB.node.text) {
                        match = true;
                        break;
                    }
                    if (child.nodeType === GB.node.element && child.hasChildNodes() === false &&
                        GB.textFormatTags[child.nodeName.toLowerCase()]) {
                        removeNodes.push(child);
                    }
                }
                return match;
            },
            compareBoundaryPoints = function (range, type, source) {
                var values = {
                    'StartToStart': 0,  //Range.START_TO_START,
                    'StartToEnd': 1,    //Range.START_TO_END,
                    'EndToEnd': 2,      //Range.END_TO_END,
                    'EndToStart': 3     //Range.END_TO_START
                };

                if (self.W3CRange) {
                    return range.compareBoundaryPoints(values[type], source);
                }

                type = type === 'StartToEnd' ? 'EndToStart' : (type === 'EndToStart' ? 'StartToEnd' : type);
                return range.compareEndPoints(type, source);
            },
            rangeSelectNode = function (range, node) {
                if (self.W3CRange) {
                    range.selectNode(node);
                } else {
                    if (node.nodeType === GB.node.text) {
                        node = node.parentNode;
                    }
                    range.moveToElementText(node);
                }
            },
            clearPreviousLeaf = function (node) {
                var leaf, css = [];
                while (!node.previousSibling) {
                    node = node.parentNode;
                    if (!node || node.nodeName.toLowerCase() === 'body') {
                        return null;
                    }
                }

                leaf = node.previousSibling;

                while (leaf.lastChild && leaf.nodeType === GB.node.element && leaf !== startNode) {
                    rangeSelectNode(cursor, leaf);
                    compare = compareBoundaryPoints(cursor, 'StartToStart', startNodeRange);
                    if (compare === 1) {
                        css = self.clearCss(leaf, cmd);
                        if (!css && (leaf.nodeName.toLowerCase() === 'span' || leaf.nodeName.toLowerCase() === 'font')) {
                            while (leaf.firstChild) {
                                leaf.parentNode.insertBefore(leaf.firstChild, leaf);
                            }
                            removeNodes.push(leaf);
                            break;
                        }
                    } else if (compare === 0) {
                        self.clearCss(leaf, cmd);
                        if (!rootNode && leaf.nodeName.toLowerCase() === 'span') {
                            rootNode = leaf;
                        }
                    } else { // -1
                        node = startNode;
                        found = false;
                        while (node) {
                            if (node.nodeType === GB.node.text) {
                                found = true;
                                break;
                            }
                            node = checkPreviousLeaf(node, leaf);
                        }
                        if (!found) {
                            self.clearCss(leaf, cmd);
                            if (!rootNode && leaf.nodeName.toLowerCase() === 'span') {
                                rootNode = leaf;
                            }
                        }
                    }

                    leaf = leaf.lastChild;
                }

                if (leaf.nodeType === GB.node.text && self.isTextVisible(leaf.nodeValue)) {
                    applyTextNodes.push(leaf);
                }

                return leaf;
            },
            checkPreviousLeaf = function (node, breakNode) {
                var leaf;
                while (!node.previousSibling) {
                    node = node.parentNode;
                    if (node === breakNode || node.nodeName.toLowerCase() === 'body') {
                        return null;
                    }
                }
                leaf = node.previousSibling;
                while (leaf.lastChild) {
                    leaf = leaf.lastChild;
                }
                return leaf;
            },
            checkInRange = function (range, source) {
                return (typeof range.inRange !== 'undefined') ? range.inRange(source) :
                    (compareBoundaryPoints(range, 'StartToStart', source) < 1
                        && compareBoundaryPoints(range, 'EndToEnd', source) > -1);
            },
            checkNextLeaf = function (node) {
                var leaf, inRange;
                while (!node.nextSibling) {
                    node = node.parentNode;

                    if (!node || node.nodeName.toLowerCase() === 'body') {
                        return null;
                    }

                    rangeSelectNode(cursor, node);
                    inRange = checkInRange(cursor, endNodeRange);
                    if (inRange) {
                        inRange = checkInRange(cursor, startNodeRange);
                        if (!inRange) {
                            tailNodes.push(node);
                        } else {
                            headNodes.push(node);
                        }
                    }

                    if (node === endNodeAncestor) {
                        return null;
                    }
                }

                leaf = node.nextSibling;
                if (leaf.nodeType === GB.node.text || !endNodeAncestor) {
                    return null;
                }

                rangeSelectNode(cursor, leaf);
                inRange = checkInRange(endNodeAncestorRange, cursor);
                if (!inRange) {
                    return null;
                }

                while (leaf.firstChild) {
                    leaf = leaf.firstChild;
                }

                if (leaf.nodeType === GB.node.text) {
                    tailNodes = [];
                    return null;
                }
                return leaf;
            },
            checkParentSpan = function (node) {
                var len = spanNodes.length, i = 0;
                for (; i < len; i++) {
                    if (spanNodes[i] === node) {
                        return true;
                    }
                }
                return false;
            };

        this.editAreaFocus();

        backupRange = this.restoreRange();
        selectionType = this.getSelectionType();

        if (this.W3CRange) {
            range = this.doc;
        } else {
            range = (selectionType === GB.selection.none) ? this.doc : backupRange;
        }

        try {
            if (cmd === 'LineHeight') {
                this.applyLineHeight(opt);
            } else {
                if (cmd === 'InsertOrderedList' || cmd === 'InsertUnOrderedList') {
                    if (checked !== true) {
                        range.execCommand(cmd, false, opt);
                        if (!GB.browser.msie) {
                            range = this.getRange();
                            node = range.commonAncestorContainer;
                            found = isEmpty = false;

                            if (node.nodeType === GB.node.element && node.lastChild &&
                                    node.lastChild.nodeName.toLowerCase() === 'br') {
                                node.lastChild.className = this.cheditor.bogusSpacerName;
                                isEmpty = true;
                            }
                            while (node) {
                                if (node.nodeName.toLowerCase() === 'ul' || node.nodeName.toLowerCase() === 'ol') {
                                    found = true;
                                    break;
                                }
                                node = node.parentNode;
                            }

                            if (found) {
                                if (node.parentNode.nodeName.toLowerCase() === 'p' ||
                                        node.parentNode.nodeName.toLowerCase() === 'div') {
                                    pNode = node.parentNode;
                                    if (pNode.lastChild && pNode.lastChild.nodeName.toLowerCase() === 'br') {
                                        pNode.removeChild(pNode.lastChild);
                                    }
                                    if (pNode.firstChild === node && pNode.lastChild === node) {
                                        pNode.parentNode.insertBefore(node, pNode);
                                        pNode.parentNode.removeChild(pNode);
                                        this.placeCaretAt(node.lastChild, isEmpty);
                                    }
                                }
                            }
                        }
                    }
                    cursor = this.getRange();
                    pNode = this.W3CRange ? cursor.commonAncestorContainer : cursor.parentElement();
                    if (pNode.nodeType === GB.node.text) {
                        pNode = pNode.parentNode;
                    }
                    while (pNode) {
                        if (pNode.nodeName.toLowerCase() === 'ol' || pNode.nodeName.toLowerCase() === 'ul') {
                            if (opt === 'desc' || opt === 'decimal') {
                                opt = '';
                            }
                            pNode.style.listStyleType = opt;
                            break;
                        }
                        pNode = pNode.parentNode;
                    }
                } else if (cmd === 'FontSize' || cmd === 'FontName' || cmd === 'ForeColor' || cmd === 'BackColor') {
                    if (cmd === 'ForeColor' || cmd === 'BackColor') {
                        opt = this.colorConvert(opt, 'hex');
                    } else if (cmd === 'FontName' && opt === '맑은 고딕') {
                        opt += ', "Malgun Gothic"';
                    }

                    cmd = GB.fontStyle[cmd];

                    range = this.getRange();
                    tempNodes = this.insertStartEndNode(range);
                    startNode = tempNodes.startNode;
                    endNode = tempNodes.endNode;

                    cursor = this.createRange();
                    startNodeRange = this.createRange();
                    rangeSelectNode(startNodeRange, startNode);

                    endNodeRange = this.createRange();
                    endNodeAncestorRange = this.createRange();

                    if (!tempNodes.collapsed) {
                        tailNodes = [];
                        endNodeAncestor = null;
                        rangeSelectNode(endNodeRange, endNode);
                        node = endNode.parentNode;

                        while (node && node.nodeName.toLowerCase() !== 'body') {
                            endNodeAncestor = node;
                            node = node.parentNode;
                        }

                        if (endNodeAncestor) {
                            rangeSelectNode(endNodeAncestorRange, endNodeAncestor);
                        }

                        node = endNode;
                        while (node) {
                            node = checkNextLeaf(node);
                        }

                        for (i = 0; i < tailNodes.length; i++) {
                            this.clearCss(tailNodes[i], cmd);
                        }

                        node = endNode;
                        while (node && node !== startNode) {
                            node = clearPreviousLeaf(node);
                        }

                        if (headNodes.length) {
                            found = false;
                            node = startNode;
                            while (node) {
                                if (node.nodeType === GB.node.text) {
                                    found = true;
                                    break;
                                }
                                node = checkPreviousLeaf(node, endNodeAncestor);
                            }
                            if (!found) {
                                for (i = 0; i < headNodes.length; i++) {
                                    self.clearCss(headNodes[i], cmd);
                                    css = self.getCssValue(headNodes[i]);
                                    if (!css && headNodes[i].nodeName.toLowerCase() === 'span') {
                                        child = headNodes[i];
                                        while (child.firstChild) {
                                            child.parentNode.insertBefore(child.firstChild, child);
                                        }
                                        child.parentNode.removeChild(child);
                                        headNodes.splice(i);
                                    }
                                }
                            }
                        }

                        for (i = 0; i < removeNodes.length; i++) {
                            removeNodes[i].parentNode.removeChild(removeNodes[i]);
                        }
                        removeNodes = [];

                        len = applyTextNodes.length;
                        if (rootNode) {
                            rangeSelectNode(startNodeRange, rootNode);
                        }

                        for (i = 0; i < len; i++) {
                            tNode = applyTextNodes[i];

                            if (tNode.previousSibling && tNode.previousSibling.nodeType === GB.node.text) {
                                if (applyTextNodes[i + 1] && tNode.previousSibling === applyTextNodes[i + 1]) {
                                    applyTextNodes[i + 1].nodeValue += tNode.nodeValue;
                                    tNode.parentNode.removeChild(tNode);
                                } else {
                                    tNode.nodeValue = tNode.previousSibling.nodeValue + tNode.nodeValue;
                                    tNode.previousSibling.parentNode.removeChild(tNode.previousSibling);
                                    i--;
                                }
                                continue;
                            }

                            pNode = tNode.parentNode;

                            if (rootNode) {
                                rangeSelectNode(cursor, pNode);
                                inRange = checkInRange(startNodeRange, cursor);
                                if (inRange) {
                                    self.makeFontCss(cmd, '', pNode);
                                    self.makeFontCss(cmd, opt, rootNode);
                                    continue;
                                }
                            }
                            if (pNode.nodeName.toLowerCase() === 'span'
                                && (pNode.firstChild === tNode  || pNode.firstChild === startNode)
                                && (pNode.lastChild === tNode || pNode.lastChild === endNode)) {
                                self.makeFontCss(cmd, opt, pNode);
                                spanNodes.push(pNode);
                                continue;
                            }
                            makeSpanText(tNode);
                        }

                        len = spanNodes.length;
                        for (i = 0; i < len; i++) {
                            child = spanNodes[i];
                            pNode = child.parentNode;
                            if (pNode.nodeName.toLowerCase() === 'span') {
                                if (checkParentSpan(pNode)) {
                                    self.makeFontCss(cmd, '', child);
                                    css = self.getCssValue(child);
                                    if (!css) {
                                        while (child.firstChild) {
                                            pNode.insertBefore(child.firstChild, child);
                                        }
                                        pNode.removeChild(child);
                                        continue;
                                    }
                                }
                                while (pNode && pNode.nodeName.toLowerCase() === 'span') {
                                    if (pNode.firstChild === child && pNode.lastChild === child) {
                                        css = self.getCssValue(pNode);
                                        if (css) {
                                            self.makeFontCss(cmd, '', pNode);
                                            css = self.getCssValue(pNode);
                                            if (!css) {
                                                pNode.parentNode.insertBefore(child, pNode);
                                                pNode.parentNode.removeChild(pNode);
                                                pNode = child;
                                                continue;
                                            }
                                        }
                                    }
                                    pNode = pNode.parentNode;
                                }
                            }
                        }

                        if (this.W3CRange) {
                            selection = this.getSelection();
                            if (selection.rangeCount > 0) {
                                selection.removeAllRanges();
                            }
                            range = this.createRange();
                            range.setStartAfter(startNode);
                            range.setEndBefore(endNode);
                            selection.addRange(range);
                        } else {
                            startNodeRange = this.createRange();
                            endNodeRange = startNodeRange.duplicate();
                            startNodeRange.moveToElementText(startNode);
                            endNodeRange.moveToElementText(endNode);
                            startNodeRange.setEndPoint('StartToEnd', startNodeRange);
                            startNodeRange.setEndPoint('EndToStart', endNodeRange);
                            startNodeRange.select();
                        }
                    } else {
                        pNode = startNode.parentNode;
                        found = false;
                        while (pNode && pNode.nodeName.toLowerCase() === 'span' && !found) {
                            css = self.getCssValue(pNode);
                            if (css) {
                                len = css.length;
                                for (i = 0; i < len; i++) {
                                    if (css[i].name === cmd && css[i].value === opt) {
                                        found = true;
                                        break;
                                    }
                                }
                            }
                            pNode = pNode.parentNode;
                        }

                        if (!found) {
                            span = this.doc.createElement('span');
                            this.makeFontCss(cmd, opt, span);
                            span.appendChild(zeroWidth);
                            endNode.parentNode.insertBefore(span, endNode);

                            if (this.W3CRange) {
                                selection = this.getSelection();
                                selection.collapse(zeroWidth, 1);
                            } else {
                                range = this.getRange();
                                range.moveToElementText(span);
                                range.collapse(false);
                                range.select();
                            }
                        }
                    }
                    this.removeStartEndNode(tempNodes);
                } else {
                    range.execCommand(cmd, false, opt);
                }
            }
        } catch (e) {
            alert(e.toString());
        }

        this.toolbarUpdate();
        this.boxHideAll();
    },

    modifyImage : function (img) {
        var self = this,
            idx, div, ico, inputCaption, wrapTextSpan, wrapTextCheckBox, wrapTextIcon, wrapTextChecked,
            width = 0, height = 0, wrapElem, currentCaption = '', caption = null, inputAlt, inputTitle, cssFloat,
            wrapperClassName = 'cheditor-caption-wrapper',
            figureClassName = 'cheditor-caption',
            figCaptionClassName = 'cheditor-caption-text',
            imageWidthOpt = {
                orig    : { size: 'normal', desc: '원본 크기' },
                fitpage : { size: '100%',   desc: '페이지 크기에 맞춤' },
                px160   : { size: 160,      desc: '썸네일, 160 픽셀' },
                px320   : { size: 320,      desc: '작은 크기, 320 픽셀' },
                px640   : { size: 640,      desc: '중간 크기, 640 픽셀' },
                px1024  : { size: 1024,     desc: '크게, 1024 픽셀' },
                px1600  : { size: 1600,     desc: '아주 크게, 1600 픽셀' }
            },
            captionAlignOpt = { left: '왼쪽', center: '가운데', right: '오른쪽' },
            imageFloatOpt = {
                left : {
                    value : '왼쪽',
                    input : null
                },
                right : {
                    value : '오른쪽',
                    input : null
                }
            },
            fmSelectWidth = document.createElement('select'),
            fmSelectCaptionAlign = document.createElement('select'),

            onChangeEventHandler = function () {
                if (self.editImages[img.src] && self.editImages[img.src].width) {
                    width = self.editImages[img.src].width;
                    if (self.editImages[img.src] && self.editImages[img.src].height) {
                        height = self.editImages[img.src].height;
                    } else {
                        height = img.height;
                    }
                } else if (img.width) {
                    width = img.width;
                } else {
                    return;
                }

                switch (this.value) {
                    case 'orig' :
                        width = width + 'px';
                        height = (height || img.height) + 'px';
                        break;
                    case 'fitpage' :
                        width = '100%';
                        height = 'auto';
                        break;
                    default :
                        width = imageWidthOpt[this.value].size;
                        if (img.height) {
                            height = Math.round((img.height * width) / img.width) + 'px';
                        }
                        width += 'px';
                }

                if (width) {
                    img.style.width = width;
                    if (caption && caption.figure) {
                        caption.figure.style.width = width;
                    }
                }
                if (height) {
                    img.style.height = height;
                }
            },
            setCssFloat = function (elem, css) {
                if (typeof elem.style.styleFloat === 'undefined') {
                    elem.style.cssFloat = css;
                } else {
                    elem.style.styleFloat = css;
                }
            },
            getCssFloat = function (elem) {
                return (typeof elem.style.styleFloat === 'undefined') ? elem.style.cssFloat : elem.style.styleFloat;
            },
            clearCssFloat = function (elem) {
                setCssFloat(elem, '');
                elem.style.marginLeft = '';
                elem.style.marginRight = '';
            },
            createInputForm = function (type, name, value, classname) {
                var input = document.createElement('input');
                input.setAttribute('type', type);
                input.setAttribute('name', name);
                input.setAttribute('value', value);
                input.className = classname;
                return input;
            },
            applyWrapText = function (elem) {
                if (!elem) {
                    return;
                }
                wrapElem = (caption && caption.wrapper) ? caption.wrapper : img;
                if (elem.checked) {
                    imageFloatOpt[(elem.name === 'left' ? 'right' : 'left')].input.checked = false;
                    setCssFloat(wrapElem, elem.name);
                    if (elem.name === 'left') {
                        wrapElem.style.marginRight = '1em';
                        wrapElem.style.marginLeft = '';
                    } else {
                        wrapElem.style.marginLeft = '1em';
                        wrapElem.style.marginRight = '';
                    }
                    if (caption && caption.wrapper) {
                        clearCssFloat(img);
                    }
                } else {
                    clearCssFloat(wrapElem);
                }
            },
            getCaptionNodes = function (img) {
                var nodes = { figure: null, figCaption: [], captionText: '', img: null, wrapper: null},
                    pNode, node;
                pNode = img.parentNode;
                if (!pNode || pNode.nodeName.toLowerCase() !== 'figure') {
                    return null;
                }
                nodes.figure = pNode;
                nodes.figCaption = pNode.getElementsByTagName('figcaption');
                for (idx = 0; idx < nodes.figCaption.length; idx++) {
                    node = nodes.figCaption[idx].firstChild;
                    while (node) {
                        if (node.nodeType === GB.node.text) {
                            nodes.captionText += self.trimSpace(node.nodeValue);
                        }
                        node = node.nextSibling;
                    }
                }
                if (pNode.parentNode.nodeName.toLowerCase() === 'div' && pNode.parentNode.className === wrapperClassName) {
                    nodes.wrapper = pNode.parentNode;
                }
                nodes.img = img;
                return nodes;
            },
            applyAlt = function () {
                var alt = self.trimSpace(inputAlt.value);
                if (alt !== '') {
                    img.setAttribute('alt', alt);
                }
                self.removeEvent(inputAlt, 'blur', applyAlt);
                self.cheditor.modifyState = false;
            },
            applyTitle = function () {
                var title = self.trimSpace(inputTitle.value);
                if (title !== '') {
                    img.setAttribute('title', title);
                }
                self.removeEvent(inputTitle, 'blur', applyTitle);
                self.cheditor.modifyState = false;
            },
            applyCaption = function () {
                var figure = self.doc.createElement('figure'),
                    figCaption = self.doc.createElement('figcaption'),
                    wrapper = self.doc.createElement('div'),
                    pNode = img.parentNode, i, para = self.doc.createElement('p');

                self.removeEvent(inputCaption, 'blur', applyCaption);

                wrapper.className = wrapperClassName;
                figure.className = figureClassName;
                figCaption.className = figCaptionClassName;

                this.value = self.trimSpace(inputCaption.value);
                if (!self.isTextVisible(this.value) && caption) {
                    caption.wrapper.parentNode.insertBefore(para, caption.wrapper);
                    para.appendChild(caption.img);
                    cssFloat = getCssFloat(caption.wrapper);
                    caption.wrapper.parentNode.removeChild(caption.wrapper);
                    caption = getCaptionNodes(img);
                    if (cssFloat && (cssFloat === 'left' || cssFloat === 'right')) {
                        applyWrapText(imageFloatOpt[cssFloat].input);
                    }
                    self.cheditor.modifyState = false;
                    return;
                }
                if (currentCaption === this.value) {
                    self.cheditor.modifyState = false;
                    return;
                }

                if (caption && caption.figure) {
                    for (i = 0; i < caption.figCaption.length; i++) {
                        caption.figure.removeChild(caption.figCaption[i]);
                    }
                    figure = caption.figure;
                } else {
                    if (pNode.nodeName.toLowerCase() !== 'body') {
                        pNode.parentNode.insertBefore(wrapper, pNode.nextSibling);
                    } else {
                        pNode.insertBefore(wrapper, img);
                    }

                    if (self.config.imgCaptionWrapper !== '') {
                        wrapper.setAttribute('style', self.config.imgCaptionWrapper);
                    }

                    wrapper.appendChild(figure);
                    figure.setAttribute('style', self.config.imgCaptionFigure);
                    figure.style.display = 'inline-block';
                    figure.style.width = img.width;
                    figure.appendChild(img);
                    para.appendChild(self.createNbspTextNode());
                    wrapper.parentNode.insertBefore(para, wrapper.nextSibling);
                }

                figure.appendChild(figCaption);
                figCaption.setAttribute('style', self.config.imgCaptionText);
                figCaption.appendChild(self.doc.createTextNode(this.value));

                if (!pNode.hasChildNodes()) {
                    pNode.parentNode.removeChild(pNode);
                }

                caption = getCaptionNodes(img);
                cssFloat = getCssFloat(img);
                if (cssFloat && (cssFloat === 'left' || cssFloat === 'right')) {
                    applyWrapText(imageFloatOpt[cssFloat].input);
                }
                self.cheditor.modifyState = false;
            };

        for (idx in imageWidthOpt) {
            if (imageWidthOpt.hasOwnProperty(idx)) {
                fmSelectWidth.options[fmSelectWidth.options.length] = new Option(imageWidthOpt[idx].desc, idx);
            }
        }

        fmSelectWidth.onchange = onChangeEventHandler;
        caption = getCaptionNodes(img);

        div = document.createElement('div');
        div.style.textAlign = 'left';
        ico = new Image();
        ico.src = this.config.iconPath + 'image_resize.png';
        ico.className = 'cheditor-ico';
        div.appendChild(ico);
        div.appendChild(fmSelectWidth);

        wrapTextChecked = getCssFloat((caption && caption.wrapper) ? caption.wrapper : img);

        for (idx in imageFloatOpt) {
            if (imageFloatOpt.hasOwnProperty(idx)) {
                wrapTextCheckBox = createInputForm('checkbox', idx, '1', 'wrap-checked');
                wrapTextCheckBox.setAttribute('id', 'idWrapText-' + idx);
                wrapTextCheckBox.onclick = function () {
                    applyWrapText(this);
                };
                imageFloatOpt[idx].input = wrapTextCheckBox;
                wrapTextSpan = document.createElement('span');
                wrapTextIcon = new Image();
                wrapTextSpan.className = 'wrap-text-desc';

                if (idx === 'left') {
                    wrapTextSpan.style.marginLeft = '20px';
                }
                if (wrapTextChecked === idx) {
                    wrapTextCheckBox.checked = 'checked';
                }
                wrapTextSpan.appendChild(wrapTextCheckBox);
                wrapTextIcon.className = 'cheditor-ico';
                wrapTextIcon.src = this.config.iconPath + 'image_align_' + idx + '_wt.png';
                wrapTextSpan.appendChild(wrapTextIcon);
                div.appendChild(wrapTextSpan);
            }
        }

        if (self.undefined(self.editImages[img.src])) {
            self.editImages[img.src] = { width: img.width, height: img.height };
        }

        div.appendChild(document.createTextNode('\u00a0\u00a0Alt:'));
        inputAlt = createInputForm('text', 'inputAlt', '', 'user-input-alt');
        inputAlt.onfocus = function () {
            self.cheditor.modifyState = true;
            self.addEvent(inputAlt, 'blur', applyAlt);
        };
        div.appendChild(inputAlt);
        if (img.getAttribute('alt')) {
            inputAlt.value = img.getAttribute('alt');
        }

        div.appendChild(document.createTextNode('타이틀:'));
        inputTitle = createInputForm('text', 'inputTitle', '', 'user-input-alt');
        inputTitle.onfocus = function () {
            self.cheditor.modifyState = true;
            self.addEvent(inputTitle, 'blur', applyTitle);
        };
        div.appendChild(inputTitle);
        if (img.getAttribute('title')) {
            inputTitle.value = img.getAttribute('title');
        }

        div.appendChild(document.createElement('br'));
        div.appendChild(document.createTextNode('사진 캡션:'));

        inputCaption = createInputForm('text', 'inputCaption', '', 'user-input-caption');
        inputCaption.onfocus = function () {
            caption = getCaptionNodes(img);
            self.cheditor.modifyState = true;
            self.addEvent(inputCaption, 'blur', applyCaption);

        };
        div.appendChild(inputCaption);

        div.appendChild(document.createTextNode('캡션 텍스트 정렬:'));
        for (idx in captionAlignOpt) {
            if (captionAlignOpt.hasOwnProperty(idx)) {
                fmSelectCaptionAlign.options[fmSelectCaptionAlign.options.length] = new Option(captionAlignOpt[idx], idx);
                if (caption && caption.figCaption[0] && caption.figCaption[0].style.textAlign) {
                    if (idx === caption.figCaption[0].style.textAlign) {
                        fmSelectCaptionAlign.options[fmSelectCaptionAlign.options.length - 1].selected = true;
                    }
                }
            }
        }

        fmSelectCaptionAlign.className = 'caption-align';
        fmSelectCaptionAlign.onchange = function () {
            caption = getCaptionNodes(img);
            if (!caption) {
                return;
            }
            for (idx = 0; idx < caption.figCaption.length; idx++) {
                caption.figCaption[idx].style.textAlign = this.value;
            }
        };
        div.appendChild(fmSelectCaptionAlign);

        if (caption && caption.captionText) {
            currentCaption = self.trimSpace(caption.captionText);
            inputCaption.value = currentCaption;
        }

        while (self.cheditor.editBlock.firstChild) {
            self.cheditor.editBlock.removeChild(self.cheditor.editBlock.firstChild);
        }
        self.cheditor.editBlock.appendChild(div);
    },

    modifyCell : function (ctd) {
        var self = this,
            ctb = ctd,
            ctr = ctb,
            tm = [], i, jr, j, jh, jv, rowIndex = 0, realIndex = 0, newc, newr, nc, tempr, rows, span, icon,
            div = document.createElement('div'),

            getCellMatrix = function () {
                rows = (ctb.rows && ctb.rows.length > 0) ? ctb.rows : ctb.getElementsByTagName('tr');
                for (i = 0; i < rows.length; i++) {
                    tm[i] = [];
                }
                for (i = 0; i < rows.length; i++) {
                    jr = 0;
                    for (j = 0; j < rows[i].cells.length; j++) {
                        while (!(self.undefined(tm[i][jr]))) {
                            jr++;
                        }
                        for (jh = jr; jh < jr + (rows[i].cells[j].colSpan || 1); jh++) {
                            for (jv = i; jv < i + (rows[i].cells[j].rowSpan || 1); jv++) {
                                tm[jv][jh] = (jv === i) ? rows[i].cells[j].cellIndex : -1;
                            }
                        }
                    }
                }
                return tm;
            },
            insertColumn = function () {
                tm = getCellMatrix();
                rows = (ctb.rows && ctb.rows.length > 0) ? ctb.rows : ctb.getElementsByTagName('tr');

                if (ctr.rowIndex >= 0) {
                    rowIndex = ctr.rowIndex;
                } else {
                    for (i = 0; i < rows.length; i++) {
                        if (rows[i] === ctr) {
                            rowIndex = i;
                            break;
                        }
                    }
                }

                for (j = 0; j < tm[rowIndex].length; j++) {
                    if (tm[rowIndex][j] === ctd.cellIndex) {
                        realIndex = j;
                        break;
                    }
                }

                for (i = 0; i < rows.length; i++) {
                    if (tm[i][realIndex] !== -1) {
                        if (rows[i].cells[tm[i][realIndex]].colSpan > 1) {
                            rows[i].cells[tm[i][realIndex]].colSpan++;
                        } else {
                            newc = rows[i].insertCell(tm[i][realIndex] + 1);
                            nc = rows[i].cells[tm[i][realIndex]].cloneNode(false);
                            nc.innerHTML = '&nbsp;';
                            rows[i].replaceChild(nc, newc);
                        }
                    }
                }
            },
            insertRow = function (idx) {
                newr = ctb.insertRow(ctr.rowIndex + 1);
                for (i = 0; i < ctr.cells.length; i++) {
                    if (ctr.cells[i].rowSpan > 1) {
                        ctr.cells[i].rowSpan++;
                    } else {
                        newc = ctr.cells[i].cloneNode(false);
                        newc.innerHTML = '&nbsp;';
                        newr.appendChild(newc);
                    }
                }

                for (i = 0; i < ctr.rowIndex; i++) {
                    if (ctb.rows && ctb.rows.length > 0) {
                        tempr = ctb.rows[i];
                    } else {
                        tempr = ctb.getElementsByTagName('tr')[i];
                    }
                    for (j = 0; j < tempr.cells.length; j++) {
                        if (tempr.cells[j].rowSpan > (ctr.rowIndex - i)) {
                            tempr.cells[j].rowSpan++;
                        }
                    }
                }
            },
            deleteColumn = function () {
                tm = getCellMatrix(ctb);
                rows = (ctb.rows && ctb.rows.length > 0) ? ctb.rows : ctb.getElementsByTagName('tr');
                rowIndex = 0; realIndex = 0;

                if (ctr.rowIndex >= 0) {
                    rowIndex = ctr.rowIndex;
                } else {
                    for (i = 0; i < rows.length; i++) {
                        if (rows[i] === ctr) {
                            rowIndex = i;
                            break;
                        }
                    }
                }

                if (tm[0].length <= 1) {
                    ctb.parentNode.removeChild(ctb);
                } else {
                    for (j = 0; j < tm[rowIndex].length; j++) {
                        if (tm[rowIndex][j] === ctd.cellIndex) {
                            realIndex = j;
                            break;
                        }
                    }

                    for (i = 0; i < rows.length; i++) {
                        if (tm[i][realIndex] !== -1) {
                            if (rows[i].cells[tm[i][realIndex]].colSpan > 1) {
                                rows[i].cells[tm[i][realIndex]].colSpan--;
                            } else {
                                rows[i].deleteCell(tm[i][realIndex]);
                            }
                        }
                    }
                }
            },
            deleteRow = function () {
                var curCI = -1, prevCI, ni, nrCI, cs, nj;

                tm = getCellMatrix(ctb);
                rows = (ctb.rows && ctb.rows.length > 0) ? ctb.rows : ctb.getElementsByTagName('TR');
                rowIndex = 0;

                if (ctr.rowIndex >= 0) {
                    rowIndex = ctr.rowIndex;
                } else {
                    for (i = 0; i < rows.length; i++) {
                        if (rows[i] === ctr) {
                            rowIndex = i;
                            break;
                        }
                    }
                }

                if (rows.length <= 1) {
                    ctb.parentNode.removeChild(ctb);
                } else {
                    for (i = 0; i < rowIndex; i++) {
                        tempr = rows[i];
                        for (j = 0; j < tempr.cells.length; j++) {
                            if (tempr.cells[j].rowSpan > (rowIndex - i)) {
                                tempr.cells[j].rowSpan--;
                            }
                        }
                    }

                    for (i = 0; i < tm[rowIndex].length; i++) {
                        prevCI = curCI;
                        curCI = tm[rowIndex][i];

                        if (curCI !== -1 && curCI !== prevCI && ctr.cells[curCI].rowSpan > 1 && (rowIndex + 1) < rows.length) {
                            ni = i;
                            nrCI = tm[rowIndex + 1][ni];
                            while (nrCI === -1) {
                                ni++;
                                nrCI = (ni < rows[rowIndex + 1].cells.length) ? tm[rowIndex + 1][ni] : rows[rowIndex + 1].cells.length;
                            }

                            newc = rows[rowIndex + 1].insertCell(nrCI);
                            rows[rowIndex].cells[curCI].rowSpan--;
                            nc = rows[rowIndex].cells[curCI].cloneNode(false);
                            rows[rowIndex + 1].replaceChild(nc, newc);

                            cs = (ctr.cells[curCI].colSpan > 1) ? ctr.cells[curCI].colSpan : 1;
                            nj = 0;

                            for (j = i; j < (i + cs); j++) {
                                tm[rowIndex + 1][j] = nrCI;
                                nj = j;
                            }
                            for (j = nj; j < tm[rowIndex + 1].length; j++) {
                                if (tm[rowIndex + 1][j] !== -1) {
                                    tm[rowIndex + 1][j]++;
                                }
                            }
                        }
                    }

                    if (ctb.rows && ctb.rows.length > 0) {
                        ctb.deleteRow(rowIndex);
                    } else {
                        ctb.removeChild(rows[rowIndex]);
                    }
                }
            },
            mergeCellRight = function () {
                tm = getCellMatrix(ctb);
                rows = (ctb.rows && ctb.rows.length > 0) ? ctb.rows : ctb.getElementsByTagName('tr');
                rowIndex = 0; realIndex = 0;

                if (ctr.rowIndex >= 0) {
                    rowIndex = ctr.rowIndex;
                } else {
                    for (i = 0; i < rows.length; i++) {
                        if (rows[i] === ctr) {
                            rowIndex = i;
                            break;
                        }
                    }
                }

                for (j = 0; j < tm[rowIndex].length; j++) {
                    if (tm[rowIndex][j] === ctd.cellIndex) {
                        realIndex = j;
                        break;
                    }
                }

                if (ctd.cellIndex + 1 < ctr.cells.length) {
                    var ccrs = ctd.rowSpan || 1,
                        cccs = ctd.colSpan || 1,
                        ncrs = ctr.cells[ctd.cellIndex + 1].rowSpan || 1,
                        nccs = ctr.cells[ctd.cellIndex + 1].colSpan || 1,
                        html;

                    j = realIndex;

                    while (tm[rowIndex][j] === ctd.cellIndex) {
                        j++;
                    }

                    if (tm[rowIndex][j] === ctd.cellIndex + 1) {
                        if (ccrs === ncrs) {
                            if (rows.length > 1) {
                                ctd.colSpan = cccs + nccs;
                            }
                            html = self.trimSpace(ctr.cells[ctd.cellIndex + 1].innerHTML);
                            html = html.replace(/^&nbsp;/, '');
                            ctd.innerHTML += html;
                            ctr.deleteCell(ctd.cellIndex + 1);
                        }
                    }
                }
            },
            mergeCellDown = function () {
                var crealIndex = 0,
                    ccrs = ctd.rowSpan || 1,
                    cccs = ctd.colSpan || 1,
                    ncellIndex, html, ncrs, nccs;

                tm = getCellMatrix(ctb);
                rows = (ctb.rows && ctb.rows.length > 0) ? ctb.rows : ctb.getElementsByTagName('tr');
                rowIndex = 0;

                if (ctr.rowIndex >= 0) {
                    rowIndex = ctr.rowIndex;
                } else {
                    for (i = 0; i < rows.length; i++) {
                        if (rows[i] === ctr) {
                            rowIndex = i;
                            break;
                        }
                    }
                }

                for (i = 0; i < tm[rowIndex].length; i++) {
                    if (tm[rowIndex][i] === ctd.cellIndex) {
                        crealIndex = i;
                        break;
                    }
                }

                if (rowIndex + ccrs < rows.length) {
                    ncellIndex = tm[rowIndex + ccrs][crealIndex];
                    if (ncellIndex !== -1 &&
                        (crealIndex === 0 || (crealIndex > 0 &&
                        (tm[rowIndex + ccrs][crealIndex - 1] !== tm[rowIndex + ccrs][crealIndex]))))
                    {
                        ncrs = rows[rowIndex + ccrs].cells[ncellIndex].rowSpan || 1;
                        nccs = rows[rowIndex + ccrs].cells[ncellIndex].colSpan || 1;
                        if (cccs === nccs) {
                            html = self.trimSpace(rows[rowIndex + ccrs].cells[ncellIndex].innerHTML);
                            html = html.replace(/^&nbsp;/, '');
                            ctd.innerHTML += html;
                            rows[rowIndex + ccrs].deleteCell(ncellIndex);
                            ctd.rowSpan = ccrs + ncrs;
                        }
                    }
                }
            },
            splitCellVertical = function () {
                var ri, cs;
                tm = getCellMatrix();
                rowIndex = 0; realIndex = 0;

                rows = (ctb.rows && ctb.rows.length > 0) ? ctb.rows : ctb.getElementsByTagName('TR');

                if (ctr.rowIndex >= 0) {
                    rowIndex = ctr.rowIndex;
                } else {
                    for (ri = 0; ri < rows.length; ri++) {
                        if (rows[ri] === ctr) {
                            rowIndex = ri;
                            break;
                        }
                    }
                }

                for (j = 0; j < tm[rowIndex].length; j++) {
                    if (tm[rowIndex][j] === ctd.cellIndex) {
                        realIndex = j;
                        break;
                    }
                }

                if (ctd.colSpan > 1) {
                    newc = rows[rowIndex].insertCell(ctd.cellIndex + 1);
                    ctd.colSpan--;
                    nc = ctd.cloneNode(false);
                    nc.innerHTML = '&nbsp;';
                    rows[rowIndex].replaceChild(nc, newc);
                    ctd.colSpan = 1;
                    ctd.removeAttribute('colSpan');
                } else {
                    newc = rows[rowIndex].insertCell(ctd.cellIndex + 1);
                    nc = ctd.cloneNode(false);
                    nc.innerHTML = '&nbsp;';
                    rows[rowIndex].replaceChild(nc, newc);
                    for (i = 0; i < tm.length; i++) {
                        if (i !== rowIndex && tm[i][realIndex] !== -1) {
                            cs = (rows[i].cells[tm[i][realIndex]].colSpan > 1) ? rows[i].cells[tm[i][realIndex]].colSpan : 1;
                            rows[i].cells[tm[i][realIndex]].colSpan = cs + 1;
                        }
                    }
                }
            },
            splitCellHorizontal = function () {
                var ni, rs;
                tm = getCellMatrix();
                rowIndex = 0; realIndex = 0;
                rows = (ctb.rows && ctb.rows.length > 0) ? ctb.rows : ctb.getElementsByTagName('TR');

                if (ctr.rowIndex >= 0) {
                    rowIndex = ctr.rowIndex;
                } else {
                    for (i = 0; i < rows.length; i++) {
                        if (rows[i] === ctr) {
                            rowIndex = i;
                            break;
                        }
                    }
                }

                for (j = 0; j < tm[rowIndex].length; j++) {
                    if (tm[rowIndex][j] === ctd.cellIndex) {
                        realIndex = j;
                        break;
                    }
                }

                if (ctd.rowSpan > 1) {
                    i = realIndex;
                    while (tm[rowIndex + 1][i] === -1) {
                        i++;
                    }

                    ni = (i === tm[rowIndex + 1].length) ? rows[rowIndex + 1].cells.length : tm[rowIndex + 1][i];

                    newc = rows[rowIndex + 1].insertCell(ni);
                    ctd.rowSpan--;

                    nc = ctd.cloneNode(false);
                    nc.innerHTML = '&nbsp;';
                    rows[rowIndex + 1].replaceChild(nc, newc);
                    ctd.rowSpan = 1;
                } else {
                    if (ctb.rows && ctb.rows.length > 0) {
                        ctb.insertRow(rowIndex + 1);
                    } else {
                        if (rowIndex < (rows.length - 1)) {
                            ctb.insertBefore(document.createElement('TR'), rows[rowIndex + 1]);
                        } else {
                            ctb.appendChild(document.createElement('TR'));
                        }
                    }
                    for (i = 0; i < ctr.cells.length; i++) {
                        if (i !== ctd.cellIndex) {
                            rs = ctr.cells[i].rowSpan > 1 ? ctr.cells[i].rowSpan : 1;
                            ctr.cells[i].rowSpan = rs + 1;
                        }
                    }

                    for (i = 0; i < rowIndex; i++) {
                        tempr = rows[i];
                        for (j = 0; j < tempr.cells.length; j++) {
                            if (tempr.cells[j].rowSpan > (rowIndex - i)) {
                                tempr.cells[j].rowSpan++;
                            }
                        }
                    }

                    newc = rows[rowIndex + 1].insertCell(0);
                    nc = ctd.cloneNode(false);
                    nc.innerHTML = '&nbsp;';
                    rows[rowIndex + 1].replaceChild(nc, newc);
                }
            },
            tblReflash = function () {
                self.editAreaFocus(); self.doEditorEvent();
            },
            colorPickerEventHandler = function () {
                GB.popupWindow.ColorPicker.argv = {
                    func: function (color) {
                        ctd.setAttribute('bgColor', color);
                        document.getElementById('fm_cell_bgcolor').value = color;
                    },
                    selectedCell : ctd
                };
                self.windowOpen('ColorPicker');
            },
            editSubmitEventHandler = function () {
                var width = self.trimSpace(document.getElementById('fm_cell_width').value),
                    height = self.trimSpace(document.getElementById('fm_cell_height').value),
                    bgcolor = self.trimSpace(document.getElementById('fm_cell_bgcolor').value);
                if (width) {
                    ctd.setAttribute('width', width);
                }
                if (height) {
                    ctd.setAttribute('height', height);
                }
                if (bgcolor) {
                    ctd.setAttribute('bgcolor', bgcolor);
                }
            },
            deleteSubmitEventHandler = function () {
                ctb.parentNode.removeChild(ctb);
                self.doEditorEvent();
            },
            funcs = {
                add_cols_after: { icon: 'table_insert_column.png', title: '열 삽입',
                    func: function () {
                        insertColumn(ctd.cellIndex); tblReflash();
                    }},
                add_rows_after: { icon: 'table_insert_row.png', title: '행 삽입',
                    func: function () {
                        insertRow(ctr.rowIndex); tblReflash();
                    }},
                remove_cols: { icon: 'table_delete_column.png', title: '열 삭제',
                    func: function () {
                        deleteColumn(ctd.cellIndex); tblReflash();
                    }},
                remove_rows: { icon: 'table_delete_row.png', title: '행 삭제',
                    func: function () {
                        deleteRow(); tblReflash();
                    }},
                sp1: { icon: 'dot.gif' },
                merge_cell_right: { icon: 'table_join_row.png', title: '오른쪽 셀과 병합',
                    func: function () {
                        mergeCellRight(); tblReflash();
                    }},
                merge_cell_down: { icon: 'table_join_column.png', title: '아래 셀과 병합',
                    func: function () {
                        mergeCellDown(); tblReflash();
                    }},
                split_cell_v: { icon: 'table_split_row.png', title: '셀 열로 나누기',
                    func: function () {
                        splitCellVertical(); tblReflash();
                    }},
                split_cell_h: { icon: 'table_split_column.png', title: '셀 행으로 나누기',
                    func: function () {
                        splitCellHorizontal(); tblReflash();
                    }}
            },
            attrFuncs = {
                setWidth: {
                    txt: '가로폭',
                    id: 'fm_cell_width',
                    marginRight: '10px',
                    value: ctd.getAttribute('width')
                },
                setHeight: {
                    txt: '세로폭',
                    id: 'fm_cell_height',
                    marginRight: '10px',
                    value: ctd.getAttribute('height')
                },
                setBgcolor: {
                    txt: '배경색',
                    id: 'fm_cell_bgcolor',
                    marginRight: '2px',
                    value: ctd.getAttribute('bgcolor')
                }
            },
            deleteSubmit = new Image(),
            spliter = document.createElement('div'), txt, input,
            colorPicker = new Image(),
            editSubmit = new Image();


        while (ctb && ctb.tagName.toLowerCase() !== 'table') {
            ctb = ctb.parentNode;
        }
        while (ctr && ctr.tagName.toLowerCase() !== 'tr') {
            ctr = ctr.parentNode;
        }

        self.cheditor.editBlock.innerHTML = '';
        div.style.padding = '6px';

        for (i in funcs) {
            if (!funcs.hasOwnProperty(i)) {
                continue;
            }
            span = document.createElement('span');
            icon = document.createElement('img');
            icon.src = self.config.iconPath + funcs[i].icon;

            if (i === 'sp1' || i === 'sp2') {
                icon.className = 'edit-table-ico';
            } else {
                icon.setAttribute('title', funcs[i].title);
                icon.className = 'edit-table-ico';
                icon.setAttribute('alt', '');
                icon.onclick = funcs[i].func;
            }
            div.appendChild(span.appendChild(icon));
        }

        deleteSubmit.src = this.config.iconPath + 'delete_table.png';
        deleteSubmit.style.marginLeft = '22px';
        deleteSubmit.className = 'edit-table-ico';
        deleteSubmit.setAttribute('title', '테이블 삭제');
        deleteSubmit.onclick = deleteSubmitEventHandler;
        div.appendChild(deleteSubmit);

        spliter.style.padding = '10px 0px 0px 0px';
        spliter.style.marginTop = '5px';
        spliter.style.borderTop = '1px solid #ccc';
        spliter.style.textAlign = 'center';

        for (i in attrFuncs) {
            if (!attrFuncs.hasOwnProperty(i)) {
                continue;
            }
            txt = document.createTextNode(attrFuncs[i].txt + ' ');
            spliter.appendChild(txt);
            input = document.createElement('input');
            input.style.marginRight = attrFuncs[i].marginRight;
            input.setAttribute('type', 'text');
            input.setAttribute('name', i);
            input.setAttribute('id', attrFuncs[i].id);
            input.setAttribute('size', 7);
            input.setAttribute('value', attrFuncs[i].value || '');
            spliter.appendChild(input);
        }

        colorPicker.src = this.config.iconPath + 'button/color_picker.gif';
        colorPicker.className = 'color-picker';
        colorPicker.onclick = colorPickerEventHandler;
        spliter.appendChild(colorPicker);

        editSubmit.src = this.config.iconPath + 'button/edit_cell.gif';
        editSubmit.className = 'input-submit';
        editSubmit.style.verticalAlign = 'top';
        editSubmit.onclick = editSubmitEventHandler;

        spliter.appendChild(editSubmit);
        div.appendChild(spliter);
        self.cheditor.editBlock.appendChild(div);
    },

    doEditorEvent : function (evt) {
        var self = this,
            cmd = null, ancestors = [], node, el, pNode, range, sType, links, span, tag, remove, bText,
            srcElement = evt.target || evt.srcElement,
            block = self.cheditor.editBlock,
            status = self.cheditor.tagPath,
            linkOnClickEventHandler = function () {
                if (bText) {
                    document.getElementById('removeSelected').style.display = 'inline';
                    self.tagSelector(this.el);
                }
            },
            removeOnClickEventHandler = function () {
                self.doc.execCommand('RemoveFormat', false, null);
                remove.style.display = 'none';
                self.editAreaFocus();
                self.doEditorEvent();
            };

        if (!this.undefined(srcElement) && srcElement.nodeType === GB.node.element) {
            pNode = srcElement;
        } else {
            range = self.getRange();
            sType = self.getSelectionType();
            bText = sType === GB.selection.text;

            if (!self.W3CRange) {
                switch (sType) {
                    case GB.selection.none :
                    case GB.selection.text :
                        pNode = range.parentElement();
                        break;
                    case GB.selection.element :
                        pNode = range.item(0);
                        break;
                    default :
                        pNode = self.editArea.document.body;
                }
            } else {
                pNode = range.commonAncestorContainer;
                if (!range.collapsed &&
                    range.startContainer === range.endContainer &&
                    range.startOffset - range.endOffset < 2 &&
                    range.startContainer.hasChildNodes())
                {
                    pNode = range.startContainer.childNodes[range.startOffset];
                }
                while (pNode.nodeType === GB.node.text) {
                    pNode = pNode.parentNode;
                }
            }
        }

        node = pNode;
        while (pNode && pNode.nodeType === GB.node.element) {
            if (pNode.tagName.toLowerCase() === 'body') {
                break;
            }
            if (pNode.tagName.toLowerCase() === 'img') {
                cmd = 'img'; break;
            }
            if (pNode.tagName.toLowerCase() === 'td' || pNode.tagName.toLowerCase() === 'th') {
                cmd = 'cell'; break;
            }
            pNode = pNode.parentNode;
        }

        if (!cmd) {
            block.style.display = 'none';
            block.innerHTML = '';
        } else {
            if (cmd === 'cell') {
                block.style.display = 'block';
                self.modifyCell(pNode);
            }
        }

        if (self.config.showTagPath) {
            while (node && node.nodeType === GB.node.element) {
                ancestors.push(node);
                if (node.tagName.toLowerCase() === 'body') {
                    break;
                }
                node = node.parentNode;
            }

            status.innerHTML = '';
            status.appendChild(document.createTextNode('<html> '));
            el = ancestors.pop();

            while (el) {
                status.appendChild(document.createTextNode('<'));
                tag = el.nodeName.toLowerCase();

                links = document.createElement('a');
                links.el = el;
                links.href = 'javascript:void%200';
                links.className = 'cheditor-tag-path-elem';
                links.title = el.style.cssText;
                (function () {
                    links.onclick = linkOnClickEventHandler;
                })();
                links.appendChild(document.createTextNode(tag));
                status.appendChild(links);
                status.appendChild(document.createTextNode('> '));
                el = ancestors.pop();
            }

            if (bText) {
                remove = document.createElement('a');
                remove.href = 'javascript:void%200';
                remove.id = 'removeSelected';
                remove.style.display = 'none';
                remove.className = 'cheditor-tag-path-elem';
                remove.style.color = '#cc3300';
                remove.appendChild(document.createTextNode('<remove format>'));
                (function () {
                    remove.onclick = removeOnClickEventHandler;
                })();
                span = document.createElement('span');
                span.style.marginTop = '2px';
                span.appendChild(remove);
                self.cheditor.tagPath.appendChild(span);
            }
        }

        self.toolbarUpdate(srcElement);
    },

    tagSelector : function (node) {
        var rng, selection;
        this.editAreaFocus();

        if (this.W3CRange) {
            selection = this.editArea.getSelection();
            if (this.undefined(selection)) {
                return;
            }
            try {
                rng = selection.getRangeAt(0);
            } catch (e) {
                return;
            }
            rng.selectNodeContents(node);
            selection.removeAllRanges();
            selection.addRange(rng);
        } else {
            rng = this.doc.body.createTextRange();
            if (rng) {
                rng.moveToElementText(node);
                rng.select();
            }
        }
    },

    getBrowser : function () {
        return GB.browser;
    },
    $ : function (id) {
        return this.doc.getElementById(id);
    }
};

(function () {
    var dragWindow = {
        obj: null,
        init: function (o, oRoot, minX, maxX, minY, maxY) {
            o.style.curser = 'default';
            o.onmousedown = dragWindow.start;
            o.onmouseover = function () {
                this.style.cursor = 'move';
            };
            o.hmode = true;
            o.vmode = true;
            o.root = (oRoot && oRoot !== null) ? oRoot : o;
            o.transId = oRoot.id + '_Trans';

            if (o.hmode  && isNaN(parseInt(o.root.style.left, 10))) {
                o.root.style.left = '0px';
            }
            if (o.vmode  && isNaN(parseInt(o.root.style.top, 10))) {
                o.root.style.top = '0px';
            }
            if (!o.hmode && isNaN(parseInt(o.root.style.right, 10))) {
                o.root.style.right = '0px';
            }
            if (!o.vmode && isNaN(parseInt(o.root.style.bottom, 10))) {
                o.root.style.bottom = '0px';
            }

            o.minX = minX !== undefined ? minX : null;
            o.minY = minY !== undefined ? minY : null;
            o.maxX = maxX !== undefined ? maxX : null;
            o.maxY = maxY !== undefined ? maxY : null;
            o.root.onDragStart = new Function();
            o.root.onDragEnd = new Function();
            o.root.onDrag = new Function();
        },
        start: function (e) {
            var o = dragWindow.obj = this,
                dragTransBg = document.createElement('div'),
                y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom, 10),
                x = parseInt(o.hmode ? o.root.style.left : o.root.style.right, 10);

            e = dragWindow.fixEv(e);
            o.root.onDragStart(x, y);
            o.lastMouseX = e.clientX;
            o.lastMouseY = e.clientY;

            document.onmousemove = dragWindow.drag;
            document.onmouseup = dragWindow.end;

            if (o.root.lastChild.id === o.transId) {
                return false;
            }

            dragTransBg.className = 'cheditor-dragWindowTransparent';
            if (GB.browser.msie && GB.browser.version < 10) {
                dragTransBg.style.filter = 'alpha(opacity=0)';
            } else {
                dragTransBg.style.opacity = 0;
            }
            dragTransBg.id = o.transId;
            dragTransBg.style.width = o.root.lastChild.firstChild.style.width;
            dragTransBg.style.height = o.root.lastChild.firstChild.style.height;
            o.root.appendChild(dragTransBg);
            return false;
        },
        drag: function (e) {
            e = dragWindow.fixEv(e);
            var o = dragWindow.obj,
                ey = e.clientY,
                ex = e.clientX,
                y = parseInt(o.vmode ? o.root.style.top : o.root.style.bottom, 10),
                x = parseInt(o.hmode ? o.root.style.left : o.root.style.right, 10),
                nx, ny;

            nx = x + ((ex - o.lastMouseX) * (o.hmode ? 1 : -1));
            ny = y + ((ey - o.lastMouseY) * (o.vmode ? 1 : -1));

            dragWindow.obj.root.style.left = nx + 'px';
            dragWindow.obj.root.style.top = ny + 'px';
            dragWindow.obj.lastMouseX = ex;
            dragWindow.obj.lastMouseY = ey;
            dragWindow.obj.root.onDrag(nx, ny);
            return false;
        },
        end: function () {
            document.onmousemove = null;
            document.onmouseup = null;
            dragWindow.obj.root.onDragEnd(parseInt(dragWindow.obj.root.style[dragWindow.obj.hmode ? 'left' : 'right'], 10),
                    parseInt(dragWindow.obj.root.style[dragWindow.obj.vmode ? 'top' : 'bottom'], 10));

            if (dragWindow.obj.root.lastChild.id === dragWindow.obj.transId) {
                dragWindow.obj.root.removeChild(dragWindow.obj.root.lastChild);
            }
            dragWindow.obj = null;
        },
        fixEv: function (e) {
            if (e === undefined) {
                e = window.event;
            }
            if (e.layerX === undefined) {
                e.layerX = e.offsetX;
            }
            if (e.layerY === undefined) {
                e.layerY = e.offsetY;
            }
            return e;
        }
    };
    GB.dragWindow = dragWindow;
})();

// --------------------------------------------------------------------------
// W3C DOM Range
//

// --------------------------------------------------------------------------
// Table
//

// --------------------------------------------------------------------------
// Color Picker
//
(function () {
    var colorDropper = {
        images: {pad: [181, 101], sld: [16, 101], cross: [15, 15], arrow: [7, 11]},
        fetchElement: function (mixed) {
            return typeof mixed === 'string' ? document.getElementById(mixed) : mixed;
        },

        addEvent: function (el, evnt, func) {
            if (el.addEventListener) {
                el.addEventListener(evnt, func, false);
            } else if (el.attachEvent) {
                el.attachEvent('on' + evnt, func);
            }
        },

        fireEvent: function (el, evnt) {
            if (!el) {
                return;
            }
            var ev;
            if (document.createEvent) {
                ev = document.createEvent('HTMLEvents');
                ev.initEvent(evnt, true, true);
                el.dispatchEvent(ev);
            } else if (document.createEventObject) {
                ev = document.createEventObject();
                el.fireEvent('on' + evnt, ev);
            } else if (el['on' + evnt]) {
                el['on' + evnt]();
            }
        },

        getElementPos: function (e) {
            var e1 = e, e2 = e, x = 0, y = 0;
            if (e1.offsetParent) {
                do {
                    x += e1.offsetLeft;
                    y += e1.offsetTop;
                    e1 = e1.offsetParent;
                } while (e1);
            }

            while (e2 && e2.nodeName.toLowerCase() !== 'body') {
                x -= e2.scrollLeft;
                y -= e2.scrollTop;
                e2 = e2.parentNode;
            }
            return [x, y];
        },

        getElementSize: function (e) {
            return [e.offsetWidth, e.offsetHeight];
        },

        getRelMousePos: function (e) {
            var x = 0, y = 0;
            if (!e) {
                e = window.event;
            }
            if (typeof e.offsetX === 'number') {
                x = e.offsetX;
                y = e.offsetY;
            } else if (typeof e.layerX === 'number') {
                x = e.layerX;
                y = e.layerY;
            }
            return {x: x, y: y};
        },

        color: function (target, prop) {
            this.required = true;
            this.adjust = true;
            this.hash = true;
            this.caps = false;
            this.valueElement = target;
            this.styleElement = target;
            this.onImmediateChange = null;
            this.hsv = [0, 0, 1];
            this.rgb = [1, 1, 1];
            this.minH = 0;
            this.maxH = 6;
            this.minS = 0;
            this.maxS = 1;
            this.minV = 0;
            this.maxV = 1;

            this.pickerOnfocus = true;
            this.pickerMode = 'HSV';
            this.pickerFace = 3;
            this.pickerFaceColor = '#fff';
            this.pickerInset = 1;
            this.pickerInsetColor = '#999';
            this.pickerZIndex = 10003;

            var p,
                self = this,
                modeID = this.pickerMode.toLowerCase() === 'hvs' ? 1 : 0,
                abortBlur = false,
                valueElement = colorDropper.fetchElement(this.valueElement), styleElement = colorDropper.fetchElement(this.styleElement),
                holdPad = false, holdSld = false, touchOffset = {},
                leaveValue = 1 << 0, leaveStyle = 1 << 1, leavePad = 1 << 2, leaveSld = 1 << 3,
                updateFieldEventHandler = function () {
                    self.fromString(valueElement.value, leaveValue);
                    dispatchImmediateChange();
                };

            colorDropper.addEvent(target, 'blur', function () {
                if (!abortBlur) {
                    window.setTimeout(function () {
                        abortBlur || blurTarget();
                        abortBlur = false;
                    }, 0);
                } else {
                    abortBlur = false;
                }
            });

            for (p in prop) {
                if (prop.hasOwnProperty(p)) {
                    this[p] = prop[p];
                }
            }

            this.hidePicker = function () {
                if (isPickerOwner()) {
                    removePicker();
                }
            };

            this.showPicker = function () {
                if (!isPickerOwner()) {
                    drawPicker();
                }
            };

            this.importColor = function () {
                if (!valueElement) {
                    this.exportColor();
                } else {
                    if (!this.adjust) {
                        if (!this.fromString(valueElement.value, leaveValue)) {
                            styleElement.style.backgroundImage = styleElement.jscStyle.backgroundImage;
                            styleElement.style.backgroundColor = styleElement.jscStyle.backgroundColor;
                            styleElement.style.color = styleElement.jscStyle.color;
                            this.exportColor(leaveValue | leaveStyle);
                        }
                    } else if (!this.required && /^\s*$/.test(valueElement.value)) {
                        valueElement.value = '';
                        styleElement.style.backgroundImage = styleElement.jscStyle.backgroundImage;
                        styleElement.style.backgroundColor = styleElement.jscStyle.backgroundColor;
                        styleElement.style.color = styleElement.jscStyle.color;
                        this.exportColor(leaveValue | leaveStyle);
                    } else if (this.fromString(valueElement.value)) {
                        // ignore
                    } else {
                        this.exportColor();
                    }
                }
            };

            this.exportColor = function (flags) {
                if (!(flags & leaveValue) && valueElement) {
                    var value = this.toString();
                    if (this.caps) {
                        value = value.toUpperCase();
                    }
                    if (this.hash) {
                        value = '#' + value;
                    }
                    valueElement.value = value;
                }
                if (!(flags & leaveStyle) && styleElement) {
                    styleElement.style.backgroundImage = 'none';
                    styleElement.style.backgroundColor = '#' + this.toString();
                    styleElement.style.color = 0.213 * this.rgb[0] + 0.715 * this.rgb[1] + 0.072 * this.rgb[2] < 0.5 ? '#FFF' : '#000';
                }
                if (!(flags & leavePad) && isPickerOwner()) {
                    redrawPad();
                }
                if (!(flags & leaveSld) && isPickerOwner()) {
                    redrawSld();
                }
            };

            this.fromHSV = function (h, s, v, flags) {
                if (h) {
                    h = Math.max(0.0, this.minH, Math.min(6.0, this.maxH, h));
                }
                if (s) {
                    s = Math.max(0.0, this.minS, Math.min(1.0, this.maxS, s));
                }
                if (v) {
                    v = Math.max(0.0, this.minV, Math.min(1.0, this.maxV, v));
                }

                this.rgb = this.HSV_RGB(
                    h === null ? this.hsv[0] : (this.hsv[0] = h),
                    s === null ? this.hsv[1] : (this.hsv[1] = s),
                    v === null ? this.hsv[2] : (this.hsv[2] = v)
                );
                this.exportColor(flags);
            };

            this.fromRGB = function (r, g, b, flags) {
                if (r) {
                    r = Math.max(0.0, Math.min(1.0, r));
                }
                if (g) {
                    g = Math.max(0.0, Math.min(1.0, g));
                }
                if (b) {
                    b = Math.max(0.0, Math.min(1.0, b));
                }

                var hsv = this.RGB_HSV(
                    r === null ? this.rgb[0] : r,
                    g === null ? this.rgb[1] : g,
                    b === null ? this.rgb[2] : b
                );
                if (hsv[0] !== null) {
                    this.hsv[0] = Math.max(0.0, this.minH, Math.min(6.0, this.maxH, hsv[0]));
                }
                if (hsv[2] !== 0) {
                    this.hsv[1] = hsv[1] === null ? null : Math.max(0.0, this.minS, Math.min(1.0, this.maxS, hsv[1]));
                }
                this.hsv[2] = hsv[2] === null ? null : Math.max(0.0, this.minV, Math.min(1.0, this.maxV, hsv[2]));

                var rgb = this.HSV_RGB(this.hsv[0], this.hsv[1], this.hsv[2]);
                this.rgb[0] = rgb[0];
                this.rgb[1] = rgb[1];
                this.rgb[2] = rgb[2];

                this.exportColor(flags);
            };

            this.fromString = function (hex, flags) {
                var m = hex.match(/^\W*([0-9A-F]{3}([0-9A-F]{3})?)\W*$/i);
                if (!m) {
                    return false;
                }
                if (m[1].length === 6) {
                    this.fromRGB(
                        parseInt(m[1].substr(0, 2), 16) / 255,
                        parseInt(m[1].substr(2, 2), 16) / 255,
                        parseInt(m[1].substr(4, 2), 16) / 255,
                        flags
                    );
                } else {
                    this.fromRGB(
                        parseInt(m[1].charAt(0) + m[1].charAt(0), 16) / 255,
                        parseInt(m[1].charAt(1) + m[1].charAt(1), 16) / 255,
                        parseInt(m[1].charAt(2) + m[1].charAt(2), 16) / 255,
                        flags
                    );
                }
                return true;
            };

            this.toString = function () {
                return (
                    (0x100 | Math.round(255 * this.rgb[0])).toString(16).substr(1) +
                    (0x100 | Math.round(255 * this.rgb[1])).toString(16).substr(1) +
                    (0x100 | Math.round(255 * this.rgb[2])).toString(16).substr(1)
                );
            };

            this.RGB_HSV = function (r, g, b) {
                var n = Math.min(Math.min(r, g), b),
                    v = Math.max(Math.max(r, g), b),
                    m = v - n, h;

                if (m === 0) {
                    return [null, 0, v];
                }
                h = r === n ? 3 + (b - g) / m : (g === n ? 5 + (r - b) / m : 1 + (g - r) / m);
                return [h === 6 ? 0 : h, m / v, v];
            };

            this.HSV_RGB = function (h, s, v) {
                if (h === null) {
                    return [v, v, v];
                }
                var i = Math.floor(h),
                    f = i % 2 ? h - i : 1 - (h - i),
                    m = v * (1 - s),
                    n = v * (1 - s * f);
                switch (i) {
                    case 6:
                    case 0:
                        return [v, n, m];
                    case 1:
                        return [n, v, m];
                    case 2:
                        return [m, v, n];
                    case 3:
                        return [m, n, v];
                    case 4:
                        return [n, m, v];
                    case 5:
                        return [v, m, n];
                }
            };

            function removePicker() {
                delete colorDropper.picker.owner;
                colorDropper.picker.boxB.parentNode.removeChild(colorDropper.picker.boxB);
            }

            function drawPicker() {
                var touchMoveEventHandler = function (e) {
                        var event = {
                            'offsetX': e.touches[0].pageX - touchOffset.X,
                            'offsetY': e.touches[0].pageY - touchOffset.Y
                        };
                        if (holdPad || holdSld) {
                            holdPad && setPad(event);
                            holdSld && setSld(event);
                            dispatchImmediateChange();
                        }
                        e.stopPropagation();
                        e.preventDefault();
                    },
                    dims = getPickerDims(self),
                    padImg = modeID ? 'color_picker_hv.png' : 'color_picker_hs.png',
                    i, seg, segSize;

                if (!colorDropper.picker) {
                    colorDropper.picker = {
                        box: document.createElement('div'),
                        boxB: document.createElement('div'),
                        pad: document.createElement('div'),
                        padB: document.createElement('div'),
                        padM: document.createElement('div'),
                        sld: document.createElement('div'),
                        sldB: document.createElement('div'),
                        sldM: document.createElement('div')
                    };
                    for (i = 0, segSize = 2; i < colorDropper.images.sld[1]; i += segSize) {
                        seg = document.createElement('div');
                        seg.style.height = segSize + 'px';
                        seg.style.fontSize = '1px';
                        seg.style.lineHeight = '0px';
                        colorDropper.picker.sld.appendChild(seg);
                    }
                    colorDropper.picker.sldB.appendChild(colorDropper.picker.sld);
                    colorDropper.picker.box.appendChild(colorDropper.picker.sldB);
                    colorDropper.picker.box.appendChild(colorDropper.picker.sldM);
                    colorDropper.picker.padB.appendChild(colorDropper.picker.pad);
                    colorDropper.picker.box.appendChild(colorDropper.picker.padB);
                    colorDropper.picker.box.appendChild(colorDropper.picker.padM);
                    colorDropper.picker.boxB.appendChild(colorDropper.picker.box);
                }

                p = colorDropper.picker;
                p.box.onmouseup = p.box.onmouseout = function () {
                    target.focus();
                };
                p.box.onmousedown = function () {
                    abortBlur = true;
                };
                p.box.onmousemove = function (e) {
                    if (holdPad || holdSld) {
                        holdPad && setPad(e);
                        holdSld && setSld(e);
                        if (document.selection) {
                            document.selection.empty();
                        } else if (window.getSelection) {
                            window.getSelection().removeAllRanges();
                        }
                        dispatchImmediateChange();
                    }
                };

                if ('ontouchstart' in window) {
                    p.box.removeEventListener('touchmove', touchMoveEventHandler, false);
                    p.box.addEventListener('touchmove', touchMoveEventHandler, false);
                }
                p.padM.onmouseup = p.padM.onmouseout = function () {
                    if (holdPad) {
                        holdPad = false;
                        colorDropper.fireEvent(valueElement, 'change');
                    }
                };
                p.padM.onmousedown = function (e) {
                    switch (modeID) {
                        case 0:
                            if (self.hsv[2] === 0) {
                                self.fromHSV(null, null, 1.0);
                            }
                            break;
                        case 1:
                            if (self.hsv[1] === 0) {
                                self.fromHSV(null, 1.0, null);
                            }
                            break;
                    }
                    holdSld = false;
                    holdPad = true;
                    setPad(e);
                    dispatchImmediateChange();
                };

                if ('ontouchstart' in window) {
                    p.padM.addEventListener('touchstart', function (e) {
                        touchOffset = {'X': getOffsetParent(e.target).Left, 'Y': getOffsetParent(e.target).Top};
                        this.onmousedown({
                            'offsetX': e.touches[0].pageX - touchOffset.X,
                            'offsetY': e.touches[0].pageY - touchOffset.Y
                        });
                    });
                }
                p.sldM.onmouseup = p.sldM.onmouseout = function () {
                    if (holdSld) {
                        holdSld = false;
                        colorDropper.fireEvent(valueElement, 'change');
                    }
                };
                p.sldM.onmousedown = function (e) {
                    holdPad = false;
                    holdSld = true;
                    setSld(e);
                    dispatchImmediateChange();
                };
                if ('ontouchstart' in window) {
                    p.sldM.addEventListener('touchstart', function (e) {
                        touchOffset = {'X': getOffsetParent(e.target).Left, 'Y': getOffsetParent(e.target).Top};
                        this.onmousedown({
                            'offsetX': e.touches[0].pageX - touchOffset.X,
                            'offsetY': e.touches[0].pageY - touchOffset.Y
                        });
                    });
                }

                p.box.style.width = dims[0] + 'px';
                p.box.style.height = dims[1] + 'px';

                p.boxB.style.position = 'relative';
                p.boxB.style.clear = 'both';
                p.boxB.style.border = 'none';
                p.boxB.style.background = self.pickerFaceColor;

                p.pad.style.width = colorDropper.images.pad[0] + 'px';
                p.pad.style.height = colorDropper.images.pad[1] + 'px';

                p.padB.style.position = 'absolute';
                p.padB.style.left = self.pickerFace + 'px';
                p.padB.style.top = self.pickerFace + 'px';
                p.padB.style.border = self.pickerInset + 'px solid';
                p.padB.style.borderColor = self.pickerInsetColor;

                p.padM.style.position = 'absolute';
                p.padM.style.left = '0';
                p.padM.style.top = '0';
                p.padM.style.width = self.pickerFace + 2 * self.pickerInset + colorDropper.images.pad[0] + colorDropper.images.arrow[0] + 'px';
                p.padM.style.height = p.box.style.height;
                p.padM.style.cursor = 'crosshair';

                p.sld.style.overflow = 'hidden';
                p.sld.style.width = '13px';
                p.sld.style.height = colorDropper.images.sld[1] + 'px';

                p.sldB.style.position = 'absolute';
                p.sldB.style.right = self.pickerFace + 'px';
                p.sldB.style.top = self.pickerFace + 'px';
                p.sldB.style.border = self.pickerInset + 'px solid';
                p.sldB.style.borderColor = self.pickerInsetColor;

                p.sldM.style.position = 'absolute';
                p.sldM.style.right = '0';
                p.sldM.style.top = '0';
                p.sldM.style.width = 14 + colorDropper.images.arrow[0] + self.pickerFace + 2 * self.pickerInset + 'px';
                p.sldM.style.height = p.box.style.height;

                try {
                    p.sldM.style.cursor = 'pointer';
                } catch (e) {
                    p.sldM.style.cursor = 'hand';
                }

                p.padM.style.backgroundImage = 'url("' + self.iconDir + '/color_picker_cross.gif")';
                p.padM.style.backgroundRepeat = 'no-repeat';
                p.sldM.style.backgroundImage = 'url("' + self.iconDir + '/color_picker_arrow.gif")';
                p.sldM.style.backgroundRepeat = 'no-repeat';
                p.pad.style.backgroundImage = 'url("' + self.iconDir + '/' + padImg + '")';
                p.pad.style.backgroundRepeat = 'no-repeat';
                p.pad.style.backgroundPosition = '0 0';
                redrawPad();
                redrawSld();
                colorDropper.picker.owner = self;
                target.parentNode.parentNode.appendChild(p.boxB);
            }

            function getPickerDims(o) {
                return [2 * o.pickerInset + 2 * o.pickerFace + colorDropper.images.pad[0] +
                2 * o.pickerInset + 2 * colorDropper.images.arrow[0] + colorDropper.images.sld[0],
                    2 * o.pickerInset + 2 * o.pickerFace + colorDropper.images.pad[1]];
            }

            function redrawPad() {
                var yComponent, x, y, i = 0, rgb, s, c, f,
                    seg = colorDropper.picker.sld.childNodes;

                switch (modeID) {
                    case 0:
                        yComponent = 1;
                        break;
                    case 1:
                        yComponent = 2;
                        break;
                }
                x = Math.round((self.hsv[0] / 6) * (colorDropper.images.pad[0] - 1));
                y = Math.round((1 - self.hsv[yComponent]) * (colorDropper.images.pad[1] - 1));
                colorDropper.picker.padM.style.backgroundPosition =
                    (self.pickerFace + self.pickerInset + x - Math.floor(colorDropper.images.cross[0] / 2)) + 'px ' +
                    (self.pickerFace + self.pickerInset + y - Math.floor(colorDropper.images.cross[1] / 2)) + 'px';

                switch (modeID) {
                    case 0:
                        rgb = self.HSV_RGB(self.hsv[0], self.hsv[1], 1);
                        if (window.File && window.FileReader) {
                            colorDropper.picker.sld.style.background = 'linear-gradient(rgb(' +
                                (rgb[0] * (1 - i / seg.length) * 100) + '%,' +
                                (rgb[1] * (1 - i / seg.length) * 100) + '%,' +
                                (rgb[2] * (1 - i / seg.length) * 100) + '%), black)';
                        } else {
                            for (i = 0; i < seg.length; i += 1) {
                                seg[i].style.backgroundColor = 'rgb(' +
                                    (rgb[0] * (1 - i / seg.length) * 100) + '%,' +
                                    (rgb[1] * (1 - i / seg.length) * 100) + '%,' +
                                    (rgb[2] * (1 - i / seg.length) * 100) + '%)';
                            }
                        }
                        break;
                    case 1:
                        c = [self.hsv[2], 0, 0];
                        i = Math.floor(self.hsv[0]);
                        f = i % 2 ? self.hsv[0] - i : 1 - (self.hsv[0] - i);
                        switch (i) {
                            case 6:
                            case 0:
                                rgb = [0, 1, 2];
                                break;
                            case 1:
                                rgb = [1, 0, 2];
                                break;
                            case 2:
                                rgb = [2, 0, 1];
                                break;
                            case 3:
                                rgb = [2, 1, 0];
                                break;
                            case 4:
                                rgb = [1, 2, 0];
                                break;
                            case 5:
                                rgb = [0, 2, 1];
                                break;
                        }

                        for (i = 0; i < seg.length; i += 1) {
                            s = 1 - 1 / (seg.length - 1) * i;
                            c[1] = c[0] * (1 - s * f);
                            c[2] = c[0] * (1 - s);
                            seg[i].style.backgroundColor = 'rgb(' +
                                (c[rgb[0]] * 100) + '%,' +
                                (c[rgb[1]] * 100) + '%,' +
                                (c[rgb[2]] * 100) + '%)';
                        }
                        break;
                }
            }

            function getOffsetParent(el) {
                var parent = el.offsetParent, top = 0, left = 0;
                while (parent) {
                    top += parent.offsetTop;
                    left += parent.offsetLeft;
                    parent = parent.offsetParent;
                }
                return {Left: left, Top: top};
            }

            function redrawSld() {
                var yComponent, y;
                switch (modeID) {
                    case 0:
                        yComponent = 2;
                        break;
                    case 1:
                        yComponent = 1;
                        break;
                }
                y = Math.round((1 - self.hsv[yComponent]) * (colorDropper.images.sld[1] - 1));
                colorDropper.picker.sldM.style.backgroundPosition =
                    '0 ' + (self.pickerFace + self.pickerInset + y - Math.floor(colorDropper.images.arrow[1] / 2)) + 'px';
            }

            function isPickerOwner() {
                return colorDropper.picker && colorDropper.picker.owner === self;
            }

            function blurTarget() {
                if (valueElement === target) {
                    self.importColor();
                }
            }

            function blurValue() {
                if (valueElement !== target) {
                    self.importColor();
                }
            }

            function setPad(e) {
                var mpos = colorDropper.getRelMousePos(e),
                    x = mpos.x - self.pickerFace - self.pickerInset,
                    y = mpos.y - self.pickerFace - self.pickerInset;
                switch (modeID) {
                    case 0:
                        self.fromHSV(x * (6 / (colorDropper.images.pad[0] - 1)), 1 - y / (colorDropper.images.pad[1] - 1), null, leaveSld);
                        break;
                    case 1:
                        self.fromHSV(x * (6 / (colorDropper.images.pad[0] - 1)), null, 1 - y / (colorDropper.images.pad[1] - 1), leaveSld);
                        break;
                }
            }

            function setSld(e) {
                var mpos = colorDropper.getRelMousePos(e),
                    y = mpos.y - self.pickerFace - self.pickerInset;
                switch (modeID) {
                    case 0:
                        self.fromHSV(null, null, 1 - y / (colorDropper.images.sld[1] - 1), leavePad);
                        break;
                    case 1:
                        self.fromHSV(null, 1 - y / (colorDropper.images.sld[1] - 1), null, leavePad);
                        break;
                }
            }

            function dispatchImmediateChange() {
                if (self.onImmediateChange) {
                    var callback;
                    if (typeof self.onImmediateChange === 'string') {
                        callback = new Function(self.onImmediateChange);
                    } else {
                        callback = self.onImmediateChange;
                    }
                    callback.call(self);
                }
            }

            if (valueElement) {
                colorDropper.addEvent(valueElement, 'keyup', updateFieldEventHandler);
                colorDropper.addEvent(valueElement, 'input', updateFieldEventHandler);
                colorDropper.addEvent(valueElement, 'blur', blurValue);
                valueElement.setAttribute('autocomplete', 'off');
            }

            this.importColor();
        }
    };
    GB.colorDropper = colorDropper.color;
})();
