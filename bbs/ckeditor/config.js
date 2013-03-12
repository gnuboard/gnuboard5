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
    config.enterMode = CKEDITOR.ENTER_BR;
    config.shiftEnterMode = CKEDITOR.ENTER_P;
    config.filebrowserUploadUrl = g4_ckeditor_url+"/upload.php?type=Images";
};
