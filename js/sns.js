$(function() {
    $(".share-facebook").click(function() {
        window.open($(this).attr("href"), "win_facebook", "menubar=1,resizable=1,width=600,height=400");
        return false;
    });

    $(".share-twitter").click(function() {
        window.open($(this).attr("href"), "win_twitter", "menubar=1,resizable=1,width=600,height=350");
        return false;
    });
});