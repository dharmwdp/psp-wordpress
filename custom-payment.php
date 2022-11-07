    <?php
    /*
    Plugin Name: Omni Pay Payment Gateway
    Description: Omni Pay payment gateway
    Author: Nikhil Singhal
    */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    require_once __DIR__.'/omnipay-php-main/Omnipay.php';
    use Omnipay\Api\Api;
    /**
     * Omni Pay Payment Gateway.
     *
     * Create payment request and retrive responce.
     */

    add_action('plugins_loaded', 'init_custom_gateway_class');
    function init_custom_gateway_class(){
        class WC_Gateway_Custom extends WC_Payment_Gateway {
            public $domain;
            
            public function __construct() { 
                $this->domain = 'omni_pay';
                $this->id                 = 'custom';
                $this->icon               = apply_filters('woocommerce_custom_gateway_icon', '');
                $this->has_fields         = false;
                $this->method_title       = __( 'Custom', $this->domain );
                $this->method_description = __( 'Allows payments with Omni Pay', $this->domain );

                // Load the settings.
                $this->init_form_fields();
                $this->init_settings();
                //$this->process_payment();

                // Define user set variables
                $this->title        = $this->get_option( 'title' );
                $this->description  = $this->get_option( 'description' );
                $this->instructions = $this->get_option( 'instructions', $this->description );
                $this->order_status = $this->get_option( 'order_status', 'Processing' );

                // Actions
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

                // Customer Emails
                add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
            }


            /**
             * Initialise Gateway Settings Form Fields.
             */
            public function init_form_fields() {

                $this->form_fields = array(
                    'enabled' => array(
                        'title'   => __( 'Enable/Disable', $this->domain ),
                        'type'    => 'checkbox',
                        'label'   => __( 'Enable Omni Pay Payment', $this->domain ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title'       => __( 'Title', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'This controls the title which the user sees during checkout.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),

                    'test_username' => array(
                        'title'       => __( 'Test Username', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'This is use to authorize in Omni Pay.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),
                    'test_password' => array(
                        'title'       => __( 'Test Password', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'This is use to authorize in Omni Pay.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),
                    'test_secret_key' => array(
                        'title'       => __( 'Test Secret Key', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'This is use to authorize in Omni Pay.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),
                    'live_username' => array(
                        'title'       => __( 'Live Username', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'This is use to authorize in Omni Pay.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),
                    'live_password' => array(
                        'title'       => __( 'Live Password', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'This is use to authorize in Omni Pay.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),
                    'live_secret_key' => array(
                        'title'       => __( 'Live Secret Key', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'This is use to authorize in Omni Pay.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),
                    'api_mode' => array(
                        'title'       => __( 'Api Mode', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'Test mode for 0 and Live mode for 1.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),
                    'live_url' => array(
                        'title'       => __( 'Live Url', $this->domain ),
                        'type'        => 'text',
                        'description' => __( 'This is use to authorize in Omni Pay.', $this->domain ),
                        'default'     => null,
                        'desc_tip'    => true,
                    ),
                    'order_status' => array(
                        'title'       => __( 'Order Status', $this->domain ),
                        'type'        => 'select',
                        'class'       => 'wc-enhanced-select',
                        'description' => __( 'Choose whether status you wish after checkout.', $this->domain ),
                        'default'     => 'wc-processing',
                        'desc_tip'    => true,
                        'options'     => wc_get_order_statuses()
                    ),
                    'description' => array(
                        'title'       => __( 'Description', $this->domain ),
                        'type'        => 'textarea',
                        'description' => __( 'Payment method description that the customer will see on your checkout.', $this->domain ),
                        'default'     => __('Payment Information', $this->domain),
                        'desc_tip'    => true,
                    ),
                    
                    'instructions' => array(
                        'title'       => __( 'Instructions', $this->domain ),
                        'type'        => 'textarea',
                        'description' => __( 'Instructions that will be added to the thank you page and emails.', $this->domain ),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                );
            }

            /**
             * Output for the order received page.
             */
            public function thankyou_page() {
                if ( $this->instructions )
                    echo wpautop( wptexturize( $this->instructions ) );
            }

            /**
             * Add content to the WC emails.
             *
             * @access public
             * @param WC_Order $order
             * @param bool $sent_to_admin
             * @param bool $plain_text
             */
            public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
                if ( $this->instructions && ! $sent_to_admin && 'custom' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
                    echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
                }
            }



            public function payment_fields(){
                
                if ( $this->description ) {
                    if ( $this->test_mode ) {
                      $this->description .= ' Test mode is enabled. You can use the dummy credit card numbers to test it.';
                    }
                    echo wpautop( wp_kses_post( $this->description ) );
                }   ?>            

                <fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">

                <?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>  

                <input type="radio" id="paywithcard" name="paywith" value="card">
                <label for="payChoice2">Pay with card </label><br> 
                
                <input type="radio" id="paywithphone" name="paywith" value="phone">
                <label for="payChoice1">Pay with phone No.</label>            
              
                <div id="custom_input_pay_card">
                    <p class="form-row form-row-wide">
                        <label for="cardnumber" class=""><?php _e('Card Number', $this->domain); ?></label>
                        <input type="text" class="cardnumber" id="cardnumber" name="cardnumber"  value="" maxlength="16" required="cardnumber"><span class="valid_card"></span>
                    </p>
                    <p class="form-row form-row-first">
                        <label for="expirymonth" class=""><?php _e('Expiry Month & Year', $this->domain); ?></label>
                        <select name="expirymonth" id="expirymonth" class="expirymonth">                            
                        <?php
                        for ($i =1; $i <= 12; $i ++) {
                            $monthValue = $i;
                            if (strlen($i) < 2) {
                                $monthValue = "0" . $monthValue;
                            }
                            ?>
                            <option value="<?php echo $monthValue; ?>"><?php echo $monthValue; ?></option>
                        <?php }  ?>
                        </select>

                        <select name="expiryyear" id="expiryyear" class="expiryyear">                            
                            <?php
                            for ($i = date("Y"); $i <= 2030; $i ++) {
                                $yearValue = substr($i, 2);
                                ?>
                            <option value="<?php echo $yearValue; ?>"><?php echo $i; ?></option>
                            <?php  }  ?>
                        </select>

                    </p>
                    <p class="form-row form-row-last">
                        <label for="cvv" class=""><?php _e('Card Code (CVV)', $this->domain); ?></label>
                        <input type="password" class="cvv" id="cvv" name="cvv"  value="" maxlength="3" required="">
                    </p>
                </div>  

                <div id="custom_input_pay_phone">
                    <p class="form-row form-row-wide">
                        <label for="mobile" class=""><?php _e('Mobile Number', $this->domain); ?></label>
                        <input type="text" class="mobile" id="mobile" name="mobile" value="" maxlength="12" required="">
                    </p>
                    <p class="form-row form-row-wide">
                        <label for="transaction" class=""><?php _e('Merchant Note', $this->domain); ?></label>
                        <input type="text" class="" name="transaction" id="transaction"  value="" readonly>
                    </p>
                </div>  


                <div class="clear"></div>
                    <?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
                    <div class="clear"></div>
                </fieldset>

                <?php
            }

            /**
             * Process the payment and return the result.
             *
             * @param int $order_id
             * @return array
             */
            public function process_payment($order_id) {  
                /*echo '<pre>';
                print_r($_POST); die;*/
                $a = get_option( 'woocommerce_custom_settings' );

                $order = wc_get_order( $order_id );
                $status = 'wc-' === substr( $this->order_status, 0, 3 ) ? substr( $this->order_status, 3 ) : $this->order_status;

                // Set order status
                $order->update_status( $status, __( 'Checkout with Omni Pay. ', $this->domain ) );
                //card  

                //card & mobile test or live
               /* $secret_key = '2c7632a8592de913288294eba0c01925';
                $api_user_name = 'psp_test.wyetz8tm.d3lldHo4dG04ZDA3ZQ==';
                $api_password = 'N05haFVJQi9xajJSUXpkWmFvM3lRejdUNmQ4R0ZGcnJCVC8xampWMWNyMD0=';

                $apiMode = 0; //0 for test and 1 for live 
*/
                $apiMode = isset($a["api_mode"])?$a["api_mode"]:0;  
                $fieldKey = !empty($apiMode)?'live':'test';  
                $secret_key = $a[$fieldKey."_secret_key"];
                $api_user_name = $a[$fieldKey."_username"];
                $api_password = $a[$fieldKey."_password"];
                
                $api = new Api($api_user_name, $api_password, $apiMode);                      

                $radioVal = $_POST["paywith"];  

                if($radioVal == "card") {   
                    $cartno = $_POST['cardnumber'];
 
                    $paymentParm = array(
                        'customer' =>array(
                            'name'=>$_POST['billing_first_name'].' '.$_POST['billing_last_name'], 
                            'email'=>$_POST['billing_email']
                        ) ,
                        'order'=>array(
                            'id' => $order_id,
                            'amount'=>$order->get_total(), 
                            'currency' => $order->get_currency()
                        ),
                        'sourceOfFunds' => array(
                            'provided'=>array(
                                'card'=>array(
                                    'number'=>$_POST['cardnumber'],
                                    'expiry'=>array(
                                        'month'=>$_POST['expirymonth'],
                                        'year'=>$_POST['expiryyear']
                                    ), 
                                    'cvv'=>$_POST['cvv']
                                )
                            ), 
                            'cardType' => 'C'
                        ), 
                        'remark'=>array(
                            'description'=>'This payment is done by card'
                        )
                    );                 

                    $encripted_result = $api->encryptDecrypt->create($paymentParm, $secret_key, 'encrypt');

                    $content = !empty($encripted_result['content'])?$encripted_result['content']:['message'=>__('Something went wrong')];
                    if(!empty($encripted_result['code']) && $encripted_result['code'] == 200){
                    //echo 123; die;
                        $param['trandata'] = $encripted_result['content']['apiResponse'];
                        $result = $api->payment->createPayment($param);
                        //echo '<pre>'; print_r($result->apiResponse->verifyUrl);die;
                        if(!empty($result->status) && $result->status==200) {
                            // Reduce stock levels
                            //$order->reduce_order_stock();
                            // Remove cart
                            return array(
                                'result' => 'success',
                                'redirect' => $result->apiResponse->verifyUrl
                            );
                        } else {
                            $result['message']=!empty($result['message'])?$result['message']:$content['message'];
                            wc_add_notice( __($result['message'], $this->domain ), 'error' );
                        } 
                    } else {
                        // Return thankyou redirect
                        wc_add_notice( __($content['message'], $this->domain ), 'error' );
                    }                                   

                } 
                if($radioVal == "phone") {                
                    $paymentParm = array(
                        'Customer' =>array(
                            'Name'=>$_POST['billing_first_name'].' '.$_POST['billing_last_name'], 
                            'Email'=>$_POST['billing_email']
                        ),                        
                        'DirectPaymentAuthorizeV4RequestMessage' => array(
                            'Id' => $order_id,
                            'MobileNo'=> $_POST['mobile'],
                            'Amount'=> $order->get_total(),                            
                            'MerchantNote'=>$_POST['transaction']                 
                        )
                    );
                    

                    $encripted_result = $api->encryptDecrypt->create($paymentParm, $secret_key, 'encrypt');

                  
                    $content = !empty($encripted_result['content'])?$encripted_result['content']:['message'=>__('Something went wrong')];
                    if(!empty($encripted_result['code']) && $encripted_result['code'] == 200){
                    //echo 123; die;
                        $param['trandata'] = $encripted_result['content']['apiResponse'];
                        $result = $api->payment->stcPay($param);
                        //echo '<pre>'; print_r($result->apiResponse->verifyUrl);die;
                        if(!empty($result->status) && $result->status==200) {
                            // Reduce stock levels
                            //$order->reduce_order_stock();
                            // Remove cart
                            return array(
                                'result' => 'success',
                                'redirect' => $result->apiResponse->verifyUrl
                            );
                        } else {
                            $result['message']=!empty($result['message'])?$result['message']:$content['message'];
                            wc_add_notice( __($result['message'], $this->domain ), 'error' );
                        } 
                    } else {
                        // Return thankyou redirect
                        wc_add_notice( __($content['message'], $this->domain ), 'error' );
                    }               
                } 
            }
        }
    }

    add_filter( 'woocommerce_payment_gateways', 'add_custom_gateway_class' );
    function add_custom_gateway_class( $methods ) {
        $methods[] = 'WC_Gateway_Custom'; 
        return $methods;
    }

    add_action('woocommerce_checkout_process', 'process_custom_payment');
    function process_custom_payment(){

        if($_POST['payment_method'] != 'custom')
            return;
        if( !isset($_POST['cardnumber']) || empty($_POST['cardnumber']) )
            wc_add_notice( __( 'Please add your card number', $this->domain ), 'error' );

        if( !isset($_POST['expirymonth']) || empty($_POST['expirymonth']) )
            wc_add_notice( __( 'Please add valid expiry month', $this->domain ), 'error' );
        if( !isset($_POST['expiryyear']) || empty($_POST['expiryyear']) )
            wc_add_notice( __( 'Please add valid expiry year', $this->domain ), 'error' );

        if( !isset($_POST['cvv']) || empty($_POST['cvv']) )
            wc_add_notice( __( 'Please add valid Cvv Number', $this->domain ), 'error' );

        if( !isset($_POST['mobile']) || empty($_POST['mobile']) )
            wc_add_notice( __( 'Please add valid Mobile Number', $this->domain ), 'error' );

        if( !isset($_POST['transaction']) || empty($_POST['transaction']) )
            wc_add_notice( __( 'Please add valid Mobile Number', $this->domain ), 'error' );


    }

    /**
     * Update the order meta with field value
     */
    add_action( 'woocommerce_checkout_update_order_meta', 'custom_payment_update_order_meta' );
    function custom_payment_update_order_meta( $order_id ) {

        if($_POST['payment_method'] != 'custom')
            return;
    }

    /**
     * Display field value on the order edit page
     */
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'custom_checkout_field_display_admin_order_meta', 10, 1 );
    function custom_checkout_field_display_admin_order_meta($order){
        $method = get_post_meta( $order->id, '_payment_method', true );
        if($method != 'custom')
            return;

    } 


    add_action('wp_enqueue_scripts','ava_test_init');
    function ava_test_init() {
        wp_enqueue_style( 'mystyle', plugins_url( 'assets/css/mystyle.css', __FILE__ ));
        wp_enqueue_script( 'myscript', plugins_url( 'assets/js/myscript.js', __FILE__ ));
        //wp_enqueue_script( 'jquery.min', plugins_url( 'assets/js/jquery.min.js', __FILE__ ));

    }


    



       