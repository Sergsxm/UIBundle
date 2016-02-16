/**
 * JS functions
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

var sergsxmUIFunctions = {
    
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
    
};
