if (typeof(CAPSLOCK_JS) == 'undefined') // 한번만 실행
{
    if (typeof g4_path == 'undefined')
        alert('g4_path 변수가 선언되지 않았습니다. js/capslock.js');

    var CAPSLOCK_JS = true;

    var capslock_delay = 3000; // "CapsLock 이 켜져 있습니다." 이미지를 몇초간 출력할 것인지?
    var capslock_left = -4; // CaplsLock 이미지의 X 좌표
    var capslock_top = 0; // CaplsLock 이미지의 Y 좌표
    function check_capslock(e, elem_id) {
        var myKeyCode=0;
        var myShiftKey=false;

        if ( document.all ) {                   // Internet Explorer 4+
            myKeyCode=e.keyCode; 
            myShiftKey=e.shiftKey;
        } else if ( document.layers ) {         // Netscape 4
            myKeyCode=e.which;  
            myShiftKey=( myKeyCode == 16 ) ? true : false;
        } else if ( document.getElementById ) { // Netscape 6
            myKeyCode=e.which; 
            myShiftKey=( myKeyCode == 16 ) ? true : false;
        }

        // Upper case letters are seen without depressing the Shift key, therefore Caps Lock is on
        if ( ( myKeyCode >= 65 && myKeyCode <= 90 ) && !myShiftKey ) {
            set_capslock_on(elem_id);
        // Lower case letters are seen while depressing the Shift key, therefore Caps Lock is on
        } else if ( ( myKeyCode >= 97 && myKeyCode <= 122 ) && myShiftKey ) {
            set_capslock_on(elem_id);
        }
    }

    function set_capslock_on(elem_id) {
        set_capslock_info_position(elem_id);
        document.getElementById("capslock_info").style.display  = "inline";
        setTimeout("set_capslock_off()", capslock_delay);
    }

    function set_capslock_off(elem_id) {
        document.getElementById("capslock_info").style.display  = "none";
    }

    function set_capslock_info_position(elem_id) {
        var o = document.getElementById("capslock_info");
        var ref = document.getElementById(elem_id);
        //var s = ""; for (i in ref) {s =  s + i + " "; } alert(s);
        if ( typeof(o)=="object" && typeof(ref)=="object" ) {
            var x = get_real_left(ref);
            var y = get_real_top(ref);
            //o.style.pixelLeft = x + capslock_left;
            //o.style.pixelTop = y + ref.offsetHeight + capslock_top;
            o.style.left = x + capslock_left;
            o.style.top = y + ref.offsetHeight + capslock_top;
        }
    }

    function get_real_left(obj) {
        if ( obj.offsetParent == null ) return 0;
        return obj.offsetLeft + obj.clientLeft + get_real_left(obj.offsetParent);
    }

    function get_real_top(obj) {
        if ( obj.offsetParent == null ) return 0;
        return obj.offsetTop + obj.clientTop + get_real_top(obj.offsetParent);
    }

    document.write("<div id='capslock_info' style='display:none; position:absolute;'><img src='"+g4_path+"/img/capslock.gif'></div>");
}