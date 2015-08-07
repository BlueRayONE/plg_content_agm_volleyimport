/**
 * Created by Alex on 05.08.2015.
 */

/*jQuery( document ).ready(function() {
    jQuery(".volleyImportContainer").each(function() {
        var tr = jQuery(this).find('tbody').find('tr:first-child');
        tr.find('td').each(function() {
            var td = jQuery(this);
            var width = td.width();

            //find th
            var th = td.closest('table').find('th').eq(td.index());


            if(th.hasClass('rotate')) {
                var div = th.find('div');
                div.css('width', width);
                div.css('transform', 'translate3d('+ width  +'px, 1px, 0) rotate(-45deg)');
            }
        });
    });
});*/

jQuery( document ).ready(function() {
    jQuery(".volleyImportContainer").each(function() {
        if(jQuery(this).find('table').hasClass('table-header-rotated')) {
            var table = jQuery(this).find('table');
            var thead = table.find('thead');
            var tr = table.find('tbody').find('tr:first-child');
            var th_first_rotated_child = table.find('th.rotate-45').first();
            var th_height = th_first_rotated_child.css('height').replace("px", "");
            var td_target = tr.find('td').last();
            var td_target_width_padding = td_target.outerWidth() - td_target.width() - 1;
            var td_target_width_after = th_height - td_target_width_padding;

            console.log('Hoehe: ' + th_height);
            console.log('Target B4: ' + td_target_width_padding);
            console.log('Target AF: ' + td_target_width_after);

            td_target.css('width', td_target_width_after);

            thead.find('th.rotate-45').each(function() {
                jQuery(this).find('div').css('left', (th_height * Math.tan(45*Math.PI / 180)) / 2);
            });



            console.log(th_height);
            /*var tr = jQuery(this).find('tbody').find('tr:first-child');
            var td_sum_width = 0;
            var container = jQuery(this);
            var container_width = container.width();
            console.log(container);

            console.log(tr);
            tr.find('td').each(function() {
                var td = jQuery(this);
                var width = td.width();
                td_sum_width = td_sum_width + width;
                console.log(td);
                console.log('#########');
                console.log('TD Width ' + width);
                console.log('Sum ' + td_sum_width);
            });
            console.log(tr);
            console.log("Table Width: " + container_width);
            console.log("TD SUM Width: " + td_sum_width);
            console.log("Diff Width: " + (container_width - td_sum_width));
            tr.find('td').last().css('width', container_width - td_sum_width);*/
        }
    });
});