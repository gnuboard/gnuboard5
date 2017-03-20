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
    fileTypeRe = /^image\/(png|jpeg|gif)$/i,
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
    selectedFilesNum = 0,
    showThumbnailSize = { width: 120, height: 90 },
    tmpLeft = 0,
    tmpTop = 0,
    uploadImagePath = '',
    uploadMaxNumber = 12,
    uploadScript;
//  deleteScript;

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
        if (i > 0 && ((i % 4) === 0)) {
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
        if (i === (uploadMaxNumber - 1)) {
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
            returnValue += (inputObj.offsetTop - inputObj.scrollTop);
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

        if (imgNum > 0 && (imgNum % 4) === 0) {
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

function img_delete_post(el){
    if( el.firstChild.tagName.toLowerCase() === 'img' ){
        var src = el.firstChild.getAttribute('src'),
            filesrc = src.replace(/^.*[\\\/]/, ''),
            data = "filesrc="+filesrc;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', deleteScript, true);
        //Send the proper header information along with the request
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.addEventListener("error", function (evt) {
            try {
                console.log("파일 전송 중 오류: " + evt.target.error.code);
            } catch(ex) {
            }
        }, false);

        xhr.send(data);
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
    btn.style.left = (showThumbnailSize.width - parseInt(btn.style.width, 10) - 1) + 'px';
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
            img_delete_post(self);
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
            resizeW = (imgSize.width > showThumbnailSize.width) ? showThumbnailSize.width : imgSize.width;
            resizeH = Math.round((imgSize.height * resizeW) / imgSize.width);
        } else {
            resizeH = (imgSize.height > showThumbnailSize.height) ? showThumbnailSize.height : imgSize.height;
            resizeW = Math.round((imgSize.width * resizeH) / imgSize.height);
        }

        if (resizeH > showThumbnailSize.height) {
            resizeH = (imgSize.height > showThumbnailSize.height) ? showThumbnailSize.height : imgSize.height;
            resizeW = Math.round((imgSize.width * resizeH) / imgSize.height);
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
    oEditor.addEvent(elem, 'mouseover', showDelete);
    elem.onmouseout = function () {
        this.className = 'imageBox_theImage';
        hideDelete();
    };

    setImageCount();
}

function showUploadWindow() {
    // ----------------------------------------------------------------------------------
    var uploadWindow  = document.getElementById("uploadWindow"),
        uploadWindowWidth = 700,
        winWidth, el, i, j, imgBox, img;

    if (!(oEditor.undefined(window.innerWidth))) {
        winWidth  = window.innerWidth;
    } else if (document.documentElement &&
        (!(oEditor.undefined(document.documentElement.clientWidth))) &&
             document.documentElement.clientWidth !== 0) {
        winWidth = document.documentElement.clientWidth;
    } else if (document.body && (!(oEditor.undefined(document.body.clientWidth)))) {
        winWidth = document.body.clientWidth;
    } else {
        alert('현재 브라우저를 지원하지 않습니다.');
        return;
    }

    uploadWindow.style.left = winWidth / 2 - (uploadWindowWidth / 2) + 'px';
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

                if (imgBox.firstChild && (imgBox.firstChild.src === modifyImages[i])) {
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
                img_delete_post(img);
                img.removeChild(remove[j]);
            }

            img.parentNode.className = 'imageBox';
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
        setTimeout('startMoveTimer()', 8);
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
            code = code === 1 ? 0 : (code === 4 ? 1 : code);
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
         (divXPositions[prop] + divWidth[prop] * 0.7) > leftPos &&
          divYPositions[prop] < topPos &&
         (divYPositions[prop] + divWidth[prop]) > topPos) {
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
                    if (view.getUint16(offset + (i * 12), little) === 0x0112) {
                        return view.getUint16(offset + (i * 12) + 8, little);
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

    imageResize : function (image, filetype, resizeWidth, orientation, addWaterMark) {
        var canvas = document.createElement("canvas"),
            width = image.width,
            height = image.height,
            bitmapData, ctx, rotateImg, rotateW, rotateH, angle, step, offcanvas, offctx, dHeight, dWidth;



        // 카메라를 돌려서 찍은 경우, 높이를 가로 사이즈로 정한 다음 리사이징 처리. 이 경우, 파일 크기와 처리 속도가
        // 증가한다.

        // if (orientation === 6 || orientation === 8) {
        //     var ratio = resizeWidth / height;
        //     dHeight = height * ratio;
        //     dWidth = width * ratio;
        // } else {
        dHeight = Math.ceil(resizeWidth / width * height);
        dWidth = resizeWidth;
        // }

        canvas.width = dWidth;
        canvas.height = dHeight;
        ctx = canvas.getContext("2d");

        step = Math.ceil(Math.log(image.width / resizeWidth) / Math.log(2));

        if (step > 1) {
            offcanvas = document.createElement('canvas');
            offctx = offcanvas.getContext('2d');
            offcanvas.width = width / 2;
            offcanvas.height = height / 2;

            offctx.drawImage(image, 0, 0, offcanvas.width, offcanvas.height);
            offctx.drawImage(offcanvas, 0, 0, offcanvas.width / 2, offcanvas.height / 2);
            ctx.drawImage(offcanvas, 0, 0, offcanvas.width / 2, offcanvas.height / 2, 0, 0, dWidth, dHeight);
        } else {
            ctx.drawImage(image, 0, 0, dWidth, dHeight);
        }

        if (orientation === 6 || orientation === 8 || orientation === 3) {
            angle = orientation === 6 ? Math.PI / 2 : (orientation === 8 ? -Math.PI / 2 : 180 * Math.PI / 2);
            bitmapData = canvas.toDataURL(filetype, oEditor.config.imgJpegQuality);

            rotateImg = new Image();
            rotateImg.src = bitmapData;
            rotateW = orientation !== 3 ? dHeight : dWidth;
            rotateH = orientation !== 3 ? dWidth : dHeight;

            canvas.width = rotateW;
            canvas.height = rotateH;

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.translate(canvas.width / 2, canvas.height / 2);
            ctx.rotate(angle);
            ctx.drawImage(rotateImg, -dWidth / 2, -dHeight / 2);
            ctx.restore();
        }

        if (this.reader.watermark && addWaterMark) {
            ctx.globalAlpha = oEditor.config.imgWaterMarkAlpha;
            ctx.drawImage(this.reader.watermark,
                canvas.width - this.reader.watermark.width, canvas.height - this.reader.watermark.height);
        }
        return canvas.toDataURL(filetype, oEditor.config.imgJpegQuality);
    },

    canvasToBlob : function (bitmapData, mimetype) {
        var raw = atob(bitmapData.split(',')[1]),
            intArray = [],
            len = bitmapData.length,
            i = 0;

        for (; i < len; i++) {
            intArray.push(raw.charCodeAt(i));
        }
        return new Blob([new Uint8Array(intArray)], {type: mimetype});
    },

    makeThumbnail : function (image, type, name, orientation) {
        var canvas = document.createElement("canvas"),
            width,
            xhr = new XMLHttpRequest(),
            data = new FormData(),
            bitmapData, file;

        xhr.open('POST', uploadScript, true);
        width = oEditor.config.thumbnailWidth;

        bitmapData = this.imageResize(image, type, width, orientation);
        file = this.canvasToBlob(bitmapData, type);

        data.append(inputFileName, file, 'thumb_' + name); // RFC Level 2

        xhr.addEventListener("loadend", function () {

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
            blob, image, orientation = 1;

        if (evt.target.readyState === FileReader.DONE) {
            blob = new self.MyBlob(self.NewBlob(evt.target.result, filetype));
            try {
                orientation = self.getOrientation(evt.target.result.slice(0, 64 * 1024));
            } catch(err) {

            }
            image = new Image();

            image.onload = function () {
                var bitmapData = null,
                    canvas = document.createElement("canvas"),
                    data = new FormData(),
                    fileFormat,
                    imgBox = file.boxElem,
                    imgInfo = {},
                    randomName,
                    xhr = new XMLHttpRequest();

                xhr.open('POST', uploadScript, true);

                if (imageResizeWidth > 0 && this.width > imageResizeWidth) {
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
                    alert("파일 전송 중 오류: " + evt.target.error.code);
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
            imageResizeInput.setAttribute('placeholder', oEditor.config.imgMaxWidth.toString());
        }
    }
}

function init(dialog) {
    var dlg, i, elem, input, select, value, name;

    oEditor = this;
    oEditor.dialog = dialog;
    dlg = new Dialog(oEditor);
    browser = oEditor.getBrowser();

    uploadImagePath = oEditor.config.iconPath + 'imageUpload';
    uploadMaxNumber = oEditor.config.imgUploadNumber;
    uploadScript = oEditor.config.editorPath + 'imageUpload/upload.php';
    deleteScript = oEditor.config.editorPath + 'imageUpload/delete.php';
    imageListWrapper = document.getElementById("imageListWrapper");

    imageResizeWidth = oEditor.config.imgMaxWidth;
    imageResizeInput = document.getElementById('idResizeWidth');
    select = document.getElementById('idResizeSelectBox');

    if (imageResizeWidth > 0) {
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

    dlg.setDialogHeight(370);
    dlg.showButton(button);
    showContents();
    initGallery();
    showUploadWindow();
    createInsertionMaker();
    selectedFilesNum = 0;

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

    if (browser.mobile) {
        input = document.getElementById('inputImageUpload');
        input.setAttribute('capture', 'gallery');
    }
}
