<?php

class woocommerceAddItemMeta{

    public function  __construct (  ) {

        add_action( 'woocommerce_before_add_to_cart_button', array($this,'custom_product_fields') , 10 );
        add_filter( 'woocommerce_add_cart_item_data',  array($this,'order_session_mobilephone'), 10, 3);
        add_action( 'woocommerce_after_order_notes', array($this,'my_custom_checkout_field' ));
        add_filter( 'woocommerce_get_item_data', array($this,'display_mobile_phone_cart'), 10, 2 );
        add_action( 'woocommerce_checkout_update_order_meta',  array($this,'my_custom_checkout_field_update_order_meta') );
        add_action( 'woocommerce_admin_order_data_after_billing_address', array($this,'my_custom_checkout_field_display_admin_order_meta'));
        add_action('woocommerce_add_order_item_meta',array($this,'add_values_to_order_item_meta'), 10, 3 );

    }
    /*
     * Show text field before add to cart button
     *  //woocommerce_before_add_to_cart_button
     */ 
    public function custom_product_fields() {

        global $product;
        $product_id = $product->get_id();
        $mobie_top_up = get_post_meta( $product_id ,'active_mobiletop_plugin', true );
     
        if( ! $product->is_type( 'simple' ) || $mobie_top_up == '' || $mobie_top_up == 0 || $mobie_top_up == NULL  ) return; // Only for simple products
        $is_sold_individually =  $product->is_sold_individually();
    ?>

        <div id="mobiletopup_phone_wrap">
            <ul id="mb-topup-number-container" style="position: relative;" class="">
                <li class="mb-airtime-btn">
                    <div class="mb-btn-icons mb-ltr">
                        <div class="mb-prefix-wrap" style="display: inline-block;">
                            <div id="mb-airtime-flag" class="mb-flag-rounded-small imagerepo-spr-flag imagerepo-spr-flag-TH"></div>
                            <span id="mb-airtime-prefix">+66</span>
                           
                        </div>
                    </div>                       
                </li>
                <li>
                    <input required  autocomplete="off" id="mobiletopup_phone" name="mobiletopup_phone" class="input-text" placeholder="<?php echo _e('Start with 6, 8 or 9', 'mobiletopup')?>" value="">
                </li>
            </ul>
        </div>
        <br clear="all">
    <?php 
    }
     /**
     * Add mobile phone to sesstion cart item.
     *  //woocommerce_add_cart_item_data
     * @param array $cart_item_data
     * @param int   $product_id
     * @param int   $variation_id
     *
     * @return array
     */
    public function order_session_mobilephone( $cart_item_data, $product_id, $variation_id) {

    
        if( isset( $_REQUEST['mobiletopup_phone'] ) ) {
            $cart_item_data[ 'mobiletopup_phone' ] = $_REQUEST['mobiletopup_phone'];
            // below statement make sure every add to cart action as unique line item
            $cart_item_data['unique_key'] = md5( microtime().rand() );
            WC()->session->set( 'mobiletopup_order_data_number', $_REQUEST['mobiletopup_phone'] );
        }

        return $cart_item_data;
    }

     // Add a hidden field with the correct value to the checkout
     function my_custom_checkout_field( $checkout ) {
        $value = WC()->session->get( 'mobiletopup_order_data_number' );
        echo '<div id="mobiletopup_order_data_number">
                <input type="hidden" class="input-hidden" name="mobiletopup_phone" id="mobiletopup_phone" value="' . $value . '">
        </div>';
    }

    /**
     * Display mobile phone in the cart.
     *
     * @param array $item_data
     * @param array $cart_item
     *
     * @return array
     */
    public function display_mobile_phone_cart(  $cart_data, $cart_item = null ) {

        $custom_items = array();
        if( !empty( $cart_data ) ) $custom_items = $cart_data;

        if( isset( $cart_item['mobiletopup_phone'] ) ){
            $custom_items[] = array( "name" => 'Thailand Phone', "value" => '</br>+66'.$cart_item['mobiletopup_phone'] );
        }
        
        return $custom_items;
    }

    // woocommerce_checkout_update_order_meta
    // Save the order meta with hidden field value
    public function my_custom_checkout_field_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['mobiletopup_phone'] ) ) {
            
            update_post_meta( $order_id, 'target_number', $_POST['mobiletopup_phone'] );
        }
    }

    // Display field value on the order edit page (not in custom fields metabox)
    public function my_custom_checkout_field_display_admin_order_meta($order){
        $target_number = get_post_meta( $order->id, 'target_number', true );
        if ( ! empty( $target_number ) ) {
            echo '<p><strong>'. __("Thailand Phone", "mobiletopup").':</strong> ' .'+66'. get_post_meta( $order->id, 'target_number', true ) . '</p>';
        }
    }

    // Add the information as meta data so that it can be seen as part of the order
    function add_values_to_order_item_meta( $item_id, $cart_item, $cart_item_key ) {
        // lets add the meta data to the order (with a label as key slug)
        if( ! empty( $cart_item['mobiletopup_phone'] ) )
            wc_add_order_item_meta($item_id, __('Thailand Phone'), $cart_item['mobiletopup_phone'], true);
    } 
}

new woocommerceAddItemMeta();