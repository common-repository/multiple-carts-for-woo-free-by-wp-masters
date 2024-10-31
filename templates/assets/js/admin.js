jQuery(document).ready(function($) {

    jQuery("body").on("change",".select-product",function(){
        var product_id = jQuery(this).val();
        var element = jQuery(this);

        jQuery.ajax({
            type:"POST",
            url: admin.ajaxurl,
            data: {
                action: "get_variations_product",
                product_id: product_id,
                nonce: admin.nonce
            },
            success: function(results){
                if(results.status === 'true') {
                    jQuery(element).closest('.item-content').find('.variation-list').html(results.html);
                }
            }
        });
    });

    $('body').on('click', '.add-item', function() {
        var element = $(this).closest('.items-list');
        var count = element.find('.item-content').length;

        if(count === 1 && element.find('.item-content').is(':hidden')) {
            element.find('.item-content').show();
            element.closest('.section_data').find('.head_items').show();
        } else {
            $(this).before($(element).find('.item-content:last').clone());
            var counter = parseInt($(element).find('.item-content:last').find('.number_element').text());

            counter++;
            $(element).find('.item-content:last').find('.number_element').text(counter);
        }
    });

    $("body").on("click",".delete_item",function(){
        var element = $(this).closest('.items-list');
        var count = element.find('.item-content').length;

        if(count === 1) {
            element.closest('.section_data').find('.head_items').hide();
            element.find('.item-content').hide();
            element.find('.item-content').find('input').val(0);
            element.find('select option').removeAttr('selected').filter('[value=0]').attr('selected', true);
            element.find('.ss_dib.ss_text').text('No Product');
        } else {
            $(this).closest('.item-content').remove();
        }
    });
});