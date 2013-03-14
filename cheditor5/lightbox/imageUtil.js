////////////////////////////////////////////////////////////////////////
//
//                    CHEditor Image Util
//
////////////////////////////////////////////////////////////////////////
function addCaption (oImgElem) {
  if( oImgElem.parentNode && oImgElem.parentNode.className=="imgblock")
    return;

  var align = 'left';
  if (oImgElem.align == 'right') align = 'right';

  var oImgBlockElem = document.createElement("div");
  oImgBlockElem.className = "imgblock";
  oImgBlockElem.style.styleFloat = align;
  oImgElem.style.styleFloat = "none";

  if(align = 'left') oImgBlockElem.className = oImgBlockElem.className + " leftjust";
  if(align == 'right') oImgBlockElem.className = oImgBlockElem.className + " rightjust";

  var oHandle = oImgElem;

  if(oImgElem.parentNode.tagName.toUpperCase() == "A") oHandle = oImgElem.parentNode;

  var oOldHandle = oHandle.parentNode.replaceChild(oImgBlockElem,oHandle);
  oImgBlockElem.appendChild(oOldHandle);
  oHandle = null;

  var oCaptionElem = document.createElement("div");
  oCaptionElem.className = "caption";
  oCaptionElem.style.marginLeft = oImgElem.style.marginLeft;

  var oCaptionTextElem = document.createElement("div");
  oCaptionTextElem.className = "caption-text";
  oCaptionTextElem.style.width = oImgElem.style.width;
  var oCaptionText = document.createTextNode(oImgElem.alt);
  oCaptionTextElem.appendChild(oCaptionText );
  oCaptionElem.appendChild(oCaptionTextElem);

  oImgBlockElem.appendChild(oCaptionElem);

  with (oImgElem.style) {
    oCaptionElem.style.width = (oImgElem.scrollWidth)+"px";
  }

  oImgBlockElem.style.width = (oImgElem.scrollWidth)+"px";
  return true;
}

var hs = {
    iconsPath : './icons/imageutil/',
    restoreCursor : "zoomout.cur",
    fullExpandIcon : 'fullexpand.gif',
    numberOfImagesToPreload : 5,
    zIndexCounter : 1001,
    fullExpandTitle : '실제 크기로 확대합니다.',
    loadingText : '불러오는 중입니다...',
    loadingTitle : '취소합니다.',
    anchor : 'auto',
    align : 'auto',
    captionId : null,
    slideshowGroup : '',
    enableKeyListener : true,
    minWidth: 200,
    minHeight: 200,
    outlineType : 'rounded-white',
    preloadTheseImages : new Array(),
    continuePreloading: true,
    expandedImagesCounter : 0,
    alt : null,
    expanders : new Array(),
    overrides : new Array(
        'anchor',
        'align',
        'outlineType',
        'spaceForCaption',
        null,
        'minWidth',
        'minHeight',
        'captionId',
        'allowSizeReduction',
        'slideshowGroup',
        'enableKeyListener'
    ),
    overlays : new Array(),
    toggleImagesGroup : null,
    ie : (document.all && !window.opera),
    nn6 : document.getElementById && !document.all,
    safari : navigator.userAgent.indexOf("Safari") != -1,
    hasFocused : false,
    isDrag : false,

createElement : function (tag, attribs, styles, parent) {
    var el = document.createElement(tag);
    if (attribs) hs.setAttribs(el, attribs);
    if (styles) hs.setStyles(el, styles);
    if (parent) parent.appendChild(el);
    return el;
},

setAttribs : function (el, attribs) {
    for (var x in attribs) {
        el[x] = attribs[x];
    }
},

setStyles : function (el, styles) {
    for (var x in styles) {
        el.style[x] = styles[x];
    }
},

ieVersion : function () {
    arr = navigator.appVersion.split("MSIE");
    return parseFloat(arr[1]);
},

$ : function (id) {
        return document.getElementById(id);
},

clientInfo : function ()    {
    var iebody = (document.compatMode && document.compatMode != "BackCompat")
        ? document.documentElement : document.body;

    this.width = hs.ie ? iebody.clientWidth : self.innerWidth;
    this.height = hs.ie ? iebody.clientHeight : self.innerHeight;
    this.scrollLeft = hs.ie ? iebody.scrollLeft : pageXOffset;
    this.scrollTop = hs.ie ? iebody.scrollTop : pageYOffset;
},

position : function(el) {
    var parent = el;
    var p = Array();
    p.x = parent.offsetLeft;
    p.y = parent.offsetTop;
    while (parent.offsetParent) {
        parent = parent.offsetParent;
        p.x += parent.offsetLeft;
        p.y += parent.offsetTop;
    }
    return p;
},

run : function(a, alt,params, contentType) {
    try {
        new ImgUTIL(a, alt,params, contentType);
        return false;

    } catch(e) {
        return true;
    }
},

focusTopmost : function() {
    var topZ = 0;
    var topmostKey = -1;
    for (i = 0; i < hs.expanders.length; i++) {
        if (hs.expanders[i]) {
            if (hs.expanders[i].wrapper.style.zIndex && hs.expanders[i].wrapper.style.zIndex > topZ) {
                topZ = hs.expanders[i].wrapper.style.zIndex;

                topmostKey = i;
            }
        }
    }
    if (topmostKey == -1) hs.focusKey = -1;
    else hs.expanders[topmostKey].focus();
},

closeId : function(elId) {
    for (i = 0; i < hs.expanders.length; i++) {
        if (hs.expanders[i] && (hs.expanders[i].thumb.id == elId || hs.expanders[i].a.id == elId)) {
            hs.expanders[i].doClose();
            return;
        }
    }
},

close : function(el) {
    var key = hs.getWrapperKey(el);
    if (hs.expanders[key]) hs.expanders[key].doClose();
    return false;
},


toggleImages : function(closeId, expandEl) {
    if (closeId) hs.closeId(closeId);
    if (hs.ie) expandEl.href = expandEl.href.replace('about:(blank)?', '');
    hs.toggleImagesExpandEl = expandEl;
    return false;
},

getAdjacentAnchor : function(key, op) {
    var aAr = document.getElementsByTagName('A');
    var hsAr = new Array;
    for (i = 0; i < aAr.length; i++) {
        if (hs.isHsAnchor(aAr[i])) {
            hsAr.push(aAr[i]);
        }
    }

    var activeI = -1;
    for (i = 0; i < hsAr.length; i++) {
        if (hs.expanders[key] && hsAr[i] == hs.expanders[key].a) {
            activeI = i;
            break;
        }
    }
    return hsAr[activeI + op];

},

getSrc : function (a) {
    return a.rel.replace(/_slash_/g, '/') || a.href;
},

registerOverlay : function (overlay) {
    hs.overlays.push(overlay);
},

getWrapperKey : function (el) {
    var key = -1;
    while (el.parentNode)   {
        el = el.parentNode;
        if (el.id && el.id.match(/^highslide-wrapper-[0-9]+$/)) {
            key = el.id.replace(/^highslide-wrapper-([0-9]+)$/, "$1");
            break;
        }
    }
    return key;
},

cleanUp : function () {
    if (hs.toggleImagesExpandEl) {
        hs.toggleImagesExpandEl.onclick();
        hs.toggleImagesExpandEl = null;
    }
    else {
        for (i = 0; i < hs.expanders.length; i++) {
            if (hs.expanders[i] && hs.expanders[i].isExpanded) hs.focusTopmost();
        }
    }
},

mouseDownHandler : function(e) {
    if (!e) e = window.event;
    if (e.button > 1) return true;
    if (!e.target) e.target = e.srcElement;

    var fobj = e.target;
    while (!fobj.tagName.match(/(HTML|BODY)/)   && !fobj.className.match(/highslide-(image|move|html)/)) {
        fobj = hs.nn6 ? fobj.parentNode : fobj.parentElement;
    }
    if (fobj.tagName.match(/(HTML|BODY)/)) return;

    hs.dragKey = hs.getWrapperKey(fobj);

    if (fobj.className.match(/highslide-(image|move)/)) {
        hs.isDrag = true;
        hs.dragObj = hs.expanders[hs.dragKey].content;

        if (fobj.className.match('highslide-image')) hs.dragObj.style.cursor = 'move';
        tx = parseInt(hs.expanders[hs.dragKey].wrapper.style.left);
        ty = parseInt(hs.expanders[hs.dragKey].wrapper.style.top);

        hs.leftBeforeDrag = tx;
        hs.topBeforeDrag = ty;

        hs.dragX = hs.nn6 ? e.clientX : event.clientX;
        hs.dragY = hs.nn6 ? e.clientY : event.clientY;
        hs.addEventListener(document, 'mousemove', hs.mouseMoveHandler);
        if (e.preventDefault) e.preventDefault();


        if (hs.dragObj.className.match(/highslide-(image|html)-blur/)) {
            hs.expanders[hs.dragKey].focus();
            hs.hasFocused = true;
        }
        return false;
    }
    else if (fobj.className.match(/highslide-html/)) {
        hs.expanders[hs.dragKey].focus();
        hs.expanders[hs.dragKey].redoShowHide();
        hs.hasFocused = false;
    }
},

mouseMoveHandler : function(e) {
    if (hs.isDrag) {
        if (!hs.expanders[hs.dragKey] || !hs.expanders[hs.dragKey].wrapper) return;
        var wrapper = hs.expanders[hs.dragKey].wrapper;

        var left = hs.nn6 ? tx + e.clientX - hs.dragX : tx + event.clientX - hs.dragX;
        wrapper.style.left = left +'px';
        var top = hs.nn6 ? ty + e.clientY - hs.dragY : ty + event.clientY - hs.dragY;
        wrapper.style.top  = top +'px';
        return false;
    }
},

mouseUpHandler : function(e) {
    if (!e) e = window.event;
    if (e.button > 1) return true;
    if (!e.target) e.target = e.srcElement;

    hs.isDrag = false;
    var fobj = e.target;

    while (!fobj.tagName.match(/(HTML|BODY)/) && !fobj.className.match(/highslide-(image|move)/)) {
        fobj = fobj.parentNode;
    }
    if (fobj.className.match(/highslide-(image|move)/) && hs.expanders[hs.dragKey]) {
        if (fobj.className.match('highslide-image')) {
            fobj.style.cursor = hs.styleRestoreCursor;
            hs.removeEventListener(document, 'mousemove', hs.mouseMoveHandler);
        }
        var left = parseInt(hs.expanders[hs.dragKey].wrapper.style.left);
        var top = parseInt(hs.expanders[hs.dragKey].wrapper.style.top);
        var hasMoved = left != hs.leftBeforeDrag || top != hs.topBeforeDrag;
        if (!hasMoved && !hs.hasFocused) {
            hs.expanders[hs.dragKey].doClose();
        }
        else if (hasMoved || (!hasMoved && hs.hasHtmlExpanders)) {
            hs.expanders[hs.dragKey].redoShowHide();
        }
        hs.hasFocused = false;

    }
    else if (fobj.className.match('highslide-image-blur')) {
        fobj.style.cursor = hs.styleRestoreCursor;
    }
},

addEventListener : function (el, event, func) {
    if (document.addEventListener) el.addEventListener(event, func, false);
    else if (document.attachEvent) el.attachEvent('on'+ event, func);
    else el[event] = func;
},

removeEventListener : function (el, event, func) {
    if (document.removeEventListener) el.removeEventListener(event, func, false);
    else if (document.detachEvent) el.detachEvent('on'+ event, func);
    else el[event] = null;
},

isHsAnchor : function (a) {
    return (a.className && (a.className.match("highslide$") || a.className.match("highslide ")));
},

preloadFullImage : function (i) {
    if (hs.continuePreloading && hs.preloadTheseImages[i] && hs.preloadTheseImages[i] != 'undefined') {
        var img = document.createElement('img');
        img.onload = function() { hs.preloadFullImage(i + 1); };
        img.src = hs.preloadTheseImages[i];
    }
},

preloadImages : function (number) {
    if (number) this.numberOfImagesToPreload = number;
    var j = 0;
    var aTags = document.getElementsByTagName('A');
    for (i = 0; i < aTags.length; i++) {
        a = aTags[i];
        if (hs.isHsAnchor(a)) {
            if (j < this.numberOfImagesToPreload) {
                hs.preloadTheseImages[j] = hs.getSrc(a);
                j++;
            }
        }
    }

    hs.preloadFullImage(0);
    var cur = document.createElement('img');
    cur.src = hs.iconsPath + hs.restoreCursor;

    for (i = 1; i <= 8; i++) {
        var img = document.createElement('img');
        img.src = hs.iconsPath +i+".png";
    }
}
};

ImgUTIL = function(a, alt, params, contentType) {
    try {
        hs.continuePreloading = false;
        hs.container = hs.$('lightbox-container');

        hs.alt = alt;
        if (params && params.thumbnailId) {
            var el = hs.$(params.thumbnailId);

        }
        else {
            for (i = 0; i < a.childNodes.length; i++) {
                if (a.childNodes[i].tagName && a.childNodes[i].tagName.toUpperCase() == 'IMG') {
                    var el = a.childNodes[i];
                    break;
                }
            }
        }

        if (!el) el = a;

        for (i = 0; i < hs.expanders.length; i++) {
            if (hs.expanders[i] && hs.expanders[i].thumb != el && !hs.expanders[i].onLoadStarted) {
                hs.expanders[i].cancelLoading();
            }
        }

        for (i = 0; i < hs.expanders.length; i++) {
            if (hs.expanders[i] && hs.expanders[i].thumb == el) {
                hs.expanders[i].focus();
                return false;
            }
        }

        this.key = hs.expandedImagesCounter++;
        hs.expanders[this.key] = this;
        if (contentType == 'html') {
            this.isHtml = true;
            this.contentType = 'html';
        }
        else {
            this.isImage = true;
            this.contentType = 'image';
        }
        this.a = a;
        for (i = 0; i < hs.overrides.length; i++) {
            var name = hs.overrides[i];
            if (params && params[name] != undefined) this[name] = params[name];
            else this[name] = hs[name];
        }

        if (hs.toggleImagesGroup != null && hs.toggleImagesGroup != this.slideshowGroup) {
            hs.toggleImagesGroup = null;
            hs.expanders[this.key] = null;
            return;
        }

        this.thumbsUserSetId = el.id || a.id;
        this.thumb = el;
        this.overlays = new Array();

        var pos = hs.position(el);
        this.wrapper = hs.createElement(
            'div',
            {
                id: 'highslide-wrapper-'+ this.key,
                className: null
            },
            {
                visibility: 'hidden',
                position: 'absolute',
                zIndex: hs.zIndexCounter++
            }
        );

        this.thumbWidth = el.width ? el.width : el.offsetWidth;
        this.thumbHeight = el.height ? el.height : el.offsetHeight;
        this.thumbLeft = pos.x;
        this.thumbTop = pos.y;
        this.thumbClass = el.className;

        this.thumbOffsetBorderW = (this.thumb.offsetWidth - this.thumbWidth) / 2;
        this.thumbOffsetBorderH = (this.thumb.offsetHeight - this.thumbHeight) / 2;

        if (this.isImage) this.imageCreate();

        return false;

    } catch(e) {
        return true;
    }

};

ImgUTIL.prototype.displayLoading = function() {
    if (this.onLoadStarted) return;

    this.originalCursor = this.a.style.cursor;
    this.a.style.cursor = 'wait';

    this.loading = hs.createElement('a',
        {
            className: 'imageUtil-loading',
            title: hs.loadingTitle,
            href: 'javascript:hs.expanders['+ this.key +'].cancelLoading()',
            innerHTML: hs.loadingText
        },
        {
            position: 'absolute',
            visibility: 'hidden'
        }, hs.container);

    if (hs.ie) this.loading.style.filter = 'alpha(opacity='+ (100*0.75) +')';
    else this.loading.style.opacity = 0.75;

    this.loading.style.left = (this.thumbLeft + this.thumbOffsetBorderW
        + (this.thumbWidth - this.loading.offsetWidth) / 2) +'px';
    this.loading.style.top = (this.thumbTop
        + (this.thumbHeight - this.loading.offsetHeight) / 2) +'px';
    setTimeout(
        "if (hs.expanders["+ this.key +"] && hs.expanders["+ this.key +"].loading) "
        + "hs.expanders["+ this.key +"].loading.style.visibility = 'visible';",
        100
    );
};

ImgUTIL.prototype.imageCreate = function() {
    var img = document.createElement('img');
    var key = this.key;

    var img = document.createElement('img');
    this.content = img;
    img.onload = function () { if (hs.expanders[key]) hs.expanders[key].onLoad();  };
    img.className = 'imageUtil-image '+ this.thumbClass;
    img.style.visibility = 'hidden';
    img.style.display = 'block';
    img.style.position = 'absolute';
    img.style.zIndex = 3;
    img.onmouseover = function () {
        if (hs.expanders[key]) hs.expanders[key].onMouseOver();
    };
    img.onmouseout = function (e) {
        var rel = e ? e.relatedTarget : event.toElement;
        if (hs.expanders[key]) hs.expanders[key].onMouseOut(rel);
    };
    if (hs.safari) hs.container.appendChild(img);
    img.src = hs.getSrc(this.a);

    this.displayLoading();
};

ImgUTIL.prototype.onLoad = function() {
    try {
        if (!this.content) return;
        if (this.onLoadStarted) return;
        else this.onLoadStarted = true;

        if (this.loading) {
            hs.container.removeChild(this.loading);
            this.loading = null;
            this.a.style.cursor = this.originalCursor || '';
        }

        if (this.isImage) {
            this.newWidth = this.content.width;
            this.newHeight = this.content.height;
            this.fullExpandWidth = this.newWidth;
            this.fullExpandHeight = this.newHeight;

            this.content.width = this.thumbWidth;
            this.content.height = this.thumbHeight;
        }

        var modMarginBottom = 30;
        var d = document.createElement("div");
        d.id = 'tmpID';
        if (hs.alt) {
            d.className = 'imageUtil-caption';
            d.appendChild(document.createTextNode(hs.alt));
        }
        else
            d.style.display = 'none';

        this.caption = d;

        if (this.caption) {
            modMarginBottom += 30;
            this.caption.id = null;
        }

        this.wrapper.appendChild(this.content);
        this.content.style.position = 'relative';
        if (this.caption) this.wrapper.appendChild(this.caption);
        this.wrapper.style.left = this.thumbLeft +'px';
        this.wrapper.style.top = this.thumbTop +'px';
        hs.container.appendChild(this.wrapper);
        if (this.swfObject) this.swfObject.write(this.flashContainerId);

        this.offsetBorderW = (this.wrapper.offsetWidth - this.thumbWidth) / 2;
        this.offsetBorderH = (this.wrapper.offsetHeight - this.thumbHeight) / 2;
        var modMarginRight = 30 + 2 * this.offsetBorderW;
        modMarginBottom += 2 * this.offsetBorderH;

        var ratio = this.newWidth / this.newHeight;
        var minWidth = this.minWidth;
        var minHeight = this.minHeight;

        var justify = { x: 'auto', y: 'auto' };
        if (this.align == 'center') {
            justify.x = 'center';
            justify.y = 'center';
        } else {
            if (this.anchor.match(/^top/)) justify.y = null;
            if (this.anchor.match(/right$/)) justify.x = 'max';
            if (this.anchor.match(/^bottom/)) justify.y = 'max';
            if (this.anchor.match(/left$/)) justify.x = null;
        }

        client = new hs.clientInfo();

        this.x = {
            min: parseInt(this.thumbLeft) - this.offsetBorderW + this.thumbOffsetBorderW,
            span: this.newWidth,
            minSpan: this.newWidth < minWidth ? this.newWidth : minWidth,
            justify: justify.x,
            marginMin:15,
            marginMax: modMarginRight,
            scroll: client.scrollLeft,
            clientSpan: client.width,
            thumbSpan: this.thumbWidth
        };
        var oldRight = this.x.min + parseInt(this.thumbWidth);
        this.x = this.justify(this.x);

        this.y = {
            min: parseInt(this.thumbTop) - this.offsetBorderH + this.thumbOffsetBorderH,
            span: this.newHeight,
            minSpan: this.newHeight < minHeight ? this.newHeight : minHeight,
            justify: justify.y,
            marginMin: 15,
            marginMax: modMarginBottom,
            scroll: client.scrollTop,
            clientSpan: client.height,
            thumbSpan: this.thumbHeight
        };
        var oldBottom = this.y.min + parseInt(this.thumbHeight);
        this.y = this.justify(this.y);

        if (this.isHtml) this.htmlSizeOperations();
        if (this.isImage) this.correctRatio(ratio);

        var x = this.x;
        var y = this.y;
        var imgPos = {x: x.min - 20, y: y.min - 20, w: x.span + 40, h: y.span + 40 + 30};

        hs.hideSelects = (hs.ie && hs.ieVersion() < 7);
        if (hs.hideSelects) this.showHideElements('SELECT', 'hidden', imgPos);
        hs.hideIframes = (window.opera || navigator.vendor == 'KDE' || (hs.ie && hs.ieVersion() < 5.5));
        if (hs.hideIframes) this.showHideElements('IFRAME', 'hidden', imgPos);

        this.changeSize(
            this.thumbLeft + this.thumbOffsetBorderW - this.offsetBorderW,
            this.thumbTop + this.thumbOffsetBorderH - this.offsetBorderH,
            this.thumbWidth,
            this.thumbHeight,
            x.min,
            y.min,
            x.span,
            y.span,
        250,
        10
        );

        setTimeout(
            "if (hs.expanders["+ this.key +"])"
            + "hs.expanders["+ this.key +"].onExpanded()",
            250
        );

    } catch(e) {
        if (hs.expanders[this.key] && hs.expanders[this.key].a)
            window.location.href = hs.getSrc(hs.expanders[this.key].a);
    }
};

ImgUTIL.prototype.changeSize = function(x1, y1, w1, h1, x2, y2, w2, h2, dur, steps) {
    dW = (w2 - w1) / steps;
    dH = (h2 - h1) / steps;
    dX = (x2 - x1) / steps;
    dY = (y2 - y1) / steps;

    for (i = 1; i < 10; i++) {
        w1 += dW;
        h1 += dH;
        x1 += dX;
        y1 += dY;

        setTimeout(
            "if (hs.expanders["+ this.key +"]) "
            + "hs.expanders["+ this.key +"]."+ this.contentType +"SetSize("
            + w1 +", "+ h1 +", "+ x1 +", "+ y1 +")",
            Math.round(i * (dur / steps))
        );
    }
};

ImgUTIL.prototype.imageSetSize = function (width, height, left, top) {
    try {
        this.content.width = width;
        this.content.height = height;

        hs.setStyles ( this.wrapper,
            {
                'visibility': 'visible',
                'left': left +'px',
                'top': top +'px'
            }
        );

        this.content.style.visibility = 'visible';
        if (this.thumb.tagName.toUpperCase() == 'IMG') {
            this.thumb.style.visibility = 'hidden';
            var oTemp = this.thumb.parentNode;
            if (oTemp.tagName.toUpperCase() == 'A') oTemp = oTemp.parentNode;
            oTemp.childNodes[1].style.visibility = 'hidden';
        }
    } catch(e) {
        window.location.href = hs.getSrc(hs.expanders[this.key].a);
    }
};

ImgUTIL.prototype.onExpanded = function() {
    this[this.contentType +'SetSize'](this.x.span, this.y.span, this.x.min, this.y.min);
    this.isExpanded = true;
    this.focus();
    this.createCustomOverlays();

    if (this.caption) this.writeCaption();
    if (this.fullExpandWidth > this.x.span) this.createFullExpand();
    if (!this.caption) this.onDisplayFinished();
};

ImgUTIL.prototype.onDisplayFinished = function() {
    var nextA = hs.getAdjacentAnchor(this.key, 1);
    if (nextA) {
        var img = document.createElement('img');
        img.src = hs.getSrc(nextA);
    }
};

ImgUTIL.prototype.justify = function (p) {
    if (p.justify == 'auto' || p.justify == 'center') {
        var hasMovedMin = false;
        var allowReduce = true;
        if (p.justify == 'center') p.min = Math.round(p.scroll + (p.clientSpan - p.span - p.marginMax) / 2);
        else p.min = Math.round(p.min - ((p.span - p.thumbSpan) / 2));
        if (p.min < p.scroll + p.marginMin) {
            p.min = p.scroll + p.marginMin;
            hasMovedMin = true;
        }

        if (p.span < p.minSpan) {
            p.span = p.minSpan;
            allowReduce = false;
        }
        if (p.min + p.span > p.scroll + p.clientSpan - p.marginMax) {
            if (hasMovedMin && allowReduce) p.span = p.clientSpan - p.marginMin - p.marginMax;
            else if (p.span < p.clientSpan - p.marginMin - p.marginMax) {
                p.min = p.scroll + p.clientSpan - p.span - p.marginMin - p.marginMax;
            }
            else {
                p.min = p.scroll + p.marginMin;
                if (allowReduce) p.span = p.clientSpan - p.marginMin - p.marginMax;
            }

        }

        if (p.span < p.minSpan) {
            p.span = p.minSpan;
            allowReduce = false;
        }

    }
    else if (p.justify == 'max') {
        p.min = Math.floor(p.min - p.span + p.thumbSpan);
    }

    if (p.min < p.marginMin) {
        tmpMin = p.min;
        p.min = p.marginMin;
        if (allowReduce) p.span = p.span - (p.min - tmpMin);
    }
    return p;
};

ImgUTIL.prototype.correctRatio = function(ratio) {
    var x = this.x;
    var y = this.y;
    var changed = false;
    if (x.span / y.span > ratio) {
        var tmpWidth = x.span;
        x.span = y.span * ratio;
        if (x.span < x.minSpan) {
            x.span = x.minSpan;
            y.span = x.span / ratio;
        }
        changed = true;

    }
    else if (x.span / y.span < ratio) {
        var tmpHeight = y.span;
        y.span = x.span / ratio;
        changed = true;
    }

    if (changed) {
        x.min = parseInt(this.thumbLeft) - this.offsetBorderW + this.thumbOffsetBorderW;
        x.minSpan = x.span;
        this.x = this.justify(x);

        y.min = parseInt(this.thumbTop) - this.offsetBorderH + this.thumbOffsetBorderH;
        y.minSpan = y.span;
        this.y = this.justify(y);
    }
};

ImgUTIL.prototype.cancelLoading = function() {
    this.a.style.cursor = this.originalCursor;

    if (this.loading) {
        hs.container.removeChild(this.loading);
        this.loading = null;
    }

    hs.expanders[this.key] = null;
};

ImgUTIL.prototype.writeCaption = function() {
    try {
        this.wrapper.style.width = this.wrapper.offsetWidth +'px';
        this.caption.style.visibility = 'hidden';
        this.caption.style.position = 'relative';
        if (hs.ie) this.caption.style.zoom = 1;
        this.caption.className += ' imageUtil-display-block';

        var capHeight = this.caption.offsetHeight;
        var slideHeight = (capHeight < this.content.height) ? capHeight : this.content.height;
        this.caption.style.marginTop = '-'+ slideHeight +'px';

        this.caption.style.zIndex = 2;

        var step = 1;
        if (slideHeight > 400) step = 4;
        else if (slideHeight > 200) step = 2;
        else if (slideHeight > 100) step = 1;

        setTimeout("if (hs.expanders["+ this.key +"] && hs.expanders["+ this.key +"].caption) "
                + "hs.expanders["+ this.key +"].caption.style.visibility = 'visible'", 10);
        var t = 0;

        for (marginTop = -slideHeight; marginTop <= 0; marginTop += step, t += 10) {
            var eval = "if (hs.expanders["+ this.key +"] && hs.expanders["+ this.key +"].caption) { "
                + "hs.expanders["+ this.key +"].caption.style.marginTop = '"+ marginTop +"px';";
            if (marginTop >= 0) eval += 'hs.expanders['+ this.key +'].writeOutline();';
            eval += "}";
            setTimeout (eval, t);
        }

    } catch (e) {}
};

ImgUTIL.prototype.writeOutline = function() {
    this.outline = new Array();
    var v = hs.ieVersion();
    hs.hasAlphaImageLoader = hs.ie && v >= 5.5 && v < 9;
    hs.hasIe7Bug = hs.ie && v == 7;
    hs.hasPngSupport = !hs.ie;
    this.preloadOutlineElement(1);
};

ImgUTIL.prototype.preloadOutlineElement = function(i) {
    if (!hs.hasAlphaImageLoader && !hs.hasPngSupport && !hs.hasIe7Bug)
        return;

    if (this.outline[i] && this.outline[i].onload) {
        this.outline[i].onload = null;
        return;
    }

    var src = hs.iconsPath +i+".png";

    if (hs.hasAlphaImageLoader) {

        this.outline[i] = hs.createElement('div',
            null,
            {
                filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader("
                    + "enabled=true, sizingMethod=scale src='"+ src + "') "
            }
        );
    }
    else if (hs.hasIe7Bug) {
        this.outline[i] = hs.createElement('div',
            null,
            {
                background: 'url('+ src +')'
            }
        );
    }

    var img = document.createElement('img');
    if (hs.hasPngSupport) {
        this.outline[i] = img;
    }

    this.outline[i].style.position = 'absolute';
    var dim = (i % 2 == 1) ? 10 : 20;
    this.outline[i].style.height = dim +'px';
    this.outline[i].style.width = dim +'px';
    if (hs.ie) {
        this.outline[i].style.lineHeight = dim +'px';
        this.outline[i].style.fontSize = 0;
    }

    this.wrapper.appendChild(this.outline[i]);
    if (i < 8) this.preloadOutlineElement(i + 1);
    else this.repositionOutline(0);

    if (hs.safari) {
        this.outline[i].style.left = '10px';
        this.outline[i].style.top = '10px';
        hs.container.appendChild(img);
    }
    img.src = src;
};

ImgUTIL.prototype.displayOutline = function() {
    this.repositionOutline(12);
    for (i = 1; i <= 8; i++) {
        this.wrapper.appendChild(this.outline[i]);
    }
    this.hasOutline = true;

    for (i = 10, t = 0; i >= 0; i--, t += 50) {
        setTimeout(
            'if (hs.expanders['+ this.key +']) hs.expanders['+ this.key +'].repositionOutline('+ i +')',
            t
        );
    }
};

ImgUTIL.prototype.repositionOutline = function(offset) {
    if (this.isClosing) return;

    var w = this.wrapper.offsetWidth;
    var h = this.wrapper.offsetHeight;

    var fix = Array (
        Array (Array (1, 5), 'width', w - (2 * offset) - 20),
        Array (Array (1, 5), 'left', 10 + offset),
        Array (Array (1, 2, 8), 'top', -10 + offset),
        Array (Array (2, 4), 'left', w - 10 - offset),
        Array (Array (3, 3), 'left', w - offset),
        Array (Array (3, 7), 'top', 10 + offset),
        Array (Array (3, 7), 'height', h - (2 * offset) - 20),
        Array (Array (4, 6), 'top', h - 10 - offset),
        Array (Array (5, 5), 'top', h - offset),
        Array (Array (6, 7, 8), 'left', -10 + offset)
    );
    if (navigator.vendor == 'KDE') {
        fix.push(Array(1, 5), 'height', (offset % 2) + 10);
    }
    for (i = 0; i < fix.length; i++) {
        for (j = 0; j < fix[i][0].length; j++) {
            this.outline[fix[i][0][j]].style[fix[i][1]] = fix[i][2] +'px';
        }
    }

    if (offset == 0) this.onDisplayFinished();
};

ImgUTIL.prototype.showHideElements = function (tagName, visibility, imgPos) {
    var els = document.getElementsByTagName(tagName);
    if (els) {
        for (i = 0; i < els.length; i++) {
            if (els[i].nodeName == tagName) {
                var hiddenBy = els[i].getAttribute('hidden-by');

                if (visibility == 'visible' && hiddenBy) {
                    hiddenBy = hiddenBy.replace('['+ this.key +']', '');
                    els[i].setAttribute('hidden-by', hiddenBy);
                    if (!hiddenBy) els[i].style.visibility = 'visible';

                }
                else if (visibility == 'hidden') {
                    var elPos = hs.position(els[i]);
                    elPos.w = els[i].offsetWidth;
                    elPos.h = els[i].offsetHeight;

                    var clearsX = (elPos.x + elPos.w < imgPos.x || elPos.x > imgPos.x + imgPos.w);
                    var clearsY = (elPos.y + elPos.h < imgPos.y || elPos.y > imgPos.y + imgPos.h);
                    var wrapperKey = hs.getWrapperKey(els[i]);
                    if (!clearsX && !clearsY && wrapperKey != this.key) {
                        if (!hiddenBy) {
                            els[i].setAttribute('hidden-by', '['+ this.key +']');
                        }
                        else if (!hiddenBy.match('['+ this.key +']')) {
                            els[i].setAttribute('hidden-by', hiddenBy + '['+ this.key +']');
                        }
                        els[i].style.visibility = 'hidden';
                    }
                    else if (hiddenBy == '['+ this.key +']' || hs.focusKey == wrapperKey) {
                        els[i].setAttribute('hidden-by', '');
                        els[i].style.visibility = 'visible';
                    }
                    else if (hiddenBy && hiddenBy.match('['+ this.key +']')) {
                        els[i].setAttribute('hidden-by', hiddenBy.replace('['+ this.key +']', ''));
                    }
                }
            }
        }
    }
};

ImgUTIL.prototype.focus = function() {
    for (i = 0; i < hs.expanders.length; i++) {
        if (hs.expanders[i] && i == hs.focusKey) {
            var blurExp = hs.expanders[i];
            blurExp.content.className += ' imageUtil-'+ blurExp.contentType +'-blur';
            if (blurExp.caption) {
                blurExp.caption.className += ' imageUtil-caption-blur';
            }
            if (blurExp.isImage) {
                blurExp.content.style.cursor = hs.ie ? 'hand' : 'pointer';
            }
        }
    }

    this.wrapper.style.zIndex = hs.zIndexCounter++;
    this.content.className = 'highslide-'+ this.contentType;
    if (this.caption)
        this.caption.className = this.caption.className.replace(' imageUtil-caption-blur', '');

    if (this.isImage) {
        this.content.title = '';

        hs.styleRestoreCursor = window.opera ? 'pointer' : 'url('+ hs.iconsPath + hs.restoreCursor +'), pointer';
        if (hs.ie && hs.ieVersion() < 6) hs.styleRestoreCursor = 'hand';
        this.content.style.cursor = hs.styleRestoreCursor;
    }

    hs.focusKey = this.key;
};

ImgUTIL.prototype.doClose = function() {
    try {
        if (!hs.expanders[this.key]) return;
        var exp = hs.expanders[this.key];
        this.isClosing = true;
        var n = this.wrapper.childNodes.length;
        for (i = n - 1; i > 0 ; i--) {
            var child = this.wrapper.childNodes[i];
            if (child != this.content) {
                this.wrapper.removeChild(this.wrapper.childNodes[i]);
            }
        }

        if (this.scrollerDiv && this.scrollerDiv != 'scrollingContent') exp[this.scrollerDiv].style.overflow = 'hidden';

        hs.outlinePreloader = 0;
        this.wrapper.style.width = null;

        var width = (this.isImage) ? this.content.width : parseInt(this.content.style.width);
        var height = (this.isImage) ? this.content.height : parseInt(this.content.style.height);
        this.changeSize(
            parseInt(this.wrapper.style.left),
            parseInt(this.wrapper.style.top),
            width,
            height,
            this.thumbLeft - this.offsetBorderW + this.thumbOffsetBorderW,
            this.thumbTop - this.offsetBorderH + this.thumbOffsetBorderH,
            this.thumbWidth,
            this.thumbHeight,
        250,
        10
        );

        setTimeout('if (hs.expanders['+ this.key +']) hs.expanders['+ this.key +'].onEndClose()',250);

    } catch(e) {
        hs.expanders[this.key].onEndClose();
    }
};

ImgUTIL.prototype.onEndClose = function () {
    this.thumb.style.visibility = 'visible';
    var oTemp = this.thumb.parentNode;
    if (oTemp.tagName.toUpperCase() == 'A') oTemp = oTemp.parentNode;
    oTemp.childNodes[1].style.visibility = 'visible';

    if (hs.hideSelects) this.showHideElements('SELECT', 'visible');
    if (hs.hideIframes) this.showHideElements('IFRAME', 'visible');

    this.wrapper.parentNode.removeChild(this.wrapper);
    hs.expanders[this.key] = null;
    hs.cleanUp();
};

ImgUTIL.prototype.createOverlay = function (el, position, hideOnMouseOut, opacity) {
    if (typeof el == 'string' && hs.$(el)) {
        el = hs.$(el).cloneNode(true);
        el.id = null;
    }
    if (!el || typeof el == 'string' || !this.isImage) return;

    if (!position) var position = 'center center';
    var overlay = hs.createElement(
        'div',
        null,
        {
            'position' : 'absolute',
            'zIndex' : 3,
            'visibility': 'hidden'
        },
        this.wrapper
    );
    if (opacity && opacity < 1) {
        if (hs.ie) overlay.style.filter = 'alpha(opacity='+ (opacity * 100) +')';
        else overlay.style.opacity = opacity;
    }
    el.className += ' imageUtil-display-block';
    overlay.appendChild(el);

    var left = this.offsetBorderW;
    var top = this.offsetBorderH;

    if (position.match(/^bottom/)) top += this.content.height - overlay.offsetHeight;
    if (position.match(/^center/)) top += (this.content.height - overlay.offsetHeight) / 2;
    if (position.match(/right$/)) left += this.content.width - overlay.offsetWidth;
    if (position.match(/center$/)) left += (this.content.width - overlay.offsetWidth) / 2;
    overlay.style.left = left +'px';
    overlay.style.top = top +'px';

    if (this.mouseIsOver || !hideOnMouseOut) overlay.style.visibility = 'visible';
    if (hideOnMouseOut) overlay.setAttribute('hideOnMouseOut', true);

    this.overlays.push(overlay);
};

ImgUTIL.prototype.createCustomOverlays = function() {
    for (i = 0; i < hs.overlays.length; i++) {
        var o = hs.overlays[i];
        if (o.thumbnailId == null || o.thumbnailId == this.thumbsUserSetId) {
            this.createOverlay(o.overlayId, o.position, o.hideOnMouseOut, o.opacity);
        }
    }
};

ImgUTIL.prototype.onMouseOver = function () {
    this.mouseIsOver = true;
    for (i = 0; i < this.overlays.length; i++) {
        this.overlays[i].style.visibility = 'visible';
    }
};

ImgUTIL.prototype.onMouseOut = function(rel) {
    this.mouseIsOver = false;
    var hideThese = new Array();
    for (i = 0; i < this.overlays.length; i++) {
        var node = rel;
        while (node && node.parentNode) {
            if (node == this.overlays[i]) return;
            node = node.parentNode;
        }
        if (this.overlays[i].getAttribute('hideOnMouseOut')) {
            hideThese.push(this.overlays[i]);
        }
    }
    for (i = 0; i < hideThese.length; i++) {
        hideThese[i].style.visibility = 'hidden';
    }
};

ImgUTIL.prototype.createFullExpand = function () {
    var a = hs.createElement(
        'a',
        {
            href: 'javascript:hs.expanders['+ this.key +'].doFullExpand();',
            title:hs.fullExpandTitle
        },
        {
            background: 'url('+ hs.iconsPath + hs.fullExpandIcon+')',
            display: 'block',
            margin: '5px 0px 0px 5px',
            width: '35px',
            height: '35px'
        }
    );

    this.createOverlay(a, 'top left', true, 1);
    this.fullExpandIcon = a;
};

ImgUTIL.prototype.doFullExpand = function () {
    try {
        var newLeft = parseInt(this.wrapper.style.left) - (this.fullExpandWidth - this.content.width) / 2;
        if (newLeft < 15) newLeft = 15;
        this.wrapper.style.left = newLeft +'px';
        var borderOffset = this.wrapper.offsetWidth - this.content.width;
        this.content.width = this.fullExpandWidth;
        this.content.height = this.fullExpandHeight;
        this.focus();

        this.fullExpandIcon.parentNode.removeChild(this.fullExpandIcon);
        this.wrapper.style.width = (this.content.width + borderOffset) +'px';
        this.repositionOutline(0);

        for (x in this.overlays) {
            this.wrapper.removeChild(this.overlays[x]);
        }
        this.createCustomOverlays();
        this.redoShowHide();
    } catch(e) {
        window.location.href = hs.expanders[this.key].content.src;
    }
};

ImgUTIL.prototype.redoShowHide = function() {
    var imgPos = {
        x: parseInt(this.wrapper.style.left) - 20,
        y: parseInt(this.wrapper.style.top) - 20,
        w: this.content.offsetWidth + 40,
        h: this.content.offsetHeight + 40 + 30
    };
    if (hs.hideSelects) this.showHideElements('SELECT', 'hidden', imgPos);
    if (hs.hideIframes) this.showHideElements('IFRAME', 'hidden', imgPos);

};

hs.addEventListener(document, 'mousedown', hs.mouseDownHandler);
hs.addEventListener(document, 'mouseup', hs.mouseUpHandler);
