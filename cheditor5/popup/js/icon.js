// ================================================================
//                       CHEditor
// ----------------------------------------------------------------
// Author: Na Chang-Ho (chna@chcode.com)
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2011 CHSOFT
// ================================================================
var oEditor = null;
var button = [ { alt : "", img : 'cancel.gif', cmd : popupClose } ];

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;
	
	var dlg = new Dialog(oEditor);	
	showContents();
	dlg.showButton(button);
	dlg.setDialogHeight();
}

function insertIcon() {
	this.removeAttribute("className");
	this.removeAttribute("class");
	this.style.margin = '1px 4px';
  	oEditor.insertHtmlPopup(this.cloneNode(false));
  	popupClose();
}

function popupClose() {
    oEditor.popupWinClose();
}

function showContents() {
	var block = document.getElementById('imgBlock');
	var path = oEditor.config.iconPath + 'em/';
	var num = 80;
	
	for (var i=40; i<num; i++) {
		if (i > 40 && (i % 10) == 0) {
			var br = document.createElement('br');
			block.appendChild(br);
		}
		
		var img = new Image();
		img.src = path + (i+1) + ".gif";
		img.style.width = '16px';
		img.style.height = '16px';
		img.style.margin = '5px 4px';
		img.style.verticalAlign = 'middle';
		img.setAttribute('alt', '');
		img.setAttribute('border', 0);
		img.className = 'handCursor';
		img.onclick = insertIcon;
		block.appendChild(img);
	}
}
