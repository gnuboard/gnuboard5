var reg_mb_id_check = function() {
    var result = "";
    $.ajax({
        type: "POST",
        url: member_skin_path+"/ajax_mb_id_check.php",
        data: {
            "reg_mb_id": encodeURIComponent($("#reg_mb_id").val())
        },
        cache: false,
        async: false,
        success: function(data) {
            result = data;
        }
    });
    return result;
}


var reg_mb_nick_check = function() {
    var result = "";
    $.ajax({
        type: "POST",
        url: member_skin_path+"/ajax_mb_nick_check.php",
        data: {
            "reg_mb_nick": ($("#reg_mb_nick").val()),
            'reg_mb_id': encodeURIComponent($('#reg_mb_id').val())
        },
        cache: false,
        async: false,
        success: function(data) {
            result = data;
        }
    });
    return result;
}


var reg_mb_email_check = function() {
    var result = "";
    $.ajax({
        type: 'POST',
        url: member_skin_path+'/ajax_mb_email_check.php',
        data: {
            'reg_mb_email': $('#reg_mb_email').val(),
            'reg_mb_id': encodeURIComponent($('#reg_mb_id').val())
        },
        cache: false,
        async: false,
        success: function(data) {
            result = data;
        }
    });
    return result;
}