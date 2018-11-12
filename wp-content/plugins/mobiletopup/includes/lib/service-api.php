<?php

class TopupAPI {

    /**
    * Groupon
    *
    * Secret Key: sk_10473e927102ac077e2f5ac61e084117
    * Publishable Key: pk_b0e6ce7b8ec297211c4608bcea45c1d9  
    *       
    * TPPC: sk_33b28dd173cbeb8ec065abab933aa7f1          
    *            pk_c0a3a2b76b96542a4160a481909addf2
    */

    private $secret = "";
    private $url = "https://thailandtopup.com/api/v1/";
    private $order_id;
    public $result = false;
    public $error;
    public $status_errors = array();
    public $statuses = array(
        "PENDING", "SUCCESS", "FAILURE", "RECALLED"
    );

    public function __construct($id,$secret) {
        $this->order_id = $id;
        $this->secret = $secret;
    }
    
    public function get_topup_token($number) {

        $url = $this->url . "get-topup-token";
        $auth = base64_encode( $this->secret . ':');
        $args = [
            'headers' => [
                'Authorization' => "Basic $auth"
            ],
            'body'    => [
                'number' => $number
            ],
        ];      
        $response =  wp_remote_get($url,$args);
        $response_body = wp_remote_retrieve_body( $response );
        $ret = json_decode($response_body);
       
        try {
            if (is_object($ret) && isset($ret->uid)) {
                    return $ret->uid;
                } elseif (isset($ret->error)) {
                    throw new Exception("Error: $ret->error->message");
            }
        } catch (Exception $ex) {
            error_log($ex->getMessage());
            return false;
        }
    }

    /**
     * Request topup to the API
     * @param string $phone Phone number
     * @param int $amount Topup credit
     * @param string $network Carrier
     * @return array JSON decoded CURL response
     */
    public function request_topup($post_id,$phone, $amount, $network = null) {
        global $wpdb;
        // wait for api same number
        $sleep = 15;
        sleep($sleep);

        $token = $this->get_topup_token($phone);
        if (!$token) {
            return false;
        }
        
        // get networking
        $network = get_option('mbt_radio_networking_mobiletopup');
        update_post_meta ($post_id, 'token_order_id'.$amount , $token  );
        
        // echo "<pre>"; var_dump($token); echo "</pre>";  
        $url = $this->url . "topup";
        $options = array(
            'amount' => (int) $amount,
            'network' => $network ? $this->correct_operator($network) : 4,
            'topup_token' => $token
        );

        $auth = base64_encode( $this->secret . ':');
        $args = [
            'headers' => [
                'Authorization' => "Basic $auth"
            ],
            'body'    =>   $options,
           
        ];    
        $response =  wp_remote_post($url,$args);
        $response_body = wp_remote_retrieve_body( $response );       
        
        $res = json_decode($response_body);    
        // echo "<pre>"; var_dump($res); echo "</pre>";    
        $order_id_thailand_topup = $this->read_response($res);

        // var_dump($order_id_thailand_topup.' '.$amount);
        // insert data to table
        // $wpdb->insert("{$wpdb->base_prefix}thailand_top_up", [
        //     'ORDER_ID' => $post_id,
        //     'ORDER_ID_THAILAD_TOPUP' => $order_id_thailand_topup ,
        //     'TOKENT_API' => $token,
        // ]);

        return $order_id_thailand_topup;
    }

    /**
     * Read order ID
     * @param type $res
     * @return boolean
     */
    private function read_response($res) {
        if (!$res || !is_object($res)) {
            $this->error = 'API respond was FALSE';
            return false;
        } elseif (isset($res->order_id)) {
            $this->result = $res;
            return $res->order_id;
        } elseif (isset($res->error)) {
            $this->error = $res->error;
        }
        return false;
    }
    
    public function get_status($id = false) {
        if ($id) {
            
            $url = $this->url . 'retrieve-topup/' . $id;
            $auth = base64_encode( $this->secret . ':');
            $args = [
                'headers' => [
                    'Authorization' => "Basic $auth"
                ],
            ];      
         
            $response =  wp_remote_get($url,$args);
            $response_body = wp_remote_retrieve_body( $response );  
            $res = json_decode($response_body, TRUE);
            return $res && isset($res['status']) ? $res['status'] : false;
        }
        else 
        {
            return $this->result ? $this->result->status : false;
        }
    }

    public function check_status($tr_id = false) {
        $status = $this->get_status($tr_id);
        if ($status == 'SUCCESS' || $status == 'FAILED') {
            return $status;
        }
       
        $this->status_errors = array($status);
        return false;
    }

    public function get_error() {
        return $this->error;
    }

    /**
     * Check if the carrier has correct form ("AIS 12Call", "True Move", "DTAC Happy")
     * @param string $operator Carrier
     * @return string
     */
    private function correct_operator($operator) {
        switch ($operator) {
            case "AIS":
                return "AIS 12Call";
            case "True":
            case "True Move":
            case "TRUEMOVE":
            case "TRUE MOVE H":
                return "True Move";
            case "DTAC":
                return "DTAC Happy";
            default:
                return $operator;
        }
    }

    /**
     * Clear the phone number
     * @param string $mobile
     * @return int
     */
    private function correct_number($mobile) {
        return preg_replace('/[^0-9]/', '', $mobile);
    }
}
