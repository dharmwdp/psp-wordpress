# psp-wordpress

#in woocommerce plugin 

1. please open templates folder and after this you need to go in checkout folder

2. in checkout folder you will see many files regarding to checkout

 2.1 : please replace form-checkout.php with this file

 2.2 : please replace thankyou.php file with this file




#Please add this script in footer.php file

<script>
jQuery( document ).ready(function() {    
    jQuery("input[value='card']").attr('checked', true);  
});

setTimeout(function() {
        jQuery( document ).ready(function() {    
            jQuery('#cardnumber,  #cvv').val('');        
            jQuery('#mobile').val('966557877988');
      jQuery('#transaction').val('STC Payment');     
        });

        jQuery(document).on('click','input[name="paywith"]',function(){
            $paywith = jQuery('input[name="paywith"]:checked').val(); 
            if($paywith == 'card'){                    
                jQuery('#custom_input_pay_card').show();
                jQuery('#custom_input_pay_phone').hide();
                jQuery('#cardnumber, #cvv').val('');         
                jQuery('#mobile').val('966557877988');
          jQuery('#transaction').val('STC Payment');       
            }else if($paywith == 'phone') {                    
                jQuery('#custom_input_pay_phone').show();
                jQuery('#custom_input_pay_card').hide();
                jQuery('#cardnumber').val('1234 1234 1234 1234');            
            jQuery('#cvv').val('123');
            jQuery('#mobile, #transaction').val('');                  
            }else{                    
                jQuery('#custom_input_pay_card').show();
                jQuery('#custom_input_pay_phone').hide();
                //alert(3);
            }

        fname = jQuery('#billing_first_name').val(); 
        lname = jQuery('#billing_last_name').val();
        jQuery('#transaction').val(fname+' '+lname);
        });

        jQuery('.cardnumber, .expirymonth, .expiryyear, .mobile, .cvv').keypress(function (e) {                 
      var charCode = (e.which) ? e.which : event.keyCode  
      if (String.fromCharCode(charCode).match(/[^0-9]/g))  
          return false; 
          }); 

          jQuery('#place_order').click(function(e) {  
    e.preventDefault();
        oCardnumber = jQuery('.cardnumber').val(); 
        oCVV = jQuery('.cvv').val();
        oMoblile = jQuery('.mobile').val(); 

        jQuery('.text-danger').remove(); 

        isErr  = 1;        

        if(oCardnumber == ''){            
          isErr  = 0;
          jQuery('.cardnumber').after('<span class="text-danger">Please enter card number</span>');
        }

        if(oCVV == ''){            
          isErr  = 0;
          jQuery('.cvv').after('<span class="text-danger">Please enter cvv number</span>');
        }        
        if(oMoblile == ''){            
          isErr  = 0;
          jQuery('.mobile').after('<span class="text-danger">Please enter mobile number</span>');
        }       

        if(isErr == 1){            
            jQuery('.woocommerce-checkout').submit();
            //window.location.reload();
        }
        //validate(visa);    
    });     
     
    jQuery("#billing_first_name").blur(function(){                
        fname = jQuery(this).val();                
        jQuery('#transaction').val(fname);

        lname = jQuery('#billing_last_name').val(); 
        if(lname.length > 1){
            jQuery('#transaction').val(fname+' '+lname);
        }

    });

    jQuery("#billing_last_name").blur(function(){                
        fname = jQuery('#billing_first_name').val(); 
        lname = jQuery(this).val();                
        jQuery('#transaction').val(fname+' '+lname);
    });

}, 3000);



</script>
