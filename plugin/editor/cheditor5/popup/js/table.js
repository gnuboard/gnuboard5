// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2014 CHSOFT
// ================================================================
var oEditor = null;
var colour = ["ffffcc","ffcc66","ff9900","ffcc99","ff6633","ffcccc","cc9999","ff6699","ff99cc","ff66cc","ffccff","cc99cc","cc66ff","cc99ff","9966cc","ccccff","9999cc","3333ff","6699ff","0066ff","99ccff","66ccff","99cccc","ccffff","99ffcc","66cc99","66ff99","99ff99","ccffcc","33ff33","66ff00","ccff99","99ff00","ccff66","cccc66","ffffff",
              "ffff99","ffcc00","ff9933","ff9966","cc3300","ff9999","cc6666","ff3366","ff3399","ff00cc","ff99ff","cc66cc","cc33ff","9933cc","9966ff","9999ff","6666ff","3300ff","3366ff","0066cc","3399ff","33ccff","66cccc","99ffff","66ffcc","33cc99","33ff99","66ff66","99cc99","00ff33","66ff33","99ff66","99ff33","ccff00","cccc33","cccccc",
              "ffff66","ffcc33","cc9966","ff6600","ff3300","ff6666","cc3333","ff0066","ff0099","ff33cc","ff66ff","cc00cc","cc00ff","9933ff","6600cc","6633ff","6666cc","3300cc","0000ff","3366cc","0099ff","00ccff","339999","66ffff","33ffcc","00cc99","00ff99","33ff66","66cc66","00ff00","33ff00","66cc00","99cc66","ccff33","999966","999999",
              "ffff33","cc9900","cc6600","cc6633","ff0000","ff3333","993333","cc3366","cc0066","cc6699","ff33ff","cc33cc","9900cc","9900ff","6633cc","6600ff","666699","3333cc","0000cc","0033ff","6699cc","3399cc","669999","33ffff","00ffcc","339966","33cc66","00ff66","669966","00cc00","33cc00","66cc33","99cc00","cccc99","999933","666666",
              "ffff00","cc9933","996633","993300","cc0000","ff0033","990033","996666","993366","cc0099","ff00ff","990099","996699","660099","663399","330099","333399","000099","0033cc","003399","336699","0099cc","006666","00ffff","33cccc","009966","00cc66","339933","336633","33cc33","339900","669933","99cc33","666633","999900","333333",
              "cccc00","996600","663300","660000","990000","cc0033","330000","663333","660033","990066","cc3399","993399","660066","663366","330033","330066","333366","000066","000033","003366","006699","003333","336666","00cccc","009999","006633","009933","006600","003300","00cc33","009900","336600","669900","333300","666600","000000"];

var none = '없음';
var whichColor = null;

function popupClose() {
	oEditor.popupWinCancel();
}


function getColor()
{
    var color = this.bgColor;
    var input = document.getElementById("id"+whichColor);
    input.style.backgroundColor = input.value = color;
}

function drawColor() {
    var table, tr, td, insideTable, k = 0, i, j, tr2, td2;

    table = document.createElement('table');
    table.cellPadding = 0;
    table.cellSpacing = 0;
    table.border = 0;
    table.align = 'center';
	tr = table.insertRow(0);
	td = tr.insertCell(0);
	td.style.backgroundColor = '#fff';

	insideTable = document.createElement('table');
	insideTable.border = 0;
	insideTable.cellSpacing = 1;
	insideTable.cellPadding = 0;
	insideTable.align = 'center';

    var onMouseOver = function() { this.className = 'colorCellMouseOver'; };
    var onMouseOut = function() { this.className = 'colorCellMouseOut'; };

    for (i = 0; i < 6; i++) {
        tr2 = insideTable.insertRow(i);
        for (j = 0; j < 36; j++) {
            td2 = tr2.insertCell(j);
            td2.setAttribute('bgColor', '#' + colour[k]);
            td2.className = 'colorCellMouseOut';
            td2.onclick = getColor;
            td2.appendChild(document.createTextNode('\u00a0'));
            td2.onmouseover = onMouseOver;
            td2.onmouseout = onMouseOut;
            k++;
        }
    }

    td.appendChild(insideTable);
    document.getElementById('colorWrapper').appendChild(table);
}

function setColor(which) {
    whichColor = which;
}

function doSubmit()
{
    var rows, cols, border, width, widthType, height, cellpd, cellsp, bgcolor, align, bordercolor, cssclass, cssid, cellWidth;
    rows = document.getElementById("numrows").value;
    rows = parseInt(oEditor.trimSpace(rows), 10);
    if (isNaN(rows)) {
    	rows = 0;
    }

    cols = document.getElementById("numcols").value;
    cols = parseInt(oEditor.trimSpace(cols), 10);
    if (isNaN(cols)) {
    	cols = 0;
    }
        
    border = document.getElementById("bordersize").value;
    border = parseInt(oEditor.trimSpace(border), 10);
    if (isNaN(border)) {
        border = 0;
    }
    
    width = document.getElementById("width").value;
    width = parseInt(oEditor.trimSpace(width), 10);
    widthType = document.getElementById("widthtype").value;
    if (isNaN(width)) {
        cellWidth = width = null;
    }
    else {
        cellWidth = parseInt(width / cols, 10) + widthType;
        width += widthType;
    }

    height = document.getElementById("height").value;
    height = parseInt(oEditor.trimSpace(height), 10);
    if (isNaN(height)) {
    	height = null;
    }
    else {
    	height += document.getElementById("heighttype").value;
    }
    
    cellpd = document.getElementById("cellpd").value;
    cellpd = parseInt(oEditor.trimSpace(cellpd), 10);
    if (isNaN(cellpd)) {
    	cellpd = 0;
    }
    
    cellsp = document.getElementById("cellsp").value;
    cellsp = parseInt(oEditor.trimSpace(cellsp), 10);
    if (isNaN(cellsp)) {
    	cellsp = 0;
    }
    
    bgcolor = document.getElementById("idbgcolor").value;
    bgcolor = oEditor.trimSpace(bgcolor);
    if (bgcolor === none || bgcolor === '') {
    	bgcolor = null;
    }
    
    align = document.getElementById("talign").value;
    if (align === 'none') {
    	align = null;
    }
    
    bordercolor = document.getElementById("idbordercolor").value;
    bordercolor = oEditor.trimSpace(bordercolor);
    if (bordercolor === '') {
    	bordercolor = null;
    }

    cssclass = document.getElementById("cssClass").value;
    cssclass = oEditor.trimSpace(cssclass);
    if (cssclass === '') {
        cssclass = null;
    }
    
    cssid = document.getElementById("cssId").value;
    cssid = oEditor.trimSpace(cssid);
    if (cssid === '') {
        cssid = null;
    }
    
    if (rows < 1 || cols < 1) {
    	alert('표의 줄 또는 칸 개수가 1개 이상 필요합니다.');
    	return;
    }
    
    var caption = document.getElementById('tableCaption');
    var captionValue = oEditor.trimSpace(caption.value);
    var summary = document.getElementById('tableSummary');
    var summaryValue = oEditor.trimSpace(summary.value);
    var header =  document.getElementById('tableHeader').value;
    var table = document.createElement("table");

    var createHeadCell = function(scope) {
        var cell = document.createElement('th');
        cell.setAttribute('scope', scope);
        return cell;
    };
    
    var oHead = document.createElement('thead');
    var oBody = document.createElement('tbody');

    if (border) {
        if (bordercolor) {
            table.style.borderColor = oEditor.colorConvert(bordercolor, 'rgb');
        }
        table.style.borderStyle = 'solid';
        table.style.borderWidth = border + 'px';
    }
    
    table.style.borderCollapse = "collapse";
    
    var i, row, tr, j, cell;
    for (i=0; i < rows; i++) {
        tr = document.createElement('tr');
        if ((header === 'col' || header === 'all') && i===0) {
            row = oHead.appendChild(tr);
        }
        else {
            row = oBody.appendChild(tr);
        }

        for (j=0; j < cols; j++) {
            if (header === 'col' && i===0) {
                cell = createHeadCell('col');
            }
            else if (header === 'all') {
                if (j === 0) {
                    cell = createHeadCell('row');
                }
                else if (i === 0 && j > 0) {
                    cell = createHeadCell('col');
                }
                else {
                    cell = document.createElement('td');
                }
            }
            else if (header === 'row' && j === 0) {
                cell = createHeadCell('row');
            }
            else {
                cell = document.createElement('td');
            }

            if (border) {
                cell.style.borderStyle = 'solid';
                cell.style.borderWidth = table.style.borderWidth;
                cell.style.borderColor = table.style.borderColor;
            }
//            cell.setAttribute("width", cellWidth);
            cell.appendChild(document.createTextNode('\u00a0'));
            row.appendChild(cell);
        }
    }

    if (oHead.hasChildNodes()) {
        table.appendChild(oHead);
    }
   
    table.appendChild(oBody);
    
    if (summaryValue !== '') {
        table.setAttribute('summary', summaryValue);
    }
    if (width) {
        table.style.width = width;
    }
    if (height) {
        table.style.height = height;
    }
    if (align) {
        table.setAttribute("align", align);
    }
    if (bgcolor) {
        table.setAttribute("bgcolor", bgcolor);
    }

    table.setAttribute("cellpadding", cellpd);
    table.setAttribute("cellspacing", cellsp);

    if (captionValue !== '') {
        var hideCaption, tableCaption;
        tableCaption = table.createCaption();
        tableCaption.appendChild(document.createTextNode(captionValue));
        
        hideCaption = document.getElementById('hideCaption');
        if (hideCaption.checked === true) {
            tableCaption.style.visibility = 'hidden';
            tableCaption.style.overFlow = 'hidden';
            tableCaption.style.lineHeight = '0px';
            tableCaption.style.position = 'absolute';
            tableCaption.style.display = 'none';
        }
    }

    table.id = oEditor.makeRandomString();
    oEditor.insertHtmlPopup(table.cloneNode(true));
    var newTable = oEditor.$(table.id);
    newTable.removeAttribute('id');
    
    if (cssclass) {
        newTable.className = cssclass;
    }
    if (cssid) {
        newTable.id = cssid;
    }
    
    var focusCell = newTable.getElementsByTagName('th')[0];
    if (oEditor.undefined(focusCell)) {
        focusCell = newTable.getElementsByTagName('td')[0];
    }
    
    if (oEditor.getBrowser().msie) {
        var cursor = oEditor.doc.body.createTextRange();
        cursor.moveToElementText(focusCell);
        cursor.collapse(false);
        cursor.select();
        oEditor.backupRange(oEditor.getRange());
    }
    else {
        var selection = oEditor.getSelection();
        var range = oEditor.getRange();
        range.selectNodeContents(focusCell);
        range.collapse(false);
        selection.removeAllRanges();
        selection.addRange(range);
    }

    oEditor.popupWinClose();
}

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;

    var button = [ { alt : "", img : 'submit.gif', cmd : doSubmit },
               { alt : "", img : 'cancel.gif', cmd : popupClose } ];

	var dlg = new Dialog(oEditor);
	dlg.showButton(button);
	dlg.setDialogHeight();
}
