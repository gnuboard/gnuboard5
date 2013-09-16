/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.on('dialogDefinition', function(ev) {
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;
    var dialog = dialogDefinition.dialog;
    var editor = ev.editor;

    if (dialogName=='image') {
        // memo: dialogDefinition.onShow = ... throws JS error (C.preview not defined) 
        /* 
        // Get a reference to the 'Link Info' tab. 
        var infoTab = dialogDefinition.getContents('info'); 
        // Remove unnecessary widgets 
        infoTab.remove( 'ratioLock' ); 
        infoTab.remove( 'txtHeight' );          
        infoTab.remove( 'txtWidth' );          
        infoTab.remove( 'txtBorder'); 
        infoTab.remove( 'txtHSpace'); 
        infoTab.remove( 'txtVSpace'); 
        infoTab.remove( 'cmbAlign' ); 
        */
        /*
        dialogDefinition.onLoad = function(){ 
            var dialog = CKEDITOR.dialog.getCurrent(); 
            var elem = dialog.getContentElement('info','htmlPreview');     
            elem.getElement().hide(); 
            dialog.hidePage('Link'); 
            dialog.hidePage('advanced'); 
            dialog.hidePage('info'); // works now (CKEditor v3.6.4) 
            this.selectPage('Upload'); 
        }; 
        */

        dialogDefinition.onLoad = function(){ 
            dialog.getContentElement('info', 'htmlPreview').getElement().hide();     
            dialog.getContentElement('info', 'cmbAlign').getElement().hide();     
            this.hidePage('Link');
            this.hidePage('advanced');
            this.selectPage('Upload'); 
        }; 

        /*
        alt 를 넘기지 못하는 관계로 주석 처리함. 130314 kagla
        dialogDefinition.onOk = function (e) {
            var imageSrcUrl = e.sender.originalElement.$.src;
            var imgHtml = CKEDITOR.dom.element.createFromHtml('<img src=' + imageSrcUrl + ' alt="" />');
            editor.insertElement(imgHtml);

            var uploadTab = dialogDefinition.getContents('Upload');
            var uploadButton = uploadTab.get('uploadButton');
            uploadButton['filebrowser']['onSelect'] = function(fileUrl, errorMessage) {
                $("input.cke_dialog_ui_input_text").val(fileUrl);
                $(".cke_dialog_ui_button_ok span").click();
            }
        };
        */

        /*
        var uploadTab = dialogDefinition.getContents('Upload');
        var uploadButton = uploadTab.get('uploadButton');
        uploadButton['filebrowser']['onSelect'] = function(fileUrl, errorMessage) {
            $("input.cke_dialog_ui_input_text").val(fileUrl);
            $(".cke_dialog_ui_button_ok span").click();
        }
        */
    } else if (dialogName=='link') {
        dialogDefinition.removeContents('advanced');

        dialogDefinition.onShow = function(){ 
            dialog.getContentElement('info','anchorOptions').getElement().hide(); 
            dialog.getContentElement('info','emailOptions').getElement().hide();
            dialog.getContentElement('info','linkType').getElement().hide(); 
            dialog.getContentElement('info','protocol').disable();
        }; 
    }

    var infoTab = dialogDefinition.getContents('info');
    if (infoTab) {
        infoTab.remove('txtHSpace');
        infoTab.remove('txtVSpace');
        infoTab.remove('txtBorder');
        infoTab.remove('txtWidth');
        infoTab.remove('txtHeight');
        infoTab.remove('ratioLock');
    }
});

CKEDITOR.editorConfig = function( config ) {
    config.language = "ko";
    config.toolbar = [
        ['Format','Font','FontSize'],
        ['Image','Flash','Link','-','Table','-','Smiley'],
        ['Print','Maximize'],
        ['Source'],
        '/',
        ['Bold','Italic','Underline','Strike','-','TextColor','BGColor','-','Find','Replace','-','Outdent','Indent'],
        ['NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']
        
    ];
    config.font_defaultLabel = "굴림";
    config.font_names = "굴림;돋움;바탕;궁서;굴림체;돋움체;바탕체;궁서체;나눔고딕;나눔명조;"+
        'Arial;Comic Sans MS;Courier New;Lucida Sans Unicode;monospace;sans-serif;serif;Tahoma;Times New Roman;Verdana';
    config.fontSize_defaultLabel = "10pt";
    config.fontSize_sizes = "7pt/9px;8pt/11px;9pt/12px;10pt/13px;11pt/15px;14pt/19px;18pt/24px;24pt/32px;36/48px;";
    config.enterMode = CKEDITOR.ENTER_DIV;
    config.shiftEnterMode = CKEDITOR.ENTER_DIV;
    config.filebrowserUploadUrl = g5_editor_url+"/upload.php?type=Images";
    config.keystrokes=[
        // Formatting
        [ CKEDITOR.CTRL + 81 /*Q*/, 'blockquote' ],
        [ CKEDITOR.CTRL + 66 /*B*/, 'bold' ],
        [ CKEDITOR.CTRL + 56 /*8*/, 'bulletedlist' ],
        [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 56 /*8*/, 'bulletedListStyle' ],
        [ CKEDITOR.CTRL + 77 /*M*/, 'indent' ],
        [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 77 /*M*/, 'outdent' ],
        [ CKEDITOR.CTRL + 73 /*I*/, 'italic' ],
        [ CKEDITOR.CTRL + 74 /*J*/, 'justifyblock' ],
        [ CKEDITOR.CTRL + 69 /*E*/, 'justifycenter' ],
        [ CKEDITOR.CTRL + 76 /*L*/, 'justifyleft' ],
        [ CKEDITOR.CTRL + 82 /*R*/, 'justifyright' ],
        [ CKEDITOR.CTRL + 55 /*7*/, 'numberedlist' ],
        [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 55 /*7*/, 'numberedListStyle' ],
        [ CKEDITOR.CTRL + 89 /*Y*/, 'redo' ],
        [ CKEDITOR.CTRL + 32 /*SPACE*/, 'removeFormat' ],
        [ CKEDITOR.CTRL + 65 /*A*/, 'selectall' ],
        [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 88 /*X*/, 'strike' ],
        [ CKEDITOR.CTRL + 188 /*COMMA*/, 'subscript' ],
        [ CKEDITOR.CTRL + 190 /*PERIOD*/, 'superscript' ],
        [ CKEDITOR.CTRL + 85 /*U*/, 'underline' ],
        [ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],
        // Insert
        [ CKEDITOR.ALT + 65 /*A*/, 'anchor' ],
        [ CKEDITOR.ALT + 68 /*D*/, 'creatediv' ],
        [ CKEDITOR.ALT + CKEDITOR.SHIFT + 68 /*D*/, 'editdiv' ],
        [ CKEDITOR.ALT + 70 /*F*/, 'flash' ],
        [ CKEDITOR.ALT + 72 /*H*/, 'horizontalrule' ],
        [ CKEDITOR.CTRL + 57 /*9*/, 'image' ],
        [ CKEDITOR.ALT + 73 /*I*/, 'image' ],
        [ CKEDITOR.CTRL + 75 /*K*/, 'link' ],
        [ CKEDITOR.ALT + 76 /*L*/, 'link' ],
        [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 75 /*K*/, 'unlink' ],
        [ CKEDITOR.ALT + CKEDITOR.SHIFT + 76 /*L*/, 'unlink' ],
        [ CKEDITOR.CTRL + 13 /*ENTER*/, 'pagebreak' ],
        [ CKEDITOR.ALT + 13 /*ENTER*/, 'pagebreak' ],
        [ CKEDITOR.ALT + 69 /*E*/, 'smiley' ],
        [ CKEDITOR.ALT + 67 /*C*/, 'specialchar' ],
        [ CKEDITOR.ALT + 84 /*T*/, 'table' ],
        [ CKEDITOR.ALT + 79 /*O*/, 'templates' ],
        // Other - dialogs, views, etc.
        [ 112 /*F1*/, 'about' ],
        [ CKEDITOR.ALT + 48 /*ZERO*/, 'blur' ],
        [ CKEDITOR.ALT + 8 /*Backspace*/, 'blur' ],
        [ CKEDITOR.CTRL + 87 /*W*/, 'blur' ],
        [ CKEDITOR.ALT + 51 /*#3*/, 'colordialog' ],
        [ CKEDITOR.ALT + 77 /*M*/, 'contextMenu' ],
        [ CKEDITOR.ALT + 122 /*F11*/, 'elementsPathFocus' ],
        [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 70 /*F*/, 'find' ],
        [ CKEDITOR.ALT + 88 /*X*/, 'maximize' ],
        [ CKEDITOR.CTRL + 113 /*F2*/, 'preview' ],
        [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 80 /*P*/, 'print' ],
        [ CKEDITOR.CTRL + 72 /*H*/, 'replace' ],
        [ CKEDITOR.ALT + 83 /*S*/, 'scaytcheck' ],
        [ CKEDITOR.ALT + 66 /*B*/, 'showblocks' ],
        [ CKEDITOR.ALT + CKEDITOR.SHIFT + 84 /*T*/, 'showborders' ],
        [ CKEDITOR.ALT + 90 /*Z*/, 'source' ],
        [ CKEDITOR.ALT + 48 /*ZERO*/, 'toolbarCollapse' ],
        [ CKEDITOR.ALT + 121 /*F10*/, 'toolbarFocus' ],
    ];
};