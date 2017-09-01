<?php

add_action( 'wp_ajax_order_details', 'get_order_details' );
add_action( 'wp_ajax_nopriv_order_details', 'get_order_details' );

add_action( 'wp_ajax_breed_tests', 'get_breed_tests' );
add_action( 'wp_ajax_nopriv_breed_tests', 'get_breed_tests' );


 function get_order_details() {
	 global $wpdb; // this is how you get access to the database
	
	 $return = array();
	 $orderId = intval( $_POST['orderId'] );
	
	 $order_details = $wpdb->get_row("select o.* from orders o where o.id=".$orderId);
	 $client_details = $wpdb->get_row("select c.* from orders o left outer join client c on client_id=c.id where o.id=".$orderId);
	 $test_details = $wpdb->get_results("select b.breed, a.*, t.*, test_name, no_results, no_swabs, sub_tests from orders o inner join order_tests t on o.id=order_id left outer join animal a on animal_id=a.id inner join breed_list b on a.breed_id=b.id left outer join test_codes using(test_code) where b.is_primary=1 and o.id=".$orderId);
	 
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