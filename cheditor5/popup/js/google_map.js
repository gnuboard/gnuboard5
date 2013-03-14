// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2011 CHSOFT
// ================================================================
var button = [
    { alt : "", img : 'submit.gif', cmd : doSubmit },
	{ alt : "", img : 'cancel.gif', cmd : popupClose }
];

var oEditor = null;
var googleMapKey = null;
var center_lat = 0;
var center_lng = 0;
var setZoom = 4;
var marker_lat = 0;
var marker_lng = 0;
var currentName = { '지도' : 'map',
				'중첩' : 'hybrid',
				'위성' : 'satellite',
				'지형' : 'satellite' };
var mapType;

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;
	googleMapKey = oEditor.googleMapKey;
	
	var dlg = new Dialog(oEditor);
	dlg.showButton(button);
	
	var buttonUrl = oEditor.config.iconPath + 'button/map_address.gif';
	var search = new Image();
	search.src = buttonUrl;
	search.onclick = function() { initMap(14); };
	search.className = 'button';
	document.getElementById('map_search').appendChild(search);
	dlg.setDialogHeight();

}

function doSubmit() {
	var map = new Image();
	if (marker_lat == 0) marker_lat = center_lat;
	if (marker_lng == 0) marker_lng = center_lng;
	
	var mapWidth = 512;
	var mapHeight = 320;
	
	map.setAttribute('width', mapWidth);
	map.setAttribute('height',mapHeight);
	map.style.border = '1px #000 solid';
	
	map.src = "http://maps.google.com/maps/api/staticmap?center=" + center_lat + ',' + center_lng +
			"&zoom=" + setZoom + 
			"&size=" + mapWidth + 'x' + mapHeight +
			"&maptype=" + currentName[mapType] +
			"&markers=" + marker_lat + ',' + marker_lng +
			"&sensor=false";
	oEditor.insertHtmlPopup(map);
   	oEditor.insertHtmlPopup(document.createElement('br'));
	oEditor.setImageEvent(true);
	popupClose();
}

function initMap(zoom) {
	zoom = zoom ? zoom : 14;
	var address = document.getElementById('fm_address').value;
	var map = new GMap2(document.getElementById("map_canvas"));
	var geocoder = new GClientGeocoder();
	geocoder.getLatLng(address,    
			function (point) {      
				if (!point) {        
					alert(address + " 주소를 찾을 수 없습니다.");     
				} 
				else {       
					map.setCenter(point, zoom);
					map.addControl(new GScaleControl());

					map.enableDoubleClickZoom();
					//map.enableContinuousZoom();
					map.enableScrollWheelZoom();
					map.setUIToDefault();

					var marker = new GMarker(point, {draggable: true});
					GEvent.addListener(marker, "dragend", function() {
						marker_lat = marker.getLatLng().lat(); 
						marker_lng = marker.getLatLng().lng(); 
						});

					map.addOverlay(marker);
				}
			});

	GEvent.addListener(map, "maptypechanged", function() {
		mapType = map.getCurrentMapType().getName();
	});
	GEvent.addListener(map, "moveend", function() {
		center_lat = map.getCenter().lat();
		center_lng = map.getCenter().lng();
		setZoom = map.getZoom();
	});
}

function popupClose() {
    oEditor.popupWinClose();
}
