// ================================================================
//                           CHEditor 5
// ================================================================

var activeImage = null,
    browser = null,
    button,
    debug = false,
    destinationObject = null,
    divHeight = [],
    divWidth = [],
    divXPositions = [],
    divYPositions = [],
    dragDropDiv,
    eventDiff_x = 0,
    eventDiff_y = 0,
    fileTypeRe = /^image\/(png|jpeg|gif|webp)$/i,
    geckoOffsetX_marker = -3,
    geckoOffsetY_marker = -1,
    imageCompleted = 0,
    imageCompletedList = [],
    imageListWrapper,
    imageResizeInput,
    imageResizeWidth = 0,
    insertionMarker,
    inputFileName = 'file',
    modifyImages = [],
    moveTimer = -1,
    oEditor = null,
    offsetX_marker = -3,
    offsetY_marker = -3,
    readyToMove = false,
    showThumbnailSize = { width: 120, height: 90 },
    tmpLeft = 0,
    tmpTop = 0,
    uploadImagePath = '',
    uploadMaxNumber = 12,
    uploadScript,
    useWebGL = false,
    supportImageOrientation;

if (ArrayBuffer && !ArrayBuffer.prototype.slice) {
    ArrayBuffer.prototype.slice = function (start, end) {
        var len = this.byteLength;
        start || isFinite(start) && 0 < start && start < len || (start = 0);
        end || (isFinite(end && start < end && end < len) || (end = len - 1));
        return new DataView(this, start, end).buffer;
    };
}

function GLScale(options) {
    if (!(this instanceof GLScale)) {
        return new GLScale(options);
    }
    this.precompile(options);
    return this.scale.bind(this);
}

GLScale.prototype.precompile = function (options) {
    var ctxOptions, vertex, fragment, resolutionLocation, positionLocation;

    this.canvas = document.createElement('canvas');
    this.canvas.width = options.width;
    this.canvas.height = options.height;

    ctxOptions = {preserveDrawingBuffer: true};
    this.gl = this.canvas.getContext('webgl', ctxOptions) || this.canvas.getContext('experimental-webgl', ctxOptions);

    if (!this.gl) {
        throw new Error('Could not initialize webgl context');
    }

    vertex = GLScale.compileShader(this.gl, GLScale.Hermite.vertex, this.gl.VERTEX_SHADER);
    fragment = GLScale.compileShader(this.gl, GLScale.Hermite.fragment, this.gl.FRAGMENT_SHADER);

    this.program = GLScale.createProgram(this.gl, vertex, fragment);
    this.gl.useProgram(this.program);

    this.gl.bindTexture(this.gl.TEXTURE_2D, this.gl.createTexture());

    this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_WRAP_S, this.gl.CLAMP_TO_EDGE);
    this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_WRAP_T, this.gl.CLAMP_TO_EDGE);
    this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_MIN_FILTER, this.gl.NEAREST);
    this.gl.texParameteri(this.gl.TEXTURE_2D, this.gl.TEXTURE_MAG_FILTER, this.gl.NEAREST);

    resolutionLocation = this.gl.getUniformLocation(this.program, 'u_resolution');
    this.gl.uniform2f(resolutionLocation, options.width, options.height);

    this.gl.bindBuffer(this.gl.ARRAY_BUFFER, this.gl.createBuffer());
    positionLocation = this.gl.getAttribLocation(this.program, 'a_position');
    this.gl.enableVertexAttribArray(positionLocation);
    this.gl.vertexAttribPointer(positionLocation, 2, this.gl.FLOAT, false, 0, 0);
};

GLScale.prototype.scale = function (image, cb) {
    var srcResolutionLocation;
    if (typeof image === 'string') {
        return this.loadImage(image, function (err, image) {
            if (!err) {
                return this.scale(image, cb);
            }
        });
    }

    this.gl.texImage2D(this.gl.TEXTURE_2D, 0, this.gl.RGBA, this.gl.RGBA, this.gl.UNSIGNED_BYTE, image);

    srcResolutionLocation = this.gl.getUniformLocation(this.program, 'u_srcResolution');
    this.gl.uniform2f(srcResolutionLocation, image.width, image.height);

    this.setRectangle(0, 0, image.width, image.height);
    this.gl.drawArrays(this.gl.TRIANGLES, 0, 6);
    this.gl.finish();

    if (cb) {
        cb(this.canvas);
    }
    return this;
};

GLScale.prototype.setRectangle = function (x, y, width, height) {
    var x1 = x,
        x2 = x + width,
        y1 = y,
        y2 = y + height;

    this.gl.bufferData(this.gl.ARRAY_BUFFER, new Float32Array([
        x1, y1,
        x2, y1,
        x1, y2,
        x1, y2,
        x2, y1,
        x2, y2
    ]), this.gl.STATIC_DRAW);
};

GLScale.loadImage = function (url, cb) {
    var image = new Image();
    image.onload = cb.bind(this, null, image);
    image.onerror = cb.bind(this);
    image.src = url;
    return this;
};
GLScale.prototype.loadImage = GLScale.loadImage;

GLScale.toBlob = (function toBlob() {
    var CanvasPrototype = window.HTMLCanvasElement.prototype;

    if (CanvasPrototype.toBlob) {
        return CanvasPrototype.toBlob;
    }

    return function (callback, type, quality) {
        var binStr = atob(this.toDataURL(type, quality).split(',')[1]),
            len = binStr.length,
            arr = new Uint8Array(len), i = 0;

        for (; i < len; i++) {
            arr[i] = binStr.charCodeAt(i);
        }
        callback(new Blob([arr], {type: type || 'image/png'}));
    };
})();

GLScale.compileShader = function (gl, shaderSource, shaderType) {
    var shader = gl.createShader(shaderType);
    
    gl.shaderSource(shader, shaderSource);
    gl.compileShader(shader);
    
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
        throw new Error('Could not compile shader: ' + gl.getShaderInfoLog(shader));
    }
    return shader;
};

GLScale.createProgram = function (gl, vertexShader, fragmentShader) {
    var program = gl.createProgram();

    gl.attachShader(program, vertexShader);
    gl.attachShader(program, fragmentShader);
    gl.linkProgram(program);
    
    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        throw new Error('Program failed to link: ' + gl.getProgramInfoLog (program));
    }
    return program;
};

function createInsertionMaker() {
    var wrapper = document.getElementById('insertionMarker'),
        topIco = new Image(),
        middleIco = new Image(),
        bottomIco = new Image();

    topIco.src = uploadImagePath + '/marker_top.gif';
    topIco.style.width = '6px';
    topIco.style.height = '1px';
    wrapper.appendChild(topIco);

    middleIco.src = uploadImagePath + '/marker_middle.gif';
    middleIco.style.height = '96px';
    middleIco.style.width = '6px';
    wrapper.appendChild(middleIco);

    bottomIco.src = uploadImagePath + '/marker_bottom.gif';
    bottomIco.style.width = '6px';
    bottomIco.style.height = '1px';
    wrapper.appendChild(bottomIco);
}

function popupClose() {
    oEditor.popupWinCancel();
}

function showContents() {
    var spacer = function (id) {
        var clear = document.createElement('div');
        clear.style.height = '0px';
        clear.style.width = '0px';
        clear.className = 'clear';
        clear.id = 'spacer' + id;
        return clear;
    },
    spacerNo = 1, i, imgBox, theImg, lastSpacer;

    for (i = 0; i < uploadMaxNumber; i++) {
        if (i > 0 && i % 2 === 0) {
            imageListWrapper.appendChild(spacer(spacerNo++));
        }

        imgBox = document.createElement('div');
        imgBox.id = 'imgBox' + i;
        imgBox.className = 'imageBox';
        theImg = document.createElement('div');
        theImg.id = 'img_' + i;
        theImg.className = 'imageBox_theImage';
        imgBox.appendChild(theImg);

        imageListWrapper.appendChild(imgBox);
        if (i === uploadMaxNumber - 1) {
            lastSpacer = spacer(spacerNo);
            lastSpacer.style.height = "7px";
            imageListWrapper.appendChild(lastSpacer);
        }
    }

    imageListWrapper.style.padding = '5px 7px 0px 5px';
    document.getElementById('imageInfoBox').style.height = '298px';
    document.getElementById('imageInfoBox').style.width = '130px';
}

function setImageCount() {
    imageCompleted++;
    document.getElementById('imageCount').innerHTML = imageCompleted.toString();
}

function getImageCount() {
    return imageCompleted;
}

function allowedMaxImage() {
    return uploadMaxNumber - getImageCount();
}

function getUploadedCount() {
    return imageListWrapper.getElementsByTagName('img').length;
}

function uploadedImageCount() {
    imageCompleted = getUploadedCount();
    document.getElementById('imageCount').innerHTML = imageCompleted.toString();
}

function getTopPos(inputObj) {
    // ----------------------------------------------------------------------------------
    var returnValue = inputObj.offsetTop;

    inputObj = inputObj.offsetParent;
    while (inputObj) {
        if (inputObj.tagName.toLowerCase() !== 'html') {
            returnValue += inputObj.offsetTop - inputObj.scrollTop;
            if (browser.msie) {
                returnValue += inputObj.clientTop;
            }
        }
        inputObj = inputObj.offsetParent;
    }
    return returnValue;
}

function getLeftPos(inputObj) {
    // ----------------------------------------------------------------------------------
    var returnValue = inputObj.offsetLeft;

    inputObj = inputObj.offsetParent;
    while (inputObj) {
        if (inputObj.id !== 'imageListWrapper') {
            returnValue += inputObj.offsetLeft;
        }
        inputObj = inputObj.offsetParent;
    }
    return returnValue;
}

function getDivCoordinates() {
    // ----------------------------------------------------------------------------------
    var imgBox = imageListWrapper.getElementsByTagName('DIV'),
        i = 0;

    for (; i < imgBox.length; i++) {
        if ((imgBox[i].className === 'imageBox' || imgBox[i].className === 'imageBoxHighlighted') && imgBox[i].id) {
            divXPositions[imgBox[i].id] = getLeftPos(imgBox[i]);
            divYPositions[imgBox[i].id] = getTopPos(imgBox[i]);
            divWidth[imgBox[i].id]  = imgBox[i].offsetWidth;
            divHeight[imgBox[i].id] = imgBox[i].offsetHeight;
        }
    }
}

function reOrder() {
    // ----------------------------------------------------------------------------------
    var imgBox = imageListWrapper.getElementsByTagName('div'),
        imgNum = 0, i, spacer, breakline = [];

    for (i = 0; i < imgBox.length; i++) {
        if (imgBox[i].id.indexOf('imgBox') === -1) {
            continue;
        }

        imgBox[i].className = 'imageBox';
        imgBox[i].firstChild.className = 'imageBox_theImage';

        if (imgNum > 0 && imgNum % 4 === 0) {
            breakline.push(imgBox[i].id);
        }

        imgNum++;
    }

    for (i = 0; i < breakline.length; i++) {
        spacer = document.getElementById('spacer' + (i + 1));
        if (i + 1 === breakline.length) {
            imageListWrapper.appendChild(spacer);
        } else {
            imageListWrapper.insertBefore(spacer, document.getElementById(breakline[i]));
        }
    }
}

function showDelete() {
    var self = this, btn;

    if (readyToMove) {
        return;
    }

    getDivCoordinates();
    self.className = 'imageBox_theImage_over';
    btn = document.getElementById('removeImageButton');
    btn.style.left = showThumbnailSize.width - parseInt(btn.style.width, 10) - 1 + 'px';
    btn.style.top = '-1px';

    self.appendChild(btn);
    btn.style.display = 'block';

    btn.onmouseover = function (ev) {
        oEditor.stopEvent(ev);
        this.style.display = 'block';
        this.className = 'removeButton_over';
        self.className = 'imageBox_theImage_over';
    };
    btn.onmouseout = function () {
        this.className = 'removeButton';
    };
    btn.onmousedown = function (ev) {
        var images = self.getElementsByTagName('img'), i, moveobj, target;

        for (i = 0; i < images.length; i++) {
            self.removeChild(images[i]);
        }

        self.removeChild(self.firstChild);
        self.className = 'imageBox_theImage';

        if (self.parentNode.nextSibling && self.parentNode.nextSibling.id) {
            moveobj = self.parentNode.nextSibling;
            target = self.parentNode;

            while (moveobj !== null) {
                if (moveobj.firstChild && !moveobj.firstChild.firstChild) {
                    break;
                }
                if (/^spacer/.test(moveobj.id)) {
                    moveobj = moveobj.nextSibling;
                    continue;
                }
                imageListWrapper.insertBefore(moveobj, target);
                moveobj = target.nextSibling;
            }
        }

        reOrder();
        uploadedImageCount();
        this.style.display = 'none';
        document.body.appendChild(ev.target);
        oEditor.removeEvent(self, 'mouseover', showDelete);
        self.removeAttribute('title');
    };
}

function hideDelete() {
    // ----------------------------------------------------------------------------------
    document.getElementById('removeImageButton').style.display = 'none';
}

function startUpload(list) {
    // ----------------------------------------------------------------------------------
    var el = imageListWrapper.getElementsByTagName('div'), i, imgBox,
        count = 0, len = list.length;

    for (i = 0; i < el.length; i++) {
        imgBox = el[i];
        if (imgBox.className !== 'imageBox_theImage') {
            continue;
        }
        if (count === len) {
            break;
        }
        if (!imgBox.firstChild || imgBox.firstChild.tagName.toLowerCase()  !== 'img') {
            imgBox.style.backgroundImage = "url('" + uploadImagePath + "/loader.gif')";
            list[count++].boxElem = imgBox;
        }
    }
}

function fileFilterError(file) {
    alert("선택하신 '" + file + "' 파일은 전송할 수 없습니다.\n" +
       "gif, png, jpg 사진 파일만 전송할 수 있습니다.");
}

function imgComplete(img, imgSize, boxId) {
    var resizeW, resizeH, M, elem;
    img.setAttribute("border", '0');

    if (imgSize.width > showThumbnailSize.width || imgSize.height > showThumbnailSize.height) {
        if (imgSize.width > imgSize.height) {
            resizeW = imgSize.width > showThumbnailSize.width ? showThumbnailSize.width : imgSize.width;
            resizeH = Math.round(imgSize.height * resizeW / imgSize.width);
        } else {
            resizeH = imgSize.height > showThumbnailSize.height ? showThumbnailSize.height : imgSize.height;
            resizeW = Math.round(imgSize.width * resizeH / imgSize.height);
        }

        if (resizeH > showThumbnailSize.height) {
            resizeH = imgSize.height > showThumbnailSize.height ? showThumbnailSize.height : imgSize.height;
            resizeW = Math.round(imgSize.width * resizeH / imgSize.height);
        }

    } else {
        resizeW = imgSize.width;
        resizeH = imgSize.height;
    }

    img.style.width  = resizeW - 2 + 'px';
    img.style.height = resizeH - 2 + 'px';
    img.style.margin = "1px";

    if (resizeW < showThumbnailSize.width) {
        M = showThumbnailSize.width - resizeW;
        img.style.marginLeft = Math.round(M / 2) + 'px';
    }

    if (resizeH < showThumbnailSize.height) {
        M = showThumbnailSize.height - resizeH;
        img.style.marginTop = Math.round(M / 2) + 'px';
    }

    elem = document.getElementById(boxId);
    boxId = boxId.replace(/img_/, '');

    if (boxId % 12 === 0) {
        imageListWrapper.scrollTop = elem.offsetTop - 6;
    }

    elem.style.backgroundImage = "url('" + uploadImagePath + "/dot.gif')";
    setElemTitle(elem, imgSize.width, imgSize.height);
    oEditor.addEvent(elem, 'mouseover', showDelete);
    elem.onmouseout = function () {
        this.className = 'imageBox_theImage';
        hideDelete();
    };

    setImageCount();
}

function setElemTitle(elem, width, height) {
    elem.setAttribute('title', 'Width: ' + width + 'px, Height: ' + height + 'px');
}

function showUploadWindow() {
    // ----------------------------------------------------------------------------------
    var uploadWindow  = document.getElementById("uploadWindow"),
        uploadWindowWidth = 700,
        winWidth, el, i, j, imgBox, img;

    if (!oEditor.undefined(window.innerWidth)) {
        winWidth  = window.innerWidth;
    } else if (document.documentElement &&
        !oEditor.undefined(document.documentElement.clientWidth) &&
             document.documentElement.clientWidth !== 0) {
        winWidth = document.documentElement.clientWidth;
    } else if (document.body && !oEditor.undefined(document.body.clientWidth)) {
        winWidth = document.body.clientWidth;
    } else {
        alert('현재 브라우저를 지원하지 않습니다.');
        return;
    }

    uploadWindow.style.left = winWidth / 2 - uploadWindowWidth / 2 + 'px';
    uploadWindow.style.display = "block";
    uploadWindow.style.width = uploadWindowWidth + 'px';

    if (modifyImages.length > 0) {
        el = imageListWrapper.getElementsByTagName('div');
        for (i = 0; i < modifyImages.length; i++) {
            if (i > 7) {
                break;
            }

            for (j = 0; j < el.length; j++) {
                imgBox = el[j];
                if (imgBox.className !== 'imageBox_theImage') {
                    continue;
                }
                if (imgBox.firstChild && imgBox.firstChild.src === modifyImages[i]) {
                    break;
                }
                if (imgBox.firstChild === null) {
                    img = new Image();
                    img.src = modifyImages[i];
                    img.border = 0;
                    img.alt = '';
                    img.style.width = '120px';
                    img.style.height = '90px';
                    imgBox.appendChild(img);
                    break;
                }
            }
        }
    }
}

function removeImages() {
    var images = [], i, j, theImage, img, remove;
    document.body.appendChild(document.getElementById('removeImageButton'));

    for (i = 0; i < uploadMaxNumber; i++) {
        theImage = document.getElementById('img_' + i);
        if (theImage.hasChildNodes() && theImage.firstChild.tagName.toLowerCase() === 'img') {
            images.push(theImage);
        }
    }

    for (i = 0; i < images.length; i++) {
        img = images[i];
        if (img.firstChild !== null) {
            oEditor.removeEvent(img, 'mouseover', showDelete);
            remove = img.getElementsByTagName('img');

            for (j = 0; j < remove.length; j++) {
                img.removeChild(remove[j]);
            }

            img.parentNode.className = 'imageBox';
            img.removeAttribute('title');
            oEditor.removeEvent(img, 'mouseover', showDelete);
        }
    }
    uploadedImageCount();
    imageCompletedList = [];
}

function removeImage() {
    // ----------------------------------------------------------------------------------
    var i, theImage, found = false;

    for (i = 0; i < uploadMaxNumber; i++) {
        theImage = document.getElementById('img_' + i);
        if (theImage.hasChildNodes() && theImage.firstChild.tagName.toLowerCase() === 'img') {
            found = true;
            break;
        }
    }

    if (found) {
        if (!confirm('추가하신 사진이 있습니다. 사진 넣기를 취소하시겠습니까?')) {
            return false;
        }
        removeImages();
    }

    return true;
}

function closeWindow() {
    // ----------------------------------------------------------------------------------
    if (removeImage()) {
        popupClose();
    }
}

function cancelEvent() {
    // ----------------------------------------------------------------------------------
    return false;
}

function startMoveTimer() {
    // ----------------------------------------------------------------------------------
    var subElements, newDiv;

    if (moveTimer >= 0 && moveTimer < 10) {
        moveTimer++;
        setTimeout(startMoveTimer(), 8);
    }

    if (moveTimer === 5) {
        getDivCoordinates();
        subElements = dragDropDiv.getElementsByTagName('div');
        if (subElements.length > 0) {
            dragDropDiv.removeChild(subElements[0]);
        }

        dragDropDiv.style.display = 'block';
        newDiv = activeImage.cloneNode(true);
        newDiv.className = 'imageBox';
        newDiv.style.opacity = 0.5;

        newDiv.id = '';
        newDiv.style.padding = '2px';
        dragDropDiv.appendChild(newDiv);

        dragDropDiv.style.top = tmpTop + 'px';
        dragDropDiv.style.left = tmpLeft + 'px';
    }

    return false;
}

function getMouseButtn(e) {
    var code;
    e = e || window.event;
    code = e.button;

    if (code) {
        if (browser.msie && browser.version < 9) {
            code = code === 1 ? 0 : code === 4 ? 1 : code;
        }
    }

    return code;
}

function selectImage(e) {
    // ----------------------------------------------------------------------------------
    var el = this.parentNode.firstChild.firstChild, obj;

    if (!el) {
        return;
    }

    e = e || window.event;
    if (getMouseButtn(e) === 2) {
        return;
    }

    obj = this.parentNode;
    hideDelete();

    obj.className = 'imageBoxHighlighted';
    activeImage = obj;
    readyToMove = true;
    moveTimer = 0;

    tmpLeft = e.clientX + Math.max(document.body.scrollLeft, document.documentElement.scrollLeft);
    tmpTop = e.clientY + Math.max(document.body.scrollTop, document.documentElement.scrollTop);

    startMoveTimer();
    return false;
}

function dragDropEnd() {
    // ----------------------------------------------------------------------------------
    var parentObj, chkObj, turn = false;

    readyToMove = false;
    moveTimer = -1;
    dragDropDiv.style.display = 'none';
    insertionMarker.style.display = 'none';

    if (!activeImage) {
        return;
    }

    if (destinationObject && destinationObject !== activeImage) {
        parentObj = destinationObject.parentNode;
        chkObj = destinationObject.previousSibling;
        turn = false;

        if (chkObj === null) {
            chkObj = imageListWrapper.firstChild;
            turn = true;
        }

        if (chkObj.id.indexOf('spacer') !== -1) {
            chkObj = chkObj.previousSibling;
        }

        if (chkObj.firstChild.firstChild === null) {
            reOrder();
            return;
        }

        if (chkObj && chkObj.id !== null) {
            while (chkObj) {
                if (chkObj.firstChild.firstChild !== null) {
                    break;
                }
                chkObj = chkObj.previousSibling;
            }
            destinationObject = turn ? chkObj : chkObj.nextSibling;
        }

        parentObj.insertBefore(activeImage, destinationObject);
        reOrder();

        activeImage = null;
        destinationObject = null;
        getDivCoordinates();

        return false;
    }

    activeImage.className = "imageBox";
    return true;
}

function dragDropMove(e) {
    // ----------------------------------------------------------------------------------
    var elementFound = false, prop, offsetX, offsetY, leftPos, topPos;

    if (moveTimer === -1 || !readyToMove) {
        return;
    }

    e = e || window.event;

    leftPos = e.clientX + document.documentElement.scrollLeft - eventDiff_x;
    topPos = e.clientY + document.documentElement.scrollTop - eventDiff_y;

    dragDropDiv.style.top = topPos + 'px';
    dragDropDiv.style.left = leftPos + 'px';

    leftPos = leftPos + eventDiff_x;
    topPos = topPos + eventDiff_y;

    if (getMouseButtn(e) !== 0) {
        dragDropEnd();
    }

    for (prop in divXPositions) {
        if (!divXPositions.hasOwnProperty(prop) || divXPositions[prop].className === 'clear') {
            continue;
        }

        if (divXPositions[prop] < leftPos &&
         divXPositions[prop] + divWidth[prop] * 0.7 > leftPos &&
          divYPositions[prop] < topPos &&
         divYPositions[prop] + divWidth[prop] > topPos) {
            if (browser.msie) {
                offsetX = offsetX_marker;
                offsetY = offsetY_marker;
            } else {
                offsetX = geckoOffsetX_marker;
                offsetY = geckoOffsetY_marker;
            }

            insertionMarker.style.top = divYPositions[prop] + offsetY + 'px';
            insertionMarker.style.left = divXPositions[prop] + offsetX + 'px';
            insertionMarker.style.display = 'block';
            destinationObject = document.getElementById(prop);
            elementFound = true;
            break;
        }
    }

    if (!elementFound) {
        insertionMarker.style.display = 'none';
        destinationObject = false;
    }

    return false;
}

function initGallery() {
    // ----------------------------------------------------------------------------------
    var imgBox = imageListWrapper.getElementsByTagName('div'),
        i;

    for (i = 0; i < imgBox.length; i++) {
        if (imgBox[i].className === 'imageBox_theImage') {
            imgBox[i].onmousedown = selectImage;
        }
    }

    document.body.onselectstart = cancelEvent;
    document.body.ondragstart = cancelEvent;
    document.body.onmouseup = dragDropEnd;
    document.body.onmousemove = dragDropMove;

    dragDropDiv = document.getElementById('dragDropContent');
    insertionMarker = document.getElementById('insertionMarker');
    getDivCoordinates();
}

function doSubmit() {
    // ----------------------------------------------------------------------------------
    var el = imageListWrapper.getElementsByTagName('div'),
        imageArray = [],
        num = 0,
        elem = document.getElementById('id_alignment').elements,
        imgParagraph = false,
        useSpacer = false,
        imgAlign = 'top', i, imgBox, input;

    for (i = 0; i < elem.length; i++) {
        input = elem[i];
        switch (input.name) {
            case "alignment" :
                if (input.checked) {
                    imgAlign = input.value;
                }
                break;
            case "para" :
                imgParagraph = input.checked;
                break;
            case "use_spacer" :
                useSpacer = input.checked;
                break;
        }
    }

    for (i = 0; i < el.length; i++) {
        imgBox = el[i];
        if (imgBox.className !== "imageBox_theImage") {
            continue;
        }

        if (imgBox.firstChild !== null) {
            imageArray[num] = imageCompletedList[imgBox.id];

            if (imgAlign === "break") {
                imageArray[num].alt = "break";
            } else {
                imageArray[num].alt = '';
                imageArray[num].align = imgAlign;
            }

            num++;
        }
    }

    if (imageArray.length > 0) {
        oEditor.doInsertImage(imageArray, imgParagraph, useSpacer);
    }
    oEditor.popupWinClose();
}

function selectedFiles(evt) {
    var upload = new DoUpload(),
        files = evt.target.files || [];

    oEditor.stopEvent(evt);
    if (files) {
        upload.select(files);
    }
}

function DoUpload() {
    this.list = [];
    this.reader = new FileReader();
    this.URL = window.URL || window.webkitURL;

    this.reader.onprogress = null;
    this.reader.onloadstart = null;
    this.reader.onabort = null;
    this.reader.onerror = null;

    this.MyBlob = (function () {
        var key, blob, self = this;
        function MYBLOB(blob) {
            var url = null;
            this.blob = blob;
            blob = null;

            this.getURL = function () {
                if (url) {
                    return url;
                }
                url = self.URL.createObjectURL(this.blob);
                return url;
            };
            this.dispose = function () {
                if (url) {
                    url = self.URL.revokeObjectURL(url);
                }
                if (typeof this.blob.msClose !== 'undefined') {
                    this.blob.msClose();
                }
                this.blob = null;
                if (debug) {
                    console.log("Blob Data Clear");
                }
            };
        }

        blob = new Blob();
        for (key in blob) {
            if (blob.hasOwnProperty(key)) {
                (function (key) {
                    Object.defineProperty(MYBLOB.prototype,
                        key,
                        {
                            enumerable: true,
                            configurable: true,
                            get: function () {
                                return this.blob[key];
                            }
                        }
                    );
                }(key));
            }
        }

        key = undefined;
        return MYBLOB;
    }());

    return this;
}

DoUpload.prototype = {
    select: function (files) {
        var self = this,
            num = files.length,
            i = 0,
            file = null;

        if (num > allowedMaxImage()) {
            num = allowedMaxImage();
        }

        for (; i < num; i++) {
            file = files[i];

            if (!file.type.match(fileTypeRe)) {
                fileFilterError(file.name);
                continue;
            }
            this.list.push(file);
        }

        if (this.list.length < 1) {
            return;
        }

        this.reader.addEventListener("error", function (evt) {
            self.onReadDataErrorHandler(evt);
        }, false);

        this.reader.onloadend = function (evt) {
            self.dataLoadHandler(evt);
        };

        setResizeWidth();
        startUpload(this.list);

        this.load();
    },

    getDateTime : function () {
        var date = new Date(),
            year = date.getFullYear(),
            month = date.getMonth() + 1,
            day = date.getDate(),
            hours = date.getHours(),
            minutes = date.getMinutes(),
            seconds = date.getSeconds();

        return String(10000 * year + 100 * month + day +
            ('0' + hours).slice(-2) + ('0' + minutes).slice(-2) + ('0' + seconds).slice(-2));
    },

    makeFilename : function (type) {
        var chars = "abcdefghiklmnopqrstuvwxyz",
            len = 8, clen = chars.length, rData = '', i, rnum;

        for (i = 0; i < len; i++) {
            rnum = Math.floor(Math.random() * clen);
            rData += chars.substring(rnum, rnum + 1);
        }

        if (type !== '') {
            rData += type.toLowerCase();
        }

        return this.getDateTime() + '_' + rData;
    },

    getOrientation : function (data) {
        var view = new DataView(data),
            length = view.byteLength,
            offset = 2,
            marker, little, tags, i;

        if (view.getUint16(0, false) !== 0xffd8) {
            return -2;
        }

        while (offset < length) {
            marker = view.getUint16(offset, false);
            offset += 2;

            if (marker === 0xffe1) {
                if (view.getUint32(offset += 2, false) !== 0x45786966) {
                    return -1;
                }

                little = view.getUint16(offset += 6, false) === 0x4949;
                offset += view.getUint32(offset + 4, little);
                tags = view.getUint16(offset, little);
                offset += 2;

                for (i = 0; i < tags; i++) {
                    if (view.getUint16(offset + i * 12, little) === 0x0112) {
                        return view.getUint16(offset + i * 12 + 8, little);
                    }
                }
            } else if ((marker & 0xff00) !== 0xff00) {
                break;
            } else {
                offset += view.getUint16(offset, false);
            }
        }

        return -1;
    },

    NewBlob : function (data, datatype) {
        var blob = null, blobb;
        try {
            blob = new Blob([data], {type: datatype});
        } catch (e) {
            window.BlobBuilder = window.BlobBuilder
                || window.WebKitBlobBuilder
                || window.MozBlobBuilder
                || window.MSBlobBuilder;

            if (e.name === 'TypeError' && window.BlobBuilder) {
                blobb = new BlobBuilder();
                blobb.append(data);
                blob = blobb.getBlob(datatype);
                console.log("TypeError");
            } else if (e.name === "InvalidStateError") {
                console.log("InvalidStateError");
            } else {
                console.log("Error");
            }
        }
        return blob;
    },

    imageResize : function (image, filetype, resize_w, orientation, addWaterMark) {
        var canvas = document.createElement("canvas"),
            ctx = canvas.getContext("2d"),
            source_w = image.width,
            source_h = image.height,
            resize_h, ratio_w, ratio_h, ratio_w_half, ratio_h_half,
            source_img, resize_img, source_data, resize_data, j, i,
            x2, weight, weights, weights_alpha, gx_a, gx_b, gx_g, gx_r, 
            center_x, center_y, x_start, x_stop,
            y_start, y_stop, y, dy, part_w, x, dx, w, pos_x, gl, imageData = null;

        // 카메라 로테이션 보정
        if (orientation > 0 && !supportImageOrientation) {
            if ([5,6,7,8].indexOf(orientation) > -1) {
                canvas.width = source_h;
                canvas.height = source_w;
            } else {
                canvas.width = source_w;
                canvas.height = source_h;
            }

            switch (orientation) {
                case 2 : ctx.transform(-1, 0, 0, 1, source_w, 0); break;
                case 3 : ctx.transform(-1, 0, 0, -1, source_w, source_h); break;
                case 4 : ctx.transform(1, 0, 0, -1, 0, source_h); break;
                case 5 : ctx.transform(0, 1, 1, 0, 0, 0); break;
                case 6 : ctx.transform(0, 1, -1, 0, source_h, 0); break;
                case 7 : ctx.transform(0, -1, -1, 0, source_h, source_w); break;
                case 8 : ctx.transform(0, -1, 1, 0, 0, source_w); break;
                default: break;
            }

            ctx.drawImage(image, 0, 0, source_w, source_h);
            imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            ctx.restore();
            source_w = canvas.width;
            source_h = canvas.height;
        }

        if (source_w > resize_w) {
            resize_h = Math.ceil(resize_w / source_w * source_h);

            try {
                gl = GLScale({
                    width: resize_w, height: resize_h
                });
                gl(imageData || image, function (gl_canvas) {
                    canvas = gl_canvas;
                });
            } catch (ignore) {
                canvas.width = source_w;
                canvas.height = source_h;

                if (imageData) {
                    ctx.putImageData(imageData, 0, 0);
                } else {
                    ctx.drawImage(image, 0, 0);
                }

                ratio_w = source_w / resize_w;
                ratio_h = source_h / resize_h;
                ratio_w_half = Math.ceil(ratio_w / 2);
                ratio_h_half = Math.ceil(ratio_h / 2);
                source_img = ctx.getImageData(0, 0, source_w, source_h);
                resize_img = ctx.createImageData(resize_w, resize_h);
                source_data = source_img.data;
                resize_data = resize_img.data;

                for (j = 0; j < resize_h; j++) {
                    for (i = 0; i < resize_w; i++) {
                        x2 = (i + j * resize_w) * 4;
                        weight = weights = weights_alpha = 0;
                        gx_r = gx_g = gx_b = gx_a = 0;
                        center_y = (j + 0.5) * ratio_h;

                        x_start = Math.floor(i * ratio_w);
                        x_stop = Math.ceil((i + 1) * ratio_w);
                        y_start = Math.floor(j * ratio_h);
                        y_stop = Math.ceil((j + 1) * ratio_h);

                        x_stop = Math.min(x_stop, source_w);
                        y_stop = Math.min(y_stop, source_h);

                        for (y = y_start; y < y_stop; y++) {
                            dy = Math.abs(center_y - (y + 0.5)) / ratio_h_half;
                            center_x = (i + 0.5) * ratio_w;
                            part_w = dy * dy;

                            for (x = x_start; x < x_stop; x++) {
                                dx = Math.abs(center_x - (x + 0.5)) / ratio_w_half;
                                w = Math.sqrt(part_w + dx * dx);
                                if (w >= 1) {
                                    continue;
                                }
                                // Hermite 필터
                                weight = 2 * w * w * w - 3 * w * w + 1;
                                pos_x = 4 * (x + y * source_w);
                                // 알파 채널
                                gx_a += weight * source_data[pos_x + 3];
                                weights_alpha += weight;

                                if (source_data[pos_x + 3] < 255) {
                                    weight = weight * source_data[pos_x + 3] / 250;
                                }

                                gx_r += weight * source_data[pos_x];
                                gx_g += weight * source_data[pos_x + 1];
                                gx_b += weight * source_data[pos_x + 2];
                                weights += weight;
                            }
                        }
                        resize_data[x2] = gx_r / weights;
                        resize_data[x2 + 1] = gx_g / weights;
                        resize_data[x2 + 2] = gx_b / weights;
                        resize_data[x2 + 3] = gx_a / weights_alpha;
                    }
                }
                canvas.width = resize_w;
                canvas.height = resize_h;
                ctx.putImageData(resize_img, 0, 0);
            }
        } else {
            canvas.width = source_w;
            canvas.height = source_h;
            ctx.drawImage(image, 0, 0);
        }

        if (this.reader.watermark && addWaterMark) {
            ctx.globalAlpha = oEditor.config.imgWaterMarkAlpha;
            ctx.drawImage(this.reader.watermark,
                canvas.width - this.reader.watermark.width, canvas.height - this.reader.watermark.height);
        }
        
        return canvas.toDataURL(filetype, oEditor.config.imgJpegQuality);
    },

    canvasToBlob : function (bitmapData, mimetype) {
        var i = 0,
            intArray = [],
            len = bitmapData.length,
            raw = atob(bitmapData.split(',')[1]);

        for (; i < len; i++) {
            intArray.push(raw.charCodeAt(i));
        }
        return new Blob([new Uint8Array(intArray)], {type: mimetype});
    },

    makeThumbnail : function (image, type, name, orientation) {
        var width,
            xhr = new XMLHttpRequest(),
            data = new FormData(),
            bitmapData, file;

        xhr.open('POST', uploadScript, true);
        width = oEditor.config.thumbnailWidth;

        bitmapData = this.imageResize(image, type, width, orientation);
        file = this.canvasToBlob(bitmapData, type);
        data.append(inputFileName, file, 'thumb_' + name); // RFC Level 2

        xhr.addEventListener("loadend", function () {
            // loadend
        }, false);

        xhr.addEventListener("error", function () {
            alert("Thumbnail 파일 전송 중 오류:");
        }, false);

        xhr.send(data);
    },

    dataLoadHandler: function (evt) {
        var self = this,
            filename = evt.target.file.name,
            filetype = evt.target.file.type,
            file = evt.target.file,
            blob, image, orientation = 1, slice = 64 * 1024;

        if (slice > file.size - 1) {
            slice = file.size;
        }

        if (evt.target.readyState === FileReader.DONE) {
            blob = new self.MyBlob(self.NewBlob(evt.target.result, filetype));
            orientation = self.getOrientation(evt.target.result.slice(0, slice));

            image = new Image();
            image.onload = function () {
                var bitmapData = null,
                    data = new FormData(),
                    fileFormat,
                    imgBox = file.boxElem,
                    imgInfo = {},
                    randomName,
                    xhr = new XMLHttpRequest();

                xhr.open('POST', uploadScript, true);

                if (imageResizeWidth > 0) {
                    bitmapData = self.imageResize(this, filetype, imageResizeWidth, orientation, true);
                    file = self.canvasToBlob(bitmapData, filetype);
                }

                fileFormat = filename.substring(filename.lastIndexOf('.'));
                randomName = self.makeFilename(fileFormat);

                data.append('origname', filename);
                data.append(inputFileName, file, randomName); // RFC Level 2

                if (debug) {
                    console.log('Successed: ' + filename);
                }

                xhr.addEventListener("error", function (evt) {
                    alert("파일 전송 중 오류: " + evt.toString());
                }, false);

                xhr.addEventListener("loadend", function onLoadendImageHandler(xhrevt) {
                    if (xhrevt.target.readyState === xhrevt.target.DONE) {
                        if (oEditor.config.makeThumbnail) {
                            self.makeThumbnail(image, filetype, randomName, orientation, false);
                        }
                    }
                    image.src = '';
                    image = null;
                }, false);

                xhr.addEventListener("load", function (xhrevt) {
                    var jsonText, jsonData, img, onLoadHandler;
                    data = null;

                    if (xhrevt.target.status === 200) {
                        jsonText = decodeURI(oEditor.trimSpace(this.responseText));
                        jsonText = jsonText.replace(/\+/g, ' ').replace(/\\/g, '\\\\');
                        jsonData = JSON.parse(jsonText);

                        onLoadHandler = function () {
                            imgInfo = {
                                fileName: jsonData.fileName,
                                filePath: jsonData.filePath,
                                fileSize: jsonData.fileSize,
                                fileUrl: jsonData.fileUrl,
                                origName: filename,
                                origSize: file.size,
                                height: img.height,
                                width: img.width
                            };
                            imageCompletedList[imgBox.id] = imgInfo;
                            imgComplete(this, imgInfo, imgBox.id);
                            imgBox.appendChild(img);

                            if (debug) {
                                console.log('Image URL: ' + img.src + ', size:' + file.size);
                            }

                            setTimeout(function () {
                                self.load();
                            }, 100);

                            if (debug) {
                                console.log('Uploaded');
                            }
                        };
                        img = new Image();
                        img.onload = onLoadHandler;
                        img.src = decodeURIComponent(jsonData.fileUrl);
                    } else {
                        alert("HTTP 오류: " + xhr.status);
                    }
                }, false);

                blob.dispose();
                blob = null;
                xhr.send(data);
            };

            image.src = blob.getURL();
        }
    },

    onReadDataErrorHandler: function (evt) {
        var status = '';
        switch (evt.target.error.code) {
            case evt.target.error.NOT_FOUND_ERR:
                status = "파일을 찾을 수 없습니다.";
                break;
            case evt.target.error.NOT_READABLE_ERR:
                status = "파일을 읽을 수 없습니다.";
                break;
            case evt.target.error.ABORT_ERR:
                status = "파일 읽기가 중지되었습니다.";
                break;
            case evt.target.error.SECURITY_ERR:
                status = "파일이 잠겨 있습니다.";
                break;
            case evt.target.error.ENCODING_ERR:
                status = "data:// URL의 파일 인코딩 길이가 너무 깁니다.";
                break;
            default:
                status = "파일 읽기 오류: " + evt.target.error.code;
        }
        this.removeEventListener('error', this.onReadDataErrorHandler);
        alert("'" + evt.target.filename + "' " + status);
    },

    load: function () {
        var file = this.list.shift(), self = this, watermark = null;

        if (file) {
            if (debug) {
                console.log('File ' + this.index + ', Name: ' + file.name + ', Size: ' + file.size);
            }
            this.reader.file = file;
            this.reader.watermark = null;

            if (oEditor.config.imgWaterMarkUrl !== '' && oEditor.config.imgWaterMarkUrl !== null) {
                watermark = new Image();
                watermark.onerror = function () {
                    alert('워터마크 이미지를 읽을 수 없습니다. (' + oEditor.config.imgWaterMarkUrl + ')');
                    self.reader.readAsArrayBuffer(file);
                };
                watermark.onload = function () {
                    self.reader.watermark = this;
                    self.reader.readAsArrayBuffer(file);
                };
                watermark.src = oEditor.config.imgWaterMarkUrl;
            } else {
                this.reader.readAsArrayBuffer(file);
            }
        } else {
            this.clear();
        }
    },

    clear: function () {
        var inputFile = document.getElementById('inputImageUpload'),
            theForm = document.createElement('form'),
            fileSelectButton = document.getElementById('fileSelectButton');

        this.list = [];

        theForm.appendChild(inputFile);
        theForm.reset();
        fileSelectButton.parentNode.insertBefore(inputFile, fileSelectButton);
        fileSelectButton.style.marginLeft = '-1px';
    }
};

function fileSelectDrop(evt) {
    var files,
        upload = new DoUpload();

    oEditor.stopEvent(evt);
    this.className = "imageListWrapperHtml5";

    files = evt.dataTransfer.files;
    upload.select(files);
}

function dragOver(ev) {
    oEditor.stopEvent(ev);
    this.className = "dragOver";
}

function dragOut(ev) {
    oEditor.stopEvent(ev);
    this.className = "imageListWrapperHtml5";
}

function setResizeWidth() {
    var value = oEditor.trimSpace(imageResizeInput.value);
    if (value) {
        value = Math.ceil(parseInt(value, 10));
        if (!isNaN(value) && value < oEditor.config.imgMaxWidth) {
            imageResizeWidth = value;
        } else {
            imageResizeInput.value = '';
            imageResizeWidth = oEditor.config.imgMaxWidth;
            imageResizeInput.setAttribute('placeholder', oEditor.config.imgMaxWidth.toString());
        }
    }
}

function init(dialog) {
    var dlg, i, elem, input, select, value, name, xhr_f, xhr_v, tmpcanvas, glicon, testImg;

    oEditor = this;
    oEditor.dialog = dialog;
    dlg = new Dialog(oEditor);
    browser = oEditor.getBrowser();

    uploadImagePath = oEditor.config.iconPath + 'imageUpload';
    uploadMaxNumber = oEditor.config.imgUploadNumber;
    uploadScript = oEditor.config.editorPath + 'imageUpload/upload.php';
    imageListWrapper = document.getElementById("imageListWrapper");
    imageResizeInput = document.getElementById('idResizeWidth');
    select = document.getElementById('idResizeSelectBox');

    if (oEditor.config.imgMaxWidth > 0) {
        imageResizeWidth = oEditor.config.imgMaxWidth;
        for (i = 0; i < oEditor.config.imgResizeValue.length; i++) {
            name = value = oEditor.config.imgResizeValue[i];
            if (value > oEditor.config.imgMaxWidth) {
                continue;
            }
            if (value === -1) {
                name = '<입력>';
            }
            select.options[select.options.length] = new Option(name, value, false, value === oEditor.config.imgResizeSelected);
        }
        select.onchange = function () {
            if (this.value < 0) {
                document.getElementById('idUserInputWrapper').style.display = '';
                imageResizeInput.value = '';
                imageResizeWidth = oEditor.config.imgMaxWidth;
                imageResizeInput.focus();
            } else {
                document.getElementById('idUserInputWrapper').style.display = 'none';
                imageResizeWidth = this.value;
            }
        };
        imageResizeInput.setAttribute('placeholder', imageResizeWidth.toString());
        imageResizeWidth = select.value;
    } else {
        select.options[0] = new Option('원본', 0);
        select.setAttribute('disabled', 'disabled');
        imageResizeWidth = 0;
    }

    document.getElementById("maxImageNum").appendChild(document.createTextNode(uploadMaxNumber.toString()));

    button = [
        { alt: "", img: 'submit.gif', cmd: doSubmit, hspace: 2 },
        { alt: "", img: 'cancel.gif', cmd: closeWindow, hspace: 2 }
    ];

    dlg.setDialogHeight(340);
    dlg.showButton(button);
    showContents();
    initGallery();
    showUploadWindow();
    createInsertionMaker();

    oEditor.addEvent(imageListWrapper, 'dragover', dragOver);
    oEditor.addEvent(imageListWrapper, 'dragleave', dragOut);
    oEditor.addEvent(imageListWrapper, 'drop', fileSelectDrop);

    elem = document.getElementById('id_alignment').elements;

    for (i = 0; i < elem.length; i++) {
        if (elem[i].name === "alignment" && elem[i].value === oEditor.config.imgDefaultAlign) {
            elem[i].checked = "checked";
            break;
        }
    }

    tmpcanvas = document.createElement('canvas');
    if (tmpcanvas.getContext('webgl', {preserveDrawingBuffer: true}) ||
        tmpcanvas.getContext('experimental-webgl', {preserveDrawingBuffer: true}))
    {
        useWebGL = true;
        GLScale.Hermite = { vertex: '', fragment: '' };
        xhr_v = new XMLHttpRequest();
        xhr_f = new XMLHttpRequest();
        xhr_f.open('POST', 'js/fragment-shader.glsl', true);
        xhr_f.addEventListener("load", function (evt) {
            if (evt.target.status === 200) {
                GLScale.Hermite.fragment = this.responseText;
            } else {
                useWebGL = false;
            }
        });
        xhr_v.open('POST', 'js/vertex-shader.glsl', true);
        xhr_v.addEventListener("load", function (evt) {
            if (evt.target.status === 200) {
                GLScale.Hermite.vertex = this.responseText;
            } else {
                useWebGL = false;
            }
        });
        xhr_f.send();
        xhr_v.send();
    }

    glicon = new Image();
    glicon.className = 'webgl_logo';
    glicon.src = uploadImagePath + (useWebGL ? "/webgl.png" : "/webgl-off.png");
    document.getElementById('webgl_logo_wrapper').appendChild(glicon);

    // 브라우저가 사진 Orientation 보정을 자동으로 지원하지는지 확인
    testImg = new Image();
    testImg.onload = function () {
        supportImageOrientation = testImg.width === 2 && testImg.height === 3;
    };
    testImg.src =
      'data:image/jpeg;base64,/9j/4QAiRXhpZgAATU0AKgAAAAgAAQESAAMAAAABAAYAAAA' +
      'AAAD/2wCEAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBA' +
      'QEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQE' +
      'BAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf/AABEIAAIAAwMBEQACEQEDEQH/x' +
      'ABRAAEAAAAAAAAAAAAAAAAAAAAKEAEBAQADAQEAAAAAAAAAAAAGBQQDCAkCBwEBAAAAAAA' +
      'AAAAAAAAAAAAAABEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AG8T9NfSMEVMhQ' +
      'voP3fFiRZ+MTHDifa/95OFSZU5OzRzxkyejv8ciEfhSceSXGjS8eSdLnZc2HDm4M3BxcXw' +
      'H/9k=';
}
