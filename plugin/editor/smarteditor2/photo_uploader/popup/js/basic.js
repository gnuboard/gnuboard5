jQuery.fn.bindAll = function (options) {
    var $this = this;
    jQuery.each(options, function (key, val) {
        $this.bind(key, val);
    });
    return this;
}

jQuery(function ($) {
    'use strict';

    var ed_nonce = '';

    if (!!opener && !!opener.window && !!opener.window.nhn) {
        ed_nonce = opener.window.nhn.husky.SE2M_Configuration.SE2M_Accessibility.ed_nonce;
    }

    // Change this to the location of your server-side upload handler:
    var gnu = {
        url: './php/?_nonce=' + ed_nonce,
        container_el: 'body',
        dreg_area: '#drag_area',
        dreg_area_list: '#drag_area > ul',
        progress_bar: '#progress .progress-bar',
        filter: /^(image\/bmp|image\/gif|image\/jpg|image\/jpeg|image\/png|image\/webp)$/i,
        files: [],
        file_limit: 10, //한번에 올릴수 파일갯수 제한
        imgw: 100,
        imgh: 70,
        file_api_support: !!(window.ProgressEvent && window.FileReader),
        $elTextGuide: $("#guide_text"),
        init: function () {
            $(this.dreg_area_list).sortable({
                'cursor': 'pointer',
                'placeholder': 'placeholder'
            });
            $(this.dreg_area_list).disableSelection();
            if (this.file_api_support) this.$elTextGuide.removeClass("hidebg").addClass("showbg");
        },
        file_push: function (file) {
            var othis = this,
                last = othis.files.length;

            othis.files.push(file);
        },
        _readymodebg: function () {
            if (this.file_api_support) {
                var sClass = this.$elTextGuide.attr('class');
                if (sClass.indexOf('hidebg') > 0) {
                    this.$elTextGuide.removeClass('hidebg');
                    this.$elTextGuide.addClass('showbg');
                }
            }
        },
        _startmodebg: function () {
            if (this.file_api_support) {
                var sClass = this.$elTextGuide.attr('class');
                if (sClass.indexOf('hidebg') < 0) {
                    this.$elTextGuide.removeClass('showbg');
                    this.$elTextGuide.addClass('hidebg');
                }
            }
        },
        _delete: function (e) {
            e.preventDefault();
            var othis = gnu,
                $button = $(e.currentTarget),
                delete_url = $button.attr("data-delete");
            if (delete_url) {
                $.ajax({
                    url: othis.url + "&del=1&file=" + delete_url
                }).done(function (result) {
                });
            }
            $button.parents('li.sort_list').fadeOut(300, function () {
                $(this).remove();
                var $dreg_area = $(othis.dreg_area_list);
                $dreg_area.sortable('refresh');
                if (!$dreg_area.children('li').length) othis._readymodebg();
            });
        },
        _add: function (e, data, preload) {
            var othis = this;
            othis._startmodebg();
            data.context = $('<li/>').addClass("sort_list").appendTo(this.dreg_area_list);
            $.each(data.files, function (index, file) {
                if (!preload && !othis.filter.test(file.type)) {
                    var agent = navigator.userAgent.toLowerCase();
                    var msg = '';
                    msg = (agent.indexOf('trident') != -1 || agent.indexOf("msie") != -1)? "익스플로러 환경에서는 gif, png, jpg 파일만 \n전송할 수 있습니다.":"이미지만 허용합니다.";
                    data.context.remove();
                    alert(msg);
                    return true;
                }
                var node = $('<div/>')
                    .append($('<span/>').text(file.name))
                    .append($('<span/>').addClass("delete_img").attr({ "data-delete": file.name, "data-url": file.url }).html("<img src='./img/system_delete.png' alt='삭제' title='삭제' >")),
                    $img = "<img src='./img/loading.gif' class='pre_thumb' />",
                    size_text = '';

                if (preload && preload != 'swfupload') {
                    var ret = othis.get_ratio(file.width, file.height),
                        size_text = file.width + " x " + file.height;
                    $img = "<img src='" + file.url + "' width='" + ret['width'] + "' height='" + ret['height'] + "' class='pre_thumb' />";

                }
                if (!index) {
                    node.prepend('<br>')
                        .prepend($img);
                    if (size_text) {
                        node.append('<br>')
                            .append($('<span/>').text(size_text))
                    }
                }
                node.appendTo(data.context);
                node.find(".delete_img").on("click", othis._delete);
            });
            $(othis.dreg_area_list).sortable('refresh');
        },
        get_file_all: function () {
            var othis = this,
                oDate = new Date();
            $.ajax({
                // Uncomment the following to send cross-domain cookies:
                //xhrFields: {withCredentials: true},
                //url: $('#fileupload').fileupload('option', 'url'),
                url: this.url + "&t=" + oDate.getTime(),
                dataType: 'json',
                context: $('#fileupload')[0]
            }).always(function () {
                //$(this).removeClass('fileupload-processing');
            }).done(function (result) {
                $.each(result.files, function (index, data) {
                    var tmp = { files: [] };
                    tmp.files[0] = data;
                    othis._add($.Event('add'), tmp, 'preload');
                });
            });
        },
        _processalways: function (e, data) {
            var index = data.index,
                file = data.files[index],
                node = $(data.context.children()[index]);
            if (file.error) {
                node
                    .append('<br>')
                    .append($('<span class="text-danger"/>').text(file.error));
            }
            if (index + 1 === data.files.length) {
                data.context.find('button')
                    .text('Upload')
                    .prop('disabled', !!data.files.error);
            }
        },
        obj_to_arr: function (obj) {
            var array = $.map(obj, function (value, index) {
                return [value];
            });
            return array;
        },
        _done: function (e, data) {
            var othis = this;
            $.each(data.result.files, function (index, file) {
                if (file.url && !file.error) {
                    var ret = othis.get_ratio(file.width, file.height),
                        node = $(data.context.children()[index]),
                        size_text = file.width + " x " + file.height,
                        //$img = "<img src='"+file.url+"' width='"+ret['width']+"' height='"+ret['height']+"' />",
                        link = $('<a>')
                            .attr('target', '_blank')
                            .prop('href', file.url);
                    node
                        //.wrap(link)
                        .append('<br>')
                        .append($('<span/>').text(size_text))
                        .find("img.pre_thumb").attr({ "src": file.url, "width": ret['width'], "height": ret['height'] })
                        .end().find(".delete_img").attr({ "data-delete": file.name, "data-url": file.url });
                } else if (file.error) {
                    var error = $('<span class="text-danger"/>').text(file.error);
                    $(data.context.children()[index])
                        .append('<br>')
                        .append(error);
                }
                othis.file_push(file);
            });
        },
        get_ratio: function (width, height) {
            var ratio = 0,
                ret_img = [];
            if (!width || !height) {
                ret_img['width'] = this.imgw;
                ret_img['height'] = this.imgh;
                return ret_img;
            }
            if (width > this.imgw) {
                ratio = this.imgw / width;
                height = height * ratio;
                width = this.imgw;
            }
            if (height > this.imgh) {
                ratio = this.imgh / height;
                width = width * ratio;
                height = this.imgh;
            }
            ret_img['width'] = parseInt(width);
            ret_img['height'] = parseInt(height);
            return ret_img;
        },
        setPhotoToEditor: function (oFileInfo) {
            if (!!opener && !!opener.nhn && !!opener.nhn.husky && !!opener.nhn.husky.PopUpManager) {
                //스마트 에디터 플러그인을 통해서 넣는 방법 (oFileInfo는 Array)
                opener.nhn.husky.PopUpManager.setCallback(window, 'SET_PHOTO', [oFileInfo]);
                //본문에 바로 tag를 넣는 방법 (oFileInfo는 String으로 <img src=....> )
                //opener.nhn.husky.PopUpManager.setCallback(window, 'PASTE_HTML', [oFileInfo]);
            }
        }
    }

    $('#fileupload').fileupload({
        url: gnu.url,
        dataType: 'json',
        container_el: gnu.container_el,
        dropZone: $(gnu.dreg_area),
        autoUpload: true,
        sequentialUploads: true,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|bmp|png|webp)$/i,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: true,
        limit_filesLength: gnu.file_limit
    }).on('fileuploadadd', function (e, data) {
        gnu._add(e, data);
    }).on('fileuploadprocessalways', function (e, data) {
        gnu._processalways(e, data);
    }).on('fileuploaddone', function (e, data) {

        gnu._done(e, data);

    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index, file) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    gnu.init();

    var listeners = {
        data: {},
        log: false,
        swfuploadLoaded: function (event) {
            if (this.log) $('.log', this).append('<li>Loaded</li>');
        },
        fileQueued: function (event, file) {
            if (this.log) $('.log', this).append('<li>File queued - ' + file.name + '</li>');
            // start the upload once it is queued
            // but only if this queue is not disabled
            if (!$('input[name=disabled]:checked', this).length) {
                $(this).swfupload('startUpload');
            }
        },
        fileQueueError: function (event, file, errorCode, message) {
            switch (errorCode) {
                case -100:
                    alert("파일을 " + message + "개 이하로 선택해주세요.");
                    break;
            }
            if (this.log) $('.log', this).append('<li>File queue error - ' + message + '</li>');
        },
        fileDialogStart: function (event) {
            if (this.log) $('.log', this).append('<li>File dialog start</li>');
        },
        fileDialogComplete: function (event, numFilesSelected, numFilesQueued) {
            if (this.log) $('.log', this).append('<li>File dialog complete</li>');
        },
        uploadStart: function (event, file) {
            listeners.data.files = $.makeArray(file);
            gnu._add(event, listeners.data, 'swfupload');
            if (this.log) $('.log', this).append('<li>Upload start - ' + file.name + '</li>');
        },
        uploadProgress: function (event, file, bytesLoaded) {
            if (this.log) $('.log', this).append('<li>Upload progress - ' + bytesLoaded + '</li>');
        },
        uploadSuccess: function (event, file, serverData) {
            listeners.data.result = jQuery.parseJSON(serverData);
            gnu._done(event, listeners.data);
            if (this.log) $('.log', this).append('<li>Upload success - ' + file.name + '</li>');

        },
        uploadComplete: function (event, file) {
            if (this.log) $('.log', this).append('<li>Upload complete - ' + file.name + '</li>');
            // upload has completed, lets try the next one in the queue
            // but only if this queue is not disabled
            if (!$('input[name=disabled]:checked', this).length) {
                $(this).swfupload('startUpload');
            }
        },
        uploadError: function (event, file, errorCode, message) {
            if (this.log) $('.log', this).append('<li>Upload error - ' + message + '</li>');
        }
    };

    $(gnu.container_el).bindAll(listeners);
    /* listeners이벤트 */

    $(gnu.dreg_area).bind('drop dragover', function (e) {
        e.preventDefault();
        if (!gnu.file_api_support && e.type == 'drop') alert("브라우저가 드래그 앤 드랍을 지원하지 않습니다.");
    });
    $("#all_remove_btn").bind("click", function (e) {
        e.preventDefault();
        if ($(gnu.dreg_area_list).children().length) {
            if (confirm("추가한 이미지가 있습니다.정말 삭제 하시겠습니까?")) {
                $(gnu.dreg_area_list).find(".delete_img").each(function (i) {
                    $(this).trigger("click");
                });
                $(gnu.dreg_area_list).sortable('refresh');
            }
        }
    });
    $("#img_upload_submit").bind("click", function (e) {
        e.preventDefault();
        var aResult = [], j = 0;
        $(gnu.dreg_area_list).find(".delete_img").each(function (i, f) {
            if (!$(this).attr("data-url")) return true;
            aResult[j] = [];
            aResult[j]['bNewLine'] = 'true';
            aResult[j]['sAlign'] = '';
            aResult[j]['sFileName'] = $(this).attr("data-delete");
            aResult[j]['sFileURL'] = $(this).attr("data-url");
            j++;
        });
        if (aResult.length) {
            gnu.setPhotoToEditor(aResult);
            aResult = null;
        }
        window.close();
    });
    $("#close_w_btn").bind("click", function (e) {
        e.preventDefault();
        window.close();
    });
});