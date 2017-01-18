// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2014 CHSOFT
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

var none = '없음';
var modifyTable;
var beforeHeaderType;
var whichColor = null;

function popupClose() {
	oEditor.popupWinCancel();
}

function isError() {
	alert('표 정보를 얻을 수 없습니다. 수정하실 표을 다시 한 번 선택해 주십시오.');
	popupClose();
}

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;
	
	var dlg = new Dialog(oEditor);
	dlg.showButton(button);
	dlg.setDialogHeight();

  	var rng = oEditor.range, pNode;

  	if (oEditor.W3CRange) {
        pNode = rng.commonAncestorContainer;
        if (!rng.collapsed &&
            rng.startContainer === rng.endContainer &&
            rng.startOffset - rng.endOffset < 2 &&
            rng.startContainer.hasChildNodes())
        {
            pNode = rng.startContainer.childNodes[rng.startOffset];
        }

        while (pNode.nodeType === 3) {
            pNode = pNode.parentNode;
        }
        
        if (pNode.nodeName !== 'TD' && pNode.nodeName !== 'TH' && pNode.nodeName !== 'CAPTION' && pNode.nodeName !== 'TABLE')
        {
            isError();
            return;
        }
  	}
  	else {
  		if (rng.item) {
  			pNode = rng.item(0);
  			if (pNode.nodeName.toLowerCase() !== 'table') {
  				isError();
  				return;
  			}
  		}
  		else {
  			pNode = rng.parentElement();
        }
  	}
	
  	while (pNode && pNode.nodeName.toLowerCase() !== 'table') {
  		pNode = pNode.parentNode;
    }

  	if (pNode.nodeName.toLowerCase() !== 'table') {
  		isError();
  		return;
  	}

    modifyTable = pNode;
    var border, el_size, fm_size, el_type, fm_type, cellpd, cellsp, bgcolor, idbgcolor,
        bordercolor, idbordercolor, captionValue, summaryValue, caption, captionInput, summary;
    
    border = modifyTable.getAttribute('border');
    if (!border || isNaN(border)) {
        border = parseInt(modifyTable.style.borderWidth, 10);
        if (!border) {
            border = 0;
        }
    }
    document.getElementById("bordersize").value = border;
    
    if (modifyTable.className !== '') {
        document.getElementById('cssClass').value = modifyTable.className;
    }
    if (modifyTable.id !== '') {
        document.getElementById('cssId').value = modifyTable.id;
    }
    
    el_size = modifyTable.getAttribute('width');
    if (!el_size) {
        el_size = modifyTable.style.width;
    }
    
    fm_size = document.getElementById("width");
    el_type = 'px';
    fm_type = document.getElementById("widthtype");
    
    if (el_size) {
    	el_type = (/%$/.test(el_size)) ? '%' : 'px';
    	el_size = parseInt(el_size, 10);
    	if (isNaN(el_size)) {
            el_size = '';
        }
    }
    else {
    	el_size = '';
    }

    fm_size.value = el_size;
    fm_type.value = el_type;
    
    el_size = modifyTable.getAttribute('height');
    if (!el_size) {
        el_size = modifyTable.style.height;
    }
    fm_size = document.getElementById("height");
    el_type = 'px';
    fm_type = document.getElementById("heighttype");
    
    if (el_size) {
    	el_type = (/\%$/.test(el_size)) ? '%' : 'px';
    	el_size = parseInt(el_size, 10);
    	if (isNaN(el_size)) {
            el_size = '';
        }
    }
    else {
    	el_size = '';
    }

    fm_size.value = el_size;
    fm_type.value = el_type;
    
    fm_type = modifyTable.getAttribute('align');
    if (!fm_type) {
        fm_type = 'none';
    }
	document.getElementById("talign").value = fm_type;
	
    cellpd = modifyTable.getAttribute('cellpadding');
    if (isNaN(cellpd)) {
        cellpd = 0;
    }
    document.getElementById("cellpd").value = cellpd || 0;
    
    cellsp = modifyTable.getAttribute('cellspacing');
    if (isNaN(cellsp)) {
        cellsp = 0;
    }
    document.getElementById("cellsp").value = cellsp || 0;
    
    bgcolor = modifyTable.getAttribute('bgcolor');
    idbgcolor = document.getElementById("idbgcolor");
    if (bgcolor) {
        if (/rgb/.test(bgcolor)) {
            bgcolor = oEditor.colorConvert(bgcolor, 'hex');
        }        
        idbgcolor.value = bgcolor.toLowerCase();
        idbgcolor.style.backgroundColor = idbgcolor.value;
    }
    else {
    	idbgcolor.value = none;
    }

    bordercolor = modifyTable.getAttribute('bordercolor');
    if (!bordercolor) {
        bordercolor = modifyTable.style.borderColor;
        if (bordercolor) {
            bordercolor = oEditor.colorConvert(bordercolor, 'hex');
        }
        else {
            bordercolor = null;
        }
    }
    
    idbordercolor = document.getElementById("idbordercolor");
    if (bordercolor) {
        if (/rgb/.test(bordercolor)) {
            bordercolor = oEditor.colorConvert(bordercolor, 'hex');
        }        
    	idbordercolor.value = bordercolor.toLowerCase();
    	idbordercolor.style.backgroundColor = idbordercolor.value;
    }
    else {
    	idbordercolor.value = none;
    }

    caption = modifyTable.getElementsByTagName('caption')[0];
    if (caption) {
        captionValue = oEditor.trimSpace(caption.innerHTML);
        if (captionValue !== '') {
            captionInput = document.getElementById('tableCaption');
            captionInput.value = captionValue;
            
            if (caption.style.visibility === 'hidden') {
                document.getElementById('hideCaption').checked = 'checked';
            }            
        }
    }
    
    summaryValue = modifyTable.getAttribute('summary');
    if (summaryValue) {
        summaryValue = oEditor.trimSpace(summaryValue);
        if (summaryValue !== '') {
            summary = document.getElementById('tableSummary');
            summary.value = summaryValue;
        }
    }
    
    var tableHeader, rows, i, j, cells, headCol, headRow, rowLength, rowCellLength, cellLength, header, headTagName;
    headCol = headRow = null;
    headTagName = 'th';
    
    tableHeader = document.getElementById('tableHeader');
    rows = (modifyTable.rows && modifyTable.rows.length > 0) ? modifyTable.rows : modifyTable.getElementsByTagName('tr');
    rowLength = rows.length;
    
    document.getElementById('numrows').appendChild(document.createTextNode(rowLength));
    
    if (rowLength > 0) {
        cells = rows[0].cells;
        cellLength = cells.length;
        if (cellLength > 0) {
            for (j=0; j < cellLength; j++) {
                if (cells[j].tagName.toLowerCase() === headTagName) {
                    headCol = 'col';
                }
                else {
                    headCol = null;
                    break;
                }
            }
        }
        
        rowCellLength = 0;
        for (i=0; i < rowLength; i++) {
            headRow = (rows[i].cells[0] && rows[i].cells[0].tagName.toLowerCase() === headTagName) ? 'row' : null;
            if (rowCellLength < rows[i].cells.length) {
                rowCellLength = rows[i].cells.length;
            }
        }
        
        if (headRow && headCol && cellLength === 1) {
            headCol = null;
        }
        document.getElementById('numcols').appendChild(document.createTextNode(rowCellLength));
    }
    
    header = (headCol && headRow) ? 'all' : headCol || headRow || 'none';
    tableHeader.value = beforeHeaderType = header;
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
    var width, widthType, widthValue, cellWidth, i, j, row, rows, cell;
    width = document.getElementById("width");
    widthType = document.getElementById("widthtype").value;
    if (width) {
        widthValue = parseInt(oEditor.trimSpace(width.value), 10);
        if (isNaN(widthValue)) {
            cellWidth = widthValue = null;
        }
        else {
            modifyTable.removeAttribute('width');
            modifyTable.style.width = widthValue + widthType;
            rows = modifyTable.rows;
            if (rows.length > 0) {
                for (i=0; i < rows.length; i++) {
                    row = rows[i];
                    for (j=0; j < row.cells.length; j++) {
                        cellWidth = parseInt(widthValue/row.cells.length, 10) + widthType;
                        cell = row.cells[j];
                        cell.setAttribute("width", cellWidth);
                    }
                }
            }
        }
    }

    var height, heightValue;
    height = document.getElementById("height");
    if (height) {
        heightValue = parseInt(oEditor.trimSpace(height.value), 10);
        if (isNaN(heightValue)) {
            heightValue = null;
        }
        else {
            heightValue += document.getElementById("heighttype").value;
            modifyTable.removeAttribute('height');
            modifyTable.style.height = heightValue;
        }
    }

    var cellpadding, cellpaddingValue;
    cellpadding = document.getElementById("cellpd");
    if (cellpadding) {
        cellpaddingValue = oEditor.trimSpace(cellpadding.value);
        if (!cellpaddingValue || isNaN(cellpaddingValue)) {
            cellpaddingValue = 0;
        }
        else {
            cellpaddingValue = parseInt(cellpaddingValue, 10);
        }
        modifyTable.setAttribute('cellpadding', cellpaddingValue);
    }

    var cellspacing, cellspacingValue;
    cellspacing = document.getElementById("cellsp");
    if (cellspacing) {
        cellspacingValue = oEditor.trimSpace(cellspacing.value);
        if (!cellspacingValue || isNaN(cellspacingValue)) {
            cellspacingValue = 0;
        }
        else {
            cellspacingValue = parseInt(cellspacingValue, 10);
        }
         modifyTable.setAttribute('cellspacing', cellspacingValue);
    }
   
    var bgcolor, bgcolorValue;
    bgcolor = document.getElementById("idbgcolor");
    if (bgcolor) {
        bgcolorValue = oEditor.trimSpace(bgcolor.value);
        if (bgcolorValue !== '' && bgcolorValue !== none) {
            modifyTable.removeAttribute('bgcolor');
            modifyTable.bgColor = bgcolorValue;
        }
    }
    
    var align, alignValue;
    align = document.getElementById("talign");
    if (align) {
        alignValue = align.value;
        if (alignValue !== 'none') {
            modifyTable.removeAttribute('align');
            modifyTable.setAttribute('align', alignValue);
        }
    }

    var cssclass, cssclassValue, cssid, cssidValue;
    cssclass = document.getElementById('cssClass');
    cssclassValue = oEditor.trimSpace(cssclass.value);
    if (cssclassValue !== '') {
        modifyTable.className = cssclassValue;
    }
    else {
        modifyTable.removeAttribute('class');
    }
    
    cssid = document.getElementById('cssId');
    cssidValue = oEditor.trimSpace(cssid.value);
    if (cssidValue !== '') {
        modifyTable.id = cssidValue;
    }
    else {
        modifyTable.removeAttribute('id');
    }
    
    var caption = document.getElementById('tableCaption');
    var captionValue = oEditor.trimSpace(caption.value);
    var summary = document.getElementById('tableSummary');
    var summaryValue = oEditor.trimSpace(summary.value);
    var oCaption;
    
    if (summaryValue !== '') {
        modifyTable.setAttribute('summary', summaryValue);
    }
    if (captionValue !== '') {
        var hideCaption, tableCaption;
        tableCaption = modifyTable.createCaption();
        tableCaption.innerHTML = captionValue;
        
        hideCaption = document.getElementById('hideCaption');
        if (hideCaption.checked === true) {
            tableCaption.style.visibility = 'hidden';
            tableCaption.style.overFlow = 'hidden';
            tableCaption.style.lineHeight = '0px';
            tableCaption.style.position = 'absolute';
            tableCaption.style.display = 'none';
        }
        else {
            tableCaption.removeAttribute('style');
        }
    }
    else {
        oCaption = modifyTable.getElementsByTagName('caption')[0];
        if (oCaption) {
            modifyTable.removeChild(oCaption);
        }
    }
 
    var copyAttribute = function(target, source) {
        var attr, attrValue, nodeName;
        attr = source.attributes;
        for (i=0; i<attr.length; i++) {
            nodeName = attr[i].nodeName;
            if (nodeName === 'style') {
                attrValue = source.getAttribute(nodeName);
                target.style.cssText = oEditor.undefined(attrValue.cssText) ? attrValue : attrValue.cssText;
            }
            else if (nodeName === 'class' || nodeName === 'id' || nodeName === 'nowrap' || nodeName === 'colspan' || 
                      nodeName === 'rowspan' || nodeName === 'align') 
            {
                attrValue = source.getAttribute(nodeName);
                if (attrValue) {
                    target.setAttribute(nodeName, attrValue);
                }
            }
        }
    };
    
    var copyChildNodes = function (target, source) {
        var child;
        child = source.firstChild;
        while (child) {
            target.appendChild(child);
            child = source.firstChild;
        }        
    };
    
    var tableHeader = document.getElementById('tableHeader').value;
    var replaceCol = function (rows, newTagName) {
        var cellLength, newCell, oldCell, newCells=[], oHead;
        row = rows[0];
        cellLength = row.cells.length;
        
        for (i=0; i < cellLength; i++) {
            oldCell = row.cells[i];
            newCell = document.createElement(newTagName);
            copyAttribute(newCell, oldCell);
            copyChildNodes(newCell, oldCell);
            
            if (newTagName === 'th') {
                newCell.setAttribute('scope', 'col');
            }
            else {
                newCell.removeAttribute('scope');
            }
            
            newCells.push(newCell);
        }

        for (j = cellLength-1; j >= 0; j--) {
            row.deleteCell(j);
        }

        for (j=0; j < newCells.length; j++) {
            row.appendChild(newCells[j]);
        }

        if (newTagName === 'th') {
            oHead = modifyTable.getElementsByTagName('thead')[0];
            if (!oHead) {
                oHead = document.createElement('thead');
                modifyTable.insertBefore(oHead, modifyTable.firstChild);
                oHead.appendChild(row);
            }
        }
        else if (row.parentNode.nodeName.toLowerCase() === 'thead') {
            oHead = row.parentNode;
            if (rows[1]) {
                rows[1].parentNode.insertBefore(row, rows[1]);
            }
            else {
                modifyTable.insertBefore(row, oHead);
            }
            modifyTable.removeChild(oHead);
        }
    };
    
    var replaceRow = function (rows, newTagName) {
        var len, newCell, sourceCell;
        len = rows.length;
        for (i=0; i < len; i++) {
            row = rows[i];
            sourceCell = row.cells[0];
            newCell = document.createElement(newTagName);
            
            if (newTagName === 'th') {
                newCell.setAttribute('scope', 'row');
            }
            else {
                sourceCell.removeAttribute('scope');
            }
            
            row.insertBefore(newCell, sourceCell);
            copyAttribute(newCell, sourceCell);
            copyChildNodes(newCell, sourceCell);            
            row.deleteCell(1);
        }
    };
    
    var border, borderValue;
    if (beforeHeaderType !== tableHeader) {
        rows = (modifyTable.rows && modifyTable.rows.length > 0) ? 
                    modifyTable.rows : 
                        modifyTable.getElementsByTagName('tr');
        
        if (tableHeader === 'col') {
            replaceRow(rows, 'td');            
            replaceCol(rows, 'th');
        }
        else if (tableHeader === 'row') {
            replaceCol(rows, 'td');
            replaceRow(rows, 'th');
        }
        else if (tableHeader === 'all') {
            replaceCol(rows, 'th');
            replaceRow(rows, 'th');
        }
        else if (tableHeader === 'none') {
            replaceCol(rows, 'td');
            replaceRow(rows, 'td');
        }
        
        oCaption = modifyTable.getElementsByTagName('caption')[0];
        if (oCaption && oCaption !== modifyTable.firstChild) {
            modifyTable.insertBefore(oCaption, modifyTable.firstChild);
        }
    }


    border  = document.getElementById("bordersize");
    if (border) {
        borderValue = oEditor.trimSpace(border.value);
        if (isNaN(borderValue) === false) {
            var borderColor, borderColorValue;
            borderValue = parseInt(borderValue, 10);
            rows = (modifyTable.rows && modifyTable.rows.length > 0) ? 
                        modifyTable.rows : 
                            modifyTable.getElementsByTagName('tr');            
                    
            if (borderValue) {
                borderColor = document.getElementById("idbordercolor");
                if (borderColor) {
                    borderColorValue = oEditor.trimSpace(borderColor.value);
                }
                if (!borderColorValue || borderColorValue === none) {
                    borderColorValue = '#000000';
                }
                    
                borderColorValue = oEditor.colorConvert(borderColorValue, 'rgb');
                
                modifyTable.style.border = borderValue + 'px solid ' + borderColorValue;
                modifyTable.style.borderCollapse = "collapse";
                modifyTable.removeAttribute('border');
                
                for (i=0; i < rows.length; i++) {
                    row = rows[i];
                    for (j=0; j < row.cells.length; j++) {
                        cell = row.cells[j];
                        cell.style.border = borderValue + 'px solid ' + borderColorValue;
                    }
                }
            }
            else if (borderValue === 0) {
                modifyTable.removeAttribute('border');
                modifyTable.style.border = '';
                modifyTable.style.borderCollapse = '';
                for (i=0; i < rows.length; i++) {
                    row = rows[i];
                    for (j=0; j < row.cells.length; j++) {
                        cell = row.cells[j];
                        cell.style.border = '';
                    }
                }                
            }
        }
    }
    
    oEditor.editArea.focus();
    oEditor.backupRange(oEditor.restoreRange());
    oEditor.clearStoredSelections();
    oEditor.popupWinClose();
}