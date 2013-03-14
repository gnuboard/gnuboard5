// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2011 CHSOFT
// ================================================================
function Dialog(oEditor) {
	this.oEditor = oEditor;
}

Dialog.prototype.setDialogCss = function() {
	var head = document.getElementsByTagName('head')[0];
	var css = head.appendChild(document.createElement('link'));
	css.setAttribute('type', 'text/css');
	css.setAttribute('rel', 'stylesheet');
	css.setAttribute('media', 'all');
	css.setAttribute('href', this.oEditor.config.cssPath + 'dialog.css');	
};

Dialog.prototype.setDialogHeight = function(height) {
	this.oEditor.dialog.style.height = (typeof height != 'undefined' ? height : document.body.scrollHeight) + 2 + 'px';
};

Dialog.prototype.showButton = function(button) {
	var buttonUrl = this.oEditor.config.iconPath + 'button/';
	var wrapper = document.getElementById("buttonWrapper");
	
	for (var i=0; i < button.length; i++) {
		var img = new Image();
		img.alt = button[i].alt;
		
		if (typeof button[i].hspace != 'undefined')
			img.hspace = button[i].hspace;
		
		img.className = "button";
		img.src = buttonUrl + button[i].img;
		img.onclick = button[i].cmd;
		wrapper.appendChild(img);
	}
};
