jQuery( document ).ready(function() {
    jQuery('#custom_input_pay_phone').hide();
    jQuery("input[value='cart']").attr('checked', true);
});

jQuery('input[name="paywith"]').click(function(){
    $paywith = jQuery('input[name="paywith"]:checked').val();

    if($paywith == 'phone') {
        jQuery('#custom_input_pay_phone').show();
        jQuery('#custom_input_pay_cart').hide();
    }else if($paywith == 'cart'){
        jQuery('#custom_input_pay_cart').show();
        jQuery('#custom_input_pay_phone').hide();
    }else{
        jQuery('#custom_input_pay_cart').show();
    }
});