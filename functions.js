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
        var x = 1024 + (jQuery('#x').val()*1) - 345,
            y = 382 + (jQuery('#y').val()*1) - 445;

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

    jQuery(function() {
        setPhotoPosition();

        $('button.move-up').click(function() {
            setY(-10);
        });

        $('button.move-down').click(function() {
            setY(10);
        });

        $('button.move-left').click(function() {
            setX(-10);
        });

        $('button.move-right').click(function() {
            setX(10);
        });

        $('button.next').click(next);
        $('button.wipe-all').click(ask);
    });
}

function settings_init($) {
    "use strict";

    function removeTemplateHandler(e) {
        var self = $(this);

        if (confirm('Are you sure you want to delete this template? It cannot be undone')) {
            $.ajax({
                url: "ajax.php",
                type: "GET",
                data: {filename: self.data('filename'), action: 'delete-template', ajax: true},
                success: function() {
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
            html_template = $('#template-list-item').text();

        function appendNewTemplate() {
            var data = JSON.parse(xhr.responseText),
                html = html_template.replace(/!filename!/, data.filename).replace(/!name!/, data.template);

            $('section.templates ul').append(html);
            form.find('input[name="template-file"]').val('');
            self.attr('disabled', false);
            igu.messageBox({message: 'Template uploaded', type: 'success'});
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

    $('section.templates ul').on('click.remove-template', 'img.remove-template', removeTemplateHandler);

    if (window.FormData) {
        $('button.upload-template').click(uploadTemplateHandler);
    }
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
