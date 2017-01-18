// ================================================================
//                       CHEditor 5
// ----------------------------------------------------------------
// Homepage: http://www.chcode.com
// Copyright (c) 1997-2014 CHSOFT
// ================================================================
var c = null;
var curView = null;
var S1 = '＂ （ ） ［ ］ ｛ ｝ ‘ ’ “ ” 〔 〕 〈 〉 《 》 「 」 『 』 【 】 § ※ ☆ ★ ○ ● ■ △ ▲ ▽ ▼ → 〓 ◁ ◀ ▷ ▶ ♤ ♣ ⊙ ◈ ▣ ◐ ◑ ▧ ▦ ▩ ♨ ☏ ☎ ‡ ㉿ ↕ ↗ ↙ ↖ ↘ ㈜ № ㏇ ™ ㏂ ＋ － ＜ ＝ ＞ ± × ÷ ≠ ≤ ≥ ∞ ∴ ♂ ♀ ∠ ⊥ ⌒ ∂ ∇ ≡ ≒ ≪ ≫ √ ∽ ∝ ∵ ∫ ∬ ∈ ∋ ⊆ ⊇ ⊂ ⊃ ∮ ∪ ∩ ∑ ∏ ∧ ∨ ￢ ⇒ ⇔ ∀ ∃';
var S2 = '─ │ ┌ ┐ ┘ └ ├ ┬ ┤ ┴ ┼ ━ ┃ ┏ ┓ ┛ ┗ ┣ ┳ ┫ ┻ ╋ ┠ ┯ ┨ ┷ ┿ ┝ ┰ ┥ ┸ ╂ ┒ ┑ ┚ ┙ ┖ ┕ ┎ ┍ ┞ ┟ ┡ ┢ ┦ ┧ ┩ ┪ ┭ ┮ ┱ ┲ ┵ ┶ ┹ ┺ ┽ ┾ ╀ ╁ ╃ ╄ ╅ ╆ ╇ ╈ ╉ ╊';
var S3 = '½ ⅓ ⅔ ¼ ¾ ⅛ ⅜ ⅝ ⅞ ¹ ² ³ ⁴ ⁿ ₁ ₂ ₃ ₄ ０ １ ２ ３ ４ ５ ６ ７ ８ ９ ⅰ ⅱ ⅲ ⅳ ⅴ ⅵ ⅶ ⅷ ⅸ ⅹ Ⅰ Ⅱ Ⅲ Ⅳ Ⅴ Ⅵ Ⅶ Ⅷ Ⅸ Ⅹ ＄ ％ ￦ ° ′ ″ ℃ Å ￠ ￡ ￥ ¤ ℉ ‰ ㎕ ㎖ ㎗ ℓ ㎘ ㏄ ㎣ ㎤ ㎥ ㎦ ㎙ ㎚ ㎛ ㎜ ㎝ ㎞ ㎟ ㎠ ㎡ ㎢ ㏊ ㎍ ㎎ ㎏ ㏏ ㎈ ㎉ ㏈ ㎧ ㎨ ㎰ ㎱ ㎲ ㎳ ㎴ ㎵ ㎶ ㎷ ㎸ ㎹ ㎀ ㎁ ㎂ ㎃ ㎄ ㎺ ㎻ ㎼ ㎽ ㎾ ㎿ ㎐ ㎑ ㎒ ㎓ ㎔ Ω ㏀ ㏁ ㎊ ㎋ ㎌ ㏖ ㏅ ㎭ ㎮ ㎯ ㏛ ㎩ ㎪ ㎫ ㎬ ㏝ ㏐ ㏓ ㏉ ㏜ ㏆'; 
var S4 = 'ㅥ ㅦ ㅧ ㅨ ㅩ ㅪ ㅫ ㅬ ㅭ ㅮ ㅰ ㅯ ㅱ ㅲ ㅳ ㅴ ㅵ ㅶ ㅷ ㅸ ㅹ ㅺ ㅻ ㅼ ㅽ ㅾ ㅿ ㆀ ㆁ ㆂ ㆃ ㆄ ㆅ ㆆ ㆇ ㆈ ㆉ ㆊ ㆋ ㆌ ㆍ ㆎ';
var S5 = '㉠ ㉡ ㉢ ㉣ ㉤ ㉥ ㉦ ㉧ ㉨ ㉩ ㉪ ㉫ ㉬ ㉭ ㉮ ㉯ ㉰ ㉱ ㉲ ㉳ ㉴ ㉶ ㉶ ㉷ ㉸ ㉹ ㉺ ㉻ ㈀ ㈁ ㈂ ㈃ ㈄ ㈅ ㈆ ㈇ ㈈ ㈉ ㈊ ㈋ ㈌ ㈍ ㈎ ㈏ ㈐ ㈑ ㈒ ㈓ ㈔ ㈕ ㈖ ㈗ ㈘ ㈙ ㈚ ㈛ ⓐ ⓑ ⓒ ⓓ ⓔ ⓕ ⓖ ⓗ ⓘ ⓙ ⓚ ⓛ ⓜ ⓝ ⓞ ⓟ ⓠ ⓡ ⓢ ⓣ ⓤ ⓥ ⓦ ⓧ ⓨ ⓩ ① ② ③ ④ ⑤ ⑥ ⑦ ⑧ ⑨ ⑩ ⑪ ⑫ ⑬ ⑭ ⑮ ⒜ ⒝ ⒞ ⒟ ⒠ ⒡ ⒢ ⒣ ⒤ ⒥ ⒦ ⒧ ⒨ ⒩ ⒪ ⒫ ⒬ ⒭ ⒮ ⒯ ⒰ ⒱ ⒲ ⒳ ⒴ ⒵ ⑴ ⑵ ⑶ ⑷ ⑸ ⑹ ⑺ ⑻ ⑼ ⑽ ⑾ ⑿ ⒀ ⒁ ⒂';
var japan1 = 'ぁ か さ た ど び ぽ ょ ゑ あ が ざ だ な ぴ ま よ を ぃ き し ち に ふ み ら ん い ぎ じ ぢ ぬ ぶ む り ぅ く す っ ね ぷ め る う ぐ ず つ の へ も れ ぇ け せ づ は べ ゃ ろ え げ ぜ て ば ぺ や ゎ ぉ こ そ で ぱ ほ ゅ わ お ご ぞ と ひ ぼ ゆ ゐ';
var japan2 = 'ァ カ サ タ ド ビ ポ ョ ヱ ア ガ ザ ダ ナ ピ マ ヨ ヲ ィ キ シ チ ニ フ ミ ラ ン イ ギ ジ ヂ ヌ ブ ム リ ヴ ゥ ク ス ッ ネ プ メ ル ヵ ウ グ ズ ツ ノ ヘ モ レ ヶ ェ ケ セ ヅ ハ ベ ャ ロ エ ゲ ゼ テ バ ペ ヤ ヮ ォ コ ソ デ パ ホ ュ ワ オ ゴ ゾ ト ヒ ボ ユ ヰ';

c = S1.split(' ');
var button = [ { alt : "", img : 'input.gif', 	cmd : inputChar },              
               { alt : "", img : 'cancel.gif', 	cmd : popupClose } ];

var oEditor = null;

function init(dialog) {
	oEditor = this;
	oEditor.dialog = dialog;
	
	var dlg = new Dialog(oEditor);
	dlg.showButton(button);
	
	setupEvent();
	dlg.setDialogHeight();
}

function hover(obj, val) {
  	obj.style.backgroundColor = val ? "#5579aa" : "#fff";
  	obj.style.color = val ? "#fff" : "#000";
}

function showTable() {
  	var k = 0;
  	var len = c.length;
  	var w = 9;
  	var h = 20;
  	var span, i, j, tr, td;
    
  	var table = document.createElement('table');
  	table.border = 0;
  	table.cellSpacing = 1;
  	table.cellPadding = 0;
  	table.align = 'center';
  	
    var getChar = function() {
        document.getElementById('fm_input').value = document.getElementById('fm_input').value + c[this.id];
    };
    var mouseOver = function() {
        hover(this, true);
    };
    var mouseOut = function() {
        hover(this, false);
    };    
  	for (i=0; i < w; i++) {
  		tr = table.insertRow(i);
    	for (j = 0; j < h; j++) {
    		td = tr.insertCell(j);
    		td.className = 'schar';
    		
        	if ( len < k+1) {
        		td.appendChild(document.createTextNode('\u00a0'));
        	}
        	else {
        		td.style.cursor = 'pointer';
        		td.id = k;
        		td.onclick = getChar;
        		td.onmouseover = mouseOver;
        		td.onmouseout = mouseOut;
                span = document.createElement("span");
                span.style.fontSize = "13px";
                span.appendChild(document.createTextNode(c[k]));
        		td.appendChild(span);
        	}
      		k++;
    	}
  	}

  	var output = document.getElementById('output');
  	if (output.hasChildNodes()) {
  		for (i=0; i<output.childNodes.length; i++) {
  			output.removeChild(output.firstChild);
  		}
  	}
  	output.appendChild(table);
}

function sp1 () {
	c = S1.split(' ');
	showTable();
}

function sp2 () {
	c = S2.split(' ');
	showTable();
}

function sp3 () {
	c = S3.split(' ');
	showTable();
}

function sp4 () {
	c = S4.split(' ');
	showTable();
}

function sp5 () {
	c = S5.split(' ');
	showTable();
}

function sp6 () {
	c = japan1.split(' ').concat(japan2.split(' '));
	showTable();
}

function inputChar() {
	oEditor.insertHtmlPopup(document.getElementById('fm_input').value);
	oEditor.popupWinClose();
}

function popupClose() {
    oEditor.popupWinCancel();
}

function setupEvent() {
	var el = document.body.getElementsByTagName('LABEL');
    var i;
    var tab = function() {
        document.getElementById(this.id).style.fontWeight = 'bold';
        switch (this.id) {
        case 's1' : sp1(); break;
        case 's2' : sp2(); break;
        case 's3' : sp3(); break;
        case 's4' : sp4(); break;
        case 's5' : sp5(); break;
        default : sp6();
        }
        
        if (curView != this.id) {
            document.getElementById(curView).style.fontWeight = 'normal';
        }
        curView = this.id;        
    };
    
	for (i=0; i < el.length; i++) {
		el[i].className = 'handCursor';
		el[i].style.fontSize = '9pt';
		el[i].style.margin = (i==0) ? '0px 0px 5px 5px' : '0px 0px 5px 0px';
		el[i].onclick = tab;
	}
	
	if (curView == null) {
		showTable();
		curView = 's1';
		document.getElementById(curView).style.fontWeight = 'bold';
		document.getElementById('output').style.visibility = 'visible';
	}
	document.getElementById("fm_input").value = "";
}