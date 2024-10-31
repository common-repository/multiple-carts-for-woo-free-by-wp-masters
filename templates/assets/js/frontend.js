jQuery(document).ready(function($) {
    $('body').on('change', 'form.woocommerce-checkout input', function(){
        $('body').trigger("update_checkout");
    });
});