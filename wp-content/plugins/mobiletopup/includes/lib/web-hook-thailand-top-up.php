<?php

function pippin_stripe_event_listener() {
	global $wpdb;
	$body = file_get_contents('php://input');
	$event_json = json_decode($body);
	if(!$event_json){
		$event_json = 'test';
	}	
	// grab the event information
	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
	$filename= $DOCUMENT_ROOT. '/wp-content/plugins/mobiletopup/log/txt.log';
	$fh = fopen($filename, 'w+');//open file and create if does not exist
	fwrite($fh, $event_json);//write data
	fclose($fh);//close file


	if(isset($_GET['wps-listener']) && $_GET['wps-listener'] == 'thailandtopup') {
		// we will process the events here
		
 
		// grab the event information
		

		// if( $event_json )
		// {
		// 	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}thailand_top_up WHERE ORDER_ID_THAILAD_TOPUP = $event_json->order_id", OBJECT );
			
		// 	if( $results ){
				
		// 		foreach($results as $result ){

		// 			$order_id  = $result->ORDER_ID;
		// 			$order = new WC_Order($order_id);
		// 			//$post_id_products = get_post_id_mobile_top_up($order);
		// 			// $target_number = get_post_meta( $post_id_products, 'target_number', true );
		// 			$note = $event_json->status.' top up to'.'+66'.$target_number.' for '.$event_json->amount.' THB';
						
		// 			if(  $event_json->status=='SUCCESS'){
		// 				$order->update_status($event_json->status );
		// 				$order->add_order_note( $note , 1);
		// 				return;
		// 			}
	
		// 			$order->update_status($event_json->status );
		// 			$order->add_order_note( $note , 1);
		// 			return;
		// 		}
		// 	}
		// }
	}
}

// function convertFormatPhone($phone){

// 	$phone = preg_replace('/[^\p{L}\p{N}\s]/u', '', $phone);
// 	$phone = '0'.$phone;
// 	return $phone; 

// }
add_action('init', 'pippin_stripe_event_listener');