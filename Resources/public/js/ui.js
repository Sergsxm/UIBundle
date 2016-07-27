/**
 * JS functions
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

/**
 * Class for drag and drop functions
 */
var SergsxmUIDraggableElement = (function () {
    /**
     * Constructor
     * 
     * @param {string} container Container element
     * @param {array} configuration Configuration
     * @returns {SergsxmUIDraggableElement} Object
     */
    function SergsxmUIDraggableElement(container, configuration) {
        this.configuration = {
            clickFilter: 'a, button, input'
        };
        $.extend(this.configuration, configuration);
        
        this.$container = $(container);
        this.$moveElement = null;
        this.savedZIndex = null;
        this.savedPosition = null;
        this.moveOffsetX = null;
        this.moveOffsetY = null;
        this.moveCurrentX = null;
        this.moveCurrentY = null;
        this.$shadowElement = null;
        this.$elements = null;
        if (this.$container.css('position') != 'absolute') {
            this.$container.css('position', 'relative');
        }
        this.$container.css({
            '-webkit-touch-callout': 'none', 
            '-webkit-user-select': 'none', 
            '-khtml-user-select': 'none',
            '-moz-user-select': 'none',
            '-ms-user-select': 'none',
            '-o-user-select': 'none',
            'user-select': 'none'
        });
        var that = this;
        this.$container.delegate('>*', 'mousedown', function (e) {
            if (that.start(e.currentTarget, e.target, e.pageX, e.pageY)) {
                e.preventDefault();
                return false;
            }
        });
        $(document).mouseup(function (e) {
            that.stop();
        });
        this.$container.mousemove(function (e) {
            if (that.move(e.pageX, e.pageY)) {
                e.preventDefault();
                return false;
            }
        });
        this.$container.delegate('>*', 'touchstart', function (e) {
            var touch = e.originalEvent.changedTouches[0];
            if (that.start(e.currentTarget, e.target, touch.pageX, touch.pageY)) {
                e.preventDefault();
                e.originalEvent.preventDefault();
                return false;
            }
        });
        $(document).on('touchend', function (e) {
            that.stop();
        });
        $(document).on('touchcancel', function (e) {
            that.stop();
        });
        this.$container.on('touchmove', function (e) {
            var touch = e.originalEvent.changedTouches[0];
            if (that.move(touch.pageX, touch.pageY)) {
                e.preventDefault();
                e.originalEvent.preventDefault();
                return false;
            }
        });
    }
    /**
     * Create shadow for element
     * Private method
     * 
     * @param {jQuery} $element Element for shadow
     */
    SergsxmUIDraggableElement.prototype.createShadow = function ($element) {
        if (this.$shadowElement) {
            this.destroyShadow();
        }
        var offset = $element.position();
        this.$shadowElement = $('<div></div>');
        var css = $element.css([
            'margin-left',
            'margin-top',
            'border-top-left-radius',
            'border-top-right-radius',
            'border-bottom-left-radius',
            'border-bottom-right-radius',
            'border-top-width',
            'border-bottom-width',
            'border-left-width',
            'border-right-width',
            'box-sizing',
            'width',
            'height',
        ]);
        css['position'] = 'absolute';
        css['left'] = offset.left;
        css['top'] = offset.top;
        css['z-index'] = -1;
        css['background'] = '#ccc';
        css['border-color'] = '#ccc';
        css['border-style'] = 'solid';
        css['box-shadow'] = '0 0 3px #ccc';
        this.$shadowElement.css(css);
        this.$container.append(this.$shadowElement);
    };
    /**
     * Move shadow to new position of element
     * Private method
     * 
     * @param {jQuery} $element Element for shadow position
     */
    SergsxmUIDraggableElement.prototype.moveShadow = function ($element) {
        if (!this.$shadowElement) {
            return false;
        }
        var offset = $element.position();
        this.$shadowElement.css({
            top: offset.top,
            left: offset.left,
        });
    };
    /**
     * Destroy shadow
     * Private method
     */
    SergsxmUIDraggableElement.prototype.destroyShadow = function () {
        if (this.$shadowElement) {
            this.$shadowElement.remove();
            this.$shadowElement = null;
        }
    };
    /**
     * Check mouse in element box
     * Private method
     * 
     * @param {jQuery} $element Element
     * @param {int} x Page x position
     * @param {int} y Page y position
     * @returns {Boolean} Result
     */
    SergsxmUIDraggableElement.prototype.inBox = function ($element, x, y) {
        var offset = $element.offset(), width = $element.outerWidth(true), height = $element.outerHeight(true);
        return ((x >= offset.left) && (x < (offset.left + width)) && (y >= offset.top) && (y < (offset.top + height)));
    };
    /**
     * Get element witch mouse in box
     * Private method
     * 
     * @param {int} x Page x position
     * @param {int} y Page y position
     * @returns {jQuery|null} Element
     */
    SergsxmUIDraggableElement.prototype.checkAllInBox = function (x, y) {
        for (var i = 0; i < this.$elements.length; i++) {
            var $el = $(this.$elements.get(i));
            if ($el.is(this.$moveElement)) {
                continue;
            }
            if (this.inBox($el, x, y)) {
                return $el;
            }
        }
        return null;
    };
    /**
     * Swap current move element with specified element
     * Private method
     * 
     * @param {jQuery} $el Element to swap
     */
    SergsxmUIDraggableElement.prototype.swapWithElement = function ($el) {
        var indexNew = this.$elements.index($el), indexOld = this.$elements.index(this.$moveElement);
        if ((indexNew == -1) || (indexOld == -1)) {
            return false;
        }
        this.$moveElement.detach();
        if (indexNew > indexOld) {
            $el.after(this.$moveElement);
        } else {
            $el.before(this.$moveElement);
        }
        this.$elements = this.$container.children();
        if (this.$shadowElement) {
            this.$elements = this.$elements.not(this.$shadowElement);
        }
    };
    /**
     * Start element move
     * Private method
     * 
     * @param {DOMObject} element Element to move
     * @param {int} x Page x position of click
     * @param {int} y Page y position of click
     * @returns {Boolean} Result
     */
    SergsxmUIDraggableElement.prototype.start = function (element, target, x, y) {
        if (this.$moveElement) {
            this.stop();
        }
        if (this.configuration.clickFilter.toString().toUpperCase().replace(' ', '').split(',').indexOf(target.tagName) >= 0) {
            return false;
        }
        this.$elements = this.$container.children();
        this.$moveElement = $(element);
        var offset = this.$moveElement.offset();
        this.moveOffsetX = x - offset.left;
        this.moveOffsetY = y - offset.top;
        this.moveCurrentX = 0;
        this.moveCurrentY = 0;
        this.savedZIndex = this.$moveElement.css('z-index');
        this.savedPosition = this.$moveElement.css('position');
        this.createShadow(this.$moveElement);
        this.$moveElement.css({position: "relative", top: 0, left: 0, 'z-index': 999});  
        return true;
    };
    /**
     * Stop element move
     * Private method
     * 
     * @returns {Boolean} Result
     */
    SergsxmUIDraggableElement.prototype.stop = function () {
        if (!this.$moveElement) {
            return false;
        }
        this.destroyShadow();
        this.$moveElement.css({position: this.savedPosition, top: 0, left: 0, 'z-index': this.savedZIndex});
        this.$moveElement = null;
        return true;
    };
    /**
     * Move element to position
     * 
     * @param {int} x Page x position of pointer
     * @param {int} y Page y position of pointer
     * @returns {Boolean} Result
     */
    SergsxmUIDraggableElement.prototype.move = function (x, y) {
        if (!this.$moveElement) {
            return false;
        }
        var $el = this.checkAllInBox(x, y);
        if ($el) {
            this.moveShadow($el);
            this.swapWithElement($el);
        }
        var offset = this.$moveElement.offset();
        var posX = x - this.moveOffsetX - offset.left + this.moveCurrentX;
        var posY = y - this.moveOffsetY - offset.top + this.moveCurrentY;
        this.$moveElement.css({top: posY, left: posX});
        this.moveCurrentX = posX;
        this.moveCurrentY = posY;
        return true;    
    };
    
    return SergsxmUIDraggableElement;
})();

/*
 * Class for tree form functions
 */
var SergsxmUITree = (function () {
    /**
     * Constructor
     * 
     * @param {string} container Tree container
     * @param {object} configuration Configuration array
     */
    function SergsxmUITree(container, configuration) {
        this.configuration = {
            nestingDataProperty: "nesting", // data property name to storage current nesting
            idDataProperty: "id",           // data property name where placed element ID
            maxNesting: false,              // max nesting or false to disable function
            nestingPadding: 50,             // element padding to one nesing level
            clickFilter: 'a, button, input',// filter of click elements
            removeButton: false,             // close button selector
            
            formContainer: false,           // Container for form inputs
            inputName: 'tree',              // Input name
            
            readOnly: false,
        };
        $.extend(this.configuration, configuration);
        this.$container = $(container);
        this.changed = false;
        this.$moveElement = null;
        this.moveOffsetX = null;
        this.moveOffsetY = null;
        this.moveCurrentX = null;
        this.moveCurrentY = null;
        this.$shadowElement = null;
        this.$elements = null;
        this.currentNesting = null;
        this.currentNestingRanges = null;
        if (this.$container.css('position') != 'absolute') {
            this.$container.css('position', 'relative');
        }
        this.$container.css({
            '-webkit-touch-callout': 'none', 
            '-webkit-user-select': 'none', 
            '-khtml-user-select': 'none',
            '-moz-user-select': 'none',
            '-ms-user-select': 'none',
            '-o-user-select': 'none',
            'user-select': 'none'
        });
        this.$container.children().css('position', 'relative');
        this.updateElementsNesting(true);
        if (this.configuration.readOnly == true) {
            if (this.configuration.removeButton !== false) {
                this.$container.find('>* '+this.configuration.removeButton).remove();
            }
            return false;
        }
        var that = this;
        if (this.configuration.removeButton !== false) {
            this.$container.delegate('>* '+this.configuration.removeButton, 'click', function (e) {
                that.removeElement(e.currentTarget);
            });
        }
        this.$container.delegate('>*', 'mousedown', function (e) {
            if (that.start(e.currentTarget, e.target, e.pageX, e.pageY)) {
                e.preventDefault();
                return false;
            }
        });
        $(document).mouseup(function (e) {
            that.stop();
        });
        this.$container.mousemove(function (e) {
            if (that.move(e.pageX, e.pageY)) {
                e.preventDefault();
                return false;
            }
        });
        this.$container.delegate('>*', 'touchstart', function (e) {
            var touch = e.originalEvent.changedTouches[0];
            if (that.start(e.currentTarget, e.target, touch.pageX, touch.pageY)) {
                e.preventDefault();
                e.originalEvent.preventDefault();
                return false;
            }
        });
        $(document).on('touchend', function (e) {
            that.stop();
        });
        $(document).on('touchcancel', function (e) {
            that.stop();
        });
        this.$container.on('touchmove', function (e) {
            var touch = e.originalEvent.changedTouches[0];
            if (that.move(touch.pageX, touch.pageY)) {
                e.preventDefault();
                e.originalEvent.preventDefault();
                return false;
            }
        });
    }

/**
 * Remove element from tree
 * 
 * @param {DOMElement} element Event element
 */
    SergsxmUITree.prototype.removeElement = function (element) {
        this.changed = true;
        $(element).closest(this.$container.children()).remove();
        this.updateElementsNesting(true);
    };

/**
 * Update element nesting
 * Private method
 * 
 * @param {boolean|undefined} updateForm If true inputs will be updated
 */
    SergsxmUITree.prototype.updateElementsNesting = function (updateForm) {
        var that = this, $elements = this.$container.children(), order = 0;
        if (this.$shadowElement) {
            $elements = $elements.not(this.$shadowElement);
        }
        if ((updateForm) && (that.configuration.formContainer)) {
            $(that.configuration.formContainer).find('input').remove();
        }
        $elements.each(function () {
            var $this = $(this), nesting = $this.data(that.configuration.nestingDataProperty), ranges = that.getNestingRange($this, $elements);
            nesting = that.checkRanges(nesting, ranges);
            $this.data(that.configuration.nestingDataProperty, nesting);
            $this.css({'left': nesting * that.configuration.nestingPadding, 'top': 0});
            if (updateForm) {
                var id = $this.data(that.configuration.idDataProperty),
                    inputs = '<input type="hidden" name="'+that.configuration.inputName+'['+id+'][nesting]" value="'+nesting+'" />'+
                             '<input type="hidden" name="'+that.configuration.inputName+'['+id+'][order]" value="'+order+'" />';
                if (that.configuration.formContainer) {
                    $(that.configuration.formContainer).append(inputs);
                } else {
                    $this.find('input').remove();
                    $this.append(inputs);
                }
            }
            order++;
        });
    }

/**
 * Create shadow for element
 * 
 * @param {jQuery} $element Element
 */    
    SergsxmUITree.prototype.createShadow = function ($element) {
        if (this.$shadowElement) {
            this.destroyShadow();
        }
        var offset = $element.position();
        this.$shadowElement = $('<div></div>');
        var css = $element.css([
            'margin-left',
            'margin-top',
            'border-top-left-radius',
            'border-top-right-radius',
            'border-bottom-left-radius',
            'border-bottom-right-radius',
            'border-top-width',
            'border-bottom-width',
            'border-left-width',
            'border-right-width',
            'box-sizing',
            'width',
            'height',
        ]);
        css['position'] = 'absolute';
        css['left'] = offset.left;
        css['top'] = offset.top;
        css['z-index'] = -1;
        css['background'] = '#ccc';
        css['border-color'] = '#ccc';
        css['border-style'] = 'solid';
        css['box-shadow'] = '0 0 3px #ccc';
        this.$shadowElement.css(css);
        this.$container.append(this.$shadowElement);
    };

/**
 * Move shadow to element position
 * Private method
 * 
 * @param {jQuery} $element Element
 */    
    SergsxmUITree.prototype.moveShadow = function ($element) {
        if (!this.$shadowElement) {
            return false;
        }
        if ($element !== undefined) {
            var offset = $element.position();
            this.$shadowElement.css({
                top: offset.top,
            });
        }
        this.$shadowElement.css({
            left: this.currentNesting * this.configuration.nestingPadding,
        });
    };

/**
 * Destroy shadow
 * Private method
 * 
 */    
    SergsxmUITree.prototype.destroyShadow = function () {
        if (this.$shadowElement) {
            this.$shadowElement.remove();
            this.$shadowElement = null;
        }
    };
    
/**
 * Check if position in horizontal level of element
 * Private method
 * 
 * @param {jQuery} $element Element to check
 * @param {int} y Position
 * @returns {Boolean} Result
 */    
    SergsxmUITree.prototype.inHorizontalBox = function ($element, y) {
        var offset = $element.offset(), height = $element.outerHeight(true);
        return ((y >= offset.top) && (y < (offset.top + height)));
    };

/**
 * Find element in witch is position placed
 * Private method
 * 
 * @param {int} y Position
 * @returns {jQuery|null} Element
 */    
    SergsxmUITree.prototype.checkAllInHorizontalBox = function (y) {
        for (var i = 0; i < this.$elements.length; i++) {
            var $el = $(this.$elements.get(i));
            if ($el.is(this.$moveElement)) {
                continue;
            }
            if (this.inHorizontalBox($el, y)) {
                return $el;
            }
        }
        return null;
    };

/**
 * Swap current move element with specified element
 * Private method
 * 
 * @param {jQuery} $el Element to swap
 */    
    SergsxmUITree.prototype.swapWithElement = function ($el) {
        var indexNew = this.$elements.index($el), indexOld = this.$elements.index(this.$moveElement);
        this.$moveElement.detach();
        if (indexNew > indexOld) {
            $el.after(this.$moveElement);
        } else {
            $el.before(this.$moveElement);
        }
        this.$elements = this.$container.children();
        if (this.$shadowElement) {
            this.$elements = this.$elements.not(this.$shadowElement);
        }
    };

/**
 * Get nesting range 
 * Private method
 * 
 * @param {jQuery} $element Element to find nesting
 * @param {jQuery} $elements Elements
 * @returns {object} Range object {min, max}
 */    
    SergsxmUITree.prototype.getNestingRange = function ($element, $elements) {
        var index = $elements.index($element), element = (index > 0 ? $elements.get(index - 1) : undefined), ret = {min: 0, max: 0};
        if (element !== undefined) {
            var nesting = $(element).data(this.configuration.nestingDataProperty);
            ret.max = nesting;
            if ((this.configuration.maxNesting === false) || (ret.max < this.configuration.maxNesting)) {
                ret.max++;
            }
        }
        return ret;
    };

/**
 * Check value for ranges
 * Private method
 * 
 * @param {int} value Input value
 * @param {object} ranges Range object
 * @returns {int} New value
 */    
    SergsxmUITree.prototype.checkRanges = function (value, ranges) {
        if (ranges === undefined) {
            return value;
        }
        if ((ranges.min !== undefined) && (value < ranges.min)) {
            value = ranges.min;
        }
        if ((ranges.max !== undefined) && (value > ranges.max)) {
            value = ranges.max;
        }
        return value;
    };

/**
 * Start element move
 * Private method
 * 
 * @param {DOMElement} element Element to move
 * @param {int} x Current cursor position
 * @param {int} y Current cursor position
 * @returns {Boolean} Result
 */    
    SergsxmUITree.prototype.start = function (element, target, x, y) {
        if (this.$moveElement) {
            this.stop();
        }
        if (this.configuration.clickFilter.toString().toUpperCase().replace(' ', '').split(',').indexOf(target.tagName) >= 0) {
            return false;
        }
        this.$elements = this.$container.children();
        this.$moveElement = $(element);
        var offset = this.$moveElement.offset(), currentLeft = this.$moveElement.data(this.configuration.nestingDataProperty) * this.configuration.nestingPadding;
        this.moveOffsetX = x - offset.left;
        this.moveOffsetY = y - offset.top;
        this.moveCurrentX = currentLeft;
        this.moveCurrentY = 0;
        this.currentNesting = this.$moveElement.data(this.configuration.nestingDataProperty);
        this.currentNestingRanges = this.getNestingRange(this.$moveElement, this.$elements);
        this.createShadow(this.$moveElement);
        this.$moveElement.css({top: 0, left: currentLeft, 'z-index': 999});  
        return true;
    };

/**
 * Stop element move
 * Private method
 * 
 * @returns {Boolean} Result
 */    
    SergsxmUITree.prototype.stop = function () {
        if (!this.$moveElement) {
            return false;
        }
        this.destroyShadow();
        this.$moveElement.data(this.configuration.nestingDataProperty, this.currentNesting);
        this.$moveElement.css({top: 0, left: this.currentNesting * this.configuration.nestingPadding, 'z-index': 0});
        this.updateElementsNesting(true);
        this.$moveElement = null;
        return true;
    };

/**
 * Move element to position
 * Private method
 * 
 * @param {int} x Current cursor position
 * @param {int} y Current cursor position
 * @returns {Boolean} Result
 */    
    SergsxmUITree.prototype.move = function (x, y) {
        if (!this.$moveElement) {
            return false;
        }
        this.changed = true;
        var $el = this.checkAllInHorizontalBox(y);
        if ($el) {
            this.moveShadow($el);
            this.swapWithElement($el);
            this.currentNestingRanges = this.getNestingRange(this.$moveElement, this.$elements);
        }
        var offset = this.$moveElement.offset();
        var posX = x - this.moveOffsetX - offset.left + this.moveCurrentX;
        var posY = y - this.moveOffsetY - offset.top + this.moveCurrentY;
        this.$moveElement.css({top: posY, left: posX});
        this.moveCurrentX = posX;
        this.moveCurrentY = posY;
        var nesting = Math.round(posX / this.configuration.nestingPadding);
        this.currentNesting = this.checkRanges(nesting, this.currentNestingRanges);
        this.moveShadow();
        return true;    
    };

/**
 * Get changed status
 * 
 * @returns {Boolean} True if form changed
 */
    SergsxmUITree.prototype.getChangedStatus = function () {
        return this.changed;
    };

    return SergsxmUITree;
})();

/**
 * UI functions class
 */
var sergsxmUIFunctions = {
    
    locale: 'en',
    fileUploadPath: '',
    configuration: {
        mapCenter: [0, 0],
        mapZoom: 5,
    },

/**
 * Init context for UI Bundle
 * This function must call in page initialization
 * 
 * @param {string} locale Current locale code (2 letters, e.g. en, ru)
 * @param {string} fileUploadPath Ajax file upload URL
 */    
    initContext : function (locale, fileUploadPath, configuration) {
        this.locale = locale;
        this.fileUploadPath = fileUploadPath;
        $.extend(this.configuration, configuration);
    },

/**
 * Put bootstrap alert box into alerts container
 * 
 * @param {string} message Alert message
 * @param {string} type Alert type (success, error, warning, info)
 * @param {string|jQuery} container Container for alert box
 */    
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

/**
 * Show bootstrap confirm dialog window
 * 
 * @param {string} message Confirm message
 * @param {string} title Confirm dialog title
 * @param {string} okText Text on OK button
 * @param {string} cancelText Text on Cancel button
 * @param {function} callBack Callback function (when OK button is pressed)
 */    
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

/**
 * Init WYSIWYG editor area
 * Function used TinyMCE
 * 
 * @param {string} selector Textarea selector
 */    
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

/**
 * Check parameter is a function
 * 
 * @param {mixed} functionToCheck Function to check
 * @returns {Boolean} Parameter is a function
 */    
    _isFunction : function (functionToCheck) {
        var getType = {};
        return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
    },

/**
 * Init single file upload inputs
 * 
 * @param {string} selector Input selector
 * @param {string} formId Form ID (need for ajax request)
 * @param {string} fileTemplate File HTML template to insert into file container (use replaces %name%, %size%)
 * @param {function} validator Validator function (parameters: element, true)
 * @param {function} errorFunction Function to place element error (parameters: element ID, error text)
 */    
    initFileUploadInput : function (selector, formId, fileTemplate, validator, errorFunction) {
        $(selector).css({position: 'fixed', top: '-100px'});
        $(selector).change(function () {
            if ((this.files === undefined) || (this.files[0] === undefined)) {
                return false;
            }
            var inputId = ($(this).data('replace-input-id') ? $(this).data('replace-input-id') : $(this).prop('id'));
            if (sergsxmUIFunctions._isFunction(validator)) {
                var errors = validator(this, true);
                if (errors[inputId] !== undefined) {
                    return false;
                }
            }
            var $fileInput = $(this), $filesContainer = $('#'+inputId+'-files'), file = this.files[0], formData = new FormData(), inputName = $filesContainer.data('input-name');
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
                            $progressBar.find('.progress-bar').attr('aria-valuenow', completed).css({width: completed+'%'}).find('span.sr-only').text(completed+'%');
                        }
                    }, false);
                    return xhr;
                },
                success: function (data) {
                    var insertion = fileTemplate.replace('%name%', data.fileName).replace('%size%', data.size).replace('%input%', '<input type="hidden" name="'+inputName+'" value="'+data.id+'" />');
                    $fileInput.val('');
                    $filesContainer.html(insertion);
                },
                error: function (response) {
                    if (response.responseJSON.error) {
                        if (sergsxmUIFunctions._isFunction(errorFunction)) {
                            errorFunction(inputId, response.responseJSON.error);
                        } else {
                            alert(response.responseJSON.error);
                        }
                    }
                },
                complete: function () {
                    $($filesContainer).show();
                    $progressBar.remove();
                }
            });
        });    
    },
    
/**
 * Init single image upload inputs
 * 
 * @param {string} selector Input selector
 * @param {string} formId Form ID (need for ajax request)
 * @param {string} imageTemplate Image HTML template to insert into image container (use replaces %name%, %size%, %thumbnail%)
 * @param {function} validator Validator function (parameters: element, true)
 * @param {function} errorFunction Function to place element error (parameters: element ID, error text)
 */    
    initImageUploadInput : function (selector, formId, imageTemplate, validator, errorFunction) {
        $(selector).css({position: 'fixed', top: '-100px'});
        $(selector).change(function () {
            if ((this.files === undefined) || (this.files[0] === undefined)) {
                return false;
            }
            var inputId = ($(this).data('replace-input-id') ? $(this).data('replace-input-id') : $(this).prop('id'));
            if (sergsxmUIFunctions._isFunction(validator)) {
                var errors = validator(this, true);
                if (errors[inputId] !== undefined) {
                    return false;
                }
            }
            var $fileInput = $(this), $filesContainer = $('#'+inputId+'-images'), file = this.files[0], formData = new FormData(), inputName = $filesContainer.data('input-name');
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
                            $progressBar.find('.progress-bar').attr('aria-valuenow', completed).css({width: completed+'%'}).find('span.sr-only').text(completed+'%');
                        }
                    }, false);
                    return xhr;
                },
                success: function (data) {
                    var insertion = imageTemplate.replace('%thumbnail%', data.thumbnail).replace('%name%', data.fileName).replace('%size%', data.size).replace('%input%', '<input type="hidden" name="'+inputName+'" value="'+data.id+'" />');
                    $fileInput.val('');
                    $filesContainer.html(insertion);
                },
                error: function (response) {
                    if (response.responseJSON.error) {
                        if (sergsxmUIFunctions._isFunction(errorFunction)) {
                            errorFunction(inputId, response.responseJSON.error);
                        } else {
                            alert(response.responseJSON.error);
                        }
                    }
                },
                complete: function () {
                    $($filesContainer).show();
                    $progressBar.remove();
                }
            });
        });    
    },

/**
 * Init draggable elements
 * 
 * @param {string} selector Selector
 */    
    initDraggableElements : function (selector) {
        $(selector).each(function () {
            new SergsxmUIDraggableElement(this);
        });
    },
    
/**
 * Init multiply file upload inputs
 * 
 * @param {string} selector Input selector
 * @param {string} formId Form ID (need for ajax request)
 * @param {string} fileTemplate File HTML template to insert into file container (use replaces %name%, %size%)
 * @param {function} validator Validator function (parameters: element, true)
 * @param {function} errorFunction Function to place element error (parameters: element ID, error text)
 */    
    initMultiplyFileUploadInput : function (selector, formId, fileTemplate, validator, errorFunction) {
        $(selector).css({position: 'fixed', top: '-100px'});
        $(selector).change(function () {
            if ((this.files == undefined) || (this.files[0] == undefined)) {
                return false;
            }
            var inputId = ($(this).data('replace-input-id') ? $(this).data('replace-input-id') : $(this).prop('id'));
            if (sergsxmUIFunctions._isFunction(validator)) {
                var errors = validator(this, true);
                if (errors[inputName] != undefined) {
                    return false;
                }
            }
            var $fileInput = $(this), $filesContainer = $('#'+inputId+'-files'), file = this.files[0], formData = new FormData(), inputName = $filesContainer.data('input-name');
            formData.append($fileInput.prop('name'), file);
            formData.append('form_id', formId);
            formData.append('input_name', $fileInput.prop('name'));
            var $progressBar = $('<div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"><span class="sr-only">0%</span></div></div>').insertAfter($filesContainer);
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
                            $progressBar.find('.progress-bar').attr('aria-valuenow', completed).css({width: completed+'%'}).find('span.sr-only').text(completed+'%');
                        }
                    }, false);
                    return xhr;
                },
                success: function (data) {
                    var insertion = fileTemplate.replace('%name%', data.fileName).replace('%size%', data.size).replace('%input%', '<input type="hidden" name="'+inputName+'" value="'+data.id+'" />');
                    $fileInput.val('');
                    $filesContainer.append(insertion);
                },
                error: function (response) {
                    if (response.responseJSON.error) {
                        if (sergsxmUIFunctions._isFunction(errorFunction)) {
                            errorFunction(inputId, response.responseJSON.error);
                        } else {
                            alert(response.responseJSON.error);
                        }
                    }
                },
                complete: function () {
                    $progressBar.remove();
                }
            });
        });    
    },

/**
 * Init multiply image upload inputs
 * 
 * @param {string} selector Input selector
 * @param {string} formId Form ID (need for ajax request)
 * @param {string} imageTemplate Image HTML template to insert into image container (use replaces %name%, %size%, %thumbnail%)
 * @param {function} validator Validator function (parameters: element, true)
 * @param {function} errorFunction Function to place element error (parameters: element ID, error text)
 */    
    initMultiplyImageUploadInput : function (selector, formId, imageTemplate, validator, errorFunction) {
        $(selector).css({position: 'fixed', top: '-100px'});
        $(selector).change(function () {
            if ((this.files === undefined) || (this.files[0] === undefined)) {
                return false;
            }
            var inputId = ($(this).data('replace-input-id') ? $(this).data('replace-input-id') : $(this).prop('id'));
            if (sergsxmUIFunctions._isFunction(validator)) {
                var errors = validator(this, true);
                if (errors[inputId] !== undefined) {
                    return false;
                }
            }
            var $fileInput = $(this), $filesContainer = $('#'+inputId+'-images'), file = this.files[0], formData = new FormData(), inputName = $filesContainer.data('input-name');
            formData.append($fileInput.prop('name'), file);
            formData.append('form_id', formId);
            formData.append('input_name', $fileInput.prop('name'));
            var $progressBar = $('<div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"><span class="sr-only">0%</span></div></div>').insertAfter($filesContainer);
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
                            $progressBar.find('.progress-bar').attr('aria-valuenow', completed).css({width: completed+'%'}).find('span.sr-only').text(completed+'%');
                        }
                    }, false);
                    return xhr;
                },
                success: function (data) {
                    var insertion = imageTemplate.replace('%thumbnail%', data.thumbnail).replace('%name%', data.fileName).replace('%size%', data.size).replace('%input%', '<input type="hidden" name="'+inputName+'" value="'+data.id+'" />');
                    $fileInput.val('');
                    $filesContainer.append(insertion);
                },
                error: function (response) {
                    if (response.responseJSON.error) {
                        if (sergsxmUIFunctions._isFunction(errorFunction)) {
                            errorFunction(inputId, response.responseJSON.error);
                        } else {
                            alert(response.responseJSON.error);
                        }
                    }
                },
                complete: function () {
                    $progressBar.remove();
                }
            });
        });    
    },

/**
 * Init map fields
 * Function used Yandex maps
 * 
 * @param {string} selector Textarea selector
 */    
    initMap : function (selector) {
        if (($(selector).length) && (ymaps != undefined) && (typeof ymaps === 'object')) {
            ymaps.ready(function () {
                $(selector).each(function () {
                    var inputId = '#'+$(this).data('input-id'), latitudeId = '#'+$(this).data('latitude-id'), longitudeId = '#'+$(this).data('longitude-id');
                    if (($(latitudeId).val() != '') || ($(longitudeId).val() != '')) {
                        center = [$(latitudeId).val(), $(longitudeId).val()];
                    } else {
                        center = sergsxmUIFunctions.configuration.mapCenter;
                    }
                    zoom = sergsxmUIFunctions.configuration.mapZoom;
                    var inputMap = new ymaps.Map(this, {center: center, zoom: zoom});                
                    var inputPlacemark = new ymaps.Placemark([$(latitudeId).val(), $(longitudeId).val()]);
                    inputMap.geoObjects.add(inputPlacemark);
                    $(inputId).change(function () {
                        /*$.ajax({
                            url : 'https://geocode-maps.yandex.ru/1.x/',
                            type : 'GET',
                            data : {format: 'json', results: 1, geocode: $(this).val()},
                            success: function (data) {
                                var position = data.response.GeoObjectCollection.featureMember[0].GeoObject.Point.pos.toString().split(' ').reverse();
                                if (position.length == 2) {
                                    var bound = [];
                                    bound[0] = data.response.GeoObjectCollection.featureMember[0].GeoObject.boundedBy.Envelope.lowerCorner.toString().split(' ').reverse();
                                    bound[1] = data.response.GeoObjectCollection.featureMember[0].GeoObject.boundedBy.Envelope.upperCorner.toString().split(' ').reverse();
                                    inputMap.setBounds(bound); // inputMap.setCenter(position);
                                    inputPlacemark.geometry.setCoordinates(position);
                                    $(latitudeId).val(position[0]);
                                    $(longitudeId).val(position[1]);
                                }
                            }
                        });*/
                        ymaps.geocode($(this).val(), {
                            results: 1
                        }).then(function (res) {
                            var firstGeoObject = res.geoObjects.get(0),
                                coords = firstGeoObject.geometry.getCoordinates(),
                                bounds = firstGeoObject.properties.get('boundedBy');
                            inputMap.setBounds(bounds); // inputMap.setCenter(coords);
                            inputPlacemark.geometry.setCoordinates(coords);
                            $(latitudeId).val(coords[0]);
                            $(longitudeId).val(coords[1]);
                        });                        
                    });
                    inputMap.events.add('click', function (e) {
                        ymaps.geocode(e.get('coords'), {
                            results: 1
                        }).then(function (res) {
                            var firstGeoObject = res.geoObjects.get(0),
                                coords = firstGeoObject.geometry.getCoordinates(),
                                address = firstGeoObject.properties.get('text');
                            inputPlacemark.geometry.setCoordinates(coords);
                            $(latitudeId).val(coords[0]);
                            $(longitudeId).val(coords[1]);
                            $(inputId).val(address);
                        });
                    });
                });
            });
        }
    },

/**
 * Convert text to HTML escaped string
 * 
 * @param {string} text Input string
 * @returns {string} HTML escaped string
 */
    _htmlSpecialChars : function (text) {
        return $('<span>').text(text).html();
    },

/**
 * Init tags inputs
 * 
 * @param {string} selector Input selector
 * @param {string} tagTemplate Tag template
 */    
    initTagInput : function (selector, tagTemplate) {
        $(selector).on('keypress', function (e) {
            if (e.charCode != 44) {
                return true;
            }
            var inputId = ($(this).data('replace-input-id') ? $(this).data('replace-input-id') : $(this).prop('id')), 
                $tagsContainer = $('#'+inputId+'-tags'), 
                inputName = $tagsContainer.data('input-name'),
                newTags = $(this).val().toString().split(',');
            for (var i in newTags) {
                var tag = newTags[i].trim();
                if (!tag) {
                    continue;
                }
                var escapeTag = sergsxmUIFunctions._htmlSpecialChars(tag);
                var insertion = tagTemplate.replace('%tag%', escapeTag).replace('%input%', '<input type="hidden" name="'+inputName+'" value="'+escapeTag+'" />');
                $tagsContainer.append(insertion);
            }
            $(this).val('');
            return false;
        });
        $(selector).on('blur', function (e) {
            if ($(this).val() == '') {
                return true;
            }
            var inputId = ($(this).data('replace-input-id') ? $(this).data('replace-input-id') : $(this).prop('id')), 
                $tagsContainer = $('#'+inputId+'-tags'), 
                inputName = $tagsContainer.data('input-name'),
                newTags = $(this).val().toString().split(',');
            for (var i in newTags) {
                var tag = newTags[i].trim();
                if (!tag) {
                    continue;
                }
                var escapeTag = sergsxmUIFunctions._htmlSpecialChars(tag);
                var insertion = tagTemplate.replace('%tag%', escapeTag).replace('%input%', '<input type="hidden" name="'+inputName+'" value="'+escapeTag+'" />');
                $tagsContainer.append(insertion);
            }
            $(this).val('');
            return false;
        });
    },
    
};
