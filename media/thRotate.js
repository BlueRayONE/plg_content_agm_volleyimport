/**
 * Created by Alex on 05.08.2015.
 */

jQuery( document ).ready(function() {
    jQuery(".volleyImportContainer").each(function() {
        if(jQuery(this).find('table').hasClass('table-header-rotated')) {
            var table = jQuery(this).find('table');
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
                
                var th_width = jQuery(this).width();
                jQuery(this).find('span').css('left', th_width / 2);
            });

        }
    });
});
