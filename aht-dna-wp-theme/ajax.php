<?php

add_action( 'wp_ajax_order_details', 'get_order_details' );
add_action( 'wp_ajax_nopriv_order_details', 'get_order_details' );

add_action( 'wp_ajax_breed_tests', 'get_breed_tests' );
add_action( 'wp_ajax_nopriv_breed_tests', 'get_breed_tests' );


 function get_order_details() {
	 global $wpdb; // this is how you get access to the database
	
	 $return = array();
	 echo $_POST['orderId'];
	 $orderId = intval( $_POST['orderId'] );
	 $swabId = intval( $_POST['swabId'] );
	
	 //$order_details = $wpdb->get_row("select o.* from orders o where o.id=".$orderId);
	 $orders = orderSearch(array('id' => $orderId));
	 $order_details = $orders[0];
	 foreach ($order_details as $key => $value){
	 	if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $value)){
	 		$order_details->$key = SQLToDate($value);
	 	}
	 }
	 
	 $client_details = $wpdb->get_row("select c.* from orders o left outer join client c on client_id=c.id where o.id=".$orderId);
	 foreach ($client_details as $key => $value){
	 	if($value === null){ $client_details->$key = ""; }
	 }
	 
	 if (isset($swabId) && $swabId > 0){ $test_details = getTestDetails($swabId); }
	 else{ $test_details = getTestsByOrder($orderId); }
	 foreach ($test_details as $key => $value){
	 	if($value === null){ $test_details->$key = ""; }
	 	elseif (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $value)){
	 		$test_details->$key = SQLToDate($value);
	 	}
	 }
	 $order_details->test_details = $test_details;
	
	 $return['order'] = $order_details;
	 $return['client'] = $client_details;
	 
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