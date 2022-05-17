// ================================================================
//                            CHEditor 5
// ================================================================
var oEditor = null,
    button = [
        { alt : "", img : 'submit.gif', cmd : returnSelected },
        { alt : "", img : 'cancel.gif', cmd : popupClose }
    ];

function resetValues() {
    document.getElementById('fm_link_value').value = 'http://';
    document.getElementById("fm_title").value = '';
}

function popupClose() {
    oEditor.popupWinCancel();
}

function updateProtocol() {
    var selectedItem = protocolSel.selectedIndex,
        selectedItemValue = protocolSel.options[selectedItem].value,
        inputtedText = document.getElementById("fm_link_value").value,
        datum,
        protocol = inputtedText.split(":");

    if (protocol[1]) {
        datum = protocol[1].replace(/^\/\//, "");
        datum = datum.replace(/\\/, "");
        datum = datum.replace(/^\//, "");
    } else {
        if (inputtedText.indexOf(":") > 0) {
            datum = "";
        } else {
            datum = protocol[0].replace(/^\/\/\//, "//");
        }
    }

    document.getElementById("fm_link_value").value = selectedItemValue + datum;
    document.getElementById("fm_link_value").focus();
}

function returnSelected() {
    var text, target = '', title = '';

    if (document.getElementById("fm_link_value").value !== "") {
        text = document.getElementById("fm_link_value").value;
    } else {
        alert("링크 URL을 입력하여 주십시오.");
        return false;
    }

    if (document.getElementById("fm_target").value !== "") {
        target = document.getElementById("fm_target").value;
    }

    if (document.getElementById("fm_title").value !== "") {
        title = document.getElementById("fm_title").value;
    }

    if ((/^(http|https|file|ftp|mailto|gopher|news|telnet|):\/\//i.test(text)) === false &&
            (/^(wias|javascript):/i.test(text) === false))
    {
        text = "http://" + text;
    }

    oEditor.hyperLink(text, target, title);
    oEditor.popupWinClose();
}

function getSelected() {
    var rng = oEditor.range, link = null, protocol, protocolSel, i, oldTarget, targetSel, j;

    if (window.getSelection) {
        link = oEditor.getElement(rng.startContainer, "A");
    } else {
        link = rng.parentElement ? oEditor.getElement(rng.parentElement(), "A") : oEditor.getElement(rng.item(0), "A");
    }

    if (link === null || link.nodeName.toLowerCase() !== 'a') {
        return;
    }

    protocol = link.href.split(":");

    if (protocol[0]) {
        protocolSel = document.getElementById("fm_protocol");
        for (i = 0; i < protocolSel.length; i++) {
            if (protocolSel[i].value.indexOf(protocol[0].toLowerCase()) !== -1) {
                oldTarget = link.target;
                targetSel = document.getElementById("fm_target");

                if (oldTarget) {
                    for (j = 0; j < targetSel.length; j++) {
                        if (targetSel[j].value === oldTarget.toLowerCase()) {
                            targetSel[j].selected = true;
                            break;
                        }
                    }
                } else {
                    targetSel[0].selected = true;
                }

                protocolSel[i].selected = true;

                if (link.title) {
                    document.getElementById("fm_title").value = link.title;
                }
                break;
            }
        }
    }
    document.getElementById("fm_link_value").value = link.href;
}

function init(dialog) {
    oEditor = this;
    oEditor.dialog = dialog;

    resetValues();
    getSelected();

    var dlg = new Dialog(oEditor);
    dlg.showButton(button);
    dlg.setDialogHeight();
}
