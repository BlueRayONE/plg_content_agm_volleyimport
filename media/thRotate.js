/**
 * Created by Alex on 05.08.2015.
 */

jQuery( document ).ready(function() {
    jQuery(".volleyImportContainer").each(function() {
        if(jQuery(this).find('table').hasClass('table-header-rotated')) {
            var table = jQuery(this).find('table');
            var i = 0;
            var array_rot_th_width = table.parent().getRealDimensions(false);

            var thead = table.find('thead');
            var tr = table.find('tbody').find('tr:first-child');
            var th_first_rotated_child = table.find('th.rotate-45').first();
            var th_height = th_first_rotated_child.css('height').replace("px", "");
            var td_target = tr.find('td').last();
            var td_target_width_padding = td_target.outerWidth() - td_target.width();
            var td_target_width_after = th_height - td_target_width_padding + 4;

            td_target.css('width', td_target_width_after);

            thead.find('th.rotate-45').each(function() {
                jQuery(this).find('div').css('left', (th_height * Math.tan(45*Math.PI / 180)) / 2);

                /*new ResizeSensor(jQuery('.volleyImportContainer').find('table'), doSomething());*/

                /*var th_width = jQuery(this).getRealDimensions(true);
                console.log(th_width);*/

                jQuery(this).find('span').css('left', array_rot_th_width[i] / 2);
                i++;
            });

        }
    });
});




(function( $ ){
    $.fn.getRealDimensions = function (outer) {
        var $this = $(this);
        var i = 0;

        var return_array = new Array();

        if ($this.length == 0) {
            return false;
        }

        var insertAfter = ($this.is(':hidden')) ? $this.closest(':visible') : $this ;
        console.log(insertAfter);

        var $clone = $this.clone()
            .show()
            .css('visibility','hidden')
            .appendTo(insertAfter);


        $clone.find('thead').find('th.rotate-45').each(function() {
            return_array[i] = (outer) ? $(this).outerWidth() : $(this).innerWidth();
            i = i + 1;
        });

        $clone.remove();
        return return_array;
    }
})( jQuery );
