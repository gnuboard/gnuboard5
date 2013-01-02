function chk_tcaptcha(user_answer, user_token)
{
    if (typeof(user_answer) == "undefined") return false;
    if (typeof(user_token ) == "undefined") return false;

    var error = false;
    $.ajax({
        type: "POST",
        url: g4_path+"/plugin/tcaptcha/chk_answer.ajax.php",
        async: false,
        data: { 
            "user_answer": user_answer.value, 
            "user_token" : user_token.value 
        },
        dataType: "json",
        success: function(data, textStatus, jqXHR) {
            error = data.error;
            if (data.token) {
                $("#user_token").val(data.token);
            }
        }
    });

    if (error) {
        alert(error);
        user_answer.select();
        return false;
    }
    return true;
}

$(function() {
    $("#tcaptcha").click(function() {
        $.ajax({
            url: g4_path+"/plugin/tcaptcha/run.php?t="+(new Date).getTime(),
            dataType: "json",
            success: function(data, textStatus, jqXHR) {
                $("#tcaptcha").html(data.tcaptcha);
                $("#user_token").val(data.token);
            }
        })
    })
    .css("cursor", "pointer");
});