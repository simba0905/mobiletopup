<?php 
class mobileTopupOrderCompleted {

    /**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

    private $show_customer ;
    private $group_amounts;
    public function __construct()
    {
        $this->show_customer = 1;
        $this->assets_url = esc_url( trailingslashit( plugins_url( 'mobiletopup/assets/', $this->file ) ) );
        add_action( 'woocommerce_thankyou',  array($this,'custom_thankyou_subscription_action'), 50, 1 );
        add_filter( 'woocommerce_order_status_changed', array($this,'status_orders_complete'));
        add_filter('woocommerce_payment_complete_order_status', array( $this ,'mb_autocomplete_paid_virtual_orders'), 50, 2);  
        add_action( 'woocommerce_before_save_order_items',  array($this,'my_save_post_function'), 200, 2);
        add_action('admin_init', array($this ,'checkAdmin'));
        $this->group_amounts = [ 10, 20, 30, 50, 100, 200, 300, 500, 800, 1000];
    }

    /**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
    public function my_enqueue (){
        wp_register_script( 'mobiletopup-admin-WC', esc_url( $this->assets_url ) . '../assets/js/backend-WC.js', array( 'jquery' ), $this->_version );
        wp_enqueue_style('mobiletopup-admin-WC-style',esc_url( $this->assets_url ) . '../assets/css/backend.css', array(), false, false);
		wp_enqueue_script( 'mobiletopup-admin-WC' );
    }

    public function checkAdmin(){
        $post_id = absint( isset($_GET['post']) ? $_GET['post'] : ( isset($_POST['post_ID']) ? $_POST['post_ID'] : 0 ) );
        $post = $post_id != 0 ? get_post( $post_id ) : false; // Post Object, like in the Theme loop
        $id = $post->ID;    
        if (get_post_type($id) !== 'shop_order' || $id == null) return ;

        $order = new WC_Order($id);
       
        if($order->status === 'completed'){
            return;
        }
        foreach ($order->get_items() as $item) 
        {
            $id = $item->get_product_id();
            $virtual_order = $this->checkProductsISVirtual($order,$item);
            $is_mobiletop = get_post_meta(  $id, 'active_mobiletop_plugin', true );
            // check plugin Thailand topup
            if(!$is_mobiletop) return;
            add_action( 'admin_enqueue_scripts', array( $this, 'my_enqueue' ), 10 );
           
        }
    }

    /**
     *  
     */
   
    public function custom_thankyou_subscription_action( $order_id )
    {
        if( ! $order_id ) return;
        $order = new WC_Order($order_id); // Get an instance of the WC_Order object
        // If the order not a 'completed' status 
        if (!$order->has_status( 'completed' ) ) return;
        if(!$this->checkHasorder($order))  return;    
    }

    


    private function checkProductsISVirtual($order,$order_item)
    {
        $productVirtual = false;
        $_product = $order->get_product_from_item($order_item);
        if ($_product->is_virtual()) {
            $productVirtual = true;
        } 
        return $productVirtual;

    }
  
    private function checkHasorder($order)
    {
        $results = false ;
        if (count($order->get_items()) > 0)  $results = true ;
        return $results;
    }
    
    /**
     *  convert number phone formate 
     *  Remove dash format
     *  add 0 front of 
     *  Ex 8-0000-0000 to 0800000000
     *  formate Thailand 
     */
    private function convertFormatPhone($phone){
        $phone = preg_replace('/[^\p{L}\p{N}\s]/u', '', $phone);
        $phone = '0'.$phone;
        return $phone; 

    }
    /**
     *  Check status for Api Thailand top alredy
     *  ever 3 sec.
     */
    public function checkStatus($topup_order_id){
    
        $secret_key = get_option('mbt_secret_key_api_mobiletopup');
        $topup = new TopupAPI( $order_id ,$secret_key);
        $status = $topup->get_status($topup_order_id);

        if ($status == 'SUCCESS') 
        {
            return 'success';
        } 
        elseif ($status == 'FAILED')
        {
            return 'failed';
        } 
        else 
        {
            return 'pending';
        }    
     }
     
    /**
     * Check status Api 
     */
    private function checkStatusApi($topup_order_id)
    {
        $secret_key = get_option('mbt_secret_key_api_mobiletopup');
        $topup = new TopupAPI( $order_id ,$secret_key);
        $status = $topup->get_status($topup_order_id);
        if ($status == 'SUCCESS' || $status == 'FAILED') {
            return $status;
        }
        return false;
    }

    /**
     *  update status 
     */
    private function updateStatus ($order,$amount,$target_number,$status){
        
        global $woocommerce;

        $note = $status.' top up to'.'+66'.$target_number.' for '.$amount.' THB';
        if($status == 'success')
        { 
            $order->update_status( 'completed');
        }
        $order->add_order_note( $note , $this->show_customer);
    }

    private function usedApiMobileTopup($order,$order_id,$amount,$format_number,$id ){

        // secrect key get form setting menu
        $secret_key = get_option('mbt_secret_key_api_mobiletopup');
        $is_mobiletop = get_post_meta(  $id, 'active_mobiletop_plugin', true );
        
        if($is_mobiletop)
        {
            $topup = new TopupAPI( $order_id ,$secret_key);      
            $topup_order_id = $topup->request_topup($order_id,$format_number, $amount);
            return $topup_order_id ;
        }
        
    }
    /**
     *  
     */
    public function status_orders_complete($order_id){
       
        $order = new WC_Order($order_id);
        if($order->status !== 'completed'){
            return;
        }
        $this->activeMobileTopUp($order,$order_id);
    }
    
    public function activeMobileTopUp($order,$order_id){
        // Loop cart items
        foreach ($order->get_items() as $item) 
        {
            $id = $item->get_product_id();
            $virtual_order = $this->checkProductsISVirtual($order,$item);
            $is_mobiletop = get_post_meta(  $id, 'active_mobiletop_plugin', true );
            // check plugin Thailand topup
            if(!$is_mobiletop) return;
            
            if ($virtual_order) {
                $amount_repeat = get_post_meta(  $id, 'mobiletopup_amount_repeat', true );
                // get mobile phone
                $target_number = get_post_meta( $order_id, 'target_number', true );
                $format_number = $this->convertFormatPhone($target_number);

                if(!$amount_repeat || $amount_repeat == 'Default'){
                    $this->is_not_repeact_topup($order,$order_id,$id, $format_number,$target_number);
                }else{
                  $this->is_repeat_topup($order,$order_id,$id, $format_number, $amount_repeat,$target_number);
                }
            }
        }
    }
    /**
     * 
     * 
     */
    public function is_not_repeact_topup($order,$order_id,$id, $format_number,$target_number){

        foreach($this->group_amounts as $amount) {
            
            $check_value = get_post_meta(  $id, 'mobiletopup_amount_'.$amount, true );
            if( $check_value ){
                $topup_order_id = $this->usedApiMobileTopup( $order,$order_id,$amount,$format_number,$id );
                // save order id form topup
                update_post_meta( $order_id, 'topup_order_id'.$amount, $topup_order_id );
                $sleep = 12;
                sleep($sleep);
                $status = $this->checkStatus($topup_order_id);
                
                if($status == 'pending'){
                    $sleep = 12;
                    sleep($sleep);
                    $status = $this->checkStatus($topup_order_id);
                  
                }
                
                $this->updateStatus($order,$amount,$target_number,$status);
                
            }
        }
       
    }
  
    public function is_repeat_topup($order,$order_id,$id, $format_number, $amount_repeat ,$target_number){

        switch($amount_repeat){
            case "90":
                $repeat = 3;
            break;
            case "180":
                $repeat = 6;
            break;
            case "360":
                $repeat = 12;
            break;
            default:
                $repeat = 0;
        }

        for( $i = 1; $i <= $repeat; $i++ ){
            $amount = 10;
            // used Api
           
            $topup_order_id = $this->usedApiMobileTopup( $order,$order_id,$amount,$format_number,$id );
            
            // if( !$topup_order_id) {
            //     $order->add_order_note( $note , 1);
            // }
            // save order id form topup
            update_post_meta( $order_id, 'topup_order_id', $topup_order_id );
            $sleep = 12;
            sleep($sleep);
            $status = $this->checkStatus($topup_order_id);
            if($status == 'pending'){
                $sleep = 12;
                sleep($sleep);
                $status = $this->checkStatus($topup_order_id);
              
            }
            
            $this->updateStatus($order,$amount,$target_number,$status);
        }
    }


    public function mb_autocomplete_paid_virtual_orders($order_status,$order_id){
        return 'completed';
    }
}

if (class_exists('mobileTopupOrderCompleted')){
    new mobileTopupOrderCompleted();
}


