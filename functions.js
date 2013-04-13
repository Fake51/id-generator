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
