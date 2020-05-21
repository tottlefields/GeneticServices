<?php
add_action ( 'wp_ajax_plate_details', 'get_plate_details' );
add_action ( 'wp_ajax_nopriv_plate_details', 'get_plate_details' );

add_action ( 'wp_ajax_order_details', 'get_order_details' );
add_action ( 'wp_ajax_nopriv_order_details', 'get_order_details' );

add_action ( 'wp_ajax_cancel_test', 'do_cancel_test' );
add_action ( 'wp_ajax_nopriv_cancel_test', 'do_cancel_test' );

add_action ( 'wp_ajax_cancel_order', 'do_cancel_order' );
add_action ( 'wp_ajax_nopriv_cancel_order', 'do_cancel_order' );

add_action ( 'wp_ajax_request_repeat', 'do_request_repeat' );
add_action ( 'wp_ajax_nopriv_request_repeat', 'do_request_repeat' );

add_action ( 'wp_ajax_send_sample', 'do_send_sample' );
add_action ( 'wp_ajax_nopriv_send_sample', 'do_send_sample' );

add_action ( 'wp_ajax_return_sample', 'do_return_sample' );
add_action ( 'wp_ajax_nopriv_return_sample', 'do_return_sample' );

add_action ( 'wp_ajax_order_paid', 'do_order_paid' );
add_action ( 'wp_ajax_nopriv_order_paid', 'do_order_paid' );

add_action ( 'wp_ajax_breed_tests', 'get_breed_tests' );
add_action ( 'wp_ajax_nopriv_breed_tests', 'get_breed_tests' );

add_action ( 'wp_ajax_pick_swabs', 'pick_swabs' );
add_action ( 'wp_ajax_nopriv_pick_swabs', 'pick_swabs' );

add_action ( 'wp_ajax_extract_swabs', 'extract_swabs' );
add_action ( 'wp_ajax_nopriv_extract_swabs', 'extract_swabs' );

add_action ( 'wp_ajax_add_plate', 'add_plate' );
add_action ( 'wp_ajax_nopriv_add_plate', 'add_plate' );

function do_cancel_test() {
	global $wpdb, $current_user; // this is how you get access to the database

	$swabId = intval ( $_POST ['swabId'] );

	$update_args = array (
			'cancelled_by' => $current_user->user_login,
			'cancelled_date' => date ( 'Y-m-d' )
	);

	$wpdb->update ( 'order_tests', $update_args, array (
			'id' => $swabId
	) );

	echo json_encode ( array (
			'results' => 'Successfully cancelled test with id of ' . $swabId
	) );

	wp_die ();
}

function do_cancel_order() {
	global $wpdb, $current_user; // this is how you get access to the database
	
	$orderId = intval ( $_POST ['orderId'] );
	$reason = $_POST['reason'];
	
	$update_args = array (
		'cancelled_by' => $current_user->user_login,
		'cancelled_date' => date ( 'Y-m-d' )
	);
	
	$wpdb->update ( 'order_tests', $update_args, array (
		'order_id' => $orderId
	) );	
	
	
	$sql = "select id from order_tests where order_id=".$orderId;
	$test_ids = $wpdb->get_results($sql, ARRAY_N );
	foreach ($test_ids as $row){
		$note_data = array (
				'test_id' => $row[0],
				'note_by' => $current_user->user_login,
				'note_text' => base64_encode ( stripslashes ( "Order cancelled - ".$reason ) )
		);
		$wpdb->insert ( 'order_test_notes', $note_data );
	}
	
	echo json_encode ( array (
		'results' => 'Successfully cancelled order with id of ' . $orderId
	) );
	
	wp_die ();
}

function do_request_repeat() {
	global $wpdb, $current_user; // this is how you get access to the database

	$swabId = intval ( $_POST ['swabId'] );

	$old_data = $wpdb->get_row ( 'select OrderID,PortalID,AnimalID,TestCode,Quantity,SampleType,order_id,animal_id,test_code,bundle from order_tests where id=' . $swabId );
	$new_data = array (
			'OrderID' => $old_data->OrderID,
			'PortalID' => $old_data->PortalID,
			'AnimalID' => $old_data->AnimalID,
			'TestCode' => $old_data->TestCode,
			'Quantity' => $old_data->Quantity,
			'SampleType' => $old_data->SampleType,
			'order_id' => $old_data->order_id,
			'animal_id' => $old_data->animal_id,
			'test_code' => $old_data->test_code,
			'bundle' => $old_data->bundle
	);
	$wpdb->insert ( 'order_tests', $new_data );
	$new_swab = $wpdb->insert_id;

	$update_args = array (
			'cancelled_by' => $current_user->user_login,
			'cancelled_date' => date ( 'Y-m-d' ),
			'repeat_swab' => $new_swab
	);
	$wpdb->update ( 'order_tests', $update_args, array (
			'id' => $swabId
	) );

	$note_data = array (
			'test_id' => $swabId,
			'note_by' => $current_user->user_login,
			'note_text' => base64_encode ( stripslashes ( "Repeat swab requested for this test - replacement test ID is $new_swab." ) )
	);
	$wpdb->insert ( 'order_test_notes', $note_data );

	echo json_encode ( array (
			'results' => 'Successfully requested a repeat sample with id of ' . $new_swab
	) );

	wp_die ();
}

function do_send_sample() {
	global $wpdb, $current_user; // this is how you get access to the database

	$swabId = intval ( $_POST ['swabId'] );

	$update_args = array (
			'sent_by' => $current_user->user_login,
			'kit_sent' => date ( 'Y-m-d' )
	);

	$wpdb->update ( 'order_tests', $update_args, array (
			'id' => $swabId
	) );

	echo json_encode ( array (
			'results' => 'Successfully logged dispatch of sample with id of ' . $swabId
	) );

	wp_die ();
}

function do_return_sample() {
	global $wpdb, $current_user; // this is how you get access to the database

	$swabId = intval ( $_POST ['swabId'] );
	$date = new DateTime ( 'now', new DateTimeZone ( "Europe/London" ) );
	$returned_date = $date->format ( 'Y-m-d H:i:s' );
	$date->add ( new DateInterval ( 'P21D' ) );

	$update_args = array (
			'received_by' => $current_user->user_login,
			'returned_date' => $returned_date,
			'due_date' => $date->format ( 'Y-m-d' )
	);

	$wpdb->update ( 'order_tests', $update_args, array (
			'id' => $swabId
	) );
	createSwabs ( $swabId );

	echo json_encode ( array (
			'results' => 'Successfully logged return of sample with id of ' . $swabId
	) );

	wp_die ();
}

function do_order_paid() {
	global $wpdb, $current_user; // this is how you get access to the database

	$orderId = intval ( $_POST ['orderId'] );
	$wpdb->update ( 'orders', array (
			'Paid' => 1
	), array (
			'id' => $orderId
	) );

	echo json_encode ( array (
			'results' => 'Successfully marked order #' . $orderId . ' as paid.'
	) );

	wp_die ();
}

function get_plate_details() {
	global $wpdb;
	$return = array ();

	$plate_id = $_POST ['pid'];

	$return = getPlateDetails ( $plate_id );
	echo json_encode ( $return );

	wp_die ();
}

function extract_swabs() {
	global $wpdb, $current_user;

	$return = array ();
	$plate = $_POST ['plate'];
	$barcode = $_POST ['barcode'];
	$well = $_POST ['well'];

	$sample_details = explode ( '/', $barcode );
	$swabs = getSwabDetails ( $sample_details [1] );
	$test_details = getBasicTestDetails ( $sample_details [1] );

	if (count ( $swabs ) == 2 && $swabs [1]->extraction_plate != null) {
		$return = array (
				'status' => 'Error',
				'msg' => 'Both swabs have already been recorded as extracted for this sample.'
		);
	} else {
		$query_args = array (
				'extraction_plate' => $plate,
				'extraction_well' => $well,
				'extracted_by' => $current_user->user_login,
				'extraction_date' => date ( 'Y-m-d' )
		);
		if ($swabs [0]->extraction_plate != null) {
			$query_args ['test_id'] = $sample_details [1];
			$query_args ['swab'] = 'B';
			$wpdb->insert ( 'test_swabs', $query_args );
		} else {
			$wpdb->update ( 'test_swabs', $query_args, array (
					'id' => $swabs [0]->id
			) );
		}
		$return = array (
				'status' => 'Success',
				'data' => array (
						'barcode' => 'AHT' . $barcode,
						'order_id' => $sample_details [0],
						'test_code' => $test_details->test_code
				)
		);
	}

	echo json_encode ( $return );

	wp_die ();
}

function pick_swabs() {
	global $wpdb; // this is how you get access to the database

	$return = array ();
	$swab_ids = $_POST ['swabIds'];

	$sql = 'select test_code, concat("AHT",order_id,"/",t.id) as barcode, due_date, datediff(due_date, date(NOW())) as days, test_type
    from order_tests t inner join test_swabs s on t.id=s.test_id
    left outer join test_codes using (test_code)
    inner join animal a on a.id=animal_id
    inner join breed_list b on b.id=breed_id
    where t.id in (' . $swab_ids . ')
    order by test_code, due_date, t.ID';
	$results = $wpdb->get_results ( $sql, ARRAY_A );

	foreach ( $results as $row ) {
		if (! isset ( $return [$row ['test_code']] )) {
			$return [$row ['test_code']] = array ();
		}
		array_push ( $return [$row ['test_code']], $row );
	}

	echo json_encode ( $return );

	wp_die ();
}

function get_order_details() {
	global $wpdb; // this is how you get access to the database

	$return = array ();
	$swabId = intval ( $_POST ['swabId'] );
	$pending = isset ( $_POST ['pending'] ) ? intval ( $_POST ['pending'] ) : 0;

	$order_ids = (is_array ( $_POST ['orderId'] )) ? $_POST ['orderId'] : explode ( ',', trim ( $_POST ['orderId'] ) );
	foreach ( $order_ids as $orderId ) {
		$order_return = array ();
		$orderId = intval ( $orderId );

		// $order_details = $wpdb->get_row("select o.* from orders o where o.id=".$orderId);
		$orders = orderSearch ( array (
				'id' => $orderId
		) );
		$order_details = $orders [0];
		foreach ( $order_details as $key => $value ) {
			if (preg_match ( '/\d{4}-\d{1,2}-\d{1,2}/', $value )) {
				$order_details->$key = SQLToDate ( $value );
			}
		}

		$client_details = $wpdb->get_row ( "select c.*, o.* from orders o left outer join client c on client_id=c.id where o.id=" . $orderId );
		foreach ( $client_details as $key => $value ) {
			if ($value === null) {
				$client_details->$key = "";
			}
		}

		if (isset ( $swabId ) && $swabId > 0) {
			$test_details = array (
					getTestDetails ( $swabId )
			);
		} else {
			$test_details = getTestsByOrder ( $orderId, $pending );
		}
		foreach ( $test_details as $test ) {
			foreach ( $test as $key => $value ) {
				if ($value === null) {
					$test->$key = "";
				} elseif (is_string ( $value ) && preg_match ( '/\d{4}-\d{1,2}-\d{1,2}/', $value )) {
					$test->$key = SQLToDate ( $value );
				}
			}
		}
		$order_details->test_details = $test_details;

		$order_return ['order'] = $order_details;
		$order_return ['client'] = $client_details;
		array_push ( $return, $order_return );
	}

	echo json_encode ( $return );

	wp_die (); // this is required to terminate immediately and return a proper response
}

function get_breed_tests() {
	global $wpdb; // this is how you get access to the database

	$return = array ();
	$breedId = intval ( $_POST ['breedId'] );

	$test_details = $wpdb->get_results ( "SELECT * FROM breed_test_lookup INNER JOIN test_codes using (test_code) WHERE breed_id in (0," . $breedId . ") order by test_name" );
	$return ['tests'] = $test_details;

	echo json_encode ( $return );

	wp_die (); // this is required to terminate immediately and return a proper response
}

function add_plate() {
	global $wpdb, $current_user; // this is how you get access to the database
	
	$username = (isset($_POST['username'])) ? $_POST['username'] : $current_user->user_login;
	$now_date = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d H:i:s');
	$new_plate = getNextPlate($_POST['plate_type']);	
	
	$result = $wpdb->insert( "plates", array(
			'plate_type' => $_POST['plate_type'],
			'test_plate' => $new_plate,
			'created_by' => $username,
			'created_date' => $now_date
	));
	
	$update_args = array();
	$count = 0;
	foreach ($_POST['plate_data']['cols'] as $col){
		$count++;
		if (preg_match ( '/^Q\d+/', $col )) { $update_args['col'.$count] = $col; }
	}
	$result = $wpdb->update('plates', $update_args, array('test_plate' => $new_plate));
	
	$swabs_seen = array();
	
	foreach ($_POST['plate_data']['wells'] as $well => $swab){
		if (preg_match ( '/^\d{5,}$/', $swab )) {
			$sql = "select t1.id, t1.test_id, t2.test_code,  no_results, sub_tests 
			from test_swabs t1 inner join order_tests t2 on t1.test_id=t2.id 
			inner join test_codes t3 using (test_code) where t1.id=".$swab;
			$results = $wpdb->get_results($sql, OBJECT );
			
			foreach ($results as $r){
				if ($r->no_results > 1){
					foreach (explode(':', $r->sub_tests) as $test_code){
						$wpdb->query("INSERT INTO test_swab_results (test_id, swab_id, test_code, test_plate, test_plate_well)
						VALUES (".$r->test_id.",".$swab.",'".$test_code."','".$new_plate."','".$well."')");
					}
				}
				elseif (isset($r->sub_tests)){
					$sub_tests = explode(':', $r->sub_tests);
					if (!isset($swabs_seen[$swab])){ $swabs_seen[$swab] = 0; }
					$wpdb->query("INSERT INTO test_swab_results (test_id, swab_id, test_code, test_plate, test_plate_well)
						VALUES (".$r->test_id.",".$swab.",'".$sub_tests[$swabs_seen[$swab]]."','".$new_plate."','".$well."')");
				}
				else{
					$wpdb->query("INSERT INTO test_swab_results (test_id, swab_id, test_code, test_plate, test_plate_well)
					VALUES (".$r->test_id.",".$swab.",'".$r->test_code."','".$new_plate."','".$well."')");
				}
			}
			$result = $wpdb->update('test_swabs', array('plate_allocated' => 1), array('id' => $swab));
			$swabs_seen[$swab]++;
		}
		else{
			$swab_data = explode(':', $swab);
			$wpdb->insert('plate_wells', array( 'test_plate' => $new_plate, 'well_id' => $well, 'well_contents' => $swab_data[1], 'test_code' => $swab_data[0] ));
		}
	}
	
	echo json_encode ( array('redirect' => get_site_url().'/plate/'.$new_plate ) );	
	wp_die (); // this is required to terminate immediately and return a proper response
	
	
}

?>
