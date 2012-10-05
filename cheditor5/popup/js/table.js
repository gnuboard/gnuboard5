// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2011 CHSOFT
// ================================================================
var oEditor = null;
var button = [ { alt : "", img : 'submit.gif', cmd : doSubmit },              
               { alt : "", img : 'cancel.gif', cmd : popupClose } ];
var colour = ["ffffcc","ffcc66","ff9900","ffcc99","ff6633","ffcccc","cc9999","ff6699","ff99cc","ff66cc","ffccff","cc99cc","cc66ff","cc99ff","9966cc","ccccff","9999cc","3333ff","6699ff","0066ff","99ccff","66ccff","99cccc","ccffff","99ffcc","66cc99","66ff99","99ff99","ccffcc","33ff33","66ff00","ccff99","99ff00","ccff66","cccc66","ffffff",
              "ffff99","ffcc00","ff9933","ff9966","cc3300","ff9999","cc6666","ff3366","ff3399","ff00cc","ff99ff","cc66cc","cc33ff","9933cc","9966ff","9999ff","6666ff","3300ff","3366ff","0066cc","3399ff","33ccff","66cccc","99ffff","66ffcc","33cc99","33ff99","66ff66","99cc99","00ff33","66ff33","99ff66","99ff33","ccff00","cccc33","cccccc",
              "ffff66","ffcc33","cc9966","ff6600","ff3300","ff6666","cc3333","ff0066","ff0099","ff33cc","ff66ff","cc00cc","cc00ff","9933ff","6600cc","6633ff","6666cc","3300cc","0000ff","3366cc","0099ff","00ccff","339999","66ffff","33ffcc","00cc99","00ff99","33ff66","66cc66","00ff00","33ff00","66cc00","99cc66","ccff33","999966","999999",
              "ffff33","cc9900","cc6600","cc6633","ff0000","ff3333","993333","cc3366","cc0066","cc6699","ff33ff","cc33cc","9900cc","9900ff","6633cc","6600ff","666699","3333cc","0000cc","0033ff","6699cc","3399cc","669999","33ffff","00ffcc","339966","33cc66","00ff66","669966","00cc00","33cc00","66cc33","99cc00","cccc99","999933","666666",
              "ffff00","cc9933","996633","993300","cc0000","ff0033","990033","996666","993366","cc0099","ff00ff","990099","996699","660099","663399","330099","333399","000099","0033cc","003399","336699","0099cc","006666","00ffff","33cccc","009966","00cc66","339933","336633","33cc33","339900","669933","99cc33","666633","999900","333333",
              "cccc00","996600","663300","660000","990000","cc0033","330000","663333","660033","990066","cc3399","993399","660066","663366","330033","330066","333366","000066","000033","003366","006699","003333","336666","00cccc","009999","006633","009933","006600","003300","00cc33","009900","336600","669900","333300","666600","000000"];

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;
	
	var dlg = new Dialog(oEditor);
	dlg.showButton(button);
	dlg.setDialogHeight();
}

function popupClose() {
	oEditor.popupWinClose();
}

function drawColor(el)
{
    var table = document.createElement('table');
    table.cellPadding = 0;
    table.cellSpacing = 0;
    table.border = 0;
	var tr = table.insertRow(0);
	var td = tr.insertCell(0);
	td.style.backgroundColor = '#000';

	var insideTable = document.createElement('table');
	insideTable.border = 0;
	insideTable.cellSpacing = 1;
	insideTable.cellPadding = 0;
	insideTable.align = 'center';
    
    var k = 0;

    for (var i = 0; i < 6; i++) {
        var tr2 = insideTable.insertRow(i);
        for (var j = 0; j < 36; j++) {
            var td2 = tr2.insertCell(j);
            td2.setAttribute('bgColor', '#' + colour[k]);
            td2.className = el;
            td2.style.width = '9px';
            td2.style.height = '9px';
            td2.onclick = getColor;
            k++;
        }
    }

    td.appendChild(insideTable);
    document.getElementById(el + '_wrapper').appendChild(table);
}

function getColor()
{
    var color = this.bgColor;
    var input = document.getElementById("id"+this.className);
    input.style.backgroundColor = input.value = color.toUpperCase();
}

function doSubmit()
{
    var rows = parseInt(document.getElementById("numrows").value);
    if (isNaN(rows))
    	rows = 0;
    
    var cols = parseInt(document.getElementById("numcols").value);
    if (isNaN(cols))
    	cols = 0;
    
    var border  = parseInt(document.getElementById("bordersize").value);
    if (isNaN(border))
    	border = 0;
    
    var width = document.getElementById("width").value;
    if (document.getElementById("widthtype").value == 'none')
    	width = null;
    else {
    	width = isNaN(width) ? null : parseInt(width) + document.getElementById("widthtype").value;
    }
    
    var height = document.getElementById("height").value;
    if (document.getElementById("heighttype").value == 'none')
    	height = null;
    else {
    	height = isNaN(height) ? null : height + document.getElementById("heighttype").value;
    }
    
    var cellpd = parseInt(document.getElementById("cellpd").value);
    if (isNaN(cellpd))
    	cellpd = 0;
    
    var cellsp = parseInt(document.getElementById("cellsp").value);
    if (isNaN(cellsp))
    	cellsp = 0;
    
    var bgcolor = document.getElementById("idbgcolor").value;
    bgcolor = oEditor.trimSpace(bgcolor);
    if (bgcolor == '--' || bgcolor == '')
    	bgcolor = null;
    
    var align = document.getElementById("talign").value;
    if (align == 'none')
    	align = null;
    
    var bordercolor = document.getElementById("idbordercolor").value;
    bordercolor = oEditor.trimSpace(bordercolor);
    if (bordercolor == '')
    	bordercolor = null;

    if (rows < 1 || cols < 1) {
    	alert('테이블 행 또는 열 개수를 바르게 입력하여 주십시오.');
    	return;
    }
    
    if (rows > 0 && cols > 0) {
        var table = document.createElement("table");
        
        if (border) table.setAttribute("border", border);
        if (width) 	table.setAttribute("width", width);
        if (height) table.setAttribute("height", height);
        if (align) table.setAttribute("align", align);
        
        table.style.borderStyle = 'solid';
        table.setAttribute("cellpadding", cellpd);
        table.setAttribute("cellspacing", cellsp);
        table.style.borderCollapse = "collapse";

        if (bgcolor) table.setAttribute("bgcolor", bgcolor);
        if (bordercolor) table.setAttribute("bordercolor", bordercolor);

        //var ie = navigator.userAgent.toLowerCase().indexOf("msie") != -1;
        //var tdWidth = (cols > 1) ? parseInt(100 / cols) : null; 

        for (var i=0; i < rows; i++) {
            var tr = table.insertRow(i);
            for (var j=0; j < cols; j++) {
                var td = tr.insertCell(j);
                /*if (tdWidth)
                	td.setAttribute("width", tdWidth + "%");*/
                td.innerHTML = '&nbsp;';
                tr.appendChild(td);
            }
        }
        
       	oEditor.insertHtmlPopup(table.cloneNode(true));
    }
    
    popupClose();
}