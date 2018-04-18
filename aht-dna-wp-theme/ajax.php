<?php

add_action( 'wp_ajax_order_details', 'get_order_details' );
add_action( 'wp_ajax_nopriv_order_details', 'get_order_details' );

add_action( 'wp_ajax_cancel_test', 'do_cancel_test' );
add_action( 'wp_ajax_nopriv_cancel_test', 'do_cancel_test' );

add_action( 'wp_ajax_send_sample', 'do_send_sample' );
add_action( 'wp_ajax_nopriv_send_sample', 'do_send_sample' );

add_action( 'wp_ajax_return_sample', 'do_return_sample' );
add_action( 'wp_ajax_nopriv_return_sample', 'do_return_sample' );

add_action( 'wp_ajax_breed_tests', 'get_breed_tests' );
add_action( 'wp_ajax_nopriv_breed_tests', 'get_breed_tests' );


function do_cancel_test(){
	global $wpdb, $current_user; // this is how you get access to the database
	
	$swabId = intval( $_POST['swabId'] );
	
	$update_args = array(
			'cancelled_by' => $current_user->user_login,
			'cancelled_date' => date('Y-m-d')
	);
	
	$wpdb->update('order_tests', $update_args, array('id' => $swabId));

	echo json_encode(array('results' => 'Successfully cancelled test with id of '.$swabId));
	
	wp_die();
}

function do_send_sample(){
	global $wpdb, $current_user; // this is how you get access to the database
	
	$swabId = intval( $_POST['swabId'] );
	
	$update_args = array(
			'sent_by' => $current_user->user_login,
			'kit_sent' => date('Y-m-d')
	);
	
	$wpdb->update('order_tests', $update_args, array('id' => $swabId));

	echo json_encode(array('results' => 'Successfully logged dispatch of sample with id of '.$swabId));
	
	wp_die();
}

function do_return_sample(){
	global $wpdb, $current_user; // this is how you get access to the database
	
	$swabId = intval( $_POST['swabId'] );
	
	$update_args = array(
			'received_by' => $current_user->user_login,
			'returned_date' => date('Y-m-d')
	);
	
	$wpdb->update('order_tests', $update_args, array('id' => $swabId));

	echo json_encode(array('results' => 'Successfully logged return of sample with id of '.$swabId));
	
	wp_die();
}

 function get_order_details() {
	global $wpdb; // this is how you get access to the database
	
	$return = array();
	$swabId = intval( $_POST['swabId'] );
	$pending = isset( $_POST['pending'] ) ? intval( $_POST['pending'] ) : 0;
	
	$order_ids = (is_array($_POST['orderId'])) ? $_POST['orderId'] : explode(',', trim($_POST['orderId']));		
	foreach ($order_ids as $orderId){
		$order_return = array();
		$orderId = intval( $orderId );
	
		 //$order_details = $wpdb->get_row("select o.* from orders o where o.id=".$orderId);
		 $orders = orderSearch(array('id' => $orderId));
		 $order_details = $orders[0];
		 foreach ($order_details as $key => $value){
		 	if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $value)){
		 		$order_details->$key = SQLToDate($value);
		 	}
		 }
		 
		 $client_details = $wpdb->get_row("select c.*, o.* from orders o left outer join client c on client_id=c.id where o.id=".$orderId);
		 foreach ($client_details as $key => $value){
		 	if($value === null){ $client_details->$key = ""; }
		 }
		 
		 if (isset($swabId) && $swabId > 0){ $test_details = [getTestDetails($swabId)]; }
		 else{ $test_details = getTestsByOrder($orderId, $pending); }
		 foreach ($test_details as $test){
		 	foreach ($test as $key => $value){
			 	if($value === null){ $test->$key = ""; }
			 	elseif(is_string($value) && preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $value)){
			 		$test->$key = SQLToDate($value);
			 	}
		 	}
		 }
		 $order_details->test_details = $test_details;
		
		 $order_return['order'] = $order_details;
		 $order_return['client'] = $client_details;
		 array_push($return, $order_return);
	}
	 
	echo json_encode($return);
	
	wp_die(); // this is required to terminate immediately and return a proper response
 }
 
 function get_breed_tests(){
	 global $wpdb; // this is how you get access to the database
	
	 $return = array();
	 $breedId = intval( $_POST['breedId'] );
	 
	 $test_details = $wpdb->get_results("SELECT * FROM breed_test_lookup INNER JOIN test_codes using (test_code) WHERE breed_id in (0,".$breedId.") order by test_name");
	 $return['tests'] = $test_details;
	 
	 echo json_encode($return);
	
	 wp_die(); // this is required to terminate immediately and return a proper response
 	
 }

?>