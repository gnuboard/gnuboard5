// ================================================================
//                           CHEditor 5
// ================================================================
var uploadScript = '',
    deleteScript = '',
    AppID = 'chximage',
    AppSRC = '',
    activeImage = null,
    destinationObject = null,
    readyToMove = false,
    moveTimer = -1,
    dragDropDiv,
    insertionMarker,
    offsetX_marker = -4,
    offsetY_marker = -3,
    geckoOffsetX_marker = 4,
    geckoOffsetY_marker = -2,
    divXPositions = [],
    divYPositions = [],
    divWidth = [],
    divHeight = [],
    tmpLeft = 0,
    tmpTop = 0,
    eventDiff_x = 0,
    eventDiff_y = 0,
    modifyImages = [],
    uploadMaxNumber = 12,
    imageCompleted = 0,
    imageCompletedList = [],
    uploadButton = '',
    uploadImagePath = '',
    showThumbnailSize = { width: 120, height: 90 },
    oEditor = null,
    button,
    imageResizeWidth = 0,
    makeThumbnail = true,
    makeThumbnailWidth = 120,
    makeThumbnailHeight = 90,
    sortOnName = false,
    browser = null;

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
    // ----------------------------------------------------------------------------------
    swfobject.removeSWF(AppID);
    oEditor.popupWinCancel();
}

function showContents() {
    var spacer = function (id) {
        var clear = document.createElement('span');
        clear.style.height = '0';
        clear.style.width = '0';
        clear.className = 'clear';
        clear.id = 'spacer' + id;
        return clear;
    }, spacerNo = 1, i, imgBox, theImg, lastSpacer;

    for (i = 0; i < uploadMaxNumber; i++) {
        if (i > 0 && ((i % 4) === 0)) {
            document.getElementById('imageListWrapper').appendChild(spacer(spacerNo++));
        }

        imgBox = document.createElement('div');
        imgBox.id = 'imgBox' + i;
        imgBox.className = 'imageBox';
        theImg = document.createElement('div');
        theImg.id = 'img_' + i;
        theImg.className = 'imageBox_theImage';
        imgBox.appendChild(theImg);

        document.getElementById('imageListWrapper').appendChild(imgBox);
        if (i === (uploadMaxNumber - 1)) {
            lastSpacer = spacer(spacerNo);
            lastSpacer.style.height = "7px";
            document.getElementById('imageListWrapper').appendChild(lastSpacer);
        }
    }

    if (browser.msie && browser.ver < 7) {
        document.getElementById('imageListWrapper').style.padding = '5px 2px 5px 2px';
        document.getElementById('imageInfoBox').style.height = '302px';
        document.getElementById('imageInfoBox').style.width = '124px';
    } else {
        document.getElementById('imageListWrapper').style.padding = '5px 7px 0 5px';
        document.getElementById('imageInfoBox').style.height = '298px';
        document.getElementById('imageInfoBox').style.width = '130px';
    }
}

function openFiles() {
    // ----------------------------------------------------------------------------------
    var elem = browser.msie ? document.getElementById(AppID) : document[AppID];
    elem.AddFiles();
}

function setImageCount() {
    imageCompleted++;
    document.getElementById('imageCount').innerHTML = imageCompleted;
}

function getImageCount() {
    return imageCompleted;
}

function allowedMaxImage() {
    return uploadMaxNumber - getImageCount();
}

function getUploadedCount() {
    return document.getElementById('imageListWrapper').getElementsByTagName('img').length;
}

function uploadedImageCount() {
    imageCompleted = getUploadedCount();
    document.getElementById('imageCount').innerHTML = imageCompleted;
}

function uploadError(msg) {
    alert(msg);
}

function imageDelete(filePath) {
    var chximage = document.getElementById(AppID);
    chximage.ImageDelete(encodeURI(filePath));
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
            if (browser.msie) {
                returnValue += inputObj.clientLeft;
            }
        }
        inputObj = inputObj.offsetParent;
    }
    return returnValue;
}

function getDivCoordinates() {
    // ----------------------------------------------------------------------------------
    var imgBox = document.getElementById('imageListWrapper').getElementsByTagName('DIV'),
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
    var wrapper = document.getElementById('imageListWrapper'),
        imgBox = wrapper.getElementsByTagName('div'),
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
            wrapper.appendChild(spacer);
        } else {
            wrapper.insertBefore(spacer, document.getElementById(breakline[i]));
        }
    }
}

function setImageInfo(id) {
    var elem;
    if (!id) {
        document.getElementById('selectedImageWidth').innerHTML = '0';
        document.getElementById('selectedImageHeight').innerHTML = '0';
        document.getElementById('selectedImageName').innerHTML = "없음";
    } else {
        elem = imageCompletedList[id];
        document.getElementById('selectedImageWidth').innerHTML = elem.width;
        document.getElementById('selectedImageHeight').innerHTML = elem.height;
        document.getElementById('selectedImageName').innerHTML = elem.origName;
    }
}

function showDelete() {
    // ----------------------------------------------------------------------------------
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
        ev = ev || window.event;
        ev.cancelBubble = true;
        this.style.display = 'block';
        setImageInfo(self.id);
        this.className = 'removeButton_over';
        self.className = 'imageBox_theImage_over';
    };
    btn.onmouseout = function () {
        this.className = 'removeButton';
    };
    btn.onmousedown = function () {
        var images = self.getElementsByTagName('img'), i, wrapper, moveobj, target;

        for (i = 0; i < images.length; i++) {
            self.removeChild(images[i]);
        }

        self.removeChild(self.firstChild);
        self.className = 'imageBox_theImage';

        if (self.parentNode.nextSibling && self.parentNode.nextSibling.id) {
            wrapper = document.getElementById('imageListWrapper');
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
                wrapper.insertBefore(moveobj, target);
                moveobj = target.nextSibling;
            }
        }

        reOrder();
        uploadedImageCount();
        setImageInfo(0);
        this.style.display = 'none';
        document.body.appendChild(this);
        self.onmouseout = self.onmouseover = null;
    };

    setImageInfo(self.id);
}

function hideDelete() {
    // ----------------------------------------------------------------------------------
    document.getElementById('removeImageButton').style.display = 'none';
}

function startUpload(count) {
    // ----------------------------------------------------------------------------------
    var el = document.getElementById('imageListWrapper').getElementsByTagName('div'), i, imgBox;

    for (i = 0; i < el.length; i++) {
        imgBox = el[i];
        if (imgBox.className !== 'imageBox_theImage') {
            continue;
        }

        if (count === 0) {
            break;
        }

        if (!imgBox.firstChild || imgBox.firstChild.tagName.toLowerCase() !== 'img') {
            imgBox.style.backgroundImage = "url('" + uploadImagePath + "/loader.gif')";
            count--;
        }
    }
}

function fileFilterError(file) {
    alert("선택하신 '" + file + "' 파일은 전송할 수 없습니다.\n" +
       "gif, png, jpg, webp 그림 파일만 전송할 수 있습니다.");
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
    elem.style.backgroundImage = "url('" + uploadImagePath + "/dot.gif')";
    elem.onmouseover = showDelete;
    elem.onmouseout = function() {
        this.className = 'imageBox_theImage';
        setImageInfo(0);
        hideDelete();
    };

    setImageCount();
}

function uploadComplete(image) {
    // ----------------------------------------------------------------------------------
    var el = document.getElementById('imageListWrapper').getElementsByTagName('div'),
        imgBox = null, tmpImg, i, imgInfo,
        imgOnLoad = function () {
            imgInfo = { "width": image.width, "height": image.height, "fileSize": image.fileSize,
                    "fileUrl": image.fileUrl, "fileName": image.fileName, "filePath": image.filePath, "origName": image.origName };

            imageCompletedList[imgBox.id] = imgInfo;
            imgComplete(this, imgInfo, imgBox.id);
        };

    image.filePath = decodeURI(image.filePath);
    image.origName = decodeURI(image.origName);

    for (i = 0; i < el.length; i++) {
        imgBox = el[i];
        if (imgBox.className !== 'imageBox_theImage') {
            continue;
        }

        if (!imgBox.firstChild || imgBox.firstChild.tagName.toLowerCase() !== 'img') {
            tmpImg = new Image();
            tmpImg.style.width = "0px";
            tmpImg.style.height = "0px";
            tmpImg.setAttribute("alt", image.origName);
            tmpImg.onload = imgOnLoad;
            tmpImg.src = image.fileUrl;
            imgBox.appendChild(tmpImg);
            break;
        }
    }
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
        el = document.getElementById('imageListWrapper').getElementsByTagName('div');
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
            chkObj = document.getElementById('imageListWrapper').firstChild;
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

    activeImage.className = 'imageBox';
    return true;
}

function dragDropMove(e) {
    // ----------------------------------------------------------------------------------
    var elementFound = false, prop, offsetX, offsetY, leftPos, topPos, btnCode;

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
        destinationObject = null;
    }

    return false;
}

function saveImageOrder() {
    // ----------------------------------------------------------------------------------
    var rData = [],
        objects = document.getElementById('imageListWrapper').getElementsByTagName('div'),
        i;

    for (i = 0; i < objects.length; i++) {
        if (objects[i].className === 'imageBox' ||
         objects[i].className === 'imageBoxHighlighted') {
            rData.push(objects[i].id);
        }
    }

    return rData;
}

function initGallery() {
    // ----------------------------------------------------------------------------------
    var imgBox = document.getElementById('imageListWrapper').getElementsByTagName('div'),
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
    var el = document.getElementById('imageListWrapper').getElementsByTagName('div'),
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

function initEvent() {
    var swfVersionStr = "11.1.0",
        xiSwfUrlStr = "http://get.adobe.com/kr/flashplayer/",
        flashvars = {
            UploadScript:     uploadScript,
            DeleteScript:     deleteScript,
            UploadButton:     uploadButton,
            MakeThumbnail:    makeThumbnail,
            ThumbnailWidth:   makeThumbnailWidth,
            ThumbnailHeight:  makeThumbnailHeight,
            ImageResizeWidth: imageResizeWidth,
            loadPolicyFile:   true,
            SortOnName:       sortOnName
        },
        params = {
            quality: "high",
            bgcolor: "#ffffff",
            allowscriptaccess: "Always",
            allowfullscreen: "false",
            //allowNetworking: "all",
            wmode: "transparent"
        },
        attributes = { id: AppID, name: AppID, align: "middle" };

    swfobject.embedSWF(AppSRC, "oFlashButton", "93", "22", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
}

function init(dialog) {
    var dlg = new Dialog(this),
        elem = document.getElementById('id_alignment').elements,
        i;

    oEditor = this;
    oEditor.dialog = dialog;

    browser = oEditor.getBrowser();

    uploadImagePath = oEditor.config.iconPath + 'imageUpload';
    uploadButton = '../icons/imageUpload/add.gif';
    AppSRC = oEditor.config.popupPath + 'flash/chximage.swf';
    uploadMaxNumber = oEditor.config.imgUploadNumber;
    uploadScript = oEditor.config.editorPath + 'imageUpload/upload.php';
    deleteScript = oEditor.config.editorPath + 'imageUpload/delete.php';

    imageResizeWidth = oEditor.config.imgMaxWidth;
    makeThumbnail = oEditor.config.makeThumbnail;
    sortOnName = oEditor.config.imgUploadSortName;
    makeThumbnailWidth = oEditor.config.thumbnailWidth;
    makeThumbnailHeight = oEditor.config.thumbnailHeight;

    document.getElementById("maxImageNum").appendChild(document.createTextNode(uploadMaxNumber));

    button = [
        { alt: "", img: 'submit.gif', cmd: doSubmit, hspace: 2 },
        { alt: "", img: 'cancel.gif', cmd: closeWindow, hspace: 2 }
    ];

    dlg.setDialogHeight(370);
    dlg.showButton(button);
    showContents();
    initGallery();
    showUploadWindow();
    initEvent();
    createInsertionMaker();

    for (i = 0; i < elem.length; i++) {
        if (elem[i].name === "alignment" && elem[i].value === oEditor.config.imgDefaultAlign) {
            elem[i].checked = "checked";
            break;
        }
    }
}
