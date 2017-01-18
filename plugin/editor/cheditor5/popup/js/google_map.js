// ================================================================
//                            CHEditor 5
// ----------------------------------------------------------------
var oEditor = null,
    centerLat = 0,
    centerLng = 0,
    latlng = 0,
    setZoom = 14,
    marker_lat = 0,
    marker_lng = 0,
    mapType = "roadmap",
    map,
    mapWidth = 512,
    mapHeight = 320,
    panorama,
    panoramaVisible = false;

function doSubmit() {
    var mapImg = document.createElement("img"),
        panoramaPitch, panoramaHeading, panoramaZoom, panoramaPosition;

    if (marker_lat === 0) {
        marker_lat = centerLat;
    }
    if (marker_lng === 0) {
        marker_lng = centerLng;
    }

    mapImg.style.width = mapWidth + 'px';
    mapImg.style.height = mapHeight + 'px';
    mapImg.style.border = '1px #000 solid';
    mapImg.setAttribute("alt", "Google Map");
    mapImg.onload = function () {
        oEditor.insertHtmlPopup(mapImg);
        oEditor.setImageEvent(true);
        oEditor.popupWinClose();
    };

    if (panoramaVisible) {
        panoramaPitch = panorama.getPov().pitch;
        panoramaHeading = panorama.getPov().heading;
        panoramaZoom = panorama.getPov().zoom;
        panoramaPosition = panorama.getPosition();

        mapImg.src = "http://maps.googleapis.com/maps/api/streetview?location=" + panoramaPosition +
            "&pitch=" + panoramaPitch +
            "&heading=" + panoramaHeading +
            "&size=" + mapWidth + 'x' + mapHeight +
            "&zoom=" + panoramaZoom +
            "&sensor=false" +
            "&region=KR";
    } else {
        mapImg.src = "http://maps.google.com/maps/api/staticmap?center=" + centerLat + ',' + centerLng +
            "&zoom=" + setZoom +
            "&size=" + mapWidth + 'x' + mapHeight +
            "&maptype=" + mapType +
            //"&markers=" + marker_lat + ',' + marker_lng +
            "&sensor=false" +
            "&language=ko" +
            "&region=KR";
    }
}

function searchAddress() {
    var address = document.getElementById('fm_address').value,
        geocoder = new google.maps.Geocoder();
    //var results, status;
    //var marker = new google.maps.Marker({ 'map': map, 'draggable': true });

    geocoder.geocode({'address' : address},
            function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    centerLat = results[0].geometry.location.lat();
                    centerLng = results[0].geometry.location.lng();
                    latlng = new google.maps.LatLng(centerLat, centerLng);
                    map.setCenter(latlng);
                    map.setZoom(setZoom);
                }
            });
}

function initMap(zoom) {
    var mapOptions = {
        zoom: zoom || setZoom,
        panControl: true,
        zoomControl: true,
        scaleControl: true,
        center: new google.maps.LatLng(37.566, 126.977),
        disableDefaultUI: false,
        streetViewControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
    centerLat = map.getCenter().lat();
    centerLng = map.getCenter().lng();

    google.maps.event.addListener(map, 'dragend', function () {
        centerLat = map.getCenter().lat();
        centerLng = map.getCenter().lng();
    });

    google.maps.event.addListener(map, 'maptypeid_changed', function () {
        mapType = map.getMapTypeId();
    });

    google.maps.event.addListener(map, 'zoom_changed', function () {
        centerLat = map.getCenter().lat();
        centerLng = map.getCenter().lng();
    });

    panorama = map.getStreetView();
    google.maps.event.addListener(panorama, 'visible_changed', function () {
        panoramaVisible = panorama.getVisible();
    });
}

function popupClose() {
    oEditor.popupWinCancel();
}

function init(dialog) {
    oEditor = this;
    oEditor.dialog = dialog;

    var dlg = new Dialog(oEditor),
        button = [
            { alt : "", img : 'submit.gif', cmd : doSubmit },
            { alt : "", img : 'cancel.gif', cmd : popupClose }
        ],
        buttonUrl = oEditor.config.iconPath + 'button/map_address.gif',
        search = new Image();

    dlg.showButton(button);

    search.src = buttonUrl;
    search.onclick = function () {
        searchAddress();
    };
    search.className = 'button';
    document.getElementById('map_search').appendChild(search);
    dlg.setDialogHeight();
}
