/**
 * JS functions
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

var sergsxmUIFunctions = {
    
    locale: 'en',
    fileUploadPath: '',
    
    initContext : function (locale, fileUploadPath) {
        this.locale = locale;
        this.fileUploadPath = fileUploadPath;
    },
    
    alert : function (message, type, container) {
        var cssclass = 'alert-info';
        if (type == 'success') {
            cssclass = 'alert-success';
        } else if (type == 'error') {
            cssclass = 'alert-danger';
        } else if (type == 'warning') {
            cssclass = 'alert-warning';
        }
        $('<div class="alert '+cssclass+' alert-dismissible" role="alert" style="display:none"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+message+'</div>').appendTo(container).slideDown(200);
    },
    
    confirm : function (message, title, okText, cancelText, callBack) {
        var $alert = 
        $(' <div class="modal fade">\
                <div class="modal-dialog">\
                    <div class="modal-content">\
                        <div class="modal-header">\
                            <button type="button" class="close sergsxmui-modal-alert-cancel" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                            <h4 class="modal-title">'+title+'</h4>\
                        </div>\
                        <div class="modal-body">\
                            <p>'+message+'</p>\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default sergsxmui-modal-alert-cancel">'+cancelText+'</button>\
                            <button type="button" class="btn btn-primary sergsxmui-modal-alert-ok">'+okText+'</button>\
                        </div>\
                    </div>\
                </div>\
            </div>');
        $alert.appendTo('body').modal('show');
        $alert.on('hidden.bs.modal', function () {
            this.remove();
        });
        $alert.find('.sergsxmui-modal-alert-cancel').click(function () {
            $alert.modal('hide');
        });
        $alert.find('.sergsxmui-modal-alert-ok').click(function () {
            callBack();
            $alert.modal('hide');
        });
    },
    
    initWysiwyg : function (selector) {
        if (($(selector).length) && (tinymce != undefined) && (typeof tinymce === 'object')) {
            tinymce.init({
                selector: selector,
                locale: this.locale,
                plugins: [
                    "advlist autolink lists link image charmap print preview anchor",
                    "searchreplace visualblocks code fullscreen",
                    "insertdatetime media table contextmenu paste"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
            });
        }
    },
    
    isFunction : function (functionToCheck) {
        var getType = {};
        return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
    },
    
    initFileUploadInput : function (selector, formId, fileTemplate, emptyFileTemplate, validator) {
        $(selector).css({position: 'fixed', top: '-100px'});
        $(selector).change(function () {
            if ((this.files == undefined) || (this.files[0] == undefined)) {
                return false;
            }
            var inputName = ($(this).data('replace-input-name') ? $(this).data('replace-input-name') : $(this).prop('name')), 
                inputId = ($(this).data('replace-input-id') ? $(this).data('replace-input-id') : $(this).prop('id'));
            if (sergsxmUIFunctions.isFunction(validator)) {
                var errors = validator(this, true);
                if (errors[inputName] != undefined) {
                    return false;
                }
            }
            var $fileInput = $(this), $filesContainer = $('#'+inputId+'-files'), file = this.files[0], formData = new FormData();
            formData.append($fileInput.prop('name'), file);
            formData.append('form_id', formId);
            formData.append('input_name', $fileInput.prop('name'));
            var $progressBar = $('<div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"><span class="sr-only">0%</span></div></div>').insertAfter($filesContainer);
            $($filesContainer).hide();
            $.ajax({
                url : sergsxmUIFunctions.fileUploadPath,
                type : 'POST',
                data : formData,
                processData: false,
                contentType: false,
                cache: false,
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    if (!xhr.upload) {
                        return xhr;
                    }
                    xhr.upload.addEventListener("progress", function (e) {
                        if (e.lengthComputable) {
                            var completed = Math.ceil(e.loaded * 100 / e.total);
                            $progressBar.attr('aria-valuenow', completed).css({width: completed+'%'}).find('span.sr-only').text(completed+'%');
                        }
                    }, false);
                    return xhr;
                },
                success: function (data) {
                    var insertion = fileTemplate.replace('%name%', data.fileName).replace('%size%', data.size);
                    $fileInput.val('');
                    $filesContainer.html('<input type="hidden" name="'+inputName+'" value="'+data.id+'" />'+insertion);
                },
                error: function (data) {
                    if (data.error) {
                        alert(data.error);
                    }
                },
                complete: function () {
                    $($filesContainer).show();
                    $progressBar.remove();
                }
            });
        });    
    }
    
};
