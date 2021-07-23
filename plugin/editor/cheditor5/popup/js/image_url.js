// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2014 CHSOFT
// ================================================================
var AppWidth = "250";
var AppHeight = "175";
var AppID = "cheditorPreview";
var oEditor = null;
var button = [ { alt : "", img : 'submit.gif', cmd : doSubmit },
               { alt : "", img : 'cancel.gif', cmd : popupClose } ];
var newImage = null;

function CHEditorImagePreview () {
// ----------------------------------------------------------------------------------
// callBack function

    document.getElementById(AppID).CHEditorImagePreview("1", "1");
}

function CHXUploadRUN(src) {
// ----------------------------------------------------------------------------------
// Preview
//
    chxupload_RUN("src", src,
              "width", AppWidth,
              "height", AppHeight,
              "align", "middle",
              "id", AppID,
              "classid", AppID,
              "quality", "high",
              "bgcolor", "#ebe9ed",
              "name", AppID,
              "wmode", "transparent",
              "allowScriptAccess","Always",
              "type", "application/x-shockwave-flash",
              "pluginspage", "http://www.adobe.com/go/getflashplayer");
}

function getFilename (file) {
    while (file.indexOf("/") != -1) {
        file = file.slice(file.indexOf("/") + 1);
    }
    return file;
}

function popupClose()
{
    oEditor.popupWinCancel();
}

function chkImgFormat (url)
{
    var imageName = getFilename(url);
    var allowSubmit = false;
    var extArray = [".gif", ".jpg", ".jpeg", ".png", ".webp"];

    extArray.join(" ");
    if (imageName === "") {
        return false;
    }

    var ext = imageName.slice(imageName.lastIndexOf(".")).toLowerCase();
    var i;
    
    for (i = 0; i < extArray.length; i++) {
        if (extArray[i] == ext) {
            allowSubmit = true;
            break;
        }
    }

    if (!allowSubmit) {
        alert("사진은 GIF, JPG, PNG, WEBP 형식만 넣을 수 있습니다.");
        return false;
    }

    return imageName;
}

function previewImage (source) {
    if (navigator.appName.indexOf("microsoft") != -1) {
        window[AppID].CHEditorImagePreview(source, 0, 0);
    }
    else {
        document[AppID].CHEditorImagePreview(source, 0, 0);
    }
}

function checkImageComplete (img) {
    if (img.complete != true) {
        setTimeout("checkImageComplete(document.getElementById('"+img.id+"'))", 250);
    }
    else {
        document.getElementById('imageSize').innerHTML = '';

        newImage = new Image();
        newImage.style.width = img.width + 'px';
        newImage.style.height = img.height + 'px';
        newImage.setAttribute("src", img.src);
        newImage.setAttribute("alt", getFilename(img.src));
    }
}

function doPreview () {
    var imgurl = document.getElementById('fm_imageUrl').value;
    var fileName = chkImgFormat(imgurl);
    if (!fileName) {
        return;
    }

    var img = new Image();
    img.src = imgurl;
    img.id = fileName;

    document.getElementById('tmpImage').appendChild(img);
    checkImageComplete(img);
    previewImage(img.src);
}

function getElementById(id) {
    var el = null;
    try {
        el = document.getElementById(id);
    }
    catch (ignore) {}
    return el;
}

function removeObjectInIE(id) {
    var obj = getElementById(id);
    if (obj) {
        var i;
        for (i in obj) {
            if (typeof obj[i] == "function") {
                obj[i] = null;
            }
        }
        obj.parentNode.removeChild(obj);
    }
}

function removeSWF(id) {
    var obj = getElementById(id);
    if (obj && obj.nodeName == "OBJECT") {
        if (oEditor.getBrowser().msie) {
            obj.style.display = "none";
            (function(){
                if (obj.readyState == 4) {
                    removeObjectInIE(id);
                }
            })();
        }
        else {
            obj.parentNode.removeChild(obj);
        }
    }
}

function doSubmit ()
{
    if (newImage == null) {
         alert("미리 보기 버튼을 클릭하여 이미지를 확인해 주십시오.");
         return;
    }

    if (navigator.userAgent.toLowerCase().indexOf("msie")  != -1) {
        document.getElementById(AppID).style.display = 'none';
    }

    var fm_align = document.getElementById('fm_align').alignment;
    var align = 'center';
    var i;

    for (i=0; i<fm_align.length; i++) {
        if (fm_align[i].checked) {
            align = fm_align[i].value;
            break;
        }
    }

    newImage.setAttribute("align", align);
    var p = document.createElement("p");
    var id = oEditor.makeRandomString();
    p.id = id;
    p.setAttribute('align', align);
    p.appendChild(newImage);
    oEditor.insertHtmlPopup(p.cloneNode(true));
    p = oEditor._$(id);
    var br = document.createElement('br');
    oEditor.doc.body.insertBefore(br, p);
    oEditor.doc.body.insertBefore(p, br);

    removeSWF(AppID);
    oEditor.popupWinClose();
}

function outputImageSize (w, h) {
    var txt = document.createTextNode(w + ' X ' + h);
    document.getElementById('imageSize').innerHTML = '';
    document.getElementById('imageSize').appendChild(txt);
}

function showImageSize (w, h) {
    outputImageSize(w, h);
}

function setWrapper () {
    var wrapper = document.getElementById('tmpImage');
    wrapper.style.width = '0px';
    wrapper.style.height = '0px';
    wrapper.style.overflow = 'hidden';
    
    if (navigator.userAgent.toLowerCase().indexOf('opera') != -1) {
        wrapper.style.visibility = 'hidden';
    }
    else {
        wrapper.style.display = 'none';
    }
}

function showPreviewButton() {
    var img = new Image();
    img.src = oEditor.config.iconPath + 'button/preview.gif';
    img.style.verticalAlign = 'middle';
    img.className = 'button';
    img.alt = "";
    img.onclick = doPreview;
    document.getElementById("inputOutline").appendChild(img);
}

function init(dialog) {
    oEditor = this;
    oEditor.dialog = dialog;

    var dlg = new Dialog(oEditor);
    dlg.showButton(button);

    CHXUploadRUN(oEditor.config.popupPath + 'flash/ImagePreview');
    setWrapper();
    showPreviewButton();
    dlg.setDialogHeight();
}