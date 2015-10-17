/**
 * Copyright (c) 2013, Peter Lind & Kristoffer Mads Sørensen
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies, 
 * either expressed or implied, of the FreeBSD Project.
 *
 * @copyright Copyright (c) 2013 Peter Lind & Kristoffer Mads Sørensen
 * @license   https://github.com/Fake51/id-generator/blob/master/LICENSE link FreeBSD license
 * @link      https://github.com/Fake51/id-generator
 * @version   0.1
 * @author    Peter Lind <peter.e.lind@gmail.com>
 */
function editor_init(jQuery) {
    "use strict";

    var $ = jQuery;

    function setX(num){
        num = jQuery('#x').val() * 1 + num;
        jQuery('#x').val(num);
        jQuery('#text_x').html(num);
        setPhotoPosition();
    }

    function setY(num){
        num = jQuery('#y').val() * 1 + num;
        jQuery('#y').val(num);
        jQuery('#text_y').html(num);
        setPhotoPosition();
    }

    function setPhotoPosition(){
        var x = jQuery('#x').val(),
            y = jQuery('#y').val();

        jQuery('#photo').css({left: x + "px", top: y + "px"});
    }

    function next(e) {
        e.preventDefault();
        e.stopPropagation();
        window.location.href = next_location;
        return false;
    }

    function ask() {
        var v = prompt("Skriv 'SLET' med store bogstaver hvis vi skal starte forfra");
        if (v == "SLET") {
            window.location.href = 'index.php?view=delete';
        }

    }

    /**
     * handles template upload event
     *
     * @param Event e Click event triggered
     *
     * @return void
     */
    function uploadPhotoHandler(e) {
        var self          = $(this),
            form          = self.closest('form'),
            formdata      = new FormData(form[0]),
            xhr           = new XMLHttpRequest();

        e.preventDefault();
        e.stopPropagation();

        self.attr('disabled', true);

        formdata.append('ajax-call', 'true');

        xhr.open(form.attr('method'), form.attr('action'), true);
        xhr.onreadystatechange = function(e) {
            if (xhr.readyState == 4) {
                form.find('input[name="photo"]').val('');
                self.attr('disabled', false);

                if (xhr.status != 200) {
                    igu.messageBox({message: 'Failed to upload photo', type: 'error', timeout: 0});

                } else {
                    igu.messageBox({message: 'Photo uploaded', type: 'success'});
                }
            }
        };

        xhr.send(formdata);
    }

    if (window.FormData) {
        $('button.upload-photo').click(uploadPhotoHandler);
    }

    function movePhoto(axis, amount, e) {
        e.stopPropagation();
        e.preventDefault();

        if (axis === 'x') {
            setX(amount);
        } else {
            setY(amount);
        }

        return false;
    }

    jQuery(function() {
        setPhotoPosition();

        $('button.move-up').click(function(e) {
            return movePhoto('y', -10, e);
        });

        $('button.move-down').click(function(e) {
            return movePhoto('y', 10, e);
        });

        $('button.move-left').click(function(e) {
            return movePhoto('x', -10, e);
        });

        $('button.move-right').click(function(e) {
            return movePhoto('x', 10, e);
        });

        $('button.next').click(next);
        $('button.wipe-all').click(ask);
    });
}

function settings_init($, canvas_settings) {
    "use strict";

    var settings = {},
        listeners = {
            template_upload: [],
            template_remove: []
        },
        canvas;

    /**
     * notifies potential event listeners about
     * an event
     *
     * @param string event_name Name of event to publish
     * @param object data       Data from publisher
     *
     * @return void
     */
    function notify(event_name, data) {
        var callbacks;

        if (listeners[event_name] && listeners[event_name].length) {
            callbacks = listeners[event_name];
            for (var index = callbacks.length; index; --index) {
                if (typeof callbacks[index - 1] == 'function') {
                    window.setTimeout((function(callback) {
                        return function() {
                            callback.call(null, data);
                        };
                    })(callbacks[index - 1]), 0);
                }
            }
        }
    }

    function removeTemplateHandler(e) {
        var self = $(this);

        if (confirm('Are you sure you want to delete this template? It cannot be undone')) {
            $.ajax({
                url: "ajax.php",
                type: "GET",
                data: {filename: self.data('filename'), action: 'delete-template', ajax: true},
                success: function() {
                    notify('template_remove', {filename: self.data('filename'), name: self.parent().text()});

                    self.parent()
                        .fadeOut(300, function() {
                            self.remove();
                        });
                },
                error: function() {
                    igu.messageBox({message: 'Could not delete template', type: 'error', timeout: 0});
                }
            });
        }
    }

    /**
     * handles template upload event
     *
     * @param Event e Click event triggered
     *
     * @return void
     */
    function uploadTemplateHandler(e) {
        var self          = $(this),
            form          = self.closest('form'),
            formdata      = new FormData(form[0]),
            xhr           = new XMLHttpRequest(),
            html_template = $('#template-list-item').html();

        function appendNewTemplate() {
            var data = JSON.parse(xhr.responseText),
                html = html_template.replace(/!filename!/, data.filename).replace(/!name!/, data.template);

            $('section.templates ul').append(html);
            form.find('input[name="template-file"]').val('');
            self.attr('disabled', false);
            igu.messageBox({message: 'Template uploaded', type: 'success'});

            notify('template_upload', {filename: data.filename, name: data.template});
        }

        e.preventDefault();
        e.stopPropagation();

        self.attr('disabled', true);

        formdata.append('ajax-call', 'true');

        xhr.open(form.attr('method'), form.attr('action'), true);
        xhr.onreadystatechange = function(e) {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    appendNewTemplate();

                } else {
                    igu.messageBox({message: 'Failed to upload template', type: 'error', timeout: 0});
                    self.attr('disabled', false);
                }
            }
        };

        xhr.send(formdata);
    }

    /**
     * sets up the background image handling
     * for the canvas editing
     *
     * @return void
     */
    function setupCanvasBackgroundHandling() {
        var background_select = $('select.background'),
            temp;

        $('section.templates li').each(function() {
            temp = $(this);

            background_select.append('<option value="' + temp.find('img').data('filename') + '">' + temp.text() + '</option>');
        });

        background_select.on('change', function() {
            settings.setCanvasBackground(canvas_settings.template_path + $(this).val());
        });

        if (background_select.val()) {
            background_select.trigger('change');
        }

        listeners.template_upload.push(function(data) {
            background_select.append('<option value="' + data.filename + '">' + data.name + '</option>');
        });

        listeners.template_remove.push(function(data) {
            background_select.children().each(function() {
                if (this.value == data.filename) {
                    $(this).remove();
                }
            });

            background_select.trigger('change');
        });
    }

    /**
     * saves the configuration of the photo and text boxes
     *
     * @param Event event Event triggered
     *
     * @return void
     */
    function saveFieldConfiguration(event) {
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            data: {action: 'save-field-configuration', configuration: JSON.stringify(settings.getGroupConfiguration())},
            success: function(data) {
                igu.messageBox({message: 'Configuration saved', type: 'success'});
            },
            error: function(jqXHR) {
                igu.messageBox({message: 'Failed to save configuration', type: 'error', timeout: 0});
            }
        });
    }

    /**
     * sets up the canvas template editing
     *
     * @return void
     */
    function setupTemplateEditing() {
        var boxes = canvas_settings.boxes,
            groups = {},
            rect,
            text,
            group;

        /**
         * returns data on current group configuration
         *
         * @return object
         */
        function getGroupConfiguration() {
            var config = {},
                state,
                group;

            for (var name in groups) {
                if (groups.hasOwnProperty(name)) {
                    group = groups[name];
                    state = group.saveState();

                    config[name] = {
                        x:      state.left,
                        y:      state.top,
                        width:  state.currentWidth,
                        height: state.currentHeight,
                        angle:  state.angle
                    };
                }
            }

            return config;
        }

        canvas = new fabric.Canvas('template-container', {
            backgroundColor: 'rgba(255, 255, 255, 1)'
        });

        for (var index = boxes.length; index; index--) {
            // create a rectangle object
            rect = new fabric.Rect({
                fill: boxes[index - 1].color,
                width: boxes[index - 1].width,
                height: boxes[index - 1].height
            });

            text = new fabric.Text(boxes[index - 1].name, {
                fontSize: 30,
                fill: 'white'
            });

            group = new fabric.Group([text, rect], {
                originX: 'left',
                originY: 'top',
                left: boxes[index - 1].offset_x,
                top: boxes[index - 1].offset_y,
                angle: boxes[index - 1].angle
            });

            // "add" rectangle onto canvas
            canvas.add(group);
            groups[boxes[index - 1].name] = group;

            canvas.on('mouse:down', function(options) {
                if (options.target) {
                    options.target.bringToFront();
                }
            });
        }

        settings.setCanvasBackground = function(url) {
            canvas.setBackgroundImage(url, function() {
                canvas.renderAll();
            });
        };

        settings.getGroupConfiguration = getGroupConfiguration;

        setupCanvasBackgroundHandling();

        $('button.save-field-configuration').click(saveFieldConfiguration);
    }

    $('section.templates ul').on('click.remove-template', 'img.remove-template', removeTemplateHandler);
    $('div.draggable').draggable({containment: 'parent'});

    if (window.FormData) {
        $('button.upload-template').click(uploadTemplateHandler);
    }

    if (canvas_settings) {
        setupTemplateEditing();
    }

    return settings;
}

var igu = (function($) {
    var igu = {};

    igu.messageBox = function(options) {
        var classname = 'js-alertbox',
            box;

        function removeBox() {
            box.remove();
        }

        if (!options.message) {
            return;
        }

        if (options.type) {
            switch(options.type.toLowerCase()) {
            case 'error':
            case 'success':
            case 'warning':
            case 'info':
                classname += ' ' + options.type.toLowerCase();
            }
        }

        box = $('<div/>', {'class': classname}).text(options.message);
        if (options['class']) {
            box.addClass(options['class']);
        }

        box.appendTo($('body'));

        if (options.timeout !== 0) {
            window.setTimeout(removeBox, options.timeout ? options.timeout : 3000);

        } else {
            box.append($('<img/>', {alt: 'Close dialog box', src: 'images/close.png'}))
                .on('click.removeBox', 'img', removeBox);
        }
    }

    return igu;
})(jQuery);
