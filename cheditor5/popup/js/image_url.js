// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2011 CHSOFT
// ================================================================
var AppWidth = "250";
var AppHeight = "175";
var AppID = "cheditorPreview";
var oEditor = null;
var button = [ { alt : "", img : 'submit.gif', cmd : doSubmit },
               { alt : "", img : 'cancel.gif', cmd : popupClose } ];
var imageArray = null;

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;
	
	imageArray = new Array();
	
	var dlg = new Dialog(oEditor);
	dlg.showButton(button);
	
	CHXUploadRUN(oEditor.config.popupPath + 'flash/ImagePreview');
	setWrapper();
	showPreviewButton();
	dlg.setDialogHeight();
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
	          "allowScriptAccess","Always",
	          "type", "application/x-shockwave-flash",
	          "pluginspage", "http://www.adobe.com/go/getflashplayer");
}



function popupClose()
{
	oEditor.popupWinClose();
}

function doSubmit ()
{
  	if (navigator.userAgent.toLowerCase().indexOf("msie")  != -1)
    	document.getElementById(AppID).style.display = 'none';

   	var fm_align = document.getElementById('fm_align').alignment;
    var align = '';
	var i = 0;

    for (; i<fm_align.length; i++) {
        if (fm_align[i].checked) {
            align = fm_align[i].value;
        	break;
    	}
	}

	imageArray[0]['align'] = align;

  	oEditor.doInsertImage(imageArray);
	popupClose();
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
    	var txt = document.createTextNode(img.width + ' X ' + img.height);
    	document.getElementById('imageSize').innerHTML = '';
    	document.getElementById('imageSize').appendChild(txt);

		var i = 0;
		imageArray[i] = new Object();
       	imageArray[i]['width'] = img.width;
       	imageArray[i]['height'] = img.height;
		imageArray[i]['src'] = img.src;
		imageArray[i]['alt'] = img.src;
  	}
}

function chkImgFormat (url)
{
    var imageName = getFilename(url);
    var allowSubmit = false;
    var extArray = new Array(".gif", ".jpg", ".jpeg", ".png");

    extArray.join(" ");
    if (imageName == "") return false;

    var ext = imageName.slice(imageName.lastIndexOf(".")).toLowerCase();

    for (var i = 0; i < extArray.length; i++) {
        if (extArray[i] == ext) {
            allowSubmit = true;
            break;
    	}
	}

    if (!allowSubmit) {
        alert("사진은 GIF, JPG, PNG 형식만 넣을 수 있습니다.");
        return false;
    }

	return imageName;
}

function doPreview () {
	var imgurl = document.getElementById('fm_imageUrl').value;
  	var fileName = chkImgFormat(imgurl);
	if (!fileName) return;

  	var img = new Image();
  	img.src = imgurl;
  	img.id = fileName;

  	document.getElementById('tmpImage').appendChild(img);
  	checkImageComplete(img);
  	previewImage(img.src);
}

function outputImageSize (w, h) {
  	var txt = document.createTextNode(w + ' X ' + h);
  	document.getElementById('imageSize').innerHTML = '';
  	document.getElementById('imageSize').appendChild(txt);
}

function showImageSize (w, h) {
    outputImageSize(w, h);
}

function getFilename (file) {
	while (file.indexOf("/") != -1) {
    	file = file.slice(file.indexOf("/") + 1);
	}
  	return file;
}

function setWrapper () {
  	var wrapper = document.getElementById('tmpImage');
  	wrapper.style.width = '0px';
  	wrapper.style.height = '0px';
  	wrapper.style.overflow = 'hidden';
  	
  	if (navigator.userAgent.toLowerCase().indexOf('opera') != -1)
    	wrapper.style.visibility = 'hidden';
  	else
    	wrapper.style.display = 'none';
}