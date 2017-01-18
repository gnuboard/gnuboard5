// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2014 CHSOFT
// ================================================================
var button = [
	{ alt : "", img : 'play.gif', cmd : play },
	{ alt : "", img : 'submit.gif', cmd : doSubmit },              
	{ alt : "", img : 'cancel.gif', cmd : popupClose }
];

var oEditor = null;

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;
	
	var dlg = new Dialog(oEditor);
	dlg.showButton(button);
	
	dlg.setDialogHeight();
}

function play()
{
    var file = document.getElementById("fm_linkurl");
    if (!file.value) 
    	return;
    
    var mediaobj = "<embed src='"+file.value+"' autostart='true' loop='true'></embed>";
    var obj = document.getElementById("play");
    obj.innerHTML = mediaobj;
}

function doSubmit()
{
    var file = document.getElementById("fm_linkurl");
    var media = "<embed src='"+file.value+"' autostart='true' loop='true'></embed>";
    oEditor.insertHtmlPopup(media);
    oEditor.popupWinClose();
}

function popupClose() {
    oEditor.popupWinCancel();
}