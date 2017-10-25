<?php

$order_steps = array(
		'Order Placed',
		'Kit(s) Dispatched',
		'Sample(s) Received',
		'Sample(s) Processed',
		'Data Analysis & QC',
		'Result(s) Sent'
);

$sexes = array('m' => 'Male', 'f', 'Female');

function debug_array($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function dateToSQL($date){
	if ($date == ""){ return ""; }
	return date_format(DateTime::createFromFormat('d/m/Y', $date), 'Y-m-d');
}

function SQLToDate($date){
	if ($date == ""){ return ""; }
	return date_format(DateTime::createFromFormat('Y-m-d', $date), 'd/m/Y');
}

function countOrders($status){
	global $wpdb;
	$sql = "SELECT count(distinct orders.id) from orders inner join order_tests on orders.id=order_id where cancelled_date is null";
	switch($status){
		case 'Order Placed' : $sql .= ' AND kit_sent IS NULL'; break;
		case 'Sample(s) Received' : $sql .= ' AND returned_date IS NOT NULL'; break;
		case 'Sample(s) Processed' : $sql .= ''; break;
	}
	$count = $wpdb->get_var($sql, OBJECT );
	return $count;	
}

function clientSearch($search_terms){
	global $wpdb;	
	$clients = array();
	
	if(count($search_terms)>0){
		$sql = "SELECT * FROM client WHERE (";
		//$last = array_pop(array_keys($search_terms));
		$where = array();
		foreach ($search_terms as $field => $term){
			if($term !== ""){
				array_push($where, "($field <> ''  AND $field = '$term')");
			}
			//$sql .= "($field <> ''  AND $field = '$term')";
			//if($field != $last) { $sql .= ' OR '; }
		}
		$sql .= implode(" AND ", $where);
		$sql .= ")";
		$clients = $wpdb->get_results($sql, OBJECT );
		//echo $wpdb->last_query."\n";
	}
	return $clients;
}

function animalSearch($search_terms, $client_id=0){
	global $wpdb;	
	$animals = array();
	
	if(count($search_terms)>0){
		$sql = "SELECT *, date_format(BirthDate, \"%d/%m/%Y\") as DOB, case when Sex='f' then 'Female' else 'Male' end as sex FROM animal WHERE (";
		//$last = array_pop(array_keys($search_terms));
		$where = array();
		foreach ($search_terms as $field => $term){
			if($term !== ""){
				array_push($where, "($field <> ''  AND $field = '$term')");
			}
			//$sql .= "($field <> ''  AND $field = '$term')";
			//if($field != $last) { $sql .= ' OR '; }
		}
		$sql .= implode(" AND ", $where);
		$sql .= ")";
		if (isset($client_id) && $client_id > 0){
			$sql .= " AND client_id=$client_id";
		}
		$animals = $wpdb->get_results($sql, OBJECT );
		//echo $wpdb->last_query."\n";
	}
	return $animals;
	
}

function orderSearch($search_terms){
	global $wpdb;	
	$clients = array();
	
	if(count($search_terms)>0){
//		$sql = "SELECT * FROM orders WHERE (";
		$sql = "select o.OrderID as webshop_id, o.id as ID, OrderDate, ReportFormat, VetReportFormat, Paid, AgreeResearch, 
			client_id, FullName, Email, o.ShippingCountry, count(*) as TestCount, content 
			from orders o left outer join client on client.id=client_id left outer join order_tests on o.id=order_id WHERE (";
		//$last = array_pop(array_keys($search_terms));
		$where = array();
		foreach ($search_terms as $field => $term){
			//$sql .= "$field = '$term'";
			//if($field != $last) { $sql .= ' AND '; }
			if($term !== ""){
				if ($field == 'id'){ $field = 'o.id'; }
				array_push($where, "$field = '$term'");
			}
		}
		$sql .= implode(" AND ", $where);
		$sql .= ")  group by o.id";
		$orders = $wpdb->get_results($sql, OBJECT );
	}
	return $orders;
}

function getTestsByOrder($order_id){
	global $wpdb;	
	$tests = array();
	
	$sql = "select id from order_tests where order_id=".$order_id;
	$test_ids = $wpdb->get_results($sql, ARRAY_N );
	foreach ($test_ids as $row){
		$test_details = getTestDetails($row[0]);
		array_push($tests, $test_details);
	}
	return $tests;	
}

function getTestsByAnimal($animal_id){
	global $wpdb;	
	$tests = array();
	
	$sql = "select id from order_tests where animal_id=".$animal_id;
	$test_ids = $wpdb->get_results($sql, ARRAY_N );
	foreach ($test_ids as $row){
		$test_details = getTestDetails($row[0]);
		array_push($tests, $test_details);
	}
	return $tests;	
}

function getTestsByClient($client_id){
	global $wpdb;	
	$tests = array();
	
	$sql = "select t.* from order_tests t inner join orders o on order_id=o.id  where client_id=".$client_id;
	$test_ids = $wpdb->get_results($sql, ARRAY_N );
	foreach ($test_ids as $row){
		$test_details = getTestDetails($row[0]);
		array_push($tests, $test_details);
	}
	return $tests;	
}

function getTestDetails($swab_id){
	global $wpdb;	
	$test_details = array();
	
	$sql = "select case when b.breed is NOT NULL then b.breed else a.Breed end as breed, date_format(o.OrderDate, \"%d/%m/%Y\") as order_date,
			a.*, t.*, test_name, no_results, no_swabs, sub_tests, 
			date_format(a.BirthDate, \"%d/%m/%Y\") as DOB, case when Sex='f' then 'Female' else 'Male' end as sex
			from orders o inner join order_tests t on o.id=order_id 
			left outer join animal a on animal_id=a.id 
			left outer join breed_list b on a.breed_id=b.id 
			left outer join test_codes using(test_code) 
			where (a.breed_id is NULL or b.is_primary=1) and t.id=".$swab_id;
	$test_details = $wpdb->get_results($sql, OBJECT );
#	echo $wpdb->last_query."\n";
#	echo $wpdb->last_result."\n";
#	echo $wpdb->last_error."\n";
	return $test_details[0];	
}


function addNewClient($client){
	//| id | ClientID | Tel | Fax | Email | FullName | Address | Address2 | Address3 | Town | County | Postcode | Country |
	//| ShippingName | ShippingCompany | ShippingAddress | ShippingAddress2 | ShippingAddress3 | ShippingTown | ShippingCounty | ShippingPostcode | ShippingCountry |
	global $wpdb;
	
	$wpdb->insert('client', $client);
	$insert_id = $wpdb->insert_id;
	
	return $insert_id;
	
}


function addNewAnimal($animal){
	//| id   | AnimalID | ClientID | Species | Breed                  | RegisteredName             | RegistrationNo | Sex  | BirthDate | TattooOrChip    | PetName | Colour            | breed_id | client_id |
	global $wpdb;
	
	$wpdb->insert('animal', $animal);
#	echo $wpdb->last_query."\n";
#	echo $wpdb->last_result."\n";
#	echo $wpdb->last_error."\n";
	$insert_id = $wpdb->insert_id;
	
	return $insert_id;
	
}

function addNewOrder($order){
	//| id   | client_id | OrderID | ClientID | OrderDate  | ReportFormat | VetReportFormat | Paid | AgreeResearch |
	global $wpdb;
	
	$wpdb->insert('orders', $order);
#	echo $wpdb->last_query."\n";
#	echo $wpdb->last_result."\n";
#	echo $wpdb->last_error."\n";
	$insert_id = $wpdb->insert_id;
	
	return $insert_id;
}

function addOrderTest($order_test){
	//| id   | OrderID | AnimalID | TestCode | Quantity | SampleType | VetID | kit_sent | returned_date | received_by | order_id | animal_id | test_code |
	global $wpdb;

	$wpdb->insert('order_tests', $order_test);
#	echo $wpdb->last_query."\n";
#	echo $wpdb->last_result."\n";
#	echo $wpdb->last_error."\n";
	$insert_id = $wpdb->insert_id;

	return $insert_id;
}





//test/order statistics
function countTests($year=null, $month=0){
	global $wpdb;
	
	if(!isset($year))
		$year = date('Y');
	
	$sql = "SELECT COUNT(*) FROM order_tests t INNER JOIN orders o ON t.order_id=o.id WHERE YEAR(OrderDate)='".$year."'";
	if (isset($month) && $month > 0){
		$sql .= " AND MONTH(OrderDate)='".$month."'";
	}
	$test_count = $wpdb->get_var($sql);
	return $test_count;
}

function countOrders($year=null, $month=0){
	global $wpdb;
	
	if(!isset($year))
		$year = date('Y');
	
	$sql = "SELECT COUNT(*) FROM orders WHERE YEAR(OrderDate)='".$year."'";
	if (isset($month) && $month > 0){
		$sql .= " AND MONTH(OrderDate)='".$month."'";
	}
	$test_count = $wpdb->get_var($sql);
	return $test_count;
}





?>
