<?php

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

function clientSearch($search_terms){
	global $wpdb;	
	$clients = array();
	
	if(count($search_terms)>0){
		$sql = "SELECT * FROM client WHERE (";
		$last = array_pop(array_keys($search_terms));
		foreach ($search_terms as $field => $term){
			$sql .= "$field = '$term'";
			if($field != $last) { $sql .= ' OR '; }
		}
		$sql .= ")";
		$clients = $wpdb->get_results($sql, OBJECT );
	}
	return $clients;
}

function animalSearch($search_terms, $client_id=0){
	global $wpdb;	
	$animals = array();
	
	if(count($search_terms)>0){
		$sql = "SELECT * FROM animal WHERE (";
		$last = array_pop(array_keys($search_terms));
		foreach ($search_terms as $field => $term){
			$sql .= "$field = '$term'";
			if($field != $last) { $sql .= ' OR '; }
		}
		$sql .= ")";
		if (isset($client_id) && $client_id > 0){
			$sql .= " AND client_id=$client_id";
		}
		$animals = $wpdb->get_results($sql, OBJECT );
	}
	return $animals;
	
}


function addNewAnimal($animal){
	//| id   | AnimalID | ClientID | Species | Breed                  | RegisteredName             | RegistrationNo | Sex  | BirthDate | TattooOrChip    | PetName | Colour            | breed_id | client_id |
	global $wpdb;
	
	$wpdb->insert('animal', $animal);
	$insert_id = $wpdb->insert_id;
	
	return $insert_id;
	
}

function addNewOrder($order){
	//| id   | client_id | OrderID | ClientID | OrderDate  | ReportFormat | VetReportFormat | Paid | AgreeResearch |
	global $wpdb;
	
	$wpdb->insert('orders', $order);
	$insert_id = $wpdb->insert_id;
	
	return $insert_id;
}

function addOrderTest($order_test){
	//| id   | OrderID | AnimalID | TestCode | Quantity | SampleType | VetID | kit_sent | returned_date | received_by | order_id | animal_id | test_code |
	global $wpdb;

	$wpdb->insert('order_tests', $order_test);
	$insert_id = $wpdb->insert_id;

	return $insert_id;
}












?>