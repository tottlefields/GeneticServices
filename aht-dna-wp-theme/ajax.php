<?php

add_action( 'wp_ajax_order_details', 'get_order_details' );
add_action( 'wp_ajax_nopriv_order_details', 'get_order_details' );


 function get_order_details() {
	 global $wpdb; // this is how you get access to the database
	
	 $return = array();
	 $orderId = intval( $_POST['orderId'] );
	
	 $order_details = $wpdb->get_row("select o.* from orders o where o.id=".$orderId);
	 $client_details = $wpdb->get_row("select c.* from orders o left outer join client c on client_id=c.id where o.id=".$orderId);
	 $test_details = $wpdb->get_results("select a.*, t.*, test_name from orders o inner join order_tests t on o.id=order_id left outer join animal a on animal_id=a.id left outer join test_codes using(test_code) where o.id=".$orderId);
	 
	 $order_details->test_details = $test_details;
	
	 $return['order'] = $order_details;
	 $return['client'] = $client_details;
	 
	 echo json_encode($return);
	
	 wp_die(); // this is required to terminate immediately and return a proper response
 }

?>