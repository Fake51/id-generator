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
(function(jQuery) {
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
})(jQuery);
