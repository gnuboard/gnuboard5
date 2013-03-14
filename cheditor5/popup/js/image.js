// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2011 CHSOFT
// ================================================================
var operaBrowser = false;
if (navigator.userAgent.indexOf('Opera') >= 0)
	operaBrowser = 1;

var MSIE = navigator.userAgent.indexOf('MSIE') >= 0;
var navigatorVersion = navigator.appVersion.replace(/.*?MSIE (\d\.\d).*/g,'$1')/1;
var UploadScript = "";
var DeleteScript = "";
var AppID = "CHXImage";
var AppSRC = "";
var activeImage = false;
var readyToMove = false;
var moveTimer = -1;
var dragDropDiv;
var insertionMarker;
var hideTimer = null;
var offsetX_marker = 4;
var offsetY_marker = -3;
var firefoxOffsetX_marker = 4;
var firefoxOffsetY_marker = -2;
	
if (navigatorVersion == 8 && MSIE) {
	offsetX_marker = 3;
	offsetY_marker = -4;	
}
	
var destinationObject = false;
var divXPositions = [];
var divYPositions = [];
var divWidth = [];
var divHeight = [];
var tmpLeft = 0;
var tmpTop = 0;
var eventDiff_x = 0;
var eventDiff_y = 0;
var modifyImages = [];
var uploadMaxNumber = 12;
var imageCompleted = 0;
var imageCompletedList = [];
var UploadButton = "";
var UploadImagePath = "";
var oEditor = null;
var button = [ { alt : "", img : 'imageUpload/submit.gif', cmd : doSubmit, hspace : 2 },
               { alt : "", img : 'imageUpload/cancel.gif', cmd : closeWindow, hspace : 2 } ];

var allowedMaxImgSize = 0;

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;
	var dlg = new Dialog(oEditor);

	UploadImagePath = oEditor.config.iconPath + 'imageUpload';
	UploadButton = oEditor.config.iconPath + 'imageUpload/add_image_button.gif';
	AppSRC = oEditor.config.popupPath + 'flash/CHXImage';

	UploadScript = oEditor.config.editorPath + 'imageUpload/upload.php';
	DeleteScript = oEditor.config.editorPath + 'imageUpload/delete.php';

	allowedMaxImgSize = oEditor.config.allowedMaxImgSize;

	dlg.setDialogHeight(397);
	dlg.showButton(button);
	showContents();
	initGallery();
	showUploadWindow();
	initEvent();
	createInsertionMaker();
}

function createInsertionMaker() {
	var wrapper = document.getElementById('insertionMarker');
	var topIco = new Image();
	topIco.src = UploadImagePath + '/marker_top.gif';
	topIco.style.width = '6px';
	topIco.style.height = '1px';
	wrapper.appendChild(topIco);

	var middleIco = new Image();
	middleIco.src = UploadImagePath + '/marker_middle.gif';
	middleIco.style.height = '100px';
	middleIco.style.width = '6px';
	wrapper.appendChild(middleIco);
	
	var bottomIco = new Image();
	bottomIco.src = UploadImagePath + '/marker_bottom.gif';
	bottomIco.style.width = '6px';
	bottomIco.style.height = '1px';
	wrapper.appendChild(bottomIco);
}

function popupClose() {
// ----------------------------------------------------------------------------------
   	oEditor.popupWinClose();
}

function showContents() {
	var spacer = function(id) {
		var clear = document.createElement('DIV');
		clear.style.height = '0px';
		clear.style.width = '0px';
		clear.className = 'clear';
		clear.id = 'spacer' + id;
		if (MSIE && navigatorVersion < 7) clear.style.display = 'inline';
		return clear;
	};

	var spacerNo = 1;
	for (var i=0; i<uploadMaxNumber; i++) {
		if (i > 0 && ((i % 4) == 0)) {
			document.getElementById('imageListWrapper').appendChild(spacer(spacerNo++));
		}
		var imgBox = document.createElement('DIV');
		imgBox.id = 'imgBox' + i;
		imgBox.className = 'imageBox';
		var theImg = document.createElement('DIV');
		theImg.id = 'img_' + i;
		theImg.className = 'imageBox_theImage';
		imgBox.appendChild(theImg);

		document.getElementById('imageListWrapper').appendChild(imgBox);
		if (i == 11) {
			document.getElementById('imageListWrapper').appendChild(spacer(spacerNo));
		}
	}

	if (MSIE && navigatorVersion < 7) {
		document.getElementById('imageListWrapper').style.padding = '5px 2px 5px 2px';
		document.getElementById('imageInfoBox').style.height = '302px';
		document.getElementById('imageInfoBox').style.width = '124px';
	}
	else {
		document.getElementById('imageListWrapper').style.padding = '5px 7px 7px 5px';
		document.getElementById('imageInfoBox').style.height = '298px';
		document.getElementById('imageInfoBox').style.width = '130px';
	}
}

function openFiles() {
// ----------------------------------------------------------------------------------
	var elem = MSIE ? document.getElementById(AppID) : document[AppID];
	elem.AddFiles();
}

function setImageCount() {
	imageCompleted++;
	document.getElementById('imageCount').innerHTML = imageCompleted;
}

function getImageCount() {
	return imageCompleted;
}

function resetImageCount(num) {
	imageCompleted = num;
	document.getElementById('imageCount').innerHTML = num;
}

function showDelete(event) {
// ----------------------------------------------------------------------------------
	getDivCoordinates();

	var self = this;
	var button = document.getElementById('removeImage');
	var L = divXPositions[self.parentNode.id];
	var T = divYPositions[self.parentNode.id];

	self.className = 'imageBox_theImage_over';
	button.style.left = (L + 126) + 'px';
	button.style.top = (T - 7) + 'px';
	button.style.display = 'block';
	button.onmouseover = function() {
		self.className = 'imageBox_theImage_over';
		document.getElementById('selectedImageWidth').innerHTML = imageCompletedList[self.id]['width'];
		document.getElementById('selectedImageHeight').innerHTML = imageCompletedList[self.id]['height'];
	};
	
	document.getElementById('selectedImageWidth').innerHTML = imageCompletedList[self.id]['width'];
	document.getElementById('selectedImageHeight').innerHTML = imageCompletedList[self.id]['height'];

	button.onclick = function() {
		create_request_object(DeleteScript + '?img=' + self.firstChild.src);
		self.removeChild(self.firstChild);
		self.onmouseover = null;
		self.className = 'imageBox_theImage';
		document.getElementById('removeImage').style.display = 'none';

		if (self.parentNode.nextSibling && self.parentNode.nextSibling.id)
		{
			var wrapper = document.getElementById('imageListWrapper');
			var moveobj = self.parentNode.nextSibling;
			var target = self.parentNode;

			while (moveobj != null) {
				wrapper.insertBefore(moveobj, target);
				moveobj = target.nextSibling;
			}
		}

		resetSelectedImageSize();
		reOrder();
	};

	if (hideTimer) clearTimeout(hideTimer);
	hideTimer = setTimeout('hideDelete()', 3000);
}

function hideDelete(event) {
// ----------------------------------------------------------------------------------
	document.getElementById('removeImage').style.display = 'none';
}

function resetSelectedImageSize() {
	document.getElementById('selectedImageWidth').innerHTML = 0;
	document.getElementById('selectedImageHeight').innerHTML = 0;
}

function startUpload(count) {
// ----------------------------------------------------------------------------------
	var el = document.getElementById('imageListWrapper').getElementsByTagName('DIV');

	for (var i=0; i < el.length; i++) {
		var imgBox = el[i];
		if (imgBox.className != 'imageBox_theImage')
			continue;

		if (count == 0) break;

		if (imgBox.firstChild == null || typeof(imgBox.firstChild.src) == 'undefined') {
			imgBox.style.backgroundImage = "url('"+UploadImagePath+"/wait.gif')";
			count--;
		}
	}
}

function fileFilterError(file) {
	alert("선택하신 '" + file + "' 파일은 전송할 수 없습니다.\n" +
		  "gif, png, jpg, 그림 파일만 전송할 수 있습니다.");
}

function uploadComplete(fileData) {
// ----------------------------------------------------------------------------------
	fileData = fileData.replace(/^\s+/g, '').replace(/\s+$/g, '');
	if (/^-ERR/.test(fileData)) {
		alert(fileData);
		popupClose();
	}

	if (imageCompleted >= uploadMaxNumber)
		return;
	
	var tmpData = eval('('+fileData+')');

	if (typeof tmpData == 'undefined')
		return;

	var el = document.getElementById('imageListWrapper').getElementsByTagName('DIV');

	for (var i=0; i < el.length; i++) {
		var imgBox = el[i];
		if (imgBox.className != 'imageBox_theImage')
			continue;
	
		if (tmpData['fileSize'] == 0) {
			imgBox.style.backgroundImage = '';
			alert(tmpData['origName'] + ' 파일은 잘못된 파일입니다.');
			break;
		}

		if (imgBox.firstChild == null || typeof(imgBox.firstChild.src) == 'undefined') {
			var tmpImg = new Image();
			tmpImg.src = tmpData.fileUrl;

			imgBox.appendChild(tmpImg);
			if (MSIE) tmpImg.style.display = "none";
			else tmpImg.style.visibility = 'hidden';
			imgComplete(tmpImg, imgBox.id, tmpData);
			break;
		}
	}
}

function imgComplete(img, boxId, dataObj) {
	if (img.complete != true) {
		var R = function() { imgComplete(img, boxId, dataObj);img=null;};
		setTimeout(R, 100);
	}
	else {
		img.border = 0;
		img.alt = '';
		var fixWidth = 120;
		var fixHeight = 90;
		var resizeW, resizeH;
		imageCompletedList[boxId] = { width: img.width, height: img.height, info: dataObj };

		if (img.width > fixWidth || img.height > fixHeight) {
			if (img.width > img.height) {
				resizeW = (img.width > fixWidth) ? fixWidth : img.width;
				resizeH = Math.round((img.height * resizeW) / img.width);
			}
			else {
				resizeH = (img.height > fixHeight) ? fixHeight : img.height;
				resizeW = Math.round((img.width * resizeH) / img.height);
			}

			if (resizeH > fixHeight) {
				resizeH = (img.height > fixHeight) ? fixHeight : img.height;
				resizeW = Math.round((img.width * resizeH) / img.height);
			}

		}
		else {
			resizeW = img.width;
			resizeH = img.height;
		}

		img.style.width  = resizeW + 'px';
		img.style.height = resizeH + 'px';
		img.hspace = 2;
		img.vspace = 2;

		if (resizeW < fixWidth) {
			var M = fixWidth - resizeW;
			img.style.marginLeft = Math.round(M/2) + 'px';
		}

		if (resizeH < fixHeight) {
			var M = fixHeight - resizeH;
			img.style.marginTop = Math.round(M/2) + 'px';
		}

		var elem = document.getElementById(boxId);
		elem.style.backgroundImage = "url('"+oEditor.config.iconPath+"dot.gif')";
		elem.onmouseover = showDelete;
		elem.onmouseout = function() {
				this.className = 'imageBox_theImage';
				resetSelectedImageSize();
		};
		
		if (MSIE) img.style.display = "block";
		else img.style.visibility = 'visible';
		setImageCount();
	}
}

function errMaxFileSize (errFileName) {
	alert("선택하신 '"+errFileName+"' 파일의 크기가 너무 큽니다.\n선택 가능한 파일의 최대 크기는 " + 
			allowedMaxImgSize+" 바이트입니다.");
}

function initEvent() {
//----------------------------------------------------------------------------------
	CHXImageRUN (
			"src", 			AppSRC,
			"width", 		"93",
			"FlashVars", 	"ServerURL="+UploadScript+"&UploadButton="+UploadButton+"&MaxFileSize="+allowedMaxImgSize,
			"height", 		"22",
			"align", 		"middle",
			"id", 			AppID,
			"quality", 		"high",
			"bgcolor", 		"#ffffff",
			"name", 		AppID,
			"allowScriptAccess","Always",
			"type", 		"application/x-shockwave-flash",
			"pluginspage", 	"http://www.adobe.com/go/getflashplayer"
		);
}

function showUploadWindow() {
// ----------------------------------------------------------------------------------
  	var uploadWindow  = document.getElementById("uploadWindow");
  	var uploadWindowWidth  = 700;
  	var winWidth  = 0;
  
  	if (typeof(window.innerWidth) != 'undefined') {
  		winHeight = window.innerHeight;
  		winWidth  = window.innerWidth;
  	}
	else if (document.documentElement && typeof document.documentElement.clientWidth!='undefined'
		&& document.documentElement.clientWidth != 0 )
	{
		winWidth  = document.documentElement.clientWidth;
		winHeight = document.documentElement.clientHeight;
	} 
	else if (document.body && typeof document.body.clientWidth!='undefined') {
		winWidth  = document.body.clientWidth;
		winHeight = document.body.clientHeight;
	}
	else {
		alert('현재 브라우저를 지원하지 않습니다.');
		return;
	}

  	var left = winWidth / 2 - (uploadWindowWidth / 2) + 'px';
  
  	uploadWindow.style.left = left;
  	uploadWindow.style.display = "block";
  	uploadWindow.style.width = uploadWindowWidth + 'px';

  	if (modifyImages.length > 0) {
		var el = document.getElementById('imageListWrapper').getElementsByTagName('DIV');

	  	for (var i=0; i < modifyImages.length; i++) {
			if (i > 7) break;

			for (var j=0; j < el.length; j++) {
				var imgBox = el[j];
				if (imgBox.className != 'imageBox_theImage')
					continue;

				if (imgBox.firstChild && (imgBox.firstChild.src == modifyImages[i])) {
					break;
				}

				if (imgBox.firstChild == null) {
					var img = new Image();
					img.src = modifyImages[i];
					img.border = 0;
					img.alt = '';
					img.style.width = '120px';
					img.style.height = '90px';
					imgBox.appendChild(img);
					imgBox.onmouseover = showDelete;
					break;
				}
	  		}
	  	}
  	}

}

function closeWindow() {
// ----------------------------------------------------------------------------------
	if (removeImage())
		popupClose();
}

function removeImage() {
// ----------------------------------------------------------------------------------
	var images = [];

	for (var i=0; i < uploadMaxNumber; i++) {
		var theImage = document.getElementById('img_'+i);
		if (theImage.hasChildNodes() && (typeof theImage.firstChild.src != 'undefined'))
			images.push(theImage);
	}

	if (images.length > 0) {
		if (!confirm('추가하신 사진이 있습니다. 사진 넣기를 취소하시겠습니까?')) {
			return false;
		}

		for (var i=0; i<images.length; i++) {
			var img = images[i];
			if (img.firstChild != null) {
				create_request_object(DeleteScript + '?img=' + img.firstChild.src);
				img.removeChild(img.firstChild);
				img.onmouseover = null;
				img.parentNode.className = 'imageBox';
			}
		}
	}
	
	return true;
}

function cancelEvent() {
// ----------------------------------------------------------------------------------
	return false;
}

function getTopPos(inputObj) {		
// ----------------------------------------------------------------------------------
	var returnValue = inputObj.offsetTop;
  	while ((inputObj = inputObj.offsetParent) != null) {
	  	if (inputObj.tagName != 'HTML') {
	  		returnValue += (inputObj.offsetTop - inputObj.scrollTop);
			if (MSIE)
				returnValue+=inputObj.clientTop;
	  	}
	} 

	return returnValue;
}

function getLeftPos(inputObj) {	  
// ----------------------------------------------------------------------------------
	var returnValue = inputObj.offsetLeft;
  	while ((inputObj = inputObj.offsetParent) != null) {
	  	if (inputObj.id != 'imageListWrapper') {
	  		returnValue += inputObj.offsetLeft;
			if (MSIE)
				returnValue+=inputObj.clientLeft;
	  	}
	}

	return returnValue;
}
		
function selectImage(e) {
// ----------------------------------------------------------------------------------
	if (MSIE)
		e = event;

	var el = this.parentNode.firstChild.firstChild;
	if (!el) return;

	var obj = this.parentNode;
	if (activeImage)
		activeImage.className = 'imageBox';

	obj.className = 'imageBoxHighlighted';
	activeImage = obj;
	readyToMove = true;
	moveTimer = 0;
		
	tmpLeft = e.clientX + Math.max(document.body.scrollLeft,document.documentElement.scrollLeft);
	tmpTop = e.clientY + Math.max(document.body.scrollTop,document.documentElement.scrollTop);
		
	startMoveTimer();	
	return false;	
}
	
function startMoveTimer() {
// ----------------------------------------------------------------------------------
	if (moveTimer >= 0 && moveTimer < 10) {
		moveTimer++;
		setTimeout('startMoveTimer()', 8);
	}

	if (moveTimer == 5) {
		getDivCoordinates();
		var subElements = dragDropDiv.getElementsByTagName('DIV');
		if (subElements.length > 0) {
			dragDropDiv.removeChild(subElements[0]);
		}
		
		dragDropDiv.style.display = 'block';
		var newDiv = activeImage.cloneNode(true);
		newDiv.className = 'imageBox';	
		newDiv.id = '';
		newDiv.style.padding = '2px';
		dragDropDiv.appendChild(newDiv);	
			
		dragDropDiv.style.top = tmpTop + 'px';
		dragDropDiv.style.left = tmpLeft + 'px';
	}

	return false;
}

function reOrder() {
// ----------------------------------------------------------------------------------
	var wrapper = document.getElementById('imageListWrapper');
	var imgBox = wrapper.getElementsByTagName('DIV');
	var imgNum = 0;
	var breakLine = [];
	var uploadImg = 0;

	for (var i=0; i < imgBox.length; i++) {
		if (imgBox[i].id.indexOf('imgBox') == -1) continue;
		imgBox[i].className = 'imageBox';
		imgBox[i].firstChild.className = 'imageBox_theImage';

		if (imgBox[i].firstChild.firstChild != null) {
			uploadImg++;
		}

		switch (imgNum) {
			case 4 :
			case 8 :
			case 11 :
				breakLine.push(imgBox[i].id);
				break;
		}
		imgNum++;
	}

	for (var i=0; i<breakLine.length; i++) {
		var spacer = document.getElementById('spacer' + (i+1));
		wrapper.insertBefore(spacer, document.getElementById(breakLine[i]));
		if (i==2) wrapper.appendChild(spacer);
	}

	resetImageCount(uploadImg);
}

function dragDropEnd() {
// ----------------------------------------------------------------------------------
	readyToMove = false;
	moveTimer = -1;
	dragDropDiv.style.display = 'none';
	insertionMarker.style.display = 'none';
		
	if (destinationObject && destinationObject != activeImage) {
		var parentObj = destinationObject.parentNode;
		var chkObj = destinationObject.previousSibling;
		var turn = false;

		if (chkObj == null) {
			chkObj = document.getElementById('imageListWrapper').firstChild;
			turn = true;
		}

		if (chkObj.id.indexOf('spacer') != -1) {
			chkObj = chkObj.previousSibling;
		}

		if (chkObj.firstChild.firstChild == null) {
			reOrder();
			return;
		}

		if (chkObj && chkObj.id != null) {
			while (chkObj) {
				if (chkObj.firstChild.firstChild != null) {
					break;
				}
				chkObj = chkObj.previousSibling;
			}
			destinationObject = turn ? chkObj : chkObj.nextSibling;
		}

		parentObj.insertBefore(activeImage, destinationObject);
		reOrder();

		activeImage.className = 'imageBox';
		activeImage = false;
		destinationObject = false;
		getDivCoordinates();

		return false;
	}

	return true;
}
	
function dragDropMove(e) {
// ----------------------------------------------------------------------------------
	if (moveTimer == -1)
		return;

	if(MSIE)
		e = event;

	var leftPos = e.clientX + document.documentElement.scrollLeft - eventDiff_x;
	var topPos = e.clientY + document.documentElement.scrollTop - eventDiff_y;
	dragDropDiv.style.top = topPos + 'px';
	dragDropDiv.style.left = leftPos + 'px';
		
	leftPos = leftPos + eventDiff_x;
	topPos = topPos + eventDiff_y;
		
	if (e.button != 1 && MSIE)
		dragDropEnd();

	var elementFound = false;

	for (var prop in divXPositions) {
		if (divXPositions[prop].className == 'clear')
			continue;

		if  (divXPositions[prop] / 1 < leftPos / 1 && 
			(divXPositions[prop] / 1 + divWidth[prop] * 0.7) > leftPos / 1 && 
			 divYPositions[prop] / 1 < topPos / 1 && 
			(divYPositions[prop] / 1 + divWidth[prop]) > topPos / 1)
		{
			if (MSIE) {
				offsetX = offsetX_marker;
				offsetY = offsetY_marker;
			}
			else {
				offsetX = firefoxOffsetX_marker;
				offsetY = firefoxOffsetY_marker;
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
	
function getDivCoordinates() {
// ----------------------------------------------------------------------------------
	var imgBox = document.getElementById('imageListWrapper').getElementsByTagName('DIV');

	for (var i=0; i < imgBox.length; i++) {	
		if (imgBox[i].className == 'imageBox' || 
			imgBox[i].className == 'imageBoxHighlighted' && imgBox[i].id)
		{
			divXPositions[imgBox[i].id] = getLeftPos(imgBox[i]);	
			divYPositions[imgBox[i].id] = getTopPos(imgBox[i]);			
			divWidth[imgBox[i].id]  = imgBox[i].offsetWidth;		
			divHeight[imgBox[i].id] = imgBox[i].offsetHeight;	
		}		
	}
}
	
function saveImageOrder() {
// ----------------------------------------------------------------------------------
	var rData = [];
	var objects = document.getElementById('imageListWrapper').getElementsByTagName('DIV');

	for (var i=0; i < objects.length; i++) {
		if (objects[i].className == 'imageBox' || 
			objects[i].className == 'imageBoxHighlighted')
		{
			rData.push(objects[i].id);
		}
	}

	return rData;
}

function initGallery() {
// ----------------------------------------------------------------------------------
	var imgBox = document.getElementById('imageListWrapper').getElementsByTagName('DIV');
	for (var i=0; i < imgBox.length; i++) {
		if (imgBox[i].className == 'imageBox_theImage') {
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

function create_request_object(params) {
// ----------------------------------------------------------------------------------
  var http_request = false;
  if (window.XMLHttpRequest) {
    http_request = new XMLHttpRequest();
    if (http_request.overrideMimeType) {
      http_request.overrideMimeType('text/xml');
    }
  }
  else if (window.ActiveXObject) {
    try {
      http_request = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e) {
      try {
        http_request = new ActiveXObject("Microsoft.XMLHTTP");
      }
      catch (e) {}
    }
  }

  if (!http_request) {
    return false;
  }

  http_request.onreadystatechange = function() { handle_response(http_request); };
  http_request.open("GET", params, true);
  http_request.send(null);
}

function handle_response (http_request) {
// ----------------------------------------------------------------------------------
  if(http_request.readyState == 4){
    if (http_request.status == 200) {
      var response = http_request.responseText;
      if (response) {
		  return true;
      }
    }
  } 
}

function doSubmit() {
// ----------------------------------------------------------------------------------
	var el = document.getElementById('imageListWrapper').getElementsByTagName('DIV');
	var imageArray = [];
	var num = 0;
	var fm_align = document.getElementById('fm_alignment').alignment;
	var img_align = 'top';

	for (var i=0; i < fm_align.length; i++) {
		if (fm_align[i].checked) {
			img_align = fm_align[i].value;
			break;
		}
	}

	for (var i=0; i < el.length; i++) {
		var imgBox = el[i];
		if (imgBox.className != 'imageBox_theImage')
			continue;

//----------------------------------------------------------------------------
// 
		if (imgBox.firstChild != null) {
			imageArray[num] = new Object();
			imageArray[num]['width'] = imageCompletedList[imgBox.id].width;
			imageArray[num]['height'] = imageCompletedList[imgBox.id].height;
			imageArray[num]['src'] = imgBox.firstChild.src;
			imageArray[num]['info'] = imageCompletedList[imgBox.id].info;

			if (img_align == 'break' ) {
				imageArray[num]['alt'] = "break";
			}
			else {
				imageArray[num]['alt'] = "";
				imageArray[num]['align'] = img_align;
			}

			num++;
		}
	}

	if (imageArray.length > 0)
		oEditor.doInsertImage(imageArray);

	popupClose();
}
