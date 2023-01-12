// ================================================================
//                              Movie
// ================================================================
var button = [
    { alt : '', img : 'play.gif', cmd : doPlay },
    { alt : '', img : 'submit.gif', cmd : doSubmit },
    { alt : '', img : 'cancel.gif', cmd : popupClose }
],
    oEditor = null,
    iframeSource = null,
    showMovie = false,
    defaultMovieWidth = 640,
    defaultMovieHeight = 360;

function init(dialog) {
    var dlg = new Dialog(this);
    oEditor = this;
    oEditor.dialog = dialog;
    dlg.showButton(button);
    dlg.setDialogHeight();
}

function getSource() {
    return oEditor.trimSpace(document.getElementById("fm_source").value);
}

function doPlay() {
    var embed = null,
        div = document.createElement('div'),
        pos, str, object, child, movieHeight, movieWidth, params = [], showWrapper,
        source = getSource(), iframe;

    showMovie = true;
    if (source === '') {
        return;
    }

    showWrapper = document.getElementById('fm_player');
    if (source.toLowerCase().indexOf("iframe") !== -1) {
        showWrapper.innerHTML = source;
        iframeSource = source;
        return;
    }

    if (/https?:\/\//.test(source)) {
        iframe = createIframeElement(defaultMovieWidth, defaultMovieHeight, source);
        if (iframe) {
            showWrapper.innerHTML = '';
            showWrapper.appendChild(iframe);
            iframeSource = iframe;
        }
        return;
    }

    pos = source.toLowerCase().indexOf("embed");
    if (pos !== -1) {
        str = source.substr(pos);
        pos = str.indexOf(">");
        div.innerHTML = "<" + str.substr(0, pos) + ">";
        embed = div.firstChild;
    } else {
        div.innerHTML = source;
        object = div.getElementsByTagName('OBJECT')[0];
        if (object && object.hasChildNodes()) {
            child = object.firstChild;
            movieWidth  = (isNaN(object.width) !== true) ? object.width : defaultMovieWidth;
            movieHeight = (isNaN(object.height) !== true) ? object.height : defaultMovieHeight;

            do {
                if (child.nodeName === 'PARAM' && typeof child.name !== 'undefined' && typeof child.value !== 'undefined')
                {
                    params.push({
                        key: child.name === 'movie' ? 'src' : child.name,
                        val: child.value
                    });
                }
                child = child.nextSibling;
            } while (child);

            if (params.length > 0) {
                embed = createEmbedElement(movieWidth, movieHeight, params, null);
            }
        }
    }

    if (embed !== null) {
        document.getElementById('fm_player').appendChild(embed);
    }
}

function createIframeElement(width, height, src) {
    var iframe = document.createElement('iframe'), uri, query, id, movie = null;

    uri = new oEditor.URI(src);
    if (uri.path && uri.path.charAt(0) !== '/') {
        uri.path = '/' + uri.path;
    }

    switch (uri.authority) {
        case 'youtu.be' :
        case 'youtube.com':
        case 'www.youtube.com':
            if (uri.path === '/watch' && uri.query) {
                query = uri.query.split('=');
                if (query[0] === 'v') {
                    movie = '/' + query[1];
                }
            }
            if (!movie && uri.path) {
                movie = uri.path;
                if (uri.query) {
                    movie += '?' + uri.query;
                }
            }
            if (movie) {
                movie = 'https://www.youtube.com/embed' + movie;
            }
            break;
        case 'vimeo.com' :
            if (uri.path) {
                movie = 'https://player.vimeo.com/video' + uri.path;
            }
            break;
        case 'afree.ca' :
            if (uri.path) {
                movie = 'http://play.afreecatv.com' + uri.path + '/embed';
            }
            break;
        case 'tv.naver.com' :
            if (uri.path) {
                id = uri.path.substring(uri.path.lastIndexOf('/'));
                movie = 'https://tv.naver.com/embed' + id + '?autoPlay=true';
            }
            break;
        case 'tv.kakao.com' :
            if (uri.path) {
                id = uri.path.substring(uri.path.lastIndexOf('/'));
                movie = 'https://tv.kakao.com/embed/player/cliplink' + id;
            }
            break;
        default :
            movie = null;
    }
    if (!movie) {
        return null;
    }

    iframe.setAttribute('width', width);
    iframe.setAttribute('height', height);
    iframe.setAttribute('frameborder', "0");
    iframe.setAttribute('allowfullscreen', "true");
    iframe.setAttribute('src', movie);
    return iframe;
}

function createEmbedElement(width, height, params, src) {
    var embed = document.createElement('embed'), i;

    embed.setAttribute("type", "application/x-shockwave-flash");
    embed.setAttribute('width', width);
    embed.setAttribute('height', height);

    if (src) {
        embed.setAttribute('src', src);
    }

    for (i = 0; i < params.length; i++) {
        embed.setAttribute(params[i].key, params[i].val);
    }

    return embed;
}

function popupClose() {
    document.getElementById('fm_player').innerHTML = '';
    oEditor.popupWinCancel();
}

function doSubmit()
{
    var source = getSource();
    if (source === '') {
        popupClose();
    }
    if (!showMovie) {
        document.getElementById('fm_player').style.visibility = 'hidden';
        doPlay();
    }

    if (iframeSource) {
        oEditor.insertHtmlPopup(iframeSource);
    } else {
        oEditor.insertFlash(source);
    }

    document.getElementById('fm_player').innerHTML = '';
    oEditor.popupWinClose();
}

