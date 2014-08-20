$(function() {
    var is_chrome = navigator.userAgent.toLowerCase().indexOf("chrome") > -1;
    if(!(g5_is_mobile && is_chrome)) {  //모바일 크롬에서 먹통되는 현상이 있음
        $("html, body").addClass("daum_juso_body");
    }
});

var el_id = document.getElementById("daum_juso_wrap");
new daum.Postcode({
    oncomplete: function(data) {
        var address1 = data.address1,
            address2 = "";
        if(data.addressType == "R"){        //도로명이면
            address2 = data.address2;
        }
        put_data2(data.postcode1, data.postcode2, address1, "", address2, data.addressType);
    },
    width : "100%",
    height : "100%"
}).embed(el_id);