
var cheditorPath = "../";

var lightBoxCssPath = cheditorPath + "css/imageUtil.css"; 
var lightBoxIconPath = cheditorPath + "icons/imageutil/";
var lightBoxPath = cheditorPath + "utils/imageUtil.js";

function runLightbox() {
	var head = document.getElementsByTagName('head');
	var css = head[0].appendChild(document.createElement('link'));
 	css.setAttribute('type', 'text/css');
 	css.setAttribute('rel', 'stylesheet');
 	css.setAttribute('media', 'all');
 	css.setAttribute('href', lightBoxCssPath);

	hs.iconsPath = lightBoxIconPath;

	var img = document.images;
	var container = document.getElementById('lightbox-container');
	if (typeof container == 'undefined') {
		return;
	}
		
	for (var i=0; i<img.length; i++) {
		if (img[i].className != 'chimg_photo') continue;
		if (typeof img[i].parentNode != 'undefined' && img[i].parentNode.tagName != 'A') {
			var a = document.createElement('A');
			a.id = "lightbox_" + i;
			a.href = img[i].src;
			a.className = "imageUtil";
			
			var alt = img[i].getAttribute("alt");
			
			a.onclick = function() { 
				this.parentNode.insertBefore(container, this);
				return hs.run(this, alt);
			};
			img[i].parentNode.insertBefore(a, img[i]);
			a.appendChild(img[i]);
		}
	}
}

if (window.addEventListener) {
	window.addEventListener('DOMContentLoaded', runLightbox, false);
	window.addEventListener('load', runLightbox, false);
}
else if (window.attachEvent)
	window.attachEvent('onload', runLightbox);
else
	window.onload = runLightbox;
