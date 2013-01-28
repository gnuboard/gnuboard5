/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

    config.language = "ko";
    config.toolbar = [
        ['Styles','Format','Font','FontSize'],
        ['Image','Flash','Link','-','Table','-','Smiley'],
        ['Print','Maximize'],
        ['Source'],
        '/',
        ['Bold','Italic','Underline','Strike','-','TextColor','BGColor','-','Undo','Redo','-','Cut','Copy','Paste','Find','Replace','-','Outdent','Indent'],
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
